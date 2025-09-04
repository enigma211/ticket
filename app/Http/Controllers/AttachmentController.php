<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class AttachmentController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Attachment $attachment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attachment $attachment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attachment $attachment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attachment $attachment)
    {
        $user = auth()->user();
        $isOwner = $attachment->message && $attachment->message->ticket && $attachment->message->ticket->user_id === auth()->id();
        $isStaff = $user && ($user->is_agent || $user->is_superadmin);
        if (!$isOwner && !$isStaff) {
            abort(403);
        }
        // Delete from private storage
        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();
        return back()->with('status', 'Attachment removed');
    }

    public function download(Attachment $attachment)
    {
        $user = auth()->user();
        $ticket = $attachment->message?->ticket;
        if (!$ticket) abort(404);
        $isOwner = $ticket->user_id === auth()->id();
        $isStaff = $user && ($user->is_agent || $user->is_superadmin);
        if (!$isOwner && !$isStaff) {
            abort(403);
        }
        if (!Storage::disk('local')->exists($attachment->path)) {
            abort(404);
        }
        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }
}
