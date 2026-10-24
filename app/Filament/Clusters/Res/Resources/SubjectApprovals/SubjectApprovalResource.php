<?php

namespace App\Filament\Clusters\Res\Resources\SubjectApprovals;

use App\Filament\Clusters\Res\ResCluster;
use App\Filament\Clusters\Res\Resources\SubjectApprovals\Pages\CreateSubjectApproval;
use App\Filament\Clusters\Res\Resources\SubjectApprovals\Pages\EditSubjectApproval;
use App\Filament\Clusters\Res\Resources\SubjectApprovals\Pages\ListSubjectApprovals;
use App\Filament\Clusters\Res\Resources\SubjectApprovals\Schemas\SubjectApprovalForm;
use App\Filament\Clusters\Res\Resources\SubjectApprovals\Tables\SubjectApprovalsTable;
use App\Models\Res\SubjectApprovalStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubjectApprovalResource extends Resource
{
    protected static ?string $model = SubjectApprovalStatus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = ResCluster::class;

    // protected static ?string $recordTitleAttribute = 'SubjectApprovalStatus';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string
    {
        return __('main.settings');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->orderBy('created_at', 'DESC');
    }

    public static function getLabel(): ?string
    {
        return __('res.SubjectApprovalStatuses');
    }

    public function getTitle(): string|Htmlable
    {
        return __('res.SubjectApprovalStatuses');
    }

    public static function getPluralLabel(): ?string
    {
        return __('res.SubjectApprovalStatuses');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('res.SubjectApprovalStatuses'));
    }

    public static function form(Schema $schema): Schema
    {
        return SubjectApprovalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubjectApprovalsTable::configure($table);
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
            'index' => ListSubjectApprovals::route('/'),
            'create' => CreateSubjectApproval::route('/create'),
            'edit' => EditSubjectApproval::route('/{record}/edit'),
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
