<?php

namespace App\Filament\Resources\Candidates\Actions;

use App\Services\InvitationService;
use App\Models\CandidateAssessment;
use Filament\Actions\Action;
use Filament\Forms\Get;
use Filament\Notifications\Notification;

class SendInvitationAction
{
    public static function make(): Action
    {
        return Action::make('sendInvitation')
            ->label('Kirim Undangan')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->hidden(fn ($record) => in_array($record?->status, ['assessment_completed', 'approved']))
            ->form([
                \Filament\Forms\Components\Select::make('assessment_id')
                    ->label('Pilih Assessment')
                    ->required()
                    ->options(function () {
                        return \App\Models\Assessment::where('is_active', true)
                            ->pluck('title', 'id')
                            ->toArray();
                    })
                    ->reactive(),
                    
                \Filament\Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('Jadwal Mulai')
                    ->required()
                    ->default(now()),
                    
                \Filament\Forms\Components\DateTimePicker::make('deadline')
                    ->label('Deadline')
                    ->required()
                    ->default(now()->addDays(7))
                    ->after('scheduled_at'),
            ])
            ->action(function (array $data, $record, InvitationService $invitationService) {
                // Find existing active assessment or create new one
                $candidateAssessment = \App\Models\CandidateAssessment::where('candidate_id', $record->id)
                    ->where('assessment_id', $data['assessment_id'])
                    ->whereIn('status', ['scheduled', 'ongoing'])
                    ->first();

                if ($candidateAssessment) {
                    $candidateAssessment->update([
                        'assigned_by' => auth()->id(),
                        'scheduled_at' => $data['scheduled_at'],
                        'deadline' => $data['deadline'],
                    ]);
                } else {
                    $candidateAssessment = \App\Models\CandidateAssessment::create([
                        'candidate_id' => $record->id,
                        'assessment_id' => $data['assessment_id'],
                        'assigned_by' => auth()->id(),
                        'scheduled_at' => $data['scheduled_at'],
                        'deadline' => $data['deadline'],
                        'status' => 'scheduled',
                    ]);
                }

                // Send invitation
                $success = $invitationService->sendInvitation($candidateAssessment);

                if ($success) {
                    $record->update(['status' => 'assessment_scheduled']);
                    \Filament\Notifications\Notification::make()
                        ->title('Undangan Berhasil Dikirim')
                        ->body("Undangan assessment telah dikirim ke {$record->email}")
                        ->success()
                        ->send();
                } else {
                    \Filament\Notifications\Notification::make()
                        ->title('Gagal Mengirim Undangan')
                        ->body('Terjadi kesalahan saat mengirim email. Silakan coba lagi.')
                        ->danger()
                        ->send();
                }
            })
            ->requiresConfirmation()
            ->modalHeading('Kirim Undangan Assessment')
            ->modalDescription('Pilih assessment dan jadwal untuk kandidat ini.')
            ->modalSubmitActionLabel('Kirim Undangan');
    }
}
