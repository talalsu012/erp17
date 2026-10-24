<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <title>بطاقة الطالب - جامعة الخرطوم</title>
  <style>
  
    @page {
      size: 85.6mm 53.98mm;
      margin: 0;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      /* ألوان شعار جامعة الخرطوم المستخرجة */
      --uofk-blue: #4059AD; /* الأزرق الموجود في الشعار */
      --uofk-black: #000000; /* الأسود في قاعدة الشعار */
      --uofk-orange: #D97B51; /* اللون البرتقالي للمبنى في الشعار (للتأكيد) */
      --text-main: #1a1a1a;
      --white: #FFFFFF;
      --border-light: #e0e0e0;
    }

    body {
      font-family: 'Cairo', sans-serif;
      direction: rtl;
      background: #f4f4f4;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px;
    }

    .card-page {
      width: 85.6mm;
      height: 53.98mm;
      position: relative;
      overflow: hidden;
      background: var(--white);
      border: 0.1mm solid var(--border-light);
      border-radius: 2mm;
    }

    /* ─── FRONT CARD ─── */
    .card-front {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    /* Header */
    .front-header {
      height: 15mm;
      display: flex;
      align-items: center;
      padding: 0 4mm;
      gap: 3mm;
      border-bottom: 0.8mm solid var(--uofk-blue);
    }

    .header-logo-wrap img {
      width: 11mm;
      height: 11mm;
      object-fit: contain;
    }

    .header-text {
      flex: 1;
    }

    .header-title {
      font-family: 'Noto Kufi Arabic', sans-serif;
      font-size: 9pt;
      font-weight: 800;
      color: var(--uofk-blue);
      line-height: 1.1;
    }

    .header-sub {
      font-size: 5.5pt;
      font-weight: 600;
      color: var(--uofk-black);
      direction: ltr;
      text-align: right;
      opacity: 0.8;
    }

    /* Badge Strip - خلفية فاتحة جداً لتوفير الحبر */
    .badge-strip {
      background: #f9f9f9;
      height: 4mm;
      display: flex;
      align-items: center;
      justify-content: center;
      border-bottom: 0.1mm solid var(--border-light);
    }

    .badge-text {
      font-size: 5pt;
      font-weight: 700;
      color: var(--uofk-black);
      letter-spacing: 1.5px;
    }

    /* Body */
    .front-body {
      flex: 1;
      display: flex;
      padding: 2.5mm 4mm;
      gap: 5mm;
    }

    /* Photo Frame */
    .photo-frame {
      width: 19mm;
      height: 24mm;
      border: 0.4mm solid var(--uofk-blue);
      padding: 0.4mm;
      position: relative;
    }

    .photo-inner {
      width: 100%;
      height: 100%;
      background: #fcfcfc;
      overflow: hidden;
    }

    .photo-inner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .photo-id-chip {
      position: absolute;
      bottom: -1.5mm;
      left: 50%;
      transform: translateX(-50%);
      background: var(--uofk-black);
      color: white;
      font-size: 4.5pt;
      font-weight: bold;
      padding: 0.1mm 2.5mm;
      border-radius: 0.3mm;
    }

    /* Info Column */
    .info-col {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 1.2mm;
    }

    .field-row {
      display: flex;
      flex-direction: column;
    }

    .field-lbl {
      font-size: 4.5pt;
      color: #666;
      font-weight: 600;
    }

    .field-val {
      font-size: 7.5pt;
      font-weight: 700;
      color: var(--uofk-black);
    }

    .field-val.blue-val {
      color: var(--uofk-blue);
      font-size: 8.5pt;
    }

    .expiry-box {
      margin-top: auto;
      font-size: 5pt;
      color: var(--uofk-blue);
      border-top: 0.1mm solid var(--border-light);
      padding-top: 1mm;
    }

    /* ─── BACK CARD ─── */
    .card-back {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      padding: 4mm;
    }

    .back-header {
      text-align: center;
      border-bottom: 0.4mm solid var(--uofk-blue);
      padding-bottom: 1.5mm;
      margin-bottom: 3mm;
    }

    .back-body {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 6mm;
    }

    .qr-inner {
      width: 20mm;
      height: 20mm;
      border: 0.1mm solid var(--uofk-blue);
      padding: 0.5mm;
    }

    .qr-inner img {
      width: 100%;
      height: 100%;
    }

    .back-notice {
      flex: 1;
      font-size: 5pt;
      line-height: 1.6;
      color: var(--uofk-black);
    }

    .back-footer {
      margin-top: auto;
      text-align: center;
      font-size: 4.5pt;
      color: #777;
      border-top: 0.1mm solid var(--border-light);
      padding-top: 1.5mm;
    }

    @media print {
      body { background: none; padding: 0; }
      .card-page { border: 0.1mm solid #eee; box-shadow: none; page-break-after: always; }
    }
  </style>
</head>

<body>

  <div class="card-page">
    <div class="card-front">
      <div class="front-header">
        <div class="header-logo-wrap">
            <img src="https://upload.wikimedia.org/wikipedia/en/9/97/Logo_of_the_University_of_Khartoum.jpeg" alt="UofK Logo">
        </div>
        <div class="header-text">
          <div class="header-title">جامعة الخرطوم</div>
          <div class="header-sub">University of Khartoum</div>
        </div>
      </div>

      <div class="badge-strip">
        <div class="badge-text">بطاقة هوية طالب | STUDENT ID CARD</div>
      </div>

      <div class="front-body">
        <div class="photo-col">
          <div class="photo-frame">
            <div class="photo-inner">
                @if($data['avatar'] && !str_contains($data['avatar'], 'default-avatar'))
                    <img src="{{ $data['avatar'] }}" alt="Student">
                @else
                    <svg viewBox="0 0 100 100" fill="#f0f0f0"><circle cx="50" cy="35" r="20"/><path d="M20 90c0-20 20-25 30-25s30 5 30 25H20z"/></svg>
                @endif
            </div>
            <div class="photo-id-chip">{{ $data['university_no'] }}</div>
          </div>
        </div>

        <div class="info-col">
          <div class="field-row">
            <div class="field-lbl">الاسم الكامل</div>
            <div class="field-val">{{ $data['name_ar'] }}</div>
          </div>
          <div class="field-row">
            <div class="field-lbl">الكلية / البرنامج</div>
            <div class="field-val" style="font-size: 6.5pt;">{{ $data['faculty'] }}</div>
          </div>
         
          <div class="expiry-box">
            <b>تاريخ الانتهاء:</b> {{ $data['expiry_date'] }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card-page" style="margin-top: 20px;">
    <div class="card-back">
      <div class="back-header">
        <div class="header-title" style="font-size: 7.5pt; color: var(--uofk-blue);">جامعة الخرطوم - أمانة الشؤون العلمية</div>
      </div>

      <div class="back-body">
        <div class="qr-inner">
          <img src="data:image/svg+xml;base64,{{ $qrCode }}">
        </div>
        <div class="back-notice">
          • هذه البطاقة هي وثيقة رسمية تثبت شخصية حاملها داخل الجامعة.<br>
          • لا يجوز كشط أو تعديل أي من البيانات الموضحة أعلاه.<br>
          • يرجى من يجدها إعادتها إلى أقرب مكتب أمن أو شؤون علمية.<br>
          • الجامعة غير مسؤولة عن سوء استخدام هذه البطاقة.
        </div>
      </div>

      <div class="back-footer">
        {{ $data['card_number'] }} | uofk.edu | Khartoum - Sudan
      </div>
    </div>
  </div>

</body>
</html>