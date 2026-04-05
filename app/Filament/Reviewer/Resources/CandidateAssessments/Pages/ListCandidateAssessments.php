<?php

namespace App\Filament\Reviewer\Resources\CandidateAssessments\Pages;

use App\Filament\Reviewer\Resources\CandidateAssessments\CandidateAssessmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCandidateAssessments extends ListRecords
{
    protected static string $resource = CandidateAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
