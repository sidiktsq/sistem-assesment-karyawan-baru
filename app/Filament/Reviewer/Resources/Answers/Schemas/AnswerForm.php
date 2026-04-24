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
                                            ->content(fn ($record) => $record?->question?->question_text),
                                        \Filament\Forms\Components\Textarea::make('answer')
                                            ->label('Jawaban Kandidat')
                                            ->rows(4)
                                            ->readOnly(),
                                        \Filament\Schemas\Components\Section::make('Penilaian')
                                            ->columns(2)
                                            ->schema([
                                                \Filament\Forms\Components\TextInput::make('score_obtained')
                                                    ->label('Skor Diberikan')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(0)
                                                    ->maxValue(fn ($record) => $record?->question?->score ?? 100)
                                                    ->helperText(fn ($record) => 'Skor Maksimum: ' . ($record?->question?->score ?? 'N/A')),
                                                \Filament\Forms\Components\Textarea::make('feedback')
                                                    ->label('Catatan Reviewer / Noted')
                                                    ->placeholder('Tulis alasan pemberian skor ini...')
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
