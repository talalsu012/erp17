<x-student.card id="academic" title="البيانات الأكاديمية" class="scroll-mt-16">
    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-student.item label="الحرم الجامعي" :value="$student->faculty?->campus?->name_ar" />
        <x-student.item label="الكلية" :value="$student->faculty?->name_ar" />
        <x-student.item label="القسم" :value="$student->department?->name_ar" />
        <x-student.item label="التخصص" :value="$student->special?->name_ar" />
        <x-student.item label="المستوى" :value="$student->level?->name_ar" />
        <x-student.item label="نظام الدراسة" :value="$student->stady?->name_ar" />
        <x-student.item label="نوع القبول" :value="$student->admission?->name_ar" />
        <x-student.item label="العام الأكاديمي" :value="$student->academicYear?->name_ar" />
        <x-student.item label="الدفعة" :value="$student->batch?->name_ar" />
    </dl>
</x-student.card>
