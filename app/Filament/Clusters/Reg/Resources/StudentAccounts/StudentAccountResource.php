<?php

namespace App\Filament\Clusters\Reg\Resources\StudentAccounts;

use App\Filament\Clusters\Reg\RegCluster;
use App\Filament\Clusters\Reg\Resources\StudentAccounts\Pages\EditStudentAccount;
use App\Filament\Clusters\Reg\Resources\StudentAccounts\Pages\ListStudentAccounts;
use App\Filament\Clusters\Reg\Resources\StudentAccounts\Schemas\StudentAccountForm;
use App\Filament\Clusters\Reg\Resources\StudentAccounts\Tables\StudentAccountsTable;
use App\Models\StudentAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StudentAccountResource extends Resource
{
    protected static ?string $model = StudentAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?int $navigationSort = 10;

    protected static ?string $cluster = RegCluster::class;

    public static function getPluralLabel(): ?string
    {
        return 'حسابات بوابة الطلاب';
    }

    public static function getLabel(): ?string
    {
        return 'حساب بوابة طالب';
    }

    public static function getNavigationGroup(): string
    {
        return __('main.registration');
    }

    public static function form(Schema $schema): Schema
    {
        return StudentAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentAccountsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudentAccounts::route('/'),
            'edit' => EditStudentAccount::route('/{record}/edit'),
        ];
    }
}
