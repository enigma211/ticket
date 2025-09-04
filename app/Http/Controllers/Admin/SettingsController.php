<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function edit()
    {
        $setting = Setting::instance();
        return view('admin.settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'home_title' => ['required', 'string', 'max:255'],
            'home_subtitle' => ['required', 'string'],
            'footer_text' => ['required', 'string'],
            'timezone' => ['nullable', 'regex:/^(UTC|[+-]\d{2}:\d{2})$/'],
            // uploads & limits, ticket settings, and work hours moved to dedicated pages
            'logo' => ['nullable', 'image', 'max:2048'],
            // faq moved to dedicated page
        ]);

        $setting = Setting::instance();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }

            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo_path'] = $logoPath;
        }

        // Sanitize footer_text to avoid XSS while allowing some formatting
        $validated['footer_text'] = $this->sanitizeHtml($validated['footer_text'] ?? '');
        $setting->update($validated);

        return redirect()->route('admin.settings.edit')
            ->with('success', 'تنظیمات با موفقیت بروزرسانی شد.');
    }

    public function editFaq()
    {
        $setting = Setting::instance();
        return view('admin.settings.faq', compact('setting'));
    }

    public function updateFaq(Request $request)
    {
        $validated = $request->validate([
            'faq' => ['nullable', 'array'],
            'faq.*.q' => ['nullable', 'string'],
            'faq.*.a' => ['nullable', 'string'],
        ]);

        $setting = Setting::instance();

        $faq = $request->input('faq', []);
        $faq = array_values(array_filter($faq, function($item){
            return isset($item['q']) && isset($item['a']) && trim((string)$item['q']) !== '' && trim((string)$item['a']) !== '';
        }));

        $setting->update([
            'faq_json' => $faq,
        ]);

        return redirect()->route('admin.settings.faq.edit')
            ->with('success', 'سوالات متداول با موفقیت بروزرسانی شد.');
    }

    private function sanitizeHtml(?string $html): ?string
    {
        if ($html === null) return null;
        $allowed = '<a><strong><em><br>';
        $clean = strip_tags($html, $allowed);
        $clean = preg_replace('/on\w+\s*=\s*"[^"]*"/i', '', $clean);
        $clean = preg_replace("/on\w+\s*=\s*'[^']*'/i", '', $clean);
        $clean = preg_replace('/javascript\s*:/i', '', $clean);
        return $clean;
    }

    public function editUploads()
    {
        $setting = Setting::instance();
        return view('admin.settings.uploads', compact('setting'));
    }

    public function updateUploads(Request $request)
    {
        $validated = $request->validate([
            'allowed_mimes' => ['required', 'string'],
            'max_upload_mb' => ['required', 'integer', 'min:1', 'max:100'],
            'auto_close_days' => ['required', 'integer', 'min:1', 'max:60'],
            'max_description_words' => ['required', 'integer', 'min:100', 'max:5000'],
            'allow_user_submission' => ['nullable', 'boolean'],
        ]);

        $setting = Setting::instance();
        $setting->update(array_merge($validated, [
            'allow_user_submission' => $request->boolean('allow_user_submission', false),
        ]));

        return redirect()->route('admin.settings.uploads.edit')
            ->with('success', 'تنظیمات آپلود و محدودیت‌ها با موفقیت بروزرسانی شد.');
    }

    public function editWorkhours()
    {
        $setting = Setting::instance();
        return view('admin.settings.workhours', compact('setting'));
    }

    public function updateWorkhours(Request $request)
    {
        $validated = $request->validate([
            'working_days' => ['required', 'string'],
            'working_start_time' => ['required', 'date_format:H:i'],
            'working_end_time' => ['required', 'date_format:H:i'],
        ]);

        $setting = Setting::instance();
        $setting->update($validated);

        return redirect()->route('admin.settings.workhours.edit')
            ->with('success', 'زمان‌بندی کاری با موفقیت بروزرسانی شد.');
    }

    public function editSecurity()
    {
        $setting = Setting::instance();
        return view('admin.settings.security', compact('setting'));
    }

    public function updateSecurity(Request $request)
    {
        $validated = $request->validate([
            'cooldown_login_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'cooldown_password_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'cooldown_ticket_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'cooldown_message_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'cooldown_attachment_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ]);

        $setting = Setting::instance();
        $setting->update($validated);

        return redirect()->route('admin.settings.security.edit')
            ->with('success', 'تنظیمات امنیتی با موفقیت بروزرسانی شد.');
    }

    public function editRetention()
    {
        $setting = Setting::instance();
        return view('admin.settings.retention', compact('setting'));
    }

    public function updateRetention(Request $request)
    {
        $validated = $request->validate([
            'retention_archive_months' => ['required', 'integer', 'min:0', 'max:120'],
            'retention_delete_months' => ['required', 'integer', 'min:0', 'max:240'],
        ]);

        // Ensure delete is not earlier than archive
        if ((int)$validated['retention_delete_months'] > 0 && (int)$validated['retention_archive_months'] > 0 && (int)$validated['retention_delete_months'] <= (int)$validated['retention_archive_months']) {
            return back()->withErrors(['retention_delete_months' => 'حذف نهایی باید دیرتر از آرشیو انجام شود.'])->withInput();
        }

        $setting = Setting::instance();
        $setting->update($validated);

        return redirect()->route('admin.settings.retention.edit')
            ->with('success', 'تنظیمات نگهداری با موفقیت بروزرسانی شد.');
    }

    public function editEmail()
    {
        $setting = Setting::instance();
        return view('admin.settings.email', compact('setting'));
    }

    public function updateEmail(Request $request)
    {
        $validated = $request->validate([
            'email_forgot_enabled' => ['nullable', 'boolean'],
            'email_forgot_subject' => ['nullable', 'string', 'max:255'],
            'email_forgot_html' => ['nullable', 'string'],
            'email_welcome_enabled' => ['nullable', 'boolean'],
            'email_welcome_subject' => ['nullable', 'string', 'max:255'],
            'email_welcome_html' => ['nullable', 'string'],
            'email_queue_enabled' => ['nullable', 'boolean'],
            'email_rate_limit_count' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'email_rate_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
        ]);
        $setting = Setting::instance();
        $setting->update([
            'email_forgot_enabled' => $request->boolean('email_forgot_enabled'),
            'email_forgot_subject' => $validated['email_forgot_subject'] ?? null,
            'email_forgot_html' => $validated['email_forgot_html'] ?? null,
            'email_welcome_enabled' => $request->boolean('email_welcome_enabled'),
            'email_welcome_subject' => $validated['email_welcome_subject'] ?? null,
            'email_welcome_html' => $validated['email_welcome_html'] ?? null,
            'email_queue_enabled' => $request->boolean('email_queue_enabled'),
            'email_rate_limit_count' => $validated['email_rate_limit_count'] ?? $setting->email_rate_limit_count ?? 30,
            'email_rate_limit_minutes' => $validated['email_rate_limit_minutes'] ?? $setting->email_rate_limit_minutes ?? 10,
        ]);
        return redirect()->route('admin.settings.email.edit')->with('success', 'تنظیمات ایمیل ذخیره شد.');
    }

    public function testEmail(Request $request)
    {
        $request->validate(['to' => ['nullable', 'email']]);
        $to = $request->input('to') ?: (auth()->user()->email ?? null);
        if (!$to) { return back()->withErrors(['to' => 'گیرنده یافت نشد.']); }
        \Mail::raw('این یک ایمیل تست از سامانه تیکت است.', function($m) use ($to){
            $m->to($to)->subject('ایمیل تست سامانه');
        });
        return back()->with('success', 'ایمیل تست ارسال شد به: '.$to);
    }

    public function pingEmail(Request $request)
    {
        $mailer = config('mail.default');
        $host = config("mail.mailers.$mailer.host");
        $port = (int) (config("mail.mailers.$mailer.port") ?? 25);
        $timeout = 5;
        $ok = false; $err = null;
        if ($host && $port) {
            try {
                $fp = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, $timeout);
                if ($fp) { $ok = true; fclose($fp); }
            } catch (\Throwable $e) { $err = $e->getMessage(); }
        } else {
            $err = 'پیکربندی SMTP ناقص است (host/port).';
        }
        if ($ok) return back()->with('success', "ارتباط با SMTP برقرار شد: $host:$port");
        return back()->withErrors(['smtp' => $err ?: 'اتصال برقرار نشد.']);
    }
}