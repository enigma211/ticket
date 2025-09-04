<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'retention_archive_months')) {
                $table->integer('retention_archive_months')->nullable();
            }
            if (!Schema::hasColumn('settings', 'retention_delete_months')) {
                $table->integer('retention_delete_months')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'retention_archive_months')) {
                $table->dropColumn('retention_archive_months');
            }
            if (Schema::hasColumn('settings', 'retention_delete_months')) {
                $table->dropColumn('retention_delete_months');
            }
        });
    }
};


