<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketsRetentionCommand extends Command
{
    protected $signature = 'tickets:retention';
    protected $description = 'Archive and delete old tickets/messages/attachments based on retention settings';

    public function handle(): int
    {
        $setting = Setting::instance();
        $archiveMonths = (int) ($setting->retention_archive_months ?? 0);
        $deleteMonths = (int) ($setting->retention_delete_months ?? 0);

        $archived = 0; $deleted = 0; $attachmentsDeleted = 0;

        // Mark tickets as archived
        if ($archiveMonths > 0) {
            $archiveBefore = now()->subMonths($archiveMonths);
            $archived += Ticket::whereNull('archived_at')
                ->where('updated_at', '<', $archiveBefore)
                ->update(['archived_at' => now()]);
        }

        // Delete tickets and attachments older than deleteMonths
        if ($deleteMonths > 0) {
            $deleteBefore = now()->subMonths($deleteMonths);

            $ticketIds = Ticket::where('updated_at', '<', $deleteBefore)
                ->pluck('id');

            if ($ticketIds->count() > 0) {
                // Collect attachment paths
                $paths = DB::table('attachments')
                    ->join('messages', 'attachments.message_id', '=', 'messages.id')
                    ->whereIn('messages.ticket_id', $ticketIds)
                    ->pluck('attachments.path')
                    ->all();

                if (!empty($paths)) {
                    Storage::disk('public')->delete($paths);
                    $attachmentsDeleted = count($paths);
                }

                // Delete cascaded by foreign keys if set; else manual
                DB::transaction(function () use ($ticketIds) {
                    DB::table('attachments')->whereIn('message_id', function ($q) use ($ticketIds) {
                        $q->select('id')->from('messages')->whereIn('ticket_id', $ticketIds);
                    })->delete();
                    DB::table('messages')->whereIn('ticket_id', $ticketIds)->delete();
                    DB::table('tickets')->whereIn('id', $ticketIds)->delete();
                });
                $deleted = $ticketIds->count();
            }
        }

        $this->info("Archived: {$archived}, Deleted tickets: {$deleted}, Deleted attachments: {$attachmentsDeleted}");
        return Command::SUCCESS;
    }
}


