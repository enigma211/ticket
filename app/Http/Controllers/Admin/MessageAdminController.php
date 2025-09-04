<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class MessageAdminController extends Controller
{
	public function index(Request $request)
	{
		$query = Message::query()->with(['user:id,name,email', 'ticket:id,tracking_code']);

		if ($request->filled('q')) {
			$q = $request->string('q');
			$query->where(function ($sub) use ($q) {
				$sub->where('body', 'like', "%{$q}%")
					->orWhereHas('ticket', function ($t) use ($q) {
						$t->where('tracking_code', 'like', "%{$q}%");
					})
					->orWhereHas('user', function ($u) use ($q) {
						$u->where('name', 'like', "%{$q}%")
							->orWhere('email', 'like', "%{$q}%");
					});
			});
		}

		$messages = $query->latest()->paginate(20)->withQueryString();

		return view('admin.messages.index', compact('messages'));
	}

	public function edit(Message $message)
	{
		$message->load(['user:id,name,email', 'ticket:id,tracking_code']);
		return view('admin.messages.edit', compact('message'));
	}

	public function update(Request $request, Message $message)
	{
		$user = auth()->user();
		if (!$user) { abort(403); }
		// Only superadmins can edit any message; agents can edit only their own messages
		if (!$user->is_superadmin && $message->user_id !== $user->id) {
			abort(403);
		}

		$validated = $request->validate([
			'body' => ['required', 'string'],
		]);
		$message->update([
			'body' => $validated['body'],
			'visibility' => 'public',
		]);
		return back()->with('success', 'پیام بروزرسانی شد.');
	}

	public function destroy(Message $message)
	{
		$user = auth()->user();
		if (!$user) { abort(403); }
		if (!$user->is_superadmin && $message->user_id !== $user->id) {
			abort(403);
		}
		$attachments = $message->attachments()->get();
		$paths = [];
		foreach ($attachments as $att) { $paths[] = $att->path; }
		if (!empty($paths)) { \Illuminate\Support\Facades\Storage::disk('public')->delete($paths); }
		$message->delete();
		return back()->with('success', 'پیام حذف شد.');
	}
}
