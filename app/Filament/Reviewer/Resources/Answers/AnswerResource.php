<?php

namespace App\Filament\Reviewer\Resources\Answers;

use App\Filament\Reviewer\Resources\Answers\Pages\CreateAnswer;
use App\Filament\Reviewer\Resources\Answers\Pages\EditAnswer;
use App\Filament\Reviewer\Resources\Answers\Pages\ListAnswers;
use App\Filament\Reviewer\Resources\Answers\Schemas\AnswerForm;
use App\Filament\Reviewer\Resources\Answers\Tables\AnswersTable;
use App\Models\Answer;
use App\Models\CandidateAssessment;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AnswerResource extends Resource
{
    protected static ?string $model = CandidateAssessment::class;

    protected static ?string $slug = 'grade-answers';

    protected static ?string $navigationLabel = 'Grade Essays';

    protected static ?string $pluralLabel = 'Grade Essays';

    protected static ?string $breadcrumb = 'Grade Essays';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('status', ['completed', 'reviewed'])
            ->whereHas('answers', function ($query) {
                $query->whereHas('question', function ($q) {
                    $q->whereIn('type', ['essay', 'short_answer']);
                });
            });
    }

    public static function form(Schema $schema): Schema
    {
        return AnswerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('candidate.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('assessment.title')
                    ->label('Assesment')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('essay_score')
                    ->label('Skor Essay')
                    ->getStateUsing(fn ($record) => $record->essay_score)
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('status')
                    ->label('Sudah Dinilai')
                    ->icon(fn (string $state): string => match ($state) {
                        'reviewed' => 'heroicon-o-check-circle',
                        'completed' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'reviewed' => 'success',
                        'completed' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Action::make('nilai')
                    ->label('Nilai')
                    ->button()
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn ($record) => AnswerResource::getUrl('edit', ['record' => $record])),
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
