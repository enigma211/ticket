@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">دپارتمان‌ها</h1>
        <a href="{{ route('admin.departments.create') }}" class="px-3 py-2 bg-primary text-white rounded">ایجاد دپارتمان</a>
    </div>

    <x-ui.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-600">
                    <th class="p-2 text-right">نام</th>
                    <th class="p-2 text-right">وضعیت</th>
                    <th class="p-2 text-right">اقدامات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dep)
                    <tr class="border-t">
                        <td class="p-2">{{ $dep->name }}</td>
                        <td class="p-2">{{ $dep->active ? 'فعال' : 'غیرفعال' }}</td>
                        <td class="p-2 flex items-center gap-3">
                            <a href="{{ route('admin.departments.edit', $dep) }}" class="text-primary">ویرایش</a>
                            <form method="POST" action="{{ route('admin.departments.destroy', $dep) }}">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600" onclick="return confirm('حذف دپارتمان؟')">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="p-6 text-center text-gray-500">دپارتمانی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $departments->links() }}</div>
    </x-ui.card>
@endsection


