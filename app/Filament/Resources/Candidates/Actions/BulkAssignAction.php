<?php

namespace App\Filament\Resources\Candidates\Actions;

use App\Models\CandidateAssessment;
use App\Services\InvitationService;
use Filament\Actions\Action;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class BulkAssignAction
{
    public static function make(): Action
    {
        return Action::make('bulkAssign')
            ->label('Bulk Assign Assessment')
            ->icon('heroicon-o-users')
            ->color('primary')
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
                    
                \Filament\Forms\Components\Toggle::make('send_invitation')
                    ->label('Kirim Undangan Email')
                    ->default(true)
                    ->helperText('Kirim email undangan ke semua kandidat'),
            ])
            ->action(function (array $data, Collection $records, InvitationService $invitationService) {
                $successCount = 0;
                $updatedCount = 0;
                $errorCount = 0;
                $invitationResults = [];
                
                foreach ($records as $candidate) {
                    // Skip if completed or approved
                    if (in_array($candidate->status, ['assessment_completed', 'approved'])) {
                        continue;
                    }
                    
                    try {
                        // Check if an active assessment already exists
                        $candidateAssessment = CandidateAssessment::where('candidate_id', $candidate->id)
                            ->where('assessment_id', $data['assessment_id'])
                            ->whereIn('status', ['scheduled', 'ongoing'])
                            ->first();

                        if ($candidateAssessment) {
                            $candidateAssessment->update([
                                'assigned_by' => auth()->id(),
                                'scheduled_at' => $data['scheduled_at'],
                                'deadline' => $data['deadline'],
                            ]);
                            $updatedCount++;
                        } else {
                            // Create candidate assessment
                            $candidateAssessment = CandidateAssessment::create([
                                'candidate_id' => $candidate->id,
                                'assessment_id' => $data['assessment_id'],
                                'assigned_by' => auth()->id(),
                                'scheduled_at' => $data['scheduled_at'],
                                'deadline' => $data['deadline'],
                                'status' => 'scheduled',
                            ]);
                            $successCount++;
                        }

                        // Send invitation if requested
                        if ($data['send_invitation']) {
                            $success = $invitationService->sendInvitation($candidateAssessment);
                            $invitationResults[$candidate->id] = $success;
                        } else {
                            $invitationResults[$candidate->id] = true;
                        }
                    } catch (\Exception $e) {
                        $errorCount++;
                        $invitationResults[$candidate->id] = false;
                    }
                }
                
                $sentCount = count(array_filter($invitationResults));
                $body = "Berhasil memproses " . ($successCount + $updatedCount) . " kandidat.";
                if ($updatedCount > 0) {
                    $body .= " ({$updatedCount} diperbarui/dikirim ulang).";
                }
                if ($data['send_invitation']) {
                    $body .= " {$sentCount} undangan terkirim.";
                }
                
                Notification::make()
                    ->title('Bulk Assign Selesai')
                    ->body($body)
                    ->success($errorCount === 0)
                    ->warning($errorCount > 0)
                    ->send();
            })
            ->requiresConfirmation()
            ->modalHeading('Bulk Assign Assessment')
            ->modalDescription('Assign assessment ke semua kandidat yang dipilih')
            ->modalSubmitActionLabel('Assign & Kirim')
            ->deselectRecordsAfterCompletion();
    }
}
