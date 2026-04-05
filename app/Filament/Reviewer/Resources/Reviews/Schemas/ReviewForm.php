<?php

namespace App\Filament\Reviewer\Resources\Reviews\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Final Decision')
                    ->description('Provide your final recommendation for this candidate.')
                    ->icon('heroicon-o-check-badge')
                    ->columns(2)
                    ->schema([
                        Select::make('candidate_assessment_id')
                            ->label('Assessment Session')
                            ->relationship('candidateAssessment', 'id') // Simplified for now
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('recommendation')
                            ->label('Final Recommendation')
                            ->options([
                                'approved' => 'Approved',
                                'probation' => 'Probation',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->native(false),
                    ]),

                \Filament\Schemas\Components\Section::make('Aspect Scoring & Feedback')
                    ->icon('heroicon-o-presentation-chart-bar')
                    ->schema([
                        \Filament\Forms\Components\KeyValue::make('aspect_scores')
                            ->label('Scores by Aspect')
                            ->helperText('e.g. Technical Skills, Communication, Problem Solving')
                            ->keyLabel('Aspect')
                            ->valueLabel('Score (1-10)'),
                        Textarea::make('notes')
                            ->label('Detailed Evaluation Notes')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
