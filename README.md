# ERP جامعة البحر الأحمر

نظام إدارة أكاديمي ومالي (التسجيل، النتائج، الشهادات، الفواتير والمدفوعات، الإشراف الأكاديمي) مبني على
**Laravel 12** و**Filament 5** و**Livewire 4**، مع واجهة عربية (RTL) وقاعدة بيانات MariaDB.

## المتطلبات

- PHP 8.2+ (يُختبر في CI على 8.4) بالإضافات: `mbstring`, `intl`, `gd`, `zip`, `pdo_mysql`
- MariaDB 10.6+ (أو MySQL 8)
- Composer 2 وNode 20+ (لبناء الأصول)
- Chromium/Puppeteer فقط إن استعملت توليد PDF عبر Browsershot

## التثبيت المحلي

```bash
git clone <repository> erp15redsea && cd erp15redsea
composer install
cp .env.example .env          # ثم عدّل بيانات قاعدة البيانات
php artisan key:generate
npm install && npm run build
php artisan storage:link
```

للتطوير المحلي اضبط في `.env`: `APP_ENV=local` و`APP_DEBUG=true`.
لوحة Filament تمنع الدخول خارج البيئة `local` ما لم يطبّق نموذج `User` واجهة `FilamentUser`
(راجع قسم «ملاحظات معروفة»).

### قاعدة البيانات

> **تنبيه:** ملفات `database/migrations` **لا تبني المخطط كاملاً** (جداول مثل `invoices` و`students`
> الكاملة وعمود `reg_levels.parent_code` غير مُنشأة بها). لا تعتمد على `migrate` وحده لإنشاء قاعدة جديدة؛
> استورد نسخة من قاعدة البيانات الحالية ثم شغّل `php artisan migrate` للتحديثات اللاحقة.

بعد استيراد القاعدة:

```bash
php artisan migrate
php artisan shield:generate --all    # عند إضافة Resources جديدة
```

بيانات مرجعية للتجربة: `php artisan db:seed --class=RegSettingsTableSeeder` ثم `RegLtColumns`
(تحذف الجداول المرجعية وتعيد ملأها، لا تشغّلها على قاعدة إنتاج).

## التشغيل

```bash
php artisan serve                      # الخادم
php artisan queue:work --tries=3       # الطابور: الاستيراد والتصدير والنسخ الاحتياطي
npm run dev                            # أو npm run build
```

أو `composer run dev` لتشغيلها معاً. لوحة الإدارة على `/admin`.

## الاختبارات وجودة الكود

```bash
php artisan test --compact             # Pest (SQLite في الذاكرة)
vendor/bin/pint                        # تنسيق الكود (--test للفحص فقط)
vendor/bin/phpstan analyse             # Larastan مستوى 5 مع baseline
vendor/bin/rector process --dry-run    # اقتراحات التحديث
```

- اختبارات Filament تستعمل المخطط المستخرج في `tests/Support/erp_schema.php`
  (الدالة `migrateErpSchema()` في `tests/Pest.php`) لأن الـ migrations ناقصة.
- `phpstan-baseline.neon` يضم الأخطاء القديمة فقط؛ أي خطأ جديد يفشل الفحص.
  لا تُعد توليد الـ baseline لإخفاء خطأ جديد.
- يعمل GitHub Actions (`.github/workflows/ci.yml`) عند كل push وpull request: Pint ثم Larastan ثم Pest.

## بنية المشروع

| المسار | المحتوى |
|---|---|
| `app/Filament/Clusters/{Reg,Res,Invoices,Cert,AcademicSupervision}` | وحدات اللوحة (Resources وPages) |
| `app/Filament/{Imports,Exports}` | Importer/Exporter لـ Filament (تعمل عبر الطابور) |
| `app/Actions` | منطق الأعمال المستدعى من واجهة Filament |
| `app/Services` | خدمات النتائج والدفع والشهادات والقبول |
| `app/Reg` | الـ Enums (`LtGroup`, `Currency`, ...) |
| `app/Policies` | سياسات Shield بصيغة `Ability:Model` |
| `routes/api.php` | API البنك (Sanctum) لاستعلام ودفع الرسوم |
| `lang/{ar,en}` | الترجمات |

## بوابة الطالب

واجهة منفصلة للطلاب (دخول وتسجيل واستعادة كلمة المرور ولوحة بياناتهم الأكاديمية والمالية)، بينما تبقى الإدارة على Filament.

- **العزل:** guard مستقل `student` وprovider `students` على جدول `users_std` وbroker `students`
  بجدول `student_password_reset_tokens`. جلسة الطالب لا تفتح `/admin` والعكس.
- **العنوان:** `STUDENT_PORTAL_DOMAIN` (نطاق فرعي، موصى به) وإلا المسار `/student`. المسارات في `routes/student.php`
  وتحمل الأسماء `student.*`.
- **التسجيل المحصّن:** لا يتم إلا بتطابق **الرقم الجامعي مع رقم الهوية (`students.nationaltyno`)** في السجل الأكاديمي،
  مع حد معدل لكل IP ولكل رقم جامعي، وردّ عام موحّد لا يكشف وجود الطالب أو اسمه. الاسم يُؤخذ من السجل لا من المتصفح.
  طالب بلا رقم هوية مسجّل لا يستطيع التسجيل حتى تُدخله الإدارة.
- **الصور:** تُحفظ على القرص الخاص باسم عشوائي وتُعرض لصاحبها فقط عبر `student.photo`.
- **النتائج:** لا تظهر إلا إذا كان `students.student_account` = 4002001 (براءة الذمة المالية)، وللسجلات `is_visible`.
- **حذف الحساب:** لا يملكه الطالب؛ يطلبه من الإدارة. الإدارة تدير الحسابات من «حسابات بوابة الطلاب»
  (إرسال رابط التعيين، تصحيح البريد، الحذف) بصلاحيات `*:StudentAccount`.
- **الأصول:** مدخل Vite مستقل `resources/{css,js}/student.*` (Tailwind 4 وAlpine)، فنفّذ `npm run build` بعد النشر.
- **الترويسات الأمنية:** `StudentSecurityHeaders` (HSTS عند HTTPS وX-Frame-Options وCSP بنمط المراقبة) على مسارات الطالب فقط.

## نقاط API (البنك)

- `POST /api/login` (محدود بـ 5 محاولات/دقيقة) يعيد توكن لمن يملك دور `BankApiAccess`.
- `GET /api/APITIQ/{university_no}` استعلام الرسوم، و`POST /api/APITIQ` تسجيل دفعة طالب أو فاتورة.
- أخطاء API بصيغة موحّدة: `{"status": "error", "message": "..."}`.

## السجلات ومعالجة الأخطاء

- القناة الافتراضية `stack` ← `daily` (تدوير يومي، الاحتفاظ `LOG_DAILY_DAYS`، المستوى `LOG_LEVEL`).
- يُلحق بكل خطأ في السجل: `user_id` والرابط والطريقة (`bootstrap/app.php`).
- تنبيه Slack اختياري عبر `LOG_SLACK_WEBHOOK_URL`.
- الإنتاج: `APP_DEBUG=false`، و`LOG_LEVEL=warning`.

## قائمة النشر (Deployment checklist)

قبل النشر:

- [ ] `.env` الإنتاج: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` الصحيح، `SESSION_SECURE_COOKIE=true`
- [ ] `QUEUE_CONNECTION=database` (أو redis) ومشرف عمليات (Supervisor/systemd) يشغّل `php artisan queue:work --tries=3 --timeout=300`
- [ ] نسخة احتياطية من قاعدة البيانات (`php artisan backup:run`) قبل أي migration
- [ ] CI أخضر (Pint وLarastan والاختبارات) على الفرع المنشور

خطوات النشر:

```bash
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link          # أول مرة فقط
php artisan optimize:clear
php artisan filament:optimize
php artisan optimize
php artisan queue:restart
php artisan up
```

بعد النشر:

- [ ] افتح `/admin` وتأكد من تسجيل الدخول والقوائم الرئيسية (الطلاب، الدرجات، المدفوعات)
- [ ] راجع `storage/logs/laravel-*.log` بحثاً عن أخطاء
- [ ] تأكد أن العامل يعالج الطابور: `php artisan queue:failed` فارغ
- [ ] لا توجد مهام مجدولة معرّفة حالياً (`routes/console.php`)؛ إن أردت نسخاً احتياطياً دورياً أضف `backup:run` إلى الجدولة وشغّل `php artisan schedule:run` كل دقيقة عبر cron

للتراجع: `php artisan down` ثم استرجاع الإصدار السابق ونسخة القاعدة، ثم `optimize` من جديد.

## ملاحظات معروفة

- `User` لا يطبّق `Filament\Models\Contracts\FilamentUser`؛ الدخول إلى `/admin` خارج `local` يرجع 403 (موثّق باختبار).
- الـ migrations لا تغطي كل الجداول (انظر قسم قاعدة البيانات)، وملف `2026_08_03_143642_add_avatar_url_to_users_table`
  مكرر مع `2026_08_03_123829`.
- جداول الشهادات (`cert_students`, `cert_settings`, `cert_styles`) غير موجودة في القاعدة الحالية.
