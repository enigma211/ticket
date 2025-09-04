@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold flex items-center gap-2">تیکت‌های {{ (($user->first_name ?? null) || ($user->last_name ?? null)) ? trim(($user->first_name ?? '').' '.($user->last_name ?? '')) : ($user->name ?? '') }}
            @if($user->is_spammer)
                <span class="inline-flex items-center gap-1 text-red-700 bg-red-50 px-2 py-0.5 rounded text-xs">مزاحم</span>
            @endif
        </h1>
        <a href="{{ route('admin.tickets.index') }}" onclick="if (history.length > 1) { history.back(); return false; }" class="text-primary">بازگشت</a>
    </div>

    <x-ui.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-600">
                    <th class="p-2 text-right">عنوان</th>
                    <th class="p-2 text-right">وضعیت</th>
                    <th class="p-2 text-right">کد پیگیری</th>
                    <th class="p-2 text-right">بروزرسانی</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                    @php $rowSpammer = $user->is_spammer; @endphp
                    <tr class="border-t {{ $rowSpammer ? 'bg-red-50 border-red-200' : '' }}">
                        <td class="p-2">
                            <a href="{{ route('admin.tickets.show', $ticket) }}" class="text-primary hover:underline">{{ $ticket->title }}</a>
                            @if($rowSpammer)
                                <span class="mr-2 inline-flex items-center gap-1 text-red-700 bg-red-50 px-2 py-0.5 rounded text-xs align-middle">مزاحم</span>
                            @endif
                        </td>
                        <td class="p-2">
                            @php $statusColors = ['open' => 'text-yellow-600', 'awaiting_user' => 'text-blue-600', 'closed' => 'text-gray-600', 'auto_closed' => 'text-gray-500']; @endphp
                            <span class="{{ $statusColors[$ticket->status] ?? 'text-gray-600' }}">{{ $ticket->status_label }}</span>
                        </td>
                        <td class="p-2">{{ $ticket->tracking_code }}</td>
                        <td class="p-2 text-gray-600">@jdateOffset($ticket->updated_at)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $tickets->links() }}</div>
    </x-ui.card>
@endsection


