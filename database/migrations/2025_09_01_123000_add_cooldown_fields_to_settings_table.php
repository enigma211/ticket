<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'cooldown_login_minutes')) {
                $table->unsignedInteger('cooldown_login_minutes')->default(10);
            }
            if (!Schema::hasColumn('settings', 'cooldown_password_minutes')) {
                $table->unsignedInteger('cooldown_password_minutes')->default(10);
            }
            if (!Schema::hasColumn('settings', 'cooldown_ticket_minutes')) {
                $table->unsignedInteger('cooldown_ticket_minutes')->default(30);
            }
            if (!Schema::hasColumn('settings', 'cooldown_message_minutes')) {
                $table->unsignedInteger('cooldown_message_minutes')->default(5);
            }
            if (!Schema::hasColumn('settings', 'cooldown_attachment_minutes')) {
                $table->unsignedInteger('cooldown_attachment_minutes')->default(2);
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach ([
                'cooldown_login_minutes',
                'cooldown_password_minutes',
                'cooldown_ticket_minutes',
                'cooldown_message_minutes',
                'cooldown_attachment_minutes',
            ] as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};


