@extends('layouts.app')

@section('content')
    @php
        $daysStr = (string) settings('working_days', 'sat,sun,mon,tue,wed,thu');
        $daysArr = array_filter(array_map(fn($d) => strtolower(trim($d)), explode(',', $daysStr)));
        $start = (string) settings('working_start_time', '09:00');
        $end = (string) settings('working_end_time', '17:00');
        $now = now();
        $dayKey = strtolower($now->format('D'));
        $withinDay = in_array($dayKey, $daysArr);
        $timeNow = $now->format('H:i');
        $withinTime = $timeNow >= $start && $timeNow <= $end;
        $canBySchedule = $withinDay && $withinTime;
        $allowSubmission = settings('allow_user_submission', true) && $canBySchedule;
    @endphp

    @php $isAdminView = auth()->check() && (auth()->user()->is_agent || auth()->user()->is_superadmin) && request()->routeIs('admin.*'); @endphp

    @if(!$isAdminView)
    <div class="grid grid-cols-12 gap-6">
        <aside class="col-span-12 md:col-span-3">
            <x-ui.card>
                <nav class="px-2 py-3 space-y-1">
                    <a href="{{ route('tickets.index') }}" class="block px-3 py-2 rounded-lg flex items-center gap-2 transition-colors @if(request()->routeIs('tickets.index')) bg-gray-50 text-primary font-medium border border-gray-200 @else text-gray-700 hover:bg-gray-50 @endif">
                        <svg class="w-4 h-4 @if(request()->routeIs('tickets.index')) text-primary @else text-gray-400 group-hover:text-gray-500 @endif" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
                        <span class="text-sm">تیکت‌ها</span>
                    </a>
                    <a href="{{ route('user.profile.edit') }}" class="block px-3 py-2 rounded-lg flex items-center gap-2 transition-colors @if(request()->routeIs('user.profile.*')) bg-gray-50 text-primary font-medium border border-gray-200 @else text-gray-700 hover:bg-gray-50 @endif">
                        <svg class="w-4 h-4 @if(request()->routeIs('user.profile.*')) text-primary @else text-gray-400 group-hover:text-gray-500 @endif" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span class="text-sm">پروفایل من</span>
                    </a>
                </nav>
            </x-ui.card>
        </aside>
        <section class="col-span-12 md:col-span-9">
    @endif

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">ایجاد تیکت جدید</h1>
            @php $backRoute = $isAdminView ? route('admin.tickets.index') : route('tickets.index'); @endphp
            <a href="{{ $backRoute }}" class="text-primary hover:underline">بازگشت به لیست</a>
        </div>

        @if (!$allowSubmission)
            <x-ui.alert type="warning" class="mb-6">در حال حاضر به علت زمان‌بندی و تنظیمات سیستم، قادر به ارسال پیام نیستید. لطفاً بعداً مراجعه نمایید.</x-ui.alert>
        @endif

        @if ($errors->has('error') || $errors->has('general'))
            <x-ui.alert type="error" class="mb-6">{{ $errors->first('error') ?: $errors->first('general') }}</x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @if (!$allowSubmission)
                    <fieldset disabled>
                @endif

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">عنوان</label>
                    <x-ui.input id="title" name="title" type="text" value="{{ old('title') }}" required />
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">توضیحات</label>
                    <textarea id="description" name="description" rows="6" required class="w-full rounded border-gray-300 focus:border-primary focus:ring-primary">{{ old('description') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">حداکثر {{ (int) settings('max_description_words', 700) }} کلمه</p>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Department -->
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">دپارتمان <span class="text-red-600">*</span></label>
                    <select id="department_id" name="department_id" class="w-full rounded border-gray-300 focus:border-primary focus:ring-primary" required aria-required="true" oninvalid="this.setCustomValidity('انتخاب دپارتمان الزامی است')" oninput="this.setCustomValidity('')">
                        <option value="" disabled @selected(!old('department_id'))>انتخاب کنید...</option>
                        @foreach(\App\Models\Department::where('active', true)->orderBy('name')->get() as $dep)
                            <option value="{{ $dep->id }}" @selected(old('department_id') == $dep->id)>{{ $dep->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Attachments -->
                <div>
                    <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">پیوست‌ها (اختیاری)</label>
                    <input id="attachments" name="attachments[]" type="file" multiple accept="{{ '.' . str_replace(',', ',.', (string) settings('allowed_mimes', 'jpg,jpeg,png,pdf')) }}"
                           class="w-full rounded border-gray-300 focus:border-primary focus:ring-primary" />
                    <p id="attachments_hint" class="mt-1 text-xs text-gray-500">حداکثر {{ (int) settings('max_upload_mb', 5) }} مگابایت برای هر فایل • انواع مجاز: {{ (string) settings('allowed_mimes', 'jpg,jpeg,png,pdf') }}</p>
                    <p id="attachments_error" class="mt-1 text-sm text-red-600"></p>
                    @error('attachments.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3">
                    @php $cancelRoute = $isAdminView ? route('admin.tickets.index') : route('tickets.index'); @endphp
                    <a href="{{ $cancelRoute }}" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                        انصراف
                    </a>
                    <x-ui.button type="submit">ایجاد تیکت</x-ui.button>
                </div>
                @if (!$allowSubmission)
                    </fieldset>
                @endif
            </form>
        </x-ui.card>

    @if(!$isAdminView)
        </section>
    </div>
    @endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('attachments');
    const err = document.getElementById('attachments_error');
    const maxMb = {{ (int) settings('max_upload_mb', 5) }};
    if (input && err) {
        input.addEventListener('change', function () {
            err.textContent = '';
            const maxBytes = maxMb * 1024 * 1024;
            for (const file of this.files) {
                if (file.size > maxBytes) {
                    err.textContent = `حجم فایل "${file.name}" بیش از ${maxMb} مگابایت است.`;
                    this.value = '';
                    break;
                }
            }
        });
    }
});
</script>
@endpush