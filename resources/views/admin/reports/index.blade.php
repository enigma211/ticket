@extends('layouts.admin')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">گزارش‌ها</h1>
        <p class="text-gray-600 mt-1">موضوعات دریافتی، موضوعات پاسخ‌داده‌شده و کل پیام‌های رد و بدل‌شده</p>
    </div>

    <x-ui.card>
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="text-sm text-gray-600">بازه زمانی:</div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.reports.index', ['months' => 3]) }}" class="px-3 py-1 rounded border {{ $months === 3 ? 'bg-primary text-white border-primary' : 'border-gray-300 text-gray-700' }}">۳ ماه</a>
                <a href="{{ route('admin.reports.index', ['months' => 6]) }}" class="px-3 py-1 rounded border {{ $months === 6 ? 'bg-primary text-white border-primary' : 'border-gray-300 text-gray-700' }}">۶ ماه</a>
                <a href="{{ route('admin.reports.index', ['months' => 12]) }}" class="px-3 py-1 rounded border {{ $months === 12 ? 'bg-primary text-white border-primary' : 'border-gray-300 text-gray-700' }}">۱۲ ماه</a>
            </div>
        </div>

        <div class="relative">
            <canvas id="messagesChart" height="120"></canvas>
        </div>
    </x-ui.card>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('messagesChart').getContext('2d');
        const labels = @json($labels);
        const topicsIncoming = @json($topics_incoming);
        const topicsAnswered = @json($topics_answered);
        const messagesTotal = @json($messages_total);
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'موضوعات دریافتی',
                        data: topicsIncoming,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,0.1)',
                        tension: 0.3,
                    },
                    {
                        label: 'موضوعات پاسخ‌داده‌شده',
                        data: topicsAnswered,
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34,197,94,0.1)',
                        tension: 0.3,
                    },
                    {
                        label: 'کل پیام‌ها',
                        data: messagesTotal,
                        borderColor: '#64748b',
                        backgroundColor: 'rgba(100,116,139,0.1)',
                        tension: 0.3,
                    },
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                },
                plugins: {
                    legend: { display: true },
                    tooltip: { enabled: true }
                }
            }
        });
    </script>
@endsection


