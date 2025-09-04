@extends('layouts.admin')

@section('content')
    <h1 class="text-xl font-bold mb-4">تنظیمات ایمیل</h1>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.settings.email.update') }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <h2 class="font-semibold text-gray-900">فراموشی رمز عبور</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="email_forgot_enabled" value="1" @checked(old('email_forgot_enabled', $setting->email_forgot_enabled))>
                    <span>ارسال ایمیل فراموشی رمز فعال باشد</span>
                </label>
                <div>
                    <label class="block text-sm mb-1">عنوان ایمیل</label>
                    <x-ui.input type="text" name="email_forgot_subject" value="{{ old('email_forgot_subject', $setting->email_forgot_subject ?? 'بازیابی رمز عبور') }}" />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm mb-1">متن HTML ایمیل</label>
                    <x-ui.textarea name="email_forgot_html" rows="8">{{ old('email_forgot_html', $setting->email_forgot_html ?? '<p>برای بازنشانی رمز عبور روی لینک زیر کلیک کنید:</p><p><a href="{reset_url}">بازنشانی رمز عبور</a></p>') }}</x-ui.textarea>
                    <p class="text-xs text-gray-500 mt-1">متغیرهای قابل استفاده: {{ '{reset_url}', '{user_name}' }}</p>
                </div>
            </div>

            <h2 class="font-semibold text-gray-900">خوش آمدگویی</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="email_welcome_enabled" value="1" @checked(old('email_welcome_enabled', $setting->email_welcome_enabled))>
                    <span>ارسال ایمیل خوش آمدگویی پس از ثبت‌نام</span>
                </label>
                <div>
                    <label class="block text-sm mb-1">عنوان ایمیل</label>
                    <x-ui.input type="text" name="email_welcome_subject" value="{{ old('email_welcome_subject', $setting->email_welcome_subject ?? 'خوش آمدید') }}" />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm mb-1">متن HTML ایمیل</label>
                    <x-ui.textarea name="email_welcome_html" rows="8">{{ old('email_welcome_html', $setting->email_welcome_html ?? '<h3>خوش آمدید، {user_name}</h3><p>ثبت‌نام شما با موفقیت انجام شد.</p>') }}</x-ui.textarea>
                    <p class="text-xs text-gray-500 mt-1">متغیرهای قابل استفاده: {{ '{user_name}' }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <x-ui.button>ذخیره</x-ui.button>
                <div></div>
            </div>
        </form>
        <div class="mt-8">
            <h2 class="font-semibold text-gray-900 mb-3">تحویل و سلامت</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <div class="text-sm text-gray-600">Mailer پیش‌فرض: <span class="font-medium">{{ config('mail.default') }}</span></div>
                    <div class="text-sm text-gray-600">Host: <span class="font-medium">{{ config('mail.mailers.'.config('mail.default').'.host') ?? '-' }}</span> • Port: <span class="font-medium">{{ config('mail.mailers.'.config('mail.default').'.port') ?? '-' }}</span></div>
                    <form method="POST" action="{{ route('admin.settings.email.ping') }}" class="mt-2">
                        @csrf
                        <x-ui.button type="submit" class="!bg-gray-700">تست اتصال SMTP</x-ui.button>
                    </form>
                    <div class="pt-2">
                        <a href="{{ route('admin.settings.email.report') }}" class="text-sm text-blue-700 hover:underline">مشاهده گزارش ایمیل‌ها</a>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.settings.email.test') }}" class="space-y-2">
                    @csrf
                    <label class="block text-sm mb-1">ارسال تست به آدرس دلخواه</label>
                    <x-ui.input type="email" name="to" placeholder="example@domain.com" />
                    <x-ui.button type="submit" class="!bg-gray-700">ارسال ایمیل تست</x-ui.button>
                </form>
            </div>
        </div>

        <div class="mt-6">
            <form method="POST" action="{{ route('admin.settings.email.update') }}" class="flex flex-wrap items-center gap-3">
                @csrf
                @method('PATCH')
                <input type="hidden" name="email_queue_enabled" value="0">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="email_queue_enabled" value="1" @checked((bool)settings('email_queue_enabled'))>
                    <span class="text-sm">ارسال ایمیل‌ها با صف (Queue)</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <span class="text-sm">نرخ ارسال:</span>
                    <x-ui.input type="number" name="email_rate_limit_count" value="{{ (int)settings('email_rate_limit_count', 30) }}" class="w-24" />
                    <span class="text-sm">ایمیل /</span>
                    <x-ui.input type="number" name="email_rate_limit_minutes" value="{{ (int)settings('email_rate_limit_minutes', 10) }}" class="w-24" />
                    <span class="text-sm">دقیقه</span>
                </label>
                <x-ui.button type="submit">ذخیره تحویل</x-ui.button>
            </form>
        </div>
    </x-ui.card>
@endsection


