@php
    $continuous = $design === 'continuous';
    $sheetH = 193;
    $line101 = null;
    $footerTitle = null;
    $headH = $continuous ? 16 : 26;
    $sigH = count($signatories) ? ($continuous ? 16 : 17) : 0;
    $theadH = 10;
    $bodyH = $sheetH - $headH - $sigH - $theadH - 18;
    $perPage = min($perPage, (int) floor($bodyH / 4.6));
    $rowH = round($bodyH / $perPage, 2);

    $landscape = true;
    $printable = ['w' => 281, 'h' => 194];
    $pageTitle = 'كشف درجات | '.$subject->name_ar;

    $colCount = $showCwork ? 8 : 6;
    $pages = $rows->chunk($perPage);
    $totalPages = max($pages->count(), 1);
    $failGrades = \App\Services\Res\ResultSheetService::FAIL_GRADES;

    $line1 = __('main.univ_name', [], 'ar').' - '.($subject->faculty?->name_ar ?? '');
    $line2 = 'كشف درجات مادة: '.$subject->name_ar.' ('.$subject->code.') — '.$examType;
    $chips = array_values(array_filter([
        $subject->level?->name_ar,
        $subject->department ? 'القسم: '.$subject->department->name_ar : null,
        $subject->special ? 'التخصص: '.$subject->special->name_ar : null,
        'العام الدراسي '.$batchName,
        'الساعات '.$subject->crch,
        'المرحلة: '.$stage,
    ]));

    $cards = [
        ['label' => 'عدد الطلاب', 'value' => $rows->count(), 'tone' => 'brand'],
        ['label' => 'المُدخَلة درجاتهم', 'value' => $entered, 'tone' => 'emerald'],
        ['label' => 'المتبقي', 'value' => $rows->count() - $entered, 'tone' => 'rose'],
        ['label' => 'المرحلة', 'value' => $stage, 'tone' => 'sky'],
    ];

    $th = 'border border-gray-300 bg-gray-200 px-1 text-[11px] font-bold text-black';
    $td = 'border border-gray-300 bg-white px-1 text-center text-[11px] text-black';

    $sheetClass = 'sheet mx-auto mb-5 flex flex-col overflow-hidden bg-white px-[7mm] py-[5mm] shadow-lg ring-1 ring-slate-200 h-[calc(var(--sheet-h)+10mm)] print:h-[var(--sheet-h)] break-after-page last:break-after-auto print:m-0 print:p-0 print:shadow-none print:ring-0';
@endphp
@extends('Res.sheets._base')

@section('content')
    @include('Res.sheets._stats')

    @forelse ($pages as $index => $pageRows)
        <section data-sheet class="{{ $sheetClass }}" style="width: {{ $printable['w'] }}mm; --sheet-h: {{ $sheetH }}mm">
            @include('Res.sheets._header')

            <table class="w-full border-collapse border border-gray-300">
                <thead>
                <tr style="height: {{ $theadH }}mm">
                    <th class="{{ $th }}" style="width: 9mm">م</th>
                    <th class="{{ $th }}" style="width: 30mm">الرقم الجامعي</th>
                    <th class="{{ $th }}">اسم الطالب</th>
                    <th class="{{ $th }}" style="width: 24mm">الامتحان</th>
                    @if ($showCwork)
                        <th class="{{ $th }}" style="width: 24mm">أعمال السنة</th>
                        <th class="{{ $th }}" style="width: 24mm">المجموع</th>
                    @endif
                    <th class="{{ $th }}" style="width: 24mm">التقدير</th>
                    <th class="{{ $th }}" style="width: 24mm">النقاط</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($pageRows as $row)
                    @php $isFail = in_array($row['grade'], $failGrades, true); @endphp
                    <tr style="height: {{ $rowH }}mm">
                        <td class="{{ $td }} font-bold">{{ $index * $perPage + $loop->iteration }}</td>
                        <td class="{{ $td }} tabular-nums">{{ $row['university_no'] }}</td>
                        <td class="{{ $td }} whitespace-nowrap !text-right font-bold">{{ $row['name'] }}</td>
                        <td class="{{ $td }} tabular-nums">{{ $row['mark'] ?? '—' }}</td>
                        @if ($showCwork)
                            <td class="{{ $td }} tabular-nums">{{ $row['cwork'] ?? '—' }}</td>
                            <td class="{{ $td }} font-bold tabular-nums">{{ $row['total'] ?? '—' }}</td>
                        @endif
                        <td class="{{ $td }} {{ $isFail ? '!bg-gray-300' : '' }}" @if ($isFail) data-fail @endif>
                            @if ($row['grade'] !== null)
                                <span dir="ltr" class="inline-block px-1.5 text-[10.5px] font-bold">{{ $row['grade'] }}</span>
                            @else
                                <span>—</span>
                            @endif
                        </td>
                        <td class="{{ $td }} tabular-nums">{{ $row['point'] !== null ? number_format((float) $row['point'], 2) : '—' }}</td>
                    </tr>
                @endforeach

                @for ($i = $pageRows->count(); $i < $perPage; $i++)
                    <tr style="height: {{ $rowH }}mm">
                        @for ($c = 0; $c < $colCount; $c++)<td class="{{ $td }}">&nbsp;</td>@endfor
                    </tr>
                @endfor
                </tbody>
            </table>

            @include('Res.sheets._signatures')

            @include('Res.sheets._footer', [
                'footLeft' => 'صفحة '.($index + 1).' من '.$totalPages.' — عدد الطلاب '.$rows->count(),
                'footRight' => 'طُبع بواسطة '.$printedBy.' — '.now()->format('Y-m-d H:i'),
            ])
        </section>
    @empty
        <section data-sheet class="{{ $sheetClass }} items-center justify-center" style="width: {{ $printable['w'] }}mm; --sheet-h: {{ $sheetH }}mm">
            <p class="text-sm font-bold text-slate-500">لا يوجد طلاب.</p>
        </section>
    @endforelse
@endsection
