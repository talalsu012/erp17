<?php

namespace App\Filament\Clusters\Reg\Resources\Payments\Schemas;

use App\Models\Reg\Level;
use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('university_no')
                    ->label(__('reg.name'))->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Set $set) {
                        if (! empty($state)) {

                            $prefix = 'SABFS'; // University prefix

                            $studentId = $state; // assuming student_id is a field in your form
                            $date = now()->format('Ymd');
                            $prefix = 'VOUCH'; // or make this configurable
                            $serial = str_pad((string) mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

                            $voucher_no = "{$prefix}-{$date}-{$studentId}-{$serial}";
                            $set('voucher_no', $voucher_no);

                            // Get student and set payment amount
                            $student = Student::where('university_no', $state)->first();

                            if ($student) {
                                // Check which payment field has value and set amount
                                if ($student->pay1 > 0) {
                                    $set('amount', $student->pay1);
                                } elseif ($student->pay2 > 0) {
                                    $set('amount', $student->pay2);
                                } elseif ($student->pay3 > 0) {
                                    $set('amount', $student->pay3);
                                } elseif ($student->pay4 > 0) {
                                    $set('amount', $student->pay4);
                                }
                            }
                        }
                    })
                    ->getSearchResultsUsing(fn (string $search): array => Student::query()
                        ->where('name_ar', 'like', "{$search}%")
                        ->orWhere('university_no', '=', "{$search}")
                        ->limit(50)
                        ->get()
                        ->mapWithKeys(fn (Student $student) => [
                            $student->university_no => "{$student->university_no} - {$student->name_ar}",
                        ])
                        ->toArray())
                    ->getOptionLabelUsing(fn ($value): ?string => ($student = Student::find($value))
                        ? "{$student->university_no} - {$student->name_ar}"
                        : null)
                    ->live()
                    ->native()
                    ->searchable()
                    ->columnspan(2),

                TextInput::make('amount')
                    ->label(__('reg.amount'))
                    ->required()
                    ->numeric(),
                ToggleButtons::make('currency')
                    ->label(__('reg.currency'))
                    ->inline()
                    ->options([
                        'SDG' => 'SDG',
                        'USD' => 'USD',
                        'TRN' => 'TRN',
                    ])->default('SDG')
                    ->required(),

                TextInput::make('voucher_no')
                    ->unique(ignoreRecord: true)
                    ->required()->label(__('reg.voucher_no'))
                    // ->disabled()
                    ->dehydrated(),

                ToggleButtons::make('sys_type')
                    ->inline()->label(__('reg.deposit'))
                    ->grouped()
                    ->options([
                        'REG' => 'REG',
                        'CRT' => 'CRT',
                        'OTH' => 'OTH',
                    ])->default('REG')
                    ->required(),

                DatePicker::make('bankdate')
                    ->label(__('reg.bankdate'))
                    ->default(now())
                    ->required(),

                ToggleButtons::make('bank')
                    ->inline()->label(__('reg.deposit'))
                    ->grouped()
                    ->options([
                        'CASH' => 'CASH',
                        'CHECK' => 'CHECK',
                    ])->default('CASH')
                    ->required(),

                Select::make('level_code')
                    ->label(__('reg.level'))
                    ->options(Level::all()->pluck('name_ar', 'code'))
                    ->searchable()
                    ->preload(),

                Select::make('batch_code')
                    ->label(__('reg.batch'))->required()
                    ->relationship('batch', 'name_ar')
                    ->searchable()
                    ->preload(),

                TextInput::make('branch')
                    ->maxLength(255)->label(__('reg.branch'))
                    ->default(null),

            ])->columns(4);
    }
}
