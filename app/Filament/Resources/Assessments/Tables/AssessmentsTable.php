<?php

namespace App\Filament\Resources\Assessments\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssessmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->suffix(' min')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('questions_count')
                    ->label('Questions')
                    ->counts('questions')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('type'),
                \Filament\Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (\App\Models\Assessment $record) {
                        $newAssessment = $record->replicate();
                        $newAssessment->title = $newAssessment->title . ' (Copy)';
                        $newAssessment->push();

                        foreach ($record->questions as $question) {
                            $newQuestion = $question->replicate();
                            $newQuestion->assessment_id = $newAssessment->id;
                            $newQuestion->push();
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Assessment duplicated successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
