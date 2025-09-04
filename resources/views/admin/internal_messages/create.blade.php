@extends('layouts.admin')

@section('content')
    <h1 class="text-xl font-bold mb-4">ارسال پیام داخلی</h1>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.internal_messages.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm mb-1">گیرنده</label>
                <select name="to_user_id" class="w-full rounded border-gray-300 focus:border-primary focus:ring-primary" required>
                    <option value="">انتخاب...</option>
                    @foreach($recipients as $r)
                        <option value="{{ $r->id }}" @selected(old('to_user_id')==$r->id)>{{ ($r->first_name || $r->last_name) ? trim(($r->first_name ?? '').' '.($r->last_name ?? '')) : $r->name }}</option>
                    @endforeach
                </select>
                @error('to_user_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm mb-1">عنوان (اختیاری)</label>
                <x-ui.input type="text" name="subject" value="{{ old('subject') }}" />
                @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm mb-1">متن پیام</label>
                <x-ui.textarea name="body" rows="8">{{ old('body') }}</x-ui.textarea>
                @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.internal_messages.index') }}" class="text-gray-600">انصراف</a>
                <x-ui.button>ارسال</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection


