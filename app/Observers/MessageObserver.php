<?php

namespace App\Observers;

use App\Models\Message;

class MessageObserver
{
	public function created(Message $message): void
	{
		// Only for public messages
		if ($message->visibility !== 'public') {
			return;
		}

		$ticket = $message->ticket()->first();
		if (!$ticket || $ticket->status === 'closed') {
			return;
		}

		$user = $message->user;
		$byAgent = $user && ($user->is_agent || $user->is_superadmin);
		$ticket->update([
			'status' => $byAgent ? 'awaiting_user' : 'open',
		]);
	}
}
