<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('internal_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('thread_id')->nullable()->after('to_user_id');
            $table->index('thread_id');
            $table->foreign('thread_id')->references('id')->on('internal_messages')->onDelete('cascade');
        });
        try {
            DB::statement('UPDATE internal_messages SET thread_id = id WHERE thread_id IS NULL');
        } catch (\Throwable $e) {
            // ignore if table empty
        }
    }

    public function down(): void
    {
        Schema::table('internal_messages', function (Blueprint $table) {
            $table->dropForeign(['thread_id']);
            $table->dropIndex(['thread_id']);
            $table->dropColumn('thread_id');
        });
    }
};


