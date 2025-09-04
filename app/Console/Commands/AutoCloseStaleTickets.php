<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AutoCloseStaleTickets extends Command
{
    protected $signature = 'tickets:auto-close {--days=5 : Days to wait before auto-closing}';

    protected $description = 'Auto-close tickets awaiting user response after N days with no user reply';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: (int) (settings('auto_close_days', 5)));
        $cutoff = now()->subDays($days);

        $closedCount = 0;

        Ticket::query()
            ->where('status', 'awaiting_user')
            ->orderBy('id')
            ->chunkById(200, function ($tickets) use ($cutoff, &$closedCount) {
                foreach ($tickets as $ticket) {
                    // Find latest public message
                    $lastPublic = $ticket->messages()
                        ->where('visibility', 'public')
                        ->latest('created_at')
                        ->with('user')
                        ->first();

                    if (!$lastPublic) {
                        continue;
                    }

                    $user = $lastPublic->user;
                    $byAgent = $user && ($user->is_agent || $user->is_superadmin);

                    if ($byAgent && $lastPublic->created_at->lte($cutoff)) {
                        $ticket->update(['status' => 'auto_closed']);
                        $closedCount++;
                    }
                }
            });

        $this->info("Auto-closed {$closedCount} tickets.");
        return Command::SUCCESS;
    }
}


