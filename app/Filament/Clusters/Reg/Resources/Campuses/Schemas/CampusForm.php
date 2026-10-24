<?php

namespace App\Filament\Clusters\Reg\Resources\Campuses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CampusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label(__('reg.code'))
                    ->required()
                    ->maxLength(255),

                TextInput::make('name_ar')
                    ->label(__('reg.name_ar'))
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn (): bool => ! auth()->user()?->hasFieldPermission('reg_campuses.name_ar'))
                    ->dehydrated(),
                TextInput::make('name_en')
                    ->label(__('reg.name_en'))
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
