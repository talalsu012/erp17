-- مزامنة res_resons مع الكود (للصق في phpMyAdmin). آمن للتكرار، ولا يغيّر رموز الأسباب القديمة.
-- يضيف: 3001 (REASON_ABSENT) و1001 و2001 و2003 و2005، ويثبّت 21 (REASON_EXCLUDED)، ويصحّح أسماء 2 و18 و20 و22.
-- لا يمسّ الأسباب المحذوفة (1 و3 و4 و5 و6 و16 و23).

INSERT INTO `res_resons` (`code`, `name_ar`, `name_en`, `description`, `mark`, `grade`, `point`, `recom_code`, `is_visible`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3001, 'بديل',                    'Substitute',                       'REASON_ABSENT - غياب بعذر، لا ناجح ولا راسب',          NULL, 'بديل',  NULL, NULL, 1, NOW(), NOW(), NULL),
(21,   'نجاح سابق',               'Pass Old',                         'REASON_EXCLUDED - المادة مستبعدة من العدد والحساب',     NULL, NULL,    NULL, NULL, 1, NOW(), NOW(), NULL),
(1001, 'نجاح',                    'Pass',                             'يحفظ السبب فقط دون المساس بالدرجة',                      NULL, NULL,    NULL, NULL, 1, NOW(), NOW(), NULL),
(2001, 'رسوب',                    'Fail',                             'رسوب',                                                   NULL, 'رسوب',  NULL, NULL, 1, NOW(), NOW(), NULL),
(2003, 'حرمان',                   'Deprivation',                      'رسوب',                                                   NULL, 'رسوب',  NULL, NULL, 1, NOW(), NOW(), NULL),
(2005, 'فصل اداري',               'Administrative Dismissal',         'رسوب',                                                   NULL, 'رسوب',  NULL, NULL, 1, NOW(), NOW(), NULL),
(2,    'تقرير طبي أثناء الامتحان', 'Medical report during the exam',   'AB',                                                     NULL, 'AB',    NULL, NULL, 1, NOW(), NOW(), NULL),
(18,   'غياب بعذر',               'Absence is excused',               'AB - قديم، يعادله 3001',                                 NULL, 'AB',    NULL, NULL, 1, NOW(), NOW(), NULL),
(20,   'حالة غش',                 'Cheating Case',                    'CC',                                                     0,    'CC',    0.00, 21,   1, NOW(), NOW(), NULL),
(22,   'غياب بدون عذر',           'Absence without excuse',           'يُحتسب راسباً (درجة 0)',                                 0,    'F',     0.00, NULL, 1, NOW(), NOW(), NULL)
ON DUPLICATE KEY UPDATE
    `name_ar` = VALUES(`name_ar`),
    `name_en` = VALUES(`name_en`),
    `description` = VALUES(`description`),
    `mark` = VALUES(`mark`),
    `grade` = VALUES(`grade`),
    `point` = VALUES(`point`),
    `recom_code` = VALUES(`recom_code`),
    `is_visible` = 1,
    `updated_at` = NOW(),
    `deleted_at` = NULL;

-- الخطوة 2: توحيد السبب القديم 18 مع 3001 (التقدير «بديل»). الكود يعامل 18 كغياب بعذر أصلاً،
-- فهذا التوحيد يجعل الدرجات والتقارير تعرض «بديل» بدل «AB». يمسّ غير المقفل فقط.
-- راجع عدد الصفوف أولاً: SELECT COUNT(*) FROM res_marks WHERE reson_code = 18 AND is_locked = 0;
UPDATE `res_marks` SET `reson_code` = 3001, `grade` = 'بديل', `updated_at` = NOW()
 WHERE `reson_code` = 18 AND `is_locked` = 0;
-- بعدها شغّل «إعادة حساب التوصيات» للمجموعات المتأثرة.

-- تحقق بعد التنفيذ:
-- SELECT code, name_ar, name_en, grade, recom_code, deleted_at FROM res_resons ORDER BY code;
