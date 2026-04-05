<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use Filament\Resources\Pages\CreateRecord;

use App\Models\Question;
use Illuminate\Database\Eloquent\Model;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $sharedData = [
            'assessment_id' => $data['assessment_id'],
            'section' => $data['section'],
            'order' => $data['order'],
        ];

        $questions = $data['questions_bulk'] ?? [];
        $lastRecord = null;

        foreach ($questions as $index => $questionData) {
            $recordData = array_merge($sharedData, $questionData);
            
            // Adjust order for each question if needed, or keep shared
            if (isset($recordData['order'])) {
                $recordData['order'] += $index;
            }

            $lastRecord = Question::create($recordData);
        }

        return $lastRecord ?? Question::create($sharedData);
    }
}
