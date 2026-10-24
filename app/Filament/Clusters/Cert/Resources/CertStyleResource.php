<?php

namespace App\Filament\Clusters\Cert\Resources;

use App\Filament\Clusters\Cert\CertCluster;
use App\Filament\Clusters\Cert\Resources\CertStyleResource\Pages;
use App\Models\Cert\CertStyle;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CertStyleResource extends Resource
{
    protected static ?string $model = CertStyle::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = CertCluster::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $titles = 'cert.certsyles';

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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // grid 4 columns
                Section::make(__('cert.Style Template Certificate'))
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->label(__('cert.key'))
                            ->required()
                            ->readOnly()

                            ->maxLength(255),
                        Forms\Components\TextInput::make('value')
                            ->label(__('cert.value'))
                            ->required(),
                        Forms\Components\Select::make('group')
                            ->label(__('cert.group'))
                            ->options(CertStyle::pluck('group', 'group'))
                            ->required(),
                        Forms\Components\Select::make('cert')
                            ->label(__('cert.cert_type'))
                            ->options([
                                'graduation' => 'graduation',
                                'transcript' => 'transcript',
                            ])
                            ->required(),

                    ])
                    ->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label(__('cert.key'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('cert.value'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('group')
                    ->label(__('cert.group'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('cert')
                    ->label(__('cert.cert_type'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListCertStyles::route('/'),
            'create' => Pages\CreateCertStyle::route('/create'),
            'edit' => Pages\EditCertStyle::route('/{record}/edit'),
        ];
    }
}
