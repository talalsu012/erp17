<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('university.name_ar') }}</title>
    <link rel="icon" href="{{ asset(config('university.icon')) }}">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f3f4f6; font-family: 'Cairo', sans-serif; color: #111827; }
        .card { width: 100%; max-width: 28rem; margin: 1rem; padding: 2rem; text-align: center; background: #fff; border-radius: .75rem; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        img { width: 5rem; height: 5rem; object-fit: contain; }
        h1 { margin: 1rem 0 .5rem; font-size: 1.25rem; }
        p { margin: 0 0 1.5rem; color: #4b5563; font-size: .9rem; }
        a { display: inline-block; padding: .6rem 1.25rem; border-radius: .375rem; background: #4f46e5; color: #fff; font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ asset(config('university.logo')) }}" alt="{{ config('university.name_ar') }}">
        <h1>محاولات كثيرة</h1>
        <p>
            تجاوزت عدد المحاولات المسموح بها.
            @php($retryAfter = (int) ($exception->getHeaders()['Retry-After'] ?? 0))
            @if ($retryAfter > 0)
                حاول مرة أخرى بعد {{ max(1, (int) ceil($retryAfter / 60)) }} دقيقة تقريبًا.
            @else
                حاول مرة أخرى بعد قليل.
            @endif
        </p>
        <a href="{{ url()->previous() }}">رجوع</a>
    </div>
</body>
</html>
