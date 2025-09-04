@extends('layouts.admin')

@section('content')
    <h1 class="text-xl font-bold mb-4">امنیت</h1>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.settings.security.update') }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <h2 class="font-semibold text-gray-900">وقفه اجباری بین درخواست‌ها (Cooldown)</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm mb-1">ورود: هر چند دقیقه یکبار مجاز است؟</label>
                    <x-ui.input type="number" name="cooldown_login_minutes" value="{{ old('cooldown_login_minutes', $setting->cooldown_login_minutes ?? 10) }}" />
                </div>
                <div>
                    <label class="block text-sm mb-1">بازیابی رمز: هر چند دقیقه یکبار مجاز است؟</label>
                    <x-ui.input type="number" name="cooldown_password_minutes" value="{{ old('cooldown_password_minutes', $setting->cooldown_password_minutes ?? 10) }}" />
                </div>
                <div>
                    <label class="block text-sm mb-1">ایجاد تیکت: فاصله زمانی (دقیقه)</label>
                    <x-ui.input type="number" name="cooldown_ticket_minutes" value="{{ old('cooldown_ticket_minutes', $setting->cooldown_ticket_minutes ?? 20) }}" />
                </div>
                <div>
                    <label class="block text-sm mb-1">ارسال پیام/پاسخ: فاصله زمانی (دقیقه)</label>
                    <x-ui.input type="number" name="cooldown_message_minutes" value="{{ old('cooldown_message_minutes', $setting->cooldown_message_minutes ?? 15) }}" />
                </div>
                <div>
                    <label class="block text-sm mb-1">دانلود ضمیمه: فاصله زمانی (دقیقه)</label>
                    <x-ui.input type="number" name="cooldown_attachment_minutes" value="{{ old('cooldown_attachment_minutes', $setting->cooldown_attachment_minutes ?? 2) }}" />
                </div>
            </div>

            <div class="flex justify-end">
                <x-ui.button>ذخیره</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection


