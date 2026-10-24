<?php

namespace App\Filament\Clusters\Res\Resources\Marks\Tables;

use App\Filament\Exports\Res\MarkExporter;
use App\Filament\Imports\Res\MarkImporter;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\LtColumn;
use App\Models\Reg\Special;
use App\Models\Res\Mark;
use App\Models\Res\Subject;
use App\Reg\AbsStatus;
use App\Reg\Semester;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class MarksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('university_no')
                    ->label(__('reg.university_no'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.name_ar')
                    ->label(__('reg.student'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('subject_code')
                    ->label(__('res.code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject.name_ar')
                    ->label(__('res.subject'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('faculty.name_ar')
                    ->label(__('reg.faculty'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('level.name_ar')
                    ->label(__('reg.level'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('batch.name_ar')
                    ->label(__('reg.batch'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('semester')
                    ->label(__('res.semester'))
                    ->getStateUsing(fn ($record) => $record->semester ? Semester::tryFrom($record->semester)?->getLabel() ?? $record->semester : '-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('examType')
                    ->label(__('res.absent'))
                    ->getStateUsing(fn ($record) => $record->examType ? AbsStatus::tryFrom($record->examType)?->getLabel() ?? $record->examType : '-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mark')
                    ->label(__('res.mark'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cwork')
                    ->label(__('res.cwork'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grade')
                    ->label(__('res.grade'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('point')
                    ->label(__('res.point'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('batch_code')
                    ->relationship('batch', 'code')
                    ->label(__('reg.batch')),

                SelectFilter::make('grade')
                    ->options(fn () => Mark::query()
                        ->whereNotNull('grade')
                        ->where('grade', '!=', '')
                        ->distinct()
                        ->pluck('grade', 'grade')
                        ->map(fn ($grade) => (string) $grade)
                        ->toArray())
                    ->multiple()
                    ->searchable()
                    ->label(__('res.grade')),

                SelectFilter::make('subject')
                    ->attribute('subject_code')
                    ->label(__('res.subject'))
                    ->options(fn () => Subject::with(['faculty', 'level'])
                        ->whereNotNull('code')
                        ->get()
                        ->filter(fn ($subject) => filled($subject->code))
                        ->mapWithKeys(function ($subject) {
                            $subjectName = $subject->name_ar ?: ($subject->name_en ?: 'بدون اسم');
                            $facultyName = $subject->faculty?->name_ar ?? '-';
                            $levelName = $subject->level?->name_ar ?? '-';

                            return [
                                (string) $subject->code => sprintf(
                                    '%s | %s | %s | %s',
                                    $subjectName,
                                    $subject->code,
                                    $facultyName,
                                    $levelName
                                ),
                            ];
                        })
                        ->toArray())
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search) => Subject::with(['faculty', 'level'])
                        ->whereNotNull('code')
                        ->where(function ($q) use ($search) {
                            $q->where('name_ar', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        })
                        ->limit(50)
                        ->get()
                        ->mapWithKeys(function ($subject) {
                            $subjectName = $subject->name_ar ?: ($subject->name_en ?: 'بدون اسم');
                            $facultyName = $subject->faculty?->name_ar ?? '-';
                            $levelName = $subject->level?->name_ar ?? '-';

                            return [
                                (string) $subject->code => sprintf(
                                    '%s | %s | %s | %s',
                                    $subjectName,
                                    $subject->code,
                                    $facultyName,
                                    $levelName
                                ),
                            ];
                        })
                        ->toArray())
                    ->columnSpan(3),
            ])
            ->filtersFormColumns(3)
            ->filtersFormWidth('4xl')
            ->recordActions([
                EditAction::make()
                    ->hidden(fn (Mark $record): bool => $record->is_locked && ! auth()->user()->isSuperAdmin()),
            ])
            ->headerActions([
                ImportAction::make()
                    ->label(__('res.ImportStudentsMarks'))
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('primary')
                    ->size('md')
                    ->importer(MarkImporter::class),

                ExportAction::make()
                    ->label(__('res.ExportStudentsMarks'))
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-down')
                    ->exporter(MarkExporter::class),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('updateAcademicData')
                        ->label('تعديل البيانات الأكاديمية')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('تعديل البيانات الأكاديمية للسجلات المحددة')
                        ->modalDescription('سيتم تعديل العام الأكاديمي والفصل ونوع الامتحان لكل السجلات المحددة.')
                        ->modalSubmitActionLabel('تحديث')
                        ->form([
                            Select::make('batch_code')
                                ->label(__('reg.batch'))
                                ->options(
                                    LtColumn::query()
                                        ->where('grouped', 2003)
                                        ->whereNotNull('code')
                                        ->orderByDesc('code')
                                        ->pluck('name_ar', 'code')
                                        ->map(fn ($name, $code) => (string) ($name ?? $code))
                                )
                                ->searchable()
                                ->preload()
                                ->placeholder('اتركه فارغاً لعدم التعديل'),

                            Select::make('semester')
                                ->label(__('res.semester'))
                                ->options(Semester::class)
                                ->placeholder('اتركه فارغاً لعدم التعديل'),

                            Select::make('examType')
                                ->label(__('res.absent'))
                                ->options(AbsStatus::class)
                                ->placeholder('اتركه فارغاً لعدم التعديل'),

                            Select::make('faculty_code')
                                ->label(__('reg.faculty'))
                                ->options(
                                    Faculty::query()
                                        ->whereNotNull('code')
                                        ->selectRaw('code, CONCAT(COALESCE(name_ar, "بدون اسم"), " (", code, ")") as name_with_code')
                                        ->pluck('name_with_code', 'code')
                                        ->map(fn ($name, $code) => (string) ($name ?? $code))
                                )
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function (Set $set) {
                                    $set('department_code', null);
                                    $set('special_code', null);
                                })
                                ->placeholder('اتركه فارغاً لعدم التعديل'),

                            Select::make('department_code')
                                ->label(__('reg.department'))
                                ->options(fn (Get $get) => Department::query()
                                    ->whereNotNull('code')
                                    ->when($get('faculty_code'), fn ($q, $faculty) => $q->where('faculty_code', $faculty))
                                    ->pluck('name_ar', 'code')
                                    ->map(fn ($name, $code) => (string) ($name ?? $code)))
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(fn (Set $set) => $set('special_code', null))
                                ->placeholder('اتركه فارغاً لعدم التعديل'),

                            Select::make('special_code')
                                ->label(__('reg.special'))
                                ->options(fn (Get $get) => Special::query()
                                    ->whereNotNull('code')
                                    ->when($get('department_code'), fn ($q, $dept) => $q->where('department_code', $dept))
                                    ->pluck('name_ar', 'code')
                                    ->map(fn ($name, $code) => (string) ($name ?? $code)))
                                ->searchable()
                                ->placeholder('اتركه فارغاً لعدم التعديل'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $updateData = collect($data)
                                ->only(['batch_code', 'semester', 'examType', 'faculty_code', 'department_code', 'special_code'])
                                ->filter(fn ($value) => filled($value))
                                ->all();

                            if (empty($updateData)) {
                                return;
                            }

                            $skipped = 0;
                            $query = Mark::query()->whereIn('id', $records->pluck('id'));

                            if (! auth()->user()->isSuperAdmin()) {
                                $skipped = (clone $query)->where('is_locked', true)->count();
                                $query->where('is_locked', false);
                            }

                            $query->update($updateData);

                            if ($skipped > 0) {
                                Notification::make()
                                    ->title("تم تخطي {$skipped} نتيجة معتمدة ومقفلة")
                                    ->warning()
                                    ->send();
                            }
                        }),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
