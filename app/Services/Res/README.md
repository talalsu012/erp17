# دليل المبرمج: إدخال الدرجات (الدرجة `mark` والسبب `reason`)

هذا الدليل يشرح كيف تعمل خلايا جدول إدخال الدرجات، وكيف تفهمها وتعدّلها وتطوّرها بخطوات بسيطة.
الصفحة: **النتائج والامتحانات ← إدخال الدرجات** (`EntryMark`).

> القاعدة الذهبية: **الصفحة للعرض فقط**. كل المنطق (الصلاحيات، التحقق، التقدير، اللائحة) في `app/Services/Res`.
> لا تضف منطقاً داخل الصفحة، لأن خصائصها العامة (Livewire) يستطيع المتصفح تعديلها.

> **تغيير اللائحة لجامعة أخرى:** كل قيم اللائحة (حد الملاحق، أرقام الأسباب، نسب الإعادة والفصل، التخرج، الغياب، نمط النظام الفصلي/السنوي)
> صارت في `config/regulations.php` وتُقرأ عبر `RegulationProfile`. الثوابت المذكورة أدناه (`PASS_MARK`, `SUPPLEMENTARY_MAX`, ...) قديمة ولا يعتمد عليها الحساب.
> راجع `Docs/ACADEMIC_REGULATIONS_CUSTOMIZATION.md`.

---

## 1. الخريطة السريعة

| الملف | دوره |
|---|---|
| `app/Filament/Clusters/Res/Pages/EntryMarks/EntryMark.php` | الفورم والجدول وأعمدة الإدخال (واجهة فقط) |
| `app/Services/Res/MarkEntryService.php` | الصلاحيات والتحقق وحفظ الدرجة والسبب ومراحل الاعتماد |
| `app/Services/Res/RegulationService.php` | بعد كل حفظ: إحصائيات الطالب (المعدل/الساعات) والتوصية حسب اللائحة |
| `app/Models/Res/Mark.php` | سجل الدرجة (`res_marks`) |
| `app/Services/Res/FinalizationService.php` | اعتماد النتائج: المعتمد والرمز والموانع واللقطة الموقّعة والقفل وتعديل المدير الأعلى |
| `app/Models/Res/{Finalization,RecordMark,RecordRecom}.php` | السجل الأكاديمي المعتمد (لقطة ثابتة تقرأ منها الشهادات) |
| `app/Traits/GuardsFinalizedRecords.php` | يمنع تعديل/حذف صف مقفل إلا من المدير الأعلى (على `Mark` و`Recom`) |
| `app/Models/Res/Reson.php` | الأسباب (`res_resons`): أثرها على الدرجة وقرارها، و`appliesScore()` |
| `app/Filament/Clusters/Res/Resources/Resons/*` | واجهة إدارة الأسباب (نموذج بثلاثة أقسام وجدول مفصّل) |
| `app/Models/Res/Grade.php` | جدول التقديرات (`res_grades`) والبحث `Grade::forMark()` |
| `app/Models/Res/Recom.php` | توصية الطالب (`res_recoms`) |
| `app/Reg/RecomCode.php` | أكواد التوصيات |
| `app/Reg/AbsStatus.php` | نوع الامتحان: `1` الأساسي، `2` الملاحق |
| `app/Reg/MarkApprovalStage.php` | مراحل الاعتماد (أدوار المستخدمين) |
| `app/Traits/HasAcademicScope.php` | نطاق المستخدم (كلية/قسم/مستوى) |
| `tests/Unit/Res/*` | اختبارات التحقق واللائحة والطباعة |

## 2. ماذا يحدث عندما تكتب في خلية؟

خلية في الجدول (EntryMark.php)
  └─ updateStateUsing ─► guard() ─► MarkEntryService::saveScore()   (عمود الدرجة)
                                  └► MarkEntryService::saveReason() (عمود السبب)

  داخل الخدمة (بالترتيب):
    1) assertCanEdit          الصلاحية على المادة + دور المرحلة الحالية
    2) assertCanAccessStudent نطاق المستخدم (كلية/قسم/مستوى) للطالب
    3) assertEligible         الطالب مؤهل لهذه المادة (بياناته، التخصص...)
    4) normalizeScore         (للدرجة فقط) تنظيف القيمة والحدود
    5) DB::transaction
         lockRow              جلب/إنشاء الصف الفريد بقفل
         ضبط الحقول           mark/cwork أو reson_code
         gradeFor             حساب التقدير والنقاط
         save
    6) RegulationService::refresh   إعادة حساب إحصائيات الطالب وتوصيته
  └─ notifySaved   إشعار واحد: التقدير | التوصية | المعدل
```

أي خطأ متوقع يُرمى كـ `DomainException` برسالة عربية جاهزة. الدالة `guard()` في الصفحة تمسكه وتعرضه إشعاراً أحمر
وتُعيد رسم الجدول (`$refresh`) لتعود الخلية إلى قيمتها المحفوظة فعلاً.

## 3. مفاهيم لازمة

- **الصف الفريد:** درجة واحدة لكل
  `university_no + subject_code + level_code + batch_code + semester + examType`
  (فهرس `unique_marks`). `semester` يؤخذ من `subject.semester_id`.
  `lockRow()` يجلبه بقفل، وينشئه عند أول إدخال، ويستعيده إن كان محذوفاً (`SoftDeletes`).
- **`batch_code`**: السنة/الدفعة الدراسية للدرجة (مثل 2026)، وهي غير مستوى الطالب.
- **`examType`**: `1` الامتحان الأساسي، `2` الملاحق. الصفحة تعرض طلاب نوع واحد في كل مرة.
- **`reson_code`**: سبب اختياري للصف. إن حدد السبب درجة/تقديراً/نقاطاً نُسخت إلى الصف، وإن حدد قراراً غلب قرار اللائحة (انظر القسم 4).
- **الجدول يعرض طلاباً (`Student`)** لا درجات: الدرجات تُحمَّل مسبقاً (`with('marks')`) مقيَّدة بالمادة والدفعة والمستوى والنوع،
  لذلك `$record->marks->first()` هو درجة هذا الطالب في المادة المعروضة.

---

## 4. عمود السبب `reason`

كل سبب في `res_resons` يحمل **أثره على الدرجة** (`mark` و`grade` و`point`) و**قراره** (`recom_code` من `res_ltrecoms`).
عند اختيار السبب تُنسخ هذه القيم إلى `res_marks`، ويغلب قراره قرار اللائحة. تُدار الأسباب من
**الإعدادات ← الأسباب** (`ResonResource`)، ولا حاجة لتعديل الكود لإضافة سبب.

### الكود في الصفحة

```php
SelectColumn::make('reason')
    ->options(fn () => $this->reasonOptions())                       // Reson::pluck('name_ar','code') مرة لكل طلب
    ->getStateUsing(fn (Student $record) => $record->marks->first()?->reson_code)
    ->updateStateUsing(fn (Student $record, $state) => $this->guard(
        fn (Subject $subject) => app(MarkEntryService::class)->saveReason(
            auth()->user(), $record, $subject, $this->data['batch'], $this->data['absent'], $state, $this->data['special'] ?? null,
        )
    ))
```

### أعمدة جدول الأسباب

| العمود | المعنى |
|---|---|
| `mark` | الدرجة التي تُكتب في `res_marks.mark` (0–100، اختيارية) |
| `grade` | التقدير النصي (حتى 20 حرفاً، اختياري) |
| `point` | النقاط (0–99.99، اختيارية) |
| `recom_code` | القرار (يُحمَّل من `res_ltrecoms`، اختياري؛ فارغ = حسب اللائحة) |

الدالة `Reson::appliesScore()` تُرجع true إن حُدد **واحد على الأقل** من `mark` و`grade` و`point`.

### ماذا يفعل `saveReason`
1. الفحوص الثلاثة (القسم 2).
2. `$state` فارغ = **إلغاء السبب**. رمز غير موجود في `res_resons` ← `DomainException`.
3. داخل معاملة: يجلب الصف بقفل، ثم:

| الحالة | ما يحدث |
|---|---|
| السبب كان قد **فرض قيماً** (سبب سابق `appliesScore`) | تُمسح `mark/cwork/grade/point` أولاً، فلا تبقى قيم السبب القديم كأنها درجة حقيقية |
| السبب الجديد `appliesScore` | تُنسخ `mark/grade/point` من السبب (غير المحدد منها يصبح `null`) وتُلغى `cwork`. إن حُددت **درجة بلا تقدير أو نقاط** يُكمَل الناقص من جدول التقديرات (`gradeFor`) |
| السبب الجديد لا يحدد شيئاً (مثل 1001) | يُضبط `reson_code` فقط، وتبقى بقية الحقول (فارغة إن كان قبله سبب فرض قيماً) |
| **إلغاء** السبب | يُعاد حساب التقدير من الدرجة إن وُجدت، وإلا يصبح التقدير والنقاط فارغين |

4. `RegulationService::refresh` يعيد حساب الطالب ويُرجع `['mark' => ..., 'recom' => ...]` للإشعار.

### قرار السبب (`recom_code`)
بعد حساب قرار اللائحة، `RegulationService::reasonDecision` يبحث في **آخر صف لكل مادة** للطالب (في السنة والمستوى) عن سبب له `recom_code`:
- وُجد ← هذا القرار **يحل محل** قرار اللائحة في `res_recoms.recom_code`، ولا تُطبَّق قاعدة «المعدل صفر للملحق».
- تعدد ← يغلب **الأحدث حفظاً** (`updated_at` ثم `id`).
- زال السبب (إلغاء أو إدخال درجة بعده) ← يعود القرار إلى اللائحة تلقائياً عند الحفظ التالي.
- الإحصائيات (المعدل والساعات) لا تتأثر بالقرار، وإنما بقيم الدرجة التي نسخها السبب.

### الأسباب الموجودة اليوم (`res_resons`)
القيم بعد ترقية الجدول: الأسباب برمز أكبر من 2000 أُعطيت `grade = description` فبقي سلوكها كما كان تماماً.

| الرمز | الاسم | الدرجة | التقدير | النقاط | القرار | الأثر |
|---|---|---|---|---|---|---|
| 1001 | نجاح | — | — | — | اللائحة | يحفظ السبب فقط |
| 1002 | نجاح سابق | — | — | — | اللائحة | يحفظ السبب فقط |
| 2001–2005 | رسوب / غياب / حرمان / حالة غش / فصل اداري | فارغة | رسوب | فارغة | اللائحة | تُلغى الدرجة ويظهر «رسوب»، وتُحتسب راسبة |
| 3001 | بديل | فارغة | بديل | فارغة | اللائحة | تُلغى الدرجة، وتُحتسب **غائباً بعذر** (`s_abs`) |

### رموز خاصة في `RegulationService`
- `REASON_ABSENT = 3001`: الغياب بعذر (لا ناجح ولا راسب). مربوط بالرمز وليس بالقيم، فسبب جديد لا يُحتسب غياباً ما لم تغيّر هذا الثابت.
  (تنبيه: `ResResonsSeeder` ينشئ «بديل» برمز 4001 بينما القاعدة الحالية 3001.)
- `REASON_EXCLUDED = 21`: المادة تخرج من العدد والحساب كلياً. **لا يوجد سبب بالرمز 21 في الجدول**، فأضفه من واجهة الأسباب إن أردت هذه القاعدة.

### ملاحظات يجب أن تعرفها
- إدخال **درجة** بعد سبب يمسح السبب (`reson_code = null`) وبالتالي قراره. والسبب 3001 السابق في الملاحق يمنع التقدير `*D` (القسم 5).
- الإحصاء يحكم بالقيم المنسوخة: مادة بسبب `mark = 50` تُحتسب **ناجحة**، وبسبب بلا درجة تُحتسب **راسبة** (إلا 3001).
  لذلك يمكنك الآن جعل «نجاح» (1001) ناجحاً فعلاً بإعطائه `mark = 50` وتقديراً ونقاطاً من واجهة الأسباب.
- تغيير قيم السبب لا يُحدّث الدرجات المحفوظة سابقاً؛ تُنسخ القيم لحظة اختيار السبب فقط.
- الصف الذي لا درجة فيه ولا سبب يُعدّ "لم يُدخل" ولا يدخل الإحصاء (`refresh` يستبعده).

---

## 5. عمود الدرجة `mark` (وأعمال السنة `cwork`)

### الكود في الصفحة

```php
$this->scoreColumn('mark', __('res.exam')),

protected function scoreColumn(string $field, string $label): TextInputColumn
{
    return TextInputColumn::make("marks.{$field}")
        ->rules(['nullable', 'numeric', 'min:0'])          // تحقق سريع في الواجهة فقط
        ->getStateUsing(fn (Student $record) => $record->marks->first()?->{$field})
        ->updateStateUsing(fn (Student $record, $state) => $this->guard(
            fn (Subject $subject) => app(MarkEntryService::class)->saveScore(
                auth()->user(), $record, $subject, $this->data['batch'], $this->data['absent'], $field, $state, $this->data['special'] ?? null,
            )
        ));
}
```

نفس الدالة تبني عمود `cwork`. الفرق في الصفحة فقط:
- **يظهر** فقط إن كانت نسبة امتحان المادة أقل من 100 (`subject.exam < 100`).
- **يُعطَّل** في الملاحق إلا لمن رسب (تقديره من قائمة الرسوب).

### ماذا يفعل `saveScore`
1. الفحوص الثلاثة (القسم 2).
2. `normalizeScore` ينظّف القيمة:

| القيمة | النتيجة |
|---|---|
| فارغة | `null` (مسح الخانة) |
| غير رقمية | خطأ «يجب أن تكون رقماً» |
| عشرية | تُقرَّب لأقرب **عدد صحيح** (العمود `integer`، فنقرّب صراحةً بدل التقريب الصامت) |
| سالبة | خطأ |
| `cwork` في مادة امتحانها 100% | خطأ «ليس لها أعمال سنة» |
| `cwork` أكبر من `100 - exam` | خطأ بالحد الأقصى |
| `mark` أكبر من `exam` (الافتراضي 100) | خطأ بالحد الأقصى |
| `mark` في **الملاحق** | يُقصّ إلى `regulations.supplementary.max_mark` (الافتراضي 50) |

3. داخل معاملة: `lockRow` ثم يضبط الحقل ويصفّر `reson_code`، ثم `gradeFor`:
   - لا `mark` ولا `cwork` ← التقدير والنقاط `null`.
   - `المجموع = mark + cwork` ثم `Grade::forMark(المجموع, خطة الطالب)`. الخطة `student.stady_code` وإلا `Grade::DEFAULT_PLAN`.
   - لا نطاق مطابق في `res_grades` ← خطأ «لا يوجد تقدير».
   - **الملاحق:** إن كانت `mark >= 50` والسبب السابق ليس 3001 ← التقدير `*D` والنقاط `2.00` (ثابتان).
   - غير ذلك: `grade.title` و`grade.point`.
4. `save()` ثم `RegulationService::refresh`.

### جدول التقديرات (الخطة الافتراضية)
`A 80–100 (4.0)` ، `+B 75–79 (3.5)` ، `B 70–74 (3.0)` ، `+C 65–69 (2.75)` ، `C 60–64 (2.5)` ، `D 50–59 (2.0)` ، `F 0–49 (0)`.
(`+B` تعني B+؛ قوالب الطباعة تقلبها للعرض.) النطاقات أعمدة `tinyint` في `res_grades`.
`Grade::forMark` يخزّن الجدول مؤقتاً ساعة، ويُمسح الكاش تلقائياً عند أي حفظ/حذف/استعادة لتقدير.

### بعد الحفظ: اللائحة (`RegulationService::refresh`)
- يجلب آخر صف لكل مادة للطالب (الأحدث `id` يغلب، فالملحق يغلب الأساسي).
- `statistics()` يحسب: `countSubject, sumMarks, s_pass, s_fail, s_abs, crch, crch_fail, points, gpa`.
- `decide()` دالة **نقية** (بلا قاعدة بيانات) تُرجع `RecomCode` من نوع الامتحان والإحصائيات والتاريخ السابق.
  الحد: النجاح من `res_grades` (احتياطي `regulations.pass_mark = 50`). الأساسي: بلا رسوب/غياب `1005`، رسوب ساعات أكثر من ثلثين `4004` (السنة النهائية `2002`)،
  أكثر من ثلث `2002` (أو `4004` إن سبقت إعادة)، وأقل من ذلك: غياب فقط `3001`، غياب مع رسوب `3004`، رسوب فقط `4001`.
  الملاحق: > 70% `2001`، 50–70% `2002`، رسوب مادة واحدة `4005`، وإلا `1005` (النسب في `regulations.supplementary`).
- يحفظ النتيجة في `res_recoms` (`updateOrCreate`) ويعيدها لإشعار الحفظ.

---

## 6. اعتماد النتائج والسجل الأكاديمي (قفل نهائي + شهادات)

بعد إنهاء الأساسي والملحق يعتمد **معتمد الكلية** نتائج مجموعة (**كلية + قسم + مستوى + دفعة**) برمزه الشخصي. تُقفل النتائج نهائياً
وتُنسخ إلى سجل أكاديمي ثابت تقرأ منه الشهادات. الزر في **النتائج والامتحانات ← النتائج** بجانب طباعة النتيجة، ويظهر للمعتمد المعيَّن للكلية فقط.

### الجداول
| الجدول | الدور |
|---|---|
| `users.approval_code_hash` | رمز الاعتماد الشخصي: **HMAC** بمفتاح التطبيق، فريد (لا يتكرر بين شخصين)، لا يُعرض بعد الحفظ |
| `reg_faculties.results_approver_id` | **معتمد واحد لكل كلية** (يعيّنه المدير الأعلى من نموذج الكلية) |
| `res_finalizations` | اعتماد/نسخة: المجموعة، `version`، `kind` (`approval` أو `amendment`)، `is_active`، من وأين ومتى، `digest` و`signature` |
| `res_record_marks` | لقطة الدرجات: **آخر درجة لكل مادة** (الملحق يغلب الأساسي) مع اسم المادة وساعاتها وقت الاعتماد |
| `res_record_recoms` | لقطة التوصيات (المعدل والإحصائيات والقرار واسمه) |

### الخطوات عند الضغط على «اعتماد»
1. `canApprove`: المستخدم هو معتمد هذه الكلية ولديه رمز.
2. `verifyCode`: مقارنة الرمز بـ`hash_equals`. **5 محاولات خاطئة تقفل المعتمد 15 دقيقة** (`MAX_ATTEMPTS` و`LOCKOUT_SECONDS`).
3. `blockers`: لا اعتماد إن وُجد أحد الموانع:
   - مواد لم تصل مرحلة **معتمد نهائياً** (`SubjectApprovalStatus`).
   - طلاب بتوصية `9999` (درجات ناقصة).
   - طلاب قرارهم ملحق/بديل (`4001` و`3001` و`3004`) ولا نتيجة ملحق لهم.
   - لا نتائج أصلاً، أو المجموعة معتمدة مسبقاً.
4. داخل معاملة: لقطة موقّعة (`digest` = SHA-256 لمحتواها، و`signature` = HMAC بالمعتمد ووقت الاعتماد) ثم `is_locked = true` على الدرجات والتوصيات.

### المنع والمدير الأعلى
- **منع التعديل على مستوى الموديل** (`GuardsFinalizedRecords` على `Mark` و`Recom`): أي `update/delete` لصف مقفل يرمي `DomainException`
  إلا من `super_admin`. يشمل الإدخال والاستيراد وواجهات الموارد (ولا يعتمد على إخفاء الأزرار). أزرار التعديل مخفية للمقفل، و`canEdit/canDelete` مقيدة.
- **التحديث الجماعي والاستعلامات المباشرة تتجاوز أحداث الموديل**: مثال `Mark::query()->update()` في جدول الدرجات يتخطى المقفل صراحةً.
  أي كود جديد يكتب جماعياً في `res_marks`/`res_recoms` عليه أن يستثني `is_locked`.
- `RegulationService::refresh` يترك التوصية المقفلة كما هي لغير المدير الأعلى (فلا تفشل `RefreshRecommendationsJob`).
- **تعديل المدير الأعلى**: يعدّل مباشرة. `recordAmendment` يسجّل في `activity_log` (قبل/بعد) وينشئ **نسخة جديدة مرقّمة** (`kind = amendment`)
  تحمل بقية الطلاب من النسخة السابقة وتجدّد لقطة هذا الطالب، وتبقى النسخة القديمة (`is_active = false`) للأرشيف.

### الشهادات
`TranscriptGenerator::marksFor($universityNo)` يقرأ من `RecordMark::approvedFor()` (النسخ النشطة فقط). الإعداد `results.certificates_from_record_only`
(`RESULTS_CERTIFICATES_FROM_RECORD_ONLY` في `.env`):
- `false` (انتقالي، الافتراضي): من السجل المعتمد إن وُجد للطالب وإلا من الدرجات الحالية، فلا تتعطل شهادات من لم تُعتمد مجموعتهم.
- `true`: **من السجل المعتمد وحده**؛ طالب بلا سجل معتمد تخرج شهادته فارغة. فعّله بعد اعتماد المجموعات القائمة.

### وصفات
- **تعيين معتمد لكلية:** المدير الأعلى يضبط «رمز اعتماد» للشخص من نموذج المستخدم، ثم يختاره من «معتمد النتائج» في نموذج الكلية.
- **تغيير شروط المنع:** `FinalizationService::blockers` (أضف شرطاً واحداً، ورسالته عربية).
- **إضافة حقل للقطة:** عمود في `res_record_marks`، ثم `markRow()` في الخدمة، وسيدخل البصمة تلقائياً. الشهادات تقرأ من أعمدة اللقطة.
- **التحقق من سلامة لقطة:** `FinalizationService::isIntact($finalization)` يعيد حساب البصمة من الصفوف المخزنة ويقارنها.
- **اختبارات:** `tests/Feature/FinalizationTest.php`.

---

## 7. الصلاحيات والتحقق (لا تتجاوزها)

- **`assertCanEdit`**: `super_admin` يتجاوز. غيره: يجب أن يملك نطاق المادة (`canAccessAcademic`) **وأن يحمل دور المرحلة الحالية**
  (`professor` ← `head_department` ← `dean` ← `scientific_affairs` ← `approved`). في `approved`/`rejected` لا أحد يعدّل غير المدير الأعلى.
- **`assertCanAccessStudent`**: كلية/قسم/مستوى الطالب ضمن نطاق المستخدم.
- **`assertEligible`** (للجميع بمن فيهم المدير الأعلى): بيانات الطالب الأساسية و`stady_code` موجودة، وتطابق كلية/مستوى/دفعة/قسم المادة،
  والتخصص: عند اختيار تخصص يجب أن يتبعه الطالب والمادة، وبدون اختيار تخصص المادة ذات التخصص تتطلب الطالب فيه.
- **`lockRow`**: صف `is_locked = true` ← خطأ «مقفلة».
- الاستعلام نفسه (`studentsQuery`) يعيد استعلاماً فارغاً إن كان المستخدم خارج النطاق، فلا تثق بما في `$this->data` أبداً.

---

## 8. وصفات التعديل (خطوات بسيطة)

### أ) إضافة سبب جديد أو تعديل أثره
1. من **الإعدادات ← الأسباب** أضف سبباً: الرمز والاسم والوصف.
2. في «أثر السبب على الدرجة» حدّد ما يلزم، وفي «القرار» اختر القرار من القائمة (محمّلة من `res_ltrecoms`):

| أريد أن... | اضبط |
|---|---|
| يلغي الدرجة ويظهر تقدير مخصص | `mark = 0` (أو اتركها فارغة) + `grade = النص` + `point = 0` |
| يعطي درجة ويُحسب تقديرها من الجدول | `mark` فقط |
| يحفظ السبب دون المساس بالدرجة | اترك الدرجة والتقدير والنقاط فارغة |
| يفرض قراراً على الطالب | اختر «القرار» |
| يُحتسب غياباً بعذر | مربوط بالثابت `REASON_ABSENT = 3001` في `RegulationService`؛ غيّره أو عمّمه لقائمة رموز، وحدّث `gradeFor` الذي يقارن به أيضاً |

3. الاستيراد من Excel: `ResonImporter` يقبل الأعمدة `code, name_ar, name_en, description, mark, grade, point, recom_code`.
4. لا تعدّل `EntryMark.php`: القائمة تُقرأ من الجدول. أضف اختباراً في `tests/Feature/ReasonEntryTest.php`.

### ب) تغيير سقف درجة الملاحق
غيّر `regulations.supplementary.max_mark` و`regulations.supplementary.pass_point` في `config/regulations.php`.

### ج) تغيير حد النجاح
حد النجاح يُشتق من أدنى تقدير ناجح في `res_grades`، والاحتياطي `regulations.pass_mark`. للتقديرات عدّل `res_grades`.

### د) تعديل جدول التقديرات
عدّل صفوف `res_grades` (لكل خطة `plan`). الكاش يُمسح تلقائياً. تأكد ألا تبقى فجوات بين `lower` و`upper`.

### هـ) إضافة عمود إدخال جديد (مثل درجة العملي `practical`)
1. migration تضيف العمود إلى `res_marks` (`Mark` يستعمل `$guarded = []` فلا تعديل على `$fillable`).
2. في `normalizeScore` أضف فرعاً لحدود العمود الجديد.
3. في `gradeFor` أدخله في المجموع.
4. في الصفحة أضف `$this->scoreColumn('practical', 'العملي')` (مع `visible` إن لزم). لا حاجة لدالة حفظ جديدة، فـ`saveScore` عام لأي `$field`.
5. أضف الحقل لقوالب الطباعة إن أردت إظهاره، واختبر.

### و) إضافة قاعدة تحقق جديدة
- متعلقة **بقيمة الدرجة** ← داخل `normalizeScore`.
- متعلقة **بالطالب أو المادة** ← داخل `assertEligible`.
- متعلقة **بالمستخدم/المرحلة** ← داخل `assertCanEdit`.
- ارمِ دائماً `DomainException` برسالة عربية، وأضف اختباراً في `tests/Unit/Res/MarkEntryEligibilityTest.php`.

### ز) تغيير طريقة التقريب
في `normalizeScore` سطر `(int) round(...)`. للسماح بالكسور: غيّر العمود إلى `decimal`، وحدّث حدود `res_grades` (هي `tinyint` اليوم) وأعد النظر في `gradeFor`.

### ح) تعديل قواعد التوصية
عدّل `decideBasicExam` / `decideSupplementaryExam` في `RegulationService`. اكتب الاختبار أولاً في `RegulationServiceTest.php`
(الدالة نقية، لا تحتاج قاعدة بيانات). أكواد التوصيات في `RecomCode`.

### ط) قفل درجات
اضبط `is_locked = true` على الصف. أي حفظ لاحق يرفض برسالة «مقفلة».

---

## 9. الاختبار والتشخيص

```bash
php artisan test --compact tests/Unit/Res          # التحقق واللائحة والطباعة
php artisan test --compact tests/Feature/AcademicScopeTest.php
php artisan test --compact tests/Feature/ReasonEntryTest.php     # أثر الأسباب وقراراتها
php artisan test --compact tests/Feature/FinalizationTest.php    # اعتماد النتائج والقفل والسجل الأكاديمي
vendor/bin/pint --format agent <الملفات التي عدّلتها>
```

- لا تُختبر الواجهة هنا؛ اختبر **الخدمة** مباشرة (نقية ما أمكن). الحفظ الفعلي يحتاج جداول MariaDB، فجرّبه داخل
  `DB::beginTransaction() ... DB::rollBack()` على بيئة التطوير.
- الإشعار الأحمر يعني `DomainException` متوقعة (اقرأ رسالتها). أي خطأ آخر ينهار الطلب ويظهر في `storage/logs`.
- الخلية تعود لقيمتها بعد الخطأ لأن `guard()` يرسل `$refresh`؛ إن لم تعد فتحقق أن الخطأ `DomainException` وليس استثناءً آخر.

## 10. قائمة مراجعة قبل الدمج
- [ ] لا منطق جديداً داخل `EntryMark.php`، بل في الخدمة.
- [ ] كل رفض يمر عبر `DomainException` برسالة عربية.
- [ ] تحقق جديد ← اختبار في `tests/Unit/Res`.
- [ ] تغيير في التقدير أو اللائحة ← اختبار `RegulationServiceTest` ونظرة على أثره على الكشوف المطبوعة.
- [ ] تغيير في سلوك الأسباب ← اختبار في `tests/Feature/ReasonEntryTest.php`.
- [ ] كتابة جماعية جديدة في `res_marks`/`res_recoms` ← استثنِ `is_locked` (الاستعلامات الجماعية تتجاوز الحارس).
- [ ] شغّلت `pint` والاختبارات.

---

## 8. الحرمان بالغياب (لائحة 2020: م5 دبلوم، م8 بكالريوس)

| الملف | دوره |
|---|---|
| `app/Services/Res/AbsenceService.php` | يحسب نسبة الغياب ويقترح الحالة، ويعتمد الحرمان أو يرفعه بقرار المجلس |
| `app/Reg/AbsenceStatus.php` | `normal` · `warning` · `deprivation_proposed` · `deprived` · `lifted` |
| `app/Models/Res/AbsenceDecision.php` | جدول `res_absence_decisions` (فريد: طالب + مقرر + دفعة + فصل) |
| `app/Console/Commands/Res/EvaluateAbsence.php` | الأمر `res:evaluate-absence` ويعمل يومياً 02:00 (`routes/console.php`) |
| `app/Filament/Clusters/Res/Resources/AbsenceDecisions/*` | صفحة «الحرمان بالغياب»: اعتماد الحرمان ورفعه |
| `app/Policies/Res/AbsenceDecisionPolicy.php` | الصلاحيات `ViewAny/View/Update:AbsenceDecision` |

### القواعد
- نسبة الغياب = المحاضرات المسجّلة غياباً ÷ كل المحاضرات المسجّلة للطالب في المقرر (من `att_attendances` عبر `att_lectures`).
- `excused` يُحسب غياباً (اللائحة: «بصرف النظر عن سبب الغياب»)، وتتحكم به الثابتة `COUNT_EXCUSED_AS_ABSENT`.
- **≥ 15%** إنذار (`warning`، ويُسجَّل `warned_at` مرة واحدة). **> 25%** حرمان **مقترح** (`deprivation_proposed`).
- الحرمان قرار مجلس الكلية ونهائي: النظام يقترح فقط، و`approveDeprivation` لا يعتمد إلا مقترحاً.
- `lift` (رفع الحرمان) يلزمه سبب مكتوب. القرارات المعتمدة/المرفوعة **لا يعيد الحساب الآلي كتابتها**.
- **منع الإدخال:** `MarkEntryService::saveScore` يرمي `DomainException` عند إدخال `mark` لطالب `deprived` في المقرر
  (`isBarredFromFinalExam`). تعيين السبب (`saveReason`) و`cwork` غير مقيدين.

### وصفات
- **تغيير النسب:** `regulations.absence.warning_pct` و`regulations.absence.deprivation_pct` في `config/regulations.php`.
- **تشغيل يدوي:** `php artisan res:evaluate-absence --batch=2026 --from=2026-09-01 --to=2026-12-31`.
- **الدفعة والفصل** لكل سجل تؤخذان من `students.batch_code` و`subjects.semester_id`.
- **اختبارات:** `tests/Unit/Res/AbsenceServiceTest.php`.

## 9. التقديرات والتخرج

- **`Grade`** (`app/Models/Res/Grade.php`): `forPlan` و`forMark` و`lowestPassing` و`DEFAULT_PLAN` (`20060001`)، مع كاش ساعة يُمسح عند أي حفظ/حذف/استعادة.
  كانت هذه الدوال مستدعاة من `MarkEntryService` و`RegulationService` وغير موجودة في النموذج.
- **`GraduationService`**: `cumulativeGpa` (آخر درجة لكل مقرر، بعد استبعاد المستبعد والغائب بعذر) و`meetsGraduation` (≥ 2.00)
  و`classify($gpa, $honours)` للتصنيف العام (ممتاز 3.5 / جيد جداً 3.0 / جيد 2.5 / مقبول 2.0) ومراتب الشرف للبكالريوس.
  الخدمة لم تُربط بعد بالشهادات أو بقرار التخرج. اختبارها: `tests/Unit/Res/GraduationServiceTest.php`.

### سلّم التقديرات والعتبات حسب اللائحة (مُطبَّق)
- **السلّم:** `database/seeders/RegulationGradesSeeder.php` (الثابتة `SCALES`) يستبدل `res_grades` للخطتين (`20060001` بكالريوس، `20060002` دبلوم، حد نجاحه 40)
  بعد حفظ نسخة JSON من القيم القديمة في `storage/app/private/regulation-backups/`. **لا يمس `res_marks`.**
  التشغيل: `php artisan db:seed --class=RegulationGradesSeeder`. وللرجوع: أعد القيم من ملف النسخة.
- **إعادة حساب الدرجات القائمة:** `php artisan res:recalculate-grades` (عرض الفروق فقط) ثم `--apply`. خياره `--plan=`.
  يتخطى `is_locked` وما له سبب (`reson_code`)، ويحفظ نسخة JSON (id وقديم/جديد) قبل الكتابة، ثم يعيد `RegulationService::refresh`
  لكل طالب/فصل متأثر (والتوصية المقفلة تبقى كما هي).
- **العتبات:** `config/results.php` ← `regulation`: `repeat_above` = `[1,3]` و`dismiss_above` = `[2,3]` و`final_year_levels` = `2`.
  المقارنة بالضرب التبادلي (4 من 12 ليست «أكثر من الثلث»).
- **قرار الامتحان الأساسي (`decideBasicExam`):** رسوب > ثلثي الساعات = فصل، > الثلث = إعادة فصل، إعادة الفصل مرتين = فصل، وغير ذلك ملحق/بديل.
- **السنة النهائية:** آخر `final_year_levels` مستويات من مواد الكلية والقسم والتخصص (`RegulationService::isFinalYear`).
  فيها لا فصل بالرسوب، وتُعاد عند تجاوز الثلثين فقط، وما دون ذلك ملحق.
- **لم يتغير:** قرارات الملاحق (`decideSupplementaryExam`) وأكواد التوصيات.
