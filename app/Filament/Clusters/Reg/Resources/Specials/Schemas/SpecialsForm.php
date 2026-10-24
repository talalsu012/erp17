<?php

namespace App\Filament\Clusters\Reg\Resources\Specials\Schemas;

use App\Models\Reg\Special;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SpecialsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                    Section::make([
                        Select::make('department_code')
                            ->relationship(
                                name: 'department',
                                titleAttribute: 'name_ar',
                                modifyQueryUsing: fn (Builder $query) => $query->with('faculty') // تحميل علاقة الكلية مسبقاً لتحسين الأداء
                            )
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => $record->faculty
                                ? "{$record->faculty->name_ar} - {$record->name_ar}"
                                : $record->name_ar
                            )
                            ->preload()
                            ->searchable(['name_ar']) // يمكنك إضافة 'faculty.name_ar' إذا أردت البحث باسم الكلية أيضاً
                            ->label(__('reg.department')),

                        TextInput::make('code')->label(__('reg.code'))->numeric()->maxLength(10)
                            ->required()->unique(ignoreRecord: true),
                        TextInput::make('name_ar')->label(__('reg.name'))->maxLength(150)->required()
                            ->unique(ignoreRecord: true) // Filament built-in method
                        // ->rules([
                        //     Rule::unique('special', 'name'),
                        // ])
                        // ->live(onBlur: true)
                            ->columnSpan('full'),

                        TextInput::make('name_en')->label(__('reg.name_en'))->maxLength(150)->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan('full'),
                    ])->columns(2),
                ])->columnSpan(['lg' => 2]),
                Group::make()->schema([
                    Section::make([
                        Placeholder::make('created_at')->label(__('reg.created_at'))
                            ->content(fn (Special $record): ?string => $record->created_at?->diffForHumans())
                            ->hidden(fn (?Special $record) => $record === null),
                        Placeholder::make('updated_at')->label(__('reg.updated_at'))
                            ->content(fn (Special $record): ?string => $record->updated_at?->diffForHumans())
                            ->hidden(fn (?Special $record) => $record === null),
                    ]),
                ]),
            ])->columns(3);
    }
}
