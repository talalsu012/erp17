<?php

namespace App\Filament\Clusters\Res\Resources\SubjectApprovals\Schemas;

use App\Models\Reg\LtColumn;
use App\Models\Res\Subject;
use App\Models\User;
use App\Reg\MarkApprovalStage;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class SubjectApprovalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->gridContainer()
                    ->columns([
                        '@md' => 3,
                        '@xl' => 4,
                    ])
                    ->schema([
                        Select::make('subject_code')
                            ->label(__('res.subject'))
                            ->required()
                            ->options(Subject::pluck('name_ar', 'code'))
                            ->searchable()
                            ->live()
                            ->native()
                            ->columnSpan([
                                '@md' => 2,
                                '@xl' => 3,
                            ])
                            ->columnOrder([
                                'default' => 2,
                                '@xl' => 1,
                            ]),

                        Select::make('batch_code')
                            ->label(__('reg.batch'))
                            ->required()
                            ->options(LtColumn::where('grouped', 2003)->pluck('name_ar', 'code'))
                            ->searchable()
                            ->live()
                            ->native()
                            ->columnSpan([
                                'default' => 1,
                                '@xl' => 1,
                            ])
                            ->columnOrder([
                                'default' => 1,
                                '@xl' => 2,
                            ]),

                    ]),
                Grid::make()
                    ->gridContainer()
                    ->columns([
                        '@md' => 3,
                        '@xl' => 4,
                    ])
                    ->schema([
                        Select::make('current_stage')
                            ->label(__('res.current_stage'))
                            ->required()
                            ->options(MarkApprovalStage::class)
                            ->searchable()
                            ->live()
                            ->native()
                            ->columnSpan([
                                '@md' => 2,
                                '@xl' => 2,
                            ])
                            ->columnOrder([
                                'default' => 2,
                                '@xl' => 1,
                            ]),
                        Select::make('current_user_id')
                            ->label(__('res.current_user'))
                            ->required()
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->native()
                            ->columnSpan([
                                'default' => 1,
                                '@xl' => 2,
                            ])
                            ->columnOrder([
                                'default' => 1,
                                '@xl' => 2,
                            ]),

                    ]),

            ]);
    }
}
