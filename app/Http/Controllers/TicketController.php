<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Message;
use App\Models\TicketAssignment;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request)
	{
		$query = Ticket::query()->with(['user','department','assignee'])->orderByDesc('updated_at');
		$user = Auth::user();
		if (!$user || (!$user->is_agent && !$user->is_superadmin)) {
			$query->where('user_id', Auth::id());
		}
		// If agent (not superadmin), restrict to assigned departments
		if ($user && $user->is_agent && !$user->is_superadmin) {
			$deptIds = $user->departments()->pluck('departments.id');
			if ($deptIds->count() > 0) {
				$query->whereIn('department_id', $deptIds);
			}
			// If agent has no departments assigned, show none
			if ($deptIds->count() === 0) {
				$query->whereRaw('1=0');
			}
		}
		// Admin/Agent filters and tracking code search
		if ($user && ($user->is_agent || $user->is_superadmin)) {
			// Status filter
			$status = trim((string) $request->input('status', ''));
			if ($status !== '') {
				if ($status === 'open') {
					$query->whereIn('status', ['new','open']);
				} else {
					$query->where('status', $status);
				}
			}
			// Assigned to me filter
			if ($request->boolean('assigned') || $request->input('assigned') === 'me') {
				$query->where('assigned_to', $user->id);
			}

			// Archived filter
			$archived = $request->input('archived');
			if ($archived === 'only') {
				$query->whereNotNull('archived_at');
			} elseif ($archived === 'hide') {
				$query->whereNull('archived_at');
			}

			$tracking = trim((string) $request->input('tracking_code', ''));
			if ($tracking !== '') {
				$query->where('tracking_code', $tracking);
			}
			$fromJ = trim((string) $request->input('from_j', ''));
			$toJ = trim((string) $request->input('to_j', ''));
			if ($fromJ !== '') {
				try {
					$fromCarbon = Jalalian::fromFormat('Y/m/d', $fromJ)->toCarbon()->startOfDay();
					$query->where('updated_at', '>=', $fromCarbon);
				} catch (\Throwable $e) {
					// ignore invalid format
				}
			}
			if ($toJ !== '') {
				try {
					$toCarbon = Jalalian::fromFormat('Y/m/d', $toJ)->toCarbon()->endOfDay();
					$query->where('updated_at', '<=', $toCarbon);
				} catch (\Throwable $e) {
					// ignore invalid format
				}
			}
		}
		$tickets = $query->paginate(50);

		return view('tickets.index', compact('tickets'));
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		return view('tickets.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		if (!settings('allow_user_submission', true)) {
			return back()->withErrors(['error' => 'ارسال پیام توسط کاربران موقتاً غیرفعال است.'])->withInput();
		}

		// Cooldown check (moved from middleware to here to allow immediate retry after failures)
		$user = $request->user();
		if (!$user || (!$user->is_agent && !$user->is_superadmin)) {
			$minutes = max(1, (int) (settings('cooldown_ticket_minutes') ?? 20));
			$key = 'cooldown:ticket:' . ($user?->id ?? $request->ip());
			if (RateLimiter::tooManyAttempts($key, 1)) {
				$seconds = RateLimiter::availableIn($key);
				return back()->withErrors(['error' => 'لطفاً ' . $seconds . ' ثانیه صبر کنید و سپس دوباره تلاش کنید.'])->withInput();
			}
		}
		// Working hours guard
		$days = explode(',', (string) settings('working_days', 'sat,sun,mon,tue,wed,thu'));
		$start = (string) settings('working_start_time', '09:00');
		$end = (string) settings('working_end_time', '17:00');
		$now = now();
		$dayKey = strtolower($now->format('D'));
		if (!in_array($dayKey, $days)) {
			return back()->withErrors(['error' => 'ارسال پیام خارج از روزهای کاری مجاز نیست.'])->withInput();
		}
		if ($now->format('H:i') < $start || $now->format('H:i') > $end) {
			return back()->withErrors(['error' => 'ارسال پیام خارج از ساعات کاری مجاز نیست.'])->withInput();
		}

		$maxKb = settings('max_upload_mb', 5) * 1024;
		$allowed = collect(explode(',', (string) settings('allowed_mimes', 'jpg,jpeg,png,pdf')))
			->map(fn($s) => strtolower(trim($s)))
			->filter(fn($s) => $s !== '' && !in_array($s, ['php','phar','phtml','js','html','svg','exe','sh','bat','cmd'], true))
			->implode(',');
		
		// Word limit
		$maxWords = (int) settings('max_description_words', 700);
		$validated = $request->validate([
			'title' => ['required', 'string', 'max:255'],
			'description' => ['required', 'string', function ($attribute, $value, $fail) use ($maxWords) {
				$words = preg_split('/\s+/', trim((string) $value)) ?: [];
				if (count($words) > $maxWords) {
					$fail("حداکثر {$maxWords} کلمه مجاز است.");
				}
			}],
			'department_id' => ['required', 'exists:departments,id'],
			'attachments.*' => ['file', "mimes:{$allowed}", "max:{$maxKb}"],
		]);

		$ticket = Ticket::create([
			'user_id' => Auth::id(),
			'department_id' => $validated['department_id'] ?? null,
			'title' => $validated['title'],
			'description' => $validated['description'],
			'status' => 'open',
		]);

		// Assign tracking code after creation to avoid unique race
		$ticket->tracking_code = \App\Services\TicketTrackingCodeService::generateNext();
		$ticket->save();

		// Create an initial message to hold any attachments and initial description context
		$initialMessage = $ticket->messages()->create([
			'user_id' => Auth::id(),
			'body' => $validated['description'],
		]);

		if ($request->hasFile('attachments')) {
			foreach ($request->file('attachments') as $file) {
				$path = $file->store('attachments', 'local');
				$initialMessage->attachments()->create([
					'path' => $path,
					'original_name' => $file->getClientOriginalName(),
					'mime_type' => $file->getClientMimeType(),
					'size' => $file->getSize(),
				]);
			}
		}

		// Hit cooldown only after a successful create
		if (!$user || (!$user->is_agent && !$user->is_superadmin)) {
			$minutes = max(1, (int) (settings('cooldown_ticket_minutes') ?? 20));
			$key = 'cooldown:ticket:' . ($user?->id ?? $request->ip());
			RateLimiter::hit($key, $minutes * 60);
		}

		$user = Auth::user();
		$successMsg = 'تیکت ایجاد شد';
		if ($user && ($user->is_agent || $user->is_superadmin)) {
			return redirect()->route('admin.tickets.show', $ticket)->with('status', $successMsg);
		}
		return redirect()->route('tickets.show', $ticket)->with('status', $successMsg);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Ticket $ticket)
	{
		$user = Auth::user();
		$canViewAll = $user && ($user->is_agent || $user->is_superadmin);
		if (!$canViewAll && $ticket->user_id !== Auth::id()) {
			abort(403);
		}
		// Agent enforcement by departments
		if ($user && $user->is_agent && !$user->is_superadmin) {
			$deptIds = $user->departments()->pluck('departments.id')->toArray();
			if (!in_array($ticket->department_id, $deptIds, true)) {
				abort(403);
			}
		}
		$messages = $ticket->messages()->with('user', 'attachments')->oldest()->paginate(10);
		return view('tickets.show', compact('ticket', 'messages'));
	}

	public function print(Request $request, Ticket $ticket)
	{
		$user = Auth::user();
		if (!$user || (!$user->is_agent && !$user->is_superadmin)) {
			abort(403);
		}
		$validated = $request->validate([
			'message_ids' => ['required', 'array'],
			'message_ids.*' => ['integer', 'exists:messages,id'],
		]);
		$messages = $ticket->messages()
			->with('user')
			->whereIn('id', $validated['message_ids'])
			->orderBy('created_at')
			->get();

		return view('admin.tickets.print', [
			'ticket' => $ticket,
			'user' => $ticket->user,
			'messages' => $messages,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Ticket $ticket)
	{
		$user = Auth::user();
		$canViewAll = $user && ($user->is_agent || $user->is_superadmin);
		if (!$canViewAll && $ticket->user_id !== Auth::id()) {
			abort(403);
		}
		return view('tickets.edit', compact('ticket'));
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Ticket $ticket)
	{
		$user = Auth::user();
		$canViewAll = $user && ($user->is_agent || $user->is_superadmin);
		if (!$canViewAll && $ticket->user_id !== Auth::id()) {
			abort(403);
		}
		$validated = $request->validate([
			'title' => ['required', 'string', 'max:255'],
			'description' => ['required', 'string'],
			'department_id' => ['nullable', 'exists:departments,id'],
			'status' => ['required', 'in:open,awaiting_user,closed,auto_closed'],
		]);

		$ticket->update([
			'title' => $validated['title'],
			'description' => $validated['description'],
			'department_id' => $validated['department_id'] ?? null,
			'status' => $validated['status'],
		]);
		return redirect()->route('admin.tickets.show', $ticket)->with('status', 'Ticket updated');
	}

	public function close(Ticket $ticket)
	{
		$user = Auth::user();
		if (!$user || (!$user->is_agent && !$user->is_superadmin)) {
			abort(403);
		}
		$ticket->update(['status' => 'closed']);
		return redirect()->route('admin.tickets.show', $ticket)->with('status', 'تیکت بسته شد');
	}

	public function reopen(Ticket $ticket)
	{
		$user = Auth::user();
		if (!$user || (!$user->is_agent && !$user->is_superadmin)) {
			abort(403);
		}
		$ticket->update(['status' => 'open']);
		return redirect()->route('admin.tickets.show', $ticket)->with('status', 'تیکت باز شد');
	}

	public function assign(Request $request, Ticket $ticket)
	{
		$user = Auth::user();
		if (!$user || (!$user->is_agent && !$user->is_superadmin)) {
			abort(403);
		}
		$validated = $request->validate([
			'assigned_to' => ['required', 'exists:users,id'],
			'note' => ['nullable', 'string', 'max:2000'],
		]);
		$fromUserId = $ticket->assigned_to; // previous assignee (could be null)
		$ticket->update(['assigned_to' => $validated['assigned_to']]);
		TicketAssignment::create([
			'ticket_id' => $ticket->id,
			'from_user_id' => $fromUserId,
			'to_user_id' => $validated['assigned_to'],
			'note' => $validated['note'] ?? null,
		]);
		return back()->with('status', 'تیکت ارجاع داده شد');
	}

	public function bulk(Request $request)
	{
		$user = Auth::user();
		if (!$user || (!$user->is_agent && !$user->is_superadmin)) {
			abort(403);
		}
		$validated = $request->validate([
			'action' => ['required', 'in:delete,assign'],
			'ids' => ['required', 'array'],
			'ids.*' => ['integer', 'exists:tickets,id'],
			'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
			'note' => ['nullable', 'string', 'max:2000'],
		]);

		$tickets = Ticket::whereIn('id', $validated['ids'])->get();
		// Filter to agent's departments if needed
		if ($user->is_agent && !$user->is_superadmin) {
			$deptIds = $user->departments()->pluck('departments.id')->toArray();
			$tickets = $tickets->whereIn('department_id', $deptIds);
		}
		$affected = 0;
		if ($validated['action'] === 'delete') {
			// Only superadmin allowed for permanent delete
			if (!$user->is_superadmin) {
				return back()->withErrors(['error' => 'فقط سوپرمدیر مجاز به حذف دائمی است.']);
			}
			foreach ($tickets as $t) {
				$messages = $t->messages()->with('attachments')->get();
				$paths = [];
				foreach ($messages as $m) {
					foreach ($m->attachments as $att) {
						$paths[] = $att->path;
					}
				}
				if (!empty($paths)) {
					Storage::disk('public')->delete($paths);
				}
				$t->delete();
				$affected++;
			}
		} elseif ($validated['action'] === 'assign') {
			if (empty($validated['assigned_to'])) {
				return back()->withErrors(['error' => 'کارشناس مقصد را انتخاب کنید.']);
			}
			foreach ($tickets as $t) {
				$from = $t->assigned_to;
				$t->update(['assigned_to' => $validated['assigned_to']]);
				TicketAssignment::create([
					'ticket_id' => $t->id,
					'from_user_id' => $from,
					'to_user_id' => $validated['assigned_to'],
					'note' => $validated['note'] ?? null,
				]);
				$assignee = User::find($validated['assigned_to']);
				$assigneeName = $assignee?->full_name ?? $assignee?->name ?? ('#' . $validated['assigned_to']);
				$body = "ارجاع به کارشناس: {$assigneeName}";
				if (!empty($validated['note'])) {
					$body .= "\nیادداشت: " . $validated['note'];
				}
				Message::create([
					'ticket_id' => $t->id,
					'user_id' => Auth::id(),
					'body' => $body,
					'visibility' => 'internal',
				]);
				$affected++;
			}
		}

		return back()->with('status', "اقدام گروهی روی {$affected} تیکت انجام شد.");
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Ticket $ticket)
	{
		$user = Auth::user();
		$canViewAll = $user && ($user->is_agent || $user->is_superadmin);
		if (!$canViewAll && $ticket->user_id !== Auth::id()) {
			abort(403);
		}
		$messages = $ticket->messages()->with('attachments')->get();
		$paths = [];
		foreach ($messages as $m) {
			foreach ($m->attachments as $att) {
				$paths[] = $att->path;
			}
		}
		if (!empty($paths)) {
			Storage::disk('public')->delete($paths);
		}
		$ticket->delete();
		return redirect()->route('admin.tickets.index')->with('status', 'Ticket deleted');
	}
}
