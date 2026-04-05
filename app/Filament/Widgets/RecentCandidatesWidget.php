<?php

namespace App\Filament\Widgets;

use App\Models\CandidateAssessment;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentCandidatesWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CandidateAssessment::with(['candidate', 'assessment'])
                    ->latest()
                    ->limit(10)
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
                    
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'gray',
                        'ongoing' => 'warning',
                        'completed' => 'info',
                        'reviewed' => 'success',
                        'expired' => 'danger',
                    }),
                    
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Jadwal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('deadline')
                    ->label('Deadline')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Actions\Action::make('view')
                    ->url(fn (CandidateAssessment $record): string => route('filament.admin.resources.candidate-assessments.edit', $record))
                    ->icon('heroicon-m-eye'),
            ])
            ->paginated([5, 10, 25])
            ->striped();
    }
}
