@php
    $continuous = $design === 'continuous';
    $sheetH = 193;
    $line101 = $headerTitle ?? null;
    $footerTitle = $footerTitle ?? null;
    $headH = ($continuous ? 16 : 26) + (filled($line101) ? ($continuous ? 3 : 5) : 0);
    $signatories = array_values(array_filter($signatories, fn ($s) => $continuous || $s['title'] !== 'رئيس القسم'));
    $sigH = count($signatories) ? ($continuous ? 16 : 17) : 0;
    $theadH = 36;
    $bodyH = $sheetH - $headH - $sigH - $theadH - 12 - (filled($footerTitle) ? 8 : 0);
    $perPage = min($perPage, (int) floor($bodyH / 4.6));
    $rowH = round($bodyH / $perPage, 2);

    $landscape = true;
    $printable = ['w' => 281, 'h' => 194];
    $pageTitle = 'نتيجة الطلاب - '.($facultyInfo->name_ar ?? '');

    $has = fn (string $field) => in_array($field, $fields, true);
    $colCount = count($subjects)
        + collect(['ids', 'indexno', 'university_no', 'name_ar', 'name_en', 'sumMarks', 'crch', 'crch_fail', 'point', 'subjectFail', 'gpa', 'recom'])->filter($has)->count()
        + ($showHistory ? 3 : 0);

    $pages = $students->chunk($perPage);
    $hasSummary = $continuous && $subjectStats;
    $totalPages = max($pages->count(), 1) + ($hasSummary ? 1 : 0);

    $line1 = __('main.univ_name', [], 'ar').' - '.($facultyInfo->name_ar ?? '');
    $line2 = 'نتيجة الامتحانات '.$absentName;
    $chips = array_values(array_filter([
        $levelInfo->name_ar ?? null,
        $departmentInfo ? 'القسم: '.$departmentInfo->name_ar : null,
        ($specialInfo ?? null) ? 'التخصص: '.$specialInfo->name_ar : null,
        'العام الدراسي '.$academicYear,
        $isPrevious ? 'نتيجة سابقة' : null,
    ]));

    $cards = [
        ['label' => 'إجمالي الطلاب', 'value' => $stats['registered'], 'tone' => 'brand'],
        ['label' => 'الطلاب الجالسون', 'value' => $stats['sitting'], 'tone' => 'sky'],
        ['label' => 'الناجحون', 'value' => $stats['passed'], 'tone' => 'emerald'],
        ['label' => 'الراسبون', 'value' => $stats['failed'], 'tone' => 'rose'],
        ['label' => 'أعلى معدل', 'value' => ($highestGpa ?? null) !== null ? number_format($highestGpa, 2) : '—', 'tone' => 'sky'],
    ];

    $sheetClass = 'sheet mx-auto mb-5 flex flex-col overflow-hidden bg-white px-[7mm] py-[5mm] shadow-lg ring-1 ring-slate-200 h-[calc(var(--sheet-h)+10mm)] print:h-[var(--sheet-h)] break-after-page last:break-after-auto print:m-0 print:p-0 print:shadow-none print:ring-0';
@endphp
@extends('Res.sheets._base')

@section('toolbar')
    @include('Res.sheets._control_panel')

    <div class="inline-flex items-center gap-1 rounded-lg bg-slate-100 p-1" title="النتيجة السابقة تُجلب من سجل الدرجات والتوصيات حتى لو نُقل الطالب">
        @foreach (['current' => 'نتيجة حالية', 'previous' => 'نتيجة سابقة'] as $type => $label)
            <a href="{{ request()->fullUrlWithQuery(['result_type' => $type]) }}"
               class="rounded-md px-3 py-1 transition {{ ($isPrevious ? 'previous' : 'current') === $type ? 'bg-white font-bold text-brand-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">{{ $label }}</a>
        @endforeach
    </div>

    <a href="{{ request()->fullUrlWithQuery(['show_history' => $showHistory ? 0 : 1]) }}"
       class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 font-bold transition {{ $showHistory ? 'border-amber-300 bg-amber-100 text-amber-900' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}">
        <span class="h-2 w-2 rounded-full {{ $showHistory ? 'bg-amber-500' : 'bg-slate-300' }}"></span>
        النتائج السابقة للطلاب
    </a>
@endsection

@section('content')
    @include('Res.sheets._stats')

    @forelse ($pages as $index => $pageStudents)
        <section data-sheet class="{{ $sheetClass }}" style="width: {{ $printable['w'] }}mm; --sheet-h: {{ $sheetH }}mm">
            @include('Res.sheets._header', ['logos' => $logos])

            @include('Res.sheets._result_table', ['pageStudents' => $pageStudents, 'offset' => $index * $perPage])

            @include('Res.sheets._signatures')

            @include('Res.sheets._footer', [
                'footLeft' => 'صفحة '.($index + 1).' من '.$totalPages,
                'footRight' => 'طُبع في '.now()->format('Y-m-d H:i'),
            ])
        </section>
    @empty
        <section data-sheet class="{{ $sheetClass }} items-center justify-center" style="width: {{ $printable['w'] }}mm; --sheet-h: {{ $sheetH }}mm">
            <p class="text-sm font-bold text-slate-500">لا توجد بيانات طلاب لعرضها.</p>
        </section>
    @endforelse

    @if ($hasSummary)
        @php
            $grades = collect($subjectStats)->flatMap(fn ($row) => array_keys($row))->unique()->values();
            $failGrades = \App\Services\Res\ResultSheetService::FAIL_GRADES;
            $peak = max(1, collect($subjectStats)->flatten()->max());
        @endphp
        <section data-sheet data-summary class="{{ $sheetClass }}" style="width: {{ $printable['w'] }}mm; --sheet-h: {{ $sheetH }}mm">
            @include('Res.sheets._header', ['logos' => $logos])

            <div class="mb-2 text-sm font-extrabold text-black">ملخص التقديرات حسب المادة</div>

            <table class="w-full border-collapse text-[11px]">
                <thead>
                <tr class="bg-gray-200 text-black">
                    <th class="border border-gray-300 px-2 py-1 text-right font-bold">المادة \ التقدير</th>
                    @foreach ($grades as $grade)<th class="border border-gray-300 px-1 font-bold" dir="ltr">{{ $grade }}</th>@endforeach
                    <th class="border border-gray-300 px-1 font-bold">المجموع</th>
                    <th class="border border-gray-300 px-2 font-bold" style="width: 44mm">نسبة النجاح</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($subjectStats as $code => $row)
                    @php
                        $total = array_sum($row);
                        $failed = collect($row)->only($failGrades)->sum();
                        $rate = $total ? round(($total - $failed) / $total * 100) : 0;
                    @endphp
                    <tr class="even:bg-slate-50" style="height: 7mm">
                        <td class="border-b border-slate-200 px-2 text-right font-bold">{{ $subjectNames[$code]->name_ar ?? $code }}</td>
                        @foreach ($grades as $grade)
                            @php $n = $row[$grade] ?? 0; @endphp
                            <td class="border-b border-slate-200 text-center tabular-nums"
                                style="background: rgba(15, 42, 92, {{ round(0.55 * $n / $peak, 2) }}); color: {{ $n / $peak > 0.5 ? '#fff' : '#0f172a' }}">{{ $n }}</td>
                        @endforeach
                        <td class="border-b border-slate-200 text-center font-extrabold tabular-nums">{{ $total }}</td>
                        <td class="border-b border-slate-200 px-2">
                            <div class="flex items-center gap-2">
                                <div class="h-1.5 flex-1 rounded-full bg-slate-200"><div class="h-1.5 rounded-full {{ $rate >= 70 ? 'bg-emerald-500' : ($rate >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $rate }}%"></div></div>
                                <span class="w-8 text-end font-bold tabular-nums">{{ $rate }}%</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            @include('Res.sheets._signatures')

            @include('Res.sheets._footer', [
                'footLeft' => 'صفحة '.$totalPages.' من '.$totalPages.' — ملخص التقديرات',
                'footRight' => 'طُبع في '.now()->format('Y-m-d H:i'),
            ])
        </section>
    @endif

    @include('Res.sheets._extra_stats')

    @include('Res.sheets._display_script')
@endsection
