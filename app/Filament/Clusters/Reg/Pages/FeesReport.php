<?php

namespace App\Filament\Clusters\Reg\Pages;

use App\Filament\Clusters\Reg\RegCluster;
use App\Models\Reg\Faculty;
use App\Models\Reg\Payment;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FeesReport extends Page implements HasTable
{
    use HasPageShield;
    use InteractsWithTable;

    protected string $view = 'filament.clusters.reg.pages.fees-report';

    protected static ?string $cluster = RegCluster::class;

    public ?string $startdate = null;

    public ?string $enddate = null;

    public static function getNavigationGroup(): string
    {
        return __('reg.reports');
    }

    public static function getLabel(): ?string
    {
        return __('reg.repofees');
    }

    public function getTitle(): string|Htmlable
    {
        return __('reg.repofees');
    }

    public static function getPluralLabel(): ?string
    {
        return __('reg.repofees');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('reg.repofees'));
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()
                    ->select([
                        'students.faculty_code as faculty_code',
                        'reg_faculties.name_ar as faculty_name',
                        DB::raw('COUNT(payments.university_no) as students_count'),
                        DB::raw('SUM(payments.amount) as total_amount'),
                        DB::raw('SUM(students.reg_fees) as total_reg_fees'),
                        DB::raw('SUM(students.fees) as total_fees'),

                        DB::raw('SUM(students.fees1) as fees1'),
                        DB::raw('SUM(students.fees2) as fees2'),

                        DB::raw('SUM(students.fees3) as fees3'),
                        DB::raw('SUM(students.fees5) as fees5'),
                        DB::raw('SUM(students.fees6) as fees6'),
                        DB::raw('SUM(students.fees7) as fees7'),
                        DB::raw('SUM(students.fees8) as fees8'),
                    ])
                    ->join('students', 'payments.university_no', '=', 'students.university_no')
                    ->join('reg_faculties', 'students.faculty_code', '=', 'reg_faculties.code')
                    ->whereHas('student.faculty.users', function ($q) {
                        $q->where('user_id', auth()->id());
                    })
                    // delete_at should be null
                    ->whereNull('payments.deleted_at')
                    ->whereNull('students.deleted_at')
                    ->groupBy('students.faculty_code', 'reg_faculties.name_ar')

                    // 🔴 التعديل هنا: خداع Filament لمنعه من إضافة payments.id للترتيب
                    ->tap(function (Builder $query) {
                        $query->getModel()->setTable('reg_faculties');
                        $query->getModel()->setKeyName('name_ar');
                    })
            )
            // 2. تحديد العمود الافتراضي للفرز في الواجهة
            ->defaultSort('faculty_name')
            ->columns([
                TextColumn::make('faculty_name')
                    ->label(__('reg.faculty'))
                    // 3. استخدام reorder بدلاً من orderBy ليقوم بإلغاء الترتيب الأساسي وتطبيق الترتيب الجديد عند الضغط
                    ->sortable(query: fn (Builder $query, string $direction) => $query->reorder('reg_faculties.name_ar', $direction)),

                TextColumn::make('students_count')
                    ->label(__('reg.counts'))
                    ->sortable(query: fn (Builder $query, string $direction) => $query->reorder('students_count', $direction))
                    ->summarize(Sum::make()) // هذا سيقوم بجمع عمود students_count في أسفل الجدول
                    ->formatStateUsing(fn ($state) => number_format((float) $state)),

                TextColumn::make('total_amount')
                    ->label(__('reg.amount'))
                    ->sortable(query: fn (Builder $query, string $direction) => $query->reorder('total_amount', $direction))
                    ->summarize(Sum::make()) // هذا سيقوم بجمع عمود total_amount ليعطي الإجمالي العام
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('total_reg_fees')
                    ->label(__('reg.regfees'))
                    ->sortable(query: fn (Builder $query, string $direction) => $query->reorder('total_reg_fees', $direction))
                    ->summarize(Sum::make()) // هذا سيقوم بجمع عمود total_amount ليعطي الإجمالي العام
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('total_fees')
                    ->label(__('reg.fees'))
                    ->sortable(query: fn (Builder $query, string $direction) => $query->reorder('total_fees', $direction))
                    ->summarize(Sum::make()) // هذا سيقوم بجمع عمود total_amount ليعطي الإجمالي العام
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),

                TextColumn::make('fees1')
                    ->label(__('reg.fees1'))
                    ->summarize(Sum::make()) // هذا سيقوم بجمع عمود total_amount ليعطي الإجمالي العام
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('fees2')
                    ->label(__('reg.fees2'))
                    ->summarize(Sum::make()) // هذا سيقوم بجمع عمود total_amount ليعطي الإجمالي العام
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),

                TextColumn::make('fees3')
                    ->label(__('reg.fees3'))
                    ->summarize(Sum::make()) // هذا سيقوم بجمع عمود total_amount ليعطي الإجمالي العام
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('fees5')
                    ->label(__('reg.fees5'))
                    ->summarize(Sum::make()) // هذا سيقوم بجمع عمود total_amount ليعطي الإجمالي العام
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('fees6')
                    ->label(__('reg.fees6'))
                    ->summarize(Sum::make()) // هذا سيقوم بجمع عمود total_amount ليعطي الإجمالي العام
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('fees7')
                    ->label(__('reg.fees7'))
                    ->summarize(Sum::make()) // هذا سيقوم بجمع عمود total_amount ليعطي الإجمالي العام
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('fees8')
                    ->label(__('reg.fees8'))
                    ->summarize(Sum::make()) // هذا سيقوم بجمع عمود total_amount ليعطي الإجمالي العام
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
            ])
            ->filters([
                Filter::make('reg.bankdate')
                    ->form([
                        DatePicker::make('from')
                            ->label(__('reg.startdate'))
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->placeholder('DD/MM/YYYY')
                            ->columnSpan(1),
                        DatePicker::make('to')
                            ->label(__('reg.enddate'))
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->placeholder('DD/MM/YYYY')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('payments.bankdate', '>=', $date))
                        ->when($data['to'] ?? null, fn ($q, $date) => $q->whereDate('payments.bankdate', '<=', $date))),

                SelectFilter::make('student.level_code')
                    ->label(__('reg.level'))
                    ->columnSpan(1)
                    ->relationship('student.level', 'name_ar'),

                SelectFilter::make('batch_code')
                    ->label(__('reg.batch'))
                    ->columnSpan(1)
                    ->relationship('batch', 'name_ar'),

                SelectFilter::make('student.faculty_code')
                    ->label(__('reg.faculty'))
                    ->columnSpan(1)
                    ->options(fn () => Faculty::whereHas('users', function ($q) {
                        $q->where('user_id', auth()->id());
                    })->pluck('name_ar', 'code'))
                    ->query(function (Builder $query, array $data) {
                        if (! empty($data['value'])) {
                            $query->where('students.faculty_code', $data['value']);
                        }
                    }),

                SelectFilter::make('student.admission_code')
                    ->label(__('reg.admission'))
                    ->columnSpan(1)
                    ->relationship(
                        name: 'student.admission',
                        titleAttribute: 'name_ar',
                        modifyQueryUsing: fn (Builder $query) => $query->where('grouped', '2001')
                    ),
                SelectFilter::make('bank')
                    ->label(__('reg.bank'))
                    ->options(
                        Payment::query()
                            ->whereNotNull('bank')
                            ->where('bank', '<>', '')
                            ->distinct()
                            ->orderBy('bank')
                            ->pluck('bank', 'bank')
                            ->toArray()
                    ),

            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3);
    }

    public function getTableRecordKey(Model|array $record): string
    {
        if (is_array($record)) {
            if (! empty($record['faculty_code'])) {
                return (string) $record['faculty_code'];
            }
            if (! empty($record['faculty_name'])) {
                return (string) $record['faculty_name'];
            }

            return '';
        }

        // if ($record - Model) {
        //     return (string) ($record->faculty_code ?? $record->getKey() ?? '');
        // }

        return '';
    }
}
