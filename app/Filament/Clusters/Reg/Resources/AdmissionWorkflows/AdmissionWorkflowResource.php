<?php

namespace App\Filament\Clusters\Reg\Resources\AdmissionWorkflows;

use App\Filament\Clusters\Reg\RegCluster;
use App\Filament\Clusters\Reg\Resources\AdmissionWorkflows\Pages\ListAdmissionWorkflows;
use App\Filament\Clusters\Reg\Resources\AdmissionWorkflows\Tables\AdmissionWorkflowsTable;
use App\Models\Reg\AdmissionWorkflow;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdmissionWorkflowResource extends Resource
{
    protected static ?string $model = AdmissionWorkflow::class;

    protected static ?int $navigationSort = 9;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $cluster = RegCluster::class;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('student:university_no,name_ar');
    }

    public static function getLabel(): ?string
    {
        return 'معاينة القبول';
    }

    public static function getPluralLabel(): ?string
    {
        return 'معاينة القبول';
    }

    public static function getNavigationLabel(): string
    {
        return 'معاينة القبول';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return AdmissionWorkflowsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdmissionWorkflows::route('/'),
        ];
    }
}
