{{-- التواقيع: $signatories = [['name' =>, 'title' =>], ...] (الخانات بلا اسم محذوفة مسبقاً). $footerTitle نص اختياري قبل التواقيع. إن خلا الاثنان لا يُطبع شيء --}}
@if (count($signatories) || filled($footerTitle ?? null))
    <div class="mt-auto">
        @if (filled($footerTitle ?? null))
            <div class="mb-1 line-clamp-2 text-center text-[11px] font-bold leading-snug text-slate-700">{{ $footerTitle }}</div>
        @endif

        @if (count($signatories))
            <div class="grid gap-8 pt-2" style="height: {{ $sigH }}mm; grid-template-columns: repeat({{ count($signatories) }}, minmax(0, 1fr))">
                @foreach ($signatories as $person)
                    <div class="text-center">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $person['title'] }}</div>
                        <div class="mt-0.5 text-[12px] font-extrabold text-slate-800">{{ $person['name'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif
