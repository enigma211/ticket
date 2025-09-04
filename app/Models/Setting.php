<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'site_name',
        'home_title',
        'home_subtitle',
        'footer_text',
        'timezone',
        'logo_path',
        'max_upload_mb',
        'allowed_mimes',
        'max_description_words',
        'auto_close_days',
        'working_days',
        'working_start_time',
        'working_end_time',
        'allow_user_submission',
        'faq_json',
        'cooldown_login_minutes',
        'cooldown_password_minutes',
        'cooldown_ticket_minutes',
        'cooldown_message_minutes',
        'cooldown_attachment_minutes',
        'retention_archive_months',
        'retention_delete_months',
        'email_forgot_enabled',
        'email_forgot_subject',
        'email_forgot_html',
        'email_welcome_enabled',
        'email_welcome_subject',
        'email_welcome_html',
        'email_queue_enabled',
        'email_rate_limit_count',
        'email_rate_limit_minutes',
    ];

    protected $casts = [
        'max_upload_mb' => 'integer',
        'max_description_words' => 'integer',
        'auto_close_days' => 'integer',
        'working_days' => 'string',
        'working_start_time' => 'string',
        'working_end_time' => 'string',
        'allow_user_submission' => 'boolean',
        'faq_json' => 'array',
        'cooldown_login_minutes' => 'integer',
        'cooldown_password_minutes' => 'integer',
        'cooldown_ticket_minutes' => 'integer',
        'cooldown_message_minutes' => 'integer',
        'cooldown_attachment_minutes' => 'integer',
        'retention_archive_months' => 'integer',
        'retention_delete_months' => 'integer',
    ];

    public static function instance()
    {
        return Cache::remember('app_settings', 3600, function () {
            return static::firstOrCreate(['id' => 1]);
        });
    }

    public static function clearCache()
    {
        Cache::forget('app_settings');
    }

    protected static function booted()
    {
        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }
}