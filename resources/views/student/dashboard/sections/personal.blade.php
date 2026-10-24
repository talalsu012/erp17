<x-student.card id="personal" title="البيانات الشخصية" class="scroll-mt-16">
    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <x-student.item label="الجنس" :value="$student->gender?->name_ar" />
        <x-student.item label="الجنسية" :value="$student->nationality?->name_ar" />
        <x-student.item label="تاريخ الميلاد" :value="$student->birth_date?->format('Y-m-d')" :ltr="true" />
        <x-student.item label="مكان الميلاد" :value="$student->birth_place" />
        <x-student.item label="رقم الهاتف" :value="$student->mobile" :ltr="true" />
        <x-student.item label="البريد الإلكتروني" :value="$student->email" :ltr="true" />
        <x-student.item label="الولاية" :value="$student->state?->name_ar" />
        <x-student.item label="المدينة" :value="$student->city" />
        <x-student.item label="المحلية" :value="$student->localty" />
        <x-student.item label="الحي" :value="$student->block" />
        <x-student.item label="رقم المنزل" :value="$student->home_no" />
        <x-student.item label="وسائل التواصل الاجتماعي" :value="$student->social_media" />
        <x-student.item label="الأنشطة" :value="$student->activites" />
        <x-student.item label="المهارات" :value="$student->skill" />
    </dl>
</x-student.card>
