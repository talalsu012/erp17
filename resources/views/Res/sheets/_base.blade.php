{{--
    القالب الأساسي لكل كشوف الطباعة (Tailwind عبر CDN كبقية قوالب الطباعة في المشروع).
    المتغيرات: $pageTitle، $landscape، $design (page_header|continuous)، $designs، $perPage، $backUrl،
    $sheetH (مم). الأقسام: toolbar (أدوات إضافية)، content.
--}}
@php
    $landscape = $landscape ?? true;
    $pageSize = $landscape ? 'A4 landscape' : 'A4 portrait';
    $printable = $printable ?? ($landscape ? ['w' => 281, 'h' => 194] : ['w' => 194, 'h' => 281]);
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Tajawal', 'Tahoma', 'sans-serif'] },
                    colors: {
                        brand: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                    },
                },
            },
        }
    </script>
    <style>
        @page { size: {{ $pageSize }}; margin: 8mm; }
        * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .vertical-text { writing-mode: vertical-rl; transform: rotate(180deg); }
        .toggle-checkbox:checked { right: 0; border-color: #3b82f6; }
        .toggle-checkbox:checked + .toggle-label { background-color: #3b82f6; }
    </style>
</head>
<body class="bg-gray-100 font-sans text-slate-900 antialiased print:bg-white" data-design="{{ $design }}">

<header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur print:hidden">
    <div class="flex flex-wrap items-center gap-3 px-5 py-2.5 text-sm">
        @isset($backUrl)
            <a href="{{ $backUrl }}" onclick="goBack(event)"
               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-bold text-slate-700 transition hover:bg-slate-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M20 12H4"/></svg>
                رجوع
            </a>
        @endisset

        @isset($designs)
            <div class="inline-flex items-center gap-1 rounded-lg bg-slate-100 p-1">
                @foreach ($designs as $key => $label)
                    <a href="{{ request()->fullUrlWithQuery(['design' => $key]) }}"
                       class="rounded-md px-3 py-1 transition {{ $design === $key ? 'bg-white font-bold text-brand-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">{{ $label }}</a>
                @endforeach
            </div>

            <label class="inline-flex items-center gap-2 text-slate-600">
                طلاب في الصفحة
                <select onchange="location = this.value" class="rounded-lg border border-slate-300 bg-white py-1 ps-2 pe-7 font-bold text-slate-800">
                    @foreach ([8, 10, 12, 14, 16, 18, 20, 22] as $n)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $n]) }}" @selected($n === $perPage)>{{ $n }}</option>
                    @endforeach
                </select>
            </label>
        @endisset

        @yield('toolbar')

    </div>
</header>

<main class="py-5 print:py-0">
    @yield('content')
</main>

<button type="button" onclick="window.print()"
        class="fixed bottom-4 left-4 z-30 rounded bg-blue-600 px-4 py-2 font-bold text-white shadow-lg transition hover:bg-blue-700 print:hidden">
    طباعة التقرير
</button>

@isset($backUrl)
    <script>
        function goBack(event) {
            if (window.opener) {
                event.preventDefault();
                window.close();
            }
        }
    </script>
@endisset
</body>
</html>
