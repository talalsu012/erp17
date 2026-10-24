{{-- دليل الألوان وإحصائيات القرارات والمواد بعد صفحات الكشف. المتغيرات: $recomStats، $subjectPassStats، $printable --}}
@php
    $recomStats = $recomStats ?? [];
    $subjectPassStats = $subjectPassStats ?? [];
    $box = 'mx-auto mb-5 overflow-hidden rounded-lg bg-white shadow-lg print:mb-0 print:shadow-none print:break-before-page';
    $titleBar = 'border-b border-sky-200 bg-sky-100 p-4 text-center text-sky-950';
    $th = 'border border-gray-300 bg-gray-200 p-2 text-[11px] font-bold text-black';
    $td = 'border border-gray-300 bg-white p-2 text-center text-[11px] text-black';
    $width = 'width: '.$printable['w'].'mm';
@endphp

<section data-legend class="{{ $box }} !break-before-auto" style="{{ $width }}">
    <div class="flex flex-wrap justify-center gap-6 bg-gray-100 p-3 text-[11px]">
        <div class="flex items-center"><span class="ml-2 inline-block h-4 w-4 rounded border border-gray-300 bg-white"></span>ناجح</div>
        <div class="flex items-center"><span class="ml-2 inline-block h-4 w-4 rounded border border-gray-300 bg-gray-300"></span>راسب / غير مكتمل</div>
    </div>
</section>

@if (count($recomStats))
    <section data-recom-stats id="recommendationsStatsSection" class="{{ $box }}" style="{{ $width }}">
        <div class="{{ $titleBar }}"><h3 class="text-xl font-bold">إحصائيات التوصيات</h3></div>

        <div class="overflow-x-auto p-4">
            <table class="w-full border-collapse">
                <thead>
                <tr>
                    <th class="{{ $th }}">كود التوصية</th>
                    <th class="{{ $th }}">اسم التوصية</th>
                    <th class="{{ $th }}">عدد الطلاب</th>
                    <th class="{{ $th }}">النسبة المئوية</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($recomStats as $row)
                    <tr>
                        <td class="{{ $td }} font-mono">{{ $row['code'] }}</td>
                        <td class="{{ $td }} !text-right">{{ $row['name'] }}</td>
                        <td class="{{ $td }} font-bold">{{ $row['count'] }}</td>
                        <td class="{{ $td }} font-semibold">{{ $row['percent'] }}%</td>
                    </tr>
                @endforeach
                <tr class="font-bold">
                    <td class="{{ $th }}" colspan="2">الإجمالي</td>
                    <td class="{{ $th }} text-center">{{ array_sum(array_column($recomStats, 'count')) }}</td>
                    <td class="{{ $th }} text-center">100%</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="p-4 pt-0">
            <h4 class="mb-2 text-center font-medium">توزيع التوصيات</h4>
            <div class="flex h-8 overflow-hidden rounded bg-gray-200">
                @foreach ($recomStats as $row)
                    <div class="bg-blue-{{ [800, 600, 400, 300, 200][$loop->index % 5] }} transition-opacity hover:opacity-90"
                         style="width: {{ $row['percent'] }}%; background-color: {{ ['#1e40af', '#2563eb', '#60a5fa', '#93c5fd', '#bfdbfe'][$loop->index % 5] }}"
                         title="{{ $row['name'] }}: {{ $row['percent'] }}%"></div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if (count($subjectPassStats))
    <section data-subject-stats id="subjectsStatsSection" class="{{ $box }}" style="{{ $width }}">
        <div class="{{ $titleBar }}"><h3 class="text-xl font-bold">إحصائيات المواد الدراسية</h3></div>

        <div class="overflow-x-auto p-4">
            <table class="w-full border-collapse">
                <thead>
                <tr>
                    @foreach (['المادة', 'عدد الطلاب', 'الناجحون', 'الراسبون', 'بديل', 'أعلى درجة', 'أقل درجة', 'المعدل', 'نسبة النجاح %'] as $heading)
                        <th class="{{ $th }}">{{ $heading }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach ($subjectPassStats as $row)
                    <tr>
                        <td class="{{ $td }} !text-right">{{ $row['name'] }}</td>
                        <td class="{{ $td }}">{{ $row['total'] }}</td>
                        <td class="{{ $td }} font-semibold">{{ $row['passed'] }}</td>
                        <td class="{{ $td }} font-semibold">{{ $row['failed'] }}</td>
                        <td class="{{ $td }} font-semibold">{{ $row['alternate'] }}</td>
                        <td class="{{ $td }} font-semibold">{{ $row['max'] }}</td>
                        <td class="{{ $td }} font-semibold">{{ $row['min'] }}</td>
                        <td class="{{ $td }} font-semibold">{{ $row['avg'] }}</td>
                        <td class="{{ $td }} font-semibold">{{ $row['rate'] }}%</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 pt-0">
            <h4 class="mb-2 text-center font-medium">نسب النجاح حسب المادة</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach ($subjectPassStats as $row)
                    <div>
                        <div class="mb-1 flex justify-between text-sm font-medium">
                            <span>{{ $row['name'] }}</span>
                            <span>{{ $row['rate'] }}%</span>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-gray-200">
                            <div class="h-2.5 rounded-full bg-blue-600" style="width: {{ $row['rate'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
