@php
    $cardStatuses = [
        'active' => 'فعّالة',
        'inactive' => 'غير فعّالة',
        'lost' => 'مفقودة',
        'expired' => 'منتهية',
        'demo' => 'تجريبية',
    ];

    $decisions = ['accepted' => 'مقبول', 'rejected' => 'مرفوض', 'pending' => 'قيد الدراسة'];
    $medical = $student->medicalFitness;
    $formatDate = fn ($date): ?string => $date ? \Illuminate\Support\Carbon::parse($date)->format('Y-m-d') : null;
    $cardStatus = $student->card?->status instanceof \BackedEnum ? $student->card->status->value : $student->card?->status;
@endphp

<x-student.card id="documents" title="الوثائق والقرارات" class="scroll-mt-16">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div>
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">البطاقة الجامعية</h4>
            @if ($student->card)
                <dl class="grid grid-cols-1 gap-4">
                    <x-student.item label="رقم البطاقة" :value="$student->card->card_number" :ltr="true" />
                    <x-student.item label="تاريخ الإصدار" :value="$formatDate($student->card->issue_date)" :ltr="true" />
                    <x-student.item label="تاريخ الانتهاء" :value="$formatDate($student->card->expiry_date)" :ltr="true" />
                    <x-student.item label="الحالة" :value="$cardStatuses[$cardStatus] ?? $cardStatus" />
                </dl>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">لم تصدر بطاقة جامعية بعد.</p>
            @endif
        </div>

        <div>
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">قرار القبول والفحص الطبي</h4>
            @if ($medical)
                <dl class="grid grid-cols-1 gap-4">
                    <x-student.item label="اللياقة الطبية" :value="$medical->medical_fitness === 'fit' ? 'لائق' : 'غير لائق'" />
                    <x-student.item label="المقابلة بالكلية" :value="$medical->college_interview === 'passed' ? 'اجتاز' : 'لم يجتز'" />
                    <x-student.item label="القرار النهائي" :value="$decisions[$medical->final_decision] ?? $medical->final_decision" />
                    <x-student.item label="تاريخ القرار" :value="$formatDate($medical->decision_date)" :ltr="true" />
                </dl>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">لا يوجد قرار مسجل.</p>
            @endif
        </div>

        <div>
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">شهادة التخرج</h4>
            @if ($student->relationLoaded('certificate') && $student->certificate)
                <dl class="grid grid-cols-1 gap-4">
                    <x-student.item label="تاريخ التخرج" :value="$formatDate($student->certificate->alumia_date)" :ltr="true" />
                    <x-student.item label="التاريخ الهجري" :value="$student->certificate->hijri_date" />
                    <x-student.item label="رقم المحضر" :value="$student->certificate->meeting_no" />
                    <x-student.item label="ملاحظات" :value="$student->certificate->remarks" />
                </dl>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">لا توجد شهادة تخرج.</p>
            @endif
        </div>
    </div>
</x-student.card>
