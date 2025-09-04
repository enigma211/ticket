@extends('layouts.admin')

@section('content')
    @php($search = request('search'))
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">کاربران</h1>
        <div class="flex items-center gap-2">
            <form class="flex items-center gap-2" method="GET">
                <x-ui.input type="text" name="search" value="{{ $search }}" placeholder="جستجو..." />
                <x-ui.button>جستجو</x-ui.button>
            </form>
            @if(auth()->user()->is_superadmin)
                <a href="{{ route('admin.users.create') }}" class="px-3 py-2 bg-primary text-white rounded">ایجاد کاربر</a>
            @endif
        </div>
    </div>

    <x-ui.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-600">
                    <th class="p-2 text-right">نام و نام خانوادگی</th>
                    <th class="p-2 text-right">کد ملی</th>
                    <th class="p-2 text-right">موبایل</th>
                    <th class="p-2 text-right">ایمیل</th>
                    <th class="p-2 text-right">ادمین</th>
                    <th class="p-2 text-right">پشتیبان</th>
                    <th class="p-2 text-right">اقدامات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr class="border-t">
                        <td class="p-2">{{ (($user->first_name ?? null) || ($user->last_name ?? null)) ? trim(($user->first_name ?? '').' '.($user->last_name ?? '')) : ($user->name ?? '—') }}</td>
                        <td class="p-2">{{ $user->national_id ?? '—' }}</td>
                        <td class="p-2">{{ $user->mobile ?? '—' }}</td>
                        <td class="p-2">{{ $user->email }}</td>
                        <td class="p-2">{{ $user->is_superadmin ? 'بله' : 'خیر' }}</td>
                        <td class="p-2">{{ $user->is_agent ? 'بله' : 'خیر' }}</td>
                        <td class="p-2 flex items-center gap-3">
                            @if(auth()->user()->is_superadmin)
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-primary">ویرایش</a>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('آیا مطمئن هستید که می‌خواهید این کاربر را حذف کنید؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">حذف</button>
                                    </form>
                                @endif
                            @endif
                            <a href="{{ route('admin.users.tickets', $user) }}" class="text-primary">تیکت‌ها</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </x-ui.card>
@endsection
