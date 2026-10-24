<?php

namespace App\Filament\Clusters\Res\Resources\Subjects;

use App\Filament\Clusters\Res\ResCluster;
use App\Filament\Clusters\Res\Resources\Subjects\Pages\CreateSubjects;
use App\Filament\Clusters\Res\Resources\Subjects\Pages\EditSubjects;
use App\Filament\Clusters\Res\Resources\Subjects\Pages\ListSubjects;
use App\Filament\Clusters\Res\Resources\Subjects\Schemas\SubjectsForm;
use App\Filament\Clusters\Res\Resources\Subjects\Tables\SubjectsTable;
use App\Models\Res\Subject;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubjectsResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = ResCluster::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name_ar';

    protected static ?string $title = 'subjects';

    public static function getNavigationBadge(): ?string
    {
        return (string) auth()->user()
            ->constrainToAcademicScope(static::getModel()::query(), includeUnassigned: ['department', 'level'])
            ->count();
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
        return SubjectsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubjectsTable::configure($table);
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
            'index' => ListSubjects::route('/'),
            'create' => CreateSubjects::route('/create'),
            'edit' => EditSubjects::route('/{record}/edit'),
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
