<?php

namespace App\Filament\Clusters\Reg\Resources\LtColumns\Schemas;

use App\Models\Reg\LtColumn;
use App\Reg\ltColumnCode;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Stichoza\GoogleTranslate\GoogleTranslate;

class LtColumnForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                    Section::make([

                        TextInput::make('code')->label(__('reg.code'))->numeric()
                            ->unique(ignoreRecord: true)->numeric()
                            ->maxLength(10)->required(),

                        Select::make('grouped')
                            ->label(__('reg.group'))
                            ->required()
                            ->options(ltColumnCode::class),

                        // parent selector allowing hierarchy
                        Select::make('parent_code')
                            ->label(__('reg.parent'))
                            ->nullable()
                            ->relationship('parent', 'name_ar')
                            ->searchable()
                            ->preload(),

                        TextInput::make('name_ar')
                            ->label(__('reg.name'))
                            ->live(onBlur: true)
                            // ->afterStateUpdated(function (Set $set, ?string $state) {
                            //     // Translate Arabic to English
                            //     $translated = (new GoogleTranslate)
                            //         ->setOptions([
                            //             'verify' => false, // هذا يخبر cURL بتجاهل فحص شهادة SSL
                            //         ])
                            //         ->setSource('ar')
                            //         ->setTarget('en')
                            //         ->translate($state);

                            //     $set('name_en', $translated);

                            //     // Generate slug
                            //     //  $set('slug', Str::slug($translated));
                            // })

                            ->maxLength(100)->required(),

                        TextInput::make('name_en')
                            ->label(__('reg.name_en'))
                            ->maxLength(100),

                        TextInput::make('description')
                            ->label(__('reg.description'))
                            ->maxLength(100)
                            ->columnSpan('full'),
                    ])->columns(3),
                ])->columnSpan(['lg' => 2]),
                Group::make()->schema([
                    Section::make([
                        Placeholder::make('created_at')->label(__('reg.created_at'))
                            ->content(fn (LtColumn $record): ?string => $record->created_at?->diffForHumans())
                            ->hidden(fn (?LtColumn $record) => $record === null),
                        Placeholder::make('updated_at')->label(__('reg.updated_at'))
                            ->content(fn (LtColumn $record): ?string => $record->updated_at?->diffForHumans())
                            ->hidden(fn (?LtColumn $record) => $record === null),
                    ]),
                ]),
            ])->columns(3);
    }
}
