<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتيجة الطلاب - {{ config('university.name_ar') }}</title>
    
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

    <style>
        body { 
            background-color: #f8fafc; 
            color: #000;
        }

        @page {
            size: A4 landscape;
            margin: 12mm 10mm 12mm 10mm;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @media print {
            body {
                background-color: #fff !important;
            }
            .hide-on-print, .no-print {
                display: none !important;
            }
            .print-page-break {
                page-break-after: always !important;
                break-after: page !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }

        .page-wrapper {
            background: #fff;
            margin-bottom: 2rem;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .result-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            margin-bottom: 10px;
        }

        .result-table th, .result-table td {
            border: 1.5px solid #000 !important;
            padding: 5px 6px;
            text-align: center;
            font-size: 13px; /* حجم خط مقروء ومناسب */
            color: #000;
        }

        .result-table th {
            background-color: #f1f5f9 !important;
            font-weight: 800;
        }

        .vertical-th {
            height: 150px;
            width: 35px;
            vertical-align: bottom;
            padding-bottom: 8px !important;
        }

        .vertical-text-wrapper {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            margin: auto;
            font-size: 12px;
            font-weight: 700;
            color: #000;
        }

        .bg-fail-cell {
            background-color: #e5e7eb !important;
            font-weight: bold;
        }
    </style>
</head>
<body class="font-sans antialiased">

@php


    $studentsPerPage = $countstudent;
    $studentPages = $students->chunk($studentsPerPage);

    $universityLogo = asset(config('university.logo'));
    $facultyLogoFile = 'logos/fac' . $facultyInfo->code . '.jpeg';
    $facultyLogoPath = public_path($facultyLogoFile);
    $facultyLogo = asset($facultyLogoFile);
    $facultyLogoExists = is_file($facultyLogoPath);

    $metadata = (object) ($facultyInfo->metadata ?? []);
    $deanName = $metadata->dean_name ?? 'ادخل اسم العميد';
    $registrarName = $metadata->registrar_name ?? 'ادخل اسم المسجل';
    $academicsName = $metadata->academics_name ?? 'ادخل اسم رئيس المكتب الأكاديمي';
    
    $metadataDepartment = (object) ($departmentInfo->metadata ?? []);
    $departmentName = $metadataDepartment->department_name ?? 'ادخل اسم القسم';
@endphp

    <div class="max-w-7xl mx-auto px-4 py-6 print:px-0 print:py-0 print-container">
        
        <div class="grid grid-cols-4 gap-3 mb-6 hide-on-print">
            <div class="bg-white p-3 rounded-lg shadow-sm border border-slate-200 border-r-4 border-r-blue-600 flex items-center justify-between">
                <div><span class="text-slate-500 text-xs font-medium">إجمالي الطلاب</span></div>
                <div class="p-2 bg-blue-50 text-blue-900 rounded-lg"><h3 class="text-2xl font-extrabold">{{ $allStudent }}</h3></div>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border border-slate-200 border-r-4 border-r-blue-600 flex items-center justify-between">
                <div><span class="text-slate-500 text-xs font-medium">الطلاب الجالسين</span></div>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg"><h3 class="text-2xl font-extrabold">{{ $totalStudents }}</h3></div>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border border-slate-200 border-r-4 border-r-emerald-600 flex items-center justify-between">
                <div><span class="text-slate-500 text-xs font-medium">عدد الناجحين</span></div>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg"><h3 class="text-2xl font-extrabold">{{ $passedStudents }}</h3></div>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border border-slate-200 border-r-4 border-r-rose-600 flex items-center justify-between">
                <div><span class="text-slate-500 text-xs font-medium">عدد الراسبين</span></div>
                <div class="p-2 bg-rose-50 text-rose-600 rounded-lg"><h3 class="text-2xl font-extrabold">{{ $failedStudents }}</h3></div>
            </div>
        </div>

        @forelse($studentPages as $chunkIndex => $pageStudents)
            
            <div class="page-wrapper print-container {{ !$loop->last ? 'print-page-break' : '' }}">
                
                <div class="flex justify-between items-center border-b-2 border-black pb-3 mb-3">
                    <div class="w-24 h-24 flex items-center justify-center">
                        <img src="{{ $universityLogo }}" alt="{{ config('university.name_ar') }}" class="max-h-full object-contain">
                    </div>
                    <div class="flex-1 text-center">
                        <p class="text-sm font-bold text-black mb-1">بسم الله الرحمن الرحيم</p>
                        <h1 class="text-xl font-extrabold text-blue-900 mb-1">{{ config('university.name_ar') }} - {{ $facultyInfo->name_ar }}</h1>
                        <p class="text-sm font-bold text-black mb-1">نتيجة الامتحانات {{ $absentName }} لطلاب {{ $levelInfo->name_ar }} {{ $academicsYear }}</p>
                        <p class="text-sm font-bold text-black">القسم: {{ $departmentInfo->name_ar }}</p>
                    </div>
                    <div class="w-24 h-24 flex items-center justify-center">
                        @if ($facultyLogoExists)
                            <img src="{{ $facultyLogo }}" alt="شعار الكلية" class="max-h-full object-contain">
                        @endif
                    </div>
                </div>

                <table class="result-table">
                    <thead>
                        <tr>
                            @if(in_array('ids', $fields))
                                <th class="align-bottom pb-2 w-8">م</th>
                            @endif
                            
                            @if(in_array('indexno', $fields))
                                <th class="align-bottom pb-2">رقم الجلوس</th>
                            @endif

                            @if(in_array('university_no', $fields))
                                <th class="align-bottom pb-2 w-24">الرقم الجامعي</th>
                            @endif
                            
                            @if(in_array('name_ar', $fields))
                                <th class="align-bottom pb-2 text-right px-3 w-56">اسم الطالب</th>
                            @endif

                            @if(in_array('name_en', $fields))
                                <th class="align-bottom pb-2 text-left px-3 w-48">Name (EN)</th>
                            @endif
                            
                            @foreach($subjects as $subjCode)
                                <th class="vertical-th">
                                    <div class="vertical-text-wrapper">
                                        {{ $subjectNames[$subjCode]->name_ar ?? $subjCode }} 
                                        @if (!empty($subjectNames[$subjCode]->crch ?? null))
                                            ({{ $subjectNames[$subjCode]->crch }})
                                        @endif
                                    </div>
                                </th>
                            @endforeach

                            @if(in_array('sumMarks', $fields))
                                <th class="align-bottom pb-2">المجموع</th>
                            @endif

                            @if(in_array('crch', $fields))
                                <th class="align-bottom pb-2 w-16">الساعات</th>
                            @endif

                            @if(in_array('crch_fail', $fields))
                                <th class="align-bottom pb-2 w-16">ساعات الرسوب</th>
                            @endif

                            @if(in_array('point', $fields))
                                <th class="align-bottom pb-2 w-12">النقاط</th>
                            @endif

                            @if(in_array('subjectFail', $fields))
                                <th class="align-bottom pb-2 w-24">مواد الملحق</th>
                            @endif
                            
                            @if(in_array('gpa', $fields))
                                <th class="align-bottom pb-2 w-16">المعدل</th>
                            @endif
                            
                            @if(in_array('recom', $fields))
                                <th class="align-bottom pb-2 w-16">القرار</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pageStudents as $student)
                            @php
                                $actualIndex = ($chunkIndex * $studentsPerPage) + $loop->iteration;
                            @endphp
                            <tr>
                                @if(in_array('ids', $fields))
                                    <td class="font-bold">{{ $actualIndex }}</td>
                                @endif
                                
                                @if(in_array('indexno', $fields))
                                    <td>{{ $student->indexno ?? '-' }}</td>
                                @endif

                                @if(in_array('university_no', $fields))
                                    <td class="font-semibold">{{ $student->university_no }}</td>
                                @endif
                                
                                @if(in_array('name_ar', $fields))
                                    <td class="text-right font-bold whitespace-nowrap px-2">
                                        {{ $student->name_ar }}
                                        @php
                                            if(isset($previousExternalExamCount[$student->university_no]) && $previousExternalExamCount[$student->university_no] > 0) {
                                                echo '<span class="text-xs text-slate-500 mr-1">Ex'.$previousExternalExamCount[$student->university_no].'</span>';
                                            }
                                            if(isset($previousRepeatCount[$student->university_no]) && $previousRepeatCount[$student->university_no] > 0) {
                                                echo '<span class="text-xs text-slate-500 mr-1">R'.$previousRepeatCount[$student->university_no].'</span>';
                                            }
                                        @endphp
                                    </td>
                                @endif

                                @if(in_array('name_en', $fields))
                                    <td class="text-left whitespace-nowrap" dir="ltr">{{ $student->name_en ?? '-' }}</td>
                                @endif
                                
                                @foreach($subjects as $subjCode)
                                    @php
                                        $mark = $student->marks->firstWhere('subject_code', $subjCode);
                                        $grade = $mark->grade ?? '-';
                                        $isFail = in_array(trim($grade), ['ر', 'F', 'ض']);
                                        $displayGrade = preg_replace('/^\+([A-Za-z])$/', '$1+', trim($grade));
                                    @endphp
                                    <td class="{{ $isFail ? 'bg-fail-cell' : '' }}">
                                        @if(in_array('grade', $fields))
                                            <span class="font-bold" dir="ltr">{{ $displayGrade }}</span>
                                        @endif
                                        @if(in_array('grade', $fields) && in_array('mark', $fields))
                                            <span class="mx-1">|</span>
                                        @endif
                                        @if(in_array('mark', $fields))
                                            <span dir="ltr">{{ $mark->mark ?? '-' }}</span>
                                        @endif
                                    </td>
                                @endforeach

                                @if(in_array('sumMarks', $fields))
                                    <td class="font-bold">{{ $student->recom->first()->sumMarks ?? '-' }}</td>
                                @endif

                                @if(in_array('crch', $fields))
                                    <td>{{ $student->recom->first()->crch ?? '-' }}</td>
                                @endif
                                
                                @if(in_array('crch_fail', $fields))
                                    <td>{{ $student->recom->first()->crch_fail ?? '-' }}</td>
                                @endif

                                @if(in_array('point', $fields))
                                    <td>{{ $student->recom->first()->points ?? '-' }}</td>
                                @endif
                                
                                @if(in_array('subjectFail', $fields))
                                    <td class="text-xs">
                                        {{ count($student->failed_subject_names ?? []) > 0 ? implode('، ', $student->failed_subject_names) : '-' }}
                                    </td>
                                @endif
                                
                                @if(in_array('gpa', $fields))
                                    <td class="font-bold text-blue-800">{{ $student->recom->first()->gpa ?? '-' }}</td>
                                @endif
                                
                                @if(in_array('recom', $fields))
                                    <td class="font-bold">
                                        {{ $student->recom->first()->recomName->name_ar ?? $student->recom->first()->recom_code ?? '-' }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="grid grid-cols-3 gap-4 text-center mt-3 pt-2">
                    <div class="flex flex-col items-center">
                        <h4 class="font-bold text-black text-sm">{{ $academicsName }}</h4>
                        <p class="text-sm font-semibold text-black mt-1">رئيس المكتب الأكاديمي</p>
                      
                    </div>
                    
                    <div class="flex flex-col items-center">
                        <h4 class="font-bold text-black text-sm">{{ $registrarName }}</h4>
                        <p class="text-sm font-semibold text-black mt-1">المسجل</p>
                       
                    </div>
                    
                    <div class="flex flex-col items-center">
                        <h4 class="font-bold text-black text-sm">{{ $deanName }}</h4>
                        <p class="text-sm font-semibold text-black mt-1">عميد الكلية</p>
                       
                    </div>
                </div>

            </div>

        @empty
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-10 text-center text-slate-500">
                لا توجد بيانات طلاب لعرضها.
            </div>
        @endforelse

        <div class="fixed bottom-6 left-6 hide-on-print">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-full shadow-lg transition-colors flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                طباعة النتيجة
            </button>
        </div>

    </div>
</body>
</html>