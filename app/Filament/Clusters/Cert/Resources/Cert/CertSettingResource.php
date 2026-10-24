<?php

namespace App\Filament\Clusters\Cert\Resources\Cert;

use App\Filament\Clusters\Cert\CertCluster;
use App\Filament\Clusters\Cert\Resources\Cert\CertSettingResource\Pages;
use App\Models\Cert\CertSetting;
use App\Models\Reg\Faculty;
use App\Reg\CertSettingGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Stichoza\GoogleTranslate\GoogleTranslate;

class CertSettingResource extends Resource
{
    protected static ?string $model = CertSetting::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = CertCluster::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $titles = 'cert.settings';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->orderBy('created_at', 'DESC');
    }

    public static function getNavigationGroup(): string
    {
        return __('main.certificate');
    }

    public static function getLabel(): ?string
    {
        return __(static::$titles);
    }

    public static function getPluralLabel(): ?string
    {
        return __(static::$titles);
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__(static::$titles));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    // public static function canEdit(Model $record): bool
    // {
    //     return true;
    // }

    // public static function canDelete(Model $record): bool
    // {
    //     return true;
    // }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Main Information Section
                Section::make('')
                    ->columnSpanfull()
                    ->schema([
                        Group::make([
                            TextInput::make('name_ar')
                                ->label(__('cert.name_ar'))
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                    // Translate Arabic to English
                                    $translated = (new GoogleTranslate)->setSource('ar')->setTarget('en')->translate($state);
                                    $set('name_en', $translated);

                                    // Generate slug
                                    $set('slug', Str::slug($translated));
                                })
                            // ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ,
                            TextInput::make('name_en')
                                ->label(__('cert.name_en'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('slug')
                                ->label(__('cert.slug'))
                                ->required(),
                        ])->columns(3),
                    ]),

                // Relationships Section
                Section::make('')
                    ->columnSpanfull()
                    ->schema([
                        Group::make([

                            Select::make('groups')
                                ->label(__('cert.groups'))
                                ->options(
                                    fn () => collect(CertSettingGroup::cases())
                                        ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                                )
                                ->required()
                                ->live(),

                            Select::make('faculty_code')
                                ->label(__('cert.faculty'))
                                ->options(Faculty::pluck('name_ar', 'code'))
                                ->required(fn (Get $get): bool => in_array(
                                    $get('groups'),
                                    ['degree', 'faculty_admin', 'special']
                                ))
                                ->hidden(fn (Get $get): bool => in_array(
                                    $get('groups'),
                                    ['gender', 'academic_year', 'admission_type', 'academic_affairs', 'stady']
                                )),
                            Select::make('parent_id')
                                ->label(__('cert.parent_id'))
                                ->options(CertSetting::pluck('name_ar', 'id'))
                                ->searchable(),
                            Toggle::make('is_visible')
                                ->label(__('cert.is_visible'))
                                ->default(true)
                                ->required(),

                        ])->columns(4),

                    ]),
            ]);
    }

    // Replace with your actual API logic
    public function translateArabicToEnglish(string $text): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.deepl.key'),
        ])->post('https://api.deepl.com/v2/translate', [
            'text' => $text,
            'source_lang' => 'AR',
            'target_lang' => 'EN',
        ]);

        return $response->json('translations.0.text') ?? Str::slug($text);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label(__('cert.name_ar'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->label(__('cert.name_en'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('groups')
                    ->label(__('cert.groups'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('faculty.name_ar')
                    ->label(__('cert.faculty'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.name_ar')
                    ->label(__('cert.parent_id'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('cert.is_visible'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCertSettings::route('/'),
            'create' => Pages\CreateCertSetting::route('/create'),
            'edit' => Pages\EditCertSetting::route('/{record}/edit'),
        ];
    }
}
