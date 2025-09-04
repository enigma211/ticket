@extends('layouts.admin')

@section('content')
    <h1 class="text-xl font-bold mb-4">ویرایش پیام</h1>

    <x-ui.card>
        <div class="mb-4 text-sm text-gray-600">
            تیکت: #{{ $message->ticket->tracking_code ?? '-' }} • ارسال‌کننده: {{ (($message->user->first_name ?? null) || ($message->user->last_name ?? null)) ? trim(($message->user->first_name ?? '').' '.($message->user->last_name ?? '')) : ($message->user->name ?? '-') }} • @jdateOffset($message->created_at)
        </div>
        <form method="POST" action="{{ route('admin.messages.update', $message) }}" class="space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm mb-1">نمایش</label>
                <select name="visibility" class="w-full rounded border-gray-300">
                    <option value="public" @selected(old('visibility', $message->visibility) === 'public')>عمومی</option>
                    <option value="internal" @selected(old('visibility', $message->visibility) === 'internal')>داخلی</option>
                </select>
                @error('visibility')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm mb-1">متن پیام</label>
                <x-ui.textarea name="body" rows="6">{{ old('body', $message->body) }}</x-ui.textarea>
                @error('body')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.messages.index') }}" class="text-gray-600">انصراف</a>
                <x-ui.button>ذخیره</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection
