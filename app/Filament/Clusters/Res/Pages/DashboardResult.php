<?php

namespace App\Filament\Clusters\Res\Pages;

use App\Filament\Clusters\Res\ResCluster;
use App\Helpers\Bilingual;
use App\Jobs\RefreshRecommendationsJob;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Reg\Special;
use App\Models\Res\Mark;
use App\Models\Res\Printed;
use App\Models\Res\Subject;
use App\Models\Student;
use App\Reg\AbsStatus;
use App\Services\Res\FinalizationService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Url;

class DashboardResult extends Page
{
    use HasPageShield;
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'filament.clusters.res.pages.dashboard-result';

    protected static ?string $cluster = ResCluster::class;

    // show only for admin
    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $title = 'print_result';

    public static function getLabel(): ?string
    {
        return __('res.'.static::$title);
    }

    public static function getPluralLabel(): ?string
    {
        return __('res.'.static::$title);
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('res.'.static::$title));
    }

    public function getTitle(): string|Htmlable
    {
        return __('');
    }

    #[Url]
    public $batch;

    #[Url]
    public $faculty;

    #[Url]
    public $department;

    #[Url]
    public $special;

    #[Url]
    public $level;

    public $semester;

    #[Url]
    public $absent;

    public $university_no;

    #[Url]
    public $countstudent;

    #[Url]
    public $result_type = 'current';

    #[Url]
    public $design = 'page_header';

    #[Url]
    public $show_history = false;

    public $marksDetails = [];

    public const DEFAULT_FIELDS = ['ids', 'university_no', 'name_ar', 'grade', 'recom', 'gpa'];

    #[Url]
    public $fields = self::DEFAULT_FIELDS;

    #[Url]
    public $subjects = [];

    public $grades = [];

    public $search_results = null;

    public function EntryMarksForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->columns(['xl' => 5])
                    ->schema([

                        Select::make('batch')
                            ->label(__('reg.batch'))
                            ->required()
                            ->validationMessages([
                                'required' => Bilingual::msg('res_validation.batch_required'),
                            ])
                            ->options(
                                LtColumn::where('grouped', 2003)
                                    ->where('code', '>=', 2023)
                                    ->pluck('name_ar', 'code')

                            )
                            // default value
                            ->default('2023')
                            ->selectablePlaceholder(false)
                            ->searchable()
                            ->live(),

                        Select::make('faculty')
                            ->label(__('reg.faculty'))
                            ->required()
                            ->options(
                                Faculty::forAuthUser()
                                    ->orderBy('name_ar')
                                    ->pluck('name_ar', 'code')
                                    ->all()
                            )
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('department', null);
                                $set('special', null);
                                $set('subjects', []);
                                $set('university_no', null);
                            })
                            ->columnSpan(3),

                        Select::make('level')
                            ->label(__('reg.level'))
                            ->options(Level::forAuthUser()->pluck('name_ar', 'code'))
                            ->required()
                            ->live()
                            ->native()->columnSpan(1),

                        Select::make('department')
                            ->label(__('reg.department'))
                            ->required()
                            ->options(
                                fn (Get $get): array => blank($get('faculty'))
                                ? []
                                : Department::forAuthUser()
                                    ->where(
                                        'faculty_code',
                                        $get('faculty')
                                    )
                                    ->orderBy('name_ar')
                                    ->pluck('name_ar', 'code')
                                    ->all()
                            )
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('special', null);
                                $set('subjects', []);
                                $set('university_no', null);
                            })
                            ->columnSpan(2),

                        Select::make('special')
                            ->label(__('reg.special'))
                            ->options(
                                fn (Get $get): array => blank($get('department'))
                                ? []
                                : Special::query()
                                    ->where(
                                        'department_code',
                                        $get('department')
                                    )
                                    ->orderBy('name_ar')
                                    ->pluck('name_ar', 'code')
                                    ->all()
                            )
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('subjects', []);
                                $set('university_no', null);
                            }),

                        Hidden::make('semester')->default(\App\Services\Res\RegulationProfile::current()->defaultSemester()),

                        Select::make('absent')
                            ->label(__('res.absent'))
                            ->options(AbsStatus::class)
                            ->native()
                            ->required(),

                        Select::make('subjects')
                            ->label(__('res.subjects'))
                            ->multiple()
                            ->hintActions([
                                Action::make('selectAllSubjects')
                                    ->label('تحديد الكل')
                                    ->icon('heroicon-m-check-circle')
                                    ->link()
                                    ->action(fn (Set $set, Select $component) => $set('subjects', array_map(strval(...), array_keys($component->getOptions())))),
                                Action::make('clearSubjects')
                                    ->label('إلغاء التحديد')
                                    ->icon('heroicon-m-x-circle')
                                    ->color('gray')
                                    ->link()
                                    ->action(fn (Set $set) => $set('subjects', [])),
                            ])
                            ->native(false)
                            ->searchable()
                            ->options(function (Get $get): array {

                                if (
                                    blank($get('faculty')) ||
                                    blank($get('department')) ||
                                    blank($get('level'))
                                ) {
                                    return [];
                                }

                                return Subject::query()
                                    ->where('faculty_code', $get('faculty'))
                                    ->where('department_code', $get('department'))
                                    ->where('level_code', $get('level'))

                                    ->when(
                                        filled($get('special')),
                                        fn ($query) => $query->where(
                                            'special_code',
                                            $get('special')
                                        )
                                    )

                                    ->orderBy('code')
                                    ->pluck('name_ar', 'code')
                                    ->all();
                            })

                            ->visible(
                                fn (Get $get): bool => filled($get('faculty'))
                                && filled($get('department'))
                                && filled($get('level'))
                            )

                            ->dehydrated()
                            ->columnSpan(2),

                        Select::make('grades')
                            ->label(__('res.grade'))
                            ->options(
                                Mark::query()
                                    ->whereNotNull('grade')
                                    ->where('grade', '!=', '')
                                    ->distinct()
                                    ->orderBy('grade')
                                    ->pluck('grade', 'grade')
                                    ->toArray()
                            )
                            ->live()
                            ->native(false)
                            ->multiple()
                            ->visible(
                                fn (Get $get): bool => filled($get('faculty'))
                                    && filled($get('level'))
                                    && filled($get('batch'))
                            )
                            ->columnSpan(1),

                        Select::make('university_no')
                            ->label(__('reg.student'))
                            ->searchable()
                            ->native(false)
                            ->getSearchResultsUsing(
                                function (string $search, Get $get): array {

                                    if (
                                        blank($get('faculty')) ||
                                        blank($get('level')) ||
                                        blank($get('batch')) ||
                                        blank($get('department'))
                                    ) {
                                        return [];
                                    }

                                    return Student::query()
                                        ->select([
                                            'university_no',
                                            'name_ar',
                                        ])

                                        ->where(
                                            'faculty_code',
                                            $get('faculty')
                                        )

                                        ->where(
                                            'level_code',
                                            $get('level')
                                        )

                                        ->where(
                                            'batch_code',
                                            $get('batch')
                                        )

                                        ->where(
                                            'department_code',
                                            $get('department')
                                        )

                                        ->when(
                                            filled($get('special')),
                                            fn ($query) => $query->where(
                                                'special_code',
                                                $get('special')
                                            )
                                        )

                                        ->where(function ($query) use ($search) {
                                            $query
                                                ->where(
                                                    'name_ar',
                                                    'like',
                                                    "%{$search}%"
                                                )
                                                ->orWhere(
                                                    'university_no',
                                                    'like',
                                                    "%{$search}%"
                                                );
                                        })

                                        ->limit(30)

                                        ->get()

                                        ->mapWithKeys(
                                            function ($student): array {

                                                $name = filled($student->name_ar)
                                                    ? $student->name_ar
                                                    : 'بدون اسم';

                                                return [
                                                    (string) $student->university_no => "{$name} ({$student->university_no})",
                                                ];
                                            }
                                        )

                                        ->all();
                                }
                            )
                            ->getOptionLabelUsing(
                                function ($value): string {

                                    if (blank($value)) {
                                        return '';
                                    }

                                    $student = Student::query()
                                        ->select([
                                            'university_no',
                                            'name_ar',
                                        ])
                                        ->where(
                                            'university_no',
                                            $value
                                        )
                                        ->first();

                                    if (! $student) {
                                        // مهم جداً في Filament v5:
                                        // لا ترجع null
                                        return (string) $value;
                                    }

                                    $name = filled($student->name_ar)
                                        ? $student->name_ar
                                        : 'بدون اسم';

                                    return "{$name} ({$student->university_no})";
                                }
                            )
                            ->searchDebounce(600)
                            ->optionsLimit(30)
                            ->visible(
                                fn (Get $get): bool => filled($get('faculty'))
                                    && filled($get('level'))
                                    && filled($get('batch'))
                                    && filled($get('department'))
                            )
                            ->dehydrated()
                            ->columnSpan(2),

                        CheckboxList::make('fields')
                            ->bulkToggleable()
                            ->label(__('res.fields'))
                            ->options(
                                Printed::where('type', '>=', 1)
                                    ->pluck('name_ar', 'fields')
                            )
                            ->default(self::DEFAULT_FIELDS)
                            ->validationMessages([
                                'required' => Bilingual::msg('res_validation.fields_required'),
                                'min' => Bilingual::msg('res_validation.fields_min'),
                            ])
                            ->searchable()
                            ->columns(4)
                            ->columnSpan(5),

                    ])->columnSpan(5),

                Section::make()
                    ->schema([
                        Section::make('الطباعة')
                            ->description('كشوف النتائج والملخصات والسجل الأكاديمي للطالب.')
                            ->icon('heroicon-o-printer')
                            ->compact()
                            ->schema([
                                Actions::make([
                                    Action::make('printDetails')
                                        ->label(__('res.print_details'))
                                        ->icon('heroicon-m-printer')
                                        ->color('primary')
                                        ->button()
                                        ->action(function ($livewire) {

                                            // 1. التحقق من الحقول المحددة فقط باستخدام Livewire Validation
                                            $livewire->validate([
                                                'batch' => 'required',
                                                'faculty' => 'required',
                                                'department' => 'required',
                                                'level' => 'required',
                                                'absent' => 'required',
                                                'subjects' => 'required',
                                                'fields' => 'required|array|min:1',
                                            ], [
                                                // رسائل الخطأ المخصصة
                                                'batch.required' => Bilingual::msg('res_validation.batch_required'),
                                                'faculty.required' => Bilingual::msg('res_validation.faculty_required'),
                                                'department.required' => Bilingual::msg('res_validation.department_required'),
                                                'level.required' => Bilingual::msg('res_validation.level_required'),
                                                'absent.required' => Bilingual::msg('res_validation.absent_required'),
                                                'subjects.required' => Bilingual::msg('res_validation.subjects_required'),
                                                'fields.min' => Bilingual::msg('res_validation.fields_min'),
                                            ]);

                                            // 2. إذا لم يكن هناك أخطاء، سيتم تنفيذ الكود
                                            $formData = [
                                                'batch' => $livewire->batch,
                                                'faculty' => $livewire->faculty,
                                                'department' => $livewire->department,
                                                'special' => $livewire->special,
                                                'level' => $livewire->level,
                                                'semester' => $livewire->semester,
                                                'absent' => $livewire->absent,
                                                'countstudent' => $livewire->countstudent,
                                                'subjects' => $livewire->subjects,
                                                'grades' => $livewire->grades,
                                                'fields' => $livewire->fields,
                                                'result_type' => $livewire->result_type,
                                                'design' => $livewire->design,
                                                'show_history' => (int) $livewire->show_history,
                                                'per_page' => $livewire->countstudent,
                                            ];

                                            return redirect()->route('kassala.result', array_filter($formData, fn ($v) => filled($v) || $v === 0));
                                        }),
                                    Action::make('printSummary')
                                        ->label(__('res.print_summary'))
                                        ->icon('heroicon-m-document-text')
                                        ->color('gray')
                                        ->outlined()
                                        ->action(function ($livewire) {

                                            // 1. التحقق من الحقول المحددة فقط باستخدام Livewire Validation
                                            $livewire->validate([
                                                'batch' => 'required',
                                                'faculty' => 'required',
                                                'department' => 'required',
                                                'level' => 'required',

                                            ], [
                                                // رسائل الخطأ المخصصة
                                                'batch.required' => Bilingual::msg('res_validation.batch_required'),
                                                'faculty.required' => Bilingual::msg('res_validation.faculty_required'),
                                                'department.required' => Bilingual::msg('res_validation.department_required'),
                                                'level.required' => Bilingual::msg('res_validation.level_required'),

                                            ]);

                                            // 2. إذا لم يكن هناك أخطاء، سيتم تنفيذ الكود
                                            $formData = [
                                                'batch' => $livewire->batch,
                                                'faculty' => $livewire->faculty,
                                                'department' => $livewire->department,
                                                'special' => $livewire->special,
                                                'level' => $livewire->level,
                                                'semester' => $livewire->semester,
                                                'absent' => $livewire->absent,
                                                'subjects' => $livewire->subjects,
                                                'grades' => $livewire->grades,
                                                'fields' => $livewire->fields,
                                                'result_type' => $livewire->result_type,
                                            ];

                                            return redirect()->route('reports.result_summary', array_filter(
                                                collect($formData)->only(['batch', 'faculty', 'department', 'special', 'level', 'absent', 'subjects', 'result_type'])->all(),
                                                filled(...),
                                            ));
                                        }),
                                    Action::make('printYearResult')
                                        ->label(__('res.print_year_result'))
                                        ->icon('heroicon-m-document-text')
                                        ->color('gray')
                                        ->outlined()
                                        ->visible(fn (): bool => \App\Services\Res\RegulationProfile::current()->combinesYearForSupplements())
                                        ->action(function ($livewire) {
                                            $livewire->validate([
                                                'batch' => 'required',
                                                'faculty' => 'required',
                                                'department' => 'required',
                                                'level' => 'required',
                                            ], [
                                                'batch.required' => Bilingual::msg('res_validation.batch_required'),
                                                'faculty.required' => Bilingual::msg('res_validation.faculty_required'),
                                                'department.required' => Bilingual::msg('res_validation.department_required'),
                                                'level.required' => Bilingual::msg('res_validation.level_required'),
                                            ]);

                                            return redirect()->route('reports.year_result', array_filter([
                                                'batch' => $livewire->batch,
                                                'faculty' => $livewire->faculty,
                                                'department' => $livewire->department,
                                                'special' => $livewire->special,
                                                'level' => $livewire->level,
                                            ], filled(...)));
                                        }),
                                    Action::make('printStudent')
                                        ->label(__('res.print_student'))
                                        ->icon('heroicon-m-printer')
                                        ->color('gray')
                                        ->outlined()
                                        ->action(function ($livewire) {

                                            // 1. التحقق من الحقول المحددة فقط باستخدام Livewire Validation
                                            $livewire->validate([
                                                'batch' => 'required',
                                                'faculty' => 'required',
                                                'department' => 'required',
                                                'level' => 'required',
                                                'university_no' => 'required',
                                                // 'absent' => 'required',
                                            ], [
                                                // رسائل الخطأ المخصصة
                                                'batch.required' => Bilingual::msg('res_validation.batch_required'),
                                                'faculty.required' => Bilingual::msg('res_validation.faculty_required'),
                                                'department.required' => Bilingual::msg('res_validation.department_required'),
                                                'level.required' => Bilingual::msg('res_validation.level_required'),
                                                'university_no.required' => Bilingual::msg('res_validation.university_no_required'),
                                                'absent.required' => Bilingual::msg('res_validation.absent_required'),
                                            ]);

                                            // 2. إذا لم يكن هناك أخطاء، سيتم تنفيذ الكود
                                            $formData = [
                                                'batch' => $livewire->batch,
                                                'faculty' => $livewire->faculty,
                                                'department' => $livewire->department,
                                                'special' => $livewire->special,
                                                'level' => $livewire->level,
                                                'semester' => $livewire->semester,
                                                'absent' => $livewire->absent,
                                                'subjects' => $livewire->subjects,
                                                'grades' => $livewire->grades,
                                                'fields' => $livewire->fields,
                                                'result_type' => $livewire->result_type,
                                            ];

                                            session()->flash('print_student_data', $formData);

                                            return redirect()->route('student.transcript.print', ['university_no' => $livewire->university_no]);
                                        }),
                                ])->fullWidth(),
                            ]),
                        Section::make('الاعتماد والإجراءات')
                            ->description('إجراءات تكتب في قاعدة البيانات، وتحتاج تأكيداً.')
                            ->icon('heroicon-o-shield-check')
                            ->compact()
                            ->schema([
                                Actions::make([
                                    Action::make('finalizeResults')
                                        ->label('اعتماد النتائج نهائياً')
                                        ->icon('heroicon-m-shield-check')
                                        ->color('danger')
                                        ->button()
                                        ->visible(fn ($livewire): bool => filled($livewire->faculty)
                                            && app(FinalizationService::class)->canApprove(auth()->user(), $livewire->faculty))
                                        ->modalHeading('اعتماد النتائج نهائياً')
                                        ->modalDescription(function ($livewire): string {
                                            $text = 'سيتم قفل نتائج المجموعة المختارة نهائياً وتحويلها إلى السجل الأكاديمي الذي تُطبع منه الشهادات، ولن يعدّلها بعد ذلك إلا المدير الأعلى. أدخل رمز اعتمادك الشخصي للمتابعة.';

                                            if (blank($livewire->batch) || blank($livewire->department) || blank($livewire->level)) {
                                                return $text;
                                            }

                                            $blockers = app(FinalizationService::class)->blockers([
                                                'faculty' => $livewire->faculty,
                                                'department' => $livewire->department,
                                                'level' => $livewire->level,
                                                'batch' => $livewire->batch,
                                            ]);

                                            return $blockers === [] ? $text : $text."\n\nموانع الاعتماد الحالية: ".implode(' ', $blockers);
                                        })
                                        ->modalSubmitActionLabel('اعتماد نهائي')
                                        ->schema([
                                            TextInput::make('approval_code')
                                                ->label('رمز الاعتماد الشخصي')
                                                ->password()
                                                ->revealable()
                                                ->autocomplete('off')
                                                ->required(),
                                        ])
                                        ->action(function (array $data, $livewire): void {
                                            $livewire->validate([
                                                'batch' => 'required',
                                                'faculty' => 'required',
                                                'department' => 'required',
                                                'level' => 'required',
                                            ], [
                                                'batch.required' => Bilingual::msg('res_validation.batch_required'),
                                                'faculty.required' => Bilingual::msg('res_validation.faculty_required'),
                                                'department.required' => Bilingual::msg('res_validation.department_required'),
                                                'level.required' => Bilingual::msg('res_validation.level_required'),
                                            ]);

                                            try {
                                                $finalization = app(FinalizationService::class)->finalize(
                                                    auth()->user(),
                                                    [
                                                        'faculty' => $livewire->faculty,
                                                        'department' => $livewire->department,
                                                        'level' => $livewire->level,
                                                        'batch' => $livewire->batch,
                                                    ],
                                                    (string) $data['approval_code'],
                                                    request()->ip(),
                                                );

                                                Notification::make()
                                                    ->title('تم اعتماد النتائج وتحويلها إلى السجل الأكاديمي')
                                                    ->body("{$finalization->students_count} طالباً و{$finalization->marks_count} نتيجة (النسخة {$finalization->version}). أصبحت أساس الشهادات ولا يعدّلها إلا المدير الأعلى.")
                                                    ->success()
                                                    ->persistent()
                                                    ->send();
                                            } catch (DomainException $e) {
                                                Notification::make()->title($e->getMessage())->danger()->send();
                                            }
                                        }),
                                    Action::make('refresh_recom')
                                        ->label(__('res.refresh_recom'))
                                        ->icon('heroicon-m-arrow-path')
                                        ->color('warning')
                                        ->outlined()
                                        ->requiresConfirmation()
                                        ->modalHeading('إعادة حساب التوصيات')
                                        ->modalDescription(
                                            'سيتم إعادة حساب التوصيات لجميع الطلاب المطابقين لخيارات البحث.'
                                        )
                                        ->modalSubmitActionLabel('بدء إعادة الحساب')
                                        ->action(function ($livewire) {

                                            $livewire->validate([
                                                'batch' => 'required',
                                                'faculty' => 'required',
                                                'department' => 'required',
                                                'level' => 'required',
                                                'absent' => 'required',
                                            ], [
                                                'batch.required' => Bilingual::msg('res_validation.batch_required'),

                                                'faculty.required' => Bilingual::msg('res_validation.faculty_required'),

                                                'department.required' => Bilingual::msg('res_validation.department_required'),

                                                'level.required' => Bilingual::msg('res_validation.level_required'),

                                                'absent.required' => Bilingual::msg('res_validation.absent_required'),
                                            ]);

                                            RefreshRecommendationsJob::dispatch(
                                                batch: (string) $livewire->batch,
                                                faculty: (string) $livewire->faculty,
                                                department: (string) $livewire->department,

                                                special: filled($livewire->special)
                                                ? (string) $livewire->special
                                                : null,

                                                level: (string) $livewire->level,

                                                semester: (string) ($livewire->semester ?? 1),

                                                examType: (string) $livewire->absent,

                                                universityNo: filled($livewire->university_no)
                                                ? (string) $livewire->university_no
                                                : null,
                                            );

                                            Notification::make()
                                                ->title('تم بدء إعادة حساب التوصيات')
                                                ->body(
                                                    filled($livewire->university_no)
                                                    ? 'سيتم إعادة حساب توصية الطالب المحدد.'
                                                    : 'سيتم إعادة حساب توصيات جميع الطلاب المطابقين للبحث.'
                                                )
                                                ->success()
                                                ->send();
                                        }),
                                    Action::make('freezeStudents')
                                        ->label('تثبيت نتائج الطلاب المجمّدين')
                                        ->icon('heroicon-m-lock-closed')
                                        ->color('gray')
                                        ->outlined()
                                        ->requiresConfirmation()
                                        ->modalHeading('تثبيت نتائج المجمّدين')
                                        ->modalDescription('سيُسجَّل لكل طالب مجمّد في الفلاتر المختارة: درجة 0 وتقدير (ت) وقرار التجميد، في قاعدة البيانات. الطباعة وحدها لا تكتب أي شيء.')
                                        ->modalSubmitActionLabel('تثبيت')
                                        ->action(function ($livewire) {
                                            $livewire->validate([
                                                'batch' => 'required',
                                                'faculty' => 'required',
                                                'department' => 'required',
                                                'level' => 'required',
                                                'subjects' => 'required',
                                            ], [
                                                'subjects.required' => Bilingual::msg('res_validation.subjects_required'),
                                            ]);

                                            $count = app(\App\Services\Res\ResultSheetService::class)->persistFrozen([
                                                'batch' => $livewire->batch,
                                                'faculty' => $livewire->faculty,
                                                'department' => $livewire->department,
                                                'special' => $livewire->special,
                                                'level' => $livewire->level,
                                                'subjects' => $livewire->subjects,
                                            ]);

                                            \Filament\Notifications\Notification::make()
                                                ->title($count ? "تم تثبيت نتائج {$count} طالب مجمّد" : 'لا يوجد طلاب مجمّدون ضمن الفلاتر المختارة')
                                                ->success()
                                                ->send();
                                        }),
                                ])->fullWidth(),
                            ]),
                    ])
                    ->columnSpan(5),

            ]);
    }

    // أضف هذه الدالة داخل كلاس الصفحة

}
