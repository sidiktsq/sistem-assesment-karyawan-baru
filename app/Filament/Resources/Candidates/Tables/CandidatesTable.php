<?php

namespace App\Filament\Resources\Candidates\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Services\InvitationService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;

use App\Filament\Resources\Candidates\Actions\SendInvitationAction;
use App\Filament\Resources\Candidates\Actions\BulkAssignAction;

class CandidatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('position_applied')
                    ->label('Position')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('source')
                    ->label('Source')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'assessment_scheduled' => 'info',
                        'assessment_ongoing' => 'warning',
                        'assessment_completed' => 'success',
                        'reviewed' => 'primary',
                        'approved' => 'success',
                        'probation' => 'warning',
                        'rejected' => 'danger',
                        'assessment_expired' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Input Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'assessment_scheduled' => 'Scheduled',
                        'assessment_ongoing' => 'Ongoing',
                        'assessment_completed' => 'Completed',
                        'reviewed' => 'Reviewed',
                        'approved' => 'Approved',
                        'probation' => 'Probation',
                        'rejected' => 'Rejected',
                        'assessment_expired' => 'Expired',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('source')
                    ->label('Recruitment Source')
                    ->multiple()
                    ->options(fn () => \App\Models\Candidate::query()->distinct()->pluck('source', 'source')->filter(fn ($value) => !empty($value))->toArray()),
            ])
            ->recordActions([
                EditAction::make(),
                SendInvitationAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAssignAction::make(),
                ]),
            ]);
    }
}
