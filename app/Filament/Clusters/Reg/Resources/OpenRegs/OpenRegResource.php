<?php

namespace App\Filament\Clusters\Reg\Resources\OpenRegs;

use App\Filament\Clusters\Reg\RegCluster;
use App\Filament\Clusters\Reg\Resources\OpenRegs\Pages\CreateOpenReg;
use App\Filament\Clusters\Reg\Resources\OpenRegs\Pages\EditOpenReg;
use App\Filament\Clusters\Reg\Resources\OpenRegs\Pages\ListOpenRegs;
use App\Filament\Clusters\Reg\Resources\OpenRegs\Schemas\OpenRegForm;
use App\Filament\Clusters\Reg\Resources\OpenRegs\Tables\OpenRegsTable;
use App\Models\Reg\OpenReg;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OpenRegResource extends Resource
{
    protected static ?string $model = OpenReg::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = RegCluster::class;

    // protected static ?string $recordTitleAttribute = 'OpenReg';

    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): string
    {
        return __('main.registration');
    }

    public static function getLabel(): ?string
    {
        return __('reg.OpenReg');
    }

    public static function getPluralLabel(): ?string
    {
        return __('reg.OpenReg');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('reg.OpenReg'));
    }

    public static function form(Schema $schema): Schema
    {
        return OpenRegForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OpenRegsTable::configure($table);
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
            'index' => ListOpenRegs::route('/'),
            'create' => CreateOpenReg::route('/create'),
            'edit' => EditOpenReg::route('/{record}/edit'),
        ];
    }
}
