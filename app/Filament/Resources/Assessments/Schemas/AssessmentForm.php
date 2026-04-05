<?php

namespace App\Filament\Resources\Assessments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AssessmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('General Information')
                    ->description('Basic details of the assessment.')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('type')
                            ->options([
                                'programming' => 'Programming',
                                'marketing' => 'Marketing',
                                'finance' => 'Finance',
                                'hr' => 'Human Resources',
                                'general' => 'General',
                                'personality' => 'Personality',
                                'technical' => 'Technical',
                            ])
                            ->default('general')
                            ->required()
                            ->native(false),
                        TextInput::make('duration_minutes')
                            ->label('Total Duration (Minutes)')
                            ->required()
                            ->numeric()
                            ->default(60)
                            ->minValue(1),
                        TextInput::make('passing_score')
                            ->label('Passing Score (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('max_attempts')
                            ->label('Maximum Attempts')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                    ]),

                \Filament\Schemas\Components\Section::make('Assessment Settings')
                    ->description('Configuration for how the assessment is executed.')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true),
                        Toggle::make('shuffle_questions')
                            ->label('Shuffle Questions'),
                        Toggle::make('show_result_immediately')
                            ->label('Show Results Immediately'),
                    ]),

                \Filament\Schemas\Components\Section::make('Assessment Sections')
                    ->description('Define parts of the exam (e.g. PHP Basics, Laravel, Database).')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('sections')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('duration')
                                    ->label('Section Duration (Minutes)')
                                    ->numeric()
                                    ->required(),
                                Textarea::make('description')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->collapsible()
                            ->cloneable()
                            ->reorderableWithButtons(),
                    ]),
            ]);
    }
}
