<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'سامانه تیکت' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* User dark theme */
        body.theme-dark { background: #0f172a !important; color: #e5e7eb; }
        body.theme-dark .bg-white { background-color: #111827 !important; }
        body.theme-dark .border, body.theme-dark .border-b, body.theme-dark .border-t, body.theme-dark .border-l, body.theme-dark .border-r { border-color: #1f2937 !important; }
        body.theme-dark .text-gray-900 { color: #e5e7eb !important; }
        body.theme-dark .text-gray-800, body.theme-dark .text-gray-700, body.theme-dark .text-gray-600, body.theme-dark .text-gray-500 { color: #cbd5e1 !important; }
        body.theme-dark a:hover { color: #fff !important; }
        body.theme-dark .hover\:bg-gray-100:hover { background-color: #0b1220 !important; }

        /* User light theme subtle background */
        body.user-app {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 48%, #eef2f7 100%);
        }
    </style>
</head>
<body class="user-app min-h-screen bg-gray-50 text-gray-800 flex flex-col">
    <header class="border-b bg-white">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ url('/tickets') }}" class="flex items-center gap-3">
                <img src="{{ asset('storage/logos/AqquFLOQxDxAHPom7sxOVFejAsB3b7Fxu1irz0YS.png') }}" alt="لوگو" class="w-8 h-8 rounded" />
                <div class="font-extrabold text-gray-900">{{ settings('site_name') ?? 'سامانه تیکت' }}</div>
            </a>
            <nav class="flex items-center gap-4 text-sm">
                <button type="button" id="theme_toggle_user" class="px-2 py-1 text-xs rounded border hover:bg-gray-50">حالت تاریک</button>
                @auth
                    @if(auth()->user()->is_agent || auth()->user()->is_superadmin)
                        <a href="{{ route('admin.dashboard') }}" class="relative">
                            داشبورد
                            @php
                                $unansweredCount = \App\Models\Ticket::whereIn('status', ['new', 'open'])
                                    ->whereHas('messages', function ($query) {
                                        $query->whereHas('user', function ($userQuery) {
                                            $userQuery->where('is_agent', false)->where('is_superadmin', false);
                                        });
                                    })
                                    ->get()
                                    ->filter(function ($ticket) {
                                        $latestMessage = $ticket->messages()->latest()->first();
                                        if ($latestMessage) {
                                            $user = $latestMessage->user;
                                            return !$user->is_agent && !$user->is_superadmin;
                                        }
                                        return true;
                                    })->count();
                            @endphp
                            @if($unansweredCount > 0)
                                <span class="absolute -top-2 -left-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $unansweredCount }}</span>
                            @endif
                        </a>
                    @else
                        <a href="{{ route('tickets.create') }}" class="px-3 py-1.5 rounded bg-primary text-white hover:opacity-90">ایجاد تیکت</a>
                    @endif
                    @if(auth()->user()->is_superadmin)
                        <a href="{{ route('admin.users.index') }}">کاربران</a>
                        <a href="{{ route('admin.settings.edit') }}">تنظیمات</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button class="px-3 py-1.5 rounded bg-gray-900 text-white">خروج</button>
                    </form>
                @else
                    <a href="{{ request()->getBaseUrl() }}/login">ورود</a>
                    <a href="{{ request()->getBaseUrl() }}/register" class="px-3 py-1.5 rounded bg-primary text-white">ثبت‌نام</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="flex-1 max-w-6xl mx-auto px-4 py-6 w-full">
        @if(session('success'))
            <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('status'))
            <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="border-t bg-white mt-auto">
        <div class="max-w-6xl mx-auto px-4 py-6 text-xs text-gray-500 text-center">
            {!! settings('footer_text') ?? '' !!}
        </div>
    </footer>
    <script>
        (function(){
            const body = document.body;
            const key = 'user-theme';
            const btn = document.getElementById('theme_toggle_user');
            function applyTheme(val){
                if (val === 'dark') body.classList.add('theme-dark');
                else body.classList.remove('theme-dark');
                if (btn) btn.textContent = body.classList.contains('theme-dark') ? 'حالت روشن' : 'حالت تاریک';
            }
            try { applyTheme(localStorage.getItem(key)); } catch(_) { applyTheme(null); }
            if (btn){
                btn.addEventListener('click', function(){
                    const next = body.classList.contains('theme-dark') ? 'light' : 'dark';
                    try { localStorage.setItem(key, next); } catch(_) {}
                    applyTheme(next);
                });
            }
        })();
    </script>
</body>
</html>