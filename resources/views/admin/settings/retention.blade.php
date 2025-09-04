@extends('layouts.admin')

@section('content')
    <h1 class="text-xl font-bold mb-4">نگهداری و آرشیو</h1>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.settings.retention.update') }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">آرشیو پس از (ماه)</label>
                    <x-ui.input type="number" name="retention_archive_months" value="{{ old('retention_archive_months', $setting->retention_archive_months ?? 6) }}" />
                    <p class="text-xs text-gray-500 mt-1">0 یعنی غیرفعال</p>
                    @error('retention_archive_months')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">حذف نهایی پس از (ماه)</label>
                    <x-ui.input type="number" name="retention_delete_months" value="{{ old('retention_delete_months', $setting->retention_delete_months ?? 24) }}" />
                    <p class="text-xs text-gray-500 mt-1">باید بزرگ‌تر از مقدار آرشیو باشد؛ 0 یعنی غیرفعال</p>
                    @error('retention_delete_months')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex justify-end">
                <x-ui.button>ذخیره</x-ui.button>
                <a href="{{ route('admin.settings.retention.run') }}" class="ml-3 px-3 py-2 rounded bg-gray-900 text-white">اجرای الان</a>
            </div>
        </form>
    </x-ui.card>
@endsection


