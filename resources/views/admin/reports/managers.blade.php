@extends('layouts.admin')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">گزارش حضور و عملکرد پشتیبان‌ها</h1>
        <p class="text-gray-600 mt-1">خلاصه حضور و فعالیت کارشناسان در ماه انتخابی</p>
    </div>

    <x-ui.card>
        <form method="GET" class="mb-6 flex items-center gap-3">
            <label class="text-sm font-medium text-gray-700">ماه:</label>
            <input type="month" name="month" value="{{ $month ?? now()->format('Y-m') }}" class="rounded border-gray-300 focus:border-primary focus:ring-primary">
            <x-ui.button>اعمال</x-ui.button>
            <a href="{{ route('admin.reports.managers', ['month' => $month, 'export' => 1]) }}" class="ml-auto inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm font-medium">
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                خروجی CSV
            </a>
        </form>

        @if(count($agents) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نام کارشناس</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">روزهای فعال</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">جمع حضور (دقیقه)</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">جمع پاسخ‌ها</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">میانگین روزانه</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($agents as $agent)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $agent['name'] }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $agent['active_days'] }} روز
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-900">{{ number_format($agent['total_minutes']) }}</div>
                                    <div class="text-xs text-gray-500">{{ number_format($agent['total_minutes'] / 60, 1) }} ساعت</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ number_format($agent['total_replies']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                    @if($agent['active_days'] > 0)
                                        {{ number_format($agent['total_minutes'] / $agent['active_days'], 0) }} دقیقه
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm font-medium text-gray-900 mb-3">جزئیات روزانه (برای مشاهده کامل از خروجی CSV استفاده کنید)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($agents as $agent)
                        <div class="bg-white p-3 rounded border">
                            <h4 class="font-medium text-gray-900 mb-2">{{ $agent['name'] }}</h4>
                            <div class="space-y-1 text-xs">
                                @foreach(array_slice($agent['days'], 0, 5) as $day)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">{{ $day['date_display'] }}</span>
                                        <span class="text-gray-900">
                                            {{ $day['minutes'] ?? 0 }}د | {{ $day['replies'] }}پ
                                        </span>
                                    </div>
                                @endforeach
                                @if(count($agent['days']) > 5)
                                    <div class="text-gray-500 text-center pt-1">...</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-8">
                <div class="text-gray-500">داده‌ای برای نمایش وجود ندارد.</div>
            </div>
        @endif
    </x-ui.card>
@endsection


