{{-- مفاتيح العرض داخل شريط الأدوات العلوي (الشاشة فقط): وضع الخلايا وأقسام الإحصاء. تُحفظ في المتصفح (display_script) --}}
@php
    $toggles = [
        ['id' => 'toggleMarks', 'label' => 'الدرجات والتقديرات', 'title' => 'عرض الدرجة مع التقدير في كل خلية'],
        ['id' => 'toggleMarksOnly', 'label' => 'الدرجات فقط', 'title' => 'عرض الدرجات بدل التقديرات'],
        ['id' => 'toggleRecStats', 'label' => 'إحصائيات التوصيات', 'title' => 'صفحة إحصائيات التوصيات'],
        ['id' => 'toggleSubStats', 'label' => 'إحصائيات المواد', 'title' => 'صفحة إحصائيات المواد'],
    ];
@endphp
<div class="inline-flex flex-wrap items-center gap-x-4 gap-y-1 rounded-lg bg-slate-100 px-3 py-1.5" title="خيارات العرض">
    @foreach ($toggles as $toggle)
        <label for="{{ $toggle['id'] }}" title="{{ $toggle['title'] }}" class="inline-flex cursor-pointer select-none items-center gap-2 text-slate-700">
            <span>{{ $toggle['label'] }}</span>
            <span class="relative inline-block h-5 w-9">
                <input type="checkbox" id="{{ $toggle['id'] }}" class="toggle-checkbox absolute block h-5 w-5 cursor-pointer appearance-none rounded-full border-4 bg-white">
                <span class="toggle-label block h-5 overflow-hidden rounded-full bg-gray-300"></span>
            </span>
        </label>
    @endforeach
</div>
