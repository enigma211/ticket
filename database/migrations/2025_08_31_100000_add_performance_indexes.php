<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $database = DB::getDatabaseName();

        $ensureIndex = function (string $table, string $indexName, string $definition) use ($database) {
            $exists = DB::selectOne(
                'SELECT COUNT(1) AS c FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$database, $table, $indexName]
            );
            if ((int)($exists->c ?? 0) === 0) {
                DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` ({$definition})");
            }
        };

        // tickets indexes
        $ensureIndex('tickets', 'idx_tickets_user_id', '`user_id`');
        $ensureIndex('tickets', 'idx_tickets_assigned_to', '`assigned_to`');
        $ensureIndex('tickets', 'idx_tickets_department_id', '`department_id`');
        $ensureIndex('tickets', 'idx_tickets_status_updated', '`status`, `updated_at`');

        // messages indexes
        $ensureIndex('messages', 'idx_messages_ticket_created', '`ticket_id`, `created_at`');
        $ensureIndex('messages', 'idx_messages_user_id', '`user_id`');
        $ensureIndex('messages', 'idx_messages_visibility', '`visibility`');

        // attachments indexes
        $ensureIndex('attachments', 'idx_attachments_message_id', '`message_id`');

        // users indexes (for role flags)
        $ensureIndex('users', 'idx_users_is_agent', '`is_agent`');
        $ensureIndex('users', 'idx_users_is_superadmin', '`is_superadmin`');
    }

    public function down(): void
    {
        $dropIndex = function (string $table, string $indexName) {
            try {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
            } catch (\Throwable $e) {
                // ignore
            }
        };

        $dropIndex('tickets', 'idx_tickets_user_id');
        $dropIndex('tickets', 'idx_tickets_assigned_to');
        $dropIndex('tickets', 'idx_tickets_department_id');
        $dropIndex('tickets', 'idx_tickets_status_updated');

        $dropIndex('messages', 'idx_messages_ticket_created');
        $dropIndex('messages', 'idx_messages_user_id');
        $dropIndex('messages', 'idx_messages_visibility');

        $dropIndex('attachments', 'idx_attachments_message_id');

        $dropIndex('users', 'idx_users_is_agent');
        $dropIndex('users', 'idx_users_is_superadmin');
    }
};


