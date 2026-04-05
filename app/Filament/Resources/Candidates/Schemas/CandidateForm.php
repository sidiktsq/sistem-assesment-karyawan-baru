<?php

namespace App\Filament\Resources\Candidates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CandidateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Candidate Information')
                    ->description('Personal and contact details of the candidate.')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('position_applied')
                            ->label('Position Applied For')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('source')
                            ->label('Recruitment Source')
                            ->placeholder('e.g. LinkedIn, JobFair, Referral')
                            ->maxLength(255),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'assessment_scheduled' => 'Assessment Scheduled',
                                'assessment_ongoing' => 'Assessment Ongoing',
                                'assessment_completed' => 'Assessment Completed',
                                'reviewed' => 'Reviewed',
                                'approved' => 'Approved',
                                'probation' => 'Probation',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),
                    ]),

                \Filament\Schemas\Components\Section::make('Internal Notes & Metadata')
                    ->description('Additional internal information about the candidate.')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                        \Filament\Forms\Components\KeyValue::make('metadata')
                            ->label('Additional Data')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
