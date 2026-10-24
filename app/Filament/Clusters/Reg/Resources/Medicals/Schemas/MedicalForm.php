<?php

namespace App\Filament\Clusters\Reg\Resources\Medicals\Schemas;

use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class MedicalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // Top student info
                Grid::make(2)->schema([
                    Select::make('university_no')
                        ->label(__('reg.student'))
                        ->required()
                        ->live(onBlur: true)
                        ->getSearchResultsUsing(fn (string $search): array => Student::query()
                            ->where('name_ar', 'like', "{$search}%")
                            ->orWhere('university_no', '=', "{$search}")
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn (Student $student) => [
                                $student->university_no => "{$student->university_no} - {$student->name_ar}",
                            ])
                            ->toArray())
                        ->getOptionLabelUsing(fn ($value): ?string => ($student = Student::find($value))
                            ? "{$student->university_no} - {$student->name_ar}"
                            : null)
                        ->native()
                        ->searchable()
                        ->columnSpan(2),

                    // Final decision section at bottom

                    Select::make('final_decision')
                        ->label(__('reg.final_decision'))
                        ->options([
                            'accepted' => __('reg.accepted'),
                            'rejected' => __('reg.rejected'),
                            'pending' => __('reg.pending'),
                        ])
                        ->required()
                        ->default('pending')
                        ->disabled(fn ($get) => ! $get('medical_fitness') || ! $get('college_interview'))
                        ->dehydrated()
                        ->reactive(),

                    DatePicker::make('decision_date')
                        ->label(__('reg.decision_date'))
                        ->required()
                        ->default(now()),

                    Textarea::make('rejection_reason')
                        ->label(__('reg.rejection_reason'))
                        ->rows(3)
                        ->placeholder(__('reg.rejection_reason_placeholder'))
                        ->visible(fn ($get) => $get('final_decision') === 'rejected'),

                ]),

                Tabs::make('Medical & College Decisions')->schema([
                    Tab::make(__('reg.medical_tab'))
                        ->schema([
                            Select::make('medical_fitness')
                                ->label(__('reg.medical_status'))
                                ->options([
                                    'fit' => __('reg.fit'),
                                    'unfit' => __('reg.unfit'),
                                ])
                                ->default('fit')
                                ->required()
                                ->live(),

                            Textarea::make('medical_notes')
                                ->label(__('reg.medical_notes'))
                                ->rows(3)
                                ->placeholder(__('reg.medical_notes_placeholder')),
                        ]),

                    Tab::make(__('reg.college_tab'))
                        ->schema([
                            Select::make('college_interview')
                                ->label(__('reg.college_interview'))
                                ->options([
                                    'passed' => __('reg.passed'),
                                    'failed' => __('reg.failed'),
                                ])
                                ->default('passed')
                                ->required()
                                ->live(),

                            Textarea::make('interview_notes')
                                ->label(__('reg.interview_notes'))
                                ->rows(3)
                                ->placeholder(__('reg.interview_notes_placeholder')),
                        ]),
                ]),

            ]);
    }
}
