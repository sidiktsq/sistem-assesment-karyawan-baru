<?php

namespace App\Filament\Resources\Questions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuestionForm
{
    public static function configure(Schema $schema, $livewire = null): Schema
    {
        $isCreate = $livewire instanceof \App\Filament\Resources\Questions\Pages\CreateQuestion;

        $typeField = Select::make('type')
            ->label('Question Type')
            ->options([
                'multiple_choice' => '📝 Multiple Choice - Pilihan Ganda',
                'essay' => '✍️ Essay - Esai/Jawaban Panjang',
                'personality' => '👤 Personality - Skala Likert',
            ])
            ->required()
            ->native(false)
            ->live()
            ->helperText('Pilih jenis soal yang ingin dibuat');

        $setupFields = [
            Select::make('assessment_id')
                ->relationship('assessment', 'title')
                ->searchable()
                ->preload()
                ->required(),
            // Type field will be added here conditionally
            TextInput::make('section')
                ->label('Section Name')
                ->default('general')
                ->required()
                ->placeholder('e.g. PHP, Laravel'),
            TextInput::make('order')
                ->numeric()
                ->default(0),
        ];

        if (!$isCreate) {
            array_splice($setupFields, 1, 0, [$typeField]);
        }

        $contentFields = [
            Textarea::make('question_text')
                ->label('Question Text')
                ->required()
                ->rows(4)
                ->columnSpanFull()
                ->helperText('Masukkan pertanyaan essay yang akan dijawab oleh kandidat'),
            
            // Essay specific fields
            Textarea::make('essay_guidelines')
                ->label('Essay Guidelines (Optional)')
                ->rows(3)
                ->columnSpanFull()
                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('type') === 'essay')
                ->helperText('Berikan panduan atau petunjuk untuk menjawab essay ini'),
            
            TextInput::make('min_words')
                ->label('Minimum Words (Optional)')
                ->numeric()
                ->default(0)
                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('type') === 'essay')
                ->helperText('Jumlah kata minimum yang diharapkan'),
            
            TextInput::make('max_words')
                ->label('Maximum Words (Optional)')
                ->numeric()
                ->default(1000)
                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('type') === 'essay')
                ->helperText('Jumlah kata maksimum yang diizinkan'),
            
            \Filament\Forms\Components\Repeater::make('options')
                ->label('Answer Options')
                ->default([])
                ->schema([
                    TextInput::make('option')
                        ->label('Label')
                        ->placeholder('e.g. A, B, 1, 2')
                        ->required()
                        ->maxLength(5),
                    TextInput::make('text')
                        ->label('Option Text')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('value')
                        ->label('Numeric Value')
                        ->numeric()
                        ->helperText('Used for Personality/Likert scale calculations.'),
                ])
                ->columns(3)
                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => in_array($get('type'), ['multiple_choice', 'personality', 'true_false']))
                ->itemLabel(fn (array $state): ?string => $state['option'] ?? null),

            // True/False specific options
            TextInput::make('true_option')
                ->label('True Option Label')
                ->default('True')
                ->required()
                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('type') === 'true_false'),
            
            TextInput::make('false_option')
                ->label('False Option Label')
                ->default('False')
                ->required()
                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('type') === 'true_false'),

            TextInput::make('correct_answer')
                ->label('Correct Answer (Label)')
                ->placeholder('e.g. A, True, B')
                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => in_array($get('type'), ['multiple_choice', 'true_false']))
                ->required(fn (\Filament\Schemas\Components\Utilities\Get $get) => in_array($get('type'), ['multiple_choice', 'true_false']))
                ->maxLength(255),

            // Short answer specific field
            TextInput::make('expected_answer')
                ->label('Expected Answer')
                ->placeholder('Jawaban yang diharapkan (opsional)')
                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('type') === 'short_answer')
                ->helperText('Isi dengan jawaban yang diharapkan untuk panduan penilaian'),
        ];

        $gradingFields = [
            TextInput::make('score')
                ->label('Weight/Score')
                ->numeric()
                ->default(1)
                ->required(),
            Select::make('difficulty')
                ->options([
                    'easy' => 'Easy',
                    'medium' => 'Medium',
                    'hard' => 'Hard',
                ])
                ->default('medium')
                ->required()
                ->native(false),
            Toggle::make('is_active')
                ->label('Active')
                ->default(true),
            Textarea::make('explanation')
                ->label('Explanation/Pembahasan')
                ->rows(2)
                ->columnSpanFull(),
            \Filament\Forms\Components\TagsInput::make('tags')
                ->columnSpanFull(),
        ];

        if ($isCreate) {
            return $schema
                ->components([
                    \Filament\Schemas\Components\Section::make('Question Setup')
                        ->icon('heroicon-o-pencil-square')
                        ->columns(2)
                        ->schema($setupFields),

                    \Filament\Forms\Components\Repeater::make('questions_bulk')
                        ->label('Questions Area')
                        ->schema([
                            $typeField,
                            \Filament\Schemas\Components\Grid::make(1)
                                ->schema([
                                    ...$contentFields,
                                    \Filament\Schemas\Components\Grid::make(3)
                                        ->schema($gradingFields),
                                ]),
                        ])
                        ->default([['type' => 'multiple_choice']])
                        ->reorderableWithButtons()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['question_text'] ?? 'New Question'),
                ]);
        }

        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Question Setup')
                    ->icon('heroicon-o-pencil-square')
                    ->columns(2)
                    ->schema($setupFields),

                \Filament\Schemas\Components\Section::make('Question Content')
                    ->icon('heroicon-o-document-text')
                    ->schema($contentFields),

                \Filament\Schemas\Components\Section::make('Grading & Settings')
                    ->icon('heroicon-o-check-badge')
                    ->columns(3)
                    ->schema($gradingFields),
            ]);
    }
}
