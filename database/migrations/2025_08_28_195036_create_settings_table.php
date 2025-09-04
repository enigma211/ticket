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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('سامانه تیکت');
            $table->string('home_title')->default('سامانه پشتیبانی و تیکتینگ');
            $table->text('home_subtitle')->default('در این سامانه می‌توانید تیکت‌های پشتیبانی خود را ایجاد کرده و با تیم پشتیبانی در ارتباط باشید.');
            $table->string('footer_text')->default('سامانه تیکت');
            $table->string('logo_path')->nullable();
            $table->unsignedInteger('max_upload_mb')->default(5);
            $table->string('allowed_mimes')->default('jpg,jpeg,png,pdf');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
