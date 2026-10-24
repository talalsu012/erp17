<?php

namespace App\Filament\Clusters\Reg\Resources\Cards\Schemas;

use App\Models\Student;
use App\Reg\CardStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CardsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Fieldset::make('Card Information')
                    ->schema([
                        Select::make('university_no')
                            ->label(__('reg.name_ar'))
                            ->required()
                            ->searchable()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! empty($state)) {

                                    $prefix = 'UNI'; // University prefix
                                    $universityPart = strtoupper(substr($state, 0, 5)); // First 5 chars of university no
                                    $randomPart = Str::upper(Str::random(4)); // 4 random letters
                                    $numericPart = str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT); // 4 random digits

                                    $cardNumber = "{$prefix}-{$universityPart}-{$randomPart}-{$numericPart}";

                                    $set('card_number', $cardNumber);
                                }
                            })
                            ->getSearchResultsUsing(fn (string $search): array => Student::where('name_ar', 'like', "%{$search}%")->limit(50)->pluck('name_ar', 'university_no')->toArray())
                            ->getOptionLabelUsing(fn ($value): ?string => Student::find($value)?->name_ar)
                            ->columnSpan(2),

                        TextInput::make('card_number')
                            ->label('Card Number')
                            ->disabled()
                            ->dehydrated()
                            ->live(onBlur: true)
                            ->required(),

                        Radio::make('status')
                            ->label(__('reg.status_code'))
                            ->options(CardStatus::class)
                            ->inline()
                            ->required()
                            ->default('active')
                            ->columnSpan(2),

                    ])
                    ->columns(2),
                Fieldset::make('Card Details')
                    ->schema([

                        DatePicker::make('issue_date')
                            ->label(__('reg.issue_date'))
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->locale('ar')
                            ->required(),

                        DatePicker::make('expiry_date')
                            ->after('issue_date')
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->locale('ar')
                            ->label(__('reg.expiry_date')),
                        Toggle::make('is_visible')
                            ->label(__('reg.is_visible'))
                            ->inline()
                            ->required(),
                    ])->columns(3),
            ]);
    }
}
