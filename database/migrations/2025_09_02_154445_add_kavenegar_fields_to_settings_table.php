<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('kavenegar_api_key')->nullable()->after('email_rate_limit_minutes');
            $table->string('kavenegar_sender')->nullable()->after('kavenegar_api_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['kavenegar_api_key', 'kavenegar_sender']);
        });
    }
};
