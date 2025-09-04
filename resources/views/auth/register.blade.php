<x-guest-layout>
    <div class="relative overflow-hidden min-h-[80vh] flex items-center justify-center">
        <div class="pointer-events-none absolute -left-24 -top-24 w-72 h-72 bg-blue-50 rounded-full blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 top-10 w-72 h-72 bg-indigo-100 rounded-full blur-3xl"></div>

        <div class="relative w-full max-w-md mx-4">
            <div class="mb-5 text-center">
                <div class="inline-flex items-center gap-2 text-xs px-3 py-1 rounded-full bg-blue-50 text-blue-700">ایجاد حساب کاربری</div>
                <p class="mt-2 text-sm text-gray-600">فرم زیر را تکمیل کنید</p>
            </div>

            <div class="rounded-2xl border bg-white/80 backdrop-blur p-6 shadow-sm hover:shadow transition">
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">نام</label>
                        <x-ui.input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required autofocus />
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">نام خانوادگی</label>
                        <x-ui.input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required />
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">ایمیل</label>
                        <x-ui.input id="email" type="email" name="email" value="{{ old('email') }}" required />
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- National ID -->
                    <div>
                        <label for="national_id" class="block text-sm font-medium text-gray-700 mb-1">کد ملی</label>
                        <x-ui.input id="national_id" type="text" name="national_id" value="{{ old('national_id') }}" maxlength="10" inputmode="numeric" pattern="\d{10}" dir="ltr" />
                        @error('national_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Mobile -->
                    <div>
                        <label for="mobile" class="block text-sm font-medium text-gray-700 mb-1">شماره موبایل</label>
                        <x-ui.input id="mobile" type="tel" name="mobile" value="{{ old('mobile') }}" maxlength="11" inputmode="numeric" pattern="09\d{9}" dir="ltr" placeholder="09" />
                        @error('mobile')
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

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">تأیید رمز عبور</label>
                        <x-ui.input id="password_confirmation" type="password" name="password_confirmation" required />
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('login') }}" class="text-sm text-primary hover:underline">قبلاً ثبت‌نام کرده‌اید؟</a>
                        <x-ui.button type="submit">ثبت‌نام</x-ui.button>
                    </div>
                    <div class="mt-2 text-center">
                        <a href="{{ request()->getBaseUrl() }}/" class="text-sm text-gray-700 hover:underline">بازگشت به صفحه نخست</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // فقط اعداد را در فیلد کد ملی قبول کن
        document.getElementById('national_id').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // فقط اعداد را در فیلد موبایل قبول کن
        document.getElementById('mobile').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</x-guest-layout>