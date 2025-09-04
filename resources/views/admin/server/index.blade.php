@extends('layouts.admin')

@section('title', 'سرور من')

@section('content')
<div class="p-6">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">سرور من</h1>
            <p class="text-gray-600">اطلاعات و وضعیت سرور سیستم</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Server Status Card -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">وضعیت سرور</h3>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                </div>
                <p class="text-gray-600 mb-2">سرور در حال اجرا</p>
                <p class="text-sm text-gray-500">آخرین بررسی: {{ now()->format('Y/m/d H:i:s') }}</p>
            </div>

            <!-- PHP Version Card -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">نسخه PHP</h3>
                    <svg class="w-6 h-6 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10,9 9,9 8,9"/>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-blue-600">{{ $serverInfo['php_version'] }}</p>
                <p class="text-sm text-gray-500">نسخه فعلی PHP</p>
            </div>

            <!-- Laravel Version Card -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">نسخه Laravel</h3>
                    <svg class="w-6 h-6 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-red-600">{{ $serverInfo['laravel_version'] }}</p>
                <p class="text-sm text-gray-500">نسخه فعلی Laravel</p>
            </div>

            <!-- Memory Usage Card -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">استفاده از حافظه</h3>
                    <svg class="w-6 h-6 text-yellow-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-yellow-600">{{ number_format($serverInfo['memory_usage'] / 1024 / 1024, 2) }} MB</p>
                <p class="text-sm text-gray-500">حافظه مصرفی فعلی</p>
            </div>

            <!-- Database Status Card -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">وضعیت دیتابیس</h3>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                </div>
                <p class="text-gray-600 mb-2">متصل</p>
                <p class="text-sm text-gray-500">SQLite Database</p>
            </div>

            <!-- Environment Card -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">محیط اجرا</h3>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $serverInfo['environment'] === 'production' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $serverInfo['environment'] }}
                    </span>
                </div>
                <p class="text-gray-600 mb-2">{{ $serverInfo['environment'] === 'production' ? 'محیط تولید' : 'محیط توسعه' }}</p>
                <p class="text-sm text-gray-500">وضعیت فعلی سیستم</p>
            </div>
        </div>

        <!-- Server Information Table -->
        <div class="mt-8 bg-white rounded-lg shadow-sm border">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">اطلاعات تفصیلی سرور</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">اطلاعات سیستم</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">سیستم عامل:</span>
                                <span class="font-medium">{{ $serverInfo['os'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">معماری:</span>
                                <span class="font-medium">{{ $serverInfo['architecture'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">زمان اجرا:</span>
                                <span class="font-medium">{{ number_format($execution_time, 2) }} ثانیه</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">تنظیمات PHP</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">حداکثر حافظه:</span>
                                <span class="font-medium">{{ $serverInfo['memory_limit'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">حداکثر زمان اجرا:</span>
                                <span class="font-medium">{{ $serverInfo['max_execution_time'] }} ثانیه</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">حداکثر آپلود:</span>
                                <span class="font-medium">{{ $serverInfo['upload_max_filesize'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-center space-x-4 space-x-reverse">
            <a href="{{ route('admin.dashboard') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                بازگشت به داشبورد
            </a>
            <button onclick="location.reload()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                بروزرسانی اطلاعات
            </button>
        </div>
    </div>
</div>
@endsection
