<?php

namespace App\Filament\Reviewer\Resources\Answers;

use App\Filament\Reviewer\Resources\Answers\Pages\CreateAnswer;
use App\Filament\Reviewer\Resources\Answers\Pages\EditAnswer;
use App\Filament\Reviewer\Resources\Answers\Pages\ListAnswers;
use App\Filament\Reviewer\Resources\Answers\Schemas\AnswerForm;
use App\Filament\Reviewer\Resources\Answers\Tables\AnswersTable;
use App\Models\Answer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AnswerResource extends Resource
{
    protected static ?string $model = Answer::class;

    protected static ?string $slug = 'grade-answers';

    protected static ?string $navigationLabel = 'Grade Essays';

    protected static ?string $pluralLabel = 'Grade Essays';

    protected static ?string $breadcrumb = 'Grade Essays';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()
            ->whereHas('question', function ($query) {
                $query->whereIn('type', ['essay', 'short_answer', 'personality']);
            });

        // Filter by candidate_assessment_id if provided in the URL
        if (\Illuminate\Support\Facades\Request::has('candidate_assessment_id')) {
            $query->where('candidate_assessment_id', \Illuminate\Support\Facades\Request::query('candidate_assessment_id'));
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return AnswerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('candidateAssessment.candidate.name')
                    ->label('Kandidat')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('question.question_text')
                    ->label('Soal')
                    ->limit(60)
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('answer')
                    ->label('Jawaban Kandidat')
                    ->limit(80)
                    ->wrap(),
                \Filament\Tables\Columns\TextColumn::make('score_obtained')
                    ->label('Skor')
                    ->default('Belum dinilai')
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('is_reviewed')
                    ->label('Sudah Dinilai')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->reviewed_at !== null || $record->score_obtained !== null),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('reviewed_at')
                    ->label('Status Penilaian')
                    ->nullable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAnswers::route('/'),
            'edit' => EditAnswer::route('/{record}/edit'),
        ];
    }
}
