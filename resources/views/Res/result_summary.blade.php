<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير ملخص الفصل الدراسي</title>
    <!-- استدعاء Tailwind CSS وخط Cairo -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f3f4f6; }
        @media print {
            body { background-color: white; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            .shadow-sm { box-shadow: none !important; border: 1px solid #e5e7eb; }
        }
    </style>
</head>
<body class="text-gray-800 p-6">

    <div class="max-w-6xl mx-auto bg-white p-8 rounded-lg shadow-sm border border-gray-200">
        
        <!-- الترويسة وزر الطباعة -->
        <div class="flex justify-between items-start border-b pb-6 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">التقرير الأكاديمي الإحصائي</h1>
                <p class="text-gray-500">ملخص نتائج وموقف الطلاب الأكاديمي للعام الدراسي {{ $filters['batch'] }} - {{ $filters['faculty'] }}</p>
            </div>
            <div class="no-print flex items-center gap-2">
                <a href="{{ $backUrl }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 font-semibold text-gray-700 transition hover:bg-gray-50">رجوع</a>
                <button type="button" onclick="window.print()" class="rounded-md bg-sky-100 px-4 py-2 font-semibold text-sky-900 transition hover:bg-sky-200">طباعة</button>
            </div>
        </div>

        <!-- معلومات الفلاتر -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8 bg-gray-50 p-4 rounded-md border border-gray-100 text-sm">
            <div><span class="block text-gray-500 mb-1">العام الدراسي</span><span class="font-bold">{{ $filters['batch'] }}</span></div>
            <div><span class="block text-gray-500 mb-1">القسم</span><span class="font-bold">{{ $filters['department'] }}</span></div>
            <div><span class="block text-gray-500 mb-1">التخصص</span><span class="font-bold">{{ $filters['special'] }}</span></div>
            <div><span class="block text-gray-500 mb-1">الفصل</span><span class="font-bold">{{ $filters['level'] }}</span></div>
            <div><span class="block text-gray-500 mb-1">نوع الامتحان</span><span class="font-bold">{{ $filters['absent'] }}</span></div>
            <div class="border-r-2 border-green-500 pr-4">
                <span class="block text-gray-500 mb-1">إجمالي الطلاب</span>
                <span class="font-bold text-lg text-green-700">{{ $totalStudents }} طالب</span>
            </div>
        </div>

        <!-- بطاقات الملخص -->
        <div class="mb-8 grid grid-cols-2 gap-4 md:grid-cols-5">
            @foreach ([
                ['الطلاب الجالسون', $stats['sitting'], 'text-purple-600'],
                ['الناجحون', $stats['passed'], 'text-green-600'],
                ['الراسبون', $stats['failed'], 'text-red-600'],
                ['نسبة النجاح', ($stats['sitting'] ? round($stats['passed'] / $stats['sitting'] * 100, 1) : 0).'%', 'text-blue-600'],
                ['أعلى معدل', $highestGpa !== null ? number_format($highestGpa, 2) : '—', 'text-gray-800'],
            ] as [$label, $value, $tone])
                <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 text-center">
                    <div class="text-sm text-gray-500">{{ $label }}</div>
                    <div class="text-2xl font-bold {{ $tone }}">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        <!-- إحصائيات المقررات (النتيجة) -->
        <div class="mb-10">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-gray-100 pb-2">إحصائيات المقررات الدراسية</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 text-sm">
                            <th class="p-3 border border-gray-200">المقرر الدراسي</th>
                            <th class="p-3 border border-gray-200 text-center">الجالسين</th>
                            <th class="p-3 border border-gray-200 text-center">الناجحين</th>
                            <th class="p-3 border border-gray-200 text-center">الراسبين</th>
                            <th class="p-3 border border-gray-200 text-center">نسبة النجاح</th>
                            <th class="p-3 border border-gray-200 text-center">توزيع التقديرات (A, B, C...)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjectStats as $stat)
                        <tr class="hover:bg-gray-50 text-sm">
                            <td class="p-3 border border-gray-200 font-semibold">{{ $stat['subject_name'] }}</td>
                            <td class="p-3 border border-gray-200 text-center">{{ $stat['total'] }}</td>
                            <td class="p-3 border border-gray-200 text-center text-green-600 font-bold">{{ $stat['passed'] }}</td>
                            <td class="p-3 border border-gray-200 text-center text-red-600 font-bold">{{ $stat['failed'] }}</td>
                            <td class="p-3 border border-gray-200 text-center">
                                <span class="px-2 py-1 rounded {{ $stat['pass_rate'] >= 50 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $stat['pass_rate'] }}%
                                </span>
                            </td>
                            <td class="p-3 border border-gray-200 text-center text-gray-500 text-xs">
                                @foreach($stat['grades'] as $grade => $count)
                                    @if($grade && $grade != '')
                                        <span class="inline-block mx-1"><b class="text-gray-800">{{ $grade }}:</b> {{ $count }}</span>
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">لا توجد بيانات مقررات مطابقة للبحث</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- إحصائيات التوصيات والقرارات -->
        <div class="page-break">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-gray-100 pb-2">إحصائية القرارات والتوصيات الأكاديمية</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- جدول التوصيات -->
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 text-sm">
                                <th class="p-3 border border-gray-200">القرار / التوصية</th>
                                <th class="p-3 border border-gray-200 text-center">العدد</th>
                                <th class="p-3 border border-gray-200 text-center">النسبة من الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recomStats as $recom)
                            <tr class="hover:bg-gray-50 text-sm">
                                <td class="p-3 border border-gray-200 font-semibold">{{ $recom['name'] }}</td>
                                <td class="p-3 border border-gray-200 text-center font-bold text-blue-600">{{ $recom['count'] }}</td>
                                <td class="p-3 border border-gray-200 text-center">
                                    {{ $totalRecoms > 0 ? round(($recom['count'] / $totalRecoms) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="p-4 text-center text-gray-500">لا توجد قرارات مسجلة لهذه الفئة</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- توضيح ألوان/رسوم (مساحة لإضافة Chart.js مستقبلاً إذا رغبت) -->
                <div class="bg-blue-50 rounded-lg p-6 border border-blue-100 flex flex-col justify-center items-center text-center">
                    <svg class="w-16 h-16 text-blue-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <h3 class="text-lg font-bold text-blue-800 mb-1">إجمالي الحالات المصدرة</h3>
                    <p class="text-3xl font-black text-blue-900">{{ $totalRecoms }}</p>
                    <p class="text-sm text-blue-600 mt-2">طالب لديهم قرارات أكاديمية معتمدة</p>
                </div>
            </div>
        </div>

        <p class="mt-8 text-center text-xs text-gray-400">طُبع بواسطة {{ $printedBy }} — {{ now()->format('Y-m-d H:i') }}</p>
    </div>

</body>
</html>