<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'email_forgot_enabled')) {
                $table->boolean('email_forgot_enabled')->default(true)->after('retention_delete_months');
            }
            if (!Schema::hasColumn('settings', 'email_forgot_subject')) {
                $table->string('email_forgot_subject')->nullable();
            }
            if (!Schema::hasColumn('settings', 'email_forgot_html')) {
                $table->longText('email_forgot_html')->nullable();
            }
            if (!Schema::hasColumn('settings', 'email_welcome_enabled')) {
                $table->boolean('email_welcome_enabled')->default(false);
            }
            if (!Schema::hasColumn('settings', 'email_welcome_subject')) {
                $table->string('email_welcome_subject')->nullable();
            }
            if (!Schema::hasColumn('settings', 'email_welcome_html')) {
                $table->longText('email_welcome_html')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach ([
                'email_forgot_enabled', 'email_forgot_subject', 'email_forgot_html',
                'email_welcome_enabled', 'email_welcome_subject', 'email_welcome_html',
            ] as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};


