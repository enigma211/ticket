@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">پیام‌های داخلی</h1>
        <a href="{{ route('admin.internal_messages.create') }}" class="px-3 py-2 rounded bg-blue-600 text-white">پیام جدید</a>
    </div>

    <x-ui.card>
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('admin.internal_messages.index', ['tab' => 'inbox']) }}" class="px-2 py-1 rounded @if(($tab ?? 'inbox')==='inbox') bg-gray-800 text-white @else bg-gray-100 text-gray-700 @endif">صندوق ورودی</a>
                <a href="{{ route('admin.internal_messages.index', ['tab' => 'sent']) }}" class="px-2 py-1 rounded @if(($tab ?? 'inbox')==='sent') bg-gray-800 text-white @else bg-gray-100 text-gray-700 @endif">ارسالی‌ها</a>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <x-ui.input type="text" name="q" value="{{ request('q') }}" placeholder="جستجو" />
                <x-ui.button>جستجو</x-ui.button>
            </form>
        </div>

        <form method="POST" action="{{ route('admin.internal_messages.destroy_many') }}">
            @csrf
            @method('DELETE')
            <div class="mb-3">
                <button class="px-3 py-2 rounded bg-red-600 text-white" onclick="return confirm('حذف پیام‌های انتخاب‌شده؟')">حذف انتخاب‌شده‌ها</button>
            </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-600">
                    <th class="p-2 text-right"><input type="checkbox" id="check_all"></th>
                    <th class="p-2 text-right">عنوان</th>
                    <th class="p-2 text-right">{{ ($tab ?? 'inbox')==='sent' ? 'گیرنده' : 'فرستنده' }}</th>
                    <th class="p-2 text-right">تاریخ</th>
                    <th class="p-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $msg)
                    <tr class="border-t @if(!$msg->read_at && ($tab ?? 'inbox')==='inbox') bg-amber-50 @endif">
                        <td class="p-2"><input type="checkbox" name="ids[]" value="{{ $msg->id }}" class="row-check"></td>
                        <td class="p-2">
                            <a href="{{ route('admin.internal_messages.show', $msg) }}" class="text-primary hover:underline">
                                {{ $msg->thread?->subject ?: ($msg->subject ?: Str::limit(strip_tags($msg->body), 50)) }}
                            </a>
                        </td>
                        <td class="p-2">
                            @php $user = ($tab ?? 'inbox')==='sent' ? $msg->recipient : $msg->sender; @endphp
                            {{ ($user->first_name || $user->last_name) ? trim(($user->first_name ?? '').' '.($user->last_name ?? '')) : ($user->name ?? '-') }}
                        </td>
                        <td class="p-2 text-gray-600">@jdateOffset($msg->created_at)</td>
                        <td class="p-2 text-left">
                            @if(($tab ?? 'inbox')==='inbox' && !$msg->read_at)
                                <span class="text-xs text-amber-700">خوانده نشده</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">رکوردی یافت نشد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </form>
        <div class="mt-4">{{ $messages->links() }}</div>
    </x-ui.card>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const all = document.getElementById('check_all');
            const rows = document.querySelectorAll('.row-check');
            if (all) all.addEventListener('change', function(){ rows.forEach(cb => cb.checked = all.checked); });
        });
    </script>
@endsection


