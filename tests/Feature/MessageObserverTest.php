<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageObserverTest extends TestCase
{
	use RefreshDatabase;

	public function test_agent_public_reply_sets_awaiting_user(): void
	{
		$user = User::factory()->create(['is_agent' => true]);
		$this->actingAs($user);
		$ticketOwner = User::factory()->create();
		$ticket = Ticket::factory()->create(['user_id' => $ticketOwner->id, 'status' => 'open']);

		Message::create([
			'ticket_id' => $ticket->id,
			'user_id' => $user->id,
			'body' => 'Reply',
			'visibility' => 'public',
		]);

		$ticket->refresh();
		$this->assertEquals('awaiting_user', $ticket->status);
	}

	public function test_user_public_reply_sets_open(): void
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$ticket = Ticket::factory()->create(['user_id' => $user->id, 'status' => 'awaiting_user']);

		Message::create([
			'ticket_id' => $ticket->id,
			'user_id' => $user->id,
			'body' => 'User reply',
			'visibility' => 'public',
		]);

		$ticket->refresh();
		$this->assertEquals('open', $ticket->status);
	}

	public function test_internal_note_does_not_change_status(): void
	{
		$user = User::factory()->create(['is_agent' => true]);
		$this->actingAs($user);
		$ticketOwner = User::factory()->create();
		$ticket = Ticket::factory()->create(['user_id' => $ticketOwner->id, 'status' => 'open']);

		Message::create([
			'ticket_id' => $ticket->id,
			'user_id' => $user->id,
			'body' => 'Internal',
			'visibility' => 'internal',
		]);

		$ticket->refresh();
		$this->assertEquals('open', $ticket->status);
	}
}
