<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Message;
use App\Models\User;
use App\Models\InternalMessage;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = [
            'total_tickets' => Ticket::count(),
            'open_tickets' => Ticket::whereIn('status', ['new', 'open'])->count(),
            'closed_tickets' => Ticket::where('status', 'closed')->count(),
            'tickets_today' => Ticket::whereDate('created_at', today())->count(),
            'messages_last_24h' => Message::where('created_at', '>=', now()->subDay())->count(),
            'normal_users' => User::where('is_agent', false)->where('is_superadmin', false)->count(),
        ];

        // My assigned tickets pending my response (latest message from user or no messages)
        $myAssignedPending = Ticket::where('assigned_to', auth()->id())
            ->whereIn('status', ['new', 'open'])
            ->get()
            ->filter(function ($ticket) {
                $latestMessage = $ticket->messages()->latest()->first();
                if (!$latestMessage) {
                    return true; // no messages yet => pending
                }
                $user = $latestMessage->user;
                return $user && (!$user->is_agent && !$user->is_superadmin);
            })
            ->count();
        $metrics['my_assigned_pending'] = $myAssignedPending;

        // Unanswered tickets (where latest message is from user, not agent)
        $unansweredTickets = Ticket::whereIn('status', ['new', 'open'])
            ->whereHas('messages', function ($query) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->where('is_agent', false)
                        ->where('is_superadmin', false);
                });
            })
            ->get()
            ->filter(function ($ticket) {
                $latestMessage = $ticket->messages()->latest()->first();
                if ($latestMessage) {
                    $user = $latestMessage->user;
                    return !$user->is_agent && !$user->is_superadmin;
                }
                return true; // No messages, so unanswered
            });

        $metrics['unanswered_tickets'] = $unansweredTickets->count();

        // Unread internal messages (inbox) for current staff
        $metrics['unread_internal_messages'] = InternalMessage::where('to_user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return view('admin.dashboard', compact('metrics'));
    }
}