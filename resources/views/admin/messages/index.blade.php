@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">مدیریت پیام‌ها</h1>
        <form method="GET" class="flex items-center gap-2">
            <x-ui.input type="text" name="q" value="{{ request('q') }}" placeholder="جستجو در متن، کاربر یا کد پیگیری" />
            <x-ui.button>جستجو</x-ui.button>
        </form>
    </div>

    <x-ui.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-600">
                    <th class="p-2 text-right">تیکت</th>
                    <th class="p-2 text-right">ارسال‌کننده</th>
                    <th class="p-2 text-right">نمایش</th>
                    <th class="p-2 text-right">متن</th>
                    <th class="p-2 text-right">تاریخ</th>
                    <th class="p-2 text-right">اقدامات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($messages as $m)
                    <tr class="border-t">
                        <td class="p-2">#{{ $m->ticket->tracking_code ?? '-' }}</td>
                        <td class="p-2">{{ ($m->user->first_name ?? null) || ($m->user->last_name ?? null) ? trim(($m->user->first_name ?? '').' '.($m->user->last_name ?? '')) : ($m->user->name ?? '-') }}</td>
                        <td class="p-2">{{ $m->visibility === 'internal' ? 'داخلی' : 'عمومی' }}</td>
                        <td class="p-2 truncate max-w-[400px]">{{ Str::limit($m->body, 120) }}</td>
                        <td class="p-2 text-gray-600">@jdateOffset($m->created_at)</td>
                        <td class="p-2 flex items-center gap-3">
                            <a class="text-primary" href="{{ route('admin.messages.edit', $m) }}">ویرایش</a>
                            <form method="POST" action="{{ route('admin.messages.destroy', $m) }}">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600" onclick="return confirm('حذف پیام؟')">حذف</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $messages->links() }}</div>
    </x-ui.card>
@endsection
