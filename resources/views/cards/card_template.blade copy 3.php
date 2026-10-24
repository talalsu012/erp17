<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <title>بطاقة الطالب - أكاديمية السودان</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@300;400;600;700;800&family=Cairo:wght@300;400;600;700;900&display=swap');

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
      --navy: #0A1B3D;
      --navy-mid: #122652;
      --navy-lite: #1C3870;
      --gold: #C8A951;
      --gold-lite: #E2C878;
      --gold-dim: #8A7035;
      --white: #FFFFFF;
      --offwhite: #F4F6FA;
      --gray: #8898B4;
      --green: #14A05A;
      --red: #D93025;
    }

    body {
      font-family: 'Cairo', sans-serif;
      direction: rtl;
      background: #D0D8E8;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 16px;
      padding: 24px;
      min-height: 100vh;
    }

    /* ─── CARD SHELL ─── */
    .card-page {
      width: 85.6mm;
      height: 53.98mm;
      position: relative;
      overflow: hidden;
      border-radius: 3mm;
      box-shadow:
        0 4px 20px rgba(10, 27, 61, 0.25),
        0 1px 4px rgba(10, 27, 61, 0.15),
        inset 0 0 0 0.3mm rgba(200, 169, 81, 0.18);
    }

    /* ════════════════════════════════
         FRONT CARD
════════════════════════════════ */
    .card-front {
      width: 100%;
      height: 100%;
      background: var(--navy);
      position: relative;
      overflow: hidden;
    }

    /* Geometric background shapes */
    .card-front::before {
      content: '';
      position: absolute;
      top: -14mm;
      left: -8mm;
      width: 48mm;
      height: 48mm;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(28, 56, 112, 0.9) 0%, transparent 70%);
      z-index: 0;
    }

    .card-front::after {
      content: '';
      position: absolute;
      bottom: -10mm;
      right: -6mm;
      width: 36mm;
      height: 36mm;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(200, 169, 81, 0.12) 0%, transparent 70%);
      z-index: 0;
    }

    /* Diagonal gold accent strip */
    .front-diagonal {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      overflow: hidden;
    }

    .front-diagonal svg {
      width: 100%;
      height: 100%;
      position: absolute;
      top: 0;
      left: 0;
    }

    /* ─── HEADER BAND ─── */
    .front-header {
      position: relative;
      z-index: 2;
      width: 100%;
      height: 13mm;
      display: flex;
      align-items: center;
      padding: 0 2.5mm 0 2.5mm;
      gap: 2mm;
    }

    .header-logo-wrap {
      flex-shrink: 0;
      width: 9mm;
      height: 9mm;
      border-radius: 1.5mm;
      background: rgba(200, 169, 81, 0.12);
      border: 0.35mm solid rgba(200, 169, 81, 0.35);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .header-logo-wrap svg {
      width: 6mm;
      height: 6mm;
    }

    .header-text {
      flex: 1;
    }

    .header-title {
      font-family: 'Noto Kufi Arabic', sans-serif;
      font-size: 7.8pt;
      font-weight: 800;
      color: var(--white);
      line-height: 1.2;
      letter-spacing: 0.1px;
    }

    .header-sub {
      font-size: 4.2pt;
      font-weight: 300;
      color: rgba(255, 255, 255, 0.55);
      letter-spacing: 0.3px;
      margin-top: 0.4mm;
      direction: ltr;
      text-align: right;
    }

    /* Gold divider line */
    .gold-line {
      width: 100%;
      height: 0.45mm;
      background: linear-gradient(90deg, transparent 0%, var(--gold) 30%, var(--gold-lite) 60%, transparent 100%);
      position: relative;
      z-index: 2;
    }

    /* Badge strip */
    .badge-strip {
      position: relative;
      z-index: 2;
      width: 100%;
      height: 4mm;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(90deg,
          transparent 0%,
          rgba(200, 169, 81, 0.07) 20%,
          rgba(200, 169, 81, 0.13) 50%,
          rgba(200, 169, 81, 0.07) 80%,
          transparent 100%);
    }

    .badge-text {
      font-family: 'Noto Kufi Arabic', sans-serif;
      font-size: 5pt;
      font-weight: 700;
      color: var(--gold);
      letter-spacing: 1.2px;
      text-transform: uppercase;
    }

    /* ─── BODY ─── */
    .front-body {
      position: absolute;
      z-index: 2;
      top: 18mm;
      right: 2mm;
      left: 2mm;
      bottom: 5mm;
      display: flex;
      gap: 3mm;
    }

    /* Photo */
    .photo-col {
      flex-shrink: 0;
    }

    .photo-frame {
      width: 19mm;
      height: 24mm;
      position: relative;
    }

    .photo-frame::before {
      content: '';
      position: absolute;
      inset: -0.5mm;
      border-radius: 2mm;
      background: linear-gradient(135deg, var(--gold) 0%, var(--gold-lite) 50%, var(--gold-dim) 100%);
      z-index: 0;
    }

    .photo-inner {
      position: absolute;
      inset: 0.35mm;
      border-radius: 1.6mm;
      overflow: hidden;
      background: var(--navy-lite);
      z-index: 1;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .photo-inner img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .photo-inner svg {
      width: 60%;
      height: 60%;
      opacity: 0.3;
    }

    .photo-id-chip {
      position: absolute;
      bottom: -1mm;
      left: 50%;
      transform: translateX(-50%);
      z-index: 3;
      background: var(--gold);
      border-radius: 1mm;
      padding: 0.3mm 2mm;
      font-size: 4pt;
      font-weight: 700;
      color: var(--navy);
      white-space: nowrap;
      letter-spacing: 0.5px;
      direction: ltr;
    }

    /* Info */
    .info-col {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 1.2mm;
      padding-top: 0.5mm;
      overflow: hidden;
    }

    .field-row {
      display: flex;
      flex-direction: column;
    }

    .field-lbl {
      font-size: 4pt;
      font-weight: 400;
      color: var(--gray);
      line-height: 1;
      margin-bottom: 0.4mm;
    }

    .field-val {
      font-family: 'Noto Kufi Arabic', sans-serif;
      font-size: 6.5pt;
      font-weight: 700;
      color: var(--white);
      line-height: 1.25;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .field-val.gold-val {
      color: var(--gold-lite);
      font-size: 7.5pt;
      letter-spacing: 0.8px;
      direction: ltr;
      text-align: right;
    }

    .field-val.small {
      font-size: 5.2pt;
    }

    .field-val.active-val {
      color: #2ECC71;
      font-size: 5.5pt;
      display: flex;
      align-items: center;
      gap: 0.8mm;
    }

    .status-dot {
      display: inline-block;
      width: 1.5mm;
      height: 1.5mm;
      border-radius: 50%;
      background: #2ECC71;
      box-shadow: 0 0 2mm rgba(46, 204, 113, 0.6);
      flex-shrink: 0;
    }

    .two-col {
      display: flex;
      gap: 4mm;
    }

    /* Expiry footer inside body */
    .expiry-row {
      position: absolute;
      z-index: 2;
      bottom: 6mm;
      left: 2mm;
    }

    .expiry-lbl {
      font-size: 3.8pt;
      color: var(--gray);
    }

    .expiry-val {
      font-family: 'Noto Kufi Arabic', sans-serif;
      font-size: 5.5pt;
      font-weight: 700;
      color: #FF6B6B;
      direction: ltr;
    }

    /* ─── FOOTER ─── */
    .front-footer {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 5mm;
      z-index: 3;
      background: linear-gradient(90deg, rgba(200, 169, 81, 0.18) 0%, rgba(200, 169, 81, 0.08) 50%, rgba(200, 169, 81, 0.18) 100%);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 3mm;
      border-top: 0.3mm solid rgba(200, 169, 81, 0.25);
    }

    .footer-txt {
      font-size: 4pt;
      color: rgba(255, 255, 255, 0.4);
      letter-spacing: 0.3px;
    }

    .footer-dots {
      display: flex;
      gap: 0.7mm;
    }

    .footer-dots span {
      display: block;
      width: 0.9mm;
      height: 0.9mm;
      border-radius: 50%;
      background: rgba(200, 169, 81, 0.35);
    }

    /* ════════════════════════════════
         BACK CARD
════════════════════════════════ */
    .card-back {
      width: 100%;
      height: 100%;
      background: var(--navy);
      position: relative;
      overflow: hidden;
    }

    /* Subtle grid texture */
    .back-grid {
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(200, 169, 81, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(200, 169, 81, 0.04) 1px, transparent 1px);
      background-size: 5mm 5mm;
      z-index: 0;
    }

    /* Large faint circle */
    .back-circle {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 44mm;
      height: 44mm;
      border-radius: 50%;
      border: 0.3mm solid rgba(200, 169, 81, 0.08);
      z-index: 0;
    }

    .back-circle-2 {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 54mm;
      height: 54mm;
      border-radius: 50%;
      border: 0.3mm solid rgba(200, 169, 81, 0.04);
      z-index: 0;
    }

    /* Back header */
    .back-header {
      position: relative;
      z-index: 2;
      width: 100%;
      height: 10mm;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      padding-top: 1mm;
    }

    .back-header-title {
      font-family: 'Noto Kufi Arabic', sans-serif;
      font-size: 7pt;
      font-weight: 700;
      color: var(--white);
      letter-spacing: 0.2px;
    }

    .back-header-sub {
      font-size: 3.8pt;
      color: rgba(255, 255, 255, 0.4);
      direction: ltr;
      margin-top: 0.3mm;
    }

    /* Back body: QR + info side by side */
    .back-body {
      position: absolute;
      z-index: 2;
      top: 11mm;
      left: 2mm;
      right: 2mm;
      bottom: 7mm;
      display: flex;
      align-items: center;
      gap: 3mm;
    }

    /* QR */
    .qr-wrap {
      flex-shrink: 0;
      width: 22mm;
      height: 22mm;
      position: relative;
    }

    .qr-border {
      position: absolute;
      inset: -0.5mm;
      border-radius: 2mm;
      background: linear-gradient(135deg, var(--gold), var(--gold-lite), var(--gold-dim));
      z-index: 0;
    }

    .qr-inner {
      position: absolute;
      inset: 0.35mm;
      border-radius: 1.6mm;
      background: #fff;
      z-index: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .qr-inner canvas,
    .qr-inner img {
      width: 100%;
      height: 100%;
    }

    /* Corner marks on QR */
    .qr-corner {
      position: absolute;
      width: 3mm;
      height: 3mm;
      z-index: 2;
    }

    .qr-corner.tl {
      top: 0.8mm;
      right: 0.8mm;
      border-top: 0.5mm solid var(--gold);
      border-right: 0.5mm solid var(--gold);
      border-radius: 0 0.5mm 0 0;
    }

    .qr-corner.tr {
      top: 0.8mm;
      left: 0.8mm;
      border-top: 0.5mm solid var(--gold);
      border-left: 0.5mm solid var(--gold);
      border-radius: 0.5mm 0 0 0;
    }

    .qr-corner.bl {
      bottom: 0.8mm;
      right: 0.8mm;
      border-bottom: 0.5mm solid var(--gold);
      border-right: 0.5mm solid var(--gold);
      border-radius: 0 0 0 0.5mm;
    }

    .qr-corner.br {
      bottom: 0.8mm;
      left: 0.8mm;
      border-bottom: 0.5mm solid var(--gold);
      border-left: 0.5mm solid var(--gold);
      border-radius: 0 0 0.5mm 0;
    }

    /* Back info side */
    .back-info {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 1.5mm;
    }

    .back-notice-box {
      background: rgba(200, 169, 81, 0.08);
      border: 0.3mm solid rgba(200, 169, 81, 0.2);
      border-radius: 1.2mm;
      padding: 1.2mm 2mm;
      text-align: center;
    }

    .back-notice-txt {
      font-family: 'Noto Kufi Arabic', sans-serif;
      font-size: 5.5pt;
      font-weight: 700;
      color: var(--gold-lite);
      line-height: 1.45;
    }

    .back-card-no {
      text-align: center;
    }

    .back-no-lbl {
      font-size: 3.8pt;
      color: var(--gray);
    }

    .back-no-val {
      font-size: 5.8pt;
      font-weight: 700;
      color: var(--white);
      direction: ltr;
      letter-spacing: 0.8px;
      margin-top: 0.3mm;
    }

    .back-verify {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 1mm;
    }

    .verify-dot {
      width: 1.2mm;
      height: 1.2mm;
      border-radius: 50%;
      background: var(--green);
      box-shadow: 0 0 1.5mm rgba(20, 160, 90, 0.7);
    }

    .verify-txt {
      font-size: 4pt;
      color: rgba(255, 255, 255, 0.45);
    }

    /* Back footer */
    .back-footer {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 6.5mm;
      z-index: 3;
      background: linear-gradient(135deg, rgba(200, 169, 81, 0.15) 0%, rgba(200, 169, 81, 0.06) 50%, rgba(200, 169, 81, 0.15) 100%);
      border-top: 0.3mm solid rgba(200, 169, 81, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
    }

    .back-footer-title {
      font-family: 'Noto Kufi Arabic', sans-serif;
      font-size: 5pt;
      font-weight: 700;
      color: rgba(255, 255, 255, 0.75);
    }

    .back-footer-sub {
      font-size: 3.5pt;
      color: rgba(255, 255, 255, 0.3);
      direction: ltr;
      margin-top: 0.2mm;
    }

    /* ─── PRINT ─── */
    @media print {
      body {
        background: none;
        padding: 0;
        gap: 0;
      }

      .card-page {
        box-shadow: none;
        page-break-after: always;
        border-radius: 0;
      }
    }
  </style>
</head>

<body>

  <!-- =================== FRONT =================== -->
  <div class="card-page">
    <div class="card-front">

      <!-- Decorative diagonal SVG -->
      <div class="front-diagonal">
        <svg viewBox="0 0 323 204" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
          <!-- diagonal gold stripe -->
          <polygon points="0,0 60,0 0,30" fill="rgba(200,169,81,0.07)" />
          <polygon points="323,204 260,204 323,170" fill="rgba(200,169,81,0.06)" />
          <!-- subtle line texture -->
          <line x1="280" y1="0" x2="323" y2="60" stroke="rgba(200,169,81,0.08)" stroke-width="0.5" />
          <line x1="295" y1="0" x2="323" y2="40" stroke="rgba(200,169,81,0.06)" stroke-width="0.5" />
          <!-- bottom arc -->
          <path d="M0 180 Q160 145 323 175" stroke="rgba(200,169,81,0.06)" stroke-width="0.4" fill="none" />
        </svg>
      </div>

      <!-- Header -->
      <div class="front-header">
        <div class="header-logo-wrap">
          <!-- Custom banking/scales SVG icon -->
          <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="14.5" y="3" width="3" height="20" rx="1.5" fill="#C8A951" />
            <rect x="4" y="22" width="24" height="2.5" rx="1.25" fill="#C8A951" />
            <path d="M8 12 L4 20 L12 20 Z" fill="rgba(200,169,81,0.6)" />
            <path d="M24 12 L20 20 L28 20 Z" fill="rgba(200,169,81,0.6)" />
            <line x1="16" y1="5" x2="8" y2="12" stroke="#C8A951" stroke-width="1.5" stroke-linecap="round" />
            <line x1="16" y1="5" x2="24" y2="12" stroke="#C8A951" stroke-width="1.5" stroke-linecap="round" />
          </svg>
        </div>
        <div class="header-text">
          <div class="header-title">أكاديمية السودان للعلوم المصرفية والمالية</div>
          <div class="header-sub">Sudan Academy for Banking &amp; Financial Sciences</div>
        </div>
      </div>

      <!-- Gold divider -->
      <div class="gold-line"></div>

      <!-- Badge -->
      <div class="badge-strip">
        <div class="badge-text">بطاقة هوية طالب &nbsp;|&nbsp; STUDENT ID CARD</div>
      </div>

      <!-- Body -->
      <div class="front-body">

        <div class="photo-col">
          <div class="photo-frame">
            <div class="photo-inner">
              @if($data['avatar'] && !str_contains($data['avatar'], 'default-avatar'))
                <img src="{{ $data['avatar'] }}" style="width: 100%; height: 100%; object-fit: cover; display: block;"
                  alt="Student Photo">
              @else
                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="50" cy="36" r="22" fill="rgba(200,169,81,0.25)" />
                  <path d="M10 100 C10 68 90 68 90 100 Z" fill="rgba(200,169,81,0.2)" />
                </svg>
              @endif
            </div>
            <div class="photo-id-chip">{{ $data['university_no'] }}</div>
          </div>
        </div>

        <!-- Info -->
        <div class="info-col">
          <div class="field-row">
            <div class="field-lbl">الاسم الكامل</div>
            <div class="field-val">فاطمة عثمان حسن محمود</div>
          </div>

          <div class="field-row">
            <div class="field-lbl">الرقم الجامعي</div>
            <div class="field-val gold-val">6923415</div>
          </div>

          <div class="field-row">
            <div class="field-lbl">البرنامج</div>
            <div class="field-val small">البرنامج المحاسبة والتمويل</div>
          </div>

          <div class="two-col">
            <div class="field-row">
              <div class="field-lbl">المستوى</div>
              <div class="field-val small">الرابع</div>
            </div>
            <div class="field-row">
              <div class="field-lbl">الحالة</div>
              <div class="field-val active-val">
                <span class="status-dot"></span>نشط
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Expiry -->
      <div class="expiry-row">
        <div class="expiry-lbl">صالحة حتى</div>
        <div class="expiry-val">2026-06-30</div>
      </div>

      <!-- Footer -->
      <div class="front-footer">
        <span class="footer-txt">أمانة الشؤون العلمية</span>
        <div class="footer-dots">
          <span></span><span></span><span></span><span></span><span></span>
        </div>
        <span class="footer-txt">Academic Affairs</span>
      </div>

    </div>
  </div>


  <!-- =================== BACK =================== -->
  <div class="card-page">
    <div class="card-back">

      <div class="back-grid"></div>
      <div class="back-circle"></div>
      <div class="back-circle-2"></div>

      <!-- Header -->
      <div class="back-header">
        <div class="gold-line" style="position:absolute;top:0;left:0;right:0;"></div>
        <div class="back-header-title">أكاديمية السودان للعلوم المصرفية والمالية</div>
        <div class="back-header-sub">Sudan Academy for Banking &amp; Financial Sciences</div>
      </div>
      <div class="gold-line"></div>

      <!-- Body -->
      <div class="back-body">

        <!-- QR Code -->
        <div class="qr-wrap">
          <div class="qr-border"></div>
          <div class="qr-inner">
            <img src="data:image/svg+xml;base64,{{ $qrCode }}">
          </div>
        </div>

        <!-- Back Info -->
        <div class="back-info">
          <div class="back-notice-box">
            <div class="back-notice-txt">يجب إبراز هذه البطاقة<br>عند الدخول للحرم الجامعي</div>
          </div>

          <div class="back-card-no">
            <div class="back-no-lbl">رقم البطاقة</div>
            <div class="back-no-val">UNI-69234-EFGH-5678</div>
          </div>

          <div class="back-verify">
            <div class="verify-dot"></div>
            <span class="verify-txt">Verified &amp; Authenticated</span>
          </div>
        </div>

      </div>

      <!-- Back Footer -->
      <div class="back-footer">
        <div class="back-footer-title">أمانة الشؤون العلمية</div>
        <div class="back-footer-sub">Academic Affairs Office &nbsp;|&nbsp; www.sudanacademy.edu.sd</div>
      </div>

    </div>
  </div>

</body>

</html>