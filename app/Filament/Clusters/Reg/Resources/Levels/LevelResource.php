<?php

namespace App\Filament\Clusters\Reg\Resources\Levels;

use App\Filament\Clusters\Reg\RegCluster;
use App\Filament\Clusters\Reg\Resources\Levels\Pages\CreateLevel;
use App\Filament\Clusters\Reg\Resources\Levels\Pages\EditLevel;
use App\Filament\Clusters\Reg\Resources\Levels\Pages\ListLevels;
use App\Filament\Clusters\Reg\Resources\Levels\Schemas\LevelForm;
use App\Filament\Clusters\Reg\Resources\Levels\Tables\LevelsTable;
use App\Models\Reg\Level;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LevelResource extends Resource
{
    protected static ?string $model = Level::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = RegCluster::class;

    // protected static ?string $recordTitleAttribute = 'name_ar';

    protected static ?int $navigationSort = 5;

    public static function getPluralLabel(): ?string
    {
        return __('reg.levels');
    }

    public static function getLabel(): ?string
    {
        return __('reg.level');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('reg.levels'));
    }

    public static function getNavigationGroup(): string
    {
        return __('main.registration');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return LevelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LevelsTable::configure($table);
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
            'index' => ListLevels::route('/'),
            'create' => CreateLevel::route('/create'),
            'edit' => EditLevel::route('/{record}/edit'),
        ];
    }
}
