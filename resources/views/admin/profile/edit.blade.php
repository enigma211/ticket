@extends('layouts.admin')

@section('content')
    <h1 class="text-xl font-bold mb-4">پروفایل من</h1>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">نام</label>
                    <x-ui.input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" />
                    @error('first_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">نام خانوادگی</label>
                    <x-ui.input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" />
                    @error('last_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">ایمیل</label>
                    <x-ui.input type="email" value="{{ $user->email }}" disabled />
                </div>
                <div>
                    <label class="block text-sm mb-1">کد ملی</label>
                    <x-ui.input type="text" name="national_id" value="{{ old('national_id', $user->national_id) }}" />
                    @error('national_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">موبایل</label>
                    <x-ui.input type="text" name="mobile" value="{{ old('mobile', $user->mobile) }}" />
                    @error('mobile')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">رمز عبور جدید</label>
                    <x-ui.input type="password" name="password" />
                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">تایید رمز عبور</label>
                    <x-ui.input type="password" name="password_confirmation" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.dashboard') }}" class="text-gray-600">بازگشت</a>
                <x-ui.button>ذخیره</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection


