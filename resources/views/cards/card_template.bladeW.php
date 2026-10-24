<!-- اريد المساعدة في اعادة تصميم بطاقة الطالب الخاصة بأكاديمية السودان للعلوم المصرفية والمالية. أريد أن تكون البطاقة ذات تصميم حديث وأنيق، مع التركيز على وضوح المعلومات وسهولة القراءة. يجب أن تحتوي البطاقة على صورة الطالب، اسمه الكامل، الرقم الجامعي، الكلية، المستوى، حالة التسجيل، وتاريخ انتهاء الصلاحية. بالإضافة إلى ذلك، أريد أن يكون هناك رمز QR على الوجه الخلفي للبطاقة يحتوي على معلومات الطالب عند مسحه. يرجى استخدام الألوان الرسمية للأكاديمية وتنسيق النصوص بشكل جيد لجعل البطاقة جذابة واحترافية.   -->
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>بطاقة الطالب - أكاديمية السودان</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;600;700;800&family=Tajawal:wght@400;500;700&display=swap');

@page {
  size: 85.6mm 53.98mm;
  margin: 0;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Tajawal', 'Arial', sans-serif;
  direction: rtl;
  background: #fff;
}

/* ============ CARD WRAPPER ============ */
.card-page {
  width: 85.6mm;
  height: 53.98mm;
  position: relative;
  overflow: hidden;
  background: #ffffff;
  page-break-after: always;
}

/* ============ FRONT CARD ============ */
/* --- Header --- */
.front-header {
  width: 100%;
  height: 11mm;
  background: linear-gradient(135deg, #0f2557 0%, #1a3a7a 50%, #0f2557 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  position: relative;
}
.front-header::after {
  content: '';
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 0.6mm;
  background: linear-gradient(90deg, transparent, #c9a84c, transparent);
}
.front-header-title {
  font-family: 'Noto Kufi Arabic', sans-serif;
  font-size: 8.5pt;
  font-weight: 700;
  color: #fff;
  line-height: 1.2;
}
.front-header-sub {
  font-size: 5pt;
  color: rgba(255,255,255,0.6);
}

/* --- Card Type Badge --- */
.card-type-badge {
  width: 100%;
  height: 4.5mm;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(90deg, transparent, rgba(201,168,76,0.08), transparent);
}
.card-type-badge span {
  font-family: 'Noto Kufi Arabic', sans-serif;
  font-size: 5.5pt;
  font-weight: 700;
  color: #c9a84c;
  letter-spacing: 0.5px;
}

/* --- Content Area --- */
.front-content {
  position: absolute;
  top: 16mm; right: 2mm; left: 2mm; bottom: 4.5mm;
}

/* --- Photo --- */
.student-photo {
  position: absolute;
  top: 0; right: 0;
  width: 20mm; height: 25mm;
  border-radius: 2mm;
  border: 0.4mm solid #c9a84c;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(15,37,87,0.15);
}
.student-photo img {
  width: 100%; height: 100%;
  object-fit: cover;
}

/* --- Info Fields --- */
.info-area {
  position: absolute;
  top: 0; right: 23mm; left: 0;
}
.field { margin-bottom: 1.5mm; }
.field-label {
  font-size: 4.5pt;
  color: #7a8599;
  line-height: 1;
  margin-bottom: 0.3mm;
}
.field-value {
  font-family: 'Noto Kufi Arabic', sans-serif;
  font-size: 7pt;
  font-weight: 700;
  color: #0f2557;
  line-height: 1.3;
}
.field-value.gold { color: #c9a84c; font-size: 7.5pt; letter-spacing: 0.5px; }
.field-value.faculty {
  font-size: 5.5pt;
  color: #1a1a2e;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.field-value.active { color: #16a34a; }
.field-value.inactive { color: #dc2626; }

.row-fields {
  display: flex;
  gap: 4mm;
}

/* --- Expiry --- */
.expiry-area {
  position: absolute;
  bottom: 0; right: 23mm;
}
.expiry-value {
  font-family: 'Noto Kufi Arabic', sans-serif;
  font-size: 6pt;
  font-weight: 700;
  color: #dc2626;
}

/* --- Front Footer --- */
.front-footer {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 4.5mm;
  background: linear-gradient(135deg, #0f2557 0%, #1a3a7a 50%, #0f2557 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}
.front-footer::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 0.4mm;
  background: linear-gradient(90deg, transparent, #c9a84c, transparent);
}
.front-footer span {
  font-size: 4pt;
  color: rgba(255,255,255,0.5);
}

/* ============ BACK CARD ============ */
.back-header {
  width: 100%;
  height: 9mm;
  background: linear-gradient(135deg, #2a2a2e 0%, #3d3d42 50%, #2a2a2e 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}
.back-header::after {
  content: '';
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 0.5mm;
  background: linear-gradient(90deg, transparent, #c9a84c, transparent);
}
.back-header-title {
  font-family: 'Noto Kufi Arabic', sans-serif;
  font-size: 7.5pt;
  font-weight: 700;
  color: #fff;
}

/* --- Back Content --- */
.back-content {
  position: absolute;
  top: 10mm; right: 3mm; left: 3mm; bottom: 9mm;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

/* QR code area */
.qr-area {
  width: 18mm; height: 18mm;
  border-radius: 3mm;
  border: 0.4mm solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 2mm;
  overflow: hidden;
}
.qr-area img {
  width: 100%; height: 100%;
  object-fit: contain;
}

.back-notice {
  font-family: 'Noto Kufi Arabic', sans-serif;
  font-size: 6.5pt;
  font-weight: 700;
  color: #0f2557;
  text-align: center;
  line-height: 1.4;
  margin-bottom: 2mm;
}

.card-no-label {
  font-size: 4.5pt;
  color: #7a8599;
  margin-bottom: 0.3mm;
  text-align: center;
}
.card-no-value {
  font-family: 'Noto Kufi Arabic', sans-serif;
  font-size: 6.5pt;
  font-weight: 700;
  color: #0f2557;
  direction: ltr;
  letter-spacing: 0.8px;
}

/* --- Back Footer --- */
.back-footer {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 6mm;
  background: linear-gradient(135deg, #0f2557 0%, #1a3a7a 50%, #0f2557 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.back-footer::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 0.4mm;
  background: linear-gradient(90deg, transparent, #c9a84c, transparent);
}
.back-footer-title {
  font-family: 'Noto Kufi Arabic', sans-serif;
  font-size: 5pt;
  font-weight: 700;
  color: #fff;
}
.back-footer-sub {
  font-size: 3.5pt;
  color: rgba(255,255,255,0.4);
  margin-top: 0.3mm;
}

/* ============ PRINT RULES ============ */
@media print {
  body { background: none; }
  .card-page { box-shadow: none; border: none; }
}
</style>
</head>
<body>

<!-- =================== FRONT FACE =================== -->
<div class="card-page">

  <!-- Header -->
  <div class="front-header">
    <div class="front-header-title">أكاديمية السودان للعلوم المصرفية والمالية</div>
    <div class="front-header-sub">Sudan Academy for Banking &amp; Financial Sciences</div>
  </div>

  <!-- Card Type -->
  <div class="card-type-badge"><span>بطاقة هوية طالب</span></div>

  <!-- Content -->
  <div class="front-content">

    <!-- Photo -->
    <div class="student-photo">
      <img src="" alt="صورة الطالب">
    </div>

    <!-- Info -->
    <div class="info-area">

      <div class="field">
        <div class="field-label">الاسم</div>
        <div class="field-value">فاطمة عثمان حسن محمود</div>
      </div>

      <div class="field">
        <div class="field-label">الرقم الجامعي</div>
        <div class="field-value gold">6923415</div>
      </div>

      <div class="field">
        <div class="field-label">الكلية</div>
        <div class="field-value faculty">كلية المحاسبة والتمويل</div>
      </div>

      <div class="row-fields">
        <div class="field">
          <div class="field-label">المستوى</div>
          <div class="field-value" style="font-size:5.5pt;color:#1a1a2e;">المستوى الرابع</div>
        </div>
        <div class="field">
          <div class="field-label">الحالة</div>
          <div class="field-value active" style="font-size:5.5pt;">نشط</div>
        </div>
      </div>

    </div>

    <!-- Expiry -->
    <div class="expiry-area">
      <div class="field-label">صالحة حتى</div>
      <div class="expiry-value">2026-06-30</div>
    </div>

  </div>

  <!-- Footer -->
  <div class="front-footer"><span>أمانة الشؤون العلمية</span></div>

</div>

<!-- =================== BACK FACE =================== -->
<div class="card-page">

  <!-- Header -->
  <div class="back-header">
    <div class="back-header-title">أكاديمية السودان للعلوم المصرفية والمالية</div>
  </div>

  <!-- Content -->
  <div class="back-content">

    <!-- QR Code - استبدل src بمسار QR الحقيقي -->
    <div class="qr-area">
     
    </div>

    <div class="back-notice">يجب إبراز هذه البطاقة عند الدخول للحرم الجامعي</div>

    <div class="card-no-label">رقم البطاقة</div>
    <div class="card-no-value">UNI-69234-EFGH-5678</div>

  </div>

  <!-- Footer -->
  <div class="back-footer">
    <div class="back-footer-title">أمانة الشؤون العلمية</div>
    <div class="back-footer-sub">Academic Affairs Office</div>
  </div>

</div>

</body>
</html>