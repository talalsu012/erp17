<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير النتائج</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');

        body {
            font-family: 'Tajawal', sans-serif;
        }

        .print-only {
            display: none;
        }

        @media print {
            .no-print {
                display: none;
            }

            .print-only {
                display: block;
            }

            body {
                padding: 0;
                margin: 0;
                font-size: 12px;
            }

            .break-after {
                page-break-after: always;
            }
        }

        /* Custom colors for grades */
        .bg-pass {
            background-color: #d1fae5;
        }

        .bg-fail {
            background-color: #fee2e2;
        }

        .bg-alternate {
            background-color: #fef3c7;
        }

        .bg-excellent {
            background-color: #dbeafe;
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Toggle switch styles */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #3b82f6;
        }

        .toggle-checkbox:checked+.toggle-label {
            background-color: #3b82f6;
        }


        /* التنسيق الجديد لأسماء المواد */
        .vertical-text-cell {
            height: 180px;
            /* أقصى ارتفاع مسموح به */
            min-width: 35px;
            max-width: 45px;
            vertical-align: middle;
            padding: 0 !important;
        }

        .rotate-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            width: 100%;
        }

        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            /* لجعل القراءة من الأسفل للأعلى */
            white-space: nowrap;
            text-align: center;
            font-size: 11px;
            font-weight: 600;
        }

        @media print {
            table {
                font-size: 10px;
            }

            .vertical-text-cell {
                height: 150px;
            }

            .p-2 {
                padding: 2px !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Print Button -->
        <button onclick="window.print()"
            class="no-print fixed bottom-4 left-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-lg">
            طباعة التقرير
        </button>



        <!-- Report Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
            <!-- Header -->
            <div>
                <div class="bg-blue-800 text-white p-6 text-center">
                    <div class="text-sm font-arial mb-4 text-center">بسم الله الرحمن الرحيم</div>
                    <h1 class="text-2xl font-bold mb-2">{{ __('main.univ_name') }}</h1>
                    <div class="text-xl font-arial mb-4 text-center">{{ $faculty->name_ar }} - {{ $batch }} - {{ $level->name_ar }} - {{ $semesterLabel }} ( {{ $absentLabel }})</div>
                    <h3 class="text-lg mb-4">نتيجة امتحان العام الدراسي - {{ $batch }}</h3>
                </div>

            </div>


            <!-- Control Panel -->
            <div class="no-print bg-gray-100 p-4 border-b border-gray-200">
                <div class="flex flex-wrap gap-6 justify-center items-center">
                    <!-- Toggle for marks/grades -->
                    <div class="flex items-center">
                        <span class="mr-2 text-gray-700">عرض الدرجات/التقديرات:</span>
                        <div class="relative inline-block w-12 mr-2 align-middle select-none">
                            <input type="checkbox" id="toggleMarks"
                                class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                                checked />
                            <label for="toggleMarks"
                                class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                        </div>
                        <span id="marksLabel" class="text-gray-700">الدرجات والتقديرات</span>
                    </div>

                    <!-- Toggle for recommendations stats -->
                    <div class="flex items-center">
                        <span class="mr-2 text-gray-700">إحصائيات التوصيات:</span>
                        <div class="relative inline-block w-12 mr-2 align-middle select-none">
                            <input type="checkbox" id="toggleRecStats"
                                class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                                checked />
                            <label for="toggleRecStats"
                                class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                        </div>
                        <span id="recStatsLabel" class="text-gray-700">معروضة</span>
                    </div>

                    <!-- Toggle for subjects stats -->
                    <div class="flex items-center">
                        <span class="mr-2 text-gray-700">إحصائيات المواد:</span>
                        <div class="relative inline-block w-12 mr-2 align-middle select-none">
                            <input type="checkbox" id="toggleSubStats"
                                class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                                checked />
                            <label for="toggleSubStats"
                                class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                        </div>
                        <span id="subStatsLabel" class="text-gray-700">معروضة</span>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50">
                <div class="stat-card bg-white rounded-lg shadow p-4 text-center">
                    <h3 class="text-gray-500">عدد الطلاب</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ count($students) }}</p>
                </div>
                <div class="stat-card bg-white rounded-lg shadow p-4 text-center">
                    <h3 class="text-gray-500">الناجحون</h3>
                    <p class="text-2xl font-bold text-green-600">
                        {{ $students->filter(fn($s) => $s->recom->isNotEmpty() && $s->recom[0]['gpa'] >= 2.0)->count() }}
                    </p>
                </div>
                <div class="stat-card bg-white rounded-lg shadow p-4 text-center">
                    <h3 class="text-gray-500">الراسبون</h3>
                    <p class="text-2xl font-bold text-red-600">
                        {{ $students->filter(fn($s) => $s->recom->isNotEmpty() && $s->recom[0]['gpa'] < 2.0)->count() }}
                    </p>
                </div>
                <div class="stat-card bg-white rounded-lg shadow p-4 text-center">
                    <h3 class="text-gray-500">أعلى معدل</h3>
                    <p class="text-2xl font-bold text-purple-600">
                        @php
                            $maxGpa = $students->filter(fn($s) => $s->recom->isNotEmpty())->max(fn($s) => $s->recom[0]['gpa']);
                            echo $maxGpa ? number_format($maxGpa, 2) : 'N/A';
                        @endphp
                    </p>
                </div>
            </div>

            {{ $field }} //universityno,name_ar,...
            <!-- Table -->
            <div class="p-4 overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border border-gray-300 p-2" rowspan="2">م</th>
                            <th class="border border-gray-300 p-2" rowspan="2">رقم الجامعي</th>
                            <th class="border border-gray-300 p-2" rowspan="2">اسم الطالب</th>
                            <th class="border border-gray-300 p-2 text-center" colspan="{{ count($subjects) }}">المواد
                                الدراسية</th>
                            <th class="border border-gray-300 p-2" rowspan="2">المجموع</th>
                            <th class="border border-gray-300 p-2" rowspan="2">النسبة</th>
                            <th class="border border-gray-300 p-2" rowspan="2">التقدير</th>

                        </tr>
                        <tr class="bg-gray-200">
                            @foreach($subjects as $mark)
                                <th class="border border-gray-300 vertical-text-cell">
                                    <div class="rotate-container">
                                        <span class="vertical-text">
                                            {{ $mark?->name_ar ?? 'مادة محذوفة' }}
                                        </span>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $student)
                            @php
                                $sumMarks = '';
                                $gpa = '';
                                $recomName = '';
                                $rowClass = '';

                                // Calculate sum of marks and GPA if recommendation exists
                                if ($student->recom->isNotEmpty()) {

                                    $gpaEntry = $student->recom[0]['gpa'];
                                    $sumMarks = $student->recom[0]['sumMarks'];


                                    if ($semester == 3) {
                                        $countSubject = ($student->recom[1]['countSubject'] ?? 0) + ($student->recom[0]['countSubject'] ?? 0);
                                        $sumMarks = ($student->recom[0]['sumMarks'] ?? 0) + ($student->recom[1]['sumMarks'] ?? 0);

                                        $gpaEntry = $sumMarks / $countSubject;
                                    }


                                    $gpa = number_format($gpaEntry, 2);
                                    $recomName = $student->recom[0]['recom_code'];

                                    // Determine row color based on GPA
                                    if ($student->recom[0]['gpa'] >= 80) {
                                        $rowClass = 'bg-excellent';
                                    } elseif ($gpaEntry >= 50) {
                                        $rowClass = 'bg-pass';
                                    } else {
                                        $rowClass = 'bg-fail';
                                    }
                                }
                            @endphp

                            <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} {{ $rowClass }}">
                                <td class="border border-gray-300 p-2 text-center">{{ $index + 1 }}</td>
                                <td class="border border-gray-300 p-2 text-center">{{ $student->university_no }}</td>
                                <td class="border border-gray-300 p-2">{{ $student->name_ar }}</td>

                                @foreach($subjects as $markAll)
                                    @php
                                        $mark = $student->marks->firstWhere('subject_code', $markAll?->code);
                                        $cellClass = '';

                                        if ($mark) {
                                            if (str_contains($mark->grade, 'بديل')) {
                                                $cellClass = 'bg-alternate';
                                            } elseif (($mark->mark < 50 && $student->stady_code == 20060001) || ($mark->mark < 40 && $student->stady_code == 20060002)) {
                                                $cellClass = 'bg-fail';
                                            }
                                    } @endphp <td
                                        class="border border-gray-300 p-2 text-center {{ $cellClass }} marks-cell">
                                        {{ $mark ? $mark->mark . '  ' . $mark->grade : '-' }}
                                    </td>
                                @endforeach

                                <td class="border border-gray-300 p-2 text-center font-semibold">{{ $sumMarks }}</td>
                                <td class="border border-gray-300 p-2 text-center font-semibold">{{ $gpa }}</td>
                                <td class="border border-gray-300 p-2 text-center">
                                    @if($student->recom->isNotEmpty() && $student->recom[0]->recomName && str_contains($fields, 'recom'))
                                        {{ $student->recom[0]->recomName->name }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Legend -->
            <div class="p-4 bg-gray-100 border-t border-gray-300">
                <div class="flex flex-wrap gap-4 justify-center">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-excellent rounded mr-2"></div>
                        <span>ممتاز </span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-pass rounded mr-2"></div>
                        <span>ناجح</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-fail rounded mr-2"></div>
                        <span>راسب</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-alternate rounded mr-2"></div>
                        <span>امتحان بديل</span>
                    </div>
                </div>
            </div>

            <!-- Recommendations Statistics Section -->
            <div id="recommendationsStatsSection">
                @php
                    // تجميع التوصيات
                    $recommendationsStats = [];
                    foreach ($students as $student) {
                        if ($student->recom->isNotEmpty() && $student->recom[0]->recomName) {
                            $code = $student->recom[0]->recom_code;
                            $name = $student->recom[0]->recomName->name;

                            if (!isset($recommendationsStats[$code])) {
                                $recommendationsStats[$code] = [
                                    'name' => $name,
                                    'count' => 0,
                                    'code' => $code
                                ];
                            }
                            $recommendationsStats[$code]['count']++;
                        }
                    }

                    // ترتيب التوصيات حسب الأكثر تكراراً
                    uasort($recommendationsStats, function ($a, $b) {
                        return $b['count'] <=> $a['count'];
                    });
                @endphp

                @if(count($recommendationsStats) > 0)
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                        <div class="bg-blue-800 text-white p-4 text-center">
                            <h3 class="text-xl font-bold">إحصائيات التوصيات</h3>
                        </div>

                        <div class="p-4 overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-200">
                                        <th class="border border-gray-300 p-2">كود التوصية</th>
                                        <th class="border border-gray-300 p-2">اسم التوصية</th>
                                        <th class="border border-gray-300 p-2">عدد الطلاب</th>
                                        <th class="border border-gray-300 p-2">النسبة المئوية</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalStudents = count($students);
                                    @endphp

                                    @foreach($recommendationsStats as $stat)
                                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                            <td class="border border-gray-300 p-2 text-center font-mono">{{ $stat['code'] }}
                                            </td>
                                            <td class="border border-gray-300 p-2">{{ $stat['name'] }}</td>
                                            <td class="border border-gray-300 p-2 text-center font-bold text-indigo-600">
                                                {{ $stat['count'] }}
                                            </td>
                                            <td class="border border-gray-300 p-2 text-center">
                                                @php
                                                    $percentage = $totalStudents > 0 ? round(($stat['count'] / $totalStudents) * 100, 2) : 0;
                                                @endphp
                                                <span
                                                    class="font-semibold {{ $percentage >= 50 ? 'text-green-600' : 'text-blue-600' }}">
                                                    {{ $percentage }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach

                                    <!-- إجمالي -->
                                    <tr class="bg-gray-100 font-bold">
                                        <td class="border border-gray-300 p-2 text-center" colspan="2">الإجمالي</td>
                                        <td class="border border-gray-300 p-2 text-center text-indigo-600">
                                            {{ array_sum(array_column($recommendationsStats, 'count')) }}
                                        </td>
                                        <td class="border border-gray-300 p-2 text-center">100%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- رسم بياني مصغر -->
                        <div class="p-4">
                            <h4 class="text-center font-medium mb-2">توزيع التوصيات</h4>
                            <div class="flex h-8 bg-gray-200 rounded overflow-hidden">
                                @foreach($recommendationsStats as $stat)
                                    @php
                                        $percentage = $totalStudents > 0 ? ($stat['count'] / $totalStudents) * 100 : 0;
                                        $color = match (true) {
                                            str_contains($stat['name'], 'ناجح') => 'bg-green-500',
                                            str_contains($stat['name'], 'راسب') => 'bg-red-500',
                                            str_contains($stat['name'], 'امتياز') => 'bg-blue-500',
                                            default => 'bg-indigo-400'
                                        };
                                    @endphp
                                    <div class="{{ $color }} hover:opacity-90 transition-opacity"
                                        style="width: {{ $percentage }}%" title="{{ $stat['name'] }}: {{ $percentage }}%">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Subjects Statistics Section -->
            <div id="subjectsStatsSection">
                @php
                    // تجميع إحصائيات المواد
                    $subjectsStats = [];
                    foreach ($subjects as $subject) {
                        $passed = 0;
                        $failed = 0;
                        $alternate = 0;
                        $totalStudents = 0;
                        $sumMarks = 0;
                        $maxMark = 0;
                        $minMark = 100;

                        foreach ($students as $student) {
                            $mark = $student->marks->firstWhere('subject_code', $subject->code);
                            if ($mark && $mark->mark !== null) {
                                $totalStudents++;
                                $sumMarks += $mark->mark;
                                $maxMark = max($maxMark, $mark->mark);
                                $minMark = min($minMark, $mark->mark);

                                if (str_contains($mark->grade, 'بديل') || $mark->reson_code > 2000) {
                                    $alternate++;
                                } elseif ($mark->mark < 50 || str_contains($mark->grade, 'راسب')) {
                                    $failed++;
                                } else {
                                    $passed++;
                                }
                            }
                        }

                        $subjectsStats[] = [
                            'name' => $subject?->name_ar ?? 'مادة محذوفة',
                            'code' => $subject?->code,
                            'passed' => $passed,
                            'failed' => $failed,
                            'alternate' => $alternate,
                            'total' => $totalStudents,
                            'sum_marks' => $sumMarks,
                            'avg_mark' => $totalStudents > 0 ? round($sumMarks / $totalStudents, 2) : 0,
                            'max_mark' => $maxMark,
                            'min_mark' => $minMark == 100 ? 0 : $minMark,
                            'pass_rate' => $totalStudents > 0 ? round(($passed / $totalStudents) * 100, 2) : 0
                        ];
                    }

                    // ترتيب المواد حسب نسبة النجاح
                    usort($subjectsStats, function ($a, $b) {
                        return $b['pass_rate'] <=> $a['pass_rate'];
                    });
                @endphp

                @if(count($subjectsStats) > 0)
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
                        <div class="bg-blue-800 text-white p-4 text-center">
                            <h3 class="text-xl font-bold">إحصائيات المواد الدراسية</h3>
                        </div>

                        <div class="p-4 overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-200">
                                        <th class="border border-gray-300 p-2">المادة</th>
                                        <th class="border border-gray-300 p-2">عدد الطلاب</th>
                                        <th class="border border-gray-300 p-2">الناجحون</th>
                                        <th class="border border-gray-300 p-2">الراسبون</th>
                                        <th class="border border-gray-300 p-2">بديل</th>
                                        <th class="border border-gray-300 p-2">أعلى درجة</th>
                                        <th class="border border-gray-300 p-2">أقل درجة</th>
                                        <th class="border border-gray-300 p-2">المعدل</th>
                                        <th class="border border-gray-300 p-2">نسبة النجاح %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjectsStats as $stat)
                                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                            <td class="border border-gray-300 p-2">{{ $stat['name'] }}</td>
                                            <td class="border border-gray-300 p-2 text-center">{{ $stat['total'] }}</td>
                                            <td class="border border-gray-300 p-2 text-center text-green-600 font-semibold">
                                                {{ $stat['passed'] }}</td>
                                            <td class="border border-gray-300 p-2 text-center text-red-600 font-semibold">
                                                {{ $stat['failed'] }}</td>
                                            <td class="border border-gray-300 p-2 text-center text-yellow-600 font-semibold">
                                                {{ $stat['alternate'] }}</td>
                                            <td class="border border-gray-300 p-2 text-center text-blue-600 font-semibold">
                                                {{ $stat['max_mark'] }}</td>
                                            <td class="border border-gray-300 p-2 text-center text-purple-600 font-semibold">
                                                {{ $stat['min_mark'] }}</td>
                                            <td class="border border-gray-300 p-2 text-center font-semibold">
                                                {{ $stat['avg_mark'] }}</td>
                                            <td class="border border-gray-300 p-2 text-center font-semibold">
                                                <span
                                                    class="{{ $stat['pass_rate'] >= 50 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $stat['pass_rate'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach

                                    <!-- إجمالي الإحصائيات -->
                                    @php
                                        $totalPassed = array_sum(array_column($subjectsStats, 'passed'));
                                        $totalFailed = array_sum(array_column($subjectsStats, 'failed'));
                                        $totalAlternate = array_sum(array_column($subjectsStats, 'alternate'));
                                        $totalStudents = array_sum(array_column($subjectsStats, 'total'));
                                        $overallPassRate = $totalStudents > 0 ? round(($totalPassed / $totalStudents) * 100, 2) : 0;
                                    @endphp

                                </tbody>
                            </table>
                        </div>

                        <!-- رسم بياني لنسب النجاح -->
                        <div class="p-4">
                            <h4 class="text-center font-medium mb-2">نسب النجاح حسب المادة</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($subjectsStats as $stat)
                                    <div>
                                        <div class="flex justify-between mb-1">
                                            <span class="text-sm font-medium">{{ $stat['name'] }}</span>
                                            <span
                                                class="text-sm font-medium {{ $stat['pass_rate'] >= 50 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $stat['pass_rate'] }}%
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full"
                                                style="width: {{ $stat['pass_rate'] }}%">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="bg-gray-100 p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <p class="font-bold mb-2">امين الشؤون العلمية</p>
                        <div class="h-16 border-b border-gray-400 mx-auto w-48"></div>
                        <p class="mt-2">د/ محمد الامين حسن محمد علي</p>
                    </div>
                    <div class="text-center">
                        <p class="font-bold mb-2">ضابط الامتحانات</p>
                        <div class="h-16 border-b border-gray-400 mx-auto w-48"></div>
                        <p class="mt-2">حسن عمر حسن الخليفة</p>
                    </div>
                    <div class="text-center">
                        <p class="font-bold mb-2">اعضاء اللجنة</p>
                        <div class="space-y-2">
                            <div class="h-8 border-b border-gray-400 mx-auto w-48"></div>
                            <div class="h-8 border-b border-gray-400 mx-auto w-48"></div>
                            <div class="h-8 border-b border-gray-400 mx-auto w-48"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print Footer (only shows when printing) -->
        <div class="print-only text-center text-xs mt-8">
            <p>تم الطباعة في: {{ now()->format('Y-m-d H:i') }}</p>
            <p>صفحة 1 من 1</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Toggle for marks/grades
            const toggleMarks = document.getElementById('toggleMarks');
            const marksLabel = document.getElementById('marksLabel');
            const marksCells = document.querySelectorAll('.marks-cell');

            // Toggle for recommendations stats
            const toggleRecStats = document.getElementById('toggleRecStats');
            const recStatsLabel = document.getElementById('recStatsLabel');
            const recStatsSection = document.getElementById('recommendationsStatsSection');

            // Toggle for subjects stats
            const toggleSubStats = document.getElementById('toggleSubStats');
            const subStatsLabel = document.getElementById('subStatsLabel');
            const subStatsSection = document.getElementById('subjectsStatsSection');

            // Initialize toggle states from localStorage or default to true
            const marksVisible = localStorage.getItem('marksVisible') !== 'false';
            const recStatsVisible = localStorage.getItem('recStatsVisible') !== 'false';
            const subStatsVisible = localStorage.getItem('subStatsVisible') !== 'false';

            // Set initial toggle states
            toggleMarks.checked = marksVisible;
            toggleRecStats.checked = recStatsVisible;
            toggleSubStats.checked = subStatsVisible;

            // Update labels
            updateMarksLabel(marksVisible);
            updateRecStatsLabel(recStatsVisible);
            updateSubStatsLabel(subStatsVisible);

            // Apply initial visibility
            toggleMarksVisibility(marksVisible);
            toggleRecStatsVisibility(recStatsVisible);
            toggleSubStatsVisibility(subStatsVisible);

            // Add event listeners
            toggleMarks.addEventListener('change', function () {
                const isVisible = this.checked;
                localStorage.setItem('marksVisible', isVisible);
                updateMarksLabel(isVisible);
                toggleMarksVisibility(isVisible);
            });

            toggleRecStats.addEventListener('change', function () {
                const isVisible = this.checked;
                localStorage.setItem('recStatsVisible', isVisible);
                updateRecStatsLabel(isVisible);
                toggleRecStatsVisibility(isVisible);
            });

            toggleSubStats.addEventListener('change', function () {
                const isVisible = this.checked;
                localStorage.setItem('subStatsVisible', isVisible);
                updateSubStatsLabel(isVisible);
                toggleSubStatsVisibility(isVisible);
            });

            // Helper functions
            function updateMarksLabel(isVisible) {
                marksLabel.textContent = isVisible ? 'الدرجات والتقديرات' : 'التقديرات فقط';
            }

            function updateRecStatsLabel(isVisible) {
                recStatsLabel.textContent = isVisible ? 'معروضة' : 'مخفية';
            }

            function updateSubStatsLabel(isVisible) {
                subStatsLabel.textContent = isVisible ? 'معروضة' : 'مخفية';
            }

            function toggleMarksVisibility(isVisible) {
                marksCells.forEach(cell => {
                    const text = cell.textContent.trim();

                    if (isVisible) {
                        cell.textContent = text; // عرض الرقم مع التقدير
                    } else {
                        // حذف الرقم من البداية وإبقاء التقدير
                        cell.textContent = text.replace(/^\d+\s*/, '');
                    }
                });
            }

            function toggleRecStatsVisibility(isVisible) {
                recStatsSection.style.display = isVisible ? 'block' : 'none';
            }

            function toggleSubStatsVisibility(isVisible) {
                subStatsSection.style.display = isVisible ? 'block' : 'none';
            }
        });
    </script>
</body>

</html>