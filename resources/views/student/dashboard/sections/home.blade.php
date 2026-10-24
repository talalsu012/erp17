@php
    $currency = $student->currency ?? 'SDG';
    $money = fn ($amount) => number_format((float) $amount, 2).' '.$currency;
    $remaining = $student->remainingFees();
    $lastRecom = $terms->pluck('recom')->filter()->last();
@endphp

@if ($remaining > 0)
    <div class="flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm">
        <svg class="w-5 h-5 shrink-0 text-red-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
        <span class="flex-1 font-semibold text-red-800">
            {{ $student->hasClearedAccount() ? 'يوجد رصيد مستحق على حسابك' : 'تعليق مالي - رصيد مستحق، النتائج غير متاحة حتى تسوية الحساب' }}
        </span>
        <button type="button" class="font-semibold text-red-800 hover:underline" @click="show('finance')">عرض التفاصيل</button>
    </div>
@endif

<section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-center">
        <div class="rounded-lg bg-gray-100 p-5">
            <p class="font-semibold text-gray-900">المبلغ المستحق</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $money($remaining) }}</p>
            <button type="button" class="mt-2 text-sm font-semibold text-emerald-700 hover:underline" @click="show('finance')">عرض التفاصيل</button>
        </div>

        <div>
            <p class="text-sm font-semibold text-gray-700">إجمالي الرسوم</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $money($student->all_fees) }}</p>
        </div>
        <div>
            <p class="text-sm font-semibold text-gray-700">المتحصل</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $money($student->pay) }}</p>
        </div>
        <div>
            <p class="text-sm font-semibold text-gray-700">تخفيض الرسوم</p>
            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $student->discountPercent?->name_ar ?? '-' }}</p>
        </div>
    </div>
</section>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <x-student.stat label="المستوى" :value="$student->level?->name_ar ?? '-'" tone="blue" />
    <x-student.stat label="الدفعة" :value="$student->batch?->name_ar ?? '-'" />
    <x-student.stat label="حالة القيد" :value="$student->x_status?->name_ar ?? '-'" tone="green" />
    <x-student.stat label="آخر معدل تراكمي"
                    :value="$canViewResults && isset($lastRecom->cgpa) ? number_format($lastRecom->cgpa, 2) : '-'" />
</div>

<section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
    <h2 class="text-lg font-bold text-gray-900 mb-4">آخر الدفعات</h2>

    @if ($payments->isEmpty())
        <p class="text-sm text-gray-500">لا توجد دفعات مسجلة.</p>
    @else
        <ul class="divide-y divide-gray-100">
            @foreach ($payments->take(3) as $payment)
                <li class="flex items-center justify-between py-3 text-sm">
                    <span class="text-gray-700">
                        {{ $payment->bankdate ? \Illuminate\Support\Carbon::parse($payment->bankdate)->format('Y-m-d') : '' }} - {{ $payment->bank ?? '-' }}
                        <span class="text-gray-400">({{ $payment->voucher_no ?? '-' }})</span>
                    </span>
                    <span class="font-semibold text-gray-900">{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</span>
                </li>
            @endforeach
        </ul>
        <button type="button" class="mt-3 text-sm font-semibold text-emerald-700 hover:underline" @click="show('payments')">عرض كل المدفوعات</button>
    @endif
</section>
