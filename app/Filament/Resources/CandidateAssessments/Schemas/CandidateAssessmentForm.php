<?php

namespace App\Filament\Resources\CandidateAssessments\Schemas;

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
                \Filament\Schemas\Components\Section::make('Assignment Details')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->columns(2)
                    ->schema([
                        Select::make('candidate_id')
                            ->relationship('candidate', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('assessment_id')
                            ->relationship('assessment', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        \Filament\Forms\Components\DateTimePicker::make('scheduled_at')
                            ->required()
                            ->default(now()),
                        \Filament\Forms\Components\DateTimePicker::make('deadline')
                            ->required()
                            ->default(now()->addDays(7)),
                    ]),

                \Filament\Schemas\Components\Section::make('Execution Status')
                    ->icon('heroicon-o-play-circle')
                    ->columns(2)
                    ->collapsed(fn ($record) => $record === null)
                    ->schema([
                        Select::make('status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'ongoing' => 'Ongoing',
                                'completed' => 'Completed',
                                'expired' => 'Expired',
                                'reviewed' => 'Reviewed',
                            ])
                            ->default('scheduled')
                            ->required()
                            ->native(false),
                        Select::make('result')
                            ->options([
                                'pending' => 'Pending',
                                'pass' => 'Pass',
                                'fail' => 'Fail',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),
                        TextInput::make('total_score')
                            ->numeric()
                            ->readOnly(),
                        TextInput::make('percentage')
                            ->numeric()
                            ->suffix('%')
                            ->readOnly(),
                    ]),

                \Filament\Schemas\Components\Section::make('Access & Security')
                    ->icon('heroicon-o-key')
                    ->columns(2)
                    ->schema([
                        TextInput::make('access_token')
                            ->label('Access Token')
                            ->helperText('This token is sent to the candidate for exam access.')
                            ->maxLength(64)
                            ->suffixAction(
                                \Filament\Actions\Action::make('generateToken')
                                    ->icon('heroicon-m-arrow-path')
                                    ->action(function (TextInput $component) {
                                        $component->state(bin2hex(random_bytes(16)));
                                    })
                            ),
                        \Filament\Forms\Components\DateTimePicker::make('token_expires_at')
                            ->label('Token Expiration'),
                    ]),
            ]);
    }
}
