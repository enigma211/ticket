@extends('layouts.admin')

@section('content')
    <h1 class="text-xl font-bold mb-4">ایجاد دپارتمان</h1>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.departments.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm mb-1">نام</label>
                <x-ui.input type="text" name="name" value="{{ old('name') }}" required />
                @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm mb-1">توضیحات</label>
                <x-ui.textarea name="description" rows="4">{{ old('description') }}</x-ui.textarea>
            </div>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="active" value="1" checked>
                <span>فعال</span>
            </label>
            <div class="flex items-center gap-3 justify-end">
                <a href="{{ route('admin.departments.index') }}" class="text-gray-600">انصراف</a>
                <x-ui.button>ذخیره</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection


