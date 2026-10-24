<?php

namespace App\Filament\Clusters\Res\Resources\Recoms\Tables;

use App\Filament\Exports\Res\RecomExporter;
use App\Filament\Imports\Res\RecomImporter;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Res\LtRecom;
use App\Models\Res\Recom;
use App\Reg\AbsStatus;
use App\Reg\Semester;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecomsTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('id')->label(__('#'))->sortable(),
                TextColumn::make('recom_code')->label(__('reg.code'))->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('student.name_ar')->label(__('reg.name_ar'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('student.university_no')->label(__('reg.university_no'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('student.faculty.name_ar')->label(__('reg.faculty'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('student.department.name_ar')->label(__('reg.department'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('level.name_ar')->label(__('reg.level'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('batch_code')->label(__('reg.batch'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('recomName.name_ar')->label(__('res.recom'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('point')->label(__('res.point'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('semester')->label(__('res.semester'))->sortable()->searchable(),
                TextColumn::make('examType')->label(__('res.absent'))->sortable()->searchable(),
                TextColumn::make('countSubject')->label(__('res.CountSubject'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sumMarks')->label(__('res.SumMarks'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('s_pass')->label(__('res.SPass'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('s_fail')->label(__('res.SFail'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('s_abs')->label(__('res.SAbs'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('crch')->label(__('res.CrCh'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('points')->label(__('res.Points'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gpa')->label(__('res.GPA'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cgpa')->label(__('res.CGPA'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('note')->label(__('res.Note'))->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('reg.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

                // 1. تصفية الدفعة الأكاديمية (ابتداءً من عام 2023 فما فوق)
                SelectFilter::make('batch_code')
                    ->label(__('reg.batch'))
                    ->options(
                        LtColumn::query()
                            ->where('grouped', 2003)
                            ->where('code', '>=', 2023)
                            ->whereNotNull('code')
                            ->orderByDesc('code')
                            ->pluck('name_ar', 'code')
                            ->map(fn ($name, $code) => (string) ($name ?? $code))
                            ->toArray()
                    )
                    ->searchable()
                    ->multiple(),

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
                                    // إذا لم يتم تحديد كلية، نعرض كل الأقسام
                                    // (أو يمكنك إرجاع مصفوفة فارغة collect() إذا أردت إخفاءها)
                                    return [];
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
                            ))->columns(1),

                // 4. تصفية المستوى الأكاديمي
                SelectFilter::make('level_code')
                    ->label(__('reg.level'))
                    ->options(
                        Level::forAuthUser()
                            ->whereNotNull('code')
                            ->pluck('name_ar', 'code')
                            ->map(fn ($name, $code) => (string) ($name ?? $code))
                            ->toArray()
                    )
                    ->searchable()
                    ->multiple(),

                // Hidden::make('semester')->default(1),

                SelectFilter::make('examType')
                    ->label(__('res.absent'))
                    ->options(AbsStatus::class),

                QueryBuilder::make()
                    ->constraints([
                        NumberConstraint::make('gpa')
                            ->label(__('res.GPA'))
                            ->integer()
                            ->nullable(),
                        NumberConstraint::make('cgpa')
                            ->label(__('res.CGPA'))
                            ->integer()
                            ->nullable(),
                        SelectConstraint::make('recom_code')
                            ->label(__('res.recom'))
                            ->options(
                                LtRecom::query()
                                    ->whereNotNull('code')
                                    ->pluck('name_ar', 'code')
                                    ->map(fn ($name, $code) => (string) ($name ?? $code))
                                    ->toArray()
                            )
                            ->multiple(),
                    ]),
            ])
            ->filtersFormColumns(3)
            ->filtersFormWidth('4xl')
            ->recordActions([
                EditAction::make()
                    ->hidden(fn (Recom $record): bool => $record->is_locked && ! auth()->user()->isSuperAdmin()),
            ])
            ->headerActions([
                ImportAction::make()
                    ->label(__('res.ImportStudentsRecoms'))
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->outlined()
                    ->modalWidth('4xl')
                    ->importer(RecomImporter::class),
                ExportAction::make()
                    ->label(__('res.ExportStudentsRecoms'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->outlined()
                    ->modalWidth('4xl')
                    ->exporter(RecomExporter::class),
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
