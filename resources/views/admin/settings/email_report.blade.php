@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">گزارش ایمیل‌ها</h1>
        <a href="{{ route('admin.settings.email.edit') }}" class="text-sm text-gray-700">بازگشت به تنظیمات ایمیل</a>
    </div>

    <x-ui.card>
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('admin.settings.email.report') }}" class="px-2 py-1 rounded @if(!$status) bg-gray-800 text-white @else bg-gray-100 text-gray-700 @endif">همه</a>
                <a href="{{ route('admin.settings.email.report', ['status'=>'queued']) }}" class="px-2 py-1 rounded @if($status==='queued') bg-gray-800 text-white @else bg-gray-100 text-gray-700 @endif">در صف ({{ $counts['queued'] }})</a>
                <a href="{{ route('admin.settings.email.report', ['status'=>'sent']) }}" class="px-2 py-1 rounded @if($status==='sent') bg-gray-800 text-white @else bg-gray-100 text-gray-700 @endif">ارسال شده ({{ $counts['sent'] }})</a>
                <a href="{{ route('admin.settings.email.report', ['status'=>'failed']) }}" class="px-2 py-1 rounded @if($status==='failed') bg-gray-800 text-white @else bg-gray-100 text-gray-700 @endif">ناموفق ({{ $counts['failed'] }})</a>
            </div>
            <form method="GET" class="flex items-center gap-2">
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <x-ui.input type="text" name="q" value="{{ request('q') }}" placeholder="جستجو در گیرنده/عنوان/کلاس" />
                <x-ui.button>جستجو</x-ui.button>
            </form>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-600">
                    <th class="p-2 text-right">گیرنده</th>
                    <th class="p-2 text-right">عنوان</th>
                    <th class="p-2 text-right">وضعیت</th>
                    <th class="p-2 text-right">زمان</th>
                    <th class="p-2 text-right">شناسه پیام</th>
                    <th class="p-2 text-right">کلاس</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr class="border-t">
                        <td class="p-2 font-mono">{{ $log->to }}</td>
                        <td class="p-2">{{ $log->subject ?: '—' }}</td>
                        <td class="p-2">
                            @if($log->status==='queued')<span class="text-amber-700">در صف</span>@endif
                            @if($log->status==='sent')<span class="text-green-700">ارسال شده</span>@endif
                            @if($log->status==='failed')<span class="text-red-700">ناموفق</span>@endif
                        </td>
                        <td class="p-2 text-gray-600">
                            @if($log->status==='queued') @jdateOffset($log->queued_at ?? $log->created_at) @endif
                            @if($log->status==='sent') @jdateOffset($log->sent_at ?? $log->updated_at) @endif
                            @if($log->status==='failed') @jdateOffset($log->failed_at ?? $log->updated_at) @endif
                        </td>
                        <td class="p-2 text-xs text-gray-600">{{ $log->message_id ?: '—' }}</td>
                        <td class="p-2 text-xs text-gray-600">{{ $log->mailable ?: '—' }}</td>
                    </tr>
                    @if($log->status==='failed' && $log->error)
                        <tr class="border-b bg-red-50/50">
                            <td colspan="6" class="p-3 text-xs text-red-700">{{ $log->error }}</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">رکوردی یافت نشد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $logs->links() }}</div>
    </x-ui.card>
@endsection


