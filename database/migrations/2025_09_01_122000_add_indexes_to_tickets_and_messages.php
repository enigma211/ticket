<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Helper to check if an index exists
        $indexExists = function (string $table, string $indexName): bool {
            $connection = config('database.connections.' . config('database.default'));
            $driver = $connection['driver'] ?? 'mysql';
            if ($driver === 'mysql') {
                $dbName = $connection['database'] ?? DB::getDatabaseName();
                $rows = DB::select('SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1', [$dbName, $table, $indexName]);
                return !empty($rows);
            }
            if ($driver === 'pgsql') {
                $rows = DB::select('SELECT 1 FROM pg_indexes WHERE schemaname = ANY(current_schemas(false)) AND tablename = ? AND indexname = ? LIMIT 1', [$table, $indexName]);
                return !empty($rows);
            }
            // Fallback best effort
            return false;
        };

        // tickets.user_id
        if (!$indexExists('tickets', 'idx_tickets_user_id')) {
            Schema::table('tickets', function ($table) {
                $table->index('user_id', 'idx_tickets_user_id');
            });
        }

        // messages.ticket_id, messages.created_at, and composite (ticket_id, created_at)
        if (!$indexExists('messages', 'idx_messages_ticket_id')) {
            Schema::table('messages', function ($table) {
                $table->index('ticket_id', 'idx_messages_ticket_id');
            });
        }
        if (!$indexExists('messages', 'idx_messages_created_at')) {
            Schema::table('messages', function ($table) {
                $table->index('created_at', 'idx_messages_created_at');
            });
        }
        if (!$indexExists('messages', 'idx_messages_ticket_created')) {
            Schema::table('messages', function ($table) {
                $table->index(['ticket_id', 'created_at'], 'idx_messages_ticket_created');
            });
        }
    }

    public function down(): void
    {
        $dropIndexIfExists = function (string $table, string $indexName): void {
            $connection = config('database.connections.' . config('database.default'));
            $driver = $connection['driver'] ?? 'mysql';
            $exists = false;
            if ($driver === 'mysql') {
                $dbName = $connection['database'] ?? DB::getDatabaseName();
                $rows = DB::select('SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1', [$dbName, $table, $indexName]);
                $exists = !empty($rows);
            } elseif ($driver === 'pgsql') {
                $rows = DB::select('SELECT 1 FROM pg_indexes WHERE schemaname = ANY(current_schemas(false)) AND tablename = ? AND indexname = ? LIMIT 1', [$table, $indexName]);
                $exists = !empty($rows);
            }
            if ($exists) {
                Schema::table($table, function ($table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }
        };

        $dropIndexIfExists('tickets', 'idx_tickets_user_id');
        $dropIndexIfExists('messages', 'idx_messages_ticket_id');
        $dropIndexIfExists('messages', 'idx_messages_created_at');
        $dropIndexIfExists('messages', 'idx_messages_ticket_created');
    }
};


