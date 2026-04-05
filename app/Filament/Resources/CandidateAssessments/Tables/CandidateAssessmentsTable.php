<?php

namespace App\Filament\Resources\CandidateAssessments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
                

class CandidateAssessmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('candidate_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('assessment_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('assigned_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('deadline')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'ongoing' => 'warning',
                        'completed' => 'success',
                        'expired' => 'danger',
                        'reviewed' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('total_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('percentage')
                    ->numeric()
                    ->sortable()
                    ->suffix('%'),
                TextColumn::make('result')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pass' => 'success',
                        'fail' => 'danger',
                        'pending' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('token_expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'ongoing' => 'Ongoing',
                        'completed' => 'Completed',
                        'expired' => 'Expired',
                        'reviewed' => 'Reviewed',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('result')
                    ->options([
                        'pass' => 'Pass',
                        'fail' => 'Fail',
                        'pending' => 'Pending',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                Action::make('send_results')
                    ->label('Send Results')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'reviewed' && !$record->result_sent_at)
                    ->action(function ($record) {
                        try {
                            $record->sendResultEmail();
                            Notification::make()
                                ->title('Results sent successfully')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Failed to send results')
                                ->danger()  
                                ->description($e->getMessage())
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Send Assessment Results')
                    ->modalDescription('Are you sure you want to send the results to the candidate via email?')
                    ->modalSubmitActionLabel('Yes, send now'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
