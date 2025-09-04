<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>سامانه تیکت</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex flex-col overflow-x-hidden">
    <!-- Header -->
    <header class="border-b bg-white/80 backdrop-blur">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if(settings('logo_path'))
                    <img src="{{ asset('storage/' . settings('logo_path')) }}" alt="لوگو" class="w-10 h-10 rounded object-contain" />
                @else
                    <div class="w-10 h-10 rounded bg-primary flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                        </svg>
                    </div>
                @endif
                <div class="font-extrabold text-gray-900">{{ settings('site_name') ?? 'سامانه تیکت' }}</div>
            </div>
            <nav class="flex items-center gap-3 text-sm">
                @auth
                    @if(auth()->user()->is_agent || auth()->user()->is_superadmin)
                        <a href="{{ route('admin.tickets.index') }}" class="px-3 py-1.5 rounded bg-primary text-white hover:opacity-90">پنل تیکت‌ها</a>
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded border border-gray-300 hover:bg-gray-50">داشبورد</a>
                    @else
                        <a href="{{ route('tickets.index') }}" class="px-3 py-1.5 rounded bg-primary text-white hover:opacity-90">پنل تیکت‌ها</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button class="px-3 py-1.5 rounded border border-gray-300 hover:bg-gray-50">خروج</button>
                    </form>
                @else
                    <a href="{{ request()->getBaseUrl() }}/login" class="px-3 py-1.5 rounded border border-gray-300 hover:bg-gray-50">ورود</a>
                    <a href="{{ request()->getBaseUrl() }}/register" class="px-3 py-1.5 rounded bg-primary text-white hover:opacity-90">ثبت‌نام</a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-b from-white to-gray-50">
        <!-- Decorative blobs -->
        <div class="pointer-events-none absolute -left-32 -top-32 w-80 h-80 bg-primary/10 rounded-full blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 top-20 w-72 h-72 bg-indigo-200/40 rounded-full blur-3xl"></div>
        <div class="max-w-6xl mx-auto px-4 py-14">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
                <!-- Text column -->
                <div>
                    <span class="inline-flex items-center gap-2 text-xs px-3 py-1 rounded-full bg-blue-50 text-blue-700">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20l9-5-9-5-9 5 9 5z"/><path d="M12 12l9-5-9-5-9 5 9 5z"/></svg>
                        پشتیبانی سریع و متمرکز
                    </span>
                    <h1 class="mt-4 text-3xl md:text-4xl font-extrabold leading-relaxed text-gray-900">
                        {{ settings('home_title') ?? 'سامانه پشتیبانی و تیکتینگ' }}
                    </h1>
                    <p class="text-gray-600 mt-3 leading-8">
                        {{ settings('home_subtitle') ?? 'ایجاد تیکت، ارسال پیام و پیگیری وضعیت درخواست‌ها، ساده و یکپارچه در یک پنل حرفه‌ای.' }}
                    </p>
                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        @auth
                            @if(auth()->user()->is_agent || auth()->user()->is_superadmin)
                                <a href="{{ route('admin.tickets.index') }}" class="px-5 py-3 rounded-lg bg-primary text-white hover:opacity-90">ورود به پنل</a>
                                <a href="{{ route('admin.dashboard') }}" class="px-5 py-3 rounded-lg border border-gray-300 text-gray-800 hover:bg-gray-50">داشبورد</a>
                            @else
                                <a href="{{ route('tickets.index') }}" class="px-5 py-3 rounded-lg bg-primary text-white hover:opacity-90">ورود به پنل</a>
                            @endif
                        @else
                            <a href="{{ request()->getBaseUrl() }}/login" class="px-5 py-3 rounded-lg bg-primary text-white hover:opacity-90">ورود به سامانه</a>
                            <a href="{{ request()->getBaseUrl() }}/register" class="px-5 py-3 rounded-lg border border-gray-300 text-gray-800 hover:bg-gray-50">ایجاد حساب</a>
                        @endauth
                    </div>
                    <div class="mt-6 flex flex-wrap items-center gap-3 text-xs text-gray-700">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border">
                            <svg class="w-4 h-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            پاسخگویی سریع
                        </span>
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border">
                            <svg class="w-4 h-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M7 8h10"/></svg>
                            پیگیری لحظه‌ای
                        </span>
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border">
                            <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18"/><path d="M3 12h18"/><path d="M3 17h18"/></svg>
                            ضمیمه امن فایل
                        </span>
                    </div>
                </div>

                <!-- Visual preview column -->
                <div class="relative md:justify-self-end hidden md:block">
                    <div class="absolute -top-6 -left-6 w-24 h-24 rounded-full bg-indigo-100 blur-2xl"></div>
                    <div class="absolute -bottom-6 -right-6 w-28 h-28 rounded-full bg-blue-100 blur-2xl"></div>
                    <!-- Card stack -->
                    <div class="relative">
                        <div class="absolute -rotate-6 -top-3 -left-3 w-80 h-40 rounded-xl bg-white/70 border shadow-sm"></div>
                        <div class="absolute rotate-6 -bottom-4 -right-3 w-80 h-40 rounded-xl bg-white/70 border shadow-sm"></div>
                        <div class="relative w-[22rem] rounded-2xl border bg-white shadow-lg overflow-hidden">
                            <div class="px-5 py-4 border-b flex items-center justify-between">
                                <div class="font-semibold text-gray-900">نمونه تیکت</div>
                                <span class="text-xs px-2 py-1 rounded bg-blue-50 text-blue-700">باز</span>
                            </div>
                            <div class="p-5 space-y-3 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">کد پیگیری</span>
                                    <span class="font-mono text-gray-900">123456</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">آخرین بروزرسانی</span>
                                    <span class="text-gray-900">لحظاتی پیش</span>
                                </div>
                                <div class="mt-2">
                                    <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full w-2/3 bg-gradient-to-l from-blue-500 to-indigo-500"></div>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500">در حال پیگیری</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    

    <!-- How it works -->
    <section class="py-12 bg-white relative overflow-hidden">
        <div class="pointer-events-none absolute left-10 top-0 w-40 h-40 bg-blue-50 rounded-full blur-3xl"></div>
        <div class="pointer-events-none absolute right-10 bottom-0 w-32 h-32 bg-indigo-50 rounded-full blur-3xl"></div>
        <div class="max-w-6xl mx-auto px-4 relative">
            <h2 class="text-xl font-bold text-gray-900 mb-8">نحوه شروع</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Step 1 -->
                <div class="rounded-2xl border bg-white p-5 shadow-sm hover:shadow transition">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-l from-blue-600 to-indigo-600 text-white flex items-center justify-center text-sm font-bold">۱</div>
                        <div>
                            <div class="flex items-center gap-2 font-semibold text-gray-900 mb-1">
                                ثبت‌نام یا ورود
                                <svg class="w-4 h-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v4"/><path d="M14 10l7-7"/><path d="M5 7v11a4 4 0 0 0 4 4h7"/></svg>
                            </div>
                            <p class="text-sm text-gray-700 leading-7">با ایجاد حساب یا ورود، به پنل تیکتینگ دسترسی پیدا کنید.</p>
                        </div>
                    </div>
                </div>
                <!-- Step 2 -->
                <div class="rounded-2xl border bg-white p-5 shadow-sm hover:shadow transition">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-l from-blue-600 to-indigo-600 text-white flex items-center justify-center text-sm font-bold">۲</div>
                        <div>
                            <div class="flex items-center gap-2 font-semibold text-gray-900 mb-1">
                                ثبت موضوع
                                <svg class="w-4 h-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M7 8h10"/></svg>
                            </div>
                            <p class="text-sm text-gray-700 leading-7">عنوان و توضیحات را وارد کنید و در صورت نیاز فایل پیوست کنید.</p>
                        </div>
                    </div>
                </div>
                <!-- Step 3 -->
                <div class="rounded-2xl border bg-white p-5 shadow-sm hover:shadow transition">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-l from-blue-600 to-indigo-600 text-white flex items-center justify-center text-sm font-bold">۳</div>
                        <div>
                            <div class="flex items-center gap-2 font-semibold text-gray-900 mb-1">
                                پیگیری و پاسخ
                                <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <p class="text-sm text-gray-700 leading-7">گفت‌وگو با پشتیبان را ادامه دهید و تا حل نهایی، وضعیت را پیگیری کنید.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-12 bg-gray-50 relative overflow-hidden">
        <div class="pointer-events-none absolute -right-24 top-0 w-72 h-72 bg-indigo-100 rounded-full blur-3xl"></div>
        <div class="pointer-events-none absolute -left-24 bottom-0 w-72 h-72 bg-blue-100 rounded-full blur-3xl"></div>
        <div class="max-w-6xl mx-auto px-4 relative">
            <h2 class="text-xl font-bold text-gray-900 mb-8">سوالات متداول</h2>
            @php $faqs = settings('faq_json') ?? []; @endphp
            @if(!empty($faqs))
                <div class="grid grid-cols-1 gap-4">
                    @foreach($faqs as $i => $faq)
                        <details class="group rounded-2xl border bg-white p-5 hover:shadow-md transition">
                            <summary class="flex items-center justify-between cursor-pointer list-none">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-l from-blue-600 to-indigo-600 text-white flex items-center justify-center text-xs font-bold">{{ $i+1 }}</div>
                                    <div class="font-semibold text-gray-900">{{ $faq['q'] ?? '' }}</div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                            </summary>
                            <div class="mt-4 h-1 w-full bg-gradient-to-l from-blue-100 to-indigo-100 rounded-full"></div>
                            <div class="pt-4 text-sm text-gray-700 leading-8">
                                <div class="rounded-xl bg-gray-50 p-4 border">{!! nl2br(e($faq['a'] ?? '')) !!}</div>
                            </div>
                        </details>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-600">در حال حاضر سوال متداول ثبت نشده است.</p>
            @endif
        </div>
    </section>

    <!-- CTA -->
    <section class="py-12">
        <div class="max-w-6xl mx-auto px-4">
            <div class="rounded-2xl bg-gradient-to-l from-blue-600 to-indigo-600 p-6 md:p-10 text-white">
                <div class="md:flex items-center justify-between gap-6">
                    <div class="mb-4 md:mb-0">
                        <div class="text-lg font-bold">همین حالا شروع کنید</div>
                        <div class="text-sm text-blue-100 mt-1">ورود یا ثبت‌نام و ارسال اولین درخواست</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ request()->getBaseUrl() }}/login" class="px-5 py-2.5 rounded-lg bg-white text-gray-900 hover:opacity-90">ورود</a>
                        <a href="{{ request()->getBaseUrl() }}/register" class="px-5 py-2.5 rounded-lg border border-white hover:bg-white/10">ثبت‌نام</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="mt-auto border-t bg-white relative overflow-hidden">
        <div class="pointer-events-none absolute left-10 -top-6 w-40 h-40 bg-blue-50 rounded-full blur-3xl"></div>
        <div class="pointer-events-none absolute right-10 -bottom-6 w-36 h-36 bg-indigo-50 rounded-full blur-3xl"></div>
        <div class="max-w-6xl mx-auto px-4 py-8 text-xs text-gray-600 text-center relative">
            <div class="inline-flex items-center justify-center px-4 py-3 rounded-2xl border bg-white/80 backdrop-blur hover:shadow-md transition">
                <span>{!! settings('footer_text') ? settings('footer_text') : '© ' . jdate(now())->format('Y') . ' سامانه تیکت' !!}</span>
            </div>
    </div>
    </footer>
</body>
</html>