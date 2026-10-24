<?php

namespace App\Filament\Clusters\Res\Resources\Grades\Schemas;

use App\Models\Reg\LtColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GradeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('res.title'))
                    ->required()
                    ->maxLength(10),
                TextInput::make('titlear')
                    ->label(__('res.titlear'))
                    ->required()
                    ->maxLength(10),
                TextInput::make('point')
                    ->label(__('res.point'))
                    ->required()->default(0),
                TextInput::make('upper')
                    ->label(__('res.upper'))
                    ->required()
                    ->numeric(),
                TextInput::make('lower')
                    ->label(__('res.lower'))
                    ->required()
                    ->numeric(),
                Select::make('plan')
                    ->label(__('res.plan'))
                    ->options(LtColumn::all()
                        ->where('grouped', '2006')
                        ->pluck('name_ar', 'code'))
                    ->required(),
            ])->columns(5);
    }
}
