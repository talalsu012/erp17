<?php

namespace App\Filament\Clusters\Res\Pages\EntryMarks;

use App\Filament\Clusters\Res\ResCluster;
use App\Helpers\Bilingual;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Reg\Special;
use App\Models\Res\LtRecom;
use App\Models\Res\Mark;
use App\Models\Res\Reson;
use App\Models\Res\Subject;
use App\Models\Student;
use App\Policies\MarkPolicy;
use App\Reg\AbsStatus;
use App\Reg\MarkApprovalStage;
use App\Reg\ShowAbsentCode;
use App\Services\Res\MarkEntryService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;

/**
 * إدخال درجات الطلاب لمادة ودفعة محددة.
 * الصفحة للعرض فقط: كل منطق الحفظ والصلاحيات والتقديرات واللائحة في App\Services\Res.
 */
class EntryMark extends Page implements HasForms, HasTable
{
    use HasPageShield;
    use InteractsWithForms;
    use InteractsWithTable;

    protected string $view = 'filament.clusters.res.pages.entry-mark';

    protected static ?string $cluster = ResCluster::class;

    protected static ?string $model = Mark::class;

    protected static ?string $modelPolicy = MarkPolicy::class;

    protected static ?string $title = 'entry_mark';

    /**
     * حالة الفورم. المتصفح يستطيع تعديلها، لذلك لا نثق بها:
     * الاستعلام والحفظ كلاهما يتحقق من الصلاحية في الخادم.
     *
     * @var array{batch?: mixed, faculty?: mixed, department?: mixed, special?: mixed, level?: mixed, semester?: mixed, absent?: mixed, subject?: mixed}
     */
    /**
     * حالة الفورم. المتصفح يستطيع تعديلها، لذلك لا نثق بها:
     * الاستعلام والحفظ كلاهما يتحقق من الصلاحية في الخادم.
     *
     * @var array{batch?: mixed, faculty?: mixed, department?: mixed, special?: mixed, level?: mixed, semester?: mixed, absent?: mixed, subject?: mixed}
     */
    #[Url]
    public ?array $data = [
        'batch' => null,
        'faculty' => null,
        'department' => null,
        'special' => null,
        'level' => null,
        'semester' => 1,
        'absent' => null,
        'subject' => null,
    ];

    /** كاش لكل طلب (لا يُحفظ بين طلبات Livewire) */
    protected ?Subject $subjectCache = null;

    protected ?array $reasonOptions = null;

    /**
     * رابط الصفحة وفيه اختيارات الفورم (يُستعمل للرجوع من كشف الطباعة).
     *
     * @param  array<string, mixed>  $filters
     */
    public static function urlWithFilters(array $filters, string $panel = 'admin'): string
    {
        $data = collect($filters)
            ->only(['batch', 'faculty', 'department', 'special', 'level', 'absent', 'subject'])
            ->filter(fn ($value) => filled($value))
            ->all();

        return static::getUrl(parameters: ['data' => $data], panel: $panel);
    }

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

    public static function getNavigationGroup(): string
    {
        return __('res.StudentsMarks');
    }

    public function getTitle(): string|Htmlable
    {
        return '';
    }

    public function getSubNavigation(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('guide')
                ->label('دليل الاستخدام')
                ->icon('heroicon-o-question-mark-circle')
                ->color('gray')
                ->url(fn () => route('help.show', 'entry-marks'))
                ->openUrlInNewTab(),

            Action::make('print')
                ->label('طباعة كشف الدرجات')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->visible(fn () => $this->subject() !== null)
                ->url(fn () => route('res.entry-mark.print', array_filter(
                    collect($this->data ?? [])->only(['batch', 'faculty', 'department', 'special', 'level', 'absent', 'subject'])->all(),
                    filled(...),
                )))
                ->openUrlInNewTab(),
        ];
    }

    public function EntryMarksForm(Schema $schema): Schema
    {
        $reset = fn (Set $set, string ...$fields) => collect($fields)->each(fn ($f) => $set($f, null));

        return $schema
            ->statePath('data')
            ->schema([
                Section::make('تحديد المادة والعام الدراسي')
                    ->icon('heroicon-o-funnel')
                    ->columns(['default' => 1, 'md' => 2, 'xl' => 4])
                    ->schema([
                        Select::make('faculty')
                            ->label(__('reg.faculty'))
                            ->required()
                            ->validationMessages(['required' => Bilingual::msg('res_validation.faculty_required')])
                            ->options(fn () => Faculty::forAuthUser()->pluck('name_ar', 'code'))
                            ->searchable()
                            ->live()
                            ->columnSpan(['md' => 2])
                            ->afterStateUpdated(fn (Set $set) => $reset($set, 'department', 'special', 'subject')),

                        Select::make('department')
                            ->label(__('reg.department'))
                            ->required()
                            ->validationMessages(['required' => Bilingual::msg('res_validation.department_required')])
                            ->options(fn (Get $get) => Department::forAuthUser()->where('faculty_code', $get('faculty'))->pluck('name_ar', 'code'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $reset($set, 'special', 'subject')),

                        Select::make('special')
                            ->label(__('reg.special'))
                            ->options(fn (Get $get) => Special::where('department_code', $get('department'))->pluck('name_ar', 'code'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $reset($set, 'subject')),

                        Select::make('batch')
                            ->label(__('reg.batch'))
                            ->required()
                            ->validationMessages(['required' => Bilingual::msg('res_validation.batch_required')])
                            ->options(fn () => LtColumn::where('grouped', 2003)->where('code', '>=', 2023)->pluck('name_ar', 'code'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $reset($set, 'subject')),

                        Select::make('level')
                            ->label(__('reg.level'))
                            ->options(fn () => Level::forAuthUser()->pluck('name_ar', 'code'))
                            ->required()
                            ->validationMessages(['required' => Bilingual::msg('res_validation.level_required')])
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $reset($set, 'subject')),

                        Select::make('absent')
                            ->label(__('res.absent'))
                            ->options(AbsStatus::class)
                            ->required()
                            ->live(),

                        Hidden::make('semester')->default(\App\Services\Res\RegulationProfile::current()->defaultSemester()),

                        Select::make('subject')
                            ->label(__('res.subject'))
                            ->required()
                            ->validationMessages(['required' => Bilingual::msg('res_validation.subject_required')])
                            ->options(function (Get $get) {
                                if (blank($get('faculty')) || blank($get('level')) || blank($get('batch'))) {
                                    return [];
                                }

                                return Subject::query()
                                    ->active()
                                    ->where('faculty_code', $get('faculty'))
                                    ->where('level_code', $get('level'))
                                    ->when($get('department'), fn ($q, $v) => $q->where('department_code', $v))
                                    ->when($get('special'), fn ($q, $v) => $q->where('special_code', $v))
                                    ->pluck('name_ar', 'code');
                            })
                            ->live()
                            ->columnSpanFull()
                            ->afterStateUpdated(fn () => $this->loadStudents()),

                        Grid::make(['default' => 1, 'md' => 2])
                            ->columnSpanFull()
                            ->schema([
                                Text::make(fn () => 'المرحلة الحالية: '.$this->currentStage()->getLabel())
                                    ->badge()
                                    ->color(fn () => $this->currentStage()->getColor())
                                    ->icon(fn () => $this->currentStage()->getIcon())
                                    ->visible(fn () => filled($this->data['subject'] ?? null)),

                                Actions::make([
                                    Action::make('showStudents')
                                        ->label(__('reg.students'))
                                        ->icon('heroicon-m-users')
                                        ->color('primary')
                                        ->action(fn () => $this->loadStudents()),

                                    Action::make('approveMarks')
                                        ->label(__('res.approve_marks'))
                                        ->icon('heroicon-m-check-badge')
                                        ->color('success')
                                        ->requiresConfirmation()
                                        ->modalHeading('اعتماد درجات المادة')
                                        ->modalDescription('سيتم ترحيل المادة إلى المرحلة التالية، ولن يتمكن أحد من التعديل بعدها إلا من يملك صلاحية تلك المرحلة.')
                                        ->modalSubmitActionLabel('اعتماد')
                                        ->visible(fn () => $this->canEditSelected()
                                            && ! in_array($this->currentStage(), [MarkApprovalStage::Approved, MarkApprovalStage::Rejected], true))
                                        ->action(fn () => $this->approve()),
                                ])->alignEnd(),
                            ]),
                    ]),
            ]);
    }

    protected function loadStudents(): void
    {
        try {
            $this->validate();
            $this->resetTable();
        } catch (ValidationException) {
            Notification::make()
                ->title(Bilingual::msg('res_validation.input_error_title'))
                ->body(Bilingual::msg('res_validation.input_error_body'))
                ->danger()
                ->send();
        }
    }

    protected function approve(): void
    {
        $this->guard(function (Subject $subject) {
            $stage = app(MarkEntryService::class)->advanceStage(auth()->user(), $subject, $this->data['batch']);

            Notification::make()->title('تم الترحيل إلى: '.$stage->getLabel())->success()->send();
        }, silent: true);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->studentsQuery())
            ->heading(fn () => $this->heading())
            ->description(fn () => $this->subject()
                ? 'المرحلة الحالية: '.$this->currentStage()->getLabel()
                : null)
            ->defaultSort('name_ar')
            ->emptyStateHeading(fn () => $this->emptyStateHeading())
            ->columns([
                SelectColumn::make('reason')
                    ->label(__('res.reason'))
                    ->options(fn () => $this->reasonOptions())
                    ->getStateUsing(fn (Student $record) => $record->marks->first()?->reson_code)
                    ->updateStateUsing(fn (Student $record, $state) => $this->guard(
                        fn (Subject $subject) => app(MarkEntryService::class)->saveReason(
                            auth()->user(), $record, $subject, $this->data['batch'], $this->data['absent'], $state, $this->data['special'] ?? null,
                        )
                    ))
                    ->width('10%'),

                TextColumn::make('university_no')
                    ->label(__('reg.university_no'))
                    ->searchable(isIndividual: true, isGlobal: true)
                    ->sortable()
                    ->width('12%'),

                TextColumn::make('name_ar')
                    ->label(__('reg.name_ar'))
                    ->searchable(isIndividual: true, isGlobal: true)
                    ->sortable()
                    ->width('25%'),

                $this->scoreColumn('mark', __('res.exam')),

                $this->scoreColumn('cwork', __('res.cwork'))
                    ->visible(fn () => ($this->subject()?->exam ?? 100) < 100)
                    ->disabled(fn (Student $record) => (int) ($this->data['absent'] ?? 0) === 2
                        && ($mark = $record->marks->first())
                        && ! in_array((string) $mark->grade, ['راسب', ...ShowAbsentCode::getMarkValues()], true)),

                TextColumn::make('marks.grade')
                    ->label(__('res.grade'))
                    ->sortable()
                    ->searchable(isIndividual: true, isGlobal: true, query: fn (Builder $q, string $s) => $this->searchMarks($q, 'grade', $s))
                    ->width('10%'),

                TextColumn::make('grouping.name')
                    ->label('المجموعة')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->getStateUsing(fn (Student $record) => $record->grouping->pluck('name')->filter()->implode(', ')),

                TextColumn::make('grouping.room')
                    ->label('القاعة')
                    ->badge()
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->getStateUsing(fn (Student $record) => $record->grouping->map(fn ($g) => $g->pivot->room?->name)->filter()->implode(', ')),
            ]);
    }

    /** عمود إدخال رقمي (درجة الامتحان أو أعمال السنة) */
    protected function scoreColumn(string $field, string $label): TextInputColumn
    {
        return TextInputColumn::make("marks.{$field}")
            ->label($label)
            ->inputMode('decimal')
            ->rules(['nullable', 'numeric', 'min:0'])
            ->searchable(isIndividual: true, isGlobal: true, query: fn (Builder $q, string $s) => $this->searchMarks($q, $field, $s))
            ->getStateUsing(fn (Student $record) => $record->marks->first()?->{$field})
            ->updateStateUsing(fn (Student $record, $state) => $this->guard(
                fn (Subject $subject) => app(MarkEntryService::class)->saveScore(
                    auth()->user(), $record, $subject, $this->data['batch'], $this->data['absent'], $field, $state, $this->data['special'] ?? null,
                )
            ))
            ->width('10%');
    }

    protected function studentsQuery(): Builder
    {
        return $this->service()
            ->studentsQuery(auth()->user(), $this->data ?? [])
            ->with('grouping.room');
    }

    /**
     * ينفّذ عملية حفظ ويعرض إشعاراً واحداً (نجاح/رفض) بدل إشعارين لكل خلية.
     * إن أرجعت العملية ['mark' => Mark, 'recom' => Recom] يعرض الإشعار التقدير والتوصية والمعدل.
     */
    protected function guard(callable $action, bool $silent = false): void
    {
        try {
            $subject = $this->subject() ?? throw new DomainException(Bilingual::msg('res_validation.select_subject_first'));

            $result = $action($subject);

            if (! $silent) {
                $this->notifySaved($result);
            }
        } catch (DomainException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();

            $this->dispatch('$refresh');
        }
    }

    protected function notifySaved(mixed $result): void
    {
        $notification = Notification::make()->title('تم الحفظ')->success()->duration(4000);

        if (is_array($result) && isset($result['mark'], $result['recom'])) {
            ['mark' => $mark, 'recom' => $recom] = $result;

            $recomName = LtRecom::find($recom->recom_code)?->name_ar ?? $recom->recom_code;

            $notification->body(implode(' | ', [
                'التقدير: '.($mark->grade ?? '—'),
                'التوصية: '.$recomName,
                'المعدل: '.number_format((float) $recom->gpa, 2),
            ]));
        }

        $notification->send();
    }

    protected function service(): MarkEntryService
    {
        return app(MarkEntryService::class);
    }

    protected function subject(): ?Subject
    {
        $code = $this->data['subject'] ?? null;

        if (blank($code)) {
            return null;
        }

        return $this->subjectCache = ($this->subjectCache?->code == $code ? $this->subjectCache : Subject::find($code));
    }

    protected function currentStage(): MarkApprovalStage
    {
        return app(MarkEntryService::class)->currentStage($this->data['subject'] ?? null, $this->data['batch'] ?? null);
    }

    protected function canEditSelected(): bool
    {
        $subject = $this->subject();

        return $subject && filled($this->data['batch'] ?? null)
            && app(MarkEntryService::class)->canEdit(auth()->user(), $subject, $this->data['batch']);
    }

    protected function isSuperAdmin(): bool
    {
        return $this->service()->isSuperAdmin(auth()->user());
    }

    protected function reasonOptions(): array
    {
        return $this->reasonOptions ??= Reson::pluck('name_ar', 'code')->all();
    }

    protected function searchMarks(Builder $query, string $column, string $search): Builder
    {
        return $query->whereHas('marks', fn ($q) => $q
            ->where($column, 'like', "%{$search}%")
            ->when($this->data['subject'] ?? null, fn ($q, $v) => $q->where('subject_code', $v)));
    }

    protected function heading(): string
    {
        $subject = $this->subject();

        return __('res.entry_mark').($subject ? " - {$subject->name_ar} [{$subject->crch}]" : '');
    }

    protected function emptyStateHeading(): string
    {
        $subject = $this->subject();

        if ($subject && filled($this->data['batch'] ?? null) && ! $this->canEditSelected()) {
            try {
                app(MarkEntryService::class)->assertCanEdit(auth()->user(), $subject, $this->data['batch']);
            } catch (DomainException $e) {
                return $e->getMessage();
            }
        }

        return __('No students found');
    }
}
