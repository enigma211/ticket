<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Ticket;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $maxKb = settings('max_upload_mb', 5) * 1024;
        // sanitize allowed mimes: remove dangerous types if misconfigured
        $allowed = collect(explode(',', (string) settings('allowed_mimes', 'jpg,jpeg,png,pdf')))
            ->map(fn($s) => strtolower(trim($s)))
            ->filter(fn($s) => $s !== '' && !in_array($s, ['php','phar','phtml','js','html','svg','exe','sh','bat','cmd'], true))
            ->implode(',');
        
        $validated = $request->validate([
            'ticket_id' => ['required', 'exists:tickets,id'],
            'body' => ['required', 'string'],
            'attachments.*' => ['file', "mimes:{$allowed}", "max:{$maxKb}"],
        ]);

        $ticket = Ticket::findOrFail($validated['ticket_id']);
        if ($ticket->status === 'closed') {
            abort(403);
        }
        $user = Auth::user();
        $canManage = $user && ($user->is_agent || $user->is_superadmin);
        if (!$canManage && $ticket->user_id !== Auth::id()) {
            abort(403);
        }
        // Agent must belong to ticket's department
        if ($canManage && $user->is_agent && !$user->is_superadmin) {
            $allowed = $user->departments()->where('departments.id', $ticket->department_id)->exists();
            if (!$allowed) {
                abort(403);
            }
        }

        $message = Message::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'body' => $validated['body'],
            'visibility' => 'public',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'local');
                $message->attachments()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return back()->with('status', 'پیام ارسال شد');
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message)
    {
        if ($message->ticket->user_id !== Auth::id()) {
            abort(403);
        }
        $message->delete();
        return back()->with('status', 'Message deleted');
    }
}
