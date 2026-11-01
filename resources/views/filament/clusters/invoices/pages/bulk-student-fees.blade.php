<x-filament-panels::page>
    {{ $this->form }}

    @if ($this->preview)
    <style>
        .bulk-fees-wrap { overflow-x: auto; border: 1px solid #d1d5db; border-radius: 10px; }
        .bulk-fees-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .bulk-fees-table th { background: #f3f4f6; color: #374151; font-weight: 700; font-size: 13px; padding: 10px 12px; text-align: center; white-space: nowrap; border-bottom: 2px solid #d1d5db; border-inline-start: 1px solid #e5e7eb; }
        .bulk-fees-table td { padding: 10px 12px; text-align: center; border-bottom: 1px solid #e5e7eb; border-inline-start: 1px solid #e5e7eb; }
        .bulk-fees-table th:first-child, .bulk-fees-table td:first-child { border-inline-start: 0; }
        .bulk-fees-table tbody tr:nth-child(even) { background: #f9fafb; }
        .bulk-fees-table tbody tr:hover { background: #eef2ff; }
        .bulk-fees-table tbody tr:last-child td { border-bottom: 0; }
        .bulk-fees-table .name { text-align: start; min-width: 200px; font-weight: 600; }
        .bulk-fees-table .num { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .bulk-fees-table .muted { color: #9ca3af; }
        .bulk-fees-table .strong { font-weight: 700; color: #111827; }
        .bulk-fees-table .diff { display: inline-block; margin-inline-start: 6px; padding: 1px 6px; border-radius: 999px; font-size: 11px; font-weight: 600; }
        .bulk-fees-table .diff.up { background: #dcfce7; color: #15803d; }
        .bulk-fees-table .diff.down { background: #fee2e2; color: #b91c1c; }
        .dark .bulk-fees-wrap { border-color: rgba(255, 255, 255, .15); }
        .dark .bulk-fees-table th { background: rgba(255, 255, 255, .06); color: #e5e7eb; border-color: rgba(255, 255, 255, .15); }
        .dark .bulk-fees-table td { border-color: rgba(255, 255, 255, .1); }
        .dark .bulk-fees-table tbody tr:nth-child(even) { background: rgba(255, 255, 255, .03); }
        .dark .bulk-fees-table tbody tr:hover { background: rgba(255, 255, 255, .08); }
        .dark .bulk-fees-table .strong { color: #fff; }
    </style>

        @php
            $samples = $this->preview['samples'];
            $money = fn ($value): string => number_format((float) $value, 0);
        @endphp

        <x-filament::section :heading="__('invoice.bulk_fees.preview_heading', ['count' => $this->preview['count']])">
            <div class="bulk-fees-wrap">
                <table class="bulk-fees-table">
                    <thead>
                        <tr>
                            <th>{{ __('reg.university_no') }}</th>
                            <th class="name">{{ __('reg.name_ar') }}</th>
                            <th>{{ __('invoice.bulk_fees.before_total') }}</th>
                            <th>{{ __('invoice.bulk_fees.after_total') }}</th>
                            <th>{{ __('reg.xmod') }}</th>
                            @foreach (['pay1', 'pay2', 'pay3', 'pay4'] as $installment)
                                <th>{{ __("reg.{$installment}") }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($samples as $row)
                            @php($difference = (float) $row['after']['all_fees'] - (float) $row['before']['all_fees'])
                            <tr>
                                <td class="num" dir="ltr">{{ $row['university_no'] }}</td>
                                <td class="name">{{ $row['name_ar'] }}</td>
                                <td class="num muted">{{ $money($row['before']['all_fees']) }}</td>
                                <td class="num strong">
                                    {{ $money($row['after']['all_fees']) }}
                                    @if ($difference != 0)
                                        <span class="diff {{ $difference > 0 ? 'up' : 'down' }}" dir="ltr">{{ $difference > 0 ? '+' : '' }}{{ $money($difference) }}</span>
                                    @endif
                                </td>
                                <td class="num">{{ $money($row['after']['xmod']) }}</td>
                                @foreach (['pay1', 'pay2', 'pay3', 'pay4'] as $installment)
                                    <td class="num {{ (float) $row['after'][$installment] === 0.0 ? 'muted' : '' }}">{{ $money($row['after'][$installment]) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($this->preview['count'] > count($samples))
                <p class="mt-2 text-xs text-gray-500">
                    معروض {{ count($samples) }} من {{ $this->preview['count'] }} طالباً كعيّنة، ويُطبَّق التغيير على الجميع.
                </p>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
