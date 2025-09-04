@extends('layouts.admin')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">داشبورد مدیریت</h1>
        <p class="text-gray-600 mt-1">آمار کلی سامانه تیکتینگ</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Tickets -->
        <a href="{{ route('admin.tickets.index') }}" class="block">
            <x-ui.card class="text-center hover:shadow transition">
                <div class="text-3xl font-bold text-blue-600 mb-2">{{ number_format($metrics['total_tickets']) }}</div>
                <div class="text-sm text-gray-600">کل تیکت‌ها</div>
            </x-ui.card>
        </a>

        <!-- Open Tickets -->
        <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}" class="block">
            <x-ui.card class="text-center hover:shadow transition">
                <div class="text-3xl font-bold text-yellow-600 mb-2">{{ number_format($metrics['open_tickets']) }}</div>
                <div class="text-sm text-gray-600">باز/در حال رسیدگی</div>
            </x-ui.card>
        </a>

        <!-- Closed Tickets -->
        <a href="{{ route('admin.tickets.index', ['status' => 'closed']) }}" class="block">
            <x-ui.card class="text-center hover:shadow transition">
                <div class="text-3xl font-bold text-green-600 mb-2">{{ number_format($metrics['closed_tickets']) }}</div>
                <div class="text-sm text-gray-600">بسته‌شده</div>
            </x-ui.card>
        </a>

        <!-- Today's Tickets -->
        <x-ui.card class="text-center">
            <div class="text-3xl font-bold text-purple-600 mb-2">{{ number_format($metrics['tickets_today']) }}</div>
            <div class="text-sm text-gray-600">تیکت‌های امروز</div>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- My Assigned (pending) -->
        <a href="{{ route('admin.tickets.index', ['assigned' => 'me']) }}" class="block">
            <x-ui.card class="text-center hover:shadow transition">
                <div class="text-3xl font-bold text-indigo-600 mb-2">{{ number_format($metrics['my_assigned_pending']) }}</div>
                <div class="text-sm text-gray-600">تیکت‌های ارجاع‌شده و منتظر پاسخ من</div>
            </x-ui.card>
        </a>

        <!-- Recent Messages -->
        <x-ui.card class="text-center">
            <div class="text-3xl font-bold text-teal-600 mb-2">{{ number_format($metrics['messages_last_24h']) }}</div>
            <div class="text-sm text-gray-600">پیام‌های ۲۴ ساعت اخیر</div>
        </x-ui.card>

        <!-- Normal Users -->
        <a href="{{ route('admin.users.index') }}" class="block">
            <x-ui.card class="text-center hover:shadow transition">
                <div class="text-3xl font-bold text-amber-600 mb-2">{{ number_format($metrics['normal_users']) }}</div>
                <div class="text-sm text-gray-600">کاربران عادی ثبت‌نام‌شده</div>
            </x-ui.card>
        </a>

        <!-- Unread Internal Messages -->
        <a href="{{ route('admin.internal_messages.index', ['tab' => 'inbox']) }}" class="block">
            <x-ui.card class="text-center hover:shadow transition">
                <div class="text-3xl font-bold text-rose-600 mb-2">{{ number_format($metrics['unread_internal_messages'] ?? 0) }}</div>
                <div class="text-sm text-gray-600">پیام‌های داخلی خوانده‌نشده</div>
            </x-ui.card>
        </a>
    </div>

    
@endsection
