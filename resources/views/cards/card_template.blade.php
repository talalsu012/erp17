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
        }

        /* خلفية الصفحة بيضاء بالكامل */
        .page-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 85.6mm;
            height: 53.98mm;
            background-color: #FFFFFF;
            z-index: -1;
        }

        /* دوائر الزينة الخلفية بدرجات الأخضر الشفاف */
        .circle-tl {
            position: absolute;
            top: -10mm;
            left: -10mm;
            width: 40mm;
            height: 40mm;
            background-color: rgba(20, 160, 90, 0.08);
            border-radius: 50%;
            z-index: 0;
        }

        .circle-br {
            position: absolute;
            bottom: -5mm;
            right: -10mm;
            width: 35mm;
            height: 35mm;
            background-color: rgba(20, 160, 90, 0.12);
            border-radius: 50%;
            z-index: 0;
        }

        /* النصوص المشتركة */
        .text-muted {
            color: #555555;
            font-size: 6px;
        }

        .text-main {
            color: #000000;
            font-size: 9px;
            font-weight: bold;
        }

        .text-accent {
            color: #0E7340;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- ======================= الوجه الأمامي ======================= -->
    <div class="page-bg"></div>
    <div class="circle-tl"></div>

    <!-- 1. الهيدر (الشعار واسم الجامعة) -->
    <div style="position: absolute; top: 2mm; right: 2mm; width: 81.6mm; z-index: 5;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="right" valign="middle">
                    <div style="font-size: 11px; font-weight: bold; color: #0A522E;"> {{ $data['title'] }}</div>
                    <div class="text-muted" dir="ltr" style="margin-top: 1px;">{{ config('university.name_en') }}</div>
                </td>
                <td width="10mm" align="left" valign="middle">
                    <img src="{{ $data['logo'] }}" alt="{{ config('university.name_ar') }}"
                        width="30" height="30">
                </td>
            </tr>
        </table>
    </div>

    <!-- 2. شريط العنوان الأخضر الداكن -->
    <div
        style="position: absolute; top: 12mm; left: 0; width: 100%; height: 0.5mm; background-color: #14A05A; z-index: 5;">
    </div>
    <div
        style="position: absolute; top: 12.5mm; left: 0; width: 100%; height: 4mm; background-color: #0A522E; text-align: center; z-index: 5;">
        <table width="100%" height="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center" valign="middle"
                    style="color: #FFFFFF; font-size: 7.5px; font-weight: bold; letter-spacing: 1px;">
                    {{ $data['in_card'] }} &nbsp;|&nbsp; STUDENT ID CARD
                </td>
            </tr>
        </table>
    </div>

    <!-- 3. منطقة بيانات الطالب والصورة -->
    <div style="position: absolute; top: 18mm; right: 2mm; width: 81.6mm; z-index: 5;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <!-- قسم الصورة (يمين - مع التوسيط وتدوير الحواف باستخدام Div للخلفية) -->
                <!-- قسم الصورة (يمين - مع التوسيط وتدوير الحواف) -->
                <td width="28mm" align="center" valign="top">
                    <!-- إطار الصورة -->
                    <div
                        style="border: 1.5px solid #14A05A; padding: 2px; background: #FFFFFF; border-radius: 6px; display: inline-block;">
                        @if($data['avatar'])
                            <img src="{{ $data['avatar'] }}" width="72" height="92"
                                style="border-radius: 4px; display: block; margin: 0 auto;" alt="صورة الطالب">
                        @else
                            <!-- في حال عدم وجود صورة، نعرض الشعار مع خلفية رمادية فاتحة لتمييزه -->
                            <img src="{{ $data['logo'] }}" width="72" height="92"
                                style="border-radius: 4px; display: block; margin: 0 auto; background-color: #f9f9f9;"
                                alt="صورة الطالب">
                        @endif
                    </div>

                    <!-- رقم الطالب -->
                    <div
                        style="background: #14A05A; color: #FFFFFF; font-size: 8px; font-weight: bold; text-align: center; padding: 2.5px 0; margin-top: 1.5mm; border-radius: 4px; width: 76px; display: inline-block;">
                        {{ $data['university_no'] }}
                    </div>
                </td>
                <!-- قسم البيانات (يسار) -->
                <td valign="top" style="padding-right: 3mm;">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td colspan="2" style="padding-bottom: 0.5mm;">
                                <div class="text-muted">{{ $data['full_name_label'] }}</div>
                                <div class="text-main">{{ $data['name_ar'] }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-bottom: 0.5mm;">
                                <div class="text-muted">{{ $data['stu_num_label'] }}</div>
                                <div class="text-accent" dir="ltr" style="text-align: right;">
                                    {{ $data['university_no'] }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-bottom: 0.5mm;">
                                <div class="text-muted">{{ $data['std_prog_label'] }}</div>
                                <div style="color: #333333; font-size: 8.5px; font-weight: bold;">{{ $data['faculty'] }}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                <div class="text-muted">{{ $data['std_level_label'] }}</div>
                                <div style="color: #333333; font-size: 8px; font-weight: bold;">{{ $data['level'] }}
                                </div>
                            </td>
                            <td width="50%">
                                <div class="text-muted">{{ $data['std_status_label'] }}</div>
                                <div style="color: #14A05A; font-size: 8.5px; font-weight: bold;">●
                                    {{ $data['active_status'] }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-top: 1mm;">
                                <div class="text-muted">{{ $data['std_card_expi_label'] }}</div>
                                <div style="color: #D93025; font-size: 8px; font-weight: bold;" dir="ltr">
                                    {{ $data['expiry_date'] }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- 4. الفوتر الأمامي -->
    <div
        style="position: absolute; bottom: 0; left: 0; width: 100%; height: 5mm; background-color: #0A522E; border-top: 0.5mm solid #14A05A; z-index: 5;">
        <table width="100%" height="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center" valign="middle">
                    <span style="color: #FFFFFF; font-size: 6px;">{{ $data['content'] }}</span>
                    <span style="color: #14A05A; font-size: 9px; margin: 0 2mm;">•••••</span>
                    <span style="color: #FFFFFF; font-size: 6px;" dir="ltr">Academic Affairs</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- ======================= الوجه الخلفي ======================= -->
    <pagebreak />

    <div class="page-bg"></div>
    <div class="circle-br" style="top: -5mm; right: auto; left: -10mm;"></div>

    <div style="position: absolute; top: 3mm; left: 0; width: 100%; text-align: center; z-index: 5;">
        <div style="font-size: 13px; font-weight: bold; color: #0A522E;">{{ config('university.name_ar') }}</div>
        <div class="text-muted" dir="ltr" style="margin-top: 1px;">{{ config('university.name_en') }}</div>
    </div>

    <div
        style="position: absolute; top: 12mm; left: 0; width: 100%; height: 0.5mm; background-color: #14A05A; z-index: 5;">
    </div>

    <!-- تم إضافة ارتفاع (height) للجدول لضمان التوسيط العمودي -->
    <div style="position: absolute; top: 14mm; right: 2mm; width: 81.6mm; height: 32mm; z-index: 5;">
        <table width="100%" height="100%" cellpadding="0" cellspacing="0">
            <tr>
                <!-- قسم الـ QR ورقم البطاقة (يمين) -->
                <td width="35mm" align="center" valign="middle">
                    <div
                        style="border: 1.5px solid #14A05A; padding: 2px; background: #FFFFFF; border-radius: 4px; display: inline-block;">
                        <img src="data:image/svg+xml;base64,{{ $qrCode }}" width="72" height="72"
                            style="display: block;">
                    </div>
                    <!-- رقم البطاقة تم نقله هنا -->
                    <div style="text-align: center; margin-top: 1.5mm;">
                        <br>
                        <div class="text-muted" style="font-size: 5px;">{{ $data['std_card_label'] }}</div>
                        <div style="color: #0E7340; font-size: 9.5px; letter-spacing: 1px; font-weight: bold;"
                            dir="ltr">{{ $data['card_number'] }}</div>
                    </div>
                </td>

                <!-- قسم التنبيهات (يسار - وموسطة بالكامل) -->
                <td align="center" valign="middle" style="padding-right: 2mm; padding-left: 2mm;">
                    <div>
                        <span style="color: #14A05A; font-size: 8px;">●</span>
                        <span style="color: #000000; font-size: 8.5px; font-weight: bold; line-height: 1.6;">
                            {{ $data['in_come'] }}<br>{{ $data['in_come_card'] }}
                        </span>
                    </div>

                    <div style="text-align: center;">
                        <span style="color: #14A05A; font-size: 8px;">●</span>
                        <span style="color: #555555; font-size: 7px; font-weight: bold;" dir="ltr">Verified &
                            Authenticated</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- الفوتر الخلفي -->
    <div
        style="position: absolute; bottom: 0; left: 0; width: 100%; height: 6mm; background-color: #0A522E; border-top: 0.5mm solid #14A05A; z-index: 5;">
        <table width="100%" height="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center" valign="middle">
                    <div style="font-size: 8px; font-weight: bold; color: #FFFFFF;">أمانة الشؤون العلمية</div>
                    <div style="font-size: 6px; color: #A0C8B0; margin-top: 1px;" dir="ltr">Academic Affairs Office |
                        www.kassalauni.edu.sd</div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>