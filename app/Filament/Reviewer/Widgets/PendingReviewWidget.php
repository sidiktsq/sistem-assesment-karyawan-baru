<?php

namespace App\Filament\Reviewer\Widgets;

use App\Models\CandidateAssessment;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingReviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CandidateAssessment::with(['candidate', 'assessment'])
                    ->where('status', 'completed')
                    ->whereDoesntHave('reviews')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('candidate.name')
                    ->label('Kandidat')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('assessment.title')
                    ->label('Assessment')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('percentage')
                    ->label('Skor Otomatis')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->sortable()
                    ->color(fn ($state) => $state >= 75 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),
                    
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Selesai Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('assessment.type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'programming' => 'info',
                        'marketing' => 'success',
                        'finance' => 'warning',
                        'hr' => 'primary',
                        'general' => 'gray',
                        'personality' => 'purple',
                        'technical' => 'danger',
                    }),
            ])
            ->actions([
                Actions\Action::make('review')
                    ->label('Review')
                    ->url(fn (CandidateAssessment $record): string => route('filament.reviewer.resources.grade-answers.index', [
                        'candidate_assessment_id' => $record->id
                    ]))
                    ->icon('heroicon-m-eye')
                    ->color('primary'),
            ])
            ->paginated([5, 10, 25])
            ->striped()
            ->heading('Menunggu Review');
    }
}
