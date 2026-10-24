-- مزامنة res_ltrecoms مع الكود (للصق في phpMyAdmin). آمن للتكرار، ولا يمسّ القرارات القديمة 1..30.
-- الفهرس الفريد (name_ar, name_en) يُراعى: الأسماء الإنجليزية أدناه مختلفة عن القديمة عمداً.
-- 1006 كان «انسحاب»؛ يصبح «تجميد» لأن الكود يستعمله للمجمّدين (FROZEN_RECOM).

INSERT INTO `res_ltrecoms` (`code`, `name_ar`, `name_en`, `description`, `is_visible`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1005, 'نجاح',               'Passed',                       'RecomCode::Pass - لا رسوب ولا غياب',                                    1, NOW(), NOW(), NULL),
(1006, 'تجميد',              'Frozen',                       'ResultSheetService::FROZEN_RECOM - طالب مجمّد',                         1, NOW(), NOW(), NULL),
(2001, 'إعادة بكل المقررات', 'Repeat All Courses',           'RecomCode::RepeatAll - رسوب أكثر من 70% في الملحق',                    1, NOW(), NOW(), NULL),
(2002, 'إعادة ببعض المقررات','Repeat Some Courses',          'RecomCode::RepeatSome - رسوب من 50% إلى 75%',                          1, NOW(), NOW(), NULL),
(3001, 'بديل',               'Substitute Exam',              'RecomCode::Substitute - غياب بعذر فقط',                                 1, NOW(), NOW(), NULL),
(3004, 'ملحق / بديل',        'Substitute and Supplementary', 'RecomCode::SubstituteAndSupplementary - غياب بعذر مع رسوب',             1, NOW(), NOW(), NULL),
(4001, 'ملحق',               'Supplementary',                'RecomCode::Supplementary - رسوب أقل من 50% من الساعات',                1, NOW(), NOW(), NULL),
(4002, 'فصل إداري',          'Administrative Dismissal',     'ShowAbsentCode::AdministrativeLeave - ضمن مرات الامتحان من الخارج',    1, NOW(), NOW(), NULL),
(4004, 'فصل',                'Academic Dismissal',           'RecomCode::AcademicDismissal - رسوب 75% فأكثر أو إعادة سابقة',         1, NOW(), NOW(), NULL),
(4005, 'إزالة رسوب',         'Remove Fail',                  'RecomCode::RemoveFail - مادة راسبة واحدة في الملحق',                   1, NOW(), NOW(), NULL),
(9999, 'توصية افتراضية',     'Incomplete',                   'RecomCode::Incomplete - درجات المواد غير مكتملة',                      1, NOW(), NOW(), NULL)
ON DUPLICATE KEY UPDATE
    `name_ar` = VALUES(`name_ar`),
    `name_en` = VALUES(`name_en`),
    `description` = VALUES(`description`),
    `is_visible` = 1,
    `updated_at` = NOW(),
    `deleted_at` = NULL;

-- تحقق بعد التنفيذ:
-- SELECT code, name_ar, name_en FROM res_ltrecoms WHERE code >= 1000 ORDER BY code;
