<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->nullable();
            $table->string('mailable')->nullable();
            $table->string('subject')->nullable();
            $table->string('to');
            $table->enum('status', ['queued','sent','failed'])->default('queued');
            $table->text('error')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'to']);
            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};


