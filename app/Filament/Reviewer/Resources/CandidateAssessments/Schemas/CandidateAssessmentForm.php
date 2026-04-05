<?php

namespace App\Filament\Reviewer\Resources\CandidateAssessments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CandidateAssessmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('candidate_id')
                    ->required()
                    ->numeric(),
                TextInput::make('assessment_id')
                    ->required()
                    ->numeric(),
                TextInput::make('assigned_by')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('scheduled_at')
                    ->required(),
                DateTimePicker::make('deadline')
                    ->required(),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('completed_at'),
                Select::make('status')
                    ->options([
            'scheduled' => 'Scheduled',
            'ongoing' => 'Ongoing',
            'completed' => 'Completed',
            'expired' => 'Expired',
            'reviewed' => 'Reviewed',
        ])
                    ->default('scheduled')
                    ->required(),
                TextInput::make('total_score')
                    ->numeric(),
                TextInput::make('percentage')
                    ->numeric(),
                Select::make('result')
                    ->options(['pass' => 'Pass', 'fail' => 'Fail', 'pending' => 'Pending'])
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('token_expires_at'),
                TextInput::make('metadata'),
            ]);
    }
}
