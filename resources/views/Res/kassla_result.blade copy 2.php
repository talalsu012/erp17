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
        /* إعدادات الطباعة - حجم الورقة بالكامل */
        @page {
            size: A4;
            margin: 0.8cm 0.6cm;
        }
        
        @media print {
            /* إخفاء العناصر غير المرغوب فيها */
            .no-print { display: none !important; }
            
            /* إخفاء الرسم البياني والإحصاءات */
            .hide-on-print { display: none !important; }
            
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                font-size: 10pt !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: 100% !important;
            }
            
            .max-w-7xl {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
            
            /* الترويسة في كل صفحة */
            .header-repeat {
                position: running(header);
                width: 100%;
                text-align: center;
                border-bottom: 2px solid #000;
                padding-bottom: 8px;
                margin-bottom: 10px;
                background: #fff;
            }
            
            /* الفوتر في كل صفحة */
            .footer-repeat {
                position: running(footer);
                width: 100%;
                text-align: center;
                border-top: 2px solid #000;
                padding-top: 8px;
                margin-top: 10px;
                font-size: 9pt;
            }
            
            /* ربط الترويسة والفوتر بكل صفحة */
            .page-content {
                display: block;
            }
            
            .print-card {
                border: 1px solid #9ca3af !important;
                box-shadow: none !important;
                background: #ffffff !important;
                padding: 0.3rem 0.5rem !important;
                border-radius: 0.25rem !important;
                margin-bottom: 0.2rem !important;
            }
            
            .print-table {
                border-collapse: collapse !important;
                width: 100% !important;
                font-size: 8pt !important;
            }
            .print-table th, .print-table td {
                border: 1px solid #6b7280 !important;
                padding: 2px 4px !important;
                color: #000000 !important;
                background-color: #ffffff !important;
            }
            
            .print-table th {
                background-color: #ffffff !important;
                color: #000000 !important;
                font-weight: 700 !important;
                text-align: center !important;
                border-bottom: 2px solid #000000 !important;
            }
            
            .bg-white, .bg-slate-50, .bg-slate-100, .bg-blue-50, .bg-emerald-50, .bg-rose-50 {
                background-color: #ffffff !important;
            }
            
            .shadow-sm, .shadow {
                box-shadow: none !important;
            }
            
            .text-blue-700, .text-blue-800, .text-blue-900,
            .text-emerald-600, .text-rose-600, .text-slate-800,
            .text-slate-900, .text-slate-700 {
                color: #000000 !important;
            }
            
            svg, .print-hide-icon {
                display: none !important;
            }
            
            h1, h2, h3, h4, .text-3xl, .text-2xl, .text-xl {
                font-size: 12pt !important;
                font-weight: 700 !important;
                color: #000000 !important;
            }
            
            .print-break-before {
                page-break-before: always !important;
            }
            .print-break-inside-avoid {
                page-break-inside: avoid !important;
            }
            
            .px-4, .px-6, .py-4, .py-3, .p-6, .p-8 {
                padding: 0.2rem 0.3rem !important;
            }
            
            .grid-cols-3 {
                gap: 0.3rem !important;
            }
            
            .signature-line {
                border-bottom: 1px solid #000 !important;
                width: 80% !important;
                margin: 0.3rem auto 0.2rem !important;
            }
            
            .bg-fail-print {
                background-color: #d1d5db !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .print-chart-container {
                max-height: 200px !important;
                margin-bottom: 0.4rem !important;
            }
            
            .print-summary-table th, .print-summary-table td {
                font-size: 7pt !important;
                padding: 2px 3px !important;
            }
            
            .mb-8, .mb-10, .mb-12, .mt-16, .mt-8 {
                margin-bottom: 0.2rem !important;
                margin-top: 0.2rem !important;
            }
            
            .bg-rose-100, .bg-emerald-50, .bg-blue-50, .bg-slate-50 {
                background-color: transparent !important;
            }
        }
        
        body {
            background-color: #f8fafc;
        }
        .print-card {
            transition: all 0.1s ease;
        }
        .signature-line {
            border-bottom: 1px solid #cbd5e1;
            width: 80%;
            margin: 0.3rem auto 0.2rem;
        }
        
        .bg-fail {
            background-color: #e5e7eb !important;
        }
        
        .chart-color-1 { background-color: #FF6B6B; }
        .chart-color-2 { background-color: #4ECDC4; }
        .chart-color-3 { background-color: #45B7D1; }
        .chart-color-4 { background-color: #96CEB4; }
        .chart-color-5 { background-color: #FFEAA7; }
        .chart-color-6 { background-color: #DDA0DD; }
        .chart-color-7 { background-color: #FF8A5C; }
        .chart-color-8 { background-color: #A29BFE; }

        /* ترويسة جامعة كسلا مع شعار الجامعة وشعار الكلية */
.university-report-header {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    direction: rtl;
}

.university-report-header__logo {
    display: flex;
    width: 85px;
    min-width: 85px;
    height: 85px;
    align-items: center;
    justify-content: center;
}

.university-report-header__logo img {
    display: block;
    width: 78px;
    height: 78px;
    object-fit: contain;
}

.university-report-header__content {
    flex: 1;
    min-width: 0;
    text-align: center;
}

/* في حالة عدم وجود شعار الكلية */
.university-report-header__placeholder {
    width: 85px;
    min-width: 85px;
    height: 85px;
}

/* إعدادات خاصة بالطباعة */
@media print {
    .university-report-header {
        gap: 6px !important;
    }

    .university-report-header__logo,
    .university-report-header__placeholder {
        width: 55px !important;
        min-width: 55px !important;
        height: 55px !important;
    }

    .university-report-header__logo img {
        display: block !important;
        width: 50px !important;
        height: 50px !important;
        object-fit: contain !important;
    }
}

    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased print:bg-white">

@php
    $universityLogo = asset('logos/kassala.png');

    $facultyLogoFile = 'logos/fac' . $facultyInfo->code . '.jpeg';
    $facultyLogoPath = public_path($facultyLogoFile);
    $facultyLogo = asset($facultyLogoFile);

    $facultyLogoExists = is_file($facultyLogoPath);
@endphp

    <!-- الترويسة المتكررة في كل صفحة (للطباعة) -->
    <div class="header-repeat print:block hidden">
        <p class="text-sm font-bold text-slate-800 mb-0">بسم الله الرحمن الرحيم</p>
        <h1 class="text-lg font-extrabold text-blue-900">جامعة كســــــــــــلا - كلية القانون</h1>
        <p class="text-sm font-bold text-slate-700 my-1">
            نتيجة الامتحانات {{ $absentName }} لطلاب  الفصل الدراسي الاول ٢٠٢٦/٢٠٢٥م
        </p>
        <p class="text-xs font-semibold text-slate-600">القسم: القانون المستوى الاول الفصل الاول</p>
    </div>

    <!-- الفوتر المتكرر في كل صفحة (للطباعة) -->
    <div class="footer-repeat print:block hidden">
        <div class="grid grid-cols-3 gap-2 text-center">
            <div>
                <p class="font-bold text-xs">د/ عاصم محمود عبد القادر</p>
                <p class="text-[8pt]">رئيس المكتب الأكاديمي</p>
            </div>
            <div>
                <p class="font-bold text-xs">أ. أحمد إبراهيم بابكر</p>
                <p class="text-[8pt]">المسجل</p>
            </div>
            <div>
                <p class="font-bold text-xs">د. إدريس عبدالله محمد الشيخ</p>
                <p class="text-[8pt]">عميد الكلية</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6 print:px-0 print:py-0">
        
        <!-- ========================================== -->
        <!-- الصفحة الأولى: الترويسة والبطاقات (تظهر في الشاشة فقط) -->
        <!-- ========================================== -->
        <div class="print-page-1 print-break-inside-avoid hide-on-print">
            <!-- ترويسة للشاشة -->
           <div
    class="bg-white p-4 rounded-lg shadow-sm
           border border-slate-200 mb-4"
>
    <div class="university-report-header">

        {{-- شعار جامعة كسلا في أقصى اليمين --}}
        <div class="university-report-header__logo">
            <img
                src="{{ $universityLogo }}"
                alt="شعار جامعة كسلا"
            >
        </div>

        {{-- نص الترويسة في المنتصف --}}
        <div class="university-report-header__content">
            <p class="text-lg font-bold text-slate-800 mb-1">
                بسم الله الرحمن الرحيم
            </p>

            <h1
                class="text-2xl font-extrabold
                       text-blue-900 tracking-wide"
            >
                جامعة كســــــــــــلا -
                {{ $facultyInfo->name_ar }}
            </h1>

            <p class="text-md font-bold text-slate-700 my-2">
                نتيجة الامتحانات
                {{ $absentName }}
                لطلاب  
               {{ $levelInfo->name_ar }}  

                {{ $academicsYear }}
            </p>

            <p class="text-sm font-semibold text-slate-600">
                القسم:
                {{ $departmentInfo->name_ar }}

            </p>
        </div>

        {{-- شعار الكلية في أقصى اليسار --}}
        @if ($facultyLogoExists)
            <div class="university-report-header__logo">
                <img
                    src="{{ $facultyLogo }}"
                    alt="شعار {{ $facultyInfo->name_ar }}"
                >
            </div>
        @else
            <div class="university-report-header__placeholder"></div>
        @endif

    </div>
</div>

            <!-- بطاقات إحصائية (تظهر في الشاشة فقط) -->
            <div class="grid grid-cols-4 gap-3 mb-4 hide-on-print">
                <div class="bg-white p-3 rounded-lg shadow-sm border border-slate-200 border-r-4 border-r-blue-600 flex items-center justify-between">
                    <div>
                        <span class="text-slate-500 text-xs font-medium">إجمالي الطلاب</span>
                       
                    </div>
                    <div class="p-2 bg-blue-75 text-blue-900 rounded-lg">
                     <h3 class="text-2xl font-extrabold text-slate-900 mt-0">{{ $allStudent }}</h3>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-lg shadow-sm border border-slate-200 border-r-4 border-r-blue-600 flex items-center justify-between">
                    <div>
                        <span class="text-slate-500 text-xs font-medium">الطلاب الجالسين</span>
                      
                    </div>
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                         <h3 class="text-2xl font-extrabold text-slate-900 mt-0">{{ $totalStudents }}</h3>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-lg shadow-sm border border-slate-200 border-r-4 border-r-emerald-600 flex items-center justify-between">
                    <div>
                        <span class="text-slate-500 text-xs font-medium">عدد الناجحين</span>
                      
                    </div>
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                         <h3 class="text-2xl font-extrabold text-emerald-600 mt-0">{{ $passedStudents }}</h3>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-lg shadow-sm border border-slate-200 border-r-4 border-r-rose-600 flex items-center justify-between">
                    <div>
                        <span class="text-slate-500 text-xs font-medium">عدد الراسبين</span>
                       
                    </div>
                    <div class="p-2 bg-rose-50 text-rose-600 rounded-lg">
                        <h3 class="text-2xl font-extrabold text-rose-600 mt-0">{{ $failedStudents }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- الصفحة الثانية: جدول تفاصيل الطلاب -->
        <!-- ========================================== -->
        <div class="print-break-before">
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden print-card print:border-1">
                <div class="overflow-x-auto">
                  
<!-- 
                    <table class="w-full text-right border-collapse print-table">
                        <thead>
                            <tr class="bg-white text-black text-xs font-bold tracking-wider uppercase print:bg-white print:text-black print:text-[7pt]">
                                
                                @if(in_array('ids', $fields))
                                    <th class="px-3 py-2 border-b-2 border-black">م</th>
                                @endif
                                
                                @if(in_array('indexno', $fields))
                                    <th class="px-3 py-2 border-b-2 border-black">رقم الجلوس</th>
                                @endif

                                @if(in_array('university_no', $fields))
                                    <th class="px-3 py-2 border-b-2 border-black">الرقم الجامعي</th>
                                @endif
                                
                                @if(in_array('name_ar', $fields))
                                    <th class="px-3 py-2 border-b-2 border-black">اسم الطالب</th>
                                @endif

                                @if(in_array('name_en', $fields))
                                    <th class="px-3 py-2 border-b-2 border-black text-left">Name (EN)</th>
                                @endif
                                
                                @foreach($subjects as $subjCode)
                                    <th class="px-1 py-2 border-b-2 border-black text-center">
                                        <div style="writing-mode: vertical-rl; transform: rotate(180deg); font-size: 7pt;">
                                            {{ $subjectNames[$subjCode]->name_ar ?? $subjCode }}
                                        </div>
                                    </th>
                                @endforeach

                                @if(in_array('mark', $fields))
                                    <th class="px-3 py-2 border-b-2 border-black text-center">المجموع</th>
                                @endif

                                @if(in_array('crch', $fields))
                                    <th class="px-3 py-2 border-b-2 border-black text-center">الساعات</th>
                                @endif

                                @if(in_array('crch_fail', $fields))
                                    <th class="px-3 py-2 border-b-2 border-black text-center">ساعات الرسوب</th>
                                @endif

                                @if(in_array('point', $fields))
                                    <th class="px-3 py-2 border-b-2 border-black text-center">النقاط</th>
                                @endif

                                @if(in_array('subjectFail', $fields))
                                    <th class="px-3 py-2 border-b-2 border-black text-center">مواد الملحق</th>
                                @endif
                                
                                @if(in_array('gpa', $fields))
                                    <th class="px-3 py-2 border-b-2 border-black text-center">المعدل</th>
                                @endif
                                
                                @if(in_array('recom', $fields))
                                    <th class="px-3 py-2 border-b-2 border-black text-center">القرار</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($students as $student)
                                <tr class="hover:bg-slate-50/70 transition duration-150 odd:bg-slate-50/20 print:hover:bg-transparent print:odd:bg-transparent">
                                    
                                    @if(in_array('ids', $fields))
                                        <td class="px-3 py-1.5 whitespace-nowrap text-xs font-semibold text-slate-900 print:text-[8pt]">
                                            {{ $loop->iteration }}
                                        </td>
                                    @endif
                                    
                                    @if(in_array('indexno', $fields))
                                        <td class="px-3 py-1.5 whitespace-nowrap text-xs font-semibold text-slate-900 print:text-[8pt]">
                                            {{ $student->indexno ?? '-' }}
                                        </td>
                                    @endif

                                    @if(in_array('university_no', $fields))
                                    <td class="px-3 py-1.5 whitespace-nowrap text-xs font-semibold text-slate-900 print:text-[8pt]">
                                        <div class="flex items-center gap-2">
                                            <span>{{ $student->university_no }}</span>
                                            
                                            <a href="{{ route('student.transcript.print', ['university_no' => $student->university_no]) }}" 
                                            target="_blank"
                                            title="طباعة السجل الأكاديمي"
                                            class="text-blue-600 hover:text-blue-800 transition-colors print:hidden">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                    @endif
                                    
                                    @if(in_array('name_ar', $fields))
                                        <td class="px-3 py-1.5 whitespace-nowrap text-xs font-medium text-slate-700 print:text-[8pt]">{{ $student->name_ar }}
                                            
                                        @php
                                         if(isset($previousExternalExamCount[$student->university_no]) && $previousExternalExamCount[$student->university_no] > 0) {
                                            echo 'Ex' . $previousExternalExamCount[$student->university_no] ;
                                         }
                                         if(isset($previousRepeatCount[$student->university_no]) && $previousRepeatCount[$student->university_no] > 0) {
                                            echo ' R' . $previousRepeatCount[$student->university_no] ;
                                         }
                                        @endphp
                                        </td>
                                    @endif

                                    @if(in_array('name_en', $fields))
                                        <td class="px-3 py-1.5 whitespace-nowrap text-xs font-medium text-slate-700 text-left print:text-[8pt]" dir="ltr">
                                            {{ $student->name_en ?? '-' }}
                                        </td>
                                    @endif
                                    
                                    @foreach($subjects as $subjCode)
                                        @php
                                            $mark = $student->marks->firstWhere('subject_code', $subjCode);
                                            $grade = $mark->grade ?? '-';
                                            $isFail = in_array(trim($grade), ['ر', 'F', 'ض', 'D']);
                                        @endphp
                                        <td class="px-2 py-1.5 text-center whitespace-nowrap text-xs border-x border-slate-100 print:text-[8pt] {{ $isFail ? 'bg-fail print:bg-fail-print font-bold' : 'text-slate-800' }}">
                                             @if(in_array('grade', $fields))
                                                 {{ $grade }}
                                             @endif
                                              @if(in_array('mark', $fields))
                                                 {{ $mark->mark ?? '-' }}
                                             @endif
                                        </td>
                                    @endforeach

                                    @if(in_array('sumMarks', $fields))
                                        <td class="px-3 py-1.5 text-center whitespace-nowrap text-xs font-bold text-slate-800 print:text-[8pt]"> {{ $student->recom->first()->sumMarks ?? '-' }}</td>
                                    @endif

                                    @if(in_array('crch', $fields))
                                        <td class="px-3 py-1.5 text-center whitespace-nowrap text-xs text-slate-800 print:text-[8pt]">
                                             {{ $student->recom->first()->crch ?? '-' }}
                                        </td>
                                    @endif
                                    
                                    @if(in_array('crch_fail', $fields))
                                        <td class="px-3 py-1.5 text-center whitespace-nowrap text-xs text-slate-800 print:text-[8pt]">
                                             {{ $student->recom->first()->crch_fail ?? '-' }}
                                        </td>
                                    @endif

                                    @if(in_array('point', $fields))
                                        <td class="px-3 py-1.5 text-center whitespace-nowrap text-xs text-slate-800 print:text-[8pt]">
                                            {{ $student->recom->first()->points ?? '-' }}
                                        </td>
                                    @endif
                                    
                                    @if(in_array('subjectFail', $fields))
                                        <td class="px-3 py-1.5 text-center whitespace-nowrap text-xs text-slate-800 print:text-[8pt]">
                                            @if(count($student->failed_subject_names ?? []) > 0)
                                                {{ implode('، ', $student->failed_subject_names) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endif
                                    
                                    @if(in_array('gpa', $fields))
                                        <td class="px-3 py-1.5 text-center whitespace-nowrap text-xs font-bold text-blue-700 print:text-[8pt]"> {{ $student->recom->first()->gpa ?? '-' }}</td>
                                    @endif
                                    
                                    @if(in_array('recom', $fields))
                                        <td class="px-3 py-1.5 text-center whitespace-nowrap text-xs print:text-[8pt]">
                                            @if($student->recom && $student->recom->isNotEmpty() && $student->recom->first()->recom_code)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-100 print:bg-transparent print:border-none print:text-black print:p-0">
                                                    {{ $student->recom->first()->recomName->name_ar ?? $student->recom->first()->recom_code }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="25" class="px-3 py-6 text-center text-xs font-medium text-slate-400 bg-slate-50/50">
                                        لا توجد بيانات طلاب لعرضها.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table> -->
              
           <table class="w-full text-right border-collapse print-table">
    <thead>
        <tr class="bg-white text-black text-xs font-bold tracking-wider uppercase print:bg-white print:text-black print:text-[7pt]">
            
            @if(in_array('ids', $fields))
                <th class="px-3 py-2 border-b-2 border-black">م</th>
            @endif
            
            @if(in_array('indexno', $fields))
                <th class="px-3 py-2 border-b-2 border-black">رقم الجلوس</th>
            @endif

            @if(in_array('university_no', $fields))
                <th class="px-3 py-2 border-b-2 border-black">الرقم الجامعي</th>
            @endif
            
            @if(in_array('name_ar', $fields))
                <th class="px-3 py-2 border-b-2 border-black">اسم الطالب</th>
            @endif

            @if(in_array('name_en', $fields))
                <th class="px-3 py-2 border-b-2 border-black text-left">Name (EN)</th>
            @endif
            
            @foreach($subjects as $subjCode)
                <th class="px-1 py-2 border-b-2 border-black text-center">
                    <div style="writing-mode: vertical-rl; transform: rotate(180deg); font-size: 7pt;">
                        {{ $subjectNames[$subjCode]->name_ar ?? $subjCode }}
                    </div>
                </th>
            @endforeach

            {{-- تم التعديل هنا: استخدام sumMarks بدلاً من mark ليتطابق مع الـ tbody --}}
            @if(in_array('sumMarks', $fields))
                <th class="px-3 py-2 border-b-2 border-black text-center">المجموع</th>
            @endif

            @if(in_array('crch', $fields))
                <th class="px-3 py-2 border-b-2 border-black text-center">الساعات</th>
            @endif

            @if(in_array('crch_fail', $fields))
                <th class="px-3 py-2 border-b-2 border-black text-center">ساعات الرسوب</th>
            @endif

            @if(in_array('point', $fields))
                <th class="px-3 py-2 border-b-2 border-black text-center">النقاط</th>
            @endif

            @if(in_array('subjectFail', $fields))
                <th class="px-3 py-2 border-b-2 border-black text-center">مواد الملحق</th>
            @endif
            
            @if(in_array('gpa', $fields))
                <th class="px-3 py-2 border-b-2 border-black text-center">المعدل</th>
            @endif
            
            @if(in_array('recom', $fields))
                <th class="px-3 py-2 border-b-2 border-black text-center">القرار</th>
            @endif
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
        @forelse($students as $student)
            <tr class="hover:bg-slate-50/70 transition duration-150 odd:bg-slate-50/20 print:hover:bg-transparent print:odd:bg-transparent">
                
                @if(in_array('ids', $fields))
                    <td class="px-3 py-1.5 whitespace-nowrap text-xs font-semibold text-slate-900 print:text-[8pt]">
                        {{ $loop->iteration }}
                    </td>
                @endif
                
                @if(in_array('indexno', $fields))
                    <td class="px-3 py-1.5 whitespace-nowrap text-xs font-semibold text-slate-900 print:text-[8pt]">
                        {{ $student->indexno ?? '-' }}
                    </td>
                @endif

                @if(in_array('university_no', $fields))
                <td class="px-3 py-1.5 whitespace-nowrap text-xs font-semibold text-slate-900 print:text-[8pt]">
                    <div class="flex items-center gap-2">
                        <span>{{ $student->university_no }}</span>
                        
                        <a href="{{ route('student.transcript.print', ['university_no' => $student->university_no]) }}" 
                        target="_blank"
                        title="طباعة السجل الأكاديمي"
                        class="text-blue-600 hover:text-blue-800 transition-colors print:hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </a>
                    </div>
                </td>
                @endif
                
                @if(in_array('name_ar', $fields))
                    <td class="px-3 py-1.5 whitespace-nowrap text-xs font-medium text-slate-700 print:text-[8pt]">{{ $student->name_ar }}
                        
                    @php
                     if(isset($previousExternalExamCount[$student->university_no]) && $previousExternalExamCount[$student->university_no] > 0) {
                        echo 'Ex' . $previousExternalExamCount[$student->university_no] ;
                     }
                     if(isset($previousRepeatCount[$student->university_no]) && $previousRepeatCount[$student->university_no] > 0) {
                        echo ' R' . $previousRepeatCount[$student->university_no] ;
                     }
                    @endphp
                    </td>
                @endif

                @if(in_array('name_en', $fields))
                    <td class="px-3 py-1.5 whitespace-nowrap text-xs font-medium text-slate-700 text-left print:text-[8pt]" dir="ltr">
                        {{ $student->name_en ?? '-' }}
                    </td>
                @endif
                
                @foreach($subjects as $subjCode)
                    @php
                        $mark = $student->marks->firstWhere('subject_code', $subjCode);
                        $grade = $mark->grade ?? '-';
                        $isFail = in_array(trim($grade), ['ر', 'F', 'ض', 'D']);
                    @endphp
                    <td class="px-2 py-1.5 text-center whitespace-nowrap text-xs border-x border-slate-100 print:text-[8pt] {{ $isFail ? 'bg-fail print:bg-fail-print font-bold' : 'text-slate-800' }}">
                         @if(in_array('grade', $fields))
                             {{ $grade }}
                         @endif
                          @if(in_array('mark', $fields))
                             {{ $mark->mark ?? '-' }}
                         @endif
                    </td>
                @endforeach

                @if(in_array('sumMarks', $fields))
                    <td class="px-3 py-1.5 text-center whitespace-nowrap text-xs font-bold text-slate-800 print:text-[8pt]"> {{ $student->recom->first()->sumMarks ?? '-' }}</td>
                @endif

                @if(in_array('crch', $fields))
                    <td class="px-3 py-1.5 text-center whitespace-nowrap text-xs text-slate-800 print:text-[8pt]">
                         {{ $student->recom->first()->crch ?? '-' }}
                    </td>
                @endif
                
                @if(in_array('crch_fail', $fields))
                    <td class="px-3 py-1.5 text-center whitespace-nowrap text-xs text-slate-800 print:text-[8pt]">
                         {{ $student->recom->first()->crch_fail ?? '-' }}
                    </td>
                @endif

                @if(in_array('point', $fields))
                    <td class="px-3 py-1.5 text-center whitespace-nowrap text-xs text-slate-800 print:text-[8pt]">
                        {{ $student->recom->first()->points ?? '-' }}
                    </td>
                @endif
                
                @if(in_array('subjectFail', $fields))
                    <td class="px-3 py-1.5 text-center whitespace-nowrap text-xs text-slate-800 print:text-[8pt]">
                        @if(count($student->failed_subject_names ?? []) > 0)
                            {{ implode('، ', $student->failed_subject_names) }}
                        @else
                            -
                        @endif
                    </td>
                @endif
                
                @if(in_array('gpa', $fields))
                    <td class="px-3 py-1.5 text-center whitespace-nowrap text-xs font-bold text-blue-700 print:text-[8pt]"> {{ $student->recom->first()->gpa ?? '-' }}</td>
                @endif
                
                @if(in_array('recom', $fields))
                    <td class="px-3 py-1.5 text-center whitespace-nowrap text-xs print:text-[8pt]">
                        @if($student->recom && $student->recom->isNotEmpty() && $student->recom->first()->recom_code)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-100 print:bg-transparent print:border-none print:text-black print:p-0">
                                {{ $student->recom->first()->recomName->name_ar ?? $student->recom->first()->recom_code }}
                            </span>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="25" class="px-3 py-6 text-center text-xs font-medium text-slate-400 bg-slate-50/50">
                    لا توجد بيانات طلاب لعرضها.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- الصفحة الثالثة: الجدول الموحد (يظهر في الشاشة فقط) -->
        <!-- ========================================== -->
        <div class="print-break-before print:pt-2 hide-on-print">
            <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-200 print-card print:border-1">
                
                @php
                    $allGrades = [];
                    foreach($subjectStats as $stats) {
                        foreach(array_keys($stats) as $grade) {
                            if(!in_array($grade, $allGrades)) {
                                $allGrades[] = $grade;
                            }
                        }
                    }
                @endphp

                <!-- الجدول الموحد للتقديرات -->
                <div class="w-full mb-4 overflow-x-auto print:mb-2">
                    <h4 class="text-md font-bold text-slate-800 mb-2 text-center bg-slate-100 w-full py-1.5 rounded-lg print:text-xs print:bg-transparent print:border-b print:border-black">جدول التقديرات التجميعي للمواد</h4>
                    <table class="w-full text-center border-collapse print-table print-summary-table">
                        <thead>
                            <tr class="bg-blue-50 print:bg-white">
                                <th class="px-2 py-1.5 border border-slate-200 text-xs font-bold text-slate-800 text-right print:text-[7pt] print:border-black">المادة \ التقدير</th>
                                @foreach($allGrades as $grade)
                                    <th class="px-2 py-1.5 border border-slate-200 text-xs font-bold text-blue-700 print:text-[7pt] print:text-black print:border-black">{{ $grade }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($subjectStats as $subjCode => $stats)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-2 py-1.5 border border-slate-200 text-xs font-bold text-slate-700 text-right bg-slate-50 print:text-[7pt] print:bg-transparent print:border-black">
                                        {{ $subjectNames[$subjCode]->name_ar ?? $subjCode }}
                                    </td>
                                    @foreach($allGrades as $grade)
                                        <td class="px-2 py-1.5 border border-slate-200 text-xs font-semibold text-slate-800 print:text-[7pt] print:border-black">
                                            {{ $stats[$grade] ?? 0 }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- الرسم البياني (يظهر في الشاشة فقط) -->
                <div class="w-full mb-4 print:mb-2 print-chart-container">
                    <h4 class="text-md font-bold text-slate-800 mb-2 text-center bg-slate-100 w-full py-1.5 rounded-lg print:text-xs print:bg-transparent print:border-b print:border-black">الرسم البياني للتقديرات</h4>
                    <div class="w-full h-[280px] flex items-center justify-center print:h-[180px]">
                        <canvas id="unified-chart"></canvas>
                    </div>
                </div>

            </div>
        </div>

        <!-- ========================================== -->
        <!-- الفوتر للشاشة (يظهر في الشاشة فقط) -->
        <!-- ========================================== -->

 @php
    $metadata = (object) ($facultyInfo->metadata ?? []);

    $deanName = $metadata->dean_name ?? 'ادخل اسم العميد ';
    $registrarName = $metadata->registrar_name ?? 'ادخل اسم المسجل';
    $academicsName = $metadata->academics_name ?? 'ادخل اسم رئيس المكتب الأكاديمي';

    $metadataDepartment = (object) ($departmentInfo->metadata ?? []);
    $departmentName = $metadataDepartment->department_name ?? 'ادخل اسم القسم';
@endphp

        <div class="mt-6 pt-4 border-t-2 border-slate-300 hide-on-print">
            <div class="grid grid-cols-4 gap-4 text-center">
                @if($academicsName)
                <div class="flex flex-col items-center">
                    <h4 class="font-bold text-slate-800 text-sm mb-1">{{ $academicsName }}</h4>
                    <p class="text-xs text-slate-600 mb-4">رئيس المكتب الأكاديمي</p>
                    <div class="signature-line"></div>
                    <span class="text-xs text-slate-500">التوقيع: _____________</span>
                </div>
                @endif
                @if($registrarName)
                <div class="flex flex-col items-center">
                    <h4 class="font-bold text-slate-800 text-sm mb-1">{{ $registrarName }}</h4>
                    <p class="text-xs text-slate-600 mb-4">المسجل</p>
                    <div class="signature-line"></div>
                    <span class="text-xs text-slate-500">التوقيع: _____________</span>
                </div>
                @endif
                @if($deanName)
                <div class="flex flex-col items-center">
                    <h4 class="font-bold text-slate-800 text-sm mb-1">{{ $deanName }}</h4>
                    <p class="text-xs text-slate-600 mb-4">عميد الكلية</p>
                    <div class="signature-line"></div>
                    <span class="text-xs text-slate-500">التوقيع: _____________</span>
                </div>
                @endif

                @if($departmentName)
                 <div class="flex flex-col items-center">
                    <h4 class="font-bold text-slate-800 text-sm mb-1">{{ $departmentName }}</h4>
                    <p class="text-xs text-slate-600 mb-4">رئيس القسم </p>
                    <div class="signature-line"></div>
                    <span class="text-xs text-slate-500">التوقيع: _____________</span>
                </div>
                @endif

            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const subjectStats = @json($subjectStats);
            const subjectNames = @json($subjectNames);
            const allGrades = @json($allGrades);

            const labels = Object.keys(subjectStats).map(code => {
                return subjectNames[code] ? subjectNames[code].name_ar : code;
            });

            const vibrantColors = [
                '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', 
                '#FFEAA7', '#DDA0DD', '#FF8A5C', '#A29BFE',
                '#FD79A8', '#00B894', '#0984E3', '#FDCB6E'
            ];

            function getColorForGrade(grade, index) {
                const failGrades = ['ر', 'F', 'ض', 'D'];
                if (failGrades.includes(grade.trim())) {
                    return '#E74C3C';
                }
                return vibrantColors[index % vibrantColors.length];
            }

            const datasets = allGrades.map((grade, index) => {
                return {
                    label: grade,
                    data: Object.keys(subjectStats).map(code => subjectStats[code][grade] || 0),
                    backgroundColor: getColorForGrade(grade, index),
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    borderRadius: 4,
                    hoverBorderWidth: 3
                };
            });

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
                                        size: 12,
                                        weight: 'bold'
                                    },
                                    padding: 15,
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    font: {
                                        family: 'Tajawal',
                                        size: 10,
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    font: {
                                        family: 'Tajawal',
                                        size: 10
                                    }
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
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
</html>