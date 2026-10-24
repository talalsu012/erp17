<?php

namespace App\Filament\Clusters\Invoices\Resources\Invoices\Schemas;

use App\Models\Invoice\Invoice;
use App\Models\Reg\LtColumn;
use App\Reg\InvoiceStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('بيانات الفاتورة')
                    ->schema([
                        Grid::make(3)->schema([

                            Select::make('sys_type')
                                ->label(__('invoice.sys_type'))
                                ->options(
                                    fn () => LtColumn::query()
                                        ->where('grouped', '4001')
                                        ->pluck('name_ar', 'code')
                                )
                                ->required()
                                // ->disabled(fn (?Invoice $record) => $record?->bank_pay == 1) // تعطيل إذا تم الدفع
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {

                                    // السنة رقمين + الشهر رقمين
                                    $datePart = now()->format('ym'); // مثال: 2602

                                    // آخر رقمين من sys_type
                                    $sysTypePart = substr($state, -2);

                                    // آخر id + 1
                                    $lastId = Invoice::max('id') ?? 0;
                                    $nextId = $lastId + 1;

                                    // تركيب الرقم النهائي
                                    $invoiceNumber = '100000'.$datePart.$sysTypePart.$nextId;

                                    $set('invoice_no', $invoiceNumber);
                                }),

                            TextInput::make('invoice_no')
                                ->label(__('invoice.invoice_no'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                // ->disabled()
                                ->columnSpan(2)
                                ->hint('السنة والشهر ونوع النظام')
                                ->maxLength(50),

                            TextInput::make('name_ar')
                                ->label(__('invoice.name_ar'))
                                ->required()
                                ->columnSpan(2)
                                ->maxLength(255),

                            TextInput::make('amount')
                                ->label(__('invoice.amount'))
                                ->numeric()
                                ->required()
                                ->prefix('SDG')
                                ->minValue(0),

                            Select::make('faculty_code')
                                ->label(__('invoice.faculty_code'))
                                ->relationship(
                                    name: 'faculty',
                                    titleAttribute: 'name_ar'
                                )
                                ->searchable()
                                ->preload(),

                            Select::make('status')
                                ->label(__('invoice.status'))
                                ->options(InvoiceStatus::class)
                                ->required(),

                        ]),
                    ]),

                Section::make('معلومات الدفع البنكي')
                    ->schema([
                        Grid::make(3)->schema([

                            Toggle::make('bank_pay')
                                ->label(__('invoice.bank_pay'))
                                ->inline(false),

                            TextInput::make('bank')
                                ->label(__('invoice.bank'))
                                ->maxLength(150)
                                ->visible(fn ($get) => $get('bank_pay')),

                            TextInput::make('voucher_no')
                                ->label(__('invoice.voucher_no'))
                                ->maxLength(100)
                                ->visible(fn ($get) => $get('bank_pay')),

                            DateTimePicker::make('bankdate')
                                ->label(__('invoice.bankdate'))
                                ->seconds(false)
                                ->visible(fn ($get) => $get('bank_pay')),

                        ]),
                    ])
                    ->collapsible(),

                Section::make('تواريخ الفاتورة')
                    ->schema([
                        Grid::make(2)->schema([

                            DateTimePicker::make('due_date')
                                ->label(__('invoice.due_date'))
                                ->seconds(false),

                            DateTimePicker::make('expire_at')
                                ->label(__('invoice.expire_at'))
                                ->seconds(false)
                                ->after('due_date'),

                        ]),
                    ])
                    ->collapsible(),

                Section::make('ملاحظات')
                    ->schema([
                        Grid::make(4)->schema([
                            // email,phone,description
                            TextInput::make('email')
                                ->label(__('invoice.email'))
                                ->email()
                                ->columnSpan(2)
                                ->maxLength(255),

                            TextInput::make('phone')
                                ->label(__('invoice.phone'))
                                ->tel()
                                ->required()
                                ->columnSpan(2)
                                ->maxLength(20),
                        ]),
                        Textarea::make('description')
                            ->label(__('invoice.description'))
                            ->rows(4)
                            ->columnSpan(4),
                    ])
                    ->collapsible(),

            ]);
    }
}
