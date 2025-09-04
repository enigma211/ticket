<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'email_queue_enabled')) {
                $table->boolean('email_queue_enabled')->default(false)->after('email_welcome_html');
            }
            if (!Schema::hasColumn('settings', 'email_rate_limit_count')) {
                $table->unsignedInteger('email_rate_limit_count')->default(30);
            }
            if (!Schema::hasColumn('settings', 'email_rate_limit_minutes')) {
                $table->unsignedInteger('email_rate_limit_minutes')->default(10);
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (['email_queue_enabled','email_rate_limit_count','email_rate_limit_minutes'] as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};


