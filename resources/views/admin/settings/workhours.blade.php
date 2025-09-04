@extends('layouts.admin')

@section('content')
    <h1 class="text-xl font-bold mb-4">زمان‌بندی کاری</h1>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.settings.workhours.update') }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm mb-1">روزهای کاری (با کاما جدا کنید)</label>
                    <x-ui.input type="text" name="working_days" value="{{ old('working_days', $setting->working_days ?? 'sat,sun,mon,tue,wed,thu') }}" />
                    <p class="text-xs text-gray-500 mt-1">مثال: sat,sun,mon,tue,wed,thu</p>
                    @error('working_days')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">ساعت شروع</label>
                    <x-ui.input type="time" name="working_start_time" value="{{ old('working_start_time', $setting->working_start_time ?? '09:00') }}" />
                    @error('working_start_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">ساعت پایان</label>
                    <x-ui.input type="time" name="working_end_time" value="{{ old('working_end_time', $setting->working_end_time ?? '17:00') }}" />
                    @error('working_end_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex justify-end">
                <x-ui.button>ذخیره</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection


