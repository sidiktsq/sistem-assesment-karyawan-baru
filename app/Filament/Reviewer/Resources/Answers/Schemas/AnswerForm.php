<?php

namespace App\Filament\Reviewer\Resources\Answers\Schemas;

use Filament\Schemas\Schema;

class AnswerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Soal & Jawaban Kandidat')
                    ->columns(1)
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('question_display')
                            ->label('Pertanyaan')
                            ->content(fn ($record) => $record?->question?->question_text ?? '-'),
                        \Filament\Forms\Components\Textarea::make('answer')
                            ->label('Jawaban Kandidat')
                            ->rows(6)
                            ->columnSpanFull()
                            ->readOnly(),
                    ]),

                \Filament\Schemas\Components\Section::make('Penilaian Reviewer')
                    ->icon('heroicon-o-check-badge')
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('score_obtained')
                            ->label('Skor Diberikan')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->helperText(fn ($record) => 'Skor Maksimum: ' . ($record?->question?->score ?? 'N/A')),
                        \Filament\Forms\Components\Select::make('is_correct')
                            ->label('Status Jawaban')
                            ->options([
                                1 => '✅ Benar',
                                0 => '❌ Salah',
                            ])
                            ->native(false),
                        \Filament\Forms\Components\Textarea::make('feedback')
                            ->label('Catatan Reviewer')
                            ->rows(3)
                            ->placeholder('Tulis alasan pemberian skor ini...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
