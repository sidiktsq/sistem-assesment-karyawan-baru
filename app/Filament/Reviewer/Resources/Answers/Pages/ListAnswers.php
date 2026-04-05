<?php

namespace App\Filament\Reviewer\Resources\Answers\Pages;

use App\Filament\Reviewer\Resources\Answers\AnswerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAnswers extends ListRecords
{
    protected static string $resource = AnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
