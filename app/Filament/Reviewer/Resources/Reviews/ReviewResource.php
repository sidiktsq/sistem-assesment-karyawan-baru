<?php

namespace App\Filament\Reviewer\Resources\Reviews;

use App\Filament\Reviewer\Resources\Reviews\Pages\CreateReview;
use App\Filament\Reviewer\Resources\Reviews\Pages\EditReview;
use App\Filament\Reviewer\Resources\Reviews\Pages\ListReviews;
use App\Filament\Reviewer\Resources\Reviews\Schemas\ReviewForm;
use App\Filament\Reviewer\Resources\Reviews\Tables\ReviewsTable;
use App\Models\Review;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $slug = 'final-recommendations';

    protected static ?string $navigationLabel = 'Final Reviews';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    public static function form(Schema $schema): Schema
    {
        return ReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('candidateAssessment.candidate.name')
                    ->label('Candidate')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('recommendation')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'probation' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Review Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('recommendation')
                    ->options([
                        'approved' => 'Approved',
                        'probation' => 'Probation',
                        'rejected' => 'Rejected',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
            'create' => CreateReview::route('/create'),
            'edit' => EditReview::route('/{record}/edit'),
        ];
    }
}
