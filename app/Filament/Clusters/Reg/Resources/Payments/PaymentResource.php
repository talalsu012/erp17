<?php

namespace App\Filament\Clusters\Reg\Resources\Payments;

use App\Filament\Clusters\Reg\RegCluster;
use App\Filament\Clusters\Reg\Resources\Payments\Pages\CreatePayment;
use App\Filament\Clusters\Reg\Resources\Payments\Pages\EditPayment;
use App\Filament\Clusters\Reg\Resources\Payments\Pages\ListPayments;
use App\Filament\Clusters\Reg\Resources\Payments\RelationManagers\PaymentRelationManager;
use App\Filament\Clusters\Reg\Resources\Payments\Schemas\PaymentForm;
use App\Filament\Clusters\Reg\Resources\Payments\Tables\PaymentsTable;
use App\Models\Reg\Payment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = RegCluster::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'voucher_no';

    public static function getNavigationGroup(): string
    {
        return __('main.invoice');
    }

    public static function getNavigationBadge(): ?string
    {
        $sum = static::getModel()::sum('amount');

        return number_format($sum, 2).__('reg.currency_sdg'); // Format to 2 decimal places with currency symbol
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->orderBy('created_at', 'DESC');
    }

    public static function getLabel(): ?string
    {
        return __('reg.payments');
    }

    public function getTitle(): string|Htmlable
    {
        return __('');
    }

    public static function getPluralLabel(): ?string
    {
        return __('reg.payments');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('reg.payments'));
    }

    public static function form(Schema $schema): Schema
    {
        return PaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // PaymentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'edit' => EditPayment::route('/{record}/edit'),
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
