<?php

namespace App\Filament\Resources\Assessments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use App\Filament\Resources\Assessments\Actions\SmartImportAction;

class AssessmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Assessment Editor')
                    ->tabs([
                        Tab::make('General Information')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make('Basics')
                                            ->description('Core identification for this assessment.')
                                            ->icon('heroicon-o-document-text')
                                            ->schema([
                                                TextInput::make('title')
                                                    ->required()
                                                    ->placeholder('e.g. Senior PHP Developer Assessment')
                                                    ->maxLength(255)
                                                    ->columnSpanFull(),
                                                Textarea::make('description')
                                                    ->rows(3)
                                                    ->placeholder('Brief overview of the assessment goals...')
                                                    ->columnSpanFull(),
                                                Grid::make(2)
                                                    ->schema([
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
                                                            ->label('Total Duration (Mins)')
                                                            ->placeholder('60')
                                                            ->required()
                                                            ->numeric()
                                                            ->default(60)
                                                            ->minValue(1),
                                                        TextInput::make('passing_score')
                                                            ->label('Passing Score (%)')
                                                            ->placeholder('70')
                                                            ->numeric()
                                                            ->minValue(0)
                                                            ->maxValue(100),
                                                        TextInput::make('max_attempts')
                                                            ->label('Max Attempts')
                                                            ->placeholder('1')
                                                            ->required()
                                                            ->numeric()
                                                            ->default(1)
                                                            ->minValue(1),
                                                    ]),
                                            ]),

                                        Section::make('Configuration')
                                            ->description('Behavioral settings for execution.')
                                            ->icon('heroicon-o-cog-6-tooth')
                                            ->schema([
                                                Toggle::make('is_active')
                                                    ->label('Active Status')
                                                    ->helperText('Enable or disable this assessment for candidates.')
                                                    ->default(true),
                                                Toggle::make('shuffle_questions')
                                                    ->label('Randomize Questions')
                                                    ->helperText('Present questions in a different order for each attempt.'),
                                                Toggle::make('show_result_immediately')
                                                    ->label('Instant Feedback')
                                                    ->helperText('Show final score immediately after submission.'),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Structure')
                            ->icon('heroicon-o-rectangle-stack')
                            ->schema([
                                Section::make('Assessment Sections')
                                    ->description('Break down the exam into logical parts (e.g. Logic, Coding, SQL).')
                                    ->icon('heroicon-o-list-bullet')
                                    ->schema([
                                        Repeater::make('sections')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->required()
                                                    ->placeholder('Section Name')
                                                    ->maxLength(255),
                                                TextInput::make('duration')
                                                    ->label('Duration (Mins)')
                                                    ->placeholder('20')
                                                    ->numeric()
                                                    ->required(),
                                                Textarea::make('description')
                                                    ->rows(2)
                                                    ->placeholder('Helpful metadata for this section...')
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                            ->collapsible()
                                            ->cloneable()
                                            ->reorderableWithButtons(),
                                    ]),
                            ]),

                        Tab::make('Questions')
                            ->icon('heroicon-o-pencil-square')
                            ->badge(fn ($get) => count($get('questions') ?? []))
                            ->schema([
                                Section::make('Question Management')
                                    ->description('Compose the content of this assessment.')
                                    ->headerActions([
                                        SmartImportAction::make(),
                                    ])
                                    ->schema([
                                        Repeater::make('questions')
                                            ->relationship('questions')
                                            ->live()
                                            ->partiallyRenderAfterActionsCalled(false)
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        Select::make('type')
                                                            ->options([
                                                                'multiple_choice' => '📝 Multiple Choice',
                                                                'essay' => '✍️ Essay',
                                                                'personality' => '👤 Personality',
                                                            ])
                                                            ->required()
                                                            ->live()
                                                            ->native(false),
                                                        TextInput::make('section')
                                                            ->label('Section Name')
                                                            ->placeholder('e.g. Basics')
                                                            ->default('general'),
                                                        TextInput::make('score')
                                                            ->numeric()
                                                            ->default(1)
                                                            ->required(),
                                                    ]),

                                                Textarea::make('question_text')
                                                    ->label('Question Text')
                                                    ->required()
                                                    ->rows(3)
                                                    ->columnSpanFull(),

                                                // Options for MC/Personality
                                                Repeater::make('options')
                                                    ->schema([
                                                        TextInput::make('option')
                                                            ->placeholder('A')
                                                            ->required()
                                                            ->maxLength(5),
                                                        TextInput::make('text')
                                                            ->placeholder('Option description...')
                                                            ->required()
                                                            ->maxLength(255),
                                                        TextInput::make('value')
                                                            ->label('Point Value')
                                                            ->numeric()
                                                            ->helperText('Scoring weight.'),
                                                    ])
                                                    ->columns(3)
                                                    ->visible(fn ($get) => in_array($get('type'), ['multiple_choice', 'personality']))
                                                    ->itemLabel(fn (array $state): ?string => $state['option'] ?? null)
                                                    ->collapsible()
                                                    ->grid(2),

                                                TextInput::make('correct_answer')
                                                    ->label('Correct Option Label')
                                                    ->placeholder('e.g. A')
                                                    ->visible(fn ($get) => $get('type') === 'multiple_choice')
                                                    ->required(fn ($get) => $get('type') === 'multiple_choice'),

                                                // Essay specific
                                                Grid::make(2)
                                                    ->visible(fn ($get) => $get('type') === 'essay')
                                                    ->schema([
                                                        TextInput::make('min_words')->numeric()->default(0),
                                                        TextInput::make('max_words')->numeric()->default(1000),
                                                        Textarea::make('essay_guidelines')
                                                            ->placeholder('Instructions for the candidate...')
                                                            ->columnSpanFull()
                                                            ->rows(2),
                                                    ]),

                                                // Meta & Controls
                                                Grid::make(3)
                                                    ->schema([
                                                        Select::make('difficulty')
                                                            ->options(['easy' => '🟢 Easy', 'medium' => '🟡 Medium', 'hard' => '🔴 Hard'])
                                                            ->default('medium')
                                                            ->required()
                                                            ->native(false),
                                                        TextInput::make('order')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->minValue(0)
                                                            ->required(),
                                                        Toggle::make('is_active')->label('Published')->default(true),
                                                    ]),
                                            ])
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['question_text'] ?? 'New Question')
                                            ->reorderableWithButtons()
                                            ->cloneable()
                                            ->addActionLabel('Add New Question'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }
}
