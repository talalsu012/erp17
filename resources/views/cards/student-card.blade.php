<!DOCTYPE html>
<html dir="rtl">
<head>
    <title>student Card - {{ $student->student->name_ar }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&display=swap" rel="stylesheet">
    <style>
        .card {
            width: 85mm;
            height: 54mm;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 5mm;
            font-family: Arial, sans-serif;
            position: relative;
            background: linear-gradient(to bottom right, #f8f9fa, #e9ecef);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 2mm;
            border-bottom: 1px solid #ccc;
            padding-bottom: 2mm;
        }
        .header h2 {
            margin: 0;
            font-size: 14pt;
        }
        .header p {
            margin: 0;
            font-size: 8pt;
            color: #666;
        }
        .content {
            display: flex;
        }
        .photo {
            width: 25mm;
            height: 30mm;
            border: 1px solid #ddd;
            margin-right: 3mm;
        }
        .photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .details {
            flex: 1;
            font-size: 8pt;
        }
        .detail-row {
            margin-bottom: 1mm;
            margin-right: 3mm;
            display: flex;
        }
        .detail-label {
            font-weight: bold;
            width: 15mm;
        }
        .footer {
            position: absolute;
            bottom: 5mm;
            left: 5mm;
            right: 5mm;
            font-size: 6pt;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 1mm;
        }
        .barcode {
            text-align: center;
            margin-top: 2mm;
            font-family: 'Libre Barcode 128', cursive;
            font-size: 16pt;
        }
    </style>
</head>
<!-- <script>
    window.onload = function() {
        setTimeout(function() {
            window.print();
            window.close();
        }, 500);
    };
</script> -->
<body>
    <div class="card">
        <div class="header">
            <h2>{{ __('card.card.title') }}</h2>
            <p>{{ __('card.card.authorized') }}</p>
        </div>

        <div class="content">
            <div class="photo">
                <img src="{{ $student->student->photoUrl() }}" alt="Photo">
            </div>

            <div class="details">
                <div class="detail-row">
                    <div class="detail-label">{{ __('card.card.fields.name') }}</div>
                    <div>{{ $student->student->name_ar }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">{{ __('card.card.fields.university_no') }} :</div>
                    <div>{{ $student->university_no  }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">{{ __('card.card.fields.faculty') }}:</div>
                    <div>{{ $student->student->faculty->name }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">{{ __('card.card.fields.address') }}:</div>
                    <div>{{ $student->student->mobile }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">{{ __('card.card.fields.hire_date') }}:</div>
                    <div>{{ $student->issue_date ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">{{ __('card.card.fields.expires') }}:</div>
                    <div>{{ $student->expiry_date ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">{{ __('card.card.fields.card_no') }}:</div>
                    <div>{{ $student->card_number ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <div class="barcode">
            *{{ $student->university_no }}*
        </div>

        <div class="footer">
        {{ __('card.card.footer') }}
        </div>
    </div>


</body>
</html>
