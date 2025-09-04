@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">جزئیات پیام داخلی</h1>
        <a href="{{ route('admin.internal_messages.index') }}" class="text-sm text-gray-700">بازگشت</a>
    </div>

    <x-ui.card>
        <div class="space-y-3 text-sm">
            <div class="flex items-center justify-between">
                <span class="text-gray-600">از</span>
                <span>{{ ($internalMessage->sender->first_name || $internalMessage->sender->last_name) ? trim(($internalMessage->sender->first_name ?? '').' '.($internalMessage->sender->last_name ?? '')) : ($internalMessage->sender->name ?? '-') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-600">به</span>
                <span>{{ ($internalMessage->recipient->first_name || $internalMessage->recipient->last_name) ? trim(($internalMessage->recipient->first_name ?? '').' '.($internalMessage->recipient->last_name ?? '')) : ($internalMessage->recipient->name ?? '-') }}</span>
            </div>
            @if($internalMessage->subject)
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">عنوان</span>
                    <span class="font-medium">{{ $internalMessage->subject }}</span>
                </div>
            @endif
        </div>
    </x-ui.card>

    <x-ui.card class="mt-4">
        <h3 class="font-semibold mb-3">مکالمه</h3>
        <div class="space-y-3">
            @foreach(($conversation ?? collect()) as $msg)
                <div class="border rounded p-3 @if($msg->from_user_id === auth()->id()) bg-blue-50 border-blue-100 @else bg-gray-50 border-gray-200 @endif">
                    <div class="flex items-center justify-between text-xs text-gray-600 mb-2">
                        <span>{{ ($msg->sender->first_name || $msg->sender->last_name) ? trim(($msg->sender->first_name ?? '').' '.($msg->sender->last_name ?? '')) : ($msg->sender->name ?? '-') }}</span>
                        <span>@jdateOffset($msg->created_at)</span>
                    </div>
                    <div class="whitespace-pre-line text-sm text-gray-800">{{ $msg->body }}</div>
                </div>
            @endforeach
        </div>
    </x-ui.card>

    <x-ui.card class="mt-4">
        <h3 class="font-semibold mb-3">پاسخ</h3>
        <form method="POST" action="{{ route('admin.internal_messages.reply', $internalMessage) }}" class="space-y-3">
            @csrf
            <x-ui.textarea name="body" rows="4">{{ old('body') }}</x-ui.textarea>
            @error('body')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            <div class="flex justify-end">
                <x-ui.button>ارسال پاسخ</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection


