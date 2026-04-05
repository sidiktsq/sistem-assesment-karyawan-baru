<?php

namespace App\Filament\Reviewer\Resources\CandidateAssessments\Pages;

use App\Filament\Reviewer\Resources\CandidateAssessments\CandidateAssessmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCandidateAssessment extends EditRecord
{
    protected static string $resource = CandidateAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
