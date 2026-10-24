<?php

namespace App\Filament\Clusters\Reg\Pages;

use App\Filament\Clusters\Reg\RegCluster;
use App\Filament\Widgets\StatusWidget;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\LtColumn;
use App\Models\Student;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class StatusReport extends Page implements HasTable
{
    use HasPageShield ,InteractsWithTable;

    protected static ?string $cluster = RegCluster::class;

    protected string $view = 'filament.clusters.reg.pages.status-report';

    public static function getNavigationGroup(): string
    {
        return __('reg.reports');
    }

    public static function getLabel(): ?string
    {
        return __('reg.statusReport');
    }

    public function getTitle(): string|Htmlable
    {
        return __('reg.statusReport');
    }

    public static function getPluralLabel(): ?string
    {
        return __('reg.statusReport');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('reg.statusReport'));
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function getTableRecordKey(Model|array $record): string
    {

        if (is_array($record)) {
            return (string) ($record['faculty_code'] ?? $record['id'] ?? uniqid());
        }

        return (string) ($record->faculty_code ?? $record->id ?? uniqid());
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatusWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::query()
                    ->selectRaw('faculty_code as id') // Filament سيعتبر هذا الحقل كمعرف للصف
                    ->selectRaw("SUM(CASE WHEN xstatus = '20070001' THEN 1 ELSE 0 END) AS complete")
                    ->selectRaw("SUM(CASE WHEN xstatus = '20070002' THEN 1 ELSE 0 END) AS noreg")
                    ->selectRaw("SUM(CASE WHEN xstatus = '20070003' THEN 1 ELSE 0 END) AS unreg")
                    ->selectRaw('COUNT(*) AS total')
                    ->whereHas('faculty', function ($q) {
                        $q->whereHas('users', function ($userQuery) {
                            $userQuery->where('user_id', auth()->id());
                        });
                    })
                    ->groupBy('faculty_code')

                    // 🔴 التعديل هنا: إيقاف الترتيب الافتراضي (university_no) واستبداله بـ faculty_code
                    ->tap(function (\Illuminate\Database\Eloquent\Builder $query) {
                        $query->getModel()->setKeyName('faculty_code');
                    })
            )
            ->recordAction(null)
            ->defaultSort('faculty_code') // تحديد الترتيب الافتراضي الصريح
            ->paginated(true)
            // ->defaultPaginationPageOption(null)
            ->columns([
                TextColumn::make('id')
                    ->label(__('reg.faculty'))
                    ->formatStateUsing(fn ($state) => optional(Faculty::where('code', $state)->first())->name_ar ?? $state),

                TextColumn::make('total')
                    ->label(' ')
                    ->color('primary')
                    ->summarize(Sum::make()->label(' ')),

                TextColumn::make('complete')
                    ->label(__('reg.Complete'))
                    ->description(fn (Model $record) => '('.number_format(($record->complete / max($record->total, 1)) * 100, 2).'%)')
                    ->color('success')
                    ->summarize(
                        Sum::make()
                            ->label(' ')
                            ->formatStateUsing(function ($state, $livewire) {
                                $totalComplete = $state;
                                $totalStudents = $livewire->getFilteredTableQuery()
                                    ->get()
                                    ->sum('total');

                                $percentage = $totalStudents > 0
                                    ? number_format(($totalComplete / $totalStudents) * 100, 2)
                                    : 0;

                                return "{$totalComplete} ({$percentage}%)";
                            })
                    ),

                TextColumn::make('noreg')
                    ->label(__('reg.noreg'))
                    ->description(fn (Model $record) => '('.number_format(($record->noreg / max($record->total, 1)) * 100, 2).'%)')
                    ->color('warning')
                    ->summarize(
                        Sum::make()
                            ->label(' ')
                            ->formatStateUsing(function ($state, $livewire) {
                                $totalNoreg = $state;
                                $totalStudents = $livewire->getFilteredTableQuery()
                                    ->get()
                                    ->sum('total');

                                $percentage = $totalStudents > 0
                                    ? number_format(($totalNoreg / $totalStudents) * 100, 2)
                                    : 0;

                                return "{$totalNoreg} ({$percentage}%)";
                            })
                    ),

                TextColumn::make('unreg')
                    ->label(__('reg.unRegister'))
                    ->description(fn (Model $record) => '('.number_format(($record->unreg / max($record->total, 1)) * 100, 2).'%)')
                    ->color('danger')
                    ->summarize(
                        Sum::make()
                            ->label(' ')
                            ->formatStateUsing(function ($state, $livewire) {
                                $totalUnreg = $state;
                                $totalStudents = $livewire->getFilteredTableQuery()
                                    ->get()
                                    ->sum('total');

                                $percentage = $totalStudents > 0
                                    ? number_format(($totalUnreg / $totalStudents) * 100, 2)
                                    : 0;

                                return "{$totalUnreg} ({$percentage}%)";
                            })
                    ),
            ])
            ->filters([
                SelectFilter::make('faculty')
                    ->label(__('reg.faculty'))
                    ->options(
                        Faculty::all()
                            ->mapWithKeys(fn ($faculty) => [
                                $faculty->code => "{$faculty->name_ar} ({$faculty->code})",
                            ])
                    )
                    ->attribute('faculty_code')
                    ->searchable()
                    ->multiple()
                    ->preload(),

                SelectFilter::make(__('reg.level'))
                    ->relationship('level', 'name_ar')
                    ->attribute('level_code')
                    ->searchable()
                    ->multiple()
                    ->preload(),

                SelectFilter::make('admission_code')
                    ->label(__('reg.admission'))
                    ->options(
                        LtColumn::all()
                            ->where('grouped', '2001')
                            ->pluck('name_ar', 'code')
                    ),

                SelectFilter::make('gender_code')
                    ->label(__('reg.gender'))
                    ->options(
                        LtColumn::all()
                            ->where('grouped', '1001')
                            ->pluck('name_ar', 'code')
                    ),

                QueryBuilder::make()
                    ->constraintPickerColumns(4)
                    ->constraints([
                        SelectConstraint::make('batch_code')
                            ->label(__('reg.batch'))
                            ->options(
                                LtColumn::all()
                                    ->where('grouped', '2003')
                                    ->pluck('name_ar', 'code')
                            ),

                        SelectConstraint::make('department_code')
                            ->label(__('reg.department'))
                            ->options(
                                Department::all()
                                    ->pluck('name_ar', 'code')
                            ),

                        SelectConstraint::make('academic_year_code')
                            ->label(__('reg.academic_year'))
                            ->options(
                                LtColumn::all()
                                    ->where('grouped', '2003')
                                    ->pluck('name_ar', 'code')
                            ),

                        BooleanConstraint::make('gender_code')->label(__('reg.gender')),
                        NumberConstraint::make('fees')->label(__('reg.fees')),
                        NumberConstraint::make('reg_fees')->label(__('reg.regfees')),

                        SelectConstraint::make('installment_code')
                            ->label(__('reg.Installments'))
                            ->options(
                                LtColumn::all()
                                    ->where('grouped', '2005')
                                    ->pluck('name_ar', 'code')
                            ),

                        NumberConstraint::make('all_fees')->label(__('reg.all_fees')),
                        NumberConstraint::make('xmod')->label(__('reg.xmod')),
                    ]),
            ], layout: FiltersLayout::Modal)
            ->filtersFormColumns(3)
            ->filtersFormWidth(Width::SixExtraLarge);
    }
}
