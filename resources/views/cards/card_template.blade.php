@php
    $primary = $data['primary'] ?? '#0A522E';
    $primaryDark = $data['primary_dark'] ?? '#052D19';
    $primaryTint = $data['primary_tint'] ?? '#E6F3EB';
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>بطاقة الطالب - {{ config('university.name_ar') }}</title>
    <style>
        body {
            font-family: 'cairo', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #FFFFFF;
            color: #1B2A22;
        }

        .page-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 85.6mm;
            height: 53.98mm;
            background-color: #FFFFFF;
            z-index: -1;
        }

        .circle-tl {
            position: absolute;
            top: -12mm;
            left: -12mm;
            width: 38mm;
            height: 38mm;
            background-color: {{ $primaryTint }};
            border-radius: 50%;
            z-index: 0;
        }

        .lbl {
            color: #6B7A72;
            font-size: 5.5px;
        }

        .val {
            color: #1B2A22;
            font-size: 8px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- ======================= الوجه الأمامي ======================= -->
    <div class="page-bg"></div>
    <div class="circle-tl"></div>

    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 13mm; background-color: {{ $primary }}; background: linear-gradient(135deg, {{ $primaryDark }}, {{ $primary }}); z-index: 3;"></div>
    <div style="position: absolute; top: 13mm; left: 0; width: 100%; height: 0.7mm; background-color: #C9A227; z-index: 3;"></div>

    <!-- الشعار واسم الجامعة -->
    <div style="position: absolute; top: 1.6mm; right: 3mm; width: 10mm; height: 10mm; background-color: #FFFFFF; border-radius: 50%; text-align: center; z-index: 5;">
        <img src="{{ $data['logo'] }}" alt="{{ config('university.name_ar') }}" width="26" height="26" style="margin-top: 1mm;">
    </div>
    <div style="position: absolute; top: 2.2mm; right: 15mm; z-index: 5;">
        <div style="font-size: 11px; font-weight: bold; color: #FFFFFF;">{{ $data['title'] }}</div>
        <div dir="ltr" style="font-size: 5.5px; color: #A8D5BA; text-align: right;">{{ config('university.name_en') }}</div>
    </div>

    <!-- شارة بطاقة طالب -->
    <div style="position: absolute; top: 3.2mm; left: 3mm; border: 0.3mm solid #C9A227; border-radius: 1mm; padding: 0.6mm 2mm; text-align: center; z-index: 5;">
        <div style="font-size: 6.5px; font-weight: bold; color: #C9A227;">{{ $data['in_card'] }}</div>
        <div dir="ltr" style="font-size: 5px; color: #A8D5BA;">{{ $data['badge_en'] ?? config('card.badge_en') }}</div>
    </div>

    <!-- الصورة -->
    <div style="position: absolute; top: 15.8mm; right: 3mm; width: 24mm; text-align: center; z-index: 5;">
        <div style="border: 0.5mm solid {{ $primary }}; padding: 0.4mm; background-color: #FFFFFF; border-radius: 2mm; width: 21mm;">
            <img src="{{ $data['avatar'] ?: $data['logo'] }}" style="width: 20mm; height: 26mm;" alt="صورة الطالب">
        </div>
    </div>

    <!-- البيانات -->
    <div style="position: absolute; top: 15.8mm; right: 29mm; left: 3mm; z-index: 5;">
        <div class="lbl">{{ $data['full_name_label'] }}</div>
        <div style="font-size: 9.5px; font-weight: bold; color: #1B2A22; padding-bottom: 1mm;">{{ $data['name_ar'] }}</div>

        <div class="lbl">{{ $data['stu_num_label'] }} &nbsp;·&nbsp; <span dir="ltr">{{ $data['badge_en'] ?? config('card.badge_en') }}</span></div>
        <div dir="ltr" style="font-size: 9px; font-weight: bold; color: {{ $primary }}; text-align: right; padding-bottom: 1mm;">{{ $data['university_no'] }}</div>

        <div class="lbl">{{ $data['std_prog_label'] }}</div>
        <div class="val" style="padding-bottom: 1mm;">{{ $data['faculty'] }}</div>

        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="50%" style="padding-bottom: 1mm;">
                    <div class="lbl">{{ $data['std_level_label'] }}</div>
                    <div class="val">{{ $data['level'] }}</div>
                </td>
                <td width="50%" style="padding-bottom: 1mm;">
                    <div class="lbl">{{ $data['batch_label'] ?? config('card.labels.batch') }}</div>
                    <div class="val">{{ $data['batch'] ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td width="50%">
                    <div class="lbl">{{ $data['std_status_label'] }}</div>
                    <div class="val" style="color: #0E7340;">● {{ $data['active_status'] }}</div>
                </td>
                <td width="50%">
                    <div class="lbl">{{ $data['std_card_expi_label'] }}</div>
                    <div class="val" dir="ltr" style="color: #B3261E; text-align: right;">{{ $data['expiry_date'] }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- الفوتر الأمامي -->
    <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 4.6mm; background-color: {{ $primary }}; background: linear-gradient(135deg, {{ $primaryDark }}, {{ $primary }}); border-top: 0.5mm solid #C9A227; z-index: 5;">
        <table width="100%" height="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="right" valign="middle" style="padding-right: 3mm; color: #FFFFFF; font-size: 6.5px; font-weight: bold;">{{ $data['content'] }}</td>
                <td align="left" valign="middle" dir="ltr" style="padding-left: 3mm; color: #A8D5BA; font-size: 5.5px;">{{ $data['issuer_en'] ?? config('card.issuer_en') }}</td>
            </tr>
        </table>
    </div>

    <!-- ======================= الوجه الخلفي ======================= -->
    <pagebreak />

    <div class="page-bg"></div>

    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 9mm; background-color: {{ $primary }}; background: linear-gradient(135deg, {{ $primaryDark }}, {{ $primary }}); z-index: 3;"></div>
    <div style="position: absolute; top: 9mm; left: 0; width: 100%; height: 0.7mm; background-color: #C9A227; z-index: 3;"></div>
    <div style="position: absolute; top: 1mm; left: 0; width: 100%; text-align: center; z-index: 5;">
        <div style="font-size: 10px; font-weight: bold; color: #FFFFFF;">{{ config('university.name_ar') }}</div>
        <div dir="ltr" style="font-size: 5.5px; color: #A8D5BA;">{{ config('university.name_en') }}</div>
    </div>

    <!-- QR ورقم البطاقة -->
    <div style="position: absolute; top: 12mm; right: 3mm; width: 28mm; text-align: center; z-index: 5;">
        <div style="border: 0.5mm solid {{ $primary }}; padding: 0.6mm; background-color: #FFFFFF; border-radius: 1.5mm; width: 25mm;">
            <img src="data:image/svg+xml;base64,{{ $qrCode }}" width="92" height="92">
        </div>
        <div class="lbl" style="margin-top: 1mm;">{{ $data['std_card_label'] }}</div>
        <div dir="ltr" style="color: #0E7340; font-size: 7px; font-weight: bold;">{{ $data['card_number'] }}</div>
    </div>

    <!-- التعليمات -->
    <div style="position: absolute; top: 12.5mm; right: 33mm; left: 3mm; z-index: 5;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="4mm" valign="top" style="color: #14A05A; font-size: 8px; padding-bottom: 1.6mm;">●</td>
                <td valign="top" style="font-size: 7.5px; font-weight: bold; color: #1B2A22; padding-bottom: 1.6mm;">{{ $data['in_come'] }} {{ $data['in_come_card'] }}</td>
            </tr>
            <tr>
                <td valign="top" style="color: #14A05A; font-size: 8px; padding-bottom: 1.6mm;">●</td>
                <td valign="top" style="font-size: 6.5px; color: #44544B; padding-bottom: 1.6mm;">{{ $data['scan_note'] ?? config('card.instructions.scan') }}</td>
            </tr>
            <tr>
                <td valign="top" style="color: #14A05A; font-size: 8px;">●</td>
                <td valign="top" style="font-size: 6.5px; color: #44544B;">{{ $data['return_note'] ?? config('card.instructions.return') }} {{ $data['content'] }}</td>
            </tr>
        </table>
    </div>

    <!-- التوقيع -->
    <div style="position: absolute; bottom: 8mm; right: 33mm; left: 3mm; border-top: 0.2mm dashed #9FB5A8; padding-top: 0.5mm; z-index: 5;">
        <span class="lbl">{{ $data['signature_label'] ?? config('card.labels.signature') }}</span>
    </div>

    <!-- الفوتر الخلفي -->
    <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 5.4mm; background-color: {{ $primary }}; background: linear-gradient(135deg, {{ $primaryDark }}, {{ $primary }}); border-top: 0.5mm solid #C9A227; z-index: 5;">
        <table width="100%" height="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="right" valign="middle" style="padding-right: 3mm; color: #FFFFFF; font-size: 6.5px; font-weight: bold;">{{ $data['content'] }}</td>
                <td align="left" valign="middle" dir="ltr" style="padding-left: 3mm; color: #A8D5BA; font-size: 5.5px;">{{ $data['website'] ?? config('card.website') }}</td>
            </tr>
        </table>
    </div>

</body>

</html>
