<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('working_days')->default('sat,sun,mon,tue,wed,thu')->after('auto_close_days');
            $table->string('working_start_time')->default('09:00')->after('working_days');
            $table->string('working_end_time')->default('17:00')->after('working_start_time');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'working_end_time')) {
                $table->dropColumn('working_end_time');
            }
            if (Schema::hasColumn('settings', 'working_start_time')) {
                $table->dropColumn('working_start_time');
            }
            if (Schema::hasColumn('settings', 'working_days')) {
                $table->dropColumn('working_days');
            }
        });
    }
};


