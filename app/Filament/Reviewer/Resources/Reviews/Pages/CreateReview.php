<?php

namespace App\Filament\Reviewer\Resources\Reviews\Pages;

use App\Filament\Reviewer\Resources\Reviews\ReviewResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReview extends CreateRecord
{
    protected static string $resource = ReviewResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['reviewer_id'] = auth()->id();
        $data['reviewed_at'] = now();

        return $data;
    }
}
