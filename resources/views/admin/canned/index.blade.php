@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">پاسخ‌های آماده</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @if(auth()->user()->is_superadmin)
            <x-ui.card>
                <h2 class="font-semibold mb-3">ایجاد گروه جدید</h2>
                <form method="POST" action="{{ route('admin.canned.groups.store') }}" class="space-y-3">
                    @csrf
                    <x-ui.input name="name" placeholder="نام گروه" required />
                    @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    <x-ui.button>ایجاد گروه</x-ui.button>
                </form>
            </x-ui.card>
        @endif

        <x-ui.card class="lg:col-span-2">
            <h2 class="font-semibold mb-3">گروه‌ها و پاسخ‌ها</h2>
            <div class="space-y-3">
                @forelse($groups as $group)
                    <details class="border rounded-lg p-3 group">
                        <summary class="cursor-pointer flex items-center justify-between">
                            <span class="font-medium">{{ $group->name }}</span>
                            <span class="text-xs text-gray-500 group-open:hidden">باز کردن</span>
                            <span class="text-xs text-gray-500 hidden group-open:inline">بستن</span>
                        </summary>
                        <div class="mt-3">
                            @if(auth()->user()->is_superadmin)
                                <div class="flex items-center gap-2 mb-3">
                                    <form method="POST" action="{{ route('admin.canned.groups.update', $group) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.input name="name" value="{{ $group->name }}" />
                                        <x-ui.button>ذخیره</x-ui.button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.canned.groups.destroy', $group) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600">حذف</button>
                                    </form>
                                </div>
                            @endif

                            <div class="space-y-2">
                                @foreach($group->replies as $reply)
                                    <details class="border rounded p-2">
                                        <summary class="cursor-pointer flex items-center justify-between">
                                            <span class="font-medium">{{ $reply->title }}</span>
                                            <span class="text-xs text-gray-500">مشاهده</span>
                                        </summary>
                                        <div class="mt-2 space-y-2">
                                            @if(auth()->user()->is_superadmin)
                                                <form id="reply_update_{{ $reply->id }}" method="POST" action="{{ route('admin.canned.replies.update', $reply) }}" class="space-y-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="group_id" value="{{ $group->id }}" />
                                                    <x-ui.input name="title" value="{{ $reply->title }}" />
                                                    <x-ui.textarea name="body" rows="3">{{ $reply->body }}</x-ui.textarea>
                                                </form>
                                                <div class="flex items-center gap-2">
                                                    <button type="submit" form="reply_update_{{ $reply->id }}" class="px-3 py-2 rounded bg-gray-900 text-white">ذخیره</button>
                                                    <form method="POST" action="{{ route('admin.canned.replies.destroy', $reply) }}" onsubmit="return confirm('این پاسخ حذف شود؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="text-red-600">حذف</button>
                                                    </form>
                                                </div>
                                            @else
                                                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $reply->body }}</div>
                                            @endif
                                        </div>
                                    </details>
                                @endforeach
                            </div>

                            @if(auth()->user()->is_superadmin)
                                <div class="mt-3">
                                    <form method="POST" action="{{ route('admin.canned.replies.store') }}" class="space-y-2">
                                        @csrf
                                        <input type="hidden" name="group_id" value="{{ $group->id }}" />
                                        <x-ui.input name="title" placeholder="عنوان پاسخ" />
                                        <x-ui.textarea name="body" rows="3" placeholder="متن پاسخ"></x-ui.textarea>
                                        <x-ui.button>افزودن پاسخ</x-ui.button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </details>
                @empty
                    <p class="text-gray-500">گروهی ثبت نشده است.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    
@endsection
