<?php

namespace App\Filament\Clusters\Invoices\Resources\Invoices;

use App\Filament\Clusters\Invoices\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Clusters\Invoices\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Clusters\Invoices\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Clusters\Invoices\Resources\Invoices\Schemas\InvoiceForm;
use App\Filament\Clusters\Invoices\Resources\Invoices\Tables\InvoicesTable;
use App\Filament\Clusters\Reg\RegCluster;
use App\Models\Invoice\Invoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = RegCluster::class;

    // protected static ?string $recordTitleAttribute = 'name_ar';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationGroup(): string
    {
        return __('main.invoice');
    }

    public static function getLabel(): ?string
    {
        return __('main.invoice');
    }

    public static function getPluralLabel(): ?string
    {
        return __('main.invoices');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('main.invoices'));
    }

    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
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
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'edit' => EditInvoice::route('/{record}/edit'),
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
