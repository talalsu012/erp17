<?php

namespace App\Filament\Clusters\Res\Resources\Recoms;

use App\Filament\Clusters\Res\ResCluster;
use App\Filament\Clusters\Res\Resources\Recoms\Pages\CreateRecom;
use App\Filament\Clusters\Res\Resources\Recoms\Pages\EditRecom;
use App\Filament\Clusters\Res\Resources\Recoms\Pages\ListRecoms;
use App\Filament\Clusters\Res\Resources\Recoms\Schemas\RecomForm;
use App\Filament\Clusters\Res\Resources\Recoms\Tables\RecomsTable;
use App\Models\Res\Recom;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecomResource extends Resource
{
    protected static ?string $model = Recom::class;

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = ResCluster::class;

    // protected static ?string $recordTitleAttribute = 'Recommidation';

    protected static ?string $title = 'ResLtRecoms';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->reorder()->count();
    }

    public static function getEloquentQuery(): Builder
    {
        return auth()->user()
            ->constrainToAcademicScope(static::getModel()::query())
            ->orderBy('created_at', 'DESC');
    }

    public static function canEdit(Model $record): bool
    {
        return ! $record->is_locked || auth()->user()->isSuperAdmin();
    }

    public static function canDelete(Model $record): bool
    {
        return ! $record->is_locked || auth()->user()->isSuperAdmin();
    }

    public static function getNavigationGroup(): string
    {
        return __('res.StudentsMarks');
    }

    public static function getLabel(): ?string
    {
        return __('res.'.static::$title);
    }

    public static function getPluralLabel(): ?string
    {
        return __('res.'.static::$title);
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('res.'.static::$title));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return RecomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecomsTable::configure($table);
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
            'index' => ListRecoms::route('/'),
            'create' => CreateRecom::route('/create'),
            'edit' => EditRecom::route('/{record}/edit'),
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
