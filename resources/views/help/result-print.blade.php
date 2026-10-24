<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>دليل طباعة النتيجة</title>
@include('help._style')
</head>
<body>
@verbatim
<div class="wrap">

<header class="hero">
    <h1>دليل طباعة النتيجة</h1>
    <p>كيف تعدّ كشف النتيجة وتطبعه: اختيار المجموعة والمواد والحقول، التصميمان، النتائج السابقة، الملخص، السجل الأكاديمي، والاعتماد.</p>
</header>

<nav class="toc">
    <a href="#form">1) نموذج الطباعة</a>
    <a href="#fields">2) الحقول والمواد</a>
    <a href="#buttons">3) أزرار الصفحة</a>
    <a href="#sheet">4) كشف النتيجة</a>
    <a href="#toolbar">5) شريط الأدوات</a>
    <a href="#colors">6) الألوان والرموز</a>
    <a href="#header">7) الترويسة والتواقيع</a>
    <a href="#summary">8) الملخص والسجل الأكاديمي</a>
    <a href="#final">9) الاعتماد النهائي</a>
    <a href="#errors">10) الرسائل</a>
    <a href="#tips">11) نصائح</a>
</nav>

<!-- 1 -->
<section id="form">
    <h2><span class="num">1</span> نموذج الطباعة</h2>
    <p>المسار: <b>النتائج ← النتائج</b>. الصورة تمثل النموذج، والأرقام الحمراء تشرح كل خانة.</p>

    <div class="mock">
        <div class="panel">
            <div class="grid">
                <div class="fld"><label>العام الدراسي *</label><div class="box">2026</div><span class="mark">1</span></div>
                <div class="fld"><label>الكلية *</label><div class="box">كلية علوم الحاسوب وتقانة المعلومات</div><span class="mark">2</span></div>
                <div class="fld"><label>الفصل *</label><div class="box">الفصل العاشر</div><span class="mark">3</span></div>
                <div class="fld"><label>قسم *</label><div class="box">تقانة المعلومات</div><span class="mark">4</span></div>
                <div class="fld"><label>تخصص</label><div class="box empty">اختر</div><span class="mark">5</span></div>
                <div class="fld"><label>الدور *</label><div class="box">الأساسي</div><span class="mark">6</span></div>
                <div class="fld full"><label>المواد / المقررات <span style="color:#7c3aed">☑ تحديد الكل</span> <span style="color:#6b7280">✖ إلغاء التحديد</span></label><div class="box">المشروع × &nbsp; إدارة المعرفة × &nbsp; مشروع التخرج × &nbsp; ...</div><span class="mark">7</span></div>
                <div class="fld"><label>التقدير</label><div class="box empty">اختر</div><span class="mark">8</span></div>
                <div class="fld"><label>طالب</label><div class="box empty">اختر</div><span class="mark">9</span></div>
            </div>
            <div style="margin-top:14px"><b>الحقول</b>
                <div style="display:flex;flex-wrap:wrap;gap:8px 22px;margin-top:8px">
                    <label>☑ م</label><label>☑ الرقم الجامعي</label><label>☑ الاسم عربي</label><label>☐ الاسم إنجليزي</label><label>☐ رقم الجلوس</label><label>☐ درجة</label><label>☑ التقدير</label><label>☐ نقاط</label><label>☑ معدل فصلي</label><label>☑ توصية</label><label>☐ ساعات</label><label>☐ مواد الرسوب</label><label>☐ ساعات الرسوب</label><label>☐ مجموع الدرجات</label>
                </div>
                <span class="mark" style="position:static;display:inline-flex;margin-top:8px">10</span>
            </div>
        </div>
    </div>

    <div class="legend">
        <div><span class="mark">1</span><span><b>العام الدراسي:</b> العام الذي تريد نتيجته.</span></div>
        <div><span class="mark">2</span><span><b>الكلية:</b> من كلياتك فقط.</span></div>
        <div><span class="mark">3</span><span><b>الفصل:</b> المستوى الدراسي المطلوب.</span></div>
        <div><span class="mark">4</span><span><b>القسم:</b> يعرض أقسام الكلية المختارة.</span></div>
        <div><span class="mark">5</span><span><b>التخصص (اختياري):</b> يقصر الكشف على طلاب التخصص، ويظهر في الترويسة.</span></div>
        <div><span class="mark">6</span><span><b>الدور:</b> الأساسي أو الملحق.</span></div>
        <div><span class="mark">7</span><span><b>المواد:</b> تظهر بعد الكلية والقسم والفصل. تحديد الكل وإلغاء التحديد في أعلى الخانة.</span></div>
        <div><span class="mark">8</span><span><b>التقدير:</b> خيار تصفية للمعلومة فقط حالياً، ولا يغيّر الكشف.</span></div>
        <div><span class="mark">9</span><span><b>طالب:</b> مطلوب لطباعة السجل الأكاديمي لطالب واحد.</span></div>
        <div><span class="mark">10</span><span><b>الحقول:</b> أعمدة الكشف المطبوع (القسم 2).</span></div>
    </div>

    <div class="note"><span class="ico">ℹ️</span><div>الخانات المعلّمة بنجمة (*) إلزامية. «التخصص» يُفرغ إن لم تحتجه: عندها يشمل الكشف كل طلاب القسم.</div></div>
</section>

<!-- 2 -->
<section id="fields">
    <h2><span class="num">2</span> الحقول والمواد</h2>
    <h3>الحقول المحددة افتراضياً</h3>
    <p>م، الرقم الجامعي، الاسم عربي، التقدير، توصية، معدل فصلي. غيّر ما تشاء، و«تحديد الكل» أعلى المربعات.</p>

    <table>
        <tr><th>الحقل</th><th>ماذا يظهر في الكشف</th></tr>
        <tr><td>م</td><td>الترقيم المتسلسل</td></tr>
        <tr><td>الرقم الجامعي / رقم الجلوس</td><td>رقم الطالب</td></tr>
        <tr><td>الاسم عربي / إنجليزي</td><td>اسم الطالب</td></tr>
        <tr><td>التقدير / الدرجة</td><td>تقدير كل مادة، والدرجة أسفله (يمكن اختيار أحدهما أو كليهما)</td></tr>
        <tr><td>النقاط</td><td>مجموع النقاط</td></tr>
        <tr><td>الساعات / ساعات الرسوب</td><td>الساعات المعتمدة وساعات المواد الراسبة</td></tr>
        <tr><td>مواد الرسوب</td><td>أسماء المواد التي يمتحنها الطالب في الملحق</td></tr>
        <tr><td>معدل فصلي</td><td>المعدل التراكمي للفصل</td></tr>
        <tr><td>توصية</td><td>قرار اللائحة (نجاح، ملحق، إعادة...)</td></tr>
        <tr><td>مجموع الدرجات</td><td>مجموع درجات المواد المعروضة</td></tr>
    </table>

    <div class="tip"><span class="ico">💡</span><div>للكشف الرسمي اكتفِ بـ: م، الرقم الجامعي، الاسم، التقدير، المعدل، التوصية. الحقول الكثيرة تضيّق أعمدة المواد.</div></div>
    <div class="warn"><span class="ico">⚠️</span><div>اختر المواد بعناية: كل مادة تضيف عموداً. المواد الكثيرة تصغّر الأعمدة وقد تتغير ظروف الطباعة.</div></div>
</section>

<!-- 3 -->
<section id="buttons">
    <h2><span class="num">3</span> أزرار الصفحة</h2>
    <p>تنقسم الأزرار في أسفل النموذج إلى مجموعتين:</p>
    <div class="cards">
        <div class="card"><h4>🖨 قسم «الطباعة»</h4>
            <table>
                <tr><td><b>طباعة تفاصيل النتيجة</b></td><td>الكشف الكامل (القسم 4). يتطلب العام الدراسي والكلية والفصل والقسم والدور والمواد والحقول.</td></tr>
                <tr><td><b>طباعة ملخص النتيجة</b></td><td>إحصائية المواد والتوصيات. يتطلب العام والكلية والفصل والقسم.</td></tr>
                <tr><td><b>طباعة السجل الأكاديمي لطالب</b></td><td>السجل الكامل لطالب واحد. يتطلب اختيار «طالب».</td></tr>
            </table>
        </div>
        <div class="card"><h4>🛡 قسم «الاعتماد والإجراءات»</h4>
            <table>
                <tr><td><b>اعتماد النتائج نهائياً</b></td><td>يقفل النتائج ويحوّلها للسجل الأكاديمي. يظهر للمعتمد المعيّن فقط.</td></tr>
                <tr><td><b>إعادة حساب التوصيات</b></td><td>يعيد احتساب قرارات الطلاب المطابقين للبحث (يعمل في الخلفية).</td></tr>
                <tr><td><b>تثبيت نتائج الطلاب المجمّدين</b></td><td>يسجّل للمجمّدين درجة 0 وتقدير «ت». يكتب في قاعدة البيانات.</td></tr>
            </table>
        </div>
    </div>
    <div class="danger"><span class="ico">⛔</span><div>أزرار «الاعتماد والإجراءات» <b>تكتب في قاعدة البيانات</b> وتطلب تأكيداً. أزرار الطباعة لا تكتب شيئاً.</div></div>
</section>

<!-- 4 -->
<section id="sheet">
    <h2><span class="num">4</span> كشف النتيجة المطبوع</h2>
    <p>يفتح الكشف في صفحة جديدة بها بطاقات إحصاء، وصفحات الجدول، ثم أقسام الإحصاء.</p>

    <div class="mock">
        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-bottom:12px">
            <div class="card" style="text-align:center"><small>إجمالي الطلاب</small><div style="font-size:22px;font-weight:700;color:#2563eb">101</div></div>
            <div class="card" style="text-align:center"><small>الجالسون</small><div style="font-size:22px;font-weight:700;color:#7c3aed">101</div></div>
            <div class="card" style="text-align:center"><small>الناجحون</small><div style="font-size:22px;font-weight:700;color:#16a34a">90</div></div>
            <div class="card" style="text-align:center"><small>الراسبون</small><div style="font-size:22px;font-weight:700;color:#dc2626">11</div></div>
            <div class="card" style="text-align:center"><small>أعلى معدل</small><div style="font-size:22px;font-weight:700;color:#7c3aed">4.00</div></div>
        </div>
        <div class="panel">
            <div style="background:#e0f2fe;border:1px solid #bae6fd;border-radius:10px;padding:10px;text-align:center"><small>بسم الله الرحمن الرحيم</small><div style="font-size:18px;font-weight:700">{{ config('university.name_ar') }} - كلية علوم الحاسوب</div><small>نتيجة الامتحانات الأساسي · الفصل العاشر · العام الدراسي 2026/2027</small></div>
            <div class="tbl" style="margin-top:10px">
                <div class="row head"><div>م</div><div>الرقم الجامعي</div><div>اسم الطالب</div><div>المادة 1</div><div>المعدل</div><div>القرار</div></div>
                <div class="row"><div>1</div><div>1165446022</div><div>اسم طالب</div><div>B</div><div>2.78</div><div>نجاح</div></div>
                <div class="row"><div>2</div><div>2104264622</div><div>اسم طالب</div><div style="background:#d1d5db">F</div><div>1.73</div><div>توصية افتراضية</div></div>
            </div>
        </div>
    </div>

    <p><b>الأقسام بعد الجدول:</b> دليل الألوان، إحصائيات التوصيات (جدول وشريط توزيع)، وإحصائيات المواد (ناجحون، راسبون، بديل، أعلى وأقل درجة، المعدل، نسبة النجاح).</p>

    <div class="steps">
        <div class="step"><div><b>راجع الكشف على الشاشة</b><span>البطاقات والأسماء والتقديرات والمواد.</span></div></div>
        <div class="step"><div><b>اضبط التصميم وعدد الطلاب في الصفحة</b><span>من الشريط العلوي (القسم 5).</span></div></div>
        <div class="step"><div><b>اضغط «طباعة التقرير»</b><span>الزر الأزرق أسفل الصفحة، ثم اختر الطابعة أو «حفظ كـ PDF».</span></div></div>
    </div>
    <div class="tip"><span class="ico">💡</span><div>في نافذة الطباعة: الاتجاه <b>أفقي (Landscape)</b>، والهوامش «افتراضي»، وفعّل «رسومات الخلفية» لظهور الترويسة الزرقاء الفاتحة.</div></div>
</section>

<!-- 5 -->
<section id="toolbar">
    <h2><span class="num">5</span> شريط الأدوات العلوي</h2>
    <table>
        <tr><th>العنصر</th><th>وظيفته</th></tr>
        <tr><td>رجوع</td><td>يعود لصفحة الطباعة بنفس اختياراتك لتعديلها</td></tr>
        <tr><td>ترويسة كاملة في كل صفحة</td><td>تصميم رسمي: «بسم الله»، شعارات، ترويسة وتواقيع في كل صفحة</td></tr>
        <tr><td>جدول متصل مع ملخص التقديرات</td><td>تصميم مدمج بترويسة مختصرة، مع صفحة ملخص تقديرات المواد في النهاية و4 تواقيع</td></tr>
        <tr><td>طلاب في الصفحة</td><td>عدد صفوف الصفحة (8 إلى 22). كلما زاد قلّ عدد الصفحات</td></tr>
        <tr><td>نتيجة حالية / سابقة</td><td>السابقة تُجلب من سجل الدرجات حتى لو نُقل الطالب</td></tr>
        <tr><td>النتائج السابقة للطلاب</td><td>يضيف أعمدة: العام السابق، المعدل السابق، القرار السابق</td></tr>
        <tr><td>الدرجات والتقديرات</td><td>إظهار الدرجة مع التقدير في كل خلية</td></tr>
        <tr><td>الدرجات فقط</td><td>إظهار الدرجات بدل التقديرات</td></tr>
        <tr><td>إحصائيات التوصيات / المواد</td><td>إظهار أو إخفاء الصفحتين (يُحفظ اختيارك)</td></tr>
    </table>
    <div class="note"><span class="ico">ℹ️</span><div>خيارات العرض تغيّر الشاشة <b>والطباعة معاً</b>: ما تراه هو ما يُطبع.</div></div>
</section>

<!-- 6 -->
<section id="colors">
    <h2><span class="num">6</span> الألوان والرموز</h2>
    <table class="center">
        <tr><th>الشكل</th><th>المعنى</th></tr>
        <tr><td style="background:#fff">خلية بيضاء بخط أسود</td><td>تقدير عادي (ناجح)</td></tr>
        <tr><td style="background:#d1d5db"><b>F</b> / رسوب / ت</td><td>رسوب أو غياب أو حالة غير مكتملة أو «ت» (تجميد)</td></tr>
        <tr><td>Ex1 · R1 (بجانب الاسم)</td><td>عدد مرات الامتحان من الخارج (Ex) وإعادة الفصل (R) سابقاً</td></tr>
        <tr><td>«توصية افتراضية»</td><td>درجات الطالب غير مكتملة</td></tr>
        <tr><td>B+ / C+</td><td>التقديرات الوسيطة (تُقلَب للعرض)</td></tr>
    </table>
</section>

<!-- 7 -->
<section id="header">
    <h2><span class="num">7</span> الترويسة والتواقيع: من أين تأتي؟</h2>
    <table>
        <tr><th>العنصر المطبوع</th><th>مصدره</th></tr>
        <tr><td>اسم الجامعة</td><td>ملف الإعدادات (<code>config/university.php</code>)</td></tr>
        <tr><td>اسم الكلية والقسم والفصل والتخصص</td><td>اختيارك في النموذج</td></tr>
        <tr><td>سطر أمانة/عنوان إضافي فوق عنوان الكشف</td><td>بيانات الكلية: <b>header_title</b> (يُطبع إن لم يكن فارغاً)</td></tr>
        <tr><td>نص قبل التواقيع</td><td>بيانات الكلية: <b>footer_title</b> (إن لم يكن فارغاً)</td></tr>
        <tr><td>التواقيع (رئيس المكتب الأكاديمي، المسجل، رئيس القسم، العميد)</td><td>أسماؤهم في بيانات الكلية/القسم؛ <b>التوقيع الفارغ يُحذف</b></td></tr>
        <tr><td>الشعاران</td><td>شعار الجامعة، وشعار الكلية من ملفات الشعارات (<code>fac{رمز الكلية}</code>)</td></tr>
    </table>
    <div class="note"><span class="ico">ℹ️</span><div>لتغيير الأسماء أو العناوين: عدّل بيانات الكلية (Metadata) من <b>التسجيل ← الكليات</b>، لا من صفحة الطباعة.</div></div>
</section>

<!-- 8 -->
<section id="summary">
    <h2><span class="num">8</span> ملخص النتيجة والسجل الأكاديمي</h2>
    <h3>طباعة ملخص النتيجة</h3>
    <ul>
        <li>صفحة إحصائية: عدد الجالسين والناجحين والراسبين، ونسبة النجاح وأعلى معدل.</li>
        <li>جدول المقررات: الجالسون والناجحون والراسبون ونسبة النجاح وتوزيع التقديرات.</li>
        <li>جدول القرارات والتوصيات بالعدد والنسبة.</li>
        <li>اختيار <b>التخصص</b> يحصر الأرقام في طلابه. الرابط قابل للتحديث والمشاركة.</li>
    </ul>
    <h3>طباعة السجل الأكاديمي لطالب</h3>
    <ul>
        <li>اختر الكلية والقسم والفصل والعام الدراسي ثم <b>الطالب</b> من قائمة البحث (اكتب جزءاً من الاسم أو الرقم).</li>
        <li>يفتح السجل الكامل لدرجات الطالب وتقديراته. للطالب نفسه فقط، وضمن صلاحياتك.</li>
    </ul>
    <div class="warn"><span class="ico">⚠️</span><div>إن لم تختر طالباً تظهر الرسالة «يجب اختيار الطالب لطباعة سجله الأكاديمي».</div></div>
</section>

<!-- 9 -->
<section id="final">
    <h2><span class="num">9</span> الاعتماد النهائي للنتائج</h2>
    <p>يجريه <b>معتمد الكلية</b> فقط (شخص واحد لكل كلية) برمزه الشخصي.</p>
    <div class="flow">
        <span class="n">اكتمال اعتماد المواد</span><span class="ar">←</span>
        <span class="n">لا توصية ناقصة</span><span class="ar">←</span>
        <span class="n">إدخال رمز الاعتماد</span><span class="ar">←</span>
        <span class="n" style="border-color:#16a34a">قفل + سجل أكاديمي</span>
    </div>
    <table>
        <tr><th>مانع الاعتماد</th><th>الحل</th></tr>
        <tr><td>مواد لم تصل مرحلة «معتمد نهائياً»</td><td>أكمل اعتماد المواد من صفحة إدخال الدرجات</td></tr>
        <tr><td>طلاب بتوصية ناقصة (افتراضية)</td><td>أكمل إدخال درجات المواد الناقصة</td></tr>
        <tr><td>طلاب بقرار ملحق/بديل بلا نتيجة ملحق</td><td>أدخل نتائج الملحق أولاً</td></tr>
        <tr><td>لا نتائج للمجموعة أو معتمدة سابقاً</td><td>راجع الاختيارات</td></tr>
    </table>
    <div class="danger"><span class="ico">⛔</span><div><b>بعد الاعتماد تُقفل النتائج</b> ولا يعدّلها إلا المدير الأعلى. 5 محاولات خاطئة لرمز الاعتماد تقفل الحساب 15 دقيقة.</div></div>
</section>

<!-- 10 -->
<section id="errors">
    <h2><span class="num">10</span> الرسائل وما تعنيه</h2>
    <table>
        <tr><th>الرسالة</th><th>الحل</th></tr>
        <tr><td>يجب اختيار العام الدراسي</td><td>اختر العام الدراسي</td></tr>
        <tr><td>يجب اختيار الكلية / القسم / الفصل</td><td>أكمل الخانة المطلوبة</td></tr>
        <tr><td>يجب اختيار الأساسي أو الملحق</td><td>اختر «الدور»</td></tr>
        <tr><td>يجب اختيار مادة واحدة على الأقل</td><td>اختر المواد أو «تحديد الكل»</td></tr>
        <tr><td>يجب اختيار حقل واحد على الأقل</td><td>علّم حقلاً واحداً أو «تحديد الكل»</td></tr>
        <tr><td>يجب اختيار الطالب لطباعة سجله الأكاديمي</td><td>اختر الطالب من القائمة</td></tr>
        <tr><td>403 (غير مسموح)</td><td>الكلية أو القسم أو الفصل خارج صلاحياتك</td></tr>
        <tr><td>لا توجد بيانات طلاب لعرضها</td><td>راجع العام والفصل والقسم والتخصص والدور</td></tr>
    </table>
    <p>الرسائل تظهر بالعربية والإنجليزية معاً.</p>
</section>

<!-- 11 -->
<section id="tips">
    <h2><span class="num">11</span> نصائح للمكتب الأكاديمي</h2>
    <ul>
        <li><b>قبل الطباعة الرسمية:</b> تأكد أن المواد معتمدة وأن لا توصيات «افتراضية».</li>
        <li><b>للأرشفة:</b> اطبع بصيغة PDF من نافذة الطابعة واحتفظ بالاسم: <code>نتيجة-الكلية-الفصل-العام</code>.</li>
        <li><b>للمراجعة السريعة:</b> فعّل «الدرجات والتقديرات» وراجع الخلايا الرمادية أولاً.</li>
        <li><b>لا تعتمد النتائج</b> قبل مراجعة الملخص وإحصائيات المواد.</li>
        <li><b>عند الخطأ:</b> زر «رجوع» يعيدك لنفس الاختيارات لتعدّل وتعيد الطباعة.</li>
    </ul>
    <div class="note"><span class="ico">📋</span><div><b>قائمة التحقق:</b> ☐ العام والفصل والقسم صحيحان &nbsp; ☐ المواد مكتملة &nbsp; ☐ الحقول المطلوبة محددة &nbsp; ☐ لا خلايا ناقصة &nbsp; ☐ الاتجاه أفقي في الطباعة</div></div>
</section>

<footer>دليل طباعة النتيجة. الأسماء والأرقام في الأمثلة للتوضيح فقط.</footer>
</div>
@endverbatim
</body>
</html>
