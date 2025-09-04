@extends('layouts.admin')

@section('content')
    <h1 class="text-xl font-bold mb-4">آپلود و محدودیت‌ها</h1>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.settings.uploads.update') }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm mb-1">انواع فایل مجاز (mimes)</label>
                    <x-ui.input type="text" name="allowed_mimes" value="{{ old('allowed_mimes', $setting->allowed_mimes) }}" />
                    @error('allowed_mimes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-500 mt-1">مثال: jpg,png,pdf,docx</p>
                </div>
                <div>
                    <label class="block text-sm mb-1">حداکثر حجم آپلود (MB)</label>
                    <x-ui.input type="number" name="max_upload_mb" value="{{ old('max_upload_mb', $setting->max_upload_mb) }}" />
                    @error('max_upload_mb')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <h2 class="font-semibold text-gray-900">تنظیمات تیکت</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm mb-1">بستن خودکار تیکت پس از (روز)</label>
                    <x-ui.input type="number" min="1" name="auto_close_days" value="{{ old('auto_close_days', $setting->auto_close_days ?? 5) }}" />
                    @error('auto_close_days')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm mb-1">حداکثر تعداد کلمات توضیحات تیکت</label>
                    <x-ui.input type="number" name="max_description_words" value="{{ old('max_description_words', $setting->max_description_words ?? 700) }}" />
                    @error('max_description_words')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <h2 class="font-semibold text-gray-900">سیاست‌ها</h2>
            <div class="grid grid-cols-1 gap-2">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="allow_user_submission" value="1" @checked(old('allow_user_submission', $setting->allow_user_submission ?? true))>
                    <span>اجازه ارسال پیام توسط کاربران</span>
                </label>
            </div>

            <div class="flex justify-end">
                <x-ui.button>ذخیره</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection


