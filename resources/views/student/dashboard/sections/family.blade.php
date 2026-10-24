<x-student.card id="family" title="بيانات الأسرة" class="scroll-mt-16">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">الأب / ولي الأمر</h4>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-student.item label="الاسم" :value="$student->parent" />
                <x-student.item label="على قيد الحياة" :value="is_null($student->parent_alive) ? null : ($student->parent_alive ? 'نعم' : 'لا')" />
                <x-student.item label="المهنة" :value="$student->parent_job" />
                <x-student.item label="رقم التواصل" :value="$student->parent_contacts" :ltr="true" />
                <x-student.item label="العنوان" :value="$student->parent_address" />
            </dl>
        </div>

        <div>
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">الأم</h4>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-student.item label="الاسم" :value="$student->mother" />
                <x-student.item label="على قيد الحياة" :value="is_null($student->mother_alive) ? null : ($student->mother_alive ? 'نعم' : 'لا')" />
                <x-student.item label="المهنة" :value="$student->mother_job" />
                <x-student.item label="رقم التواصل" :value="$student->mother_contacts" :ltr="true" />
                <x-student.item label="العنوان" :value="$student->mother_address" />
            </dl>
        </div>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
        <x-student.item label="عدد الإخوة" :value="$student->brothers" />
    </dl>
</x-student.card>
