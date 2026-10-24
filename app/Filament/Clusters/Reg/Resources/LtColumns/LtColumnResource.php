<?php

namespace App\Filament\Clusters\Reg\Resources\LtColumns;

use App\Filament\Clusters\Reg\RegCluster;
use App\Filament\Clusters\Reg\Resources\LtColumns\Pages\CreateLtColumn;
use App\Filament\Clusters\Reg\Resources\LtColumns\Pages\EditLtColumn;
use App\Filament\Clusters\Reg\Resources\LtColumns\Pages\ListLtColumns;
use App\Filament\Clusters\Reg\Resources\LtColumns\Schemas\LtColumnForm;
use App\Filament\Clusters\Reg\Resources\LtColumns\Tables\LtColumnsTable;
use App\Models\Reg\LtColumn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LtColumnResource extends Resource
{
    protected static ?string $model = LtColumn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = RegCluster::class;

    // protected static ?string $recordTitleAttribute = 'name_ar';

    protected static ?int $navigationSort = 6;

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->orderBy('created_at', 'DESC');
    }

    public static function getNavigationGroup(): string
    {
        return __('main.registration');
    }

    public static function getLabel(): ?string
    {
        return __('reg.ltcolumn');
    }

    public static function getPluralLabel(): ?string
    {
        return __('reg.ltcolumn');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('reg.ltcolumn'));
    }

    public static function form(Schema $schema): Schema
    {
        return LtColumnForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LtColumnsTable::configure($table);
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
            'index' => ListLtColumns::route('/'),
            'create' => CreateLtColumn::route('/create'),
            'edit' => EditLtColumn::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
