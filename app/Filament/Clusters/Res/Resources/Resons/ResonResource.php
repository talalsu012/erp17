<?php

namespace App\Filament\Clusters\Res\Resources\Resons;

use App\Filament\Clusters\Res\ResCluster;
use App\Filament\Clusters\Res\Resources\Resons\Pages\CreateReson;
use App\Filament\Clusters\Res\Resources\Resons\Pages\EditReson;
use App\Filament\Clusters\Res\Resources\Resons\Pages\ListResons;
use App\Filament\Clusters\Res\Resources\Resons\Schemas\ResonForm;
use App\Filament\Clusters\Res\Resources\Resons\Tables\ResonsTable;
use App\Models\Res\Reson;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResonResource extends Resource
{
    protected static ?string $model = Reson::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = ResCluster::class;

    protected static ?int $navigationSort = 4;

    // protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $title = 'res.ResResons';

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
        return ResonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResonsTable::configure($table);
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
            'index' => ListResons::route('/'),
            'create' => CreateReson::route('/create'),
            'edit' => EditReson::route('/{record}/edit'),
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
