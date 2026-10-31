<x-filament-panels::page>
    {{ $this->form }}

    @if ($this->preview)
        <x-filament::section :heading="__('invoice.bulk_fees.preview_heading', ['count' => $this->preview['count']])">
            <table class="w-full text-sm text-start">
                <thead>
                    <tr>
                        <th>{{ __('reg.university_no') }}</th>
                        <th>{{ __('reg.name_ar') }}</th>
                        <th>{{ __('invoice.bulk_fees.before_total') }}</th>
                        <th>{{ __('invoice.bulk_fees.after_total') }}</th>
                        <th>{{ __('reg.xmod') }}</th>
                        <th>{{ __('reg.pay1') }} / {{ __('reg.pay2') }} / {{ __('reg.pay3') }} / {{ __('reg.pay4') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->preview['samples'] as $row)
                        <tr>
                            <td>{{ $row['university_no'] }}</td>
                            <td>{{ $row['name_ar'] }}</td>
                            <td>{{ $row['before']['all_fees'] }}</td>
                            <td>{{ $row['after']['all_fees'] }}</td>
                            <td>{{ $row['after']['xmod'] }}</td>
                            <td>{{ $row['after']['pay1'] }} / {{ $row['after']['pay2'] }} / {{ $row['after']['pay3'] }} / {{ $row['after']['pay4'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-filament::section>
    @endif
</x-filament-panels::page>
