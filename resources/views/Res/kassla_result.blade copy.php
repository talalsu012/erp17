<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتيجة الطلاب - جامعة كسلا</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Tajawal', 'sans-serif'],
                    },
                },
            },
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: #ffffff !important;
                color: #000000 !important;
            }

            .print-card {
                border: 1px solid #d1d5db !important;
                box-shadow: none !important;
                background: transparent !important;
            }

            .print-table {
                border-collapse: collapse !important;
                width: 100% !important;
            }

            .print-table th,
            .print-table td {
                border: 1px solid #9ca3af !important;
                padding: 6px !important;
                color: #000000 !important;
            }

            .print-table th {
                background-color: #f3f4f6 !important;
            }

            /* فئة مسؤولة عن فصل الصفحات */
            .print-break-before {
                page-break-before: always !important;
            }

            .print-break-inside-avoid {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 font-sans min-h-screen antialiased">

    <div class="max-w-7xl mx-auto px-4 py-8 print:py-0 print:px-0">


        <!-- ========================================== -->
        <!-- الصفحة الأولى: الترويسة والبطاقات الإحصائية -->
        <!-- ========================================== -->
        <div class="print-page-1">
            <div
                class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 mb-8 text-center relative overflow-hidden print-card print:border-b-4 print:pb-6 print:mb-6">
                <div
                    class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-blue-700 to-indigo-900 print:hidden">
                </div>

                <h1 class="text-3xl font-extrabold text-blue-900 mb-3 tracking-wide">جامعة كسلا</h1>

                <p class="text-lg font-bold text-slate-700 mb-1">الكلية: <span class="text-blue-700">{{
                        $facultyInfo->name_ar ?? 'غير محدد' }}</span></p>
                <p class="text-sm font-semibold text-slate-500">
                    المستوى الأكاديمي: <span class="text-slate-800">{{ $levelInfo->name_ar ?? 'غير محدد' }}</span>
                    <span class="mx-2 text-slate-300">|</span>
                    الفصل الدراسي: <span class="text-slate-800">{{ $semester ?? 'غير محدد' }}</span>
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 print:grid-cols-3 print:gap-4 print:mb-6">
                <div
                    class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 border-r-4 border-r-blue-600 flex items-center justify-between print-card">
                    <div>
                        <span class="text-slate-500 text-sm font-medium">إجمالي الطلاب</span>
                        <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ $totalStudents }}</h3>
                    </div>
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-lg print:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A11.386 11.386 0 0 1 10.089 18H8.25c-.621 0-1.125-.504-1.125-1.125V18M15 9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5-3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </div>
                </div>

                <div
                    class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 border-r-4 border-r-emerald-600 flex items-center justify-between print-card">
                    <div>
                        <span class="text-slate-500 text-sm font-medium">عدد الناجحين</span>
                        <h3 class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $passedStudents }}</h3>
                    </div>
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg print:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>

                <div
                    class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 border-r-4 border-r-rose-600 flex items-center justify-between print-card">
                    <div>
                        <span class="text-slate-500 text-sm font-medium">عدد الراسبين</span>
                        <h3 class="text-3xl font-extrabold text-rose-600 mt-1">{{ $failedStudents }}</h3>
                    </div>
                    <div class="p-3 bg-rose-50 text-rose-600 rounded-lg print:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- الصفحة الثانية: جدول محتوى الطلاب -->
        <!-- ========================================== -->
        <div class="print-break-before">
            <!-- تمت إضافة عنوان صغير يظهر في الطباعة لتعريف الجدول -->
            <h2 class="hidden print:block text-xl font-bold text-slate-800 mb-4 text-center">تفاصيل نتيجة الطلاب</h2>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden print-card mb-10">
                <div class="overflow-x-auto">
                    <!-- <table class="w-full text-right border-collapse print-table">
                        <thead>
                            <tr class="bg-slate-800 text-white text-xs font-bold tracking-wider uppercase print:bg-gray-200 print:text-black">
                                @if(in_array('university_no', $fields))
                                    <th class="px-6 py-4 border-b border-slate-700">الرقم الجامعي</th>
                                @endif
                                
                                @if(in_array('name_ar', $fields))
                                    <th class="px-6 py-4 border-b border-slate-700">اسم الطالب</th>
                                @endif
                                
                                @foreach($subjects as $subjCode)
                                    <th class="px-2 py-4 border-b border-slate-700 text-center">
                                        <div style="writing-mode: vertical-rl; transform: rotate(180deg);">
                                            {{ $subjectNames[$subjCode]->name_ar ?? $subjCode }}
                                        </div>
                                    </th>
                                @endforeach

                                @if(in_array('mark', $fields))
                                    <th class="px-6 py-4 border-b border-slate-700 text-center">المجموع</th>
                                @endif

                                @if(in_array('subjectFail', $fields))
                                    <th class="px-6 py-4 border-b border-slate-700 text-center">المواد الراسبة</th>
                                @endif
                                
                                @if(in_array('gpa', $fields))
                                    <th class="px-6 py-4 border-b border-slate-700 text-center">المعدل (GPA)</th>
                                @endif
                                
                                @if(in_array('recom', $fields))
                                    <th class="px-6 py-4 border-b border-slate-700 text-center">التوصية</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($students as $student)
                                <tr class="hover:bg-slate-50/70 transition duration-150 odd:bg-slate-50/20 print:hover:bg-transparent">
                                    @if(in_array('university_no', $fields))
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">{{ $student->university_no }}</td>
                                    @endif
                                    
                                    @if(in_array('name_ar', $fields))
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-700">{{ $student->name_ar }}</td>
                                    @endif
                                    
                                    @foreach($subjects as $subjCode)
                                        @php
                                            $mark = $student->marks->firstWhere('subject_code', $subjCode);
                                            $grade = $mark->grade ?? '-';
                                            
                                            $isFail = in_array(trim($grade), ['ر', 'F', 'ض', 'D']);
                                            
                                            if ($isFail) {
                                                $cellClasses = 'bg-rose-100 text-rose-700 font-extrabold print:bg-gray-300';
                                            } else {
                                                $cellClasses = $grade !== '-' ? 'text-emerald-600 font-bold' : 'text-slate-400';
                                            }
                                        @endphp
                                        <td class="px-6 py-4 text-center whitespace-nowrap text-sm border-x border-slate-100 {{ $cellClasses }}" style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
                                            {{ $grade }}
                                        </td>
                                    @endforeach

                                    @if(in_array('mark', $fields))
                                        <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-bold text-slate-800">{{ $student->total_marks ?? '-' }}</td>
                                    @endif
                                    
                                    @if(in_array('subjectFail', $fields))
                                        @if(count($student->failed_subject_names ?? []) > 0)
                                            <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-slate-800">
                                                {{ implode('، ', $student->failed_subject_names) }}
                                            </td>
                                        @else
                                            <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-slate-800">-</td>
                                        @endif
                                    @endif
                                    
                                    @if(in_array('gpa', $fields))
                                        <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-bold text-blue-700">{{ $student->gpa ?? '-' }}</td>
                                    @endif
                                    
                                    @if(in_array('recom', $fields))
                                        <td class="px-6 py-4 text-center whitespace-nowrap text-sm">
                                            @if($student->recom && $student->recom->isNotEmpty() && $student->recom->first()->recom_code)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-100 print:bg-transparent print:border-none">
                                                    {{ $student->recom->first()->name_ar ?? $student->recom->first()->recom_code }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="20" class="px-6 py-10 text-center text-sm font-medium text-slate-400 bg-slate-50/50">
                                        لا توجد بيانات طلاب لعرضها في الوقت الحالي.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table> -->

                    <table class="w-full text-right border-collapse print-table">

                        <thead>
                            <tr
                                class="bg-slate-800 text-white text-xs font-bold tracking-wider print:bg-gray-200 print:text-black">

                                {{-- م --}}
                                @if(in_array('ids', $fields))
                                <th class="px-3 py-4 border-b border-slate-700 text-center">
                                    م
                                </th>
                                @endif

                                {{-- الرقم الجامعي --}}
                                @if(in_array('university_no', $fields))
                                <th class="px-6 py-4 border-b border-slate-700">
                                    الرقم الجامعي
                                </th>
                                @endif

                                {{-- رقم الجلوس --}}
                                @if(in_array('indexno', $fields))
                                <th class="px-6 py-4 border-b border-slate-700">
                                    رقم الجلوس
                                </th>
                                @endif

                                {{-- الاسم عربي --}}
                                @if(in_array('name_ar', $fields))
                                <th class="px-6 py-4 border-b border-slate-700">
                                    اسم الطالب
                                </th>
                                @endif

                                {{-- الاسم إنجليزي --}}
                                @if(in_array('name_en', $fields))
                                <th class="px-6 py-4 border-b border-slate-700">
                                    الاسم بالإنجليزية
                                </th>
                                @endif


                                {{-- ======================================= --}}
                                {{-- المواد --}}
                                {{-- ======================================= --}}

                                @foreach($subjects as $subjCode)

                                <th class="px-2 py-4 border-b border-slate-700 text-center">

                                    <div style="
                            writing-mode: vertical-rl;
                            transform: rotate(180deg);
                        ">
                                        {{ $subjectNames[$subjCode]->name_ar ?? $subjCode }}
                                    </div>

                                </th>

                                @endforeach


                                {{-- مجموع الدرجات --}}
                                @if(
                                in_array('mark', $fields) ||
                                in_array('sumMarks', $fields)
                                )
                                <th class="px-6 py-4 border-b border-slate-700 text-center">
                                    مجموع الدرجات
                                </th>
                                @endif

                                {{-- مواد الرسوب --}}
                                @if(in_array('subjectFail', $fields))
                                <th class="px-6 py-4 border-b border-slate-700 text-center">
                                    المواد الراسبة
                                </th>
                                @endif

                                {{-- ساعات الرسوب --}}
                                @if(in_array('crch_fail', $fields))
                                <th class="px-6 py-4 border-b border-slate-700 text-center">
                                    ساعات الرسوب
                                </th>
                                @endif

                                {{-- المعدل --}}
                                @if(in_array('gpa', $fields))
                                <th class="px-6 py-4 border-b border-slate-700 text-center">
                                    المعدل (GPA)
                                </th>
                                @endif

                                {{-- التوصية --}}
                                @if(in_array('recom', $fields))
                                <th class="px-6 py-4 border-b border-slate-700 text-center">
                                    التوصية
                                </th>
                                @endif

                            </tr>
                        </thead>


                        <tbody class="divide-y divide-slate-100">

                            @forelse($students as $student)

                            <tr class="
                hover:bg-slate-50/70
                transition duration-150
                odd:bg-slate-50/20
                print:hover:bg-transparent
            ">

                                {{-- م --}}
                                @if(in_array('ids', $fields))
                                <td class="px-3 py-4 text-center text-sm font-bold">
                                    {{ $loop->iteration }}
                                </td>
                                @endif


                                {{-- الرقم الجامعي --}}
                                @if(in_array('university_no', $fields))
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                    {{ $student->university_no }}
                                </td>
                                @endif


                                {{-- رقم الجلوس --}}
                                @if(in_array('indexno', $fields))
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $student->indexno ?? '-' }}
                                </td>
                                @endif


                                {{-- الاسم عربي --}}
                                @if(in_array('name_ar', $fields))
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-700">
                                    {{ $student->name_ar ?? '-' }}
                                </td>
                                @endif


                                {{-- الاسم إنجليزي --}}
                                @if(in_array('name_en', $fields))
                                <td dir="ltr" class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $student->name_en ?? '-' }}
                                </td>
                                @endif


                                {{-- ======================================= --}}
                                {{-- المواد --}}
                                {{-- ======================================= --}}

                                @foreach($subjects as $subjCode)

                                @php
                                $mark = $student->marks
                                ->firstWhere('subject_code', $subjCode);

                                $grade = $mark->grade ?? '-';

                                $isFail = in_array(
                                trim((string) $grade),
                                ['ر', 'F', 'ض', 'D']
                                );

                                if ($isFail) {

                                $cellClasses =
                                'bg-rose-100 text-rose-700 font-extrabold print:bg-gray-300';

                                } else {

                                $cellClasses =
                                $grade !== '-'
                                ? 'text-emerald-600 font-bold'
                                : 'text-slate-400';
                                }
                                @endphp

                                <td class="
                            px-3 py-4
                            text-center
                            whitespace-nowrap
                            text-sm
                            border-x border-slate-100
                            {{ $cellClasses }}
                        " style="
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                        ">
                                    {{ $grade }}
                                </td>

                                @endforeach


                                {{-- مجموع الدرجات --}}
                                @if(
                                in_array('mark', $fields) ||
                                in_array('sumMarks', $fields)
                                )
                                <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-bold text-slate-800">
                                    {{ $student->total_marks ?? $student->sumMarks ?? '-' }}
                                </td>
                                @endif


                                {{-- المواد الراسبة --}}
                                @if(in_array('subjectFail', $fields))

                                <td class="px-6 py-4 text-center text-sm text-slate-800">

                                    @if(count($student->failed_subject_names ?? []) > 0)

                                    {{ implode('، ', $student->failed_subject_names) }}

                                    @else

                                    -

                                    @endif

                                </td>

                                @endif


                                {{-- ساعات الرسوب --}}
                                @if(in_array('crch_fail', $fields))

                                <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-bold">
                                    {{ $student->crch_fail ?? '-' }}
                                </td>

                                @endif


                                {{-- GPA --}}
                                @if(in_array('gpa', $fields))

                                <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-bold text-blue-700">
                                    {{ $student->gpa ?? '-' }}
                                </td>

                                @endif


                                {{-- التوصية --}}
                                @if(in_array('recom', $fields))

                                <td class="px-6 py-4 text-center whitespace-nowrap text-sm">

                                    @if(
                                    $student->recom &&
                                    $student->recom->isNotEmpty() &&
                                    $student->recom->first()->recom_code
                                    )

                                    <span class="
                                inline-flex
                                items-center
                                px-2.5 py-1
                                rounded-full
                                text-xs
                                font-bold
                                bg-blue-50
                                text-blue-800
                                border
                                border-blue-100
                                print:bg-transparent
                                print:border-none
                            ">
                                        {{
                                        $student->recom->first()->name_ar
                                        ?? $student->recom->first()->recom_code
                                        }}
                                    </span>

                                    @else

                                    <span class="text-slate-400">
                                        -
                                    </span>

                                    @endif

                                </td>

                                @endif

                            </tr>

                            @empty

                            <tr>
                                <td colspan="{{ count($fields) + count($subjects) }}" class="
                        px-6 py-10
                        text-center
                        text-sm
                        font-medium
                        text-slate-400
                        bg-slate-50/50
                    ">
                                    لا توجد بيانات طلاب لعرضها في الوقت الحالي.
                                </td>
                            </tr>

                            @endforelse

                        </tbody>

                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- الصفحة الثالثة: الجدول الموحد، الرسم البياني، والتوقيعات -->
        <!-- ========================================== -->
        <div class="print-break-before print:pt-4">


            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 print-card print-break-inside-avoid">

                @php
                // استخراج جميع التقديرات المتاحة لجميع المواد
                $allGrades = [];
                foreach($subjectStats as $stats) {
                foreach(array_keys($stats) as $grade) {
                if(!in_array($grade, $allGrades)) {
                $allGrades[] = $grade;
                }
                }
                }
                @endphp

                <!-- الجدول الموحد -->
                <div class="w-full mb-10 overflow-x-auto">
                    <h4 class="text-lg font-bold text-slate-800 mb-4 text-center bg-slate-100 w-full py-2 rounded-lg">
                        جدول التقديرات التجميعي للمواد
                    </h4>
                    <table class="w-full text-center border-collapse print-table">
                        <thead>
                            <tr class="bg-blue-50 print:bg-gray-200">
                                <th
                                    class="px-4 py-3 border border-slate-200 text-sm font-bold text-slate-800 text-right">
                                    المادة \ التقدير</th>
                                @foreach($allGrades as $grade)
                                <th class="px-4 py-3 border border-slate-200 text-sm font-bold text-blue-700">{{ $grade
                                    }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($subjectStats as $subjCode => $stats)
                            <tr class="hover:bg-slate-50">
                                <td
                                    class="px-4 py-3 border border-slate-200 text-sm font-bold text-slate-700 text-right bg-slate-50">
                                    {{ $subjectNames[$subjCode]->name_ar ?? $subjCode }}
                                </td>
                                @foreach($allGrades as $grade)
                                <td class="px-4 py-3 border border-slate-200 text-sm font-semibold text-slate-800">
                                    {{ $stats[$grade] ?? 0 }}
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- الرسم البياني الموحد -->
                <div class="w-full mb-12 print:mb-8">
                    <h4 class="text-lg font-bold text-slate-800 mb-4 text-center bg-slate-100 w-full py-2 rounded-lg">
                        الرسم البياني للتقديرات
                    </h4>
                    <div class="w-full h-[350px] flex items-center justify-center">
                        <canvas id="unified-chart"></canvas>
                    </div>
                </div>

                <!-- قسم التوقيعات والاعتمادات (جديد) -->
                <div class="mt-16 pt-8 border-t-2 border-slate-200 print:mt-10 print:pt-6">
                    <div class="grid grid-cols-3 gap-8 text-center">
                        <!-- توقيع 1 -->
                        <div class="flex flex-col items-center">
                            <h4 class="font-bold text-slate-800 text-lg mb-12">رئيس لجنة الأمتحانات</h4>
                            <div class="border-b-2 border-slate-400 w-3/4 mb-2"></div>
                            <span class="text-sm text-slate-500">التاريخ: ___ / ___ / _____</span>
                        </div>

                        <!-- توقيع 2 -->
                        <div class="flex flex-col items-center">
                            <h4 class="font-bold text-slate-800 text-lg mb-12">مسجل الكلية</h4>
                            <div class="border-b-2 border-slate-400 w-3/4 mb-2"></div>
                            <span class="text-sm text-slate-500">التاريخ: ___ / ___ / _____</span>
                        </div>

                        <!-- توقيع 3 -->
                        <div class="flex flex-col items-center">
                            <h4 class="font-bold text-slate-800 text-lg mb-12">عميد الكلية</h4>
                            <div class="border-b-2 border-slate-400 w-3/4 mb-2"></div>
                            <span class="text-sm text-slate-500">التاريخ: ___ / ___ / _____</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // جلب البيانات من PHP
            const subjectStats = @json($subjectStats);
            const subjectNames = @json($subjectNames);
            const allGrades = @json($allGrades);

            // استخراج أسماء المواد كمسميات لمحور X
            const labels = Object.keys(subjectStats).map(code => {
                return subjectNames[code] ? subjectNames[code].name_ar : code;
            });

            // دالة لتخصيص الألوان لكل تقدير (الرسوب باللون الأحمر)
            function getColorForGrade(grade, index) {
                const failGrades = ['ر', 'F', 'ض', 'D'];
                if (failGrades.includes(grade.trim())) {
                    return '#ef4444'; // أحمر للرسوب
                }
                const colors = ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#0ea5e9', '#d946ef', '#84cc16'];
                return colors[index % colors.length];
            }

            // بناء مجموعات البيانات (Datasets)
            const datasets = allGrades.map((grade, index) => {
                return {
                    label: grade,
                    data: Object.keys(subjectStats).map(code => subjectStats[code][grade] || 0),
                    backgroundColor: getColorForGrade(grade, index),
                    borderWidth: 1,
                    borderColor: '#ffffff',
                    borderRadius: 4
                };
            });

            // رسم المخطط الموحد
            const canvasElement = document.getElementById('unified-chart');
            if (canvasElement) {
                const ctx = canvasElement.getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    font: {
                                        family: 'Tajawal',
                                        size: 13,
                                        weight: 'bold'
                                    },
                                    padding: 20
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    font: {
                                        family: 'Tajawal',
                                        size: 12,
                                        weight: 'bold'
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1, // أرقام صحيحة لعدد الطلاب
                                    font: {
                                        family: 'Tajawal',
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>