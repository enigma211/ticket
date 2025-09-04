<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>چاپ پیام‌های کاربر - کد رهگیری {{ $ticket->tracking_code }}</title>
	<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap" rel="stylesheet">
	<style>
		*, *::before, *::after { box-sizing: border-box; }
		html, body { height: 100%; }
		@font-face { font-family: 'Vazirmatn'; font-style: normal; font-weight: 400; src: local('Vazirmatn'); }
		@font-face { font-family: 'Vazirmatn'; font-style: normal; font-weight: 700; src: local('Vazirmatn'); }
		body { font-family: 'Vazirmatn', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; color: #111827; background: #ffffff; }
		.container { max-width: 900px; margin: 0 auto; padding: 24px; }
		.header { padding-bottom: 12px; margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
		.brand-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
		.brand-title { font-size: 22px; font-weight: 800; margin: 0; }
		.brand-sub { font-size: 12px; color: #6b7280; }
		.meta-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 16px; margin-top: 10px; }
		.meta-item { font-size: 12px; color: #374151; }
		.meta-label { color: #6b7280; margin-left: 6px; }
		.section { margin-top: 20px; }
		.card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; margin-bottom: 14px; background: #ffffff; page-break-inside: avoid; }
		.card-title { font-weight: 700; margin: 0 0 10px; font-size: 14px; }
		.message { border-right: 3px solid #e5e7eb; padding-right: 12px; }
		.message-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px; }
		.message-name { font-weight: 600; color: #111827; font-size: 13px; }
		.message-time { font-size: 12px; color: #6b7280; }
		.msg { white-space: pre-line; line-height: 1.9; font-size: 13px; color: #111827; }
		.small { font-size: 12px; color: #6b7280; }
		.footer-print { margin-top: 24px; font-size: 11px; color: #6b7280; text-align: center; }
		.btn { display: inline-block; padding: 8px 14px; background: #111827; color: #fff; border-radius: 6px; text-decoration: none; }
		@page { size: A4; margin: 15mm; }
		@media print {
			.no-print { display: none !important; }
			body { background: #ffffff; }
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<div class="brand-row">
				<h1 class="brand-title">{{ settings('site_name') ?? 'سامانه تیکت' }}</h1>
			</div>
			<div class="meta-grid">
				<div class="meta-item"><span class="meta-label">عنوان:</span>{{ $ticket->title }}</div>
				<div class="meta-item"><span class="meta-label">کد رهگیری:</span>{{ $ticket->tracking_code }}</div>
				<div class="meta-item"><span class="meta-label">درخواست‌کننده:</span>{{ (($user->first_name ?? null) || ($user->last_name ?? null)) ? trim(($user->first_name ?? '').' '.($user->last_name ?? '')) : ($user->name ?? '—') }}</div>
				<div class="meta-item"><span class="meta-label">ایمیل:</span>{{ $user->email ?? '—' }}</div>
				<div class="meta-item"><span class="meta-label">موبایل:</span>{{ $user->mobile ?? '—' }}</div>
				<div class="meta-item"><span class="meta-label">تاریخ ایجاد:</span>@jdateOffsetShort($ticket->created_at)</div>
			</div>
		</div>
		<!-- مشخصات کاربر در سربرگ آمده است -->
		<div class="section">
			@foreach($messages as $m)
				<div class="card message">
					<div class="message-head">
						<div class="message-name">پیام کاربر</div>
						<div class="message-time">@jdateOffset($m->created_at)</div>
					</div>
					<div class="msg">{{ $m->body }}</div>
				</div>
			@endforeach
		</div>
		<div class="section no-print" style="text-align:center;">
			<a href="#" class="btn" onclick="window.print(); return false;">چاپ</a>
		</div>
	</div>
</body>
</html>


