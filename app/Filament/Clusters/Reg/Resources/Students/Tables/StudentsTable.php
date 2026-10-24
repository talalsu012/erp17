<?php

namespace App\Filament\Clusters\Reg\Resources\Students\Tables;

use App\Filament\Clusters\Reg\Resources\Students\Tables\Actions\StudentActions;
use App\Filament\Clusters\Reg\Resources\Students\Tables\Columns\StudentColumns;
use App\Filament\Clusters\Reg\Resources\Students\Tables\Filters\StudentFilters;
use App\Filament\Exports\Reg\StudentExporter;
use App\Filament\Imports\Reg\StudentImporter;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentsTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(function (Builder $query) {
                $query->with(['faculty', 'level', 'x_status', 'department', 'stady', 'special', 'academicYear', 'batch']);
                $query->whereHas('faculty', function ($q) {
                    $q->whereHas('users', function ($userQuery) {
                        $userQuery->where('user_id', auth()->id());
                    });
                });
            })
            ->groups([
                Group::make('faculty.name_ar')
                    ->label(__('reg.faculty'))
                    ->collapsible(),
                Group::make('level.name_ar')
                    ->label(__('reg.level'))
                    ->collapsible(),
                Group::make('x_status.name_ar')
                    ->label(__('reg.xstatus'))
                    ->collapsible(),
                Group::make('admission.name_ar')
                    ->label(__('reg.admission'))
                    ->collapsible(),
            ])
            ->columns(StudentColumns::make())
            ->reorderableColumns()
            ->deferColumnManager()
            ->columnManagerColumns(4)
            ->filters(
                [...StudentFilters::make()],
                layout: FiltersLayout::Modal
            )->filtersFormColumns(3)
            ->filtersFormWidth(Width::SixExtraLarge)
            ->deferFilters()
            ->deferLoading()
            // ->filtersFormWidth(MaxWidth::SixExtraLarge)
            // تأكد من أن أفعال السجل تظهر على يمين الجدول
            ->recordActions(
                StudentActions::getRecordActions(),
                position: \Filament\Tables\Enums\RecordActionsPosition::BeforeCells,
            )
            ->headerActions([
                ImportAction::make()
                    ->label(__('reg.import_students'))
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->size('md')
                    ->visible(fn (): bool => auth()->user()?->can('Create:Student') ?? false)
                    ->importer(StudentImporter::class),
                ExportAction::make()
                    ->label(__('reg.export_students'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('secondary')
                    ->size('md')
                    ->visible(fn (): bool => auth()->user()?->can('ViewAny:Student') ?? false)
                    ->exporter(StudentExporter::class),
            ])
            ->toolbarActions(StudentActions::getBulkActions()) // استدعاء العمليات الجماعية
            ->emptyStateHeading(__('students.empty_state'))
            ->defaultSort('created_at', 'desc');
    }
}
