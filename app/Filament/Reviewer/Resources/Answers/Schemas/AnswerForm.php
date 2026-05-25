<?php

namespace App\Filament\Reviewer\Resources\Answers\Schemas;

use Filament\Schemas\Schema;

class AnswerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Penilaian Seluruh Jawaban Essay')
                    ->description('Silakan berikan nilai dan catatan untuk setiap jawaban essay kandidat di bawah ini.')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('answers')
                            ->relationship('answers', function ($query) {
                                $query->whereHas('question', fn($q) => $q->whereIn('type', ['essay', 'short_answer']));
                            })
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(1)
                                    ->schema([
                                        \Filament\Forms\Components\Placeholder::make('question_text')
                                            ->label('Pertanyaan')
                                            ->content(fn ($record) => new \Illuminate\Support\HtmlString('<div style="font-size: 1.15em; font-weight: 500; color: #f8fafc; line-height: 1.6;">' . nl2br(e($record?->question?->question_text ?? '')) . '</div>')),
                                        
                                        \Filament\Forms\Components\Textarea::make('answer')
                                            ->label('Jawaban Kandidat')
                                            ->rows(5)
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'background-color: rgba(255, 255, 255, 0.03); color: #e2e8f0; font-size: 1.05em; padding: 0.75rem;']),
                                        
                                        \Filament\Schemas\Components\Section::make('Penilaian')
                                            ->columns(2)
                                            ->schema([
                                                \Filament\Forms\Components\TextInput::make('score_obtained')
                                                    ->label('Skor Diberikan')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(0)
                                                    ->maxValue(fn ($record) => $record?->question?->score ?? 100)
                                                    ->helperText(fn ($record) => 'Skor Maksimum: ' . ($record?->question?->score ?? '100'))
                                                    ->extraInputAttributes(['style' => 'font-size: 1.1em; font-weight: bold;']),
                                                
                                                \Filament\Forms\Components\Textarea::make('feedback')
                                                    ->label('Catatan Reviewer / Noted')
                                                    ->placeholder('Tulis alasan pemberian skor...')
                                                    ->rows(2),
                                            ])
                                    ])
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->itemLabel(fn (array $state): ?string => "Jawaban #" . ($state['id'] ?? ''))
                    ]),
            ]);
    }
}
