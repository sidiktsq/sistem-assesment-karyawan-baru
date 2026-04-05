<?php

namespace App\Filament\Resources\Assessments\Actions;

use App\Models\Assessment;
use App\Models\Question;
use Filament\Actions\Action;
use Illuminate\Support\Str;

class DuplicateAssessmentAction
{
    public static function make(): Action
    {
        return Action::make('duplicate')
            ->label('Duplikat Assessment')
            ->icon('heroicon-o-document-duplicate')
            ->color('warning')
            ->action(function (Assessment $record) {
                $newAssessment = $record->replicate();
                $newAssessment->title = $record->title . ' (Copy)';
                $newAssessment->created_by = auth()->id();
                $newAssessment->save();

                // Duplicate questions
                foreach ($record->questions as $question) {
                    $newQuestion = $question->replicate();
                    $newQuestion->assessment_id = $newAssessment->id;
                    $newQuestion->save();
                }

                \Filament\Notifications\Notification::make()
                    ->title('Assessment Berhasil Diduplikat')
                    ->body("Assessment '{$newAssessment->title}' telah dibuat dengan {$record->questions->count()} soal")
                    ->success()
                    ->send();

                return redirect()->route('filament.admin.resources.assessments.edit', $newAssessment);
            })
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Duplikasi')
            ->modalDescription('Apakah Anda yakin ingin menduplikat assessment ini beserta semua soalnya?')
            ->modalSubmitActionLabel('Ya, Duplikat');
    }
}
