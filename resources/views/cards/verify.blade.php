<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>التحقق من بطاقة الطالب</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif; background: #eef3f0; color: #1b2a22; }
        .wrap { max-width: 420px; margin: 0 auto; min-height: 100vh; background: #fff; }
        .verdict { padding: 22px 16px; text-align: center; color: #fff; }
        .verdict.ok { background: #0E7340; }
        .verdict.no { background: #B3261E; }
        .verdict .big { font-size: 30px; font-weight: 700; margin: 0; }
        .verdict .why { font-size: 15px; margin: 6px 0 0; opacity: .95; }
        .body { padding: 20px 18px; text-align: center; }
        .photo { width: 140px; height: 176px; object-fit: cover; border-radius: 12px; border: 3px solid #14A05A; background: #f1f5f2; }
        .name { font-size: 22px; font-weight: 700; margin: 14px 0 4px; }
        dl { margin: 18px 0 0; text-align: right; }
        .row { display: flex; justify-content: space-between; gap: 12px; padding: 11px 0; border-top: 1px solid #e3e9e5; }
        dt { color: #6b7a72; font-size: 14px; }
        dd { margin: 0; font-weight: 600; font-size: 16px; }
        .foot { padding: 14px; text-align: center; color: #6b7a72; font-size: 12px; border-top: 1px solid #e3e9e5; }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="verdict {{ $verdict['allowed'] ? 'ok' : 'no' }}">
            <p class="big">{{ $verdict['allowed'] ? 'مسموح بالدخول' : 'ممنوع الدخول' }}</p>
            @if ($verdict['reason'])
                <p class="why">{{ $verdict['reason'] }}</p>
            @endif
        </div>

        <div class="body">
            @if ($name)
                <img class="photo" src="{{ $photo }}" alt="صورة الطالب">
                <div class="name">{{ $name }}</div>
                <dl>
                    <div class="row"><dt>الكلية</dt><dd>{{ $faculty ?: '—' }}</dd></div>
                    <div class="row"><dt>الفصل</dt><dd>{{ $level ?: '—' }}</dd></div>
                    <div class="row"><dt>العام الدراسي</dt><dd>{{ $batch ?: '—' }}</dd></div>
                    <div class="row"><dt>حالة البطاقة</dt><dd>{{ $status ?: '—' }}</dd></div>
                    <div class="row"><dt>صالحة حتى</dt><dd dir="ltr">{{ $expiry ?: '—' }}</dd></div>
                </dl>
            @endif
        </div>

        <div class="foot">{{ config('university.name_ar') }} — عمادة شؤون الطلاب</div>
    </div>
</body>

</html>
