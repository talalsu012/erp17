<?php

namespace App\Filament\Clusters\Res\Pages\EntryMarks;

use App\Helpers\Res\MarkHelper;
use App\Models\Res\Mark;
use App\Models\Res\Reson;
use App\Models\Res\Subject;
use App\Models\Student;
use App\Reg\MarkApprovalStage;
use App\Reg\ShowAbsentCode;
use App\Services\Res\StudentMarksQueryBuilder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

trait EntryMarkTable
{
    protected StudentMarksQueryBuilder $queryBuilder;

    public function bootEntryMarkTable(): void
    {
        $this->queryBuilder = app(StudentMarksQueryBuilder::class);
    }

    public function table(Table $table): Table
    {

        $data = [
            'batch' => $this->batch ?? null,
            'subject' => $this->subject ?? null,
            'level' => $this->level ?? null,
            'faculty' => $this->faculty ?? null,
            'department' => $this->department ?? null,
            'special' => $this->special ?? null,
            'absent' => $this->absent ?? null,
            'semester' => $this->semester ?? null,
        ];

        return $table
            ->query(fn () => $this->queryBuilder->build($data))
            // show heading
            ->heading(fn () => $this->getTiqanatTableHeading())
            ->description(fn () => $this->getTiqanatTableDescription())   // اختياري
            ->defaultSort('name_ar', 'asc')
            ->columns($this->getMarkEntryColumns())
            ->emptyStateHeading(fn () => $this->getTiqanatEmptyStateHeading())
            ->emptyStateDescription(fn () => $this->getTiqanatEmptyStateDescription())
            ->recordActions([

            ])
            ->toolbarActions([]);
    }

    protected function getTiqanatTableHeading(): string
    {
        if (empty($this->subject)) {
            return __('res.entry_mark');
        }

        $subject = Subject::find($this->subject);

        return __('res.entry_mark').($subject
            ? ' - '.$subject->name_ar.' ['.($subject->crch ?? '').']'
            : ' - '.__('Unknown Subject'));
    }

    protected function getTiqanatTableDescription(): ?string
    {
        // يمكنك إضافة معلومات مثل اسم الدفعة أو الفصل هنا
        return null;
    }

    // ────── Empty State ──────
    protected function getTiqanatEmptyStateHeading(): string
    {
        $stageValue = $this->getResultService()->getCurrentStage($this->subject, $this->batch);
        $currentStageEnum = MarkApprovalStage::tryFrom($stageValue);
        $userRole = auth()->user()->roles()->first()?->name ?? '';

        if ($stageValue !== $userRole && $currentStageEnum) {
            return 'المادة حالياً في مرحلة: '.$currentStageEnum->getLabel();
        }

        return __('No students found');
    }

    protected function getTiqanatEmptyStateDescription(): ?string
    {
        $stageValue = $this->getResultService()->getCurrentStage($this->subject, $this->batch);
        $userRole = auth()->user()->roles()->first()?->name ?? '';
        $currentStageEnum = MarkApprovalStage::tryFrom($stageValue);

        if ($stageValue !== $userRole && $currentStageEnum) {
            return "عذراً، لا تملك صلاحية العرض أو التعديل في المرحلة الحالية ({$currentStageEnum->getLabel()}).";
        }

        return null;
    }

    protected function getMarkEntryColumns(): array
    {
        return [
            SelectColumn::make('reason')
                ->label(__('res.reason'))
                ->searchable(isIndividual: true, isGlobal: true)
                ->options(Reson::pluck('name_ar', 'code'))
                ->updateStateUsing(function (Student $record, $state) {
                    MarkHelper::InsertReson(
                        $record->university_no,
                        $this->subject,
                        $state,
                        $this->absent,
                        $this->batch,
                    );
                })
                ->width('10%')
                ->getStateUsing(fn (Student $record) => $this->subject
                    ? $record->marks->where('subject_code', $this->subject)->first()?->reason?->code
                    : null),

            TextColumn::make('university_no')
                ->label(__('reg.university_no'))
                ->width('12%')
                ->searchable(isIndividual: true, isGlobal: true)
                ->sortable(),

            TextColumn::make('name_ar')
                ->label(__('reg.name_ar'))
                ->searchable(isIndividual: true, isGlobal: true)
                ->sortable()
                ->width('25%'),

            TextInputColumn::make('marks.mark')
                ->label(__('res.exam'))
                ->rules(['nullable', 'numeric', 'min:0'])
                ->searchable(
                    isIndividual: true,
                    isGlobal: true,
                    query: fn (Builder $query, string $search) => $query
                        ->whereHas('marks', fn ($q) => $q
                            ->where('mark', 'like', "%{$search}%")
                            ->when($this->subject, fn ($q) => $q->where('subject_code', $this->subject)))
                )

                ->disabled(function ($record) {
                    // Safely get the first mark's grade
                    $firstMark = $record->marks->first();

                    // Only check if we have a mark and $this->absent == 2
                    if ($this->absent == 2 && $firstMark) {
                        return ! in_array(
                            (string) $firstMark->grade,
                            ['راسب', ...ShowAbsentCode::getMarkValues()]
                        );
                    }

                    return false;
                })
                ->inputMode('decimal')
                ->width('10%')
                // ->getStateUsing($this->absent)
                ->getStateUsing(fn (Student $record) => $this->subject
                    ? $record->marks
                        ->where('subject_code', $this->subject)
                        ->where('examType', $this->absent)
                        ->first()?->mark
                    : null)
                ->updateStateUsing(function (Student $record, $state) {

                    $currentCwork = $record->marks
                        ->where('subject_code', $this->subject)
                        ->where('examType', $this->absent)
                        ->first()?->cwork ?? 0;

                    MarkHelper::InsertMark(
                        $record->university_no,
                        $this->subject,
                        $this->absent,
                        $this->batch,
                        $state,
                        $currentCwork,
                        'mark',

                    );

                }),
            TextInputColumn::make('marks.cwork')
                ->label(__('res.cwork'))
                ->rules(['nullable', 'numeric', 'min:0'])
                ->searchable(
                    isIndividual: true,
                    isGlobal: true,
                    query: fn (Builder $query, string $search) => $query
                        ->whereHas('marks', fn ($q) => $q
                            ->where('cwork', 'like', "%{$search}%")
                            ->when($this->subject, fn ($q) => $q->where('subject_code', $this->subject)))
                )

                ->disabled(function ($record) {
                    // Safely get the first mark's grade
                    $firstMark = $record->marks->first();

                    // Only check if we have a mark and $this->absent == 2
                    if ($this->absent == 2 && $firstMark) {
                        return ! in_array(
                            (string) $firstMark->grade,
                            ['راسب', ...ShowAbsentCode::getMarkValues()]
                        );
                    }

                    return false;
                })
                // visible only when subject exam less than 100
                ->visible(fn () => $this->subject && Subject::find($this->subject)->exam < 100)
                ->inputMode('decimal')
                ->width('10%')
                // ->getStateUsing($this->absent)
                ->getStateUsing(fn (Student $record) => $this->subject
                    ? $record->marks
                        ->where('subject_code', $this->subject)
                        ->where('examType', $this->absent)
                        ->first()?->cwork
                    : null)
                ->updateStateUsing(function (Student $record, $state) {
                    $currentMark = $record->marks
                        ->where('subject_code', $this->subject)
                        ->where('examType', $this->absent)
                        ->first()?->mark ?? 0;

                    MarkHelper::InsertMark(
                        $record->university_no,
                        $this->subject,
                        $this->absent,
                        $this->batch,
                        $currentMark,
                        $state,
                        'cwork',
                    );
                }),
            TextColumn::make('marks.grade')
                ->label(__('res.grade'))
                ->sortable()
                ->width('10%')
                ->searchable(isIndividual: true, isGlobal: true),

            TextColumn::make('approval_stage')
                ->label('المرحلة')
                ->badge()
                ->toggleable(isToggledHiddenByDefault: true)
                ->color(fn (MarkApprovalStage $state) => $state->color())
                ->formatStateUsing(fn (MarkApprovalStage $state) => $state->label()),

            TextColumn::make('approved_at')
                ->label('تاريخ الموافقة')
                ->toggleable(isToggledHiddenByDefault: true)
                ->dateTime('d/m/Y H:i'),

            TextColumn::make('grouping.name')
                ->label('المجموعة')
                ->badge()
                ->searchable(isIndividual: true, isGlobal: true)
                ->toggleable(isToggledHiddenByDefault: true)
                ->listWithLineBreaks()
                ->getStateUsing(fn (Student $record) => $record->grouping->map(fn ($g) => $g->name)->filter()->implode(', ')),
            TextColumn::make('grouping.room')
                ->label('القاعة')
                ->badge()
                ->toggleable(isToggledHiddenByDefault: true)
                ->color('success')
                ->icon('heroicon-m-envelope')
                ->listWithLineBreaks()
                ->getStateUsing(fn (Student $record) => $record->grouping->map(fn ($g) => $g->pivot->room?->name)->filter()->implode(', ')),

        ];
    }
}
