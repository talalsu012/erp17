<?php

namespace App\Filament\Clusters\Res\Resources\Marks;

use App\Filament\Clusters\Res\ResCluster;
use App\Filament\Clusters\Res\Resources\Marks\Pages\CreateMark;
use App\Filament\Clusters\Res\Resources\Marks\Pages\EditMark;
use App\Filament\Clusters\Res\Resources\Marks\Pages\ListMarks;
use App\Filament\Clusters\Res\Resources\Marks\Schemas\MarkForm;
use App\Filament\Clusters\Res\Resources\Marks\Tables\MarksTable;
use App\Models\Res\Mark;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MarkResource extends Resource
{
    protected static ?string $model = Mark::class;

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = ResCluster::class;

    // protected static ?string $recordTitleAttribute = 'Mark';

    // dispaled permissions for now, will be added later

    protected static ?string $title = 'entries';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->reorder()->count();
    }

    public static function getEloquentQuery(): Builder
    {
        return auth()->user()
            ->constrainToAcademicScope(static::getModel()::query(), studentKey: 'university_no')
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

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::ScreenTwoExtraLarge;
    }

    public static function form(Schema $schema): Schema
    {
        return MarkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarksTable::configure($table);
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
            'index' => ListMarks::route('/'),
            'create' => CreateMark::route('/create'),
            'edit' => EditMark::route('/{record}/edit'),
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
