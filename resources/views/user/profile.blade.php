@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-6">
        <aside class="col-span-12 md:col-span-3">
            <x-ui.card>
                <nav class="px-2 py-3 space-y-1">
                    <a href="{{ route('tickets.index') }}" class="block px-3 py-2 rounded-lg flex items-center gap-2 transition-colors @if(request()->routeIs('tickets.*')) bg-gray-50 text-primary font-medium border border-gray-200 @else text-gray-700 hover:bg-gray-50 @endif">
                        <svg class="w-4 h-4 @if(request()->routeIs('tickets.*')) text-primary @else text-gray-400 group-hover:text-gray-500 @endif" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
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
            <x-ui.card>
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-lg font-semibold">پروفایل من</h1>
                </div>
                @error('profile')<div class="mb-3 text-sm text-red-600">{{ $message }}</div>@enderror
                @php $locked = $user->profile_locked_until && now()->lt($user->profile_locked_until); @endphp
                @if($locked)
                    <div class="mb-3 text-sm text-amber-700 bg-amber-50 p-3 rounded">امکان ویرایش تا تاریخ {{ jdate($user->profile_locked_until)->format('Y/m/d H:i') }} غیر فعال است.</div>
                @endif

                
                <form method="POST" action="{{ route('user.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-sm mb-1">ایمیل</label>
                        <x-ui.input type="email" name="email" value="{{ old('email', $user->email) }}" :disabled="$locked" />
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm mb-1">نام</label>
                            <x-ui.input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" :disabled="$locked" />
                            @error('first_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm mb-1">نام خانوادگی</label>
                            <x-ui.input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" :disabled="$locked" />
                            @error('last_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">کد ملی</label>
                        <x-ui.input type="text" name="national_id" value="{{ old('national_id', $user->national_id) }}" :disabled="$locked" />
                        @error('national_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm mb-1">شماره موبایل</label>
                        <x-ui.input type="text" name="mobile" value="{{ old('mobile', $user->mobile) }}" :disabled="$locked" />
                        @error('mobile')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm mb-1">رمز عبور جدید</label>
                            <x-ui.input type="password" name="password" :disabled="$locked" />
                            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm mb-1">تکرار رمز عبور</label>
                            <x-ui.input type="password" name="password_confirmation" :disabled="$locked" />
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <x-ui.button :disabled="$locked">ذخیره</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </section>
    </div>
@endsection


