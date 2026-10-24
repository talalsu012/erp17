<x-student.card id="recommendations" title="التوصيات والمعدلات" class="scroll-mt-16">
    @php($recomTerms = $terms->filter(fn ($term) => $term['recom']))

    @if (! $canViewResults)
        <p class="text-center text-gray-500 dark:text-gray-400 py-6">
            التوصيات غير متاحة حاليًا. يرجى مراجعة الشؤون المالية لتسوية الحساب أو مراجعة المسجل.
        </p>
    @elseif ($recomTerms->isEmpty())
        <p class="text-center text-gray-500 dark:text-gray-400 py-6">لا توجد توصيات مسجلة - راجع المسجل</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-right">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/40 text-gray-600 dark:text-gray-300">
                        <th class="px-3 py-2 font-semibold">المستوى</th>
                        <th class="px-3 py-2 font-semibold">الفصل</th>
                        <th class="px-3 py-2 font-semibold">الدور</th>
                        <th class="px-3 py-2 font-semibold">الساعات</th>
                        <th class="px-3 py-2 font-semibold">ناجح</th>
                        <th class="px-3 py-2 font-semibold">راسب</th>
                        <th class="px-3 py-2 font-semibold">غائب</th>
                        <th class="px-3 py-2 font-semibold">المعدل الفصلي</th>
                        <th class="px-3 py-2 font-semibold">المعدل التراكمي</th>
                        <th class="px-3 py-2 font-semibold">التوصية</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-900 dark:text-gray-100">
                    @foreach ($recomTerms as $term)
                        @php($recom = $term['recom'])
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                            <td class="px-3 py-2">{{ $term['level'] ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $term['semester'] }}</td>
                            <td class="px-3 py-2">{{ $term['exam_type'] === 2 ? 'الثاني' : 'الأول' }}</td>
                            <td class="px-3 py-2">{{ $recom->crch ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $recom->s_pass ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $recom->s_fail ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $recom->s_abs ?? '-' }}</td>
                            <td class="px-3 py-2">{{ isset($recom->gpa) ? number_format($recom->gpa, 2) : '-' }}</td>
                            <td class="px-3 py-2">{{ isset($recom->cgpa) ? number_format($recom->cgpa, 2) : '-' }}</td>
                            <td class="px-3 py-2 font-semibold">{{ $recom->recomName?->name_ar ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-student.card>
