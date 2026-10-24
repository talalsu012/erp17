@php
    $currency = $student->currency ?? 'SDG';
    $money = fn ($amount) => number_format((float) $amount, 2).' '.$currency;
    $remaining = $student->remainingFees();

    $feeItems = [
        'fees' => 'الرسوم الدراسية',
        'reg_fees' => 'رسوم التسجيل',
        'fees1' => 'الكورس',
        'fees2' => 'امتحان',
        'fees3' => 'بطاقة',
        'fees4' => 'رسم إضافي 1',
        'fees5' => 'دعم',
        'fees6' => 'تقديم',
        'fees7' => 'معاينة',
        'fees8' => 'أخرى',
        'fees9' => 'يدوي',
        'fees10' => 'رسم إضافي 4',
    ];

    $installments = [
        'pay1' => 'القسط الأول',
        'pay2' => 'القسط الثاني',
        'pay3' => 'القسط الثالث',
        'pay4' => 'القسط الرابع',
    ];
@endphp

<x-student.card id="finance" title="الموقف المالي" class="scroll-mt-16">
    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-student.stat label="إجمالي الرسوم" :value="$money($student->all_fees)" tone="blue" />
        <x-student.stat label="المتحصل" :value="$money($student->pay)" tone="green" />
        <x-student.stat label="المتبقي" :value="$money($remaining)" :tone="$remaining > 0 ? 'red' : 'green'" />
        <x-student.stat label="تخفيض الرسوم" :value="$student->discountPercent?->name_ar ?? '-'" />
    </dl>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
        <x-student.item label="العملة" :value="$currency" />
        <x-student.item label="نظام السداد" :value="$student->installment?->name_ar" />
        <x-student.item label="حالة الحساب" :value="$student->hasClearedAccount() ? 'مسوّى - يسمح بعرض النتيجة' : 'غير مسوّى'" />
    </dl>

    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mt-6 mb-3">تفاصيل الرسوم</h4>
    <dl class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($feeItems as $field => $label)
            @if ((float) $student->{$field} > 0)
                <x-student.item :label="$label" :value="$money($student->{$field})" />
            @endif
        @endforeach
    </dl>

    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mt-6 mb-3">الأقساط المسددة</h4>
    <dl class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($installments as $field => $label)
            <x-student.item :label="$label" :value="$money($student->{$field})" />
        @endforeach
    </dl>
</x-student.card>
