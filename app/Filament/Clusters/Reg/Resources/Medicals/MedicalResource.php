<?php

namespace App\Filament\Clusters\Reg\Resources\Medicals;

use App\Filament\Clusters\Reg\RegCluster;
use App\Filament\Clusters\Reg\Resources\Medicals\Pages\CreateMedical;
use App\Filament\Clusters\Reg\Resources\Medicals\Pages\EditMedical;
use App\Filament\Clusters\Reg\Resources\Medicals\Pages\ListMedicals;
use App\Filament\Clusters\Reg\Resources\Medicals\Schemas\MedicalForm;
use App\Filament\Clusters\Reg\Resources\Medicals\Tables\MedicalsTable;
use App\Models\Reg\MedicalFitnessDecisions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class MedicalResource extends Resource
{
    protected static ?string $model = MedicalFitnessDecisions::class;

    protected static ?int $navigationSort = 8;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = RegCluster::class;

    // protected static ?string $recordTitleAttribute = 'MedicalFitnessDecisions';
    public static function getLabel(): ?string
    {
        return __('reg.medical_fitness_decision_title');
    }

    public function getTitle(): string|Htmlable
    {
        return __('');
    }

    public static function getPluralLabel(): ?string
    {
        return __('reg.medical_fitness_decision_title');
    }

    public static function form(Schema $schema): Schema
    {
        return MedicalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicals::route('/'),
            'create' => CreateMedical::route('/create'),
            'edit' => EditMedical::route('/{record}/edit'),
        ];
    }
}
