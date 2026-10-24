<?php

namespace App\Filament\Imports\Reg;

use App\Models\Reg\LtColumn;
use App\Models\Student;
use App\Support\ArabicNameTransliterator;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Checkbox;
use Illuminate\Validation\Rule;

/**
 * استيراد الطلاب (نسخة 2026): إنشاء طلاب جدد أو تحديث الموجودين برقمهم الجامعي، مع تنظيف الأرقام العربية
 * وتحويل أسماء القوائم (ذكر، عام...) إلى رموزها، وبناء الاسم من أجزائه، وتحويل الاسم العربي إلى إنجليزي عند غيابه.
 */
class StudentImporter extends Importer
{
    protected static ?string $model = Student::class;

    private const NAME_PARTS = ['first_name', 'second_name', 'third_name', 'fourth_name'];

    /** قيم `reg_ltcolumns.grouped` كما في نموذج الطالب */
    private const GROUP_GENDER = 1001;

    private const GROUP_NATIONALITY = 1002;

    private const GROUP_ADMISSION = 2001;

    private const GROUP_BATCH = 2003;

    private const GROUP_STADY = 2006;

    /** @var array<int, array<string, int>> اسم/رمز => رمز لكل مجموعة (يُحمَّل مرة لكل طلب) */
    private static array $ltColumnCache = [];

    public static function getColumns(): array
    {
        return [
            // التعريف
            self::universityNoColumn(),
            self::nameArColumn(),
            self::nameEnColumn(),
            ...array_map(
                self::namePartColumn(...),
                self::NAME_PARTS,
                ['محمد', 'أحمد', 'علي', 'حسن'],
            ),

            // الأكاديمية (الكلية والفصل إلزاميان عند إنشاء طالب جديد)
            self::lookupColumn('faculty_code', 'reg.faculty_code', 'reg_faculties', '101', required: true),
            self::departmentColumn(),
            self::lookupColumn('level_code', 'reg.level_code', 'reg_levels', '1', required: true),
            self::ltColumn('batch_code', 'reg.batch_code', self::GROUP_BATCH, '2023'),
            self::ltColumn('admission_code', 'reg.admission', self::GROUP_ADMISSION, 'عام'),
            self::ltColumn('stady_code', 'reg.stady', self::GROUP_STADY, '20060001'),
            self::ltColumn('academic_year_code', 'reg.academic_year', self::GROUP_BATCH, '2024'),

            // الشخصية
            self::ltColumn('gender_code', 'reg.gender', self::GROUP_GENDER, 'ذكر'),
            self::ltColumn('nationality_code', 'reg.nationality', self::GROUP_NATIONALITY, '10020001'),
            self::nationaltyNoColumn(),

            // المالية
            ...self::feesColumns(),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Checkbox::make('updateExisting')
                ->label(__('reg.import_update_existing'))
                ->helperText(__('reg.import_update_existing_help'))
                ->default(true),
        ];
    }

    public function resolveRecord(): ?Student
    {
        $universityNo = $this->data['university_no'] ?? null;

        if (! is_int($universityNo) || $universityNo <= 0) {
            throw new RowImportFailedException(__('reg.import_invalid_university_no', [
                'value' => $this->originalData[$this->columnMap['university_no'] ?? ''] ?? '',
            ]));
        }

        // يشمل المحذوفين مؤقتاً: المفتاح الأساسي ما زال محجوزاً لهم.
        $student = Student::withTrashed()->find($universityNo);

        if (! $student) {
            return new Student(['university_no' => $universityNo]);
        }

        if (! ($this->options['updateExisting'] ?? true)) {
            throw new RowImportFailedException(__('reg.import_student_exists', ['no' => $universityNo]));
        }

        return $student;
    }

    /**
     * يبني الاسم العربي من أجزائه إن لم يأتِ في الملف، ويحوّله للإنجليزية إن لم يوجد اسم إنجليزي.
     */
    protected function afterFill(): void
    {
        if (blank($this->record->name_ar)) {
            $this->record->name_ar = collect(self::NAME_PARTS)
                ->map(fn (string $part) => $this->record->{$part})
                ->filter()
                ->implode(' ') ?: null;
        }

        if (blank($this->record->name_en) && filled($this->record->name_ar)) {
            $this->record->name_en = ArabicNameTransliterator::transliterate($this->record->name_ar);
        }
    }

    protected function beforeSave(): void
    {
        if ($this->record->trashed()) {
            $this->record->deleted_at = null;
        }

        foreach (['fees', 'reg_fees', 'fees1', 'fees2', 'fees3'] as $fee) {
            $this->record->{$fee} ??= 0;
        }
    }

    public static function getCompletedNotificationTitle(Import $import): string
    {
        return __('reg.import_completed_title');
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('reg.import_completed_body', [
            'success' => number_format($import->successful_rows),
            'total' => number_format($import->total_rows),
        ]);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.__('reg.import_failed_rows', ['count' => number_format($failedRowsCount)]);
        }

        return $body;
    }

    /**************************
     * تعريف الأعمدة
     **************************/

    private static function universityNoColumn(): ImportColumn
    {
        return ImportColumn::make('university_no')
            ->label(__('reg.university_no'))
            ->requiredMapping()
            ->guess(['university_no', 'university number', 'student_no', 'الرقم الجامعي'])
            ->castStateUsing(fn ($originalState) => self::toInteger($originalState))
            ->rules(['required', 'integer', 'min:1'])
            ->example('20230001');
    }

    private static function nameArColumn(): ImportColumn
    {
        return ImportColumn::make('name_ar')
            ->label(__('reg.name_ar'))
            ->guess(['name_ar', 'arabic name', 'name', 'الاسم'])
            ->castStateUsing(fn ($state) => self::cleanText($state))
            ->ignoreBlankState()
            ->rules(['nullable', 'string', 'max:200'])
            ->example('محمد أحمد علي');
    }

    private static function nameEnColumn(): ImportColumn
    {
        return ImportColumn::make('name_en')
            ->label(__('reg.name_en'))
            ->guess(['name_en', 'english name'])
            ->castStateUsing(fn ($state) => self::cleanText($state))
            ->ignoreBlankState()
            ->rules(['nullable', 'string', 'max:200'])
            ->example('Mohamed Ahmed Ali');
    }

    /**
     * عمود رمز يشير إلى `$table.code`. الإلزامي يجب أن يكون موجوداً ومعبّأً عند إنشاء طالب جديد.
     */
    private static function lookupColumn(string $name, string $labelKey, string $table, string $example, bool $required = false): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__($labelKey))
            ->requiredMappingForNewRecordsOnly($required)
            ->castStateUsing(fn ($originalState) => self::toInteger($originalState))
            ->ignoreBlankState()
            ->rules(fn (Student $record) => [
                $required && ! $record->exists ? 'required' : 'nullable',
                'integer',
                Rule::exists($table, 'code'),
            ])
            ->example($example);
    }

    /**
     * عمود من `reg_ltcolumns`: يقبل الرمز (مثل 10010001) أو الاسم العربي/الإنجليزي (ذكر، male) ضمن المجموعة.
     */
    private static function ltColumn(string $name, string $labelKey, int $group, string $example): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__($labelKey))
            ->castStateUsing(fn ($originalState) => self::resolveLtColumn($originalState, $group))
            ->ignoreBlankState()
            ->rules([
                'nullable',
                'integer',
                Rule::exists('reg_ltcolumns', 'code')->where('grouped', $group),
            ])
            ->example($example);
    }

    private static function namePartColumn(string $name, string $example): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__("reg.{$name}"))
            ->castStateUsing(fn ($state) => self::cleanText($state))
            ->ignoreBlankState()
            ->rules(['nullable', 'string', 'max:50'])
            ->example($example);
    }

    /** اختياري، وإن وُجد فيجب أن يتبع كلية الصف. */
    private static function departmentColumn(): ImportColumn
    {
        return ImportColumn::make('department_code')
            ->label(__('reg.department_code'))
            ->castStateUsing(fn ($originalState) => self::toInteger($originalState))
            ->ignoreBlankState()
            ->rules(function (Student $record, array $data): array {
                $facultyCode = $data['faculty_code'] ?? $record->faculty_code;

                return [
                    'nullable',
                    'integer',
                    Rule::exists('reg_departments', 'code')
                        ->when(is_numeric($facultyCode), fn ($rule) => $rule->where('faculty_code', (int) $facultyCode)),
                ];
            })
            ->example('10101');
    }

    private static function nationaltyNoColumn(): ImportColumn
    {
        return ImportColumn::make('nationaltyno')
            ->label(__('reg.nationaltyno'))
            ->castStateUsing(fn ($originalState) => self::cleanIdentifier($originalState))
            ->ignoreBlankState()
            ->rules(fn (Student $record) => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('students', 'nationaltyno')->ignore($record->getKey(), 'university_no'),
            ])
            ->example('1234567890');
    }

    /**
     * @return array<int, ImportColumn>
     */
    private static function feesColumns(): array
    {
        return [
            self::moneyColumn('fees', 'reg.fees', '5000'),
            self::moneyColumn('reg_fees', 'reg.regfees', '1000'),
            self::moneyColumn('fees1', 'reg.fees1', '0'),
            self::moneyColumn('fees2', 'reg.fees2', '0'),
            self::moneyColumn('fees3', 'reg.fees3', '0'),
        ];
    }

    /**
     * أعمدة الرسوم NOT NULL في قاعدة البيانات، فالخلايا الفارغة تُتجاهل
     * (تبقى القيمة الحالية، أو 0 للطالب الجديد).
     */
    private static function moneyColumn(string $name, string $labelKey, string $example): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__($labelKey))
            ->castStateUsing(fn ($originalState) => self::toNumber($originalState))
            ->ignoreBlankState()
            ->rules(['nullable', 'numeric', 'min:0'])
            ->example($example);
    }

    /**************************
     * تنظيف القيم
     *
     * كل دالة ترجع القيمة المنظّفة، أو القيمة الأصلية إن تعذّر تحليلها فيُبلَّغ عنها في التحقق
     * بدل استيراد قيمة خاطئة بصمت (مثل «abc» تصبح 0).
     **************************/

    /** يحوّل الأرقام العربية/الفارسية وفواصلها إلى ASCII. */
    private static function normalizeDigits(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = strtr(trim((string) $value), [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٫' => '.', '٬' => ',', "\u{00A0}" => ' ',
        ]);

        return $value === '' ? null : $value;
    }

    private static function cleanText(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = preg_replace('/\s+/u', ' ', trim((string) $value));

        return $value === '' ? null : $value;
    }

    private static function toInteger(mixed $value): int|string|null
    {
        $value = self::normalizeDigits($value);

        if ($value === null) {
            return null;
        }

        // جداول البيانات تحوّل 20230001 إلى "20230001.0" أو "20,230,001".
        $clean = preg_replace('/[\s,]/', '', $value);
        $clean = preg_replace('/\.0+$/', '', $clean);

        return ctype_digit($clean) ? (int) $clean : $value;
    }

    private static function resolveLtColumn(mixed $value, int $group): int|string|null
    {
        $code = self::toInteger($value);

        if ($code === null || is_int($code)) {
            return $code;
        }

        self::$ltColumnCache[$group] ??= LtColumn::query()
            ->where('grouped', $group)
            ->get(['code', 'name_ar', 'name_en'])
            ->flatMap(fn (LtColumn $row): array => [
                mb_strtolower(trim((string) $row->name_ar)) => (int) $row->code,
                mb_strtolower(trim((string) $row->name_en)) => (int) $row->code,
            ])
            ->except([''])
            ->all();

        return self::$ltColumnCache[$group][mb_strtolower((string) self::cleanText($value))] ?? $value;
    }

    private static function toNumber(mixed $value): float|string|null
    {
        $value = self::normalizeDigits($value);

        if ($value === null) {
            return null;
        }

        $clean = preg_replace('/[\s,]/', '', $value);

        return is_numeric($clean) ? (float) $clean : $value;
    }

    private static function cleanIdentifier(mixed $value): ?string
    {
        $value = self::normalizeDigits($value);

        if ($value === null) {
            return null;
        }

        return preg_replace('/\.0+$/', '', preg_replace('/\s+/', '', $value));
    }
}
