<x-filament-panels::page>
    {{ $this->AttendanceForm }}

    <div class="report-container antialiased" dir="rtl">


        <style>
            .academic-table-card {
                background: #ffffff;
                border-radius: 14px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.03);
                overflow: hidden;
            }

            /* تحديث الأعمدة لـ 7 أعمدة لتستوعب التوزيع الجديد */
            .report-header-grid {
                display: grid;
                grid-template-columns: 120px 140px 1.2fr 1fr 100px 100px 120px;
                gap: 16px;
                align-items: center;
                background-color: #f8fafc;
                border-bottom: 1px solid #e2e8f0;
                padding: 12px 16px;
                font-size: 12px;
                font-weight: 700;
                color: #64748b;
            }

            .report-row-grid {
                display: grid;
                grid-template-columns: 120px 140px 1.2fr 1fr 100px 100px 120px;
                gap: 16px;
                align-items: center;
                border-bottom: 1px solid #f1f5f9;
                padding: 14px 16px;
                transition: all 0.2s ease;
            }

            .report-row-grid:hover {
                background-color: #f8fafc;
            }

            /* ستايل مخصص لأعمدة الأوقات المنفصلة */
            .time-cell {
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                font-size: 12px;
                font-weight: 600;
                color: #334155;
            }

            .badge-time-status {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: 700;
                text-align: center;
                width: 100%;
            }

            .status-positive {
                background-color: #dcfce7;
                color: #16a34a;
                border: 1px solid #bbf7d0;
            }

            .status-negative {
                background-color: #fef2f2;
                color: #dc2626;
                border: 1px solid #fecaca;
            }

            .status-unexecuted {
                color: #94a3b8;
                font-style: italic;
                font-size: 11px;
            }

            .text-muted-custom {
                color: #64748b;
                font-size: 11px;
                margin-top: 2px;
                display: block;
            }
        </style>

        <div class="academic-table-card dark:bg-gray-900 dark:border-gray-800">

            {{-- ترويسة الجدول المحدثة بالأعمدة المنفصلة --}}
            <div class="report-header-grid dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700">
                <span>اليوم والتاريخ</span>
                <span>الأستاذ</span>
                <span>المادة والقاعة</span>
                <span>الكلية / الدفعة</span>
                <span>الزمن المجدول</span>
                <span>الزمن الحقيقي</span>
                <span class="text-center">الفرق والالتزام</span>
            </div>

            {{-- حلقة تكرار المحاضرات الأكاديمية --}}
            @forelse($lectures as $lecture)
                @php
                    $execution = $lecture->lectureStaff;
                    $isExecuted = $execution && $execution->is_excutive;

                    $dayKey = strtolower($lecture->day_of_week);

                    $execDate = $isExecuted && $execution->date_excutive
                            ? $execution->date_excutive->format('Y-m-d')
                            : ($lecture->start_date ? $lecture->start_date->format('Y-m-d') : $formattedDate);

                    // $execDate = $isExecuted && $execution->date_excutive
                    //     ? $execution->date_excutive->format('Y-m-d')
                    //     : ($lecture->start_date ? $lecture->start_date->format('Y-m-d') : '—');

                    // الحسابات الزمنية الذكية للسطر الحالي
                    $scheduledMinutes = ($lecture->start_time && $lecture->end_time)
                        ? $lecture->start_time->diffInMinutes($lecture->end_time)
                        : 0;

                    $actualMinutes = ($isExecuted && $execution->actual_excutive_start_time && $execution->actual_excutive_end_time)
                        ? $execution->actual_excutive_start_time->diffInMinutes($execution->actual_excutive_end_time)
                        : 0;

                    $diffMinutes = $actualMinutes - $scheduledMinutes;
                    $percentage = $scheduledMinutes > 0 ? round(($actualMinutes / $scheduledMinutes) * 100) : 0;
                @endphp

                <div class="report-row-grid dark:border-gray-800">

                    {{-- العمود 1: اليوم والتاريخ --}}
                    <div>
                        <span class="font-bold text-gray-800 dark:text-gray-200 text-xs">
                            {{ $daysMapping[$dayKey] ?? $lecture->day_of_week ?? 'يوم غير محدد' }}
                        </span>
                        <span class="font-mono text-muted-custom">
                            {{ $execDate }}
                        </span>
                    </div>

                    {{-- العمود 2: الأستاذ --}}
                    <div>
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ $lecture->staff?->name ?? 'غير محدد' }}
                        </span>
                    </div>

                    {{-- العمود 3: المادة والرمز والقاعة --}}
                    <div>
                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400 block">
                            {{ $lecture->subject?->name_ar ?? 'المادة غير معرفة' }}
                        </span>
                        <span class="text-muted-custom font-medium">
                            🏛️ {{ $lecture->room?->name ?? 'قاعة غير محددة' }}
                        </span>
                    </div>

                    {{-- العمود 4: الكلية والدفعة --}}
                    <div>
                        <span class="text-xs text-gray-700 dark:text-gray-300 font-medium block">
                            {{ $lecture->subject?->faculty?->name_ar ?? 'الكلية الرئيسية' }}
                        </span>
                        <span class="text-muted-custom">
                            {{ $lecture->grouping?->name ?? 'دفعة عامة' }}
                        </span>
                    </div>

                    {{-- العمود 5: الزمن المجدول --}}
                    <div class="time-cell dark:text-gray-300">
                        @if($lecture->start_time && $lecture->end_time)
                            <div>{{ $lecture->start_time->format('H:i') }} - {{ $lecture->end_time->format('H:i') }}</div>
                            <div class="text-[10px] text-gray-400 font-sans font-normal">({{ $scheduledMinutes }} دقيقة)</div>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </div>

                    {{-- العمود 6: الزمن الحقيقي --}}
                    <div class="time-cell text-emerald-600 dark:text-emerald-400">
                        @if($isExecuted && $execution->actual_excutive_start_time && $execution->actual_excutive_end_time)
                            <div>{{ $execution->actual_excutive_start_time->format('H:i') }} -
                                {{ $execution->actual_excutive_end_time->format('H:i') }}</div>
                            <div class="text-[10px] text-emerald-500/80 font-sans font-normal">({{ $actualMinutes }} دقيقة فعلي)
                            </div>
                        @else
                            <span class="status-unexecuted">لم تنفذ</span>
                        @endif
                    </div>

                    {{-- العمود 7: الفرق ونسبة الالتزام --}}
                    <div class="text-center">
                        @if($isExecuted && $execution->actual_excutive_start_time && $execution->actual_excutive_end_time)
                            @if($diffMinutes == 0)
                                <span class="badge-time-status status-positive">
                                    ⏱️ مطابق ({{ $percentage }}%)
                                </span>
                            @elseif($diffMinutes > 0)
                                <span class="badge-time-status status-positive">
                                    ↗ زيادة {{ $diffMinutes }} د ({{ $percentage }}%)
                                </span>
                            @else
                                <span class="badge-time-status status-negative">
                                    📉 نقص {{ abs($diffMinutes) }} د ({{ $percentage }}%)
                                </span>
                            @endif
                        @else
                            <span class="badge-time-status bg-gray-100 text-gray-400 border border-gray-200">
                                غياب / لغيت
                            </span>
                        @endif
                    </div>

                </div>
            @empty
                <div class="text-center py-12 text-sm text-gray-400 dark:text-gray-600">
                  {{ $this->date_excutive_formatted }}  لا توجد محاضرات مجدولة حالياً لعرضها في تقرير الإشراف الأكاديمي.
                </div>
            @endforelse

        </div>

    </div>
</x-filament-panels::page>