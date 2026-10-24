<?php

namespace App\Filament\Clusters\Reg\Resources\Levels\Schemas;

use App\Models\Reg\Level;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

class LevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                    Section::make([

                        TextInput::make('code')->label(__('reg.code'))->numeric()->maxLength(10)
                            ->required()->unique(ignoreRecord: true),
                        TextInput::make('name_ar')
                            ->label(__('reg.name_ar'))
                            ->maxLength(150)
                            ->required()
                            ->unique(ignoreRecord: true)
                            // ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                try {
                                    // 2. محاولة الترجمة
                                    $translated = (new GoogleTranslate)
                                        ->setOptions([
                                            'verify' => false, // يخبر cURL بتجاهل فحص شهادة SSL
                                        ])
                                        ->setSource('ar')
                                        ->setTarget('en')
                                        ->translate($state);

                                    // 3. تعيين القيم في حال نجاح الترجمة
                                    $set('name_en', $translated);

                                } catch (\Exception $e) {
                                    // 4. في حال حدوث أي خطأ (مثل 429 Too Many Requests)
                                    // سيتجاهل النظام الخطأ وتستمر الصفحة بالعمل، ليقوم المستخدم بالإدخال يدوياً
                                    Log::warning('فشلت الترجمة التلقائية للاسم: '.$e->getMessage());
                                }
                            })

                            ->columnSpan('full'),
                        TextInput::make('name_en')
                            ->label(__('reg.name_en'))
                            ->maxLength(150)->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan('full'),
                        Select::make('parent_code')
                            ->label(__('reg.parent'))
                            ->relationship('parent', 'name_ar')
                            ->columnSpan('full'),

                    ])
                        ->columns(3),
                ])->columnSpan(['lg' => 1]),
                Group::make()->schema([
                    Section::make([

                        Placeholder::make('created_at')->label(__('reg.created_at'))
                            ->content(fn (Level $record): ?string => $record->created_at?->diffForHumans())
                            ->hidden(fn (?Level $record) => $record === null),
                        Placeholder::make('updated_at')->label(__('reg.updated_at'))
                            ->content(fn (Level $record): ?string => $record->updated_at?->diffForHumans())
                            ->hidden(fn (?Level $record) => $record === null),

                        Toggle::make('is_visible')->label(__('reg.is_enabled')),

                    ])->columns(2),

                ]),
            ]);
    }
}
