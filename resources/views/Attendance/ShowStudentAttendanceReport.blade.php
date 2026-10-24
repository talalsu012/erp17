
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف حضور المحاضرة</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
        body {
            font-family: 'Tajawal', sans-serif;
        }
        .header-border {
            border-bottom: 2px solid #4f46e5;
        }
        .student-row:hover {
            background-color: #f8fafc;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                font-size: 12pt;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto p-6 max-w-6xl">
        <!-- Report Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6 header-border">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="w-full md:w-1/4 flex justify-center">
                    <img src="{{ asset(config('university.logo')) }}" alt="شعار الكلية" class="h-20 object-contain">
                </div>
                <div class="w-full md:w-2/4 text-center space-y-2">
                    <p class="text-lg text-indigo-600">بسم الله الرحمن الرحيم</p>
                    <h1 class="text-2xl font-bold text-gray-800">{{ __('main.title_uni_report') }}</h1>
                    <h2 class="text-xl font-semibold text-indigo-700">كشف حضور المحاضرة</h2>
                    <p><span class="font-medium">اسم المحاضر:</span> {{ $lecture->staff->name ?? 'غير محدد' }}</p>
                </div>
                <div class="w-full md:w-1/4 text-left md:text-right">
                    <div class="bg-gray-100 p-2 rounded-lg inline-block">
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">تاريخ الطباعة:</span>
                            {{ now()->translatedFormat('Y-m-d') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Lecture Information Section -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <h3 class="font-medium text-indigo-700 mb-1">معلومات البرنامج</h3>

                    <p><span class="font-medium">الدفعة:</span> {{ $batchName ?? 'غير محدد' }}</p>
                    <p><span class="font-medium">البرنامج:</span> {{ $lecture->subject->faculty->name_ar ?? 'غير محدد' }}</p>
                    <p><span class="font-medium">المستوى:</span> {{ $levelName ?? 'غير محدد' }}  <span class="font-medium">-</span> {{ $lecture->subject->semester_id ? \App\Reg\Semester::from($lecture->subject->semester_id)->getLabel() : 'غير محدد' }}</p>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg">
                    <h3 class="font-medium text-indigo-700 mb-1">معلومات المحاضرة</h3>
                    <p><span class="font-medium">اسم المادة:</span> {{ $lecture->subject->name_ar ?? 'غير محدد' }}</p>
                    <p><span class="font-medium">اليوم:</span> {{ $lecture->day_of_week ?? 'غير محدد' }}</p>
                    <p><span class="font-medium">الوقت:</span>
                        {{ $lecture->start_time ? $lecture->start_time->format('h:i A') : 'غير محدد' }} -
                        {{ $lecture->end_time ? $lecture->end_time->format('h:i A') : 'غير محدد' }}
                    </p>

                </div>

                <div class="bg-gray-50 p-3 rounded-lg">
                    <h3 class="font-medium text-indigo-700 mb-1">معلومات المجموعة</h3>
                    <p><span class="font-medium">المجموعة:</span> {{ $group->name ?? 'غير محدد' }}</p>
                    <p><span class="font-medium">اسم القاعة:</span> {{ $lecture->room->name ?? 'غير محدد' }}</p>
                </div>
            </div>
        </div>

        <!-- Students Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-indigo-600">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">الرقم الجامعي</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">اسم الطالب</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">1</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">2</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">3</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">4</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">5</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">6</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">7</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">8</th>


                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($students as $index => $student)
                    <tr class="student-row transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900">{{ $student->university_no }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $student->name_ar }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                            <div class="w-4 h-4 border border-gray-400 mx-auto"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                            <div class="w-4 h-4 border border-gray-400 mx-auto"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                            <div class="w-4 h-4 border border-gray-400 mx-auto"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                            <div class="w-4 h-4 border border-gray-400 mx-auto"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                            <div class="w-4 h-4 border border-gray-400 mx-auto"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                            <div class="w-4 h-4 border border-gray-400 mx-auto"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                            <div class="w-4 h-4 border border-gray-400 mx-auto"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                            <div class="w-4 h-4 border border-gray-400 mx-auto"></div>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="bg-gray-50 p-3 rounded-lg">
                <h3 class="font-medium text-indigo-700 mb-2">معلومات المحاضر</h3>
                <p><span class="font-medium">اسم المحاضر:</span> {{ $lecture->staff->name ?? 'غير محدد' }}</p>
                <p class="mt-4">التوقيع: _________________________</p>
            </div>
            <div class="bg-gray-50 p-3 rounded-lg">
                <h3 class="font-medium text-indigo-700 mb-2">ملاحظات</h3>
                <p>_________________________________________</p>
                <p>_________________________________________</p>
            </div>
        </div>

        <div class="mt-6 text-center text-sm text-gray-500">
            <p>تم إنشاء هذا التقرير بواسطة نظام إدارة الأكاديمية</p>
            <p class="mt-1">الصفحة 1 من 1</p>
        </div>
    </div>

    <!-- Print Button -->
    <div class="fixed bottom-6 left-6 no-print">
        <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            طباعة التقرير
        </button>
    </div>
</body>
</html>
