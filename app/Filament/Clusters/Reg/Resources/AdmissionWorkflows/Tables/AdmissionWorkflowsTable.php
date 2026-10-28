<?php

namespace App\Filament\Clusters\Reg\Resources\AdmissionWorkflows\Tables;

use App\Filament\Clusters\Reg\Resources\Students\Schemas\StudentFeesSchema;
use App\Models\Reg\AdmissionWorkflow;
use App\Models\Student;
use App\Reg\AdmissionStage;
use App\Services\AdmissionWorkflowService;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdmissionWorkflowsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('university_no')->label('الرقم الجامعي')->searchable()->sortable(),
                TextColumn::make('student.name_ar')->label('الطالب')->searchable(),
                TextColumn::make('stage')->label('المرحلة')->badge()->sortable(),
                TextColumn::make('medical_fitness')
                    ->label('الطبيب')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'fit' => 'لائق',
                        'unfit' => 'غير لائق',
                        default => '—',
                    }),
                TextColumn::make('committee_decision')
                    ->label('اللجنة')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'accepted' => 'توصية بالقبول',
                        'rejected' => 'توصية بالرفض',
                        default => '—',
                    }),
                TextColumn::make('assigned_fees')->label('الرسوم')->numeric()->placeholder('—'),
                TextColumn::make('paid_at')->label('تاريخ الدفع')->dateTime()->placeholder('—')->toggleable(),
                TextColumn::make('rejection_reason')->label('سبب الرفض')->limit(40)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('stage')->label('المرحلة')->options(AdmissionStage::class),
            ])
            ->recordActions([
                self::step('medical', 'قرار الطبيب', AdmissionStage::AwaitingDoctor, AdmissionWorkflowService::ROLE_DOCTOR, [
                    Radio::make('fitness')->label('اللياقة الطبية')->options(['fit' => 'لائق', 'unfit' => 'غير لائق'])->required(),
                    Textarea::make('notes')->label('ملاحظات الطبيب'),
                ], fn (AdmissionWorkflowService $s, AdmissionWorkflow $r, array $d) => $s->recordMedical(auth()->user(), $r, $d['fitness'], $d['notes'] ?? null)),

                self::step('committee', 'توصية لجنة الكلية', AdmissionStage::AwaitingCommittee, AdmissionWorkflowService::ROLE_COMMITTEE, [
                    Radio::make('decision')->label('التوصية')->options(['accept' => 'قبول', 'reject' => 'رفض'])->required()->live(),
                    Textarea::make('notes')->label('ملاحظات اللجنة'),
                    Textarea::make('reason')->label('سبب الرفض')->visible(fn ($get): bool => $get('decision') === 'reject')->required(fn ($get): bool => $get('decision') === 'reject'),
                ], fn (AdmissionWorkflowService $s, AdmissionWorkflow $r, array $d) => $s->recordCommittee(auth()->user(), $r, $d['decision'] === 'accept', $d['notes'] ?? null, $d['reason'] ?? null)),

                self::step('fees', 'تحديد الرسوم', AdmissionStage::AwaitingFees, AdmissionWorkflowService::ROLE_REGISTRAR,
                    StudentFeesSchema::components(),
                    fn (AdmissionWorkflowService $s, AdmissionWorkflow $r, array $d) => $s->assignStudentFees(auth()->user(), $r, $d),
                    fn (AdmissionWorkflow $r): array => Student::query()
                        ->whereKey($r->university_no)
                        ->first([...AdmissionWorkflowService::STUDENT_FEE_FIELDS, ...AdmissionWorkflowService::STUDENT_INSTALLMENT_FIELDS])
                        ?->toArray() ?? [],
                )->modalWidth(Width::SixExtraLarge),

                self::step('approve', 'الاعتماد النهائي', AdmissionStage::AwaitingDean, AdmissionWorkflowService::ROLE_DEAN, [
                    Textarea::make('notes')->label('ملاحظات العميد'),
                ], fn (AdmissionWorkflowService $s, AdmissionWorkflow $r, array $d) => $s->approve(auth()->user(), $r, $d['notes'] ?? null)),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    /**
     * إجراء خطوة: يظهر فقط في مرحلته وللدور المعني (والمدير الأعلى)، والخدمة تعيد التحقق عند التنفيذ.
     *
     * @param  list<mixed>  $schema
     * @param  callable(AdmissionWorkflowService, AdmissionWorkflow, array<string, mixed>): mixed  $run
     * @param  (callable(AdmissionWorkflow): array<string, mixed>)|null  $fill  القيم الابتدائية للنموذج
     */
    private static function step(string $name, string $label, AdmissionStage $stage, string $role, array $schema, callable $run, ?callable $fill = null): Action
    {
        return Action::make($name)
            ->label($label)
            ->schema($schema)
            ->fillForm($fill)
            ->visible(fn (AdmissionWorkflow $record): bool => $record->stage === $stage
                && (auth()->user()?->hasAnyRole([$role, AdmissionWorkflowService::ROLE_SUPER_ADMIN]) ?? false))
            ->action(function (AdmissionWorkflow $record, array $data) use ($run): void {
                try {
                    $run(app(AdmissionWorkflowService::class), $record, $data);
                    Notification::make()->title('تم حفظ الخطوة.')->success()->send();
                } catch (DomainException $e) {
                    Notification::make()->title($e->getMessage())->danger()->send();
                }
            });
    }
}
