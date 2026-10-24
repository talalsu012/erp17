<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
<meta charset="utf-8">

<style>

@page {
    margin: 0;
}

body {
    margin: 0;
    padding: 0;
    font-family: DejaVu Sans Mono, sans-serif;
}

/* حجم البطاقة الحقيقي */

.card {

    width: 85.6mm;
    height: 53.98mm;

    position: relative;
    overflow: hidden;

}

/* انتقال للوجه الثاني */

.page-break {
    page-break-after: always;
}

/* الخلفية */

.background {

    position: absolute;

    top: 0;
    left: 0;

    width: 100%;
    height: 100%;

}

/* صورة الطالب */

.photo {

    position: absolute;

    top: 12mm;
    right: 6mm;

    width: 22mm;
    height: 26mm;

    border: 0.3mm solid #000;

}

/* الاسم */

.name {

    position: absolute;

    top: 14mm;
    right: 32mm;

    font-size: 10pt;
    font-weight: bold;

}

/* الرقم الجامعي */

.university-no {

    position: absolute;

    top: 22mm;
    right: 32mm;

    font-size: 9pt;

}

/* QR */

.qr {

    position: absolute;

    bottom: 6mm;
    left: 6mm;

    width: 18mm;
    height: 18mm;

}

.footer {

    position: absolute;

    bottom: 4mm;

    width: 100%;

    text-align: center;

    font-size: 7pt;

}

</style>

</head>

<body>

<!-- FRONT -->

<div class="card page-break">

@if($front_bg)

<img 
src="{{ public_path('images\card-front.png') }}" 
class="background">

@endif

<img 
src="{{ public_path($Card->student->photo) }}" 
class="photo">

<div class="name">

{{ $data['name_label'] }} : 

{{ $data['name_ar'] }}

</div>

<div class="university-no">

{{ $data['university_no_label'] }} :
{{ $data['university_no'] }}

</div>

</div>

<!-- BACK -->

<div class="card">

@if($back_bg)

<img
src="{{ public_path('images/card-back.png') }}"
class="background">

@endif

<img
src="data:image/svg+xml;base64,{{ $qrCode }}"
class="qr">

<div class="footer">

يرجى إبراز هذه البطاقة عند الدخول

</div>

</div>

</body>

</html>