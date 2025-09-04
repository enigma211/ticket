@extends(request()->routeIs('admin.*') ? 'layouts.admin' : 'layouts.app')

@section('content')
    @php
        $daysStr = (string) settings('working_days', 'sat,sun,mon,tue,wed,thu');
        $daysArr = array_filter(array_map(fn($d) => strtolower(trim($d)), explode(',', $daysStr)));
        $start = (string) settings('working_start_time', '09:00');
        $end = (string) settings('working_end_time', '17:00');
        $now = now();
        $dayKey = strtolower($now->format('D'));
        $withinDay = in_array($dayKey, $daysArr);
        $timeNow = $now->format('H:i');
        $withinTime = $timeNow >= $start && $timeNow <= $end;
        $canBySchedule = $withinDay && $withinTime;
        $allowSubmission = settings('allow_user_submission', true) && $canBySchedule;
    @endphp

    @php $isAdminView = auth()->check() && (auth()->user()->is_agent || auth()->user()->is_superadmin) && request()->routeIs('admin.*'); @endphp

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

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">لیست تیکت‌ها</h1>
        <div class="flex items-center gap-3">
            @if(auth()->check() && (auth()->user()->is_agent || auth()->user()->is_superadmin) && request()->routeIs('admin.*'))
                <form method="GET" action="{{ route('admin.tickets.index') }}" class="flex items-center gap-2">
                    <x-ui.input type="number" name="tracking_code" value="{{ request('tracking_code') }}" placeholder="کد پیگیری" class="w-36" />
                    <x-ui.button>جستجو</x-ui.button>
                </form>
            @endif
            {{-- Public create removed --}}
        </div>
    </div>

    

    @if (session('status'))
        <x-ui.alert type="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    @if (!request()->routeIs('admin.*') && !$allowSubmission)
        <x-ui.alert type="warning" class="mb-6">در حال حاضر به علت زمان‌بندی و تنظیمات سیستم، قادر به ارسال پیام نیستید. لطفاً بعداً مراجعه نمایید.</x-ui.alert>
    @endif

    @if ($errors->has('error') || $errors->has('general'))
        <x-ui.alert type="error" class="mb-6">{{ $errors->first('error') ?: $errors->first('general') }}</x-ui.alert>
    @endif

    @php /* $isAdminView computed above for layout purposes */ @endphp
    @if($isAdminView)
        <div class="mb-4 flex items-end gap-4 flex-wrap">
            <form method="POST" action="{{ route('admin.tickets.bulk') }}" id="bulk_form" class="flex items-end gap-3">
                @csrf
                <div>
                    <label class="block text-sm mb-1">اقدام گروهی</label>
                    <select name="action" id="bulk_action" class="rounded border-gray-300 focus:border-primary focus:ring-primary" form="bulk_form">
                        <option value="">انتخاب...</option>
                        <option value="assign">ارجاع به کارشناس</option>
                        @if(auth()->user()->is_superadmin)
                            <option value="delete">حذف دائمی انتخاب‌شده‌ها</option>
                        @endif
                    </select>
                </div>
                <div id="bulk_assignee_wrap">
                    <label class="block text-sm mb-1">انتخاب مقصد</label>
                    <select name="assigned_to" id="bulk_assignee" class="w-64 rounded border-gray-300 focus:border-primary focus:ring-primary" form="bulk_form">
                        <option value="">انتخاب...</option>
                        @foreach(\App\Models\User::where(function($q){ $q->where('is_agent', true)->orWhere('is_superadmin', true);})->orderBy('first_name')->orderBy('last_name')->get() as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->full_name ?? $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button id="bulk_submit" type="submit" class="px-4 py-2 rounded bg-blue-600 text-white" form="bulk_form" disabled>اجرای اقدام</button>
                </div>
            </form>
            <form method="GET" action="{{ route('admin.tickets.index') }}" class="flex items-end gap-2">
                <div>
                    <label class="block text-sm mb-1">فیلتر وضعیت</label>
                    @php $st = request('status'); @endphp
                    <select name="status" class="rounded border-gray-300 focus:border-primary focus:ring-primary">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="new" @selected($st==='new')>جدید</option>
                        <option value="open" @selected($st==='open')>باز</option>
                        <option value="awaiting_user" @selected($st==='awaiting_user')>پاسخ داده شده</option>
                        <option value="closed" @selected($st==='closed')>بسته</option>
                        <option value="auto_closed" @selected($st==='auto_closed')>بسته خودکار</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1">آرشیو</label>
                    @php $ar = request('archived'); @endphp
                    <select name="archived" class="rounded border-gray-300 focus:border-primary focus:ring-primary">
                        <option value="">نمایش همه</option>
                        <option value="hide" @selected($ar==='hide')>مخفی‌کردن آرشیوی‌ها</option>
                        <option value="only" @selected($ar==='only')>فقط آرشیوی‌ها</option>
                    </select>
                </div>
                <div class="pt-6">
                    <button class="px-3 py-2 rounded bg-gray-800 text-white">اعمال فیلتر</button>
                </div>
            </form>
        </div>
    @endif

    <x-ui.table>
        <x-slot name="header">
            <tr>
                @if($isAdminView)
                    <th class="p-3"><input type="checkbox" id="check_all"></th>
                @endif
                <th class="p-3 text-right font-medium text-gray-700">عنوان</th>
                <th class="p-3 text-right font-medium text-gray-700">وضعیت</th>
                <th class="p-3 text-right font-medium text-gray-700">کد پیگیری</th>
                <th class="p-3 text-right font-medium text-gray-700">تاریخ بروزرسانی</th>
                @if(auth()->check() && (auth()->user()->is_agent || auth()->user()->is_superadmin))
                    <th class="p-3 text-right font-medium text-gray-700">درخواست‌کننده</th>
                    <th class="p-3 text-right font-medium text-gray-700">دپارتمان</th>
                    <th class="p-3 text-right font-medium text-gray-700">ارجاع به</th>
                @endif
            </tr>
        </x-slot>

        @forelse ($tickets as $ticket)
            @php $rowSpammer = $isAdminView && ($ticket->user && $ticket->user->is_spammer); @endphp
            <tr class="border-t {{ $rowSpammer ? 'bg-red-50 hover:bg-red-50 border-red-200' : 'hover:bg-gray-50' }} {{ $ticket->archived_at ? 'opacity-75' : '' }}">
                @if($isAdminView)
                    <td class="p-3"><input type="checkbox" name="ids[]" value="{{ $ticket->id }}" class="row-check" form="bulk_form"></td>
                @endif
                <td class="p-3">
                    @php $showRoute = request()->routeIs('admin.*') ? route('admin.tickets.show', $ticket) : route('tickets.show', $ticket); @endphp
                    <a href="{{ $showRoute }}" class="text-primary hover:underline font-medium">{{ $ticket->title }}</a>
                    @if($ticket->archived_at)
                        <span class="ml-2 inline-flex items-center gap-1 text-amber-700 bg-amber-50 px-2 py-0.5 rounded text-xs align-middle">آرشیو</span>
                    @endif
                </td>
                <td class="p-3">
                    @php
                        $statusColors = ['open' => 'text-yellow-600', 'awaiting_user' => 'text-blue-600', 'closed' => 'text-gray-600', 'auto_closed' => 'text-gray-500'];
                    @endphp
                    <span class="{{ $statusColors[$ticket->status] ?? 'text-gray-600' }}">
                        {{ $ticket->status_label }}
                    </span>
                    @if($ticket->archived_at)
                        <span class="ml-2 text-amber-600">• آرشیو</span>
                    @endif
                </td>
                <td class="p-3">{{ $ticket->tracking_code }}</td>
                <td class="p-3 text-gray-600">@jdateOffset($ticket->updated_at)</td>
                @if(auth()->check() && (auth()->user()->is_agent || auth()->user()->is_superadmin))
                    <td class="p-3">
                        @php $fullName = (($ticket->user->first_name ?? null) || ($ticket->user->last_name ?? null))
                            ? trim(($ticket->user->first_name ?? '').' '.($ticket->user->last_name ?? ''))
                            : ($ticket->user->name ?? '—'); @endphp
                        <a href="{{ route('admin.users.tickets', $ticket->user) }}" class="text-primary hover:underline">{{ $fullName }}</a>
                        @if($ticket->user && $ticket->user->is_spammer)
                            <span class="mr-2 inline-flex items-center gap-1 text-red-700 bg-red-50 px-2 py-0.5 rounded text-xs align-middle">مزاحم</span>
                        @endif
                    </td>
                    <td class="p-3">{{ $ticket->department->name ?? '—' }}</td>
                    <td class="p-3">{{ $ticket->assignee?->full_name ?? $ticket->assignee?->name ?? '—' }}</td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="4" class="p-8 text-center text-gray-500">
                    هیچ تیکتی ثبت نشده است.
                </td>
            </tr>
        @endforelse
    </x-ui.table>

    @if($isAdminView)
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@babakhani/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@babakhani/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const checkAll = document.getElementById('check_all');
                const rowChecks = document.querySelectorAll('.row-check');
                const form = document.getElementById('bulk_form');
                if (checkAll) {
                    checkAll.addEventListener('change', function () {
                        rowChecks.forEach(cb => cb.checked = checkAll.checked);
                        updateSubmitState();
                    });
                }
                rowChecks.forEach(cb => cb.addEventListener('change', updateSubmitState));
                const actionSel = document.getElementById('bulk_action');
                const assigneeWrap = document.getElementById('bulk_assignee_wrap');
                const assigneeSel = document.getElementById('bulk_assignee');
                const submitBtn = document.getElementById('bulk_submit');
                function toggleAssignee() {
                    const show = actionSel.value === 'assign';
                    assigneeWrap.style.display = show ? '' : 'none';
                    if (assigneeSel) assigneeSel.disabled = !show;
                    updateSubmitState();
                }
                function anyChecked() {
                    for (const cb of rowChecks) if (cb.checked) return true;
                    return false;
                }
                function updateSubmitState() {
                    if (!submitBtn) return;
                    const action = actionSel ? actionSel.value : '';
                    if (!anyChecked() || !action) { submitBtn.disabled = true; return; }
                    if (action === 'assign') {
                        submitBtn.disabled = !(assigneeSel && assigneeSel.value);
                    } else {
                        submitBtn.disabled = false;
                    }
                }
                if (actionSel) {
                    actionSel.addEventListener('change', toggleAssignee);
                    // initial state: placeholder selected, hide assignee and disable submit
                    toggleAssignee();
                }
                if (assigneeSel) {
                    assigneeSel.addEventListener('change', updateSubmitState);
                }
                updateSubmitState();
            });
            // Init Persian datepickers (requires jQuery)
            (function initJalali(){
                if (window.jQuery && typeof jQuery.fn.persianDatepicker === 'function') {
                    jQuery('.jalali-picker').persianDatepicker({
                        format: 'YYYY/MM/DD',
                        initialValue: false,
                        autoClose: true,
                        calendar: { persian: { locale: 'fa' } }
                    });
                }
            })();
        </script>
    @endif

    @if ($tickets->hasPages())
        <div class="mt-6">
            {{ $tickets->appends(request()->only('tracking_code','from_j','to_j'))->links() }}
        </div>
    @endif

    @if(!$isAdminView)
        </section>
    </div>
    @endif
@endsection