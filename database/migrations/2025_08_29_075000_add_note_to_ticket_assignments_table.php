<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_assignments', 'note')) {
                $table->text('note')->nullable()->after('to_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ticket_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_assignments', 'note')) {
                $table->dropColumn('note');
            }
        });
    }
};


