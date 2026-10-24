@php
    $f = $filters;
    $standalone = $standalone ?? false;
    $tone = fn (float $rate): string => $rate >= 80 ? '#0d9488' : ($rate >= 60 ? '#3b4fd4' : ($rate >= 50 ? '#d97706' : '#dc2626'));
    $bar = fn (float $pct, string $color): string => '<span class="bar"><i style="width:'.min(100, max(0, $pct)).'%;background:'.$color.'"></i></span>';
    $money = fn (float $v): string => number_format($v, 0);
    $scope = collect([
        $f['faculty'] ? ($options['faculties'][$f['faculty']] ?? null) : null,
        $f['department'] ? ($options['departments'][$f['department']] ?? null) : null,
        $f['level'] ? ($options['levels'][$f['level']] ?? null) : null,
    ])->filter()->implode(' · ') ?: 'كل نطاق صلاحياتك';
    $resultBatchName = $f['result_batch'] ? ($options['batches'][$f['result_batch']] ?? $f['result_batch']) : '—';
    $batchName = $f['batch'] ? ($options['batches'][$f['batch']] ?? $f['batch']) : 'كل الطلاب · نتائج '.$resultBatchName;
    $main = $finance['totals'][0] ?? null;
    $maxGpaBucket = max(1, collect($gpaBuckets)->max('n'));
    $maxMarkBucket = max(1, collect($markBuckets)->max('n'));
    $maxOf = fn (array $rows): int => max(1, collect($rows)->max('n') ?? 1);
    $stageTotal = max(1, collect($stages['stages'])->sum('n'));
    $palette = ['#0d9488', '#3b4fd4', '#d97706', '#dc2626', '#7c3aed', '#0891b2', '#65a30d', '#be185d'];
    $compareRows = $byFaculty && count($byFaculty) > 1 ? $byFaculty : $byDepartment;
    $compareTitle = $byFaculty && count($byFaculty) > 1 ? 'مقارنة الكليات' : 'مقارنة الأقسام';
    $profileCards = [
        'stady' => ['نوع الدراسة', '#0d9488'],
        'admission' => ['القبول', '#3b4fd4'],
        'gender' => ['النوع', '#be185d'],
        'admission_year' => ['سنة القبول', '#d97706'],
        'xstatus' => ['حالة التسجيل', '#7c3aed'],
    ];
    $maxLevel = $maxOf($students['byLevel']);
    $maxFaculty = $maxOf($students['byFaculty']);
    $maxDepartment = $maxOf($students['byDepartment']);
@endphp
@include('analytics._styles')
<div class="an-dash" dir="rtl">

    <header class="top">
        <div class="brand">
            <b>لوحة المؤشرات الأكاديمية والمالية</b>
            <small>{{ $batchName }} · {{ $scope }} · {{ $f['exam'] === 2 ? 'الملحق' : 'الأساسي' }}</small>
        </div>
        <div class="actions">
            @if ($standalone)
                <a class="link" href="{{ url('/admin') }}">← الرجوع للنظام</a>
            @else
                <a class="link" href="{{ route('analytics.dashboard') }}" target="_blank" rel="noopener">⛶ ملء الشاشة</a>
            @endif
        </div>
    </header>

    <details class="filters">
        <summary>⚙ تصفية البيانات</summary>
        <form method="get" action="{{ url()->current() }}">
            <label>العام الدراسي
                <select name="batch">
                    <option value="">الأحدث (كل الطلاب)</option>
                    @foreach ($options['batches'] as $code => $name)
                        <option value="{{ $code }}" @selected($f['batch'] == $code)>{{ $name }}</option>
                    @endforeach
                </select>
            </label>
            <label>الكلية
                <select name="faculty">
                    <option value="">الكل</option>
                    @foreach ($options['faculties'] as $code => $name)
                        <option value="{{ $code }}" @selected($f['faculty'] == $code)>{{ $name }}</option>
                    @endforeach
                </select>
            </label>
            <label>القسم
                <select name="department">
                    <option value="">الكل</option>
                    @foreach ($options['departments'] as $code => $name)
                        <option value="{{ $code }}" @selected($f['department'] == $code)>{{ $name }}</option>
                    @endforeach
                </select>
            </label>
            <label>الفصل
                <select name="level">
                    <option value="">الكل</option>
                    @foreach ($options['levels'] as $code => $name)
                        <option value="{{ $code }}" @selected($f['level'] == $code)>{{ $name }}</option>
                    @endforeach
                </select>
            </label>
            <label>الدور
                <select name="exam">
                    <option value="1" @selected($f['exam'] === 1)>الأساسي</option>
                    <option value="2" @selected($f['exam'] === 2)>الملحق</option>
                </select>
            </label>
            <button class="btn" type="submit">تطبيق</button>
            <a class="ghost" href="{{ url()->current() }}">إعادة ضبط</a>
        </form>
    </details>

    <section class="kpis">
        <div class="kpi" style="--c:var(--blue)">
            <div class="l">الطلاب</div>
            <div class="v num">{{ number_format($students['total']) }}</div>
            <div class="s">{{ number_format($kpi['evaluated']) }} لهم نتيجة مكتملة ({{ $resultBatchName }})</div>
        </div>
        <div class="kpi" style="--c:var(--teal)">
            <div class="l">متوسط المعدل</div>
            <div class="v num">{{ number_format($kpi['gpa'], 2) }}<small> / 4</small></div>
            <div class="s">أعلى معدل <span class="num">{{ number_format($kpi['top_gpa'], 2) }}</span></div>
        </div>
        <div class="kpi has-ring" style="--c:{{ $tone($kpi['pass_rate']) }}; --p:{{ $kpi['pass_rate'] }}">
            <span class="ring"></span>
            <div class="l">نسبة النجاح</div>
            <div class="v num">{{ $kpi['pass_rate'] }}<small>%</small></div>
            <div class="s">قرار «نجاح» من المكتملين</div>
        </div>
        @if ($showFinance && $main)
            <div class="kpi has-ring" style="--c:{{ $tone($main['rate']) }}; --p:{{ $main['rate'] }}">
                <span class="ring"></span>
                <div class="l">نسبة التحصيل</div>
                <div class="v num">{{ $main['rate'] }}<small>%</small></div>
                <div class="s">المتحصل <span class="num">{{ $money($main['paid']) }}</span> {{ $main['currency'] }}</div>
            </div>
            <div class="kpi has-ring" style="--c:var(--violet); --p:{{ $installments['rate'] }}">
                <span class="ring"></span>
                <div class="l">نسبة التقسيط</div>
                <div class="v num">{{ $installments['rate'] }}<small>%</small></div>
                <div class="s">من الطلاب يدفعون بالأقساط</div>
            </div>
            <div class="kpi" style="--c:var(--amber)">
                <div class="l">المتبقي ({{ $main['currency'] }})</div>
                <div class="v num">{{ $money($main['due']) }}</div>
                <div class="s">{{ number_format($main['debtors']) }} طالب عليهم رسوم</div>
            </div>
        @else
            <div class="kpi" style="--c:var(--violet)">
                <div class="l">المواد</div>
                <div class="v num">{{ number_format($stages['subjects']) }}</div>
                <div class="s">ضمن النطاق</div>
            </div>
            <div class="kpi" style="--c:var(--amber)">
                <div class="l">معتمدة نهائياً</div>
                <div class="v num">{{ number_format(collect($stages['stages'])->firstWhere('value', 'approved')['n'] ?? 0) }}</div>
                <div class="s">من مراحل الاعتماد</div>
            </div>
            <div class="kpi" style="--c:var(--teal)">
                <div class="l">الأوائل</div>
                <div class="v num">{{ count($top) }}</div>
                <div class="s">أعلى المعدلات</div>
            </div>
        @endif
    </section>

    <main class="board">

        <article class="card s4">
            <h3>توزيع التقديرات <span class="pill">درجات المواد</span></h3>
            @forelse ($grades as $i => $g)
                <div class="row">
                    <span class="nm num">{{ $g['grade'] }}</span>
                    {!! $bar($g['percent'], $palette[$i % count($palette)]) !!}
                    <span class="pc num">{{ $g['percent'] }}%</span>
                </div>
            @empty
                <div class="empty">لا توجد تقديرات للنطاق المختار</div>
            @endforelse
        </article>

        <article class="card s4">
            <h3>{{ $compareTitle }} <span class="pill">نسبة النجاح</span></h3>
            @forelse (array_slice($compareRows, 0, 10) as $r)
                <div class="row" title="المعدل {{ $r['gpa'] }} · {{ $r['n'] }} طالب">
                    <span class="nm">{{ $r['name'] }}</span>
                    {!! $bar($r['rate'], $tone($r['rate'])) !!}
                    <span class="pc num">{{ $r['rate'] }}%</span>
                </div>
            @empty
                <div class="empty">لا توجد نتائج للمقارنة</div>
            @endforelse
        </article>

        <article class="card s4">
            <h3>القرارات الأكاديمية <span class="pill">حسب اللائحة</span></h3>
            @forelse (array_slice($recomMix, 0, 8) as $i => $r)
                <div class="row">
                    <span class="nm">{{ $r['name'] }}</span>
                    {!! $bar($r['percent'], $palette[$i % count($palette)]) !!}
                    <span class="pc num">{{ $r['n'] }}</span>
                </div>
            @empty
                <div class="empty">لا توجد قرارات</div>
            @endforelse
        </article>

        <article class="card s4">
            <h3>توزيع المعدلات <span class="pill">الطلاب</span></h3>
            <div class="hist">
                @foreach ($gpaBuckets as $i => $b)
                    <div>
                        <b class="num">{{ $b['n'] }}</b>
                        <i style="height:{{ max(2, $b['n'] / $maxGpaBucket * 100) }}%;background:{{ ['#dc2626', '#e11d48', '#d97706', '#65a30d', '#0d9488', '#3b4fd4'][$i] }}"></i>
                        <span>{{ $b['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="card s4">
            <h3>توزيع الدرجات <span class="pill">0 – 100</span></h3>
            <div class="hist">
                @foreach ($markBuckets as $i => $b)
                    <div>
                        <b class="num">{{ $b['n'] }}</b>
                        <i style="height:{{ max(2, $b['n'] / $maxMarkBucket * 100) }}%;background:{{ $i < 5 ? '#e11d48' : ($i < 7 ? '#d97706' : '#0d9488') }}"></i>
                        <span class="num">{{ $b['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="card s4">
            <h3>الاتجاه عبر الأعوام <span class="pill">نسبة النجاح</span></h3>
            @if (count($trend))
                <div class="hist">
                    @foreach ($trend as $t)
                        <div title="المعدل {{ $t['gpa'] }} · {{ $t['n'] }} نتيجة">
                            <b class="num">{{ $t['rate'] }}%</b>
                            <i style="height:{{ max(2, $t['rate']) }}%;background:{{ $tone($t['rate']) }}"></i>
                            <span>{{ $t['batch'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty">لا توجد بيانات أعوام</div>
            @endif
        </article>

        <article class="card s6">
            <h3>المواد الأضعف نجاحاً <span class="pill">أول 10</span></h3>
            @if (count($subjects))
                <div class="scroll">
                    <table>
                        <thead><tr><th>المادة</th><th class="n">الدرجات</th><th class="n">المتوسط</th><th style="width:34%">نسبة النجاح</th></tr></thead>
                        <tbody>
                        @foreach ($subjects as $s)
                            <tr>
                                <td>{{ $s['name'] }}</td>
                                <td class="n num">{{ $s['n'] }}</td>
                                <td class="n num">{{ $s['avg'] }}</td>
                                <td>
                                    <div class="row" style="grid-template-columns:1fr 46px;padding:0">
                                        {!! $bar($s['rate'], $tone($s['rate'])) !!}
                                        <span class="pc num">{{ $s['rate'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty">لا توجد درجات للنطاق المختار</div>
            @endif
        </article>

        @if ($showFinance)
            <article class="card s6">
                <h3>التحصيل والمتبقي <span class="pill">الرسوم · المتحصل · المتبقي</span></h3>
                @forelse ($finance['totals'] as $t)
                    <div class="collect">
                        <div class="big" style="--c:{{ $tone($t['rate']) }};--p:{{ $t['rate'] }}"><b class="num">{{ $t['rate'] }}%</b></div>
                        <div class="money">
                            <div><small>جملة الرسوم ({{ $t['currency'] }})</small><b class="num">{{ $money($t['fees']) }}</b></div>
                            <div><small>المتحصل</small><b class="num" style="color:var(--teal)">{{ $money($t['paid']) }}</b></div>
                            <div><small>المتبقي</small><b class="num" style="color:var(--red)">{{ $money($t['due']) }}</b></div>
                        </div>
                    </div>
                @empty
                    <div class="empty">لا توجد بيانات مالية</div>
                @endforelse
                @foreach (array_slice($finance['byFaculty'], 0, 6) as $r)
                    <div class="row" title="المتبقي {{ $money($r['due']) }}">
                        <span class="nm">{{ $r['name'] }}</span>
                        {!! $bar($r['rate'], $tone($r['rate'])) !!}
                        <span class="pc num">{{ $r['rate'] }}%</span>
                    </div>
                @endforeach
            </article>

            @foreach ($fees as $currency => $table)
                <article class="card s12">
                    <h3>جملة الرسوم مبوّبة حسب الكلية <span class="pill">{{ $currency }}</span></h3>
                    <div class="scroll">
                        <table>
                            <thead>
                            <tr>
                                <th>الكلية</th>
                                <th class="n">الطلاب</th>
                                @foreach ($table['columns'] as $label)<th class="n">{{ $label }}</th>@endforeach
                                <th class="n">الجملة</th>
                                <th class="n">المتحصل</th>
                                <th class="n">المتبقي</th>
                                <th style="width:150px">التحصيل</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($table['rows'] as $r)
                                <tr>
                                    <td>{{ $r['name'] }}</td>
                                    <td class="n num">{{ number_format($r['students']) }}</td>
                                    @foreach ($table['columns'] as $code => $label)<td class="n num">{{ $money($r[$code]) }}</td>@endforeach
                                    <td class="n num"><b>{{ $money($r['all_fees']) }}</b></td>
                                    <td class="n num" style="color:var(--teal)">{{ $money($r['pay']) }}</td>
                                    <td class="n num" style="color:var(--red)">{{ $money($r['xmod']) }}</td>
                                    <td>
                                        <div class="row" style="grid-template-columns:1fr 46px;padding:0">
                                            {!! $bar($r['rate'], $tone($r['rate'])) !!}
                                            <span class="pc num">{{ $r['rate'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="total">
                                <td>الإجمالي</td>
                                <td class="n num">{{ number_format($table['total']['students']) }}</td>
                                @foreach ($table['columns'] as $code => $label)<td class="n num">{{ $money($table['total'][$code]) }}</td>@endforeach
                                <td class="n num">{{ $money($table['total']['all_fees']) }}</td>
                                <td class="n num">{{ $money($table['total']['pay']) }}</td>
                                <td class="n num">{{ $money($table['total']['xmod']) }}</td>
                                <td class="num">{{ $table['total']['all_fees'] > 0 ? round($table['total']['pay'] / $table['total']['all_fees'] * 100, 1) : 0 }}%</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </article>
            @endforeach

            <article class="card s4">
                <h3>نظام الأقساط <span class="pill">نسبة التقسيط {{ $installments['rate'] }}%</span></h3>
                @forelse ($installments['rows'] as $i => $r)
                    <div class="row">
                        <span class="nm">{{ $r['name'] }}</span>
                        {!! $bar($r['percent'], $palette[$i % count($palette)]) !!}
                        <span class="pc num">{{ number_format($r['n']) }}</span>
                    </div>
                @empty
                    <div class="empty">لا توجد بيانات</div>
                @endforelse
            </article>
        @endif

        @foreach ($profileCards as $key => $spec)
            <article class="card s4">
                <h3>{{ $spec[0] }} <span class="pill">العدد</span></h3>
                @forelse ($profile[$key] ?? [] as $r)
                    <div class="row">
                        <span class="nm">{{ $r['name'] }}</span>
                        {!! $bar($r['percent'], $spec[1]) !!}
                        <span class="pc num">{{ number_format($r['n']) }}</span>
                    </div>
                @empty
                    <div class="empty">لا توجد بيانات</div>
                @endforelse
            </article>
        @endforeach

        <article class="card s4">
            <h3>الأوائل <span class="pill">أعلى المعدلات</span></h3>
            @forelse ($top as $i => $p)
                <div class="person">
                    <span style="display:flex;align-items:center;min-width:0"><span class="rank num">{{ $i + 1 }}</span>
                        <span style="min-width:0"><b>{{ $showNames ? $p['name'] : 'طالب '.($i + 1) }}</b><small>{{ $p['meta'] }}</small></span></span>
                    <b class="num" style="color:var(--teal);font-size:16px">{{ number_format($p['gpa'], 2) }}</b>
                </div>
            @empty
                <div class="empty">لا توجد نتائج</div>
            @endforelse
        </article>

        <article class="card s4">
            <h3>مراحل اعتماد المواد <span class="pill num">{{ $stages['subjects'] }} مادة</span></h3>
            @foreach ($stages['stages'] as $i => $s)
                <div class="row">
                    <span class="nm">{{ $s['label'] }}</span>
                    {!! $bar($s['n'] / $stageTotal * 100, $palette[$i % count($palette)]) !!}
                    <span class="pc num">{{ $s['n'] }}</span>
                </div>
            @endforeach
        </article>

        <article class="card s4">
            <h3>الطلاب حسب الفصل <span class="pill">العدد</span></h3>
            @forelse ($students['byLevel'] as $r)
                <div class="row"><span class="nm">{{ $r['name'] }}</span>{!! $bar($r['n'] / $maxLevel * 100, '#3b4fd4') !!}<span class="pc num">{{ number_format($r['n']) }}</span></div>
            @empty
                <div class="empty">—</div>
            @endforelse
        </article>

        <article class="card s6">
            <h3>الطلاب حسب الكلية <span class="pill">العدد</span></h3>
            @forelse ($students['byFaculty'] as $r)
                <div class="row"><span class="nm">{{ $r['name'] }}</span>{!! $bar($r['n'] / $maxFaculty * 100, '#0d9488') !!}<span class="pc num">{{ number_format($r['n']) }}</span></div>
            @empty
                <div class="empty">—</div>
            @endforelse
        </article>

        <article class="card s6">
            <h3>الطلاب حسب القسم <span class="pill">العدد</span></h3>
            @forelse ($students['byDepartment'] as $r)
                <div class="row"><span class="nm">{{ $r['name'] }}</span>{!! $bar($r['n'] / $maxDepartment * 100, '#7c3aed') !!}<span class="pc num">{{ number_format($r['n']) }}</span></div>
            @empty
                <div class="empty">—</div>
            @endforelse
        </article>

    </main>

    <p class="foot">{{ $userName }} — {{ now()->format('Y-m-d H:i') }} — البيانات مقيّدة بنطاق صلاحياتك وتُحدَّث كل دقيقتين.</p>
</div>
