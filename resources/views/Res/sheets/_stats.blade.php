{{-- بطاقات الإحصاء (الشاشة فقط): $cards = [['label' =>, 'value' =>, 'tone' => brand|sky|emerald|rose], ...] --}}
@php
    $tones = [
        'brand' => 'text-blue-600',
        'sky' => 'text-purple-600',
        'emerald' => 'text-green-600',
        'rose' => 'text-red-600',
    ];
@endphp
<section class="mx-auto mb-5 grid grid-cols-2 gap-4 rounded-lg bg-gray-50 p-4 print:hidden" style="max-width: {{ $printable['w'] }}mm; grid-template-columns: repeat({{ min(count($cards), 5) }}, minmax(0, 1fr))">
    @foreach ($cards as $card)
        <div class="rounded-lg bg-white p-4 text-center shadow transition hover:-translate-y-0.5 hover:shadow-md {{ $tones[$card['tone']] ?? $tones['brand'] }}">
            <div class="text-gray-500">{{ $card['label'] }}</div>
            <div class="text-2xl font-bold">{{ $card['value'] }}</div>
        </div>
    @endforeach
</section>
