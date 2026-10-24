{{-- تذييل الصفحة: $footLeft (الرقم/العدد)، $footRight (الطابع/التاريخ) --}}
<div class="mt-1 flex items-center justify-between border-t border-slate-200 pt-1 text-[9px] font-medium text-slate-500">
    <span>{{ $footLeft }}</span>
    <span class="font-bold text-slate-400">{{ __('main.univ_name', [], 'ar') }} — وثيقة رسمية</span>
    <span>{{ $footRight }}</span>
</div>
