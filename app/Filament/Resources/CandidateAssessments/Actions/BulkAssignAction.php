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
                $results = $invitationService->sendBulkInvitations($records->pluck('id')->toArray());
                
                $success = count(array_filter($results));
                $total = count($results);
                
                \Filament\Notifications\Notification::make()
                    ->title('Proses Bulk Assign Selesai')
                    ->body("Berhasil mengirim {$success} dari {$total} undangan")
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
