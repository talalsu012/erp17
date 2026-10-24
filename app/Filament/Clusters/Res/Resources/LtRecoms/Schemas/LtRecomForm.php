<?php

namespace App\Filament\Clusters\Res\Resources\LtRecoms\Schemas;

use App\Models\Res\LtRecom;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LtRecomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label(__('reg.code'))
                    ->required()
                    ->numeric() // Ensure it's treated as a number
                    ->unique(ignoreRecord: true) // Ensure uniqueness, ignore current record on edit
                    ->placeholder('e.g., 1, 20, 101')
                    ->columnSpan(1), // Take half width

                TextInput::make('name_ar')
                    ->label(__('reg.name').' (Arabic)')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true, table: LtRecom::class, column: 'name_ar') // Unique Arabic name
                    ->live(onBlur: true)

                    ->columnSpan(1),

                TextInput::make('name_en')
                    ->label(__('reg.name').' (English)')
                    ->maxLength(100)
                    ->nullable()
                    ->unique(ignoreRecord: true, table: LtRecom::class, column: 'name_en') // Unique English name
                    ->columnSpan(1),

                Textarea::make('description')
                    ->label(__('reg.description').' (Optional)')
                    ->maxLength(500)
                    ->nullable(), // Make it span full width

                Toggle::make('is_visible')
                    ->label(__('reg.is_visible'))
                    ->default(true),
            ]);
    }
}
