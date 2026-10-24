<?php

namespace App\Filament\Clusters\Res\Resources\Subjects\Tables;

use App\Filament\Exports\Res\SubjectExporter;
use App\Filament\Imports\Res\SubjectImporter;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                auth()->user()->constrainToAcademicScope($query, includeUnassigned: ['department', 'level']);
            })
            ->columns([
                ToggleColumn::make('is_visible')->label(__('reg.is_enabled'))->toggleable(true),
                TextColumn::make('code')
                    ->label(__('reg.code'))
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('name_ar')
                    ->label(__('reg.name_ar'))
                    ->searchable(isIndividual: true, isGlobal: true)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('name_en')
                    ->label(__('reg.name_en'))
                    ->searchable(isIndividual: true, isGlobal: true)
                    ->sortable(),
                TextColumn::make('parent.name_ar')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->label(__('res.parent_subject')),
                TextColumn::make('faculty.name_ar')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->label(__('reg.faculty')),
                TextColumn::make('department.name_ar')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->label(__('reg.department')),
                TextColumn::make('special.name_ar')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->label(__('reg.special')),
                TextColumn::make('level.name_ar')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->label(__('reg.level')),
                TextColumn::make('semester_id')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->label(__('res.semester')),
                TextColumn::make('name_en')
                    ->label(__('reg.name_en'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('crch')->label(__('res.crch'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('exam')->label(__('res.exam'))
                    ->toggleable()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('plans.name')->label(__('res.plan'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('status')->label(__('res.status'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('types.name')->label(__('res.type'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->label(__('reg.is_visible'))
                    ->action(function ($record, $column) {
                        $name = $column->getName();
                        $record->update([
                            $name => ! $record->$name,
                        ]);
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            // ->reorderableColumns()
            ->deferColumnManager()
            ->columnManagerColumns(3)
            ->filters([

                SelectFilter::make('level_code')
                    ->label(__('reg.level'))
                    ->options(Level::forAuthUser()->get()->pluck('name_ar', 'code'))
                    ->multiple()
                    ->native(false),

                // SelectFilter::make('faculty_code')
                //     ->label(__('reg.faculty'))
                //     ->options(Faculty::get()->pluck('name_ar', 'code'))
                //     ->multiple()
                //     ->native(),

                Filter::make('faculty_and_department')
                    ->form([
                        Select::make('faculty_code')
                            ->label(__('reg.faculty'))
                            ->options(fn () => Faculty::forAuthUser()->pluck('name_ar', 'code'))
                            ->searchable()
                            ->multiple()
                            ->preload()
                            ->live(), // هذا السطر ضروري جداً لتحديث قائمة الأقسام فور الاختيار

                        Select::make('department_code')
                            ->label(__('reg.department'))
                            ->options(function (Get $get) {
                                // المتغير هنا سيكون مصفوفة (Array) بسبب خاصية multiple
                                $facultyCodes = $get('faculty_code');

                                if (empty($facultyCodes)) {
                                    // جلب فقط أقسام الكليات المسموح بها للمستخدم الحالي بدلاً من كل الأقسام
                                    $allowedFacultyCodes = Faculty::forAuthUser()->pluck('code');

                                    return Department::forAuthUser()->whereIn('faculty_code', $allowedFacultyCodes)
                                        ->pluck('name_ar', 'code');
                                }

                                // جلب الأقسام التابعة للكليات المختارة فقط
                                return Department::forAuthUser()->whereIn('faculty_code', $facultyCodes)
                                    ->pluck('name_ar', 'code');
                            })
                            ->searchable()
                            ->multiple()
                            ->preload(),
                    ])
                    ->query(
                        // تنفيذ الاستعلام (البحث) بناءً على القيم المختارة في الفلتر
                        fn (Builder $query, array $data): Builder => $query
                            ->when(
                                $data['faculty_code'] ?? null,
                                fn (Builder $query, $codes): Builder => $query->whereIn('faculty_code', $codes),
                            )
                            ->when(
                                $data['department_code'] ?? null,
                                fn (Builder $query, $codes): Builder => $query->whereIn('department_code', $codes),
                            ))->columns(2),

                SelectFilter::make('parent')
                    ->label(__('res.parent_subject'))
                    ->relationship('parent', 'name_ar', fn (Builder $query) => $query->where('parent_code', null))
                    ->searchable()->multiple(),

                SelectFilter::make('type')
                    ->label(__('res.type'))
                    ->options(LtColumn::all()
                        ->where('grouped', '9002')
                        ->pluck('name_ar', 'code'))
                    ->native(false),

                SelectFilter::make('plan')
                    ->label(__('res.plan'))
                    ->options(LtColumn::all()
                        ->where('grouped', '9001')
                        ->pluck('name_ar', 'code'))
                    ->native(false),

                TrashedFilter::make(),
            ],
                layout: FiltersLayout::Modal)
            ->filtersFormColumns(2)
            ->filtersFormWidth(Width::SixExtraLarge)
            ->deferFilters()
            ->deferLoading()

            ->headerActions([
                ImportAction::make()
                    ->label('Import')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->importer(SubjectImporter::class),

                ExportAction::make()
                    ->label('Export')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->exporter(SubjectExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
