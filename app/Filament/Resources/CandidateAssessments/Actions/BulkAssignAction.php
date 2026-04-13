<?php

namespace App\Filament\Resources\CandidateAssessments\Actions;

use App\Services\InvitationService;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Collection;

class BulkAssignAction
{
    public static function make(): Action
    {
        return Action::make('bulkAssign')
            ->label('Assign & Kirim Undangan')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->action(function (Collection $records, InvitationService $invitationService) {
                // Filter out candidates who are already completed or approved
                $validRecords = $records->filter(function ($record) {
                    return !in_array($record->candidate->status, ['assessment_completed', 'approved']);
                });

                if ($validRecords->isEmpty()) {
                    \Filament\Notifications\Notification::make()
                        ->title('Tidak Ada Undangan Terkirim')
                        ->body("Kandidat yang dipilih sudah menyelesaikan assessment atau sudah disetujui.")
                        ->warning()
                        ->send();
                    return;
                }

                $results = $invitationService->sendBulkInvitations($validRecords->pluck('id')->toArray());
                
                $success = count(array_filter($results));
                $total = count($results);
                
                \Filament\Notifications\Notification::make()
                    ->title('Proses Bulk Assign Selesai')
                    ->body("Berhasil mengirim {$success} dari {$total} undangan valid")
                    ->success()
                    ->send();
            })
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Bulk Assign')
            ->modalDescription('Apakah Anda yakin ingin mengirim undangan assessment ke semua kandidat yang dipilih?')
            ->modalSubmitActionLabel('Ya, Kirim Undangan')
            ->deselectRecordsAfterCompletion();
    }
}
