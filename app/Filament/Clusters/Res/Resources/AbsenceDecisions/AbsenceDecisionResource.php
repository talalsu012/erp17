<?php

namespace App\Filament\Clusters\Res\Resources\AbsenceDecisions;

use App\Filament\Clusters\Res\ResCluster;
use App\Filament\Clusters\Res\Resources\AbsenceDecisions\Pages\ListAbsenceDecisions;
use App\Filament\Clusters\Res\Resources\AbsenceDecisions\Tables\AbsenceDecisionsTable;
use App\Models\Res\AbsenceDecision;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AbsenceDecisionResource extends Resource
{
    protected static ?string $model = AbsenceDecision::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserMinus;

    protected static ?string $cluster = ResCluster::class;

    protected static ?int $navigationSort = 6;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['student:university_no,name_ar', 'subject:code,name_ar'])
            ->orderByDesc('absence_pct');
    }

    public static function getNavigationGroup(): string
    {
        return __('main.settings');
    }

    public static function getLabel(): ?string
    {
        return 'حرمان بالغياب';
    }

    public static function getPluralLabel(): ?string
    {
        return 'الحرمان بالغياب';
    }

    public static function getNavigationLabel(): string
    {
        return 'الحرمان بالغياب';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return AbsenceDecisionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAbsenceDecisions::route('/'),
        ];
    }
}
