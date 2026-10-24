<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>بطاقة الطالب - {{ config('university.name_ar') }}</title>
    <!-- تم استخدام خط Cairo للغة العربية -->

    <style>
        @font-face {
            font-family: Cairo;

            src:url("{{ public_path('fonts/Cairo-Regular.ttf') }}") format("truetype");
        }

        body {

            font-family: Cairo;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: 85.6mm 53.98mm;
            margin: 0;
        }

        body {
            background: white;
            font-family: 'Cairo', 'dejavusans', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.0;
        }

        .card {
            width: 85.6mm;
            height: 53.98mm;
            background: white;
            position: relative;
            page-break-after: always;
            overflow: hidden;
        }

        .card:last-child {
            page-break-after: avoid;
        }

        /* تنسيقات الترويسة */
        .header {
            background: #0A1C3A;
            padding: 1mm 8mm 0.5mm 2mm;
            position: relative;
            text-align: center;
        }

        .logo {
            position: absolute;
            top: 0.8mm;
            right: 2mm;
            width: 6.5mm;
            height: 6.5mm;
            background: white;
            border: 0.3mm solid #C8A951;
            border-radius: 50%;
            overflow: hidden;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .academy-ar {
            font-size: 7pt;
            font-weight: 800;
            color: white;
        }

        .academy-en {
            font-size: 3pt;
            font-weight: 400;
            direction: ltr;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 0.5mm;
        }

        .badge {
            background: #C8A951;
            text-align: center;
            padding: 0.3mm 0;
            font-size: 4.5pt;
            font-weight: 800;
            color: #0A1C3A;
        }

        /* تنسيقات المحتوى الأمامي */
        .content {
            background: white;
            padding: 1mm 0;
            width: 100%;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            vertical-align: top;
            padding: 0;
        }

        .photo-cell {
            width: 23mm;
            padding-right: 2mm;
            text-align: center;
        }

        .photo-frame {
            width: 19mm;
            height: 22mm;
            border: 0.3mm solid #C8A951;
            border-radius: 1.2mm;
            background: #F5F5F5;
            display: inline-block;
            overflow: hidden;
        }

        .student-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .info-cell {
            text-align: right;
            padding-left: 2mm;
            padding-right: 2mm;
        }

        .info-row {
            margin-bottom: 0.5mm;
        }

        .label {
            font-size: 5pt;
            font-weight: 700;
            color: #555;
            display: inline-block;
            width: 16mm;
        }

        .value {
            font-size: 6pt;
            font-weight: 800;
            color: #111;
            display: inline-block;
        }

        .gold-value {
            color: #D32F2F;
            font-size: 6.5pt;
            font-weight: 800;
        }

        /* التذييل */
        .footer {
            background: #0A1C3A;
            padding: 0.5mm 2.5mm;
            border-top: 0.3mm solid #C8A951;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4mm;
        }

        .footer-left {
            float: right;
            font-size: 3.5pt;
            font-weight: 700;
            color: white;
        }

        .footer-right {
            float: left;
            font-size: 3.5pt;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            direction: ltr;
        }

        /* تنسيقات الوجه الخلفي */
        .back-body {
            background: white;
            padding: 3mm 2mm;
            text-align: center;
        }

        .qr-box {
            width: 16mm;
            height: 16mm;
            margin: 0 auto 2mm auto;
            border: 0.5mm solid #C8A951;
            border-radius: 1.5mm;
            padding: 0.5mm;
        }

        .qr-box img {
            width: 100%;
            height: 100%;
        }

        .notice-box {
            background: #F9F9F9;
            border: 0.3mm solid #C8A951;
            border-radius: 1.5mm;
            padding: 1.5mm;
            width: 90%;
            margin: 0 auto 1.5mm auto;
        }

        .notice-text {
            font-size: 5.5pt;
            font-weight: 700;
            color: #0A1C3A;
            line-height: 1.4;
        }

        .card-number-box {
            text-align: center;
        }

        .card-number-label {
            font-size: 4.5pt;
            font-weight: 700;
        }

        .card-number-value {
            font-size: 7pt;
            font-weight: 800;
            color: #222;
            direction: ltr;
        }

        .back-footer {
            text-align: center;
        }

        .back-footer span {
            font-size: 3pt;
            color: white;
            margin: 0 1mm;
        }
    </style>
</head>

<body>

    <!-- الوجه الأمامي -->
    <div class="card">
        <div class="header">
            <div class="logo">
                <img src="{{ $data['logo'] }}" alt="{{ config('university.name_en') }}">
            </div>
            <div class="academy-ar">{{ $data['title'] }}</div>
            <div class="academy-en">{{ config('university.name_en') }}</div>
        </div>

        <div class="badge">{{ $data['in_card'] }} | STUDENT ID CARD</div>

        <div class="content">
            <table class="info-table">
                <tr>
                    <td class="photo-cell">
                        <div class="photo-frame">
                            <img src="{{ $data['avatar'] }}" class="student-photo" alt="صورة الطالب">
                        </div>
                    </td>
                    <td class="info-cell">
                        <div class="info-row">
                            <div class="label">{{ $data['full_name'] }}</div>
                            <div class="value">{{ $data['name_ar'] }}</div>
                        </div>
                        <div class="info-row">
                            <div class="label">{{ $data['stu_num'] }}</div>
                            <div class="value gold-value">{{ $data['university_no'] }}</div>
                        </div>
                        <div class="info-row">
                            <div class="label">{{ $data['std_prog'] }}</div>
                            <div class="value">{{ $data['faculty'] }}</div>
                        </div>
                        <div class="info-row">
                            <div class="label">{{ $data['std_level'] }}</div>
                            <div class="value">{{ $data['level'] }}</div>
                        </div>
                        <div class="info-row">
                            <div class="label">{{ $data['std_card_expi'] }}</div>
                            <div class="value gold-value">{{ $data['expiry_date'] }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <div class="footer-left">{{ $data['content'] }}</div>
            <div class="footer-right">Academic Affairs Office</div>
        </div>
    </div>

    <!-- الوجه الخلفي -->
    <div class="card">
        <div class="header">
            <div class="logo">
                <img src="{{ $data['logo'] }}" alt="{{ config('university.name_en') }}">
            </div>
            <div class="academy-ar">{{ $data['title'] }}</div>
            <div class="academy-en">{{ config('university.name_en') }}</div>
        </div>

        <div class="badge">{{ $data['in_card'] }} | STUDENT ID CARD</div>

        <div class="back-body">
            <div class="qr-box">
                <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code">
            </div>

            <div class="notice-box">
                <div class="notice-text">{{ $data['in_come'] }} <br> {{ $data['in_come_card'] }}</div>
            </div>

            <div class="card-number-box">
                <div class="card-number-label">{{ $data['std_card'] }}</div>
                <div class="card-number-value">{{ $data['card_number'] ?? 'UNI-XXXX' }}</div>
            </div>
        </div>

        <div class="footer back-footer">
            <span>Academic Affairs Office</span> |
            <span>{{ $data['content'] }}</span> |
            <span style="direction: ltr;">Web: www.kassalauni.edu.sd</span>
        </div>
    </div>

    <!-- نصوص المغنطة المخفية لطابعة Fargo -->
    <div style="color: white; font-size: 1pt; position: absolute; bottom: 0; left: 0; z-index: -1;">
        {{ $data['mag_track_1'] }}
        {{ $data['mag_track_2'] }}
    </div>


</body>

</html>