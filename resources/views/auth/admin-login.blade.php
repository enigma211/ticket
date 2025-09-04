@extends('layouts.app')

@section('content')
    <div class="max-w-md mx-auto mt-10">
        <x-ui.card>
            <h1 class="text-xl font-bold mb-4">ورود کارکنان</h1>
            <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm mb-1">ایمیل</label>
                    <x-ui.input type="email" name="email" value="{{ old('email') }}" required autofocus />
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">رمز عبور</label>
                    <x-ui.input type="password" name="password" required />
                </div>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="remember" value="1" />
                    <span class="text-sm">مرا به خاطر بسپار</span>
                </label>
                <div class="flex justify-end">
                    <x-ui.button type="submit">ورود</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection


