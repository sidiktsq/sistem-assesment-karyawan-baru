<?php

namespace App\Filament\Reviewer\Resources\Answers\Pages;

use App\Filament\Reviewer\Resources\Answers\AnswerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAnswer extends EditRecord
{
    protected static string $resource = AnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
