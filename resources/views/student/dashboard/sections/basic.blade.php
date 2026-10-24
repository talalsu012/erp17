<x-student.card id="basic" title="البيانات الأساسية" class="scroll-mt-16">
    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <x-student.item label="الاسم بالعربية" :value="$student->name_ar" />
        <x-student.item label="الاسم بالإنجليزية" :value="$student->name_en" :ltr="true" />
        <x-student.item label="الرقم الجامعي" :value="$student->university_no" :ltr="true" />
        <x-student.item label="رقم الجلوس" :value="$student->indexno" :ltr="true" />
        <x-student.item label="الرقم الوطني" :value="$student->nationaltyno" :ltr="true" />
        <x-student.item label="حالة الطالب" :value="$student->status?->name_ar" />
        <x-student.item label="حالة القيد" :value="$student->x_status?->name_ar" />
    </dl>
</x-student.card>
