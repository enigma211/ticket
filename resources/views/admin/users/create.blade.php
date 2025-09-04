@extends('layouts.admin')

@section('content')
    <h1 class="text-xl font-bold mb-4">ایجاد کاربر</h1>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">نام</label>
                    <x-ui.input type="text" name="first_name" value="{{ old('first_name') }}" />
                    @error('first_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">نام خانوادگی</label>
                    <x-ui.input type="text" name="last_name" value="{{ old('last_name') }}" />
                    @error('last_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">ایمیل</label>
                    <x-ui.input type="email" name="email" value="{{ old('email') }}" required />
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">رمز عبور</label>
                    <x-ui.input type="password" name="password" required />
                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">کد ملی</label>
                    <x-ui.input type="text" name="national_id" value="{{ old('national_id') }}" />
                    @error('national_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">موبایل</label>
                    <x-ui.input type="text" name="mobile" value="{{ old('mobile') }}" />
                    @error('mobile')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center gap-6">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_superadmin" value="1" @checked(old('is_superadmin'))>
                    <span>سوپرادمین</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_agent" value="1" @checked(old('is_agent'))>
                    <span>پشتیبان</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="text-gray-600">انصراف</a>
                <x-ui.button>ایجاد</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection


