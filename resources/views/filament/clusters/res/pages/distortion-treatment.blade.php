<x-filament-panels::page>
    @assets
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @endassets

    <x-filament::section>
        {{ $this->filtersForm }}
    </x-filament::section>

    @php($stats = $this->getStatistics())

    @if (! $stats)
        <x-filament::section>
            <p class="text-sm text-gray-500">{{ __('res.distortion_pick_subject') }}</p>
        </x-filament::section>
    @else
        @php($before = $stats['before'])
        @php($after = $stats['after'])

        <div class="grid gap-4 sm:grid-cols-4">
            @foreach ([
                __('res.distortion_students') => $before['count'],
                __('res.distortion_mean') => $before['mean'],
                __('res.distortion_sd') => $before['sd'],
                __('res.distortion_fail') => $before['failCount'],
            ] as $label => $value)
                <x-filament::section>
                    <div class="text-sm text-gray-500">{{ $label }}</div>
                    <div class="text-2xl font-bold">{{ $value }}</div>
                </x-filament::section>
            @endforeach
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-filament::section :heading="__('res.distortion_chart_marks')">
                <div
                    wire:key="marks-{{ md5(json_encode([$before['histogram'], $after['histogram'] ?? null])) }}"
                    x-data="{
                        init() {
                            new Chart(this.$refs.canvas, {
                                type: 'line',
                                data: {
                                    labels: Array.from({ length: 101 }, (_, i) => i),
                                    datasets: [
                                        { label: @js(__('res.distortion_before')), data: @js($before['histogram']), borderColor: '#64748b', tension: 0.3, pointRadius: 0 },
                                        @if ($after)
                                        { label: @js(__('res.distortion_after')), data: @js($after['histogram']), borderColor: '#f59e0b', borderDash: [6, 4], tension: 0.3, pointRadius: 0 },
                                        @endif
                                    ],
                                },
                                options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
                            })
                        },
                    }"
                    class="h-72"
                >
                    <canvas x-ref="canvas"></canvas>
                </div>
            </x-filament::section>

            <x-filament::section :heading="__('res.distortion_chart_grades')">
                <div
                    wire:key="grades-{{ md5(json_encode([$before['grades'], $after['grades'] ?? null])) }}"
                    x-data="{
                        init() {
                            new Chart(this.$refs.canvas, {
                                type: 'bar',
                                data: {
                                    labels: @js(collect($before['grades'])->pluck('title')),
                                    datasets: [
                                        { label: @js(__('res.distortion_before')), data: @js(collect($before['grades'])->pluck('count')), backgroundColor: '#64748b' },
                                        @if ($after)
                                        { label: @js(__('res.distortion_after')), data: @js(collect($after['grades'])->pluck('count')), backgroundColor: '#f59e0b' },
                                        @endif
                                    ],
                                },
                                options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
                            })
                        },
                    }"
                    class="h-72"
                >
                    <canvas x-ref="canvas"></canvas>
                </div>
            </x-filament::section>
        </div>

        <x-filament::section :heading="__('res.distortion_table')">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-500">
                        <th class="p-2 text-start">{{ __('res.grades') }}</th>
                        <th class="p-2 text-start">{{ __('res.distortion_range_col') }}</th>
                        <th class="p-2 text-start">{{ __('res.distortion_before') }}</th>
                        @if ($after)
                            <th class="p-2 text-start">{{ __('res.distortion_after') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($before['grades'] as $index => $row)
                        <tr class="border-t border-gray-200 dark:border-white/10">
                            <td class="p-2 font-semibold">{{ $row['title'] }}</td>
                            <td class="p-2">{{ $row['lower'] }} – {{ $row['upper'] }}</td>
                            <td class="p-2">{{ $row['count'] }}</td>
                            @if ($after)
                                <td class="p-2">{{ $after['grades'][$index]['count'] }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-filament::section>

        <x-filament::section :heading="__('res.distortion_actions')">
            {{ $this->curveForm }}

            @if ($stats['previewError'])
                <p class="mt-3 text-sm text-danger-600">{{ $stats['previewError'] }}</p>
            @elseif ($after)
                <p class="mt-3 text-sm text-gray-500">
                    {{ __('res.distortion_preview', ['mean' => $after['mean'], 'sd' => $after['sd'], 'fail' => $after['failCount']]) }}
                </p>
            @endif

            <div class="mt-4">
                {{ $this->applyAction }}
            </div>
        </x-filament::section>

        <x-filament::section :heading="__('res.distortion_history')">
            @forelse ($this->getHistory() as $curve)
                <div class="flex items-center justify-between gap-4 border-t border-gray-200 py-2 text-sm first:border-t-0 dark:border-white/10" wire:key="curve-{{ $curve->id }}">
                    <div>
                        <div class="font-semibold">
                            {{ $curve->method === 'range' ? __('res.distortion_method_range') : __('res.distortion_method_stddev') }}
                            — {{ $curve->affected_count }} {{ __('res.distortion_students') }}
                        </div>
                        <div class="text-gray-500">
                            {{ $curve->user?->name }} · {{ $curve->created_at->format('Y-m-d H:i') }}
                            @if ($curve->note) · {{ $curve->note }} @endif
                        </div>
                    </div>
                    @if ($curve->isReverted())
                        <x-filament::badge color="gray">{{ __('res.distortion_reverted_badge') }} {{ $curve->reverted_at->format('Y-m-d H:i') }}</x-filament::badge>
                    @else
                        <x-filament::button color="danger" size="sm" wire:click="mountAction('revert', { curve: {{ $curve->id }} })">
                            {{ __('res.distortion_revert') }}
                        </x-filament::button>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">{{ __('res.distortion_no_history') }}</p>
            @endforelse
        </x-filament::section>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>
