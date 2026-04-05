<?php

namespace App\Filament\Reviewer\Resources\CandidateAssessments;

use App\Filament\Reviewer\Resources\CandidateAssessments\Pages\CreateCandidateAssessment;
use App\Filament\Reviewer\Resources\CandidateAssessments\Pages\EditCandidateAssessment;
use App\Filament\Reviewer\Resources\CandidateAssessments\Pages\ListCandidateAssessments;
use App\Filament\Reviewer\Resources\CandidateAssessments\Schemas\CandidateAssessmentForm;
use App\Filament\Reviewer\Resources\CandidateAssessments\Tables\CandidateAssessmentsTable;
use App\Models\CandidateAssessment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CandidateAssessmentResource extends Resource
{
    protected static ?string $model = CandidateAssessment::class;

    protected static ?string $slug = 'pending-reviews';

    protected static ?string $navigationLabel = 'Pending Reviews';

    protected static ?string $breadcrumb = 'Pending Reviews';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('status', ['completed', 'reviewed']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Candidate Assessment Overview')
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('candidate_name')
                            ->label('Candidate')
                            ->placeholder(fn ($record) => $record?->candidate?->name)
                            ->readOnly(),
                        \Filament\Forms\Components\TextInput::make('assessment_title')
                            ->label('Assessment')
                            ->placeholder(fn ($record) => $record?->assessment?->title)
                            ->readOnly(),
                        \Filament\Forms\Components\TextInput::make('total_score')
                            ->label('Current Score')
                            ->readOnly(),
                        \Filament\Forms\Components\TextInput::make('percentage')
                            ->suffix('%')
                            ->readOnly(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('candidate.name')
                    ->label('Candidate')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('assessment.title')
                    ->label('Assessment')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('completed_at')
                    ->label('Finished At')
                    ->dateTime()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'warning',
                        'reviewed' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'completed' => 'Awaiting Review',
                        'reviewed' => 'Reviewed',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCandidateAssessments::route('/'),
        ];
    }
}
