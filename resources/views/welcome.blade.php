{{--
    الصفحة الرئيسية — النظام الأكاديمي
    ضع الملف في: resources/views/welcome.blade.php
    الشعار:     public/images/logo.png   (أو غيّر $logo أدناه)
    المسارات:  student.login / admin.login  (تُستخدم /student/login و /admin/login إن لم تكن معرّفة)
--}}
@php
    $university   = config('academic.university_name', config('university.name_ar'));
    $systemName   = config('academic.system_name', 'النظام الأكاديمي');
    $logo         = asset(config('academic.logo', config('university.logo')));
    $academicYear = config('academic.year', '2026 / 2027');
    $supportPhone = config('academic.support_phone', config('university.phone'));
    $studentUrl   = Route::has('student.login') ? route('student.login') : url('/student/login');
    $adminUrl     = Route::has('admin.login')   ? route('admin.login')   : url('/admin/login');
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $systemName }} | {{ $university }}</title>
    <link rel="icon" href="{{ $logo }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600&family=Reem+Kufi:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --paper:  #F7F8F6;
            --ink:    #17262B;
            --muted:  #5B6B70;
            --nile:   #0D3B47;
            --river:  #1F6F7D;
            --seal:   #B07D2A;
            --line:   #D9E0DF;
            --radius-door: 28px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { -webkit-text-size-adjust: 100%; }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--paper);
            color: var(--ink);
            font-family: "IBM Plex Sans Arabic", system-ui, sans-serif;
            font-size: 16px;
            line-height: 1.75;
        }
        a { color: inherit; }
        :focus-visible { outline: 3px solid var(--seal); outline-offset: 3px; }

        /* ---------- header ---------- */
        .top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            max-width: 1120px;
            width: 100%;
            margin: 0 auto;
            padding: 22px 24px;
        }
        .brand { display: flex; align-items: center; gap: 14px; text-decoration: none; }
        .brand img { width: 56px; height: 56px; object-fit: contain; }
        .brand strong { display: block; font-family: "Reem Kufi", sans-serif; font-size: 1.25rem; font-weight: 700; color: var(--nile); line-height: 1.3; }
        .brand span { display: block; font-size: .85rem; color: var(--muted); line-height: 1.4; }
        .year {
            font-size: .85rem;
            color: var(--muted);
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 4px 14px;
            white-space: nowrap;
        }

        /* ---------- hero ---------- */
        main { flex: 1; width: 100%; max-width: 1120px; margin: 0 auto; padding: 24px 24px 56px; }
        .hero { display: grid; grid-template-columns: 1fr auto; align-items: end; gap: 32px; padding: 32px 0 40px; border-bottom: 1px solid var(--line); }
        .hero h1 {
            font-family: "Reem Kufi", sans-serif;
            font-weight: 700;
            font-size: clamp(2.4rem, 6vw, 4.25rem);
            line-height: 1.15;
            color: var(--nile);
        }
        .hero p { margin-top: 14px; max-width: 46ch; color: var(--muted); font-size: 1.075rem; }
        .seal {
            width: 148px; height: 148px;
            border-radius: 50%;
            display: grid; place-items: center;
            background: #fff;
            box-shadow: 0 0 0 1px var(--line), 0 0 0 10px var(--paper), 0 0 0 11px var(--seal);
        }
        .seal img { width: 108px; height: 108px; object-fit: contain; }

        /* ---------- doors ---------- */
        .doors { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 40px; }
        .door {
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 340px;
            padding: 36px 36px 32px;
            border-radius: var(--radius-door);
            text-decoration: none;
            overflow: hidden;
            transition: transform .2s ease;
        }
        .door:hover { transform: translateY(-3px); }
        .door--student { background: #fff; border: 1px solid var(--line); color: var(--ink); }
        .door--admin   { background: var(--nile); color: #EAF2F2; }
        .door__icon { width: 64px; height: 64px; border-radius: 18px; display: grid; place-items: center; }
        .door--student .door__icon { background: #E6F0EF; color: var(--river); }
        .door--admin   .door__icon { background: rgba(255,255,255,.1); color: #F2D59B; }
        .door__icon svg { width: 34px; height: 34px; }
        .door h2 { margin-top: 24px; font-family: "Reem Kufi", sans-serif; font-size: 1.85rem; font-weight: 700; line-height: 1.3; }
        .door ul { list-style: none; margin-top: 12px; display: grid; gap: 4px; }
        .door li { position: relative; padding-inline-start: 18px; font-size: .98rem; }
        .door li::before { content: ""; position: absolute; inset-inline-start: 0; top: .78em; width: 7px; height: 7px; border-radius: 50%; background: currentColor; opacity: .35; }
        .door--student li { color: var(--muted); }
        .door--admin li { color: #B9CFD1; }
        .door__cta {
            margin-top: auto;
            align-self: flex-start;
            display: inline-flex; align-items: center; gap: 10px;
            padding: 12px 26px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 1rem;
        }
        .door--student .door__cta { background: var(--river); color: #fff; }
        .door--admin   .door__cta { background: #F2D59B; color: var(--nile); }
        .door__cta svg { width: 18px; height: 18px; }
        /* large watermark glyph — the one bold gesture */
        .door__mark { position: absolute; inset-inline-end: -36px; bottom: -44px; width: 240px; height: 240px; opacity: .06; pointer-events: none; }
        .door--admin .door__mark { opacity: .09; color: #F2D59B; }

        /* ---------- footer ---------- */
        footer { border-top: 1px solid var(--line); }
        .foot { max-width: 1120px; margin: 0 auto; padding: 18px 24px; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px 24px; font-size: .85rem; color: var(--muted); }
        .foot a { text-decoration: none; direction: ltr; unicode-bidi: embed; }
        .foot a:hover { color: var(--river); }

        @media (max-width: 760px) {
            .top { padding: 16px; }
            .brand img { width: 44px; height: 44px; }
            .year { display: none; }
            main { padding: 8px 16px 40px; }
            .hero { grid-template-columns: 1fr; padding: 20px 0 28px; }
            .seal { display: none; }
            .doors { grid-template-columns: 1fr; gap: 16px; margin-top: 28px; }
            .door { min-height: 0; padding: 28px 24px 24px; }
            .door__cta { margin-top: 24px; }
            .foot { padding: 16px; }
        }
        @media (prefers-reduced-motion: reduce) { .door { transition: none; } .door:hover { transform: none; } }
    </style>
</head>
<body>

<header class="top">
    <a class="brand" href="{{ url('/') }}">
        <img src="{{ $logo }}" alt="شعار {{ $university }}">
        <div>
            <strong>{{ $university }}</strong>
            <span>{{ $systemName }}</span>
        </div>
    </a>
    <span class="year">العام الدراسي {{ $academicYear }}</span>
</header>

<main>
    <section class="hero">
        <div>
            <h2>{{ $systemName }}</h2>
            <p>بوابة واحدة للتسجيل والنتائج والسجل الأكاديمي. اختر نوع حسابك للدخول.</p>
        </div>
        <div class="seal" aria-hidden="true"><img src="{{ $logo }}" alt=""></div>
    </section>

    <section class="doors" aria-label="اختيار نوع الحساب">
        {{-- بوابة الطالب --}}
        <a class="door door--student" href="{{ $studentUrl }}">
            <span class="door__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9l10-5 10 5-10 5-10-5z"/><path d="M6 11v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/><path d="M22 9v6"/></svg>
            </span>
            <h2>الطالب</h2>
            <ul>
                <li>تسجيل المقررات وسداد الرسوم</li>
                <li>النتائج والمعدل التراكمي</li>
                <li>الجدول الدراسي والإفادات</li>
            </ul>
            <span class="door__cta">دخول الطالب
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
            </span>
            <svg class="door__mark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="M2 9l10-5 10 5-10 5-10-5z"/><path d="M6 11v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/></svg>
        </a>

        {{-- بوابة الإدارة --}}
        <a class="door door--admin" href="{{ $adminUrl }}">
            <span class="door__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M4 10h16"/><path d="M12 3l8 4H4l8-4z"/><path d="M6 10v8M10 10v8M14 10v8M18 10v8"/></svg>
            </span>
            <h2>الإدارة</h2>
            <ul>
                <li>القبول وملفات الطلاب</li>
                <li>رصد الدرجات واعتماد النتائج</li>
                <li>التقارير والصلاحيات</li>
            </ul>
            <span class="door__cta">دخول الإدارة
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
            </span>
            <svg class="door__mark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="M12 3l8 4H4l8-4z"/><path d="M4 10h16M6 10v8M10 10v8M14 10v8M18 10v8M3 21h18"/></svg>
        </a>
    </section>
</main>

<footer>
    <div class="foot">
        <span>&copy; {{ date('Y') }} {{ $university }} — جميع الحقوق محفوظة</span>
        <span>الدعم الفني: <a href="tel:{{ $supportPhone }}">{{ $supportPhone }}</a></span>
    </div>
</footer>

</body>
</html>