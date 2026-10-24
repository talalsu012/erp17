<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $lang === 'ar' ? 'شهادة التخرج' : 'Graduation Certificate' }}</title>
    <style>
        @if($lang === 'ar')
            @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
            body { font-family: 'Tajawal', sans-serif; }
        @else
            @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap');
            body { font-family: 'Roboto', sans-serif; }
        @endif

        .certificate-container {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 20mm;
            border: 1px solid #6497b1;
            position: relative;
            background-color: #fff;

            border-radius: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .university-name {
            font-size: 28px;
            font-weight: bold;
            color: #011f4b;
            margin-bottom: 5px;
        }

        .certificate-title {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
            text-decoration: underline;
        }

        .content {
            margin: 30px 0;
            line-height: 1.8;
            font-size: 18px;
        }

        .student-name {
            font-size: 22px;
            font-weight: bold;
            text-decoration: underline;
            margin: 10px 0;
        }

        .signatures {
            position: absolute;
            bottom: 50px;
            width: calc(100% - 40mm);
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 200px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 80px;
            width: 100%;
        }

        .seal {
            position: absolute;
            right: 50px;
            bottom: 50px;
            width: 120px;
            opacity: 0.8;
        }

        .certificate-number {
            position: absolute;
            top: 20px;
            {{ $lang === 'ar' ? 'left' : 'right' }}: 20px;
            font-size: 14px;
        }

        .issue-date {
            position: absolute;
            top: 40px;
            {{ $lang === 'ar' ? 'left' : 'right' }}: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate-number">
            {{ $lang === 'ar' ? 'رقم الشهادة:' : 'Certificate No:' }} {{ $certificate->certificate_number }}
        </div>

        <div class="issue-date">
            {{ $lang === 'ar' ? 'تاريخ الإصدار:' : 'Issue Date:' }} {{ $certificate->issue_date->format('Y-m-d') }}
        </div>

        <div class="header">
            <div class="university-name">
                {{ $lang === 'ar' ? config('university.name_ar') : config('university.name_en') }}
            </div>
            <div>
                {{ $lang === 'ar' ? 'المملكة العربية السعودية' : 'Kingdom of Saudi Arabia' }}
            </div>
        </div>

        <div class="certificate-title">
            {{ $lang === 'ar' ? 'شهادة تخرج' : 'Graduation Certificate' }}
        </div>

        <div class="content">
            <p>
                {{ $lang === 'ar' ? 'هذا لإثبات أن الطالب' : 'This is to certify that' }}
                <span class="student-name">{{ $certificate->student->name }}</span>
                {{ $lang === 'ar' ? 'قد أتم بنجاح متطلبات الحصول على درجة البكالوريوس في' : 'has successfully completed all requirements for the degree of Bachelor in' }}
                {!! nl2br(e($certificate->description_en)) !!}.
            </p>

            <p>{{ $certificate->{'description_' . $lang} }}</p>

            <p>
                {{ $lang === 'ar' ? 'تم منح هذه الشهادة في' : 'This certificate is granted on' }}
                {{ $certificate->issue_date->format('Y-m-d') }}.
            </p>
        </div>

        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>{{ $certificate->{'signature_authority_' . $lang} }}</div>
                <div>{{ $lang === 'ar' ? 'رئيس الجامعة' : 'University President' }}</div>
            </div>

            <div class="signature-box">
                <div class="signature-line"></div>
                <div>{{ $lang === 'ar' ? 'عميد الكلية' : 'Dean of Faculty' }}</div>
            </div>
        </div>

        @if($certificate->seal_image_path)
        <img src="{{ storage_path('app/public/' . $certificate->seal_image_path) }}" class="seal">
        @endif
    </div>
</body>
</html>
