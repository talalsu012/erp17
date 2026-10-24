@verbatim
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>دليل المكتب الأكاديمي: إدخال الدرجات وإعداد النتائج</title>
@endverbatim
@include('help._style')
@verbatim
</head>
<body>
<div class="wrap">

<header class="hero">
    <h1>دليل المكتب الأكاديمي: إدخال الدرجات وإعداد النتائج</h1>
    <p>دليل مبسّط لموظفي المكتب الأكاديمي. لا يحتاج أي خبرة برمجية: اتبع الخطوات والعلامات الحمراء المرقّمة على الصور التوضيحية.</p>
</header>

<nav class="toc">
    <a href="#map">1) خريطة الصفحة</a>
    <a href="#steps">2) خطوات الإدخال</a>
    <a href="#search">3) كيف تُجلب المواد والطلاب</a>
    <a href="#special">4) قسم فقط أم قسم وتخصص؟</a>
    <a href="#enter">5) إدخال الدرجة</a>
    <a href="#grade">6) التقدير والنقاط</a>
    <a href="#recom">7) المعدل والتوصية</a>
    <a href="#reason">8) الأسباب (الحالة)</a>
    <a href="#stage">9) مراحل الاعتماد</a>
    <a href="#errors">10) الرسائل وما تعنيه</a>
    <a href="#faq">11) أسئلة شائعة</a>
</nav>

<!-- 1 -->
<section id="map">
    <h2><span class="num">1</span> خريطة الصفحة: ماذا تعني كل خانة؟</h2>
    <p>الصفحة: <b>النتائج ← إدخال الدرجات</b>. الصورة التالية تمثل الصفحة، والأرقام الحمراء تشرح كل جزء.</p>

    <div class="mock">
        <div class="panel">
            <div class="grid">
                <div class="fld"><label>الكلية *</label><div class="box">كلية علوم الحاسوب وتقانة المعلومات</div><span class="mark">1</span></div>
                <div class="fld"><label>القسم *</label><div class="box">تقانة المعلومات</div><span class="mark">2</span></div>
                <div class="fld"><label>التخصص</label><div class="box empty">اختر</div><span class="mark">3</span></div>
                <div class="fld"><label>العام الدراسي *</label><div class="box">2026</div><span class="mark">4</span></div>
                <div class="fld"><label>الفصل *</label><div class="box">الفصل العاشر</div><span class="mark">5</span></div>
                <div class="fld"><label>الدور *</label><div class="box">الأساسي</div><span class="mark">6</span></div>
                <div class="fld full"><label>المادة *</label><div class="box">إدارة المعرفة</div><span class="mark">7</span></div>
            </div>
            <div class="btns">
                <span class="btn violet">الطلاب<span class="mark">8</span></span>
                <span class="btn green">إعتماد الإدخال<span class="mark">9</span></span>
                <span class="badge">المرحلة الحالية: الأستاذ<span class="mark">10</span></span>
            </div>
        </div>

        <div class="tbl">
            <div class="row head"><div>الحالة (السبب) <span class="mark" style="position:static;display:inline-flex">11</span></div><div>الرقم الجامعي</div><div>الاسم</div><div>الامتحان <span class="mark" style="position:static;display:inline-flex">12</span></div><div>درجة الأعمال <span class="mark" style="position:static;display:inline-flex">13</span></div><div>التقدير <span class="mark" style="position:static;display:inline-flex">14</span></div></div>
            <div class="row"><div><div class="box" style="border:1px solid #d1d5db;border-radius:6px;padding:3px 8px">اختر</div></div><div>1115877918</div><div>اسم الطالب (مثال)</div><div><input value="90" readonly></div><div><input value="" readonly></div><div><b>A</b></div></div>
        </div>
        <div class="toast"><b>✔ تم الحفظ</b><br>التقدير: A | التوصية: توصية افتراضية | المعدل: 4.00 <span class="mark" style="position:static;display:inline-flex">15</span></div>
    </div>

    <div class="legend">
        <div><span class="mark">1</span><span><b>الكلية:</b> تظهر لك الكليات المسموحة لك فقط. اختيارها إلزامي.</span></div>
        <div><span class="mark">2</span><span><b>القسم:</b> يعرض أقسام الكلية المختارة فقط.</span></div>
        <div><span class="mark">3</span><span><b>التخصص (اختياري):</b> يغيّر نتيجة البحث، انظر القسم 4.</span></div>
        <div><span class="mark">4</span><span><b>العام الدراسي:</b> دفعة الطلاب المراد إدخال درجاتهم.</span></div>
        <div><span class="mark">5</span><span><b>الفصل:</b> الفصل الذي تتبعه المادة والطلاب.</span></div>
        <div><span class="mark">6</span><span><b>الدور:</b> الأساسي أو الملحق (اختر بحذر).</span></div>
        <div><span class="mark">7</span><span><b>المادة:</b> تظهر بعد اختيار الكلية والفصل والعام الدراسي، وبمجرد اختيارها يظهر الطلاب.</span></div>
        <div><span class="mark">8</span><span><b>الطلاب:</b> يحدّث قائمة الطلاب بعد تغيير أي خانة.</span></div>
        <div><span class="mark">9</span><span><b>إعتماد الإدخال:</b> يرحّل المادة للمرحلة التالية بعد انتهائك.</span></div>
        <div><span class="mark">10</span><span><b>المرحلة الحالية:</b> من يحق له التعديل الآن.</span></div>
        <div><span class="mark">11</span><span><b>الحالة (السبب):</b> غياب، حرمان، بديل... انظر القسم 8.</span></div>
        <div><span class="mark">12</span><span><b>الامتحان:</b> درجة امتحان المادة (الخانة التي تكتب فيها).</span></div>
        <div><span class="mark">13</span><span><b>درجة الأعمال:</b> تظهر فقط للمواد التي لها أعمال سنة.</span></div>
        <div><span class="mark">14</span><span><b>التقدير:</b> يُحسب تلقائياً، لا يُكتب يدوياً.</span></div>
        <div><span class="mark">15</span><span><b>إشعار الحفظ:</b> يؤكد الحفظ ويعرض التقدير والتوصية والمعدل.</span></div>
    </div>

    <div class="note"><span class="ico">ℹ️</span><div>الخانات المعلّمة بنجمة (*) إلزامية. التقدير والنقاط والتوصية <b>لا تُكتب</b>، بل يحسبها النظام بعد كل حفظ.</div></div>
</section>

<!-- 2 -->
<section id="steps">
    <h2><span class="num">2</span> خطوات الإدخال (من البداية للنهاية)</h2>
    <div class="steps">
        <div class="step"><div><b>اختر الكلية ثم القسم</b><span>اختر القسم الذي تريد إدخال درجات طلابه. تُصفّى الأقسام حسب الكلية.</span></div></div>
        <div class="step"><div><b>اختر التخصص عند الحاجة فقط</b><span>اتركه فارغاً إن كانت المادة عامة لكل القسم. اقرأ القسم 4 قبل أن تقرر.</span></div></div>
        <div class="step"><div><b>اختر العام الدراسي والفصل والدور</b><span>الدور «الأساسي» للامتحان الأول، و«الملحق» لمن رسب ويحق له الملحق فقط.</span></div></div>
        <div class="step"><div><b>اختر المادة</b><span>بمجرد اختيارها تظهر قائمة الطلاب والمرحلة الحالية للمادة.</span></div></div>
        <div class="step"><div><b>اكتب الدرجة في خانة «الامتحان»</b><span>اضغط <kbd>Enter</kbd> أو انتقل للخانة التالية، فيُحفظ تلقائياً ويظهر إشعار «تم الحفظ».</span></div></div>
        <div class="step"><div><b>راجع التقدير والتوصية في الإشعار</b><span>إن ظهر إشعار أحمر فاقرأ رسالته (القسم 10) ثم صحّح.</span></div></div>
        <div class="step"><div><b>اضغط «إعتماد الإدخال» عند الانتهاء</b><span>يُرحَّل الإدخال للمرحلة التالية ولا يمكنك التعديل بعدها.</span></div></div>
    </div>

    <div class="warn"><span class="ico">⚠️</span><div><b>قبل الاعتماد:</b> تأكد أن كل الطلاب لديهم درجة أو حالة (غياب/بديل...). الاعتماد يُغلق التعديل عليك.</div></div>
</section>

<!-- 3 -->
<section id="search">
    <h2><span class="num">3</span> كيف يعمل البحث: من الفورم إلى المواد والطلاب</h2>
    <p>الخانات مرتبطة ببعضها: كل خانة تضيّق ما بعدها.</p>
    <div class="flow">
        <span class="n">الكلية</span><span class="ar">←</span>
        <span class="n">القسم</span><span class="ar">←</span>
        <span class="n opt">التخصص (اختياري)</span><span class="ar">←</span>
        <span class="n">العام الدراسي + الفصل</span><span class="ar">←</span>
        <span class="n">الدور</span><span class="ar">←</span>
        <span class="n">المادة</span><span class="ar">←</span>
        <span class="n" style="border-color:#16a34a">قائمة الطلاب</span>
    </div>

    <h3>ما الذي يحدد قائمة المواد؟</h3>
    <p>تُعرض المواد المفعّلة التي تطابق: <b>الكلية + الفصل</b> دائماً، و<b>القسم</b> إذا اخترته، و<b>التخصص</b> إذا اخترته.</p>

    <h3>ما الذي يحدد قائمة الطلاب؟</h3>
    <table>
        <tr><th>الشرط</th><th>يُطبَّق متى؟</th></tr>
        <tr><td>نفس الكلية والفصل والعام الدراسي</td><td>دائماً</td></tr>
        <tr><td>نفس القسم</td><td>إذا اخترت قسماً</td></tr>
        <tr><td>نفس التخصص</td><td>إذا اخترت تخصصاً</td></tr>
        <tr><td>ضمن صلاحياتك (كلية/قسم/مستوى)</td><td>دائماً. لا يظهر لك إلا ما أُسند إليك</td></tr>
        <tr><td>رسب في هذه المادة في الأساسي وتوصيته ملحق</td><td>فقط عند اختيار الدور «الملحق»</td></tr>
    </table>

    <div class="tip"><span class="ico">💡</span><div>إن لم يظهر أي طالب فتأكد من: العام الدراسي والفصل، وأن اسم المادة يتبع القسم/التخصص المختار، وأن صلاحياتك تشمل الكلية والقسم.</div></div>
</section>

<!-- 4 -->
<section id="special">
    <h2><span class="num">4</span> الطلاب بقسم فقط أم بقسم وتخصص؟ وتضاربها مع المواد</h2>
    <p>بعض الطلاب لهم <b>قسم فقط</b> (لا تخصص)، وبعضهم لهم <b>قسم وتخصص</b>. وبعض المواد <b>عامة للقسم</b> وبعضها <b>خاصة بتخصص</b>. النظام يمنع إدخال درجة لطالب لا تنتمي مادته إليه.</p>

    <h3>القاعدة المختصرة</h3>
    <div class="cards">
        <div class="card"><h4>① تركت «التخصص» فارغاً</h4>
            <p>يظهر <b>كل طلاب القسم</b> (بتخصص وبلا تخصص).</p>
            <p>تظهر كل مواد القسم: العامة والخاصة بتخصص.</p>
            <p class="ok">المادة العامة: تُدخل لكل الطلاب.</p>
            <p class="no">المادة الخاصة بتخصص: يُرفض الطالب الذي لا يتبع ذلك التخصص.</p>
        </div>
        <div class="card"><h4>② اخترت «تخصصاً»</h4>
            <p>يظهر <b>طلاب هذا التخصص فقط</b>.</p>
            <p>تظهر <b>مواد هذا التخصص فقط</b> (لا تظهر المواد العامة للقسم).</p>
            <p class="ok">طلاب التخصص + مادة التخصص: مسموح.</p>
            <p class="no">أي طالب أو مادة خارج التخصص: مرفوض.</p>
        </div>
    </div>

    <h3>جدول الحالات</h3>
    <table class="center">
        <tr><th>حالة الطالب</th><th>مادة عامة للقسم</th><th>مادة تخصص «أ»</th><th>مادة تخصص «ب»</th></tr>
        <tr><td>بلا تخصص</td><td class="ok">✔ مسموح</td><td class="no">✘ مرفوض</td><td class="no">✘ مرفوض</td></tr>
        <tr><td>تخصص «أ»</td><td class="ok">✔ مسموح</td><td class="ok">✔ مسموح</td><td class="no">✘ مرفوض</td></tr>
        <tr><td>تخصص «ب»</td><td class="ok">✔ مسموح</td><td class="no">✘ مرفوض</td><td class="ok">✔ مسموح</td></tr>
    </table>
    <p style="font-size:14px;color:var(--muted)">الجدول لحالة «التخصص فارغ». عند اختيار تخصص «أ» يُطلب أن يكون الطالب والمادة من «أ» معاً.</p>

    <div class="warn"><span class="ico">⚠️</span><div><b>أشهر خطأ:</b> اختيار تخصص ثم البحث عن مادة عامة للقسم. لن تظهر المادة لأن القائمة تعرض مواد التخصص فقط. اترك التخصص فارغاً لإدخال المواد العامة.</div></div>
    <div class="tip"><span class="ico">💡</span><div>الطالب الذي لا يظهر في التخصص قد يكون بلا تخصص مسجّل في بياناته. صحّح بياناته من شؤون الطلاب أولاً، ثم أعد البحث.</div></div>
</section>

<!-- 5 -->
<section id="enter">
    <h2><span class="num">5</span> إدخال درجة المادة</h2>
    <h3>ماذا أكتب وكيف يُحفظ؟</h3>
    <ul>
        <li>اكتب الدرجة <b>أرقاماً فقط</b> في خانة «الامتحان» ثم اضغط <kbd>Enter</kbd>. يُحفظ فوراً.</li>
        <li><b>الكسور</b> تُقرَّب لأقرب عدد صحيح (مثال: 72.6 تصبح 73).</li>
        <li><b>لمسح الدرجة</b> اترك الخانة فارغة.</li>
        <li><b>درجة الأعمال</b> تظهر فقط للمادة التي نسبة امتحانها أقل من 100%.</li>
    </ul>

    <h3>الحدود المسموحة</h3>
    <table>
        <tr><th>الحالة</th><th>الحد</th><th>مثال</th></tr>
        <tr><td>درجة الامتحان</td><td>من 0 إلى نسبة امتحان المادة (غالباً 100)</td><td>مادة امتحانها 60: الحد الأقصى 60</td></tr>
        <tr><td>درجة الأعمال</td><td>من 0 إلى (100 − نسبة الامتحان)</td><td>امتحان 60 ← الأعمال حتى 40</td></tr>
        <tr><td>الدور «الملحق»</td><td>لا تتجاوز 50 (تُقصّ تلقائياً إلى 50)</td><td>أدخلت 70 ← تُحفظ 50</td></tr>
        <tr><td>سالبة أو غير رقمية</td><td class="no">مرفوضة</td><td>-5 أو «abc»</td></tr>
    </table>

    <div class="danger"><span class="ico">⛔</span><div><b>مقفلة:</b> بعد اعتماد النتائج نهائياً تُقفل الدرجات. لا يعدّلها إلا المدير الأعلى، وستظهر لك رسالة «معتمدة ومقفلة».</div></div>
</section>

<!-- 6 -->
<section id="grade">
    <h2><span class="num">6</span> كيف يُحسب التقدير والنقاط؟</h2>
    <p><b>المجموع = درجة الامتحان + درجة الأعمال</b> (إن وُجدت)، ثم يُبحث عنه في جدول التقديرات:</p>
    <table class="center">
        <tr><th>المجموع</th><th>0 – 49</th><th>50 – 59</th><th>60 – 64</th><th>65 – 69</th><th>70 – 74</th><th>75 – 79</th><th>80 – 100</th></tr>
        <tr><th>التقدير</th><td><span class="tag r">F</span></td><td><span class="tag a">D</span></td><td><span class="tag s">C</span></td><td><span class="tag s">C+</span></td><td><span class="tag g">B</span></td><td><span class="tag g">B+</span></td><td><span class="tag g">A</span></td></tr>
        <tr><th>النقاط</th><td>0</td><td>2.00</td><td>2.50</td><td>2.75</td><td>3.00</td><td>3.50</td><td>4.00</td></tr>
    </table>
    <p style="font-size:14px;color:var(--muted)">الأرقام للخطة الافتراضية للطلاب. خطة بعض الطلاب قد تختلف، والنظام يستخدم خطة كل طالب.</p>

    <h3>حالة خاصة: الملحق</h3>
    <div class="note"><span class="ico">ℹ️</span><div>في الدور «الملحق»، من حصل على <b>50</b> (الحد الأقصى) يظهر تقديره <span class="tag a">*D</span> بنقاط <b>2.00</b> ثابتة، أي «نجاح بالملحق».</div></div>

    <h3>جرّب بنفسك (حاسبة تجريبية، لا تحفظ شيئاً)</h3>
    <div class="calc">
        <label>الدرجة: <input id="cMark" type="number" min="0" max="100" value="65"></label>
        &nbsp;
        <label>الدور:
            <select id="cRound"><option value="1">الأساسي</option><option value="2">الملحق</option></select>
        </label>
        <div class="out" id="cOut"></div>
    </div>
</section>

<!-- 7 -->
<section id="recom">
    <h2><span class="num">7</span> المعدل والتوصية: متى تظهر وما معناها؟</h2>
    <p>بعد <b>كل حفظ</b> يعيد النظام حساب نتيجة الطالب كلها من جميع مواده:</p>
    <div class="flow">
        <span class="n">درجة جديدة</span><span class="ar">←</span>
        <span class="n">تقدير + نقاط</span><span class="ar">←</span>
        <span class="n">المعدل = مجموع (النقاط × الساعات) ÷ مجموع الساعات</span><span class="ar">←</span>
        <span class="n">التوصية حسب اللائحة</span>
    </div>

    <div class="warn"><span class="ico">⚠️</span><div><b>«توصية افتراضية» ليست خطأ:</b> تظهر ما دامت درجات الطالب في مواد الفصل لم تكتمل (مثل المثال في الصورة). تتحول للتوصية الصحيحة بعد إدخال آخر مادة له.</div></div>

    <h3>قاعدة التوصية بعد اكتمال الدرجات</h3>
    <table>
        <tr><th>الدور</th><th>الحالة</th><th>التوصية</th></tr>
        <tr><td rowspan="5">الأساسي</td><td>لا رسوب ولا غياب</td><td><span class="tag g">نجاح (1005)</span></td></tr>
        <tr><td>ساعات الرسوب 75% أو أكثر</td><td><span class="tag r">فصل (4004)</span></td></tr>
        <tr><td>ساعات الرسوب من 50% إلى أقل من 75%</td><td><span class="tag r">إعادة فصل (2002)</span>، أو فصل (4004) إن سبقت له إعادة</td></tr>
        <tr><td>أقل من ذلك مع رسوب فقط</td><td><span class="tag a">ملحق (4001)</span></td></tr>
        <tr><td>غياب بعذر فقط / غياب مع رسوب</td><td><span class="tag a">بديل (3001)</span> / <span class="tag a">بديل + ملحق (3004)</span></td></tr>
        <tr><td rowspan="3">الملحق</td><td>الرسوب أكثر من 70% من الساعات</td><td><span class="tag r">إعادة (2001)</span></td></tr>
        <tr><td>من 50% إلى 70%</td><td><span class="tag r">إعادة فصل (2002)</span></td></tr>
        <tr><td>مادة واحدة راسبة / لا رسوب</td><td><span class="tag a">إزالة رسوب (4005)</span> / <span class="tag g">نجاح (1005)</span></td></tr>
    </table>
    <p style="font-size:14px;color:var(--muted)">حد النجاح للمادة هو 50. أسماء التوصيات تظهر كما هي معرّفة في النظام.</p>

    <div class="note"><span class="ico">ℹ️</span><div>إذا اخترت سبباً يحمل «قراراً» (القسم 8) فقراره <b>يحلّ محل</b> قرار اللائحة لذلك الطالب.</div></div>
</section>

<!-- 8 -->
<section id="reason">
    <h2><span class="num">8</span> الأسباب (عمود «الحالة»)</h2>
    <p>عمود «الحالة» أول عمود في الجدول. استخدمه عندما <b>لا تكون هناك درجة عادية</b> للطالب (غياب، حرمان، غش...).</p>

    <table>
        <tr><th>السبب</th><th>الأثر على الدرجة</th><th>يُحتسب الطالب</th><th>متى أستخدمه؟</th></tr>
        <tr><td>نجاح</td><td>لا يغيّر شيئاً</td><td>حسب الدرجة</td><td>تسجيل سبب للعلم فقط</td></tr>
        <tr><td>نجاح سابق</td><td>درجة 50، التقدير A، نقاط 4</td><td><span class="tag g">ناجح</span></td><td>مادة اجتازها الطالب سابقاً</td></tr>
        <tr><td>رسوب</td><td>تُلغى الدرجة ويظهر «رسوب»</td><td><span class="tag r">راسب</span></td><td>رسوب بقرار</td></tr>
        <tr><td>غياب</td><td>تُلغى الدرجة ويظهر «رسوب»</td><td><span class="tag r">راسب</span></td><td>الطالب لم يحضر الامتحان</td></tr>
        <tr><td>حرمان</td><td>تُلغى الدرجة ويظهر «رسوب»</td><td><span class="tag r">راسب</span></td><td>حرمان لنقص الحضور</td></tr>
        <tr><td>حالة غش</td><td>تُلغى الدرجة ويظهر «رسوب»</td><td><span class="tag r">راسب</span></td><td>قرار لجنة</td></tr>
        <tr><td>فصل اداري</td><td>تُلغى الدرجة ويظهر «رسوب»</td><td><span class="tag r">راسب</span></td><td>قرار إداري</td></tr>
        <tr><td>بديل</td><td>تُلغى الدرجة ويظهر «بديل»</td><td><span class="tag a">غياب بعذر (لا ناجح ولا راسب)</span></td><td>غياب بعذر مقبول، يمتحن بديلاً</td></tr>
    </table>

    <div class="warn"><span class="ico">⚠️</span><div><b>انتبه:</b> كتابة <b>درجة</b> بعد اختيار سبب <b>تمسح السبب</b> وقراره. ولإلغاء سبب اختر «اختر» (الفارغ) من القائمة.</div></div>
    <div class="note"><span class="ico">ℹ️</span><div>قائمة الأسباب وأثر كل سبب تُدار من <b>الإعدادات ← الأسباب</b> (للمشرفين). تغيير سبب لا يغيّر الدرجات المحفوظة سابقاً.</div></div>
</section>

<!-- 9 -->
<section id="stage">
    <h2><span class="num">9</span> مراحل الاعتماد: من يعدّل ومتى؟</h2>
    <div class="flow">
        <span class="n">الأستاذ</span><span class="ar">←</span>
        <span class="n">رئيس القسم</span><span class="ar">←</span>
        <span class="n">العميد / الكلية</span><span class="ar">←</span>
        <span class="n">الشؤون العلمية</span><span class="ar">←</span>
        <span class="n" style="border-color:#16a34a;color:#166534">معتمد نهائياً</span>
    </div>
    <ul>
        <li>يعدّل المادة <b>صاحب دور المرحلة الحالية فقط</b> (شارة المرحلة رقم 10).</li>
        <li>«إعتماد الإدخال» يرحّل المادة للمرحلة التالية. لا رجوع عنه إلا بصلاحية أعلى.</li>
        <li>بعد «معتمد نهائياً» لا يعدّل أحد سوى المدير الأعلى.</li>
        <li>اعتماد نتائج الكلية وتحويلها للسجل الأكاديمي (الشهادات) يتم من صفحة <b>النتائج ← الطباعة</b> بواسطة المعتمد المعيّن للكلية.</li>
    </ul>
    <div class="tip"><span class="ico">💡</span><div>إن ظهرت لك رسالة «المادة حالياً في مرحلة … ولا تملك صلاحية التعديل» فهذا طبيعي: المادة عند شخص آخر الآن.</div></div>
</section>

<!-- 10 -->
<section id="errors">
    <h2><span class="num">10</span> الرسائل الحمراء وما تعنيه</h2>
    <table>
        <tr><th>الرسالة</th><th>المعنى</th><th>ماذا أفعل؟</th></tr>
        <tr><td>المادة حالياً في مرحلة (...) ولا تملك صلاحية التعديل</td><td>المادة عند مرحلة ليست مرحلتك</td><td>انتظر وصولها أو راجع من يملك الدور</td></tr>
        <tr><td>لا تملك صلاحية على كلية هذه المادة أو قسمها أو مستواها</td><td>المادة خارج نطاقك</td><td>اطلب إضافة الكلية/القسم/الفصل لحسابك</td></tr>
        <tr><td>بيانات الطالب ناقصة: ...</td><td>الطالب بلا كلية/قسم/مستوى/دفعة/خطة دراسية</td><td>أكمل بياناته في التسجيل ثم أعد المحاولة</td></tr>
        <tr><td>الطالب لا ينتمي إلى كلية/مستوى/دفعة هذه المادة</td><td>الطالب والمادة في فصلين مختلفين</td><td>تأكد من الفصل والعام الدراسي</td></tr>
        <tr><td>قسم الطالب لا يطابق قسم المادة</td><td>المادة من قسم آخر</td><td>اختر القسم الصحيح</td></tr>
        <tr><td>الطالب ليس ضمن التخصص المختار</td><td>اخترت تخصصاً لا يتبعه الطالب</td><td>أفرغ التخصص أو اختر تخصصه الصحيح</td></tr>
        <tr><td>المادة لا تتبع التخصص المختار</td><td>المادة ليست من هذا التخصص</td><td>أفرغ التخصص أو اختر مادة التخصص</td></tr>
        <tr><td>المادة خاصة بتخصص لا ينتمي إليه الطالب</td><td>مادة تخصص لطالب من تخصص آخر أو بلا تخصص</td><td>لا تُدخل له هذه المادة</td></tr>
        <tr><td>الدرجة المدخلة غير صحيحة، يجب أن تكون رقماً</td><td>كتبت حروفاً</td><td>اكتب أرقاماً فقط</td></tr>
        <tr><td>درجة الامتحان تتجاوز الحد الأقصى (...)</td><td>أكبر من نسبة امتحان المادة</td><td>راجع نسبة الامتحان والأعمال</td></tr>
        <tr><td>هذه المادة ليس لها أعمال سنة</td><td>امتحانها 100%</td><td>اترك خانة الأعمال فارغة</td></tr>
        <tr><td>هذه النتيجة معتمدة ومقفلة نهائياً</td><td>تم اعتماد النتائج</td><td>راجع المدير الأعلى عند الحاجة لتعديل</td></tr>
        <tr><td>لا يوجد تقدير مُعرَّف للمجموع (...)</td><td>مجموع خارج جدول التقديرات</td><td>أبلغ الدعم الفني</td></tr>
    </table>
    <div class="tip"><span class="ico">💡</span><div>بعد أي رسالة حمراء تعود الخانة لقيمتها المحفوظة فعلاً. لا تقلق من «فقدان» ما كتبته.</div></div>
</section>

<!-- 11 -->
<section id="faq">
    <h2><span class="num">11</span> أسئلة شائعة</h2>
    <h3>لماذا لا يظهر اسم الطالب؟</h3>
    <p>راجع: العام الدراسي، الفصل، القسم/التخصص، وأن بيانات الطالب مكتملة، وأنه ضمن صلاحياتك. في الدور «الملحق» يظهر فقط من توصيته ملحق ورسب في المادة.</p>
    <h3>لماذا لا أجد المادة في القائمة؟</h3>
    <p>المادة تظهر فقط إن كانت <b>مفعّلة</b> وتطابق الكلية والفصل، وإن اخترت قسماً/تخصصاً فيجب أن تتبعه. جرّب إفراغ التخصص.</p>
    <h3>كتبت الدرجة ولم يتغير التقدير؟</h3>
    <p>اضغط <kbd>Enter</kbd> أو غادر الخانة وانتظر إشعار «تم الحفظ». إن لم يظهر إشعار فاقرأ الرسالة الحمراء.</p>
    <h3>لماذا التوصية «توصية افتراضية»؟</h3>
    <p>لأن درجات بقية مواد الطالب لم تُدخل بعد. ستتحول تلقائياً بعد اكتمالها.</p>
    <h3>هل يمكنني تعديل درجة بعد الاعتماد؟</h3>
    <p>لا، إلا إذا كانت المادة عند مرحلتك. وبعد الاعتماد النهائي والقفل لا يعدّل إلا المدير الأعلى.</p>
    <h3>ماذا لو أخطأت واخترت سبباً خاطئاً؟</h3>
    <p>اختر «اختر» (الفارغ) لإلغائه، ثم أدخل الدرجة أو اختر السبب الصحيح.</p>
    <h3>من أتصل به عند مشكلة؟</h3>
    <p>أرسل للدعم الفني: <b>نص الرسالة الحمراء + المادة + الرقم الجامعي للطالب + لقطة شاشة</b>. هذه المعلومات تكفي لحل المشكلة بسرعة.</p>

    <div class="note"><span class="ico">📋</span><div><b>قائمة تحقق سريعة قبل الاعتماد:</b><br>
        ☐ كل الطلاب لديهم درجة أو سبب &nbsp; ☐ لا توجد رسائل حمراء معلّقة &nbsp; ☐ درجات الملحق لا تتجاوز 50 &nbsp; ☐ التخصص مختار بشكل صحيح</div></div>
</section>

<footer>دليل المكتب الأكاديمي — إدخال الدرجات. الأرقام والأمثلة للتوضيح فقط وليست بيانات طلاب حقيقية.</footer>
</div>

<script>
(function () {
    var plan = [[80, 100, 'A', 4], [75, 79, 'B+', 3.5], [70, 74, 'B', 3], [65, 69, 'C+', 2.75], [60, 64, 'C', 2.5], [50, 59, 'D', 2], [0, 49, 'F', 0]];
    var mark = document.getElementById('cMark'), round = document.getElementById('cRound'), out = document.getElementById('cOut');
    function render() {
        var v = parseFloat(mark.value), r = round.value, msg;
        if (isNaN(v) || v < 0) { out.innerHTML = '<span class="no">اكتب رقماً صحيحاً (0 أو أكثر)</span>'; return; }
        v = Math.round(v);
        if (v > 100) { out.innerHTML = '<span class="no">أكبر من الحد الأقصى (100)</span>'; return; }
        if (r === '2') {
            if (v > 50) { v = 50; msg = ' (الملحق: تُقصّ الدرجة إلى 50)'; } else { msg = ''; }
            out.innerHTML = v >= 50
                ? 'الدرجة ' + v + msg + ' ← التقدير <span class="tag a">*D</span> ، النقاط <b>2.00</b> (نجاح بالملحق)'
                : 'الدرجة ' + v + msg + ' ← التقدير <span class="tag r">F</span> ، النقاط <b>0</b> (راسب)';
            return;
        }
        for (var i = 0; i < plan.length; i++) {
            if (v >= plan[i][0] && v <= plan[i][1]) {
                var cls = plan[i][2] === 'F' ? 'r' : (plan[i][2] === 'D' ? 'a' : 'g');
                out.innerHTML = 'الدرجة ' + v + ' ← التقدير <span class="tag ' + cls + '">' + plan[i][2] + '</span> ، النقاط <b>' + plan[i][3].toFixed(2) + '</b>';
                return;
            }
        }
    }
    mark.addEventListener('input', render); round.addEventListener('change', render); render();
})();
</script>
</body>
</html>

@endverbatim
