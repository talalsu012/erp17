<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $lang === 'ar' ? 'إفادة' : 'Affidavit' }}</title>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 16px;
        }

        .page {
            width: 90%;
            margin: 0 auto;
            padding: 15mm;
            position: relative;
            text-align: center;
            background: #fff;
            box-sizing: border-box;
        }

        .title {
            font-size: 40px;
            font-weight: bold;
            margin-top: 25mm;
            margin-bottom: 20mm;
        }

        .top-info {
            position: absolute;
            top: 35mm;
            width: 100%;
            font-size: 14px;
        }

        .top-info .right {
            float: right;
        }

        .top-info .left {
            float: left;
        }

        .content {
            font-size: 18px;
            line-height: 2.2;
            width: 92%;
            margin: 0 auto;
        }

        .name {
            font-size: 22px;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }

        .bold {
            font-weight: bold;
        }

        .dates-table {
            width: 100%;
            margin-top: 15mm;
            text-align: center;
        }

        .signatures {
            width: 100%;
            position: absolute;
            bottom: -10mm;
            left: 0;
            text-align: center;
        }

        .signatures td {
            width: 50%;
            padding: 10px;
            vertical-align: bottom;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="page">
        @php
            $isMale = $certificate->gender_code == 10010001;
        @endphp

        <div class="top-info">
            <div class="{{ $lang === 'ar' ? 'right' : 'left' }}">
                {{ $lang === 'ar' ? 'رقم الطالب:' : 'Student No:' }}
                {{ $certificate->universityno }}
            </div>

            <div class="{{ $lang === 'ar' ? 'left' : 'right' }}">
                {{ $lang === 'ar' ? 'التاريخ :' : 'Date :' }}
                {{ \Carbon\Carbon::now()->locale($lang)->translatedFormat('j F Y') }}
            </div>
        </div>

        <div class="title">
            {{ $lang === 'ar' ? 'إفــادة' : 'AFFIDAVIT' }}
        </div>

        <div class="content">
            @if($lang === 'ar')
                <p>
                    بهذا تُفِيد أمانة الشؤون العلمية بـ
                    <span class="bold">أكاديمية السودان للعلوم المصرفية والمالية</span>
                    بأن /
                </p>

                <div class="name">
                    {{ $certificate->getNameAttribute() }}
                    <br>
                    <span style="font-size: 16px; font-weight: normal;">
                        (الجنسية: {{ $certificate->nationalty?->name ?? 'غير متوفر' }})
                    </span>
                </div>

                <p>
                    قد {{ $isMale ? 'مُنح' : 'مُنحت' }} درجة
                    <span class="bold">{{ $certificate->degree?->name_ar }}</span>
                    في
                    <span class="bold">{{ $certificate->faculty?->name_ar ?? $certificate->faculty?->name }}
                        {{ $certificate->admission?->name_ar }}</span>
                    <br>
                    بتقدير (<span class="bold">{{ $certificate->grade?->name_ar }}</span>)
                </p>
            @else
                <p>
                    The Secretariat of Academic Affairs at
                    <span class="bold">Sudan Academy for Banking and Financial Sciences</span>
                    hereby certifies that:
                </p>

                <div class="name">
                    {{ $certificate->getNameAttribute() }}
                    <br>
                    <span style="font-size: 16px; font-weight: normal;">
                        (Nationality: {{ $certificate->nationalty?->name_en ?? $certificate->nationalty?->name ?? 'N/A' }})
                    </span>
                </div>

                <p>
                    Has been awarded the degree of
                    <span class="bold">{{ $certificate->degree?->name_en }}</span>
                    in
                    <span class="bold">{{ $certificate->faculty?->name_en ?? $certificate->faculty?->name }}
                        ({{ $certificate->admission?->name_en }})</span>
                    <br>
                    with grade (<span class="bold">{{ $certificate->grade?->name_en }}</span>)
                </p>
            @endif

            <table class="dates-table">
                <tr>
                    <td>
                        <b>
                            {{ $lang === 'ar' ? 'وذلك في دور ' : 'In the session of ' }}
                            {{ $lang === 'ar' ? $affidavit_date_ar : $affidavit_date_en }}
                        </b>
                    </td>
                </tr>
            </table>
        </div>

        <div class="text-center" style="margin-top:20mm; font-style: italic;">
            @if($lang === 'ar')
                حررت هذه الإفادة بناءً على طلب{{ $isMale ? 'ه' : 'ها' }} لتقديمها لمن يهمه الأمر.
            @else
                This affidavit has been issued upon {{ $isMale ? 'his' : 'her' }} request to be submitted to whom it may
                concern.
            @endif
        </div>

        {{-- Signatures --}}
        <table class="signatures">
            <tr>
                <td></td>

                <td>
                    <strong>
                        {{ $lang === 'ar'
    ? 'أمين الشؤون العلمية'
    : 'Secretary for Academic Affairs'
                }}
                    </strong>

                    <br><br>

                    {{ $lang === 'ar'
    ? ($signatories['scientific_affairs_name'] ?? '')
    : (
        $signatories['scientific_affairs_name_en']
        ?? $signatories['scientific_affairs_name']
        ?? ''
    )
            }}
                </td>
            </tr>
        </table>
    </div>

</body>

</html>