<?php

// اريد المساعدة في اكمال هذا الكود
// اريد تعديل المستوي ونقلهم الي مستوي جديد عند اختيار الكلية والمستوي الحالي و العام الاكاديمي مع امكانية عرض الطلاب المراد نقلهم الي مستوي جديد

namespace App\Filament\Clusters\Invoices\Pages;

use App\Filament\Clusters\Reg\RegCluster;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Res\LtRecom;
use App\Models\Student;
use App\Reg\AbsStatus;
use App\Services\Reg\StudentPromotionService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class MovingStudent extends Page implements HasForms, HasTable
{
    use \Filament\Tables\Concerns\InteractsWithTable,
        InteractsWithForms;
    use HasPageShield;

    protected string $view = 'filament.clusters.invoices.pages.moving-student';

    protected static ?string $cluster = RegCluster::class;

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): string
    {
        return __('main.registration_procedures');
    }

    public static function getLabel(): ?string
    {
        return __('main.movingStudent');
    }

    public function getTitle(): string|Htmlable
    {
        return __('main.movingStudent');
    }

    public static function getPluralLabel(): ?string
    {
        return __('main.movingStudent');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('main.movingStudent'));
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public ?array $promotionData = [];

    /** @var list<int|string> */
    public array $studentsToMove = [];

    public function mount(): void
    {

        $this->promotionForm->fill();

    }

    /*
    |--------------------------------------------------------------------------
    | Student Form
    |--------------------------------------------------------------------------
    */
    public function promotionForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('promotionData')
            ->schema([

                Section::make('البحث عن الطلاب')
                    ->description('معايير الطلاب المراد ترحيلهم')
                    ->schema([
                        Grid::make(4)->schema([
                            Select::make('faculty_code')
                                ->label('الكلية')
                                ->options(fn () => auth()->user()->constrainToAcademicScope(Faculty::query(), ['faculty' => 'code'])->pluck('name_ar', 'code'))
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (callable $set) => $set('department_code', null)),

                            Select::make('department_code')
                                ->label('القسم')
                                ->options(fn (callable $get) => Department::query()->where('faculty_code', $get('faculty_code'))->pluck('name_ar', 'code'))
                                ->searchable()
                                ->placeholder('كل الأقسام'),

                            Select::make('exam_type')
                                ->label('الدور')
                                ->options(AbsStatus::class)
                                ->placeholder('كل الأدوار'),

                            Select::make('recom_code')
                                ->label(__('res.recom'))
                                ->multiple()
                                ->options(fn () => LtRecom::pluck('name_ar', 'code'))
                                ->searchable()
                                ->required(),
                        ]),
                    ]),

                Section::make('الترحيل')
                    ->description('الوضع الحالي والوضع الجديد')
                    ->schema([
                        Grid::make(4)->schema([
                            Select::make('current_level')
                                ->label('المستوى الحالي')
                                ->options(fn () => Level::pluck('name_ar', 'code'))
                                ->required(),

                            Select::make('current_batch_code')
                                ->label('العام الأكاديمي الحالي')
                                ->options(fn () => LtColumn::where('grouped', '2003')->pluck('name_ar', 'code'))
                                ->required(),

                            Select::make('new_level')
                                ->label('المستوى الجديد')
                                ->options(fn () => Level::pluck('name_ar', 'code'))
                                ->required()
                                ->rule('different:current_level'),

                            Select::make('new_batch_code')
                                ->label('العام الأكاديمي الجديد')
                                ->options(fn () => LtColumn::where('grouped', '2003')->pluck('name_ar', 'code'))
                                ->required()
                                ->rule('different:current_batch_code'),
                        ]),
                    ]),

                Section::make()
                    ->schema([

                        Actions::make([

                            Action::make('preview')
                                ->label('عرض الطلاب')
                                ->icon('heroicon-o-eye')
                                ->color('info')
                                ->action(fn () => $this->loadStudentsForPromotion()),

                            Action::make('execute')
                                ->label('تنفيذ الترحيل')
                                ->icon('heroicon-o-arrow-path')
                                ->color('success')
                                ->visible(fn () => count($this->studentsToMove) > 0)
                                ->requiresConfirmation()
                                ->modalHeading('تأكيد الترحيل')
                                ->modalDescription('سيتم تحديث جميع الطلاب المطابقين للمعايير المحددة.')
                                ->action(fn () => $this->executePromotion()),

                        ])->key('promotionActions'),
                    ]),
            ]);
    }

    public function loadStudentsForPromotion(): void
    {
        $criteria = $this->promotionForm->getState();

        try {
            $this->studentsToMove = app(StudentPromotionService::class)
                ->query(auth()->user(), $criteria)
                ->pluck('university_no')
                ->all();
        } catch (DomainException $exception) {
            $this->studentsToMove = [];
            Notification::make()->title($exception->getMessage())->danger()->send();

            return;
        }

        $this->resetTable();

        if ($this->studentsToMove === []) {
            $d = app(StudentPromotionService::class)->diagnose(auth()->user(), $criteria);

            Notification::make()
                ->title('لا يوجد طلاب مطابقون')
                ->body("طلاب الكلية والمستوى والعام: {$d['students']} — لهم نتيجة في الفصل والعام نفسيهما: {$d['with_result']} — بالتوصية المختارة: {$d['with_recom']}".($d['with_exam_type'] === null ? '' : " — وبالدور المختار: {$d['with_exam_type']}"))
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('تم تحميل '.count($this->studentsToMove).' طالب')
            ->success()
            ->send();
    }

    public function executePromotion(): void
    {
        $data = $this->promotionForm->getState();

        try {
            $count = app(StudentPromotionService::class)
                ->promote(auth()->user(), $data, $data['new_level'] ?? null, $data['new_batch_code'] ?? null);
        } catch (DomainException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            return;
        }

        if ($count === 0) {
            Notification::make()->title('لا يوجد طلاب للترحيل')->danger()->send();

            return;
        }

        Notification::make()->title("تم ترحيل {$count} طالباً بنجاح")->success()->send();

        $this->studentsToMove = [];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                query: Student::query()
                    ->with('faculty', 'level', 'batch')
                    ->whereIn('university_no', $this->studentsToMove)
            )
            ->columns([
                TextColumn::make('university_no')
                    ->label(__('reg.university_no')),
                TextColumn::make('name_ar')
                    ->label(__('reg.name_ar')),
                TextColumn::make('faculty.name_ar')
                    ->label(__('reg.faculty')),
                TextColumn::make('level.name_ar')
                    ->label(__('reg.level')),
                TextColumn::make('batch.name_ar')
                    ->label(__('reg.batch')),
                // all_fees, pay, xmod, xstatus
                // TextColumn::make('all_fees')
                //     ->label('إجمالي الرسوم'),
                // TextColumn::make('pay')
                //     ->label('المدفوع'),
                TextColumn::make('xmod')
                    ->label(__('reg.xmod'))
                    ->badge()
                    ->color(fn ($record) => $record->xmod > 0 ? 'danger' : 'success'),

            ])
            ->filters([
                // ...
            ])
            ->recordActions([
                // ...
            ])
            ->toolbarActions([
                // ...
            ]);
    }
}
