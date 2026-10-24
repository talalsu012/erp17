<x-student.card id="results" title="النتائج الأكاديمية" class="scroll-mt-16">
    @if (! $canViewResults)
        <p class="text-center text-gray-500 dark:text-gray-400 py-6">
            النتائج غير متاحة حاليًا. يرجى مراجعة الشؤون المالية لتسوية الحساب أو مراجعة المسجل.
        </p>
    @elseif ($terms->pluck('marks')->flatten()->isEmpty())
        <p class="text-center text-gray-500 dark:text-gray-400 py-6">لا توجد نتائج منشورة - راجع المسجل</p>
    @else
        @php($markTerms = $terms->filter(fn ($term) => $term['marks']->isNotEmpty())->values())

        <div class="space-y-8" x-data="{ termFilter: 'all' }">
            <div class="flex items-center gap-3 text-sm">
                <label for="term-filter" class="text-gray-500">الفصل الدراسي:</label>
                <select id="term-filter" x-model="termFilter" class="rounded-md border-gray-300 text-sm py-1.5">
                    <option value="all">كل الفصول</option>
                    @foreach ($markTerms as $index => $term)
                        <option value="{{ $index }}">
                            {{ $term['level'] ?? '-' }} / الفصل {{ $term['semester'] }}{{ $term['exam_type'] === 2 ? ' (الدور الثاني)' : '' }} / {{ $term['batch'] ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>

            @foreach ($markTerms as $index => $term)
                <div x-show="termFilter === 'all' || termFilter === '{{ $index }}'">
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
                        {{ $term['level'] ?? '-' }} / الفصل {{ $term['semester'] }}
                        @if ($term['exam_type'] === 2) (الدور الثاني) @endif
                        / دفعة {{ $term['batch'] ?? '-' }}
                    </h4>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-right">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-900/40 text-gray-600 dark:text-gray-300">
                                    <th class="px-3 py-2 font-semibold">رمز المقرر</th>
                                    <th class="px-3 py-2 font-semibold">اسم المقرر</th>
                                    <th class="px-3 py-2 font-semibold">الساعات المعتمدة</th>
                                    <th class="px-3 py-2 font-semibold">أعمال السنة</th>
                                    <th class="px-3 py-2 font-semibold">الدرجة</th>
                                    <th class="px-3 py-2 font-semibold">التقدير</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-900 dark:text-gray-100">
                                @foreach ($term['marks'] as $mark)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                                        <td class="px-3 py-2">{{ $mark->subject_code }}</td>
                                        <td class="px-3 py-2">{{ $mark->subject?->name_ar ?? 'غير معروف' }}</td>
                                        <td class="px-3 py-2">{{ $mark->subject?->crch ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $mark->cwork ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $mark->mark ?? '-' }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-block rounded px-2 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-700">
                                                {{ $mark->grade ?? '-' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-student.card>
