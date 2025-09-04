<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ $title ?? 'مدیریت | سامانه تیکت' }}</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
	<style>
		/* Admin dark theme (scoped) */
		body.theme-dark { background-color: #0f172a; color: #e5e7eb; }
		body.theme-dark .bg-white { background-color: #111827 !important; }
		body.theme-dark .border, body.theme-dark .border-b, body.theme-dark .border-l { border-color: #1f2937 !important; }
		body.theme-dark .text-gray-900 { color: #e5e7eb !important; }
		body.theme-dark .text-gray-800, body.theme-dark .text-gray-700, body.theme-dark .text-gray-600, body.theme-dark .text-gray-500 { color: #cbd5e1 !important; }
		body.theme-dark a:hover { color: #fff !important; }
		body.theme-dark .hover\:bg-gray-100:hover { background-color: #0b1220 !important; }

		/* Sidebar themed backgrounds */
		.admin-sidebar { background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%); }
		body.theme-dark .admin-sidebar { background: linear-gradient(180deg, #0b1220 0%, #111827 100%); }
	</style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-800">
	<div class="min-h-screen flex">
		<!-- Sidebar (right) -->
		<aside class="w-72 border-l flex flex-col fixed inset-y-0 right-0 admin-sidebar">
			<div class="px-4 py-4 border-b flex items-center justify-between">
				<a href="{{ route('admin.dashboard') }}" class="font-bold">مدیریت</a>
			</div>
			<nav class="flex-1 overflow-y-auto p-3 space-y-1 text-sm">
				<a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.dashboard')) bg-gray-100 font-semibold @endif">
					<svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l2-2 7-7 7 7 2 2"/><path d="M5 10v10a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V10"/></svg>
					<span>داشبورد</span>
				</a>
				<a href="{{ route('admin.tickets.index') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.tickets.index')) bg-gray-100 font-semibold @endif">
					<svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M7 8h10"/><path d="M7 12h6"/></svg>
					<span>تیکت‌ها</span>
				</a>
				<a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.profile.*')) bg-gray-100 font-semibold @endif">
					<svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
					<span>پروفایل من</span>
				</a>
				@if(auth()->user()->is_superadmin)
					<a href="{{ route('admin.canned.index') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.canned.*')) bg-gray-100 font-semibold @endif">
						<svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
						<span>پیام‌های آماده</span>
					</a>
				@endif
				@if(auth()->user()->is_superadmin || auth()->user()->is_agent)
					<div class="px-1">
						<button type="button" id="users_toggle" class="w-full flex items-center justify-between px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.users.*')) bg-gray-100 font-semibold @endif">
							<span class="flex items-center gap-3">
								<svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
								<span>کاربران</span>
							</span>
							<svg id="users_chev" class="w-4 h-4 text-gray-500 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
						</button>
						<div id="users_sub" class="mt-1 pl-8 hidden">
							@if(auth()->user()->is_superadmin)
								<a href="{{ route('admin.users.index', ['role' => 'staff']) }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 @if(request()->fullUrlIs(route('admin.users.index', ['role'=>'staff']))) bg-gray-100 font-semibold @endif">مدیران</a>
							@endif
							<a href="{{ route('admin.users.index', ['role' => 'normal']) }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 @if(request()->fullUrlIs(route('admin.users.index', ['role'=>'normal']))) bg-gray-100 font-semibold @endif">کاربران عادی</a>
						</div>
					</div>
				@endif
				@if(auth()->user()->is_superadmin || auth()->user()->is_agent)
					<a href="{{ route('admin.internal_messages.index') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.internal_messages.*')) bg-gray-100 font-semibold @endif">
						<svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
						<span>پیام‌های داخلی</span>
					</a>
				@endif
				@if(auth()->user()->is_superadmin || auth()->user()->is_agent)
					<a href="{{ route('admin.server.index') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.server.*')) bg-gray-100 font-semibold @endif">
						<svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
						<span>سرور من</span>
					</a>
				@endif
				@if(auth()->user()->is_superadmin)
					<div class="px-1">
						<button type="button" id="reports_toggle" class="w-full flex items-center justify-between px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.reports.*')) bg-gray-100 font-semibold @endif">
							<span class="flex items-center gap-3">
								<svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 4-5"/></svg>
								<span>گزارش‌ها</span>
							</span>
							<svg id="reports_chev" class="w-4 h-4 text-gray-500 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
						</button>
						<div id="reports_sub" class="mt-1 pl-8 hidden">
							<a href="{{ route('admin.reports.index') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 @if(request()->fullUrlIs(route('admin.reports.index'))) bg-gray-100 font-semibold @endif">گزارش عمومی</a>
							<a href="{{ route('admin.reports.managers') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.reports.managers')) bg-gray-100 font-semibold @endif">گزارش مدیران</a>
						</div>
					</div>
					<div class="px-1">
						<button type="button" id="settings_toggle" class="w-full flex items-center justify-between px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.settings.*')) bg-gray-100 font-semibold @endif">
							<span class="flex items-center gap-3">
								<svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v2"/><path d="M12 21v2"/><path d="M4.93 4.93l1.41 1.41"/><path d="M17.66 17.66l1.41 1.41"/><path d="M1 12h2"/><path d="M21 12h2"/><path d="M4.93 19.07l1.41-1.41"/><path d="M17.66 6.34l1.41-1.41"/><circle cx="12" cy="12" r="3"/></svg>
								<span>تنظیمات</span>
							</span>
							<svg id="settings_chev" class="w-4 h-4 text-gray-500 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
						</button>
						<div id="settings_sub" class="mt-1 pl-8 hidden">
							<a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.settings.edit')) bg-gray-100 font-semibold @endif">عمومی</a>
							<a href="{{ route('admin.settings.uploads.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.settings.uploads.*')) bg-gray-100 font-semibold @endif">آپلود و محدودیت‌ها</a>
							<a href="{{ route('admin.settings.workhours.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.settings.workhours.*')) bg-gray-100 font-semibold @endif">زمان‌بندی کاری</a>
							<a href="{{ route('admin.settings.faq.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.settings.faq.*')) bg-gray-100 font-semibold @endif">سوالات متداول</a>
							<a href="{{ route('admin.settings.security.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.settings.security.*')) bg-gray-100 font-semibold @endif">امنیت</a>
							<a href="{{ route('admin.settings.email.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.settings.email.*')) bg-gray-100 font-semibold @endif">ایمیل</a>
							<a href="{{ route('admin.settings.retention.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.settings.retention.*')) bg-gray-100 font-semibold @endif">نگهداری</a>
							<a href="{{ route('admin.departments.index') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 @if(request()->routeIs('admin.departments.*')) bg-gray-100 font-semibold @endif">دپارتمان‌ها</a>
						</div>
					</div>
				@endif
			</nav>
			<div class="p-3 border-t">
				<form method="POST" action="{{ route('logout') }}" class="w-full">
					@csrf
					<button class="w-full px-3 py-2 rounded bg-gray-900 text-white">خروج</button>
				</form>
			</div>
		</aside>

		<!-- Content -->
		<div class="flex-1 pr-72 min-h-screen flex flex-col">
			<header class="h-14 bg-white border-b flex items-center px-4 justify-between">
				<div class="font-semibold">{{ $header ?? '' }}</div>
				<div class="flex items-center gap-2">
					<button type="button" id="theme_toggle" class="px-2 py-1 text-xs rounded border hover:bg-gray-50">حالت تاریک</button>
					<div class="text-sm text-gray-500">{{ auth()->user()->name ?? '' }}</div>
				</div>
			</header>
			<main class="p-6">
				@if(session('success'))
					<div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-green-800">{{ session('success') }}</div>
				@endif
				@if(session('status'))
					<div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
				@endif
				@yield('content')
			</main>
			<footer class="border-t bg-white mt-auto">
				<div class="px-4 py-4 text-xs text-gray-500 text-center">
					{!! settings('footer_text') ?? '' !!}
				</div>
			</footer>
		</div>
	</div>
	<script>
		(function(){
			const body = document.body;
			const key = 'admin-theme';
			const btn = document.getElementById('theme_toggle');
			const st = document.getElementById('settings_toggle');
			const ss = document.getElementById('settings_sub');
			const sc = document.getElementById('settings_chev');
			const rt = document.getElementById('reports_toggle');
			const rs = document.getElementById('reports_sub');
			const rc = document.getElementById('reports_chev');
			const ut = document.getElementById('users_toggle');
			const us = document.getElementById('users_sub');
			const uc = document.getElementById('users_chev');
			function applyTheme(val){
				if (val === 'dark') body.classList.add('theme-dark');
				else body.classList.remove('theme-dark');
				if (btn) btn.textContent = body.classList.contains('theme-dark') ? 'حالت روشن' : 'حالت تاریک';
			}
			applyTheme(localStorage.getItem(key));
			if (btn){
				btn.addEventListener('click', function(){
					const next = body.classList.contains('theme-dark') ? 'light' : 'dark';
					localStorage.setItem(key, next);
					applyTheme(next);
				});
			}
			if (st && ss && sc){
				st.addEventListener('click', function(){
					ss.classList.toggle('hidden');
					sc.style.transform = ss.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
				});
				@if(request()->routeIs('admin.settings.*'))
					ss.classList.remove('hidden');
					sc.style.transform = 'rotate(180deg)';
				@endif
			}
			if (rt && rs && rc){
				rt.addEventListener('click', function(){
					rs.classList.toggle('hidden');
					rc.style.transform = rs.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
				});
				@if(request()->routeIs('admin.reports.*'))
					rs.classList.remove('hidden');
					rc.style.transform = 'rotate(180deg)';
				@endif
			}
			if (ut && us && uc){
				ut.addEventListener('click', function(){
					us.classList.toggle('hidden');
					uc.style.transform = us.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
				});
				@if(request()->routeIs('admin.users.*'))
					us.classList.remove('hidden');
					uc.style.transform = 'rotate(180deg)';
				@endif
			}
		})();
	</script>
</body>
</html>
