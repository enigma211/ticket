<x-guest-layout>
    <div class="relative overflow-hidden min-h-[80vh] flex items-center justify-center">
        <div class="pointer-events-none absolute -left-24 -top-24 w-72 h-72 bg-blue-50 rounded-full blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 top-10 w-72 h-72 bg-indigo-100 rounded-full blur-3xl"></div>

        <div class="relative w-full max-w-md mx-4">
            <div class="mb-5 text-center">
                <div class="inline-flex items-center gap-2 text-xs px-3 py-1 rounded-full bg-blue-50 text-blue-700">ورود به سامانه</div>
                <p class="mt-2 text-sm text-gray-600">برای ادامه وارد حساب خود شوید</p>
            </div>

            <div class="rounded-2xl border bg-white/80 backdrop-blur p-6 shadow-sm hover:shadow transition">
                @if (session('status'))
                    <x-ui.alert type="success" class="mb-4">{{ session('status') }}</x-ui.alert>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">ایمیل</label>
                        <x-ui.input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus />
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">رمز عبور</label>
                        <x-ui.input id="password" type="password" name="password" required />
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input id="remember" type="checkbox" name="remember" class="rounded border-gray-300 text-primary focus:ring-primary">
                        <label for="remember" class="mr-2 text-sm text-gray-700">مرا به خاطر بسپار</label>
                    </div>

                    <div class="flex items-center justify-between">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">رمز عبور را فراموش کرده‌اید؟</a>
                        @endif
                        <x-ui.button type="submit">ورود</x-ui.button>
                    </div>

                    <div class="text-center space-y-2 pt-2">
                        <span class="text-sm text-gray-600">حساب کاربری ندارید؟ </span>
                        <a href="{{ route('register') }}" class="text-sm text-primary hover:underline">ثبت‌نام کنید</a>
                        <div>
                            <a href="{{ request()->getBaseUrl() }}/" class="text-sm text-gray-700 hover:underline">بازگشت به صفحه نخست</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>