<?php

namespace App\Filament\Clusters\Res\Resources\LtRecoms;

use App\Filament\Clusters\Res\ResCluster;
use App\Filament\Clusters\Res\Resources\LtRecoms\Pages\CreateLtRecom;
use App\Filament\Clusters\Res\Resources\LtRecoms\Pages\EditLtRecom;
use App\Filament\Clusters\Res\Resources\LtRecoms\Pages\ListLtRecoms;
use App\Filament\Clusters\Res\Resources\LtRecoms\Schemas\LtRecomForm;
use App\Filament\Clusters\Res\Resources\LtRecoms\Tables\LtRecomsTable;
use App\Models\Res\LtRecom;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LtRecomResource extends Resource
{
    protected static ?string $model = LtRecom::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = ResCluster::class;

    protected static ?int $navigationSort = 5;

    // protected static ?string $recordTitleAttribute = 'LtRecom';

    protected static ?string $title = 'res.ResLtRecoms';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->orderBy('created_at', 'DESC');
    }

    public static function getNavigationGroup(): string
    {
        return __('main.settings');
    }

    public static function getLabel(): ?string
    {
        return __(static::$title);
    }

    public static function getPluralLabel(): ?string
    {
        return __(static::$title);
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__(static::$title));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return LtRecomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LtRecomsTable::configure($table);
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
            'index' => ListLtRecoms::route('/'),
            'create' => CreateLtRecom::route('/create'),
            'edit' => EditLtRecom::route('/{record}/edit'),
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
