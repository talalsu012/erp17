<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>دليل إدارة الطلاب</title>
@include('help._style')
</head>
<body>
@verbatim
<div class="wrap">

<header class="hero">
    <h1>دليل إدارة الطلاب</h1>
    <p>كل إجراءات صفحة <b>الطلاب</b>: البحث والتصفية، الأعمدة، التعديل، التحديث الجماعي، التصدير، الحذف والاستعادة. بلا أي خبرة برمجية: اتبع الصور والعلامات الحمراء.</p>
</header>

<nav class="toc">
    <a href="#list">1) صفحة القائمة</a>
    <a href="#search">2) البحث والتجميع</a>
    <a href="#filters">3) التصفيات</a>
    <a href="#columns">4) الأعمدة</a>
    <a href="#edit">5) تعديل بيانات طالب</a>
    <a href="#row">6) إجراءات السجل الواحد</a>
    <a href="#bulk">7) التحديث الجماعي</a>
    <a href="#delete">8) الحذف والاستعادة</a>
    <a href="#export">9) التصدير والاستيراد</a>
    <a href="#rules">10) قواعد وتنبيهات</a>
    <a href="#faq">11) أسئلة شائعة</a>
</nav>

<!-- 1 -->
<section id="list">
    <h2><span class="num">1</span> صفحة القائمة: ماذا أرى؟</h2>
    <p>المسار: <b>الطلاب ← القائمة</b>. تعرض الصفحة الطلاب المنتمين للكليات المسندة إليك فقط.</p>

    <div class="mock">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
            <span class="btn violet" style="position:relative">إضافة طالب<span class="mark">1</span></span>
            <b style="font-size:20px">الطلاب</b>
        </div>
        <div class="panel">
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:10px">
                <span class="btn" style="background:#4338ca;position:relative">تصدير طلاب<span class="mark">2</span></span>
                <span class="fld" style="flex:1;min-width:140px"><div class="box empty">بحث</div><span class="mark">3</span></span>
                <span class="btn" style="background:#374151;position:relative">▼ تصفية<span class="mark">4</span></span>
                <span class="btn" style="background:#374151;position:relative">▥ أعمدة<span class="mark">5</span></span>
                <span class="fld" style="min-width:150px"><div class="box">تجميع حسب: -</div><span class="mark">6</span></span>
            </div>
            <div class="tbl">
                <div class="row head"><div>الإجراءات</div><div>الرقم الجامعي</div><div>الاسم</div><div>الفصل</div><div>التسجيل</div><div>الكلية</div></div>
                <div class="row"><div><span class="btn violet" style="padding:2px 10px;position:relative">⋮ الإجراءات<span class="mark">7</span></span></div><div>1104135916</div><div>اسم الطالب (مثال)</div><div>الفصل السادس</div><div>غير مسجل</div><div><span class="tag s">كلية علوم التمريض</span></div></div>
            </div>
        </div>
    </div>

    <div class="legend">
        <div><span class="mark">1</span><span><b>إضافة طالب:</b> نموذج طالب جديد.</span></div>
        <div><span class="mark">2</span><span><b>تصدير طلاب:</b> ملف Excel بالأعمدة التي تختارها (القسم 9).</span></div>
        <div><span class="mark">3</span><span><b>البحث العام:</b> اسم أو رقم جامعي أو جزء منهما.</span></div>
        <div><span class="mark">4</span><span><b>التصفية:</b> الكلية والقسم والتخصص والفصل... الرقم على الأيقونة = عدد التصفيات الفعّالة.</span></div>
        <div><span class="mark">5</span><span><b>الأعمدة:</b> إظهار/إخفاء الأعمدة وترتيبها.</span></div>
        <div><span class="mark">6</span><span><b>تجميع حسب:</b> الكلية، الفصل، الحالة، القبول.</span></div>
        <div><span class="mark">7</span><span><b>الإجراءات:</b> قائمة عمليات الطالب (القسم 6).</span></div>
    </div>

    <div class="note"><span class="ico">ℹ️</span><div>تسمية «الفصل» تعني المستوى الدراسي للطالب، و«العام الدراسي» هو العام الذي ينتمي إليه الطالب.</div></div>
</section>

<!-- 2 -->
<section id="search">
    <h2><span class="num">2</span> البحث والتجميع</h2>
    <div class="cards">
        <div class="card"><h4>🔎 البحث العام (أعلى الجدول)</h4><p>اكتب جزءاً من الاسم أو الرقم. يبحث في الرقم والاسم معاً.</p></div>
        <div class="card"><h4>🔎 البحث تحت العمود</h4><p>صندوق البحث تحت «الرقم الجامعي» و«الاسم» يبحث في ذلك العمود فقط.</p></div>
        <div class="card"><h4>↕ الترتيب</h4><p>اضغط عنوان العمود (له سهم) لترتيبه تصاعدياً/تنازلياً. اضغط ثانية لعكس الترتيب.</p></div>
        <div class="card"><h4>🗂 التجميع</h4><p>«تجميع حسب» يقسم القائمة إلى مجموعات قابلة للطي (حسب الكلية أو الفصل أو الحالة أو القبول).</p></div>
    </div>
    <div class="tip"><span class="ico">💡</span><div>للحصول على قائمة دقيقة: صفِّ بالكلية والقسم والفصل أولاً، ثم استخدم البحث للأسماء.</div></div>
</section>

<!-- 3 -->
<section id="filters">
    <h2><span class="num">3</span> التصفيات</h2>
    <p>اضغط أيقونة المصفاة ▼. تُطبَّق التصفيات عند الضغط على <b>«تطبيق التصفيات»</b>.</p>
    <div class="mock">
        <div class="panel">
            <div class="grid">
                <div class="fld full"><label>السجلات المحذوفة <span class="mark" style="position:static;display:inline-flex">1</span></label><div class="box">بدون السجلات المحذوفة</div></div>
                <div class="fld"><label>الكلية <span class="mark" style="position:static;display:inline-flex">2</span></label><div class="box empty">اختر</div></div>
                <div class="fld"><label>قسم</label><div class="box empty">اختر</div></div>
                <div class="fld"><label>تخصص</label><div class="box empty">اختر</div></div>
                <div class="fld"><label>الفصل <span class="mark" style="position:static;display:inline-flex">3</span></label><div class="box">الكل</div></div>
                <div class="fld"><label>القبول</label><div class="box">الكل</div></div>
                <div class="fld"><label>التسجيل</label><div class="box">الكل</div></div>
            </div>
            <div style="margin-top:12px"><span class="btn" style="background:#e5e7eb;color:#374151">＋ إضافة قاعدة <span class="mark" style="position:static;display:inline-flex">4</span></span></div>
            <div class="btns"><span class="btn violet">تطبيق التصفيات</span><span class="btn" style="background:#dc2626">إعادة ضبط التصفيات</span><span class="btn" style="background:#fff;color:#374151;border:1px solid #d1d5db">إغلاق</span></div>
        </div>
    </div>

    <div class="legend">
        <div><span class="mark">1</span><span><b>السجلات المحذوفة:</b> بدون المحذوفين، أو معهم، أو المحذوفون فقط (لاستعادتهم).</span></div>
        <div><span class="mark">2</span><span><b>الكلية ← القسم ← التخصص:</b> متسلسلة؛ يمكن اختيار أكثر من قيمة في كل خانة.</span></div>
        <div><span class="mark">3</span><span><b>الفصل والقبول والتسجيل والجنس:</b> اختيارات سريعة.</span></div>
        <div><span class="mark">4</span><span><b>إضافة قاعدة:</b> شروط متقدمة (الاسم، العام الدراسي، الرسوم، الأقساط، تاريخ الإنشاء...). كل قاعدة تضيّق النتيجة.</span></div>
    </div>

    <div class="warn"><span class="ico">⚠️</span><div>القاعدة المتقدمة تقبل شروطاً مثل «يحتوي» و«يساوي» و«أكبر من». استخدم زر السلة 🗑 لحذف قاعدة، وزر النسخ لتكرارها. «فصل (أو)» لتجميع شروط بديلة.</div></div>
</section>

<!-- 4 -->
<section id="columns">
    <h2><span class="num">4</span> إدارة الأعمدة</h2>
    <p>أيقونة ▥ بجانب البحث تفتح قائمة الأعمدة. فعّل أو ألغِ أي عمود، وغيّر ترتيبها بالسحب من أيقونة ≡.</p>
    <table>
        <tr><th>مجموعة الأعمدة</th><th>أمثلة</th></tr>
        <tr><td>الأساسية (ثابتة غالباً)</td><td>الصورة، الرقم الجامعي، الاسم، الفصل، الكلية</td></tr>
        <tr><td>أكاديمية</td><td>القسم، التخصص، الدراسة، سنة القبول، العام الدراسي، التسجيل</td></tr>
        <tr><td>مالية</td><td>الرسوم، المتحصل، المتبقي، رسوم التسجيل، الأقساط، الدعم</td></tr>
        <tr><td>شخصية</td><td>الرقم الوطني، الجنسية، البريد الإلكتروني، الموبايل</td></tr>
    </table>
    <div class="tip"><span class="ico">💡</span><div>«إعادة ضبط التصفيات» داخل نافذة الأعمدة تعيد الإعدادات الافتراضية. اضغط «تطبيق» لتثبيت اختيارك.</div></div>
</section>

<!-- 5 -->
<section id="edit">
    <h2><span class="num">5</span> تعديل بيانات طالب</h2>
    <p>من «الإجراءات» اختر <b>تعديل</b>، أو اضغط الرقم الجامعي لعرض الطالب. للصفحة أربعة تبويبات:</p>

    <div class="mock">
        <div class="panel">
            <div style="display:flex;gap:6px;margin-bottom:12px;flex-wrap:wrap">
                <span class="tag s">① المعلومات الأساسية</span><span class="tag n">② المعلومات الشخصية</span><span class="tag n">③ الرسوم الدراسية</span><span class="tag n">④ المفكرة</span>
            </div>
            <div class="grid">
                <div class="fld"><label>الاسم *</label><div class="box">اسم الطالب</div></div>
                <div class="fld"><label>الاسم بالإنجليزية *</label><div class="box">Student Name</div></div>
                <div class="fld"><label>الرقم الجامعي *</label><div class="box">1163286322</div></div>
                <div class="fld"><label>الكلية *</label><div class="box">كلية الاقتصاد</div></div>
                <div class="fld"><label>قسم</label><div class="box">قسم الاقتصاد</div></div>
                <div class="fld"><label>تخصص</label><div class="box">اقتصاد</div></div>
                <div class="fld"><label>الفصل</label><div class="box">الفصل الثاني</div></div>
                <div class="fld"><label>العام الدراسي</label><div class="box empty">اختر</div></div>
                <div class="fld"><label>القبول *</label><div class="box">عام</div></div>
            </div>
            <div class="btns"><span class="btn violet">حفظ التغييرات</span><span class="btn" style="background:#fff;color:#374151;border:1px solid #d1d5db">إلغاء</span></div>
        </div>
    </div>

    <table>
        <tr><th>التبويب</th><th>ما فيه</th></tr>
        <tr><td>① المعلومات الأساسية</td><td>الاسم والرقم الجامعي ورقم الجلوس؛ الكلية والقسم والتخصص والفصل والدراسة والعام الدراسي وسنة القبول والحالة والقبول وحساب الطالب</td></tr>
        <tr><td>② المعلومات الشخصية</td><td>الصورة، الجنسية، النوع، تاريخ الميلاد، عنوان ولي الأمر، بيانات ولي الأمر والوالدة</td></tr>
        <tr><td>③ الرسوم الدراسية</td><td>رسوم التسجيل والدراسة والكورس والامتحان والبطاقة والدعم، والرسوم غير الطلابية (تقديم، معاينة، أخرى)، التخفيض والعملة، والأقساط</td></tr>
        <tr><td>④ المفكرة</td><td>ملاحظات الطالب</td></tr>
    </table>
    <p>أسفل الصفحة قسم <b>التحصيل</b> لإضافة دفعة (payment) أو ربط دفعة موجودة بالطالب.</p>

    <div class="warn"><span class="ico">⚠️</span><div><b>تغيير الكلية يغيّر القسم والتخصص:</b> بعد تغيير الكلية أعد اختيار القسم والتخصص. وبعض الحقول مقفلة إن لم تكن لك صلاحية عليها.</div></div>
    <div class="danger"><span class="ico">⛔</span><div>الحقول ذات النجمة (*) إلزامية. لن يُحفظ التعديل ما لم تكتمل. اضغط «حفظ التغييرات» ولا تغلق الصفحة قبلها.</div></div>
</section>

<!-- 6 -->
<section id="row">
    <h2><span class="num">6</span> إجراءات السجل الواحد (زر ⋮ الإجراءات)</h2>
    <table>
        <tr><th>الإجراء</th><th>ماذا يفعل؟</th><th>تنبيه</th></tr>
        <tr><td>عرض</td><td>صفحة بيانات الطالب للقراءة</td><td>آمن</td></tr>
        <tr><td>تعديل</td><td>يفتح نموذج التعديل (القسم 5)</td><td>يُسجَّل في النشاطات</td></tr>
        <tr><td>حذف</td><td>حذف مؤقت (يمكن استعادته)</td><td>يطلب تأكيداً</td></tr>
        <tr><td>حذف حساب الطالب</td><td>يحذف حساب دخول الطالب للبوابة ويفرّغ حقل حساب الطالب</td><td class="no">لا يحذف الطالب نفسه. الطالب يفقد الدخول للبوابة</td></tr>
        <tr><td>النشاطات</td><td>سجل: من عدّل ماذا ومتى</td><td>للمراجعة والتدقيق</td></tr>
    </table>
</section>

<!-- 7 -->
<section id="bulk">
    <h2><span class="num">7</span> التحديث الجماعي لعدد من الطلاب</h2>
    <div class="steps">
        <div class="step"><div><b>صفِّ القائمة أولاً</b><span>بالكلية والقسم والفصل ليقتصر الجدول على من تريد تحديثهم فقط.</span></div></div>
        <div class="step"><div><b>حدّد الطلاب</b><span>بالمربعات يميناً. للتحديد الكامل اضغط «تحديد كل السجلات (العدد)» أعلى الجدول.</span></div></div>
        <div class="step"><div><b>اضغط «تحديث طلاب» (الزر الأصفر)</b><span>تفتح نافذة بتبويبين: المعلومات الأساسية، والرسوم الطلابية.</span></div></div>
        <div class="step"><div><b>حدد الحقول المراد تغييرها</b><span>علّم المربعات (الكلية، الفصل، الدراسة، العام الدراسي...) فتظهر لكل حقل خانة قيمته.</span></div></div>
        <div class="step"><div><b>اختر القيم ثم «إرسال»</b><span>تُطبَّق القيم على <b>كل</b> الطلاب المحدّدين.</span></div></div>
    </div>

    <div class="mock">
        <div class="panel">
            <b>حدد الحقول التي تريد تحديثها جماعياً:</b>
            <div style="display:flex;flex-wrap:wrap;gap:10px 22px;margin:10px 0">
                <label>☑ الكلية</label><label>☐ قسم</label><label>☐ تخصص</label><label>☐ الفصل</label><label>☑ الدراسة</label><label>☐ العام الدراسي</label><label>☐ القبول</label><label>☐ الحالة</label><label>☐ حساب الطالب</label>
            </div>
            <div class="grid"><div class="fld"><label>الكلية *</label><div class="box">كلية التربية</div></div><div class="fld"><label>الدراسة</label><div class="box">بكالريوس</div></div></div>
            <div class="btns"><span class="btn" style="background:#f59e0b">إرسال</span><span class="btn" style="background:#fff;color:#374151;border:1px solid #d1d5db">إلغاء</span></div>
        </div>
    </div>

    <div class="danger"><span class="ico">⛔</span><div><b>قبل «إرسال»:</b> تأكد من عدد المحدّدين ومن الحقول المعلَّمة فقط. لا يمكن التراجع عن التحديث الجماعي من الواجهة. التغييرات تُسجَّل في النشاطات لكل طالب.</div></div>
    <div class="warn"><span class="ico">⚠️</span><div>اختيار «الكلية» دون «القسم» أو «التخصص» قد يترك بيانات متضاربة. حدّث الحقول المترابطة معاً.</div></div>
</section>

<!-- 8 -->
<section id="delete">
    <h2><span class="num">8</span> الحذف والاستعادة</h2>
    <div class="flow">
        <span class="n">حذف (مؤقت)</span><span class="ar">←</span>
        <span class="n">يختفي من القائمة</span><span class="ar">←</span>
        <span class="n">تصفية «السجلات المحذوفة»</span><span class="ar">←</span>
        <span class="n" style="border-color:#16a34a">استعادة</span>
    </div>
    <table>
        <tr><th>العملية</th><th>أين؟</th><th>الأثر</th></tr>
        <tr><td>حذف</td><td>إجراء السجل أو الإجراءات الجماعية ← «حذف المحدد»</td><td>مؤقت، يمكن استعادته</td></tr>
        <tr><td>استعادة</td><td>صفِّ بـ«المحذوفون فقط» ثم حدّد ← «استعادة»</td><td>يعود الطالب للقائمة</td></tr>
        <tr><td class="no">حذف نهائي</td><td>صفِّ بـ«المحذوفون فقط» ثم «حذف نهائي»</td><td class="no">لا رجعة فيه</td></tr>
    </table>
    <div class="danger"><span class="ico">⛔</span><div><b>الحذف النهائي يمحو الطالب وبياناته للأبد.</b> لا تستخدمه إلا بتعليمات رسمية. الحذف العادي يكفي في أغلب الحالات.</div></div>
</section>

<!-- 9 -->
<section id="export">
    <h2><span class="num">9</span> التصدير والاستيراد</h2>
    <h3>تصدير طلاب</h3>
    <ol>
        <li>صفِّ القائمة بما تريد تصديره.</li>
        <li>اضغط «تصدير طلاب»: تظهر نافذة بالأعمدة؛ علّم المطلوب وغيّر اسم العمود إن أردت.</li>
        <li>اضغط «تصدير». يصلك إشعار عند اكتمال الملف مع رابط التحميل.</li>
    </ol>
    <h3>استيراد طلاب</h3>
    <p>زر «استيراد» يظهر فقط لمن يملك صلاحية الإضافة. اختر ملف Excel وطابق الأعمدة ثم ابدأ. راجع تقرير الاستيراد لأي صفوف مرفوضة.</p>
    <div class="note"><span class="ico">ℹ️</span><div>التصدير يحترم صلاحياتك: لن يتضمن طلاب كليات ليست لك.</div></div>
</section>

<!-- 10 -->
<section id="rules">
    <h2><span class="num">10</span> قواعد وتنبيهات مهمة</h2>
    <ul>
        <li>يظهر لك الطلاب في الكليات المسندة لك فقط؛ إن لم تجد طالباً فقد يكون في كلية ليست ضمن صلاحيتك.</li>
        <li>الرقم الجامعي <b>فريد</b>: لا يتكرر بين طالبين.</li>
        <li>اكتمال بيانات الطالب (الكلية والقسم والفصل والعام الدراسي والدراسة) شرط لإدخال درجاته لاحقاً.</li>
        <li>نتائج الطالب بعد الاعتماد لا تتأثر بتغيير بياناته، لكن الكشوف الحية قد تتأثر؛ راجع المسؤول قبل تغيير الكلية أو الفصل لطالب له نتائج.</li>
        <li>الإجراءات الجماعية تعمل على <b>كل</b> المحدّدين: راجع العدد قبل التأكيد.</li>
    </ul>
</section>

<!-- 11 -->
<section id="faq">
    <h2><span class="num">11</span> أسئلة شائعة</h2>
    <h3>لا أجد الطالب في القائمة</h3>
    <p>امسح التصفيات (إعادة ضبط)، ثم ابحث بالرقم. تأكد أنه ليس محذوفاً (صفِّ بـ«السجلات المحذوفة») وأن كليته ضمن صلاحيتك.</p>
    <h3>حذفت طالباً بالخطأ</h3>
    <p>صفِّ بـ«المحذوفون فقط»، حدّده، ثم «استعادة». لا تستخدم الحذف النهائي.</p>
    <h3>غيّرت الكلية ولم يتغير القسم</h3>
    <p>اختر القسم والتخصص من جديد بعد تغيير الكلية.</p>
    <h3>الحقل مقفل ولا أستطيع تعديله</h3>
    <p>صلاحية الحقل مقيّدة. اطلب من المسؤول منحك صلاحية الحقل.</p>
    <h3>ماذا أرسل للدعم عند المشكلة؟</h3>
    <p>الرقم الجامعي، العملية التي نفّذتها، نص الرسالة، ولقطة شاشة.</p>
</section>

<footer>دليل إدارة الطلاب. الأسماء والأرقام في الأمثلة للتوضيح فقط.</footer>
</div>
@endverbatim
</body>
</html>
