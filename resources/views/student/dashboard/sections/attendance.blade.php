<x-student.card id="attendance" title="الحضور والغياب" class="scroll-mt-16">
    @if ($attendance->isEmpty())
        <p class="text-center text-gray-500 dark:text-gray-400 py-6">لا توجد سجلات حضور</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-right">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/40 text-gray-600 dark:text-gray-300">
                        <th class="px-3 py-2 font-semibold">المقرر</th>
                        <th class="px-3 py-2 font-semibold">المحاضرات</th>
                        <th class="px-3 py-2 font-semibold">حضور</th>
                        <th class="px-3 py-2 font-semibold">تأخر</th>
                        <th class="px-3 py-2 font-semibold">غياب</th>
                        <th class="px-3 py-2 font-semibold">نسبة الحضور</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-900 dark:text-gray-100">
                    @foreach ($attendance as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                            <td class="px-3 py-2">{{ $row['subject'] ?? 'غير معروف' }}</td>
                            <td class="px-3 py-2">{{ $row['total'] }}</td>
                            <td class="px-3 py-2">{{ $row['present'] }}</td>
                            <td class="px-3 py-2">{{ $row['late'] }}</td>
                            <td class="px-3 py-2">{{ $row['absent'] }}</td>
                            <td class="px-3 py-2 font-semibold">
                                {{ $row['total'] > 0 ? round((($row['present'] + $row['late']) / $row['total']) * 100) : 0 }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-student.card>
