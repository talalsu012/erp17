<?php

namespace App\Filament\Clusters\Reg\Resources\Departments\Schemas;

use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use Filament\Forms\Components\KeyValue;
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

class DepartmentsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                    Section::make([
                        Select::make('faculty_code')
                            ->options(fn () => Faculty::forAuthUser()->pluck('name_ar', 'code'))
                            ->preload()->required()

                            ->searchable()->label(__('reg.faculty'))->columnSpan(2),
                        TextInput::make('code')->label(__('reg.code'))->numeric()->maxLength(10)
                            ->required()->unique(ignoreRecord: true),
                        TextInput::make('name_ar')
                            ->label(__('reg.name_ar'))
                            ->maxLength(150)
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state) {

                                // 1. التحقق من أن الحقل ليس فارغاً لتوفير الطلبات
                                if (blank($state)) {
                                    return;
                                }

                                try {
                                    // 2. محاولة الترجمة متجاهلين فحص SSL
                                    $translated = (new GoogleTranslate)
                                        ->setOptions([
                                            'verify' => false,
                                        ])
                                        ->setSource('ar')
                                        ->setTarget('en')
                                        ->translate($state);

                                    $set('name_en', $translated);

                                } catch (\Exception $e) {
                                    // 3. حماية النظام من الانهيار إذا تم حظر الـ IP
                                    Log::warning('فشلت الترجمة التلقائية: '.$e->getMessage());
                                }
                            })
                            ->columnSpan('full'),

                        TextInput::make('name_en')
                            ->label(__('reg.name_en'))
                            ->maxLength(150)
                            ->required()
                            // ->unique(ignoreRecord: true)
                            ->columnSpan('full'),

                    ])->columns(3),
                    // قسم بيانات إضافية
                    Section::make(__('reg.additional_data'))
                        ->collapsed()
                        ->schema([
                            KeyValue::make('metadata')
                                ->label(__('reg.metadata'))
                                ->keyLabel(__('reg.meta_key'))
                                ->valueLabel(__('reg.meta_value'))
                                ->reorderable(),
                        ]),
                ])->columnSpan(['lg' => 2]),
                Group::make()->schema([
                    Section::make([
                        Placeholder::make('created_at')->label(__('reg.created_at'))
                            ->content(fn (Department $record): ?string => $record->created_at?->diffForHumans())
                            ->hidden(fn (?Department $record) => $record === null),
                        Placeholder::make('updated_at')->label(__('reg.updated_at'))
                            ->content(fn (Department $record): ?string => $record->updated_at?->diffForHumans())
                            ->hidden(fn (?Department $record) => $record === null),

                        Toggle::make('is_visible')->label(__('reg.is_enabled')),
                        Toggle::make('results_published')->inline()->label(__('reg.results_published')),
                        // TextInput::make('slug')->columnSpan('full'),
                    ])->columns(2),
                ]),
            ])->columns(3);
    }
}
