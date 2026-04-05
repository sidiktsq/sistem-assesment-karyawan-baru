<?php

namespace App\Filament\Reviewer\Resources\Answers\Pages;

use App\Filament\Reviewer\Resources\Answers\AnswerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAnswer extends CreateRecord
{
    protected static string $resource = AnswerResource::class;
}
