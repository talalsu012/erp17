{{-- جدول نتيجة صفحة واحدة. المتغيرات: $pageStudents، $offset، $perPage، $colCount + متغيرات الكشف (fields, subjects, subjectNames, counts, history, showHistory) --}}
@php
    $has = fn (string $field) => in_array($field, $fields, true);
    $failGrades = \App\Services\Res\ResultSheetService::FAIL_GRADES;
    $absentFails = \App\Reg\ShowAbsentCode::getMarkValues();
    $continuous = $design === 'continuous';

    $widths = ['ids' => 8, 'indexno' => 18, 'university_no' => 24, 'name_ar' => 50, 'name_en' => 44, 'sumMarks' => 15, 'crch' => 13,
        'crch_fail' => 14, 'point' => 13, 'subjectFail' => 28, 'gpa' => 13, 'recom' => 20];
    $historyWidths = ['year' => 22, 'gpa' => 12, 'recom' => 20];
    $used = collect($widths)->filter(fn ($w, $field) => $has($field))->sum() + ($showHistory ? array_sum($historyWidths) : 0);
    $subjectWidth = count($subjects) ? max(6, min(11, round((($printable['w'] ?? 281) - 16 - $used) / count($subjects), 1))) : 0;

    $th = 'border border-gray-300 bg-gray-200 px-1 text-[11px] font-bold text-black';
    $td = 'border border-gray-300 bg-white px-1 text-center text-[11px] leading-none text-black';
    $grayGrades = array_values(array_unique([...$failGrades, ...$absentFails, 'ت']));

@endphp
<table class="w-full table-fixed border-collapse border border-gray-300">
    <thead>
    <tr>
        @if ($has('ids'))<th class="{{ $th }}" style="width: {{ $widths['ids'] }}mm">م</th>@endif
        @if ($has('indexno'))<th class="{{ $th }}" style="width: {{ $widths['indexno'] }}mm">رقم الجلوس</th>@endif
        @if ($has('university_no'))<th class="{{ $th }}" style="width: {{ $widths['university_no'] }}mm">الرقم الجامعي</th>@endif
        @if ($has('name_ar'))<th class="{{ $th }}" style="width: {{ $widths['name_ar'] }}mm">اسم الطالب</th>@endif
        @if ($has('name_en'))<th class="{{ $th }}" style="width: {{ $widths['name_en'] }}mm">Name (EN)</th>@endif

        @foreach ($subjects as $code)
            <th class="{{ $th }} p-0" style="height: {{ $theadH }}mm; width: {{ $subjectWidth }}mm">
                <div class="vertical-text mx-auto overflow-hidden text-center text-[10px] leading-[1.15]" style="height: {{ $theadH - 2 }}mm">
                    {{ $subjectNames[$code]->name_ar ?? $code }}
                    @if (! empty($subjectNames[$code]->crch ?? null))<span class="opacity-70">({{ $subjectNames[$code]->crch }})</span>@endif
                </div>
            </th>
        @endforeach

        @if ($has('sumMarks'))<th class="{{ $th }}" style="width: {{ $widths['sumMarks'] }}mm">المجموع</th>@endif
        @if ($has('crch'))<th class="{{ $th }}" style="width: {{ $widths['crch'] }}mm">الساعات</th>@endif
        @if ($has('crch_fail'))<th class="{{ $th }}" style="width: {{ $widths['crch_fail'] }}mm">ساعات الرسوب</th>@endif
        @if ($has('point'))<th class="{{ $th }}" style="width: {{ $widths['point'] }}mm">النقاط</th>@endif
        @if ($has('subjectFail'))<th class="{{ $th }}" style="width: {{ $widths['subjectFail'] }}mm">مواد الملحق</th>@endif
        @if ($has('gpa'))<th class="{{ $th }}" style="width: {{ $widths['gpa'] }}mm">المعدل</th>@endif
        @if ($has('recom'))<th class="{{ $th }}" style="width: {{ $widths['recom'] }}mm">القرار</th>@endif

        @if ($showHistory)
            <th class="border border-gray-300 bg-gray-200 px-1 text-[10.5px] font-bold text-black" style="width: {{ $historyWidths['year'] }}mm">العام السابق</th>
            <th class="border border-gray-300 bg-gray-200 px-1 text-[10.5px] font-bold text-black" style="width: {{ $historyWidths['gpa'] }}mm">المعدل السابق</th>
            <th class="border border-gray-300 bg-gray-200 px-1 text-[10.5px] font-bold text-black" style="width: {{ $historyWidths['recom'] }}mm">القرار السابق</th>
        @endif
    </tr>
    </thead>
    <tbody>
    @foreach ($pageStudents as $student)
        @php $row = $student->sheetRecom; @endphp
        <tr style="height: {{ $rowH }}mm">
            @if ($has('ids'))<td class="{{ $td }} font-bold">{{ $offset + $loop->iteration }}</td>@endif
            @if ($has('indexno'))<td class="{{ $td }}">{{ $student->indexno ?? '-' }}</td>@endif
            @if ($has('university_no'))<td class="{{ $td }} tabular-nums">{{ $student->university_no }}</td>@endif

            @if ($has('name_ar'))
                <td class="{{ $td }} overflow-hidden text-ellipsis whitespace-nowrap !text-right font-bold">
                    {{ $student->name_ar }}
                    @if (($counts['external'][$student->university_no] ?? 0) > 0)<span class="ms-1 rounded bg-slate-100 px-1 text-[8.5px] font-bold text-slate-500">Ex{{ $counts['external'][$student->university_no] }}</span>@endif
                    @if (($counts['repeat'][$student->university_no] ?? 0) > 0)<span class="ms-1 rounded bg-slate-100 px-1 text-[8.5px] font-bold text-slate-500">R{{ $counts['repeat'][$student->university_no] }}</span>@endif
                </td>
            @endif

            @if ($has('name_en'))<td class="{{ $td }} whitespace-nowrap !text-left" dir="ltr">{{ $student->name_en ?? '-' }}</td>@endif

            @foreach ($subjects as $code)
                @php
                    $mark = $student->marks->firstWhere('subject_code', $code);
                    $grade = trim((string) ($mark->grade ?? '-'));
                    $isFail = in_array($grade, $grayGrades, true);
                    $shown = preg_replace('/^\+([A-Za-z])$/', '$1+', $grade);
                @endphp
                <td class="{{ $td }} {{ $isFail ? '!bg-gray-300' : '' }}" @if ($isFail) data-fail @endif>
                    <div class="marks-cell flex flex-col items-center justify-center gap-0">
                        <span dir="ltr" class="grade-value px-1 text-[10px] font-bold leading-[1.1]" data-default="{{ $has('grade') ? 1 : 0 }}" @unless ($has('grade')) style="display: none" @endunless>{{ $shown }}</span>
                        <span dir="ltr" class="mark-value tabular-nums leading-[1.1] {{ $has('grade') ? 'text-[8.5px]' : 'text-[11px] font-bold' }}" data-default="{{ $has('mark') ? 1 : 0 }}" @unless ($has('mark')) style="display: none" @endunless>{{ $mark->mark ?? '-' }}</span>
                    </div>
                </td>
            @endforeach

            @if ($has('sumMarks'))<td class="{{ $td }} font-bold tabular-nums">{{ $row->sumMarks ?? '-' }}</td>@endif
            @if ($has('crch'))<td class="{{ $td }} tabular-nums">{{ $row->crch ?? '-' }}</td>@endif
            @if ($has('crch_fail'))<td class="{{ $td }} tabular-nums">{{ $row->crch_fail ?? '-' }}</td>@endif
            @if ($has('point'))<td class="{{ $td }} tabular-nums">{{ $row->points ?? '-' }}</td>@endif
            @if ($has('subjectFail'))
                <td class="{{ $td }} text-[9px]">
                    {{ $student->marks->filter(fn ($m) => in_array($m->grade, $absentFails, true))->map(fn ($m) => $m->subject?->name_ar)->filter()->implode('، ') ?: '-' }}
                </td>
            @endif
            @if ($has('gpa'))<td class="{{ $td }} font-bold tabular-nums">{{ $row?->gpa ?? '-' }}</td>@endif
            @if ($has('recom'))<td class="{{ $td }} font-bold">{{ $row?->recomName?->name_ar ?? $row?->recom_code ?? '-' }}</td>@endif

            @if ($showHistory)
                @php $prev = $history[$student->university_no] ?? null; @endphp
                <td class="{{ $td }} ">{{ $prev ? $prev['batch'].' — '.($prev['level'] ?? '') : '-' }}</td>
                <td class="{{ $td }} font-bold tabular-nums">{{ $prev['gpa'] ?? '-' }}</td>
                <td class="{{ $td }} font-bold">{{ $prev['recom'] ?? '-' }}</td>
            @endif
        </tr>
    @endforeach

    @for ($i = $pageStudents->count(); $i < $perPage; $i++)
        <tr style="height: {{ $rowH }}mm">
            @for ($c = 0; $c < $colCount; $c++)<td class="{{ $td }}">&nbsp;</td>@endfor
        </tr>
    @endfor
    </tbody>
</table>
