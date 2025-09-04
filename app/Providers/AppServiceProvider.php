<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use App\Models\Setting;
use App\Models\Message;
use App\Observers\MessageObserver;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\Events\MessageFailed;
use App\Models\EmailLog;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }
        // Conditionally force HTTPS via env flag (safe for local without SSL)
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        // Apply timezone from settings if defined
        try {
            $tz = optional(Setting::instance())->timezone ?: null;
            if ($tz) {
                // Support either IANA name (e.g., Asia/Tehran) or GMT offset (+03:30)
                if (preg_match('/^[+-]\d{2}:\d{2}$/', $tz)) {
                    // For precise offsets including minutes, keep base timezone UTC and track offset
                    Config::set('app.timezone', 'UTC');
                    date_default_timezone_set('UTC');
                    $offsetMinutes = ((int)substr($tz, 1, 2)) * 60 + ((int)substr($tz, 4, 2));
                    if ($tz[0] === '-') { $offsetMinutes = -$offsetMinutes; }
                    Config::set('app.gmt_offset_minutes', $offsetMinutes);
                } else {
                    Config::set('app.timezone', $tz);
                    @date_default_timezone_set($tz);
                    Config::set('app.gmt_offset_minutes', null);
                }
            }
        } catch (\Throwable $e) {
            // ignore if settings table not migrated yet
        }

        Message::observe(MessageObserver::class);

        // Email delivery logging
        Event::listen(MessageSending::class, function (MessageSending $event) {
            try {
                $addresses = collect($event->message->getTo() ?? [])->map(fn($a) => $a->getAddress())->implode(',');
                EmailLog::create([
                    'message_id' => method_exists($event->message, 'getId') ? ($event->message->getId() ?: null) : null,
                    'mailable' => is_string($event->data['__laravel_notification'] ?? null) ? $event->data['__laravel_notification'] : null,
                    'subject' => $event->message->getSubject(),
                    'to' => $addresses ?: '-',
                    'status' => 'queued',
                    'queued_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // swallow
            }
        });
        Event::listen(MessageSent::class, function (MessageSent $event) {
            try {
                $msgId = method_exists($event->message, 'getId') ? ($event->message->getId() ?: null) : null;
                $query = EmailLog::query();
                if ($msgId) { $query->where('message_id', $msgId); }
                $log = $query->latest()->first();
                if ($log) { $log->update(['status' => 'sent', 'sent_at' => now()]); }
            } catch (\Throwable $e) {}
        });
        Event::listen(MessageFailed::class, function (MessageFailed $event) {
            try {
                $msgId = method_exists($event->message, 'getId') ? ($event->message->getId() ?: null) : null;
                $query = EmailLog::query();
                if ($msgId) { $query->where('message_id', $msgId); }
                $addresses = collect($event->message->getTo() ?? [])->map(fn($a) => $a->getAddress())->implode(',');
                $log = $query->latest()->first();
                if ($log) {
                    $log->update(['status' => 'failed', 'failed_at' => now(), 'error' => $event->exception?->getMessage()]);
                } else {
                    EmailLog::create([
                        'message_id' => $msgId,
                        'subject' => $event->message->getSubject(),
                        'to' => $addresses ?: '-',
                        'status' => 'failed',
                        'failed_at' => now(),
                        'error' => $event->exception?->getMessage(),
                    ]);
                }
            } catch (\Throwable $e) {}
        });

        // Blade directives for timezone-offset-aware jdate rendering
        Blade::directive('jdateOffset', function ($expression) {
            return "<?php \$__dt = $expression; \$__off = config('app.gmt_offset_minutes'); if (\$__off !== null && \$__dt) { \$__dt = \$__dt->copy()->addMinutes(\$__off); } echo jdate(\$__dt)->format('Y/m/d H:i'); ?>";
        });
        Blade::directive('jdateOffsetShort', function ($expression) {
            return "<?php \$__dt = $expression; \$__off = config('app.gmt_offset_minutes'); if (\$__off !== null && \$__dt) { \$__dt = \$__dt->copy()->addMinutes(\$__off); } echo jdate(\$__dt)->format('Y/m/d'); ?>";
        });
        Blade::directive('jyearOffset', function ($expression) {
            return "<?php \$__dt = $expression ?: now(); \$__off = config('app.gmt_offset_minutes'); if (\$__off !== null && \$__dt) { \$__dt = \$__dt->copy()->addMinutes(\$__off); } echo jdate(\$__dt)->format('Y'); ?>";
        });
    }
}
