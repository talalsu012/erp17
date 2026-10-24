<?php

namespace App\Filament\Clusters\Reg\Resources\AdmissionWorkflows\Pages;

use App\Filament\Clusters\Reg\Resources\AdmissionWorkflows\AdmissionWorkflowResource;
use App\Models\Reg\AdmissionWorkflow;
use App\Models\Student;
use App\Services\AdmissionWorkflowService;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAdmissionWorkflows extends ListRecords
{
    protected static string $resource = AdmissionWorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('register')
                ->label('إضافة طالب للمعاينة')
                ->icon('heroicon-o-plus')
                ->visible(fn (): bool => auth()->user()?->can('create', AdmissionWorkflow::class) ?? false)
                ->schema([
                    Select::make('university_no')
                        ->label('الطالب')
                        ->required()
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search): array => Student::query()
                            ->where('name_ar', 'like', "{$search}%")
                            ->orWhere('university_no', $search)
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn (Student $student) => [$student->university_no => "{$student->university_no} - {$student->name_ar}"])
                            ->all())
                        ->getOptionLabelUsing(fn ($value): ?string => ($student = Student::find($value))
                            ? "{$student->university_no} - {$student->name_ar}"
                            : null),
                ])
                ->action(function (array $data): void {
                    try {
                        app(AdmissionWorkflowService::class)->register(auth()->user(), Student::findOrFail($data['university_no']));
                        Notification::make()->title('أُضيف الطالب إلى المعاينة وينتظر الطبيب.')->success()->send();
                    } catch (DomainException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}
