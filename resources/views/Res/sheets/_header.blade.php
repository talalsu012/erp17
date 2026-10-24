{{-- ترويسة الصفحة: $logos[university|faculty]، $line1 (الجامعة/الكلية)، $line2 (عنوان الكشف)، $chips (قائمة بيانات قصيرة) --}}
@php
    $chips = $chips ?? [];
@endphp

<div class="mb-[2mm] flex items-center gap-3 rounded-lg border border-sky-200 bg-sky-100 px-4 text-sky-950" style="height: {{ $headH }}mm">
    <div class="flex h-[16mm] w-[16mm] shrink-0 items-center justify-center">
        @if (! empty($logos['university']))
            <div class="flex h-full w-full items-center justify-center rounded-md bg-white p-0.5">
                <img src="{{ $logos['university'] }}" alt="" class="max-h-full max-w-full object-contain">
            </div>
        @endif
    </div>
    <div class="min-w-0 flex-1 text-center leading-snug">
        @if (($design ?? null) !== 'continuous')
            <div class="text-[10px] font-medium text-sky-700">بسم الله الرحمن الرحيم</div>
        @endif
        <h1 class="text-[18px] font-bold">{{ $line1 }}</h1>
        @if (filled($line101 ?? null))
            <h2 class="text-[13px] font-medium">{{ $line101 }}</h2>
        @endif
        <h3 class="text-[12px] text-sky-800">{{ $line2 }}</h3>
        @if (count($chips))
            <div class="mt-0.5 flex flex-wrap justify-center gap-x-5 text-[10.5px]">
                @foreach ($chips as $chip)
                    <p>{{ $chip }}</p>
                @endforeach
            </div>
        @endif
    </div>
    <div class="flex h-[16mm] w-[16mm] shrink-0 items-center justify-center">
        @if (! empty($logos['faculty']))
            <div class="flex h-full w-full items-center justify-center rounded-md bg-white p-0.5">
                <img src="{{ $logos['faculty'] }}" alt="" class="max-h-full max-w-full object-contain">
            </div>
        @endif
    </div>
</div>
