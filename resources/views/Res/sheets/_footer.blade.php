{{-- تذييل الصفحة: $footLeft (الرقم/العدد)، $footRight (الطابع/التاريخ) --}}
<div class="mt-1 flex items-center justify-between border-t border-slate-200 pt-1 text-[9px] font-medium text-slate-500">
    <span>{{ $footLeft }}</span>
    <span class="font-bold text-slate-500">
        @if (! empty($footStats))
            الجالسون {{ $footStats['sitting'] }} • الناجحون {{ $footStats['passed'] }} • الراسبون {{ $footStats['failed'] }}
            @if ($footStats['sitting'] > 0) • نسبة النجاح {{ round($footStats['passed'] / $footStats['sitting'] * 100) }}% @endif
        @else
            {{ __('main.univ_name', [], 'ar') }} — وثيقة رسمية
        @endif
    </span>
    <span>{{ $footRight }}</span>
</div>
