<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternalMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternalMessageController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->is_superadmin && !$user->is_agent)) {
            abort(403, 'دسترسی مجاز نیست.');
        }
        $tab = $request->input('tab', 'inbox');
        // Show one row per conversation (thread): latest message per thread
        $base = DB::table('internal_messages')
            ->select('thread_id')
            ->when($tab === 'sent', function($q) use ($user){
                $q->where('from_user_id', $user->id);
            }, function($q) use ($user){
                $q->where('to_user_id', $user->id);
            })
            ->groupBy('thread_id');

        $latestPerThread = DB::table('internal_messages as im')
            ->select('im.thread_id', DB::raw('MAX(im.id) as max_id'))
            ->whereIn('im.thread_id', $base)
            ->groupBy('im.thread_id');

        $query = InternalMessage::query()
            ->from('internal_messages as m')
            ->joinSub($latestPerThread, 't', function($join){
                $join->on('m.thread_id', '=', 't.thread_id')->on('m.id', '=', 't.max_id');
            })
            ->with(['sender:id,name,first_name,last_name', 'recipient:id,name,first_name,last_name', 'thread:id,subject']);

        if ($request->filled('q')) {
            $q = (string) $request->input('q');
            $query->where(function($sub) use ($q){
                $sub->where('m.subject', 'like', "%{$q}%")
                    ->orWhere('m.body', 'like', "%{$q}%");
            });
        }

        $messages = $query->orderByDesc('m.created_at')->select('m.*')->paginate(20)->withQueryString();
        return view('admin.internal_messages.index', compact('messages', 'tab'));
    }

    public function create()
    {
        $user = auth()->user();
        if (!$user || (!$user->is_superadmin && !$user->is_agent)) {
            abort(403, 'دسترسی مجاز نیست.');
        }
        $recipients = User::query()->where(function($q){ $q->where('is_agent', true)->orWhere('is_superadmin', true); })->orderBy('first_name')->orderBy('last_name')->get();
        return view('admin.internal_messages.create', compact('recipients'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->is_superadmin && !$user->is_agent)) {
            abort(403, 'دسترسی مجاز نیست.');
        }
        $validated = $request->validate([
            'to_user_id' => ['required', 'exists:users,id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);
        $message = InternalMessage::create([
            'from_user_id' => $user->id,
            'to_user_id' => (int) $validated['to_user_id'],
            'subject' => $validated['subject'] ?? null,
            'body' => $validated['body'],
        ]);
        // Set thread_id to self for root messages
        $message->update(['thread_id' => $message->thread_id ?: $message->id]);
        return redirect()->route('admin.internal_messages.index')->with('success', 'پیام ارسال شد.');
    }

    public function show(InternalMessage $internalMessage)
    {
        $user = auth()->user();
        if (!$user || (!$user->is_superadmin && !$user->is_agent)) {
            abort(403, 'دسترسی مجاز نیست.');
        }
        if ($internalMessage->to_user_id !== $user->id && $internalMessage->from_user_id !== $user->id) {
            abort(403);
        }
        if ($internalMessage->to_user_id === $user->id && !$internalMessage->read_at) {
            $internalMessage->update(['read_at' => now()]);
        }
        $internalMessage->load(['sender', 'recipient']);
        $threadId = $internalMessage->thread_id ?: $internalMessage->id;
        $conversation = InternalMessage::query()
            ->with(['sender:id,name,first_name,last_name'])
            ->where('thread_id', $threadId)
            ->orderBy('created_at')
            ->get();
        return view('admin.internal_messages.show', compact('internalMessage', 'conversation'));
    }

    public function reply(Request $request, InternalMessage $internalMessage)
    {
        $user = auth()->user();
        if (!$user || (!$user->is_superadmin && !$user->is_agent)) { abort(403, 'دسترسی مجاز نیست.'); }
        if ($internalMessage->to_user_id !== $user->id && $internalMessage->from_user_id !== $user->id) { abort(403); }
        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);
        $threadId = $internalMessage->thread_id ?: $internalMessage->id;
        $toUserId = $internalMessage->from_user_id === $user->id ? $internalMessage->to_user_id : $internalMessage->from_user_id;
        $msg = InternalMessage::create([
            'from_user_id' => $user->id,
            'to_user_id' => $toUserId,
            'thread_id' => $threadId,
            'subject' => null,
            'body' => $validated['body'],
        ]);
        return back()->with('success', 'پاسخ ارسال شد.');
    }

    public function destroyMany(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->is_superadmin && !$user->is_agent)) { abort(403); }
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:internal_messages,id'],
        ]);
        $ids = collect($validated['ids'])->map(fn($v) => (int) $v)->all();
        $deleted = InternalMessage::whereIn('id', $ids)
            ->where(function($q) use ($user){
                $q->where('from_user_id', $user->id)->orWhere('to_user_id', $user->id);
            })
            ->delete();
        return back()->with('success', "{$deleted} پیام حذف شد.");
    }
}


