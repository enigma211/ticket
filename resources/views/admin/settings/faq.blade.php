@extends('layouts.admin')

@section('content')
    <h1 class="text-xl font-bold mb-4">سوالات متداول</h1>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.settings.faq.update') }}" class="space-y-4" id="faq_form">
            @csrf
            @method('PATCH')

            <p class="text-sm text-gray-600">سوال و پاسخ را وارد کنید. برای افزودن مورد جدید روی «افزودن مورد» بزنید.</p>

            <div id="faq_list" class="space-y-4 mt-2">
                @php $faqs = old('faq', $setting->faq_json ?? []); @endphp
                @foreach($faqs as $i => $item)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm mb-1">سوال</label>
                            <x-ui.input type="text" name="faq[{{ $i }}][q]" value="{{ $item['q'] ?? '' }}" />
                        </div>
                        <div>
                            <label class="block text-sm mb-1">پاسخ</label>
                            <x-ui.textarea name="faq[{{ $i }}][a]" rows="2">{{ $item['a'] ?? '' }}</x-ui.textarea>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center gap-3 mt-3">
                <button type="button" id="faq_add" class="px-3 py-2 rounded bg-gray-800 text-white">افزودن مورد</button>
                <x-ui.button>ذخیره</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const list = document.getElementById('faq_list');
            const add = document.getElementById('faq_add');
            add.addEventListener('click', function(){
                const index = list.querySelectorAll('textarea').length;
                const wrapper = document.createElement('div');
                wrapper.className = 'grid grid-cols-1 md:grid-cols-2 gap-3';
                wrapper.innerHTML = `
                    <div>
                        <label class="block text-sm mb-1">سوال</label>
                        <input type="text" name="faq[${index}][q]" class="w-full rounded border-gray-300 focus:border-primary focus:ring-primary" />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">پاسخ</label>
                        <textarea name="faq[${index}][a]" rows="2" class="w-full rounded border-gray-300 focus:border-primary focus:ring-primary"></textarea>
                    </div>
                `;
                list.appendChild(wrapper);
            });
        });
    </script>
@endsection


