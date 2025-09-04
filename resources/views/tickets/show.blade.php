@extends(request()->routeIs('admin.*') ? 'layouts.admin' : 'layouts.app')

@section('content')
    @php
        $statusMap = ['open' => 'باز', 'awaiting_user' => 'پاسخ داده شده', 'closed' => 'بسته', 'auto_closed' => 'بسته خودکار'];
        $isAgent = auth()->check() && (auth()->user()->is_agent || auth()->user()->is_superadmin);
        $canned = $isAgent ? \App\Models\CannedGroup::with('replies')->orderBy('name')->get() : collect();
    @endphp
    @php $gmtOffset = config('app.gmt_offset_minutes'); @endphp
    @php $isAdminView = request()->routeIs('admin.*'); @endphp
    @php $backRoute = $isAdminView ? route('admin.tickets.index') : route('tickets.index'); @endphp

    

    @if(!$isAdminView)
    <div class="grid grid-cols-12 gap-6">
        <aside class="col-span-12 md:col-span-3">
            <x-ui.card>
                <nav class="px-2 py-3 space-y-1">
                    <a href="{{ route('tickets.index') }}" class="block px-3 py-2 rounded-lg flex items-center gap-2 transition-colors @if(request()->routeIs('tickets.index')) bg-gray-50 text-primary font-medium border border-gray-200 @else text-gray-700 hover:bg-gray-50 @endif">
                        <svg class="w-4 h-4 @if(request()->routeIs('tickets.index')) text-primary @else text-gray-400 group-hover:text-gray-500 @endif" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
                        <span class="text-sm">تیکت‌ها</span>
                    </a>
                    <a href="{{ route('user.profile.edit') }}" class="block px-3 py-2 rounded-lg flex items-center gap-2 transition-colors @if(request()->routeIs('user.profile.*')) bg-gray-50 text-primary font-medium border border-gray-200 @else text-gray-700 hover:bg-gray-50 @endif">
                        <svg class="w-4 h-4 @if(request()->routeIs('user.profile.*')) text-primary @else text-gray-400 group-hover:text-gray-500 @endif" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span class="text-sm">پروفایل من</span>
                    </a>
                </nav>
            </x-ui.card>
        </aside>
        <section class="col-span-12 md:col-span-9">
    @endif
        <div class="grid grid-cols-12 gap-6">
        <!-- Info card column -->
        <div class="col-span-12 lg:col-span-4 lg:order-2">
            <div class="space-y-4 sticky top-20">
                <div class="flex items-center justify-end min-h-[64px]">
                    <a href="{{ $backRoute }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 shadow-sm">
                        <svg class="w-4 h-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                        <span>بازگشت به لیست</span>
                    </a>
                </div>
                <x-ui.card>
                    @if($ticket->user)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">نام کاربر</span>
                            <span class="text-sm text-gray-800 flex items-center gap-2">
                                {{ trim(($ticket->user->first_name ?? '').' '.($ticket->user->last_name ?? '')) ?: $ticket->user->name }}
                                @if($isAgent && $ticket->user->is_spammer)
                                    <span class="inline-flex items-center gap-1 text-red-700 bg-red-50 px-2 py-0.5 rounded text-xs">مزاحم</span>
                                @endif
                            </span>
                        </div>
                        @if($isAgent && $ticket->user->is_spammer)
                            <div class="mt-2 text-xs text-red-700 bg-red-50 px-2 py-1 rounded inline-block">این کاربر مزاحم علامت‌گذاری شده است</div>
                        @endif
                        <div class="mt-3 flex items-center justify-between text-sm text-gray-600">
                            <span>شماره تماس</span>
                            <span class="font-mono">{{ $ticket->user->mobile ?? '—' }}</span>
                        </div>
                        <div class="my-3 border-t"></div>
                    @endif
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">وضعیت</div>
                        <div class="text-sm font-semibold">{{ $ticket->status_label }}</div>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-sm text-gray-600">
                        <span>دپارتمان</span>
                        <span class="text-gray-800">{{ $ticket->department->name ?? '—' }}</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-sm text-gray-600">
                        <span>کد رهگیری</span>
                        <span class="font-mono">{{ $ticket->tracking_code }}</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-sm text-gray-600">
                        <span>ایجاد</span>
                        @php $tCreated = $gmtOffset !== null ? $ticket->created_at->copy()->addMinutes($gmtOffset) : $ticket->created_at; @endphp
                        <span>{{ jdate($tCreated)->format('Y/m/d H:i') }}</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-sm text-gray-600">
                        <span>بروزرسانی</span>
                        <span>{{ jdate($ticket->updated_at)->ago() }}</span>
                    </div>
                    
                    @if($isAgent)
                        <div class="mt-4 flex items-center gap-2">
                            @if($ticket->status !== 'closed')
                                <form method="POST" action="{{ route('admin.tickets.close', $ticket) }}">
                                    @csrf
                                    <button class="px-3 py-1.5 rounded bg-red-600 text-white">بستن</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.tickets.reopen', $ticket) }}">
                                    @csrf
                                    <button class="px-3 py-1.5 rounded bg-green-600 text-white">بازکردن</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </x-ui.card>

                @if($ticket->assignments()->with(['fromUser','toUser'])->latest()->first())
                    @php $lastAssign = $ticket->assignments()->with(['fromUser','toUser'])->latest()->first(); @endphp
                    <x-ui.card>
                        <div class="text-sm text-amber-700 bg-amber-50 p-3 rounded">
                            این تیکت توسط {{ $lastAssign->fromUser?->full_name ?? $lastAssign->fromUser?->name ?? 'سیستم' }} به {{ $lastAssign->toUser?->full_name ?? $lastAssign->toUser?->name }} ارجاع شد.
                            @if($lastAssign->note)
                                <div class="mt-2 text-amber-800">یادداشت ارجاع: {{ $lastAssign->note }}</div>
                            @endif
                        </div>
                    </x-ui.card>
                @endif

                @if($isAgent)
                    <x-ui.card>
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">ارجاع</h3>
                        <form method="POST" action="{{ route('admin.tickets.assign', $ticket) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">ارجاع به کارشناس</label>
                                <select name="assigned_to" class="w-full rounded border-gray-300 focus:border-primary focus:ring-primary">
                                    @foreach(\App\Models\User::where(function($q){ $q->where('is_agent', true)->orWhere('is_superadmin', true);})->orderBy('first_name')->orderBy('last_name')->get() as $agent)
                                        <option value="{{ $agent->id }}" @selected($ticket->assigned_to == $agent->id)>
                                            {{ $agent->full_name ?? $agent->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">یادداشت</label>
                                <x-ui.input type="text" name="note" placeholder="توضیح کوتاه برای ارجاع" />
                            </div>
                            <div class="flex justify-end">
                                <button class="px-3 py-2 rounded bg-blue-600 text-white">ارجاع</button>
                            </div>
                        </form>
                    </x-ui.card>
                @endif

                
            </div>
        </div>

        <!-- Messages column -->
        <div class="col-span-12 lg:col-span-8 lg:order-1">
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-primary">{{ $ticket->title }}</h1>
                    <div class="mt-1 text-sm text-gray-600 flex items-center gap-2">
                        <span class="inline-flex items-center gap-1">کد رهگیری: <span class="font-mono">{{ $ticket->tracking_code }}</span></span>
                        <span class="text-gray-300">|</span>
                        <span class="inline-flex items-center gap-1">وضعیت: <span class="font-medium">{{ $ticket->status_label }}</span></span>
                        @if($ticket->assignee)
                            <span class="text-gray-300">|</span>
                            <span class="ml-2 inline-flex items-center gap-1 text-blue-700 bg-blue-50 px-2 py-0.5 rounded">
                                ارجاع به: {{ $ticket->assignee->full_name ?? $ticket->assignee->name }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <x-ui.card>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">پیام‌ها</h2>
                    @if($isAgent)
                        <form method="POST" action="{{ route('admin.tickets.print', $ticket) }}" id="print_form" class="flex items-center gap-2">
                            @csrf
                            <button class="px-3 py-2 rounded bg-gray-900 text-white">چاپ پیام‌های انتخاب‌شده</button>
                        </form>
                    @endif
                </div>
                <div class="space-y-4">
                    @forelse ($messages as $message)
                        @php $isStaffMsg = $message->user && ($message->user->is_agent || $message->user->is_superadmin); @endphp
                        <div class="flex {{ $isStaffMsg ? 'justify-start' : 'justify-end' }}">
                            @php $isFromSpammer = $isAgent && !$isStaffMsg && ($message->user?->is_spammer); @endphp
                            <div class="max-w-[85%] w-fit {{ $isStaffMsg ? 'bg-blue-50' : ($isFromSpammer ? 'bg-red-50' : 'bg-gray-100') }} border {{ $isStaffMsg ? 'border-blue-100' : ($isFromSpammer ? 'border-red-200' : 'border-gray-200') }} rounded-2xl p-3">
                                <div class="flex items_center justify_between mb-1">
                                    <div class="flex items-center gap-2">
                                        @if($isAgent)
                                            <input type="checkbox" name="message_ids[]" value="{{ $message->id }}" form="print_form" class="rounded border-gray-300">
                                        @endif
                                        <span class="text-xs text-gray-600 flex items-center gap-2">
                                            {{ $message->user->name }}
                                            @if($isFromSpammer)
                                                <span class="inline-flex items-center gap-1 text-red-700 bg-red-100 px-2 py-0.5 rounded">مزاحم</span>
                                            @endif
                                        </span>
                                    </div>
                                    @php $mCreated = $gmtOffset !== null ? $message->created_at->copy()->addMinutes($gmtOffset) : $message->created_at; @endphp
                                    <span class="text-xs text-gray-500">{{ jdate($mCreated)->format('Y/m/d H:i') }}</span>
                                </div>
                                <div class="whitespace-pre-line text-gray-800 text-sm">{{ $message->body }}</div>
                                @if($isAgent && $isStaffMsg && (auth()->user()->is_superadmin || auth()->id() === $message->user_id))
                                    <div class="mt-2 flex items-center gap-3">
                                        <button type="button" class="text-xs text-blue-700 hover:underline" onclick="document.getElementById('edit_msg_{{ $message->id }}').classList.toggle('hidden')">ویرایش</button>
                                        <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('این پیام حذف شود؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:underline">حذف</button>
                                        </form>
                                    </div>
                                    <form method="POST" action="{{ route('admin.messages.update', $message) }}" class="mt-2 hidden" id="edit_msg_{{ $message->id }}">
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.textarea name="body" rows="3">{{ $message->body }}</x-ui.textarea>
                                        <div class="mt-2 flex justify-end gap-2">
                                            <button type="button" class="text-xs text-gray-600" onclick="document.getElementById('edit_msg_{{ $message->id }}').classList.add('hidden')">انصراف</button>
                                            <x-ui.button class="!px-2 !py-1 text-xs">ذخیره</x-ui.button>
                                        </div>
                                    </form>
                                @endif
                                @if($message->attachments->count())
                                    <div class="mt-2 pt-2 border-t">
                                        <div class="space-y-1">
                                            @foreach($message->attachments as $attachment)
                                                <div class="flex items-center justify-between text-xs">
                                                    <a href="{{ route('attachments.download', $attachment) }}" class="text-primary hover:underline">{{ $attachment->original_name }}</a>
                                                    <form method="POST" action="{{ route('attachments.destroy', $attachment) }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('آیا مطمئن هستید؟')">حذف</button>
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">هنوز پیامی ثبت نشده است.</p>
                    @endforelse
                    @if ($messages->hasPages())
                        <div class="mt-4">{{ $messages->links() }}</div>
                    @endif
                </div>
            </x-ui.card>

            @if($ticket->status !== 'closed' || $isAgent)
                <x-ui.card>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">پاسخ</h2>
                    <form method="POST" action="{{ route('messages.store') }}" @if(!$isAgent) enctype="multipart/form-data" @endif class="space-y-4">
                        @csrf
                        <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                        
                        <div>
                            <label for="body" class="block text-sm font-medium text-gray-700 mb-2">متن پیام</label>
                            <x-ui.textarea id="body" name="body" rows="5" required>{{ old('body') }}</x-ui.textarea>
                            @error('body')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @if(!$isAgent)
                            <div>
                                <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">پیوست‌ها (اختیاری)</label>
                                <input id="attachments" name="attachments[]" type="file" multiple accept="{{ '.' . str_replace(',', ',.', (string) settings('allowed_mimes', 'jpg,jpeg,png,pdf')) }}" class="w-full rounded border-gray-300 focus:border-primary focus:ring-primary" />
                                <p class="mt-1 text-xs text-gray-500">حداکثر {{ (int) settings('max_upload_mb', 5) }} مگابایت برای هر فایل • انواع مجاز: {{ (string) settings('allowed_mimes', 'jpg,jpeg,png,pdf') }}</p>
                                @error('attachments.*')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                        @if($isAgent && $canned->count())
                            <div>
                                <button type="button" id="open_canned" class="px-3 py-2 rounded bg-amber-500 text-white text-sm hover:bg-amber-600 shadow-sm">پاسخ‌های آماده</button>
                            </div>
                            <div id="canned_modal" class="fixed inset-0 z-50 hidden">
                                <div id="canned_overlay" class="absolute inset-0 bg-black/30"></div>
                                <div class="fixed inset-y-10 left-1/2 -translate-x-1/2 w-[92vw] max-w-3xl bg-white rounded-lg shadow-lg flex flex-col">
                                    <div class="flex items-center justify-between px-4 py-3 border-b">
                                        <div class="font-semibold">پاسخ‌های آماده</div>
                                        <button type="button" id="close_canned" class="text-sm text-gray-500 hover:text-gray-700">بستن</button>
                                    </div>
                                    <div class="p-4 border-b">
                                        <input id="canned_search" type="text" class="w-full rounded border-gray-300 focus:border-primary focus:ring-primary" placeholder="جستجو در عنوان یا متن پاسخ...">
                                    </div>
                                    <div id="canned_list" class="p-4 overflow-y-auto flex-1 space-y-4">
                                        @foreach($canned as $group)
                                            <div class="space-y-2 canned-group">
                                                <div class="text-xs font-semibold text-gray-700">{{ $group->name }}</div>
                                                @foreach($group->replies as $reply)
                                                    <div class="border border-amber-200 bg-amber-50/40 hover:bg-amber-50 rounded p-3 canned-row" data-title="{{ $reply->title }}" data-body='@json($reply->body)'>
                                                        <div class="flex items-center justify-between gap-3">
                                                            <div class="font-medium text-gray-900 text-sm">{{ $reply->title }}</div>
                                                            <div class="flex items-center gap-2">
                                                                <button type="button" class="px-2 py-1 text-xs rounded bg-blue-600 text-white canned-act">درج</button>
                                                            </div>
                                                        </div>
                                                        <div class="mt-2 text-xs text-gray-600 whitespace-pre-line">{{ \Illuminate\Support\Str::limit($reply->body, 160) }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="flex justify-end">
                            <x-ui.button type="submit">ارسال پیام</x-ui.button>
                        </div>
                    </form>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const btnOpen = document.getElementById('open_canned');
                            const modal = document.getElementById('canned_modal');
                            const btnClose = document.getElementById('close_canned');
                            const overlay = document.getElementById('canned_overlay');
                            const textarea = document.getElementById('body');
                            const search = document.getElementById('canned_search');
                            const rows = Array.from(document.querySelectorAll('#canned_list .canned-row'));
                            const groups = Array.from(document.querySelectorAll('#canned_list .canned-group'));

                            function openModal(){ if(modal) modal.classList.remove('hidden'); if(search){ setTimeout(()=>search.focus(), 0); } }
                            function closeModal(){ if(modal) modal.classList.add('hidden'); if(search){ search.value=''; filter(''); } }
                            function insertAtCursor(field, text){
                                if (!field) return;
                                const start = field.selectionStart ?? field.value.length;
                                const end = field.selectionEnd ?? field.value.length;
                                const before = field.value.substring(0, start);
                                const after = field.value.substring(end);
                                field.value = before + text + after;
                                const caret = start + text.length;
                                field.setSelectionRange(caret, caret);
                                field.focus();
                            }
                            function filter(q){
                                const query = (q || '').trim().toLowerCase();
                                rows.forEach(r => {
                                    const title = (r.getAttribute('data-title') || '').toLowerCase();
                                    const body = (JSON.parse(r.getAttribute('data-body') || '""') || '').toLowerCase();
                                    const show = !query || title.includes(query) || body.includes(query);
                                    if (show) r.classList.remove('hidden'); else r.classList.add('hidden');
                                });
                                groups.forEach(g => {
                                    const any = Array.from(g.querySelectorAll('.canned-row')).some(x => !x.classList.contains('hidden'));
                                    if (any) g.classList.remove('hidden'); else g.classList.add('hidden');
                                });
                            }

                            if (btnOpen) btnOpen.addEventListener('click', openModal);
                            if (btnClose) btnClose.addEventListener('click', closeModal);
                            if (overlay) overlay.addEventListener('click', closeModal);
                            document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeModal(); });
                            if (search) search.addEventListener('input', function(){ filter(this.value); });

                            document.addEventListener('click', function(e){
                                const t = e.target;
                                if (t && t.classList && t.classList.contains('canned-act')){
                                    const row = t.closest('.canned-row');
                                    const bodyJson = row ? row.getAttribute('data-body') : '""';
                                    let text = '';
                                    try { text = JSON.parse(bodyJson) || ''; } catch(_) { text = ''; }
                                    insertAtCursor(textarea, text);
                                    closeModal();
                                }
                            });
                        });
                    </script>
                </x-ui.card>
            @else
                <x-ui.card>
                    <div class="text-sm text-gray-600">این تیکت بسته است. فقط عوامل می‌توانند پیام جدید ثبت کنند.</div>
                </x-ui.card>
            @endif
        </div>
        </div>

    @if(!$isAdminView)
        </section>
    </div>
    @endif
@endsection