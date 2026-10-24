<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Student Card Print - FARGO DTC4500</title>

    <style>
        /* ==============================
           FARGO DTC4500 PRINT OPTIMIZED
           Card Size: CR80 (85.6mm x 53.98mm)
           Resolution Target: 300 DPI
           ============================== */

        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap');

        @page {
            size: 85.6mm 53.98mm;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Cairo', "Arial", "Tahoma", sans-serif;
            direction: rtl;
            background-color: #f4f6f9; /* لمعاينة الشاشة فقط */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* ===== Card Container ===== */
        .id-card {
            width: 85.6mm;
            height: 53.98mm;
            position: relative;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); /* لمعاينة الشاشة فقط */
        }

        /* ===== Background Shapes ===== */
        .bg-shape-1 {
            position: absolute;
            top: -15mm;
            right: -10mm;
            width: 45mm;
            height: 45mm;
            background: rgba(30, 58, 138, 0.04);
            border-radius: 50%;
            z-index: 1;
        }

        .bg-shape-2 {
            position: absolute;
            bottom: -5mm;
            left: -10mm;
            width: 35mm;
            height: 35mm;
            background: rgba(200, 169, 81, 0.08);
            border-radius: 50%;
            z-index: 1;
        }

        /* ===== Header ===== */
        .header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 10.5mm;
            background: linear-gradient(90deg, #1e3a8a, #2563eb);
            border-bottom: 0.6mm solid #C8A951;
            color: #ffffff;
            text-align: center;
            padding-top: 1mm;
            z-index: 2;
        }

        .header-title {
            font-size: 9.5pt;
            font-weight: 700;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .header-subtitle {
            font-size: 6pt;
            color: #e2e8f0;
            margin-top: 0.2mm;
            letter-spacing: 0.3px;
        }

        /* ===== Content Layout ===== */
        .content {
            position: absolute;
            top: 11mm;
            right: 0;
            left: 0;
            bottom: 3mm;
            padding: 2mm 3mm;
            z-index: 2;
        }

        /* ===== Photo ===== */
        .photo {
            position: absolute;
            top: 2.5mm;
            right: 3mm;
            width: 22mm;
            height: 28mm;
            background: #f8fafc;
            border: 0.4mm solid #C8A951;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* ===== Text Area ===== */
        .info {
            position: absolute;
            top: 2.5mm;
            right: 28mm;
            left: 2mm;
        }

        .field-group {
            margin-bottom: 1.2mm;
        }

        .label {
            font-size: 5.5pt;
            color: #64748b;
            line-height: 1;
            margin-bottom: 0.5mm;
        }

        .value {
            font-size: 7.5pt;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .value-primary {
            color: #1e3a8a;
            font-size: 8pt;
            letter-spacing: 0.5px;
        }

        /* ===== Expiry ===== */
        .expiry {
            position: absolute;
            top: 21mm;
            right: 28mm;
        }

        .expiry-date {
            font-size: 7pt;
            font-weight: 700;
            color: #b91c1c;
            background: rgba(185, 28, 28, 0.08);
            padding: 0.5mm 1.5mm;
            border-radius: 2px;
            display: inline-block;
        }

        /* ===== Barcode ===== */
        .barcode-container {
            position: absolute;
            bottom: 0;
            left: 2mm;
            width: 45mm;
            text-align: left;
        }

        .barcode {
            font-family: "Libre Barcode 128", monospace; /* تأكد من تضمين الخط إذا كنت تستخدمه فعلياً */
            font-size: 9pt;
            line-height: 1;
            color: #0f172a;
        }

        /* ===== Footer Line ===== */
        .footer-line {
            position: absolute;
            bottom: 0;
            right: 0;
            left: 0;
            height: 2.5mm;
            background: #1e3a8a;
            z-index: 2;
        }

        /* ===== Print Rules ===== */
        @media print {
            body {
                background: none;
                align-items: flex-start;
                justify-content: flex-start;
            }
            .id-card {
                box-shadow: none;
                border: none;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="id-card">
        
        <!-- Background Decorations -->
        <div class="bg-shape-1"></div>
        <div class="bg-shape-2"></div>

        <!-- Header -->
        <div class="header">
            <div class="header-title">
                {{ __('card.card.title') }}
            </div>
            <div class="header-subtitle">
                {{ __('card.card.authorized') }}
            </div>
        </div>

        <!-- Content -->
        <div class="content">

          <!-- Photo -->
        <div class="photo">
            @php
                $photoSrc = $record->student?->photoDataUri() ?? \App\Models\Student::defaultPhotoDataUri();
            @endphp
            
            <img src="{{ $photoSrc }}" alt="Student Photo">
        </div>

            <!-- Info -->
            <div class="info">
                
                <div class="field-group">
                    <div class="label">{{ __('card.card.fields.name') }}</div>
                    <div class="value">{{ $record->student->name_ar }}</div>
                </div>

                <div class="field-group">
                    <div class="label">{{ __('card.card.fields.university_no') }}</div>
                    <div class="value value-primary" dir="ltr" style="text-align: right;">{{ $record->university_no }}</div>
                </div>

                <div class="field-group">
                    <div class="label">{{ __('card.card.fields.faculty') }}</div>
                    <div class="value">{{ $record->student->faculty->name ?? 'N/A' }}</div>
                </div>

            </div>

            <!-- Expiry -->
            <div class="expiry">
                <div class="label">{{ __('card.card.fields.expires') }}</div>
                <div class="expiry-date" dir="ltr">{{ $record->expiry_date ?? 'N/A' }}</div>
            </div>

            <!-- Barcode -->
            <div class="barcode-container">
                <div class="label">{{ __('card.card.fields.card_no') }}</div>
                <div class="barcode">
                    *{{ $record->card_number ?? $record->university_no }}*
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="footer-line"></div>

    </div>

</body>
</html>