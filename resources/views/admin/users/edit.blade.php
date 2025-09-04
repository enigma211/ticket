@extends('layouts.admin')

@section('content')
    <h1 class="text-xl font-bold mb-4">ویرایش کاربر</h1>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">نام</label>
                    <x-ui.input type="text" name="first_name" value="{{ old('first_name', $firstPrefill) }}" />
                    @error('first_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">نام خانوادگی</label>
                    <x-ui.input type="text" name="last_name" value="{{ old('last_name', $lastPrefill) }}" />
                    @error('last_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">ایمیل</label>
                    <x-ui.input type="email" value="{{ $user->email }}" disabled />
                </div>
                <div>
                    <label class="block text-sm mb-1">کد ملی</label>
                    <x-ui.input type="text" name="national_id" value="{{ old('national_id', $user->national_id) }}" />
                    @error('national_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">موبایل</label>
                    <x-ui.input type="text" name="mobile" value="{{ old('mobile', $user->mobile) }}" />
                    @error('mobile')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @if(auth()->user()->is_superadmin)
                <div class="flex items-center gap-6">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="is_superadmin" value="1" @checked(old('is_superadmin', $user->is_superadmin))>
                        <span>سوپرادمین</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="is_agent" value="1" @checked(old('is_agent', $user->is_agent))>
                        <span>پشتیبان</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="is_spammer" value="1" @checked(old('is_spammer', $user->is_spammer))>
                        <span>مزاحم</span>
                    </label>
                </div>
            @endif

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

            @if(auth()->user()->is_superadmin && ($user->is_agent || $user->is_superadmin))
                <div>
                    <label class="block text-sm mb-2">دپارتمان‌های مجاز</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        @foreach(\App\Models\Department::orderBy('name')->get() as $dep)
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="department_ids[]" value="{{ $dep->id }}" @checked(in_array($dep->id, old('department_ids', $user->departments()->pluck('departments.id')->toArray())))>
                                <span>{{ $dep->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="text-gray-600">انصراف</a>
                <x-ui.button>ذخیره</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection
