<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>النتيجة النهائية للسنة - {{ config('university.name_ar') }}</title>
    <link rel="icon" href="{{ asset(config('university.icon')) }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f3f4f6; }
        table.year-result { width: 100%; border-collapse: collapse; font-size: 11px; }
        table.year-result th, table.year-result td { border: 1px solid #9ca3af; padding: 3px 5px; text-align: center; }
        table.year-result thead th { background: #1f2937; color: #fff; }
        table.year-result thead tr.sub th { background: #374151; font-weight: 600; }
        table.year-result td.name { text-align: right; white-space: nowrap; }
        table.year-result tr.fail td.decision { color: #b91c1c; font-weight: 700; }
        table.year-result tr.pass td.decision { color: #047857; font-weight: 700; }
        table.year-result tr.pending td.decision { color: #b45309; font-weight: 700; }
        @page { size: A4 landscape; margin: 10mm; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .sheet { box-shadow: none !important; border: 0 !important; padding: 0 !important; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body class="p-4 text-gray-800">
    <div class="sheet mx-auto max-w-[1400px] rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <div class="no-print mb-4 flex items-center justify-end gap-2">
            <a href="{{ $backUrl }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50">رجوع</a>
            <button type="button" onclick="window.print()" class="rounded-md bg-sky-100 px-4 py-2 font-semibold text-sky-900 hover:bg-sky-200">طباعة</button>
        </div>

        <header class="mb-4 flex items-center justify-between gap-4 border-b-2 border-gray-800 pb-3">
            <div class="flex h-20 w-20 items-center justify-center">
                @if ($logos['university'])
                    <img src="{{ $logos['university'] }}" alt="{{ config('university.name_ar') }}" class="max-h-full object-contain">
                @endif
            </div>
            <div class="flex-1 text-center">
                @if (filled($headerTitle))
                    <p class="text-xs font-bold text-gray-700">{{ $headerTitle }}</p>
                @endif
                <h1 class="text-xl font-extrabold">{{ config('university.name_ar') }} - {{ $facultyInfo?->name_ar }}</h1>
                <h2 class="mt-1 text-base font-bold text-blue-900">النتيجة النهائية للسنة الدراسية (مجموع الفصلين)</h2>
                <p class="mt-1 text-xs text-gray-600">
                    العام الدراسي {{ $academicYear }}
                    @if ($departmentInfo) · قسم {{ $departmentInfo->name_ar }} @endif
                    @if ($specialInfo) · تخصص {{ $specialInfo->name_ar }} @endif
                    · {{ $levels->pluck('name_ar')->implode(' + ') }}
                </p>
            </div>
            <div class="flex h-20 w-20 items-center justify-center">
                @if ($logos['faculty'])
                    <img src="{{ $logos['faculty'] }}" alt="" class="max-h-full object-contain">
                @endif
            </div>
        </header>

        <div class="mb-3 grid grid-cols-2 gap-2 text-center text-xs md:grid-cols-5">
            @foreach ([
                ['الطلاب', $summary['total']],
                ['الناجحون', $summary['passed']],
                ['بانتظار الملاحق والبدائل', $summary['pending']],
                ['إعادة أو فصل', $summary['other']],
                ['أعلى معدل سنوي', $summary['highestGpa'] !== null ? number_format($summary['highestGpa'], 2) : '—'],
            ] as [$label, $value])
                <div class="rounded border border-gray-200 bg-gray-50 p-2">
                    <div class="text-gray-500">{{ $label }}</div>
                    <div class="text-lg font-bold">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        <table class="year-result">
            <thead>
                <tr>
                    <th rowspan="2">م</th>
                    <th rowspan="2">الرقم الجامعي</th>
                    <th rowspan="2">الاسم</th>
                    @foreach ($levels as $level)
                        <th colspan="3">{{ $level->name_ar }}</th>
                    @endforeach
                    <th colspan="3">السنة (مجموع الفصلين)</th>
                    <th rowspan="2">القرار النهائي</th>
                </tr>
                <tr class="sub">
                    @foreach ($levels as $level)
                        <th>الساعات</th>
                        <th>المعدل</th>
                        <th>القرار</th>
                    @endforeach
                    <th>الساعات</th>
                    <th>راسب</th>
                    <th>المعدل</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="{{ $row['passed'] ? 'pass' : ($row['pending'] ? 'pending' : 'fail') }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row['student']->university_no }}</td>
                        <td class="name">{{ $row['student']->name_ar }}</td>
                        @foreach ($levels as $level)
                            @php($semester = $row['semesters'][$level->code] ?? null)
                            <td>{{ $semester ? rtrim(rtrim(number_format($semester['crch'], 1), '0'), '.') : '—' }}</td>
                            <td>{{ $semester ? number_format($semester['gpa'], 2) : '—' }}</td>
                            <td>{{ $semester['recom_name'] ?? '—' }}</td>
                        @endforeach
                        <td>{{ rtrim(rtrim(number_format($row['year']['crch'], 1), '0'), '.') }}</td>
                        <td>{{ rtrim(rtrim(number_format($row['year']['crch_fail'], 1), '0'), '.') }}</td>
                        <td><strong>{{ number_format($row['year']['gpa'], 2) }}</strong></td>
                        <td class="decision">{{ $row['recom_name'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ 7 + $levels->count() * 3 }}">لا توجد نتائج لهذه السنة.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if (count($summary['byDecision']))
            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                @foreach ($summary['byDecision'] as $decision)
                    <span class="rounded border border-gray-300 bg-gray-50 px-3 py-1">{{ $decision['name'] }}: <strong>{{ $decision['count'] }}</strong></span>
                @endforeach
            </div>
        @endif

        <p class="mt-3 text-[11px] text-gray-500">
            المعدل السنوي = مجموع (النقاط × الساعات المعتمدة) ÷ مجموع الساعات للفصلين، على آخر درجة لكل مقرر (الملحق يغلب الأساسي).
            القرار النهائي: توصية الملاحق في نهاية السنة إن وُجدت، وإلا نجاح عند عدم وجود رسوب أو غياب، وإلا قرار الفصل (بانتظار الملاحق).
        </p>

        @if (filled($footerTitle) || count($signatories))
            <div class="mt-8">
                @if (filled($footerTitle))
                    <div class="mb-2 text-center text-[11px] font-bold text-slate-700">{{ $footerTitle }}</div>
                @endif
                @if (count($signatories))
                    <div class="grid gap-8 pt-2" style="grid-template-columns: repeat({{ count($signatories) }}, minmax(0, 1fr))">
                        @foreach ($signatories as $person)
                            <div class="text-center">
                                <div class="text-[10px] font-bold text-slate-500">{{ $person['title'] }}</div>
                                <div class="mt-1 text-[12px] font-extrabold text-slate-800">{{ $person['name'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <div class="mt-4 text-[10px] text-gray-400">طُبع بواسطة {{ $printedBy }} · {{ now()->format('Y-m-d H:i') }}</div>
    </div>
</body>
</html>
