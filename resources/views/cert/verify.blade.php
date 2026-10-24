<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>التحقق من الشهادة</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-slate-100">

    <div class="flex min-h-screen items-center justify-center bg-slate-50 p-4 font-sans" dir="rtl">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200">
        
        <div class="bg-emerald-600 px-6 py-8 text-center text-white">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-white/20 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-10 w-10">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold tracking-tight">نتيجة التحقق من الشهادة</h2>
            <p class="mt-1 text-emerald-100 opacity-90">Certificate Verification Result</p>
        </div>

        <div class="space-y-6 p-8">
            
            <div class="group transition-all">
                <p class="text-sm font-medium text-slate-500">اسم الطالب </p>
                <p class="mt-1 text-lg font-bold text-slate-900">{{ $certificate->name_ar }}</p>
                <div class="mt-2 h-px w-full bg-slate-100 group-hover:bg-emerald-200"></div>
            </div>

            <div class="group transition-all">
                <p class="text-sm font-medium text-slate-500">الدرجة العلمية </p>
                <p class="mt-1 text-lg font-semibold text-slate-800">{{ $certificate->degree->name_ar }}</p>
                <div class="mt-2 h-px w-full bg-slate-100 group-hover:bg-emerald-200"></div>
            </div>

            <div class="group transition-all">
                <p class="text-sm font-medium text-slate-500">تاريخ الإصدار </p>
                <p class="mt-1 text-lg font-semibold text-slate-800">{{ $certificate->alumia_date }}</p>
                <div class="mt-2 h-px w-full bg-slate-100 group-hover:bg-emerald-200"></div>
            </div>

            <div class="mt-8 flex items-center justify-center rounded-xl bg-emerald-50 p-4 border border-emerald-100">
                <span class="relative flex h-3 w-3 ml-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <p class="text-base font-bold text-emerald-700">
                    شهادة صحيحة ومعتمدة رسمياً
                </p>
            </div>
        </div>

        <div class="bg-slate-50 px-8 py-4 text-center">
            <p class="text-xs text-slate-400">
                تم التحقق عبر النظام الإلكتروني لـ{{ config('university.name_ar') }}
            </p>
        </div>
    </div>
</div>

</body>
</html>
