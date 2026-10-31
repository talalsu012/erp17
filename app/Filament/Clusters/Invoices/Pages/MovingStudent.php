<?php

// اريد المساعدة في اكمال هذا الكود
// اريد تعديل المستوي ونقلهم الي مستوي جديد عند اختيار الكلية والمستوي الحالي و العام الاكاديمي مع امكانية عرض الطلاب المراد نقلهم الي مستوي جديد

namespace App\Filament\Clusters\Invoices\Pages;

use App\Filament\Clusters\Reg\RegCluster;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Res\LtRecom;
use App\Models\Student;
use App\Services\Reg\StudentPromotionService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
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
        return __('main.movingStudent');
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

                Section::make('بيانات الترحيل')
                    ->description('اختيار معايير الطلاب المراد نقلهم')
                    ->schema([

                        Grid::make(3)->schema([

                            Select::make('faculty_code')
                                ->label('الكلية')
                                ->options(fn () => auth()->user()->constrainToAcademicScope(Faculty::query(), ['faculty' => 'code'])->pluck('name_ar', 'code'))
                                ->searchable()

                                ->required(),

                            Select::make('recom_code')
                                ->label(__('res.recom'))
                                ->multiple()
                                ->options(fn () => LtRecom::pluck('name_ar', 'code'))
                                ->searchable()

                                ->required()
                                ->columnSpan(2),

                        ]),
                    ]),

                Section::make('الوضع الحالي')
                    ->schema([

                        Grid::make(2)->schema([

                            Select::make('current_level')
                                ->label('المستوى الحالي')
                                ->options(fn () => Level::pluck('name_ar', 'code'))
                                ->required(),

                            Select::make('current_batch_code')
                                ->label('العام الأكاديمي الحالي')
                                ->options(fn () => LtColumn::where('grouped', '2003')->pluck('name_ar', 'code'))
                                ->required(),
                        ]),
                    ]),

                Section::make('الوضع الجديد')
                    ->schema([

                        Grid::make(2)->schema([

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

                        Grid::make(2)->schema([

                            Action::make('preview')
                                ->label('عرض الطلاب')
                                ->icon('heroicon-o-eye')
                                ->color('info')
                                ->action(function () {

                                    $data = $this->promotionForm->getState();
                                    $this->loadStudentsForPromotion();
                                }),

                            Action::make('execute')
                                ->label('تنفيذ الترحيل')
                                ->icon('heroicon-o-arrow-path')
                                ->color('success')
                                ->visible(fn () => count($this->studentsToMove) > 0)
                                ->requiresConfirmation()
                                ->modalHeading('تأكيد الترحيل')
                                ->modalDescription('سيتم تحديث جميع الطلاب المطابقين للمعايير المحددة.')
                                ->action(fn () => $this->executePromotion()),

                        ]),
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
        $this->resetTable();
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
