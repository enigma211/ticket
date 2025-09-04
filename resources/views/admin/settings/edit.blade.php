@extends('layouts.admin')

@section('content')
    <h1 class="text-xl font-bold mb-4">تنظیمات سامانه</h1>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6" id="settings_form">
            @csrf
            @method('PATCH')

            <h2 class="font-semibold text-gray-900">اطلاعات عمومی</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">نام سایت</label>
                    <x-ui.input type="text" name="site_name" value="{{ old('site_name', $setting->site_name) }}" />
                    @error('site_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">لوگو</label>
                    @if($setting->logo_path)
                        <div class="mb-2">
                            <p class="text-xs text-gray-500 mb-1">لوگوی فعلی:</p>
                            <img src="{{ asset('storage/' . $setting->logo_path) }}" alt="لوگوی فعلی" class="w-16 h-16 object-contain border rounded" />
                        </div>
                    @endif
                    <input type="file" name="logo" accept="image/*" />
                    <p class="text-xs text-gray-500 mt-1">فرمت‌های مجاز: JPG, PNG, GIF. حداکثر حجم: 2MB</p>
                    @error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <h2 class="font-semibold text-gray-900">صفحه نخست</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm mb-1">عنوان صفحه اصلی</label>
                    <x-ui.input type="text" name="home_title" value="{{ old('home_title', $setting->home_title) }}" />
                    @error('home_title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm mb-1">زیرعنوان</label>
                    <x-ui.textarea name="home_subtitle">{{ old('home_subtitle', $setting->home_subtitle) }}</x-ui.textarea>
                    @error('home_subtitle')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm mb-1">متن پابرگ (HTML مجاز است)</label>
                    <x-ui.textarea name="footer_text" rows="4">{{ old('footer_text', $setting->footer_text) }}</x-ui.textarea>
                    <p class="text-xs text-gray-500 mt-1">می‌توانید از برچسب‌های HTML مانند a, strong, em, br استفاده کنید.</p>
                    @error('footer_text')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <h2 class="font-semibold text-gray-900 mt-6">زمان و ناحیه زمانی</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">منطقه زمانی (GMT Offset)</label>
                    @php
                        // Determine current offset (HH:MM): if stored is offset, use it; else derive from IANA
                        $rawTz = $setting->timezone ?: 'Asia/Tehran';
                        if (preg_match('/^[+-]\d{2}:\d{2}$/', (string)$rawTz)) {
                            $storedOffset = $rawTz;
                        } else {
                            try {
                                $dtz = new \DateTimeZone($rawTz);
                                $offsetSec = (new \DateTime('now', $dtz))->getOffset();
                                $sign = $offsetSec >= 0 ? '+' : '-';
                                $abs = abs($offsetSec);
                                $h = str_pad((string)floor($abs/3600), 2, '0', STR_PAD_LEFT);
                                $m = str_pad((string)floor(($abs % 3600)/60), 2, '0', STR_PAD_LEFT);
                                $storedOffset = $sign.$h.':'.$m;
                            } catch (\Throwable $e) { $storedOffset = '+03:30'; }
                        }
                        $currentTz = old('timezone', $storedOffset);
                        $options = [];
                        for ($h = -12; $h <= 14; $h++) {
                            foreach ([0, 30] as $m) {
                                $sign = $h >= 0 ? '+' : '-';
                                $absH = str_pad((string)abs($h), 2, '0', STR_PAD_LEFT);
                                $mm = str_pad((string)$m, 2, '0', STR_PAD_LEFT);
                                $options[] = sprintf('%s%s:%s', $sign, $absH, $mm);
                            }
                        }
                        // Add common 45-min offsets
                        foreach (['+05:45', '+08:45'] as $spec) { if (!in_array($spec, $options, true)) { $options[] = $spec; } }
                        sort($options);
                    @endphp
                    <select name="timezone" class="w-full rounded border-gray-300 focus:border-primary focus:ring-primary">
                        <option value="UTC" @selected($currentTz === 'UTC')>UTC (GMT±00:00)</option>
                        @foreach($options as $off)
                            <option value="{{ $off }}" @selected($currentTz === $off)>GMT{{ $off }}</option>
                        @endforeach
                    </select>
                    @error('timezone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-500 mt-1">برای ایران: GMT+03:30</p>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <x-ui.button>ذخیره</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    
@endsection
