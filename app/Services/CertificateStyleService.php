<?php

// app/Services/CertificateStyleService.php

namespace App\Services;

use App\Models\Cert\CertStyle;

class CertificateStyleService
{
    public static function getStyles(?string $cert = null): array
    {
        return CertStyle::when(
            $cert,
            fn ($q) => $q->where('cert', $cert)
        )
            ->get()
            ->groupBy('group')
            ->map(fn ($items) => $items->pluck('value', 'key')->toArray())
            ->toArray();
    }

    public static function css(array $styles, string $lang): string
    {
        return '
        body {
            font-family: '.($styles['body']['font_family'] ?? 'Arial').';
            font-size: '.($styles['body']['font_size'] ?? '14px').';
        }

        .certificate-container {
            width: '.($styles['container']['width'] ?? '90%').';
            padding: '.($styles['container']['padding'] ?? '10mm').';
            background-color: '.($styles['container']['background'] ?? '#fff').";
            text-align: center;
            position: relative;
            height: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }

        .student-no {
            position: absolute;
            top: 60mm;

                {
                    {
                    $lang ==='ar' ? 'right': 'left'
                }
            }

            : 20mm;
            font-size: 14px;
        }

         .issue-date {
            font-size: 14px;
        }

         .date-section {
            margin: 10mm auto;
            width: 80%;
        }

        .date-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        .date-table td {
            padding: 3px;
        }

            
        .header-section {
            margin-top: ".($styles['header']['margin_top'] ?? '30mm').';
            margin-bottom: '.($styles['header']['margin_bottom'] ?? '15mm').';
        }

        .cert-title-main {
            font-size: '.($styles['title']['font_size'] ?? '36px').';
            color: '.($styles['title']['color'] ?? '#000').';
            font-weight: bold;
            margin-top: 30mm;
            margin-bottom: 15mm;

        }
       
        .content {
            margin: 25mm auto;
            line-height: 1.5;
            font-size: 18px;
            width: 100%;
        }

         .degree-details {
            font-weight: bold;
            line-height: 2;
            padding: 12px 0;

        }

        .student-name {
            font-size: '.($styles['student_name']['font_size'] ?? '22px').';
            font-weight: bold;
            margin: '.($styles['student_name']['margin'] ?? '15px 0').';
        }

        .qrcode-container {
            position: absolute;
            bottom: '.($styles['qr']['bottom'] ?? '-45mm').';
            '.($lang === 'ar' ? 'left' : 'right').': '.($styles['qr']['side'] ?? '20mm').';
        }

        .signatures-section {
            position: absolute;
            bottom: '.($styles['signature']['bottom'] ?? '-5mm').';
            width: 100%;
            text-align: center;
        }

        .issued-date-footer {
            position: absolute;
            bottom: '.($styles['footer']['bottom'] ?? '-35mm').';
            text-align: '.($lang === 'ar' ? 'right' : 'left').';
            font-weight: bold;
        }
        ';
    }

    public static function cssTranscript(array $styles, string $lang): string
    {
        $direction = ($lang === 'ar' ? 'rtl' : 'ltr');

        return '
        body {
            position: '.($styles['body']['position'] ?? 'relative').';
            font-family: '.($styles['body']['font-family'] ?? 'Arial').';
            font-size: '.($styles['body']['font-size'] ?? '14px').';
            line-height: '.($styles['body']['line-height'] ?? '1.0').';
            padding: '.($styles['body']['padding'] ?? '5px').';
            min-height: '.($styles['body']['min-height'] ?? '95vh').";
            direction: $direction;
        }

        .height-certificate {
            height: ".($styles['height-certificate']['height'] ?? '10rem').';
        }

        .certificate_detials
            {
                font-size: '.($styles['certificate_detials']['font-size'] ?? '18px').';
                font-weight: '.($styles['certificate_detials']['font-weight'] ?? 'bold').';
                margin-bottom: '.($styles['certificate_detials']['margin-bottom'] ?? '8px').';
                text-align: '.($styles['certificate_detials']['text-align'] ?? 'center').';

            }

        .student-details {
            font-size: '.($styles['student-details']['font-size'] ?? '12px').';
            font-weight: '.($styles['student-details']['font-weight'] ?? 'normal').';
            text-align: '.($styles['student-details']['text-align'] ?? 'center').';

        }
           

        .transcript-container {
            width: '.($styles['transcript-container']['width'] ?? '100%').';
            margin: '.($styles['transcript-container']['margin'] ?? '0 auto').';
            padding-bottom: '.($styles['transcript-container']['padding-bottom'] ?? '80px').';
        }

        .level-header {
            font-size: '.($styles['level-header']['font-size'] ?? '7pt').';
            font-weight: '.($styles['level-header']['font-weight'] ?? 'bold').';
            text-align: '.($styles['level-header']['text-align'] ?? 'center').';
            margin-bottom: '.($styles['level-header']['margin-bottom'] ?? '4px').';
            margin-top: '.($styles['level-header']['margin-top'] ?? '8px').';
            background-color: '.($styles['level-header']['background-color'] ?? '#f0f0f0').';
            padding: '.($styles['level-header']['padding'] ?? '2px 0').';
            border-radius: '.($styles['level-header']['border-radius'] ?? '2px').';
            border: '.($styles['level-header']['border'] ?? '1px solid #000').';
        }

        .subject-table {
            border-collapse: '.($styles['subject-table']['border-collapse'] ?? 'collapse').';
            width: '.($styles['subject-table']['width'] ?? '100%').';
            table-layout: '.($styles['subject-table']['table-layout'] ?? 'fixed').';
            font-size: '.($styles['subject-table']['font-size'] ?? '7pt').';
        }

        .subject-table th, .subject-table td {
            border: '.($styles['subject-table-th-td']['border'] ?? '1px solid #000').';
            padding: '.($styles['subject-table-th-td']['padding'] ?? '0.5px 1px').';
            vertical-align: '.($styles['subject-table-th-td']['vertical-align'] ?? 'top').';
        }

        .grade-scale-horizontal {
            width: '.($styles['grade-scale-horizontal']['width'] ?? 'fit-content').';
            border-collapse: '.($styles['grade-scale-horizontal']['border-collapse'] ?? 'collapse').';
            margin-top: '.($styles['grade-scale-horizontal']['margin-top'] ?? '0').';
        }

        .grade-scale-horizontal th,
        .grade-scale-horizontal td {
            border: '.($styles['grade-scale-horizontal-th-td']['border'] ?? '1px solid #ccc').';
            padding: '.($styles['grade-scale-horizontal-th-td']['padding'] ?? '2px 6px').';
            text-align: '.($styles['grade-scale-horizontal-th-td']['text-align'] ?? 'center').';
            font-size: '.($styles['grade-scale-horizontal-th-td']['font-size'] ?? '6.5pt').';
        }

        .p-1 { 
            padding: '.($styles['p-1']['padding'] ?? '0.25rem').'; 
        }

        .space-y-1 > p { margin-bottom: '.($styles['space-y-1 > p']['margin-bottom'] ?? '0.1rem')."; }

        [dir='rtl'] .grade-scale-horizontal {
            margin-left: ".($styles['rtl-grade-scale-horizontal']['margin-left'] ?? 'auto').';
            margin-right: '.($styles['rtl-grade-scale-horizontal']['margin-right'] ?? '0').";
        }

        [dir='ltr'] .grade-scale-horizontal {
            margin-right: ".($styles['ltr-grade-scale-horizontal']['margin-right'] ?? 'auto').';
            margin-left: '.($styles['ltr-grade-scale-horizontal']['margin-left'] ?? '0').";
        }

        [dir='rtl'] .subject-table .text-left {
            text-align: ".($styles['rtl-subject-table .text-left']['text-align'] ?? 'right').";
        }

        [dir='ltr'] .subject-name-cell {
            padding-left: ".($styles['ltr-subject-name-cell']['padding-left'] ?? '0.5rem').';
            text-align: '.($styles['ltr-subject-name-cell']['text-align'] ?? 'left').";
        }

        [dir='rtl'] .subject-name-cell {
            padding-right: ".($styles['rtl-subject-name-cell']['padding-right'] ?? '0.5rem').';
            text-align: '.($styles['rtl-subject-name-cell']['text-align'] ?? 'right').';
        }

        .signatures-section {
            position: '.($styles['signatures-section']['position'] ?? 'absolute').';
            bottom: '.($styles['signatures-section']['bottom'] ?? '0.2in').';
            left: '.($styles['signatures-section']['left'] ?? '0').';
            right: '.($styles['signatures-section']['right'] ?? '0').';
            width: '.($styles['signatures-section']['width'] ?? '100%').';
            display: '.($styles['signatures-section']['display'] ?? 'flex').';
            justify-content: '.($styles['signatures-section']['justify-content'] ?? 'space-between').';
        }

        .signature-block {
            width: '.($styles['signature-block']['width'] ?? '30%').';
            text-align: '.($styles['signature-block']['text-align'] ?? 'center').';
            font-size: '.($styles['signature-block']['font-size'] ?? '9pt').';
        }

        .signature-line {
            border-top: '.($styles['signature-line']['border-top'] ?? '1px solid #000').';
            margin-bottom: '.($styles['signature-line']['margin-bottom'] ?? '5px').';
            height: '.($styles['signature-line']['height'] ?? '1px').';
        }

        .signature-title {
            font-size: '.($styles['signature-title']['font-size'] ?? '6pt').';
        }
    ';
    }
}
