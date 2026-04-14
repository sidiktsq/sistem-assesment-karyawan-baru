<?php

namespace App\Filament\Resources\Assessments\Actions;

use Filament\Forms\Components\FileUpload;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use SplFileObject;

class ImportQuestionsAction
{
    public static function make(): Action
    {
        return Action::make('importQuestions')
            ->label('Import dari CSV')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->modalHeading('Import Soal dari File CSV')
            ->modalDescription('Unggah file CSV sesuai format template untuk menambahkan soal secara massal.')
            ->form([
                FileUpload::make('file')
                    ->label('Pilih File CSV')
                    ->acceptedFileTypes(['text/csv', 'application/csv', 'text/plain'])
                    ->required()
                    ->disk('local')
                    ->directory('temp-imports'),
            ])
            ->action(function (array $data, $get, $set) {
                $filePath = Storage::disk('local')->path($data['file']);
                
                if (!file_exists($filePath)) {
                    Notification::make()->title('File tidak ditemukan')->error()->send();
                    return;
                }

                $questions = [];
                $file = new SplFileObject($filePath);
                $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_AHEAD);
                
                $headers = [];
                foreach ($file as $index => $row) {
                    if ($index === 0) {
                        $headers = $row;
                        continue;
                    }

                    if (empty($row) || count($row) < 2) continue;

                    if (count($headers) !== count($row)) {
                        $row = array_pad($row, count($headers), '');
                    }

                    $record = array_combine($headers, $row);
                    
                    $type = $record['type'] ?? 'multiple_choice';
                    $options = [];

                    // Parse options A-E
                    foreach (['a', 'b', 'c', 'd', 'e'] as $char) {
                        $textKey = "option_{$char}_text";
                        $valueKey = "option_{$char}_value";
                        
                        if (!empty($record[$textKey])) {
                            $options[] = [
                                'option' => strtoupper($char),
                                'text' => $record[$textKey],
                                'value' => (int)($record[$valueKey] ?? ($type === 'multiple_choice' ? ($record['correct_answer'] == strtoupper($char) ? 1 : 0) : 0)),
                            ];
                        }
                    }

                    $questions[] = [
                        'type' => $type,
                        'section' => $record['section'] ?? 'general',
                        'question_text' => $record['question_text'] ?? '',
                        'score' => (int)($record['score'] ?? 1),
                        'difficulty' => $record['difficulty'] ?? 'medium',
                        'correct_answer' => $record['correct_answer'] ?? null,
                        'essay_guidelines' => $record['essay_guidelines'] ?? null,
                        'min_words' => (int)($record['min_words'] ?? 0),
                        'max_words' => (int)($record['max_words'] ?? 1000),
                        'options' => $options,
                        'is_active' => true,
                        'order' => 0,
                    ];
                }

                // Append to current repeater state
                $currentState = $get('questions') ?? [];
                $set('questions', array_merge($currentState, $questions));

                // Cleanup
                Storage::disk('local')->delete($data['file']);

                Notification::make()
                    ->title('Berhasil Import')
                    ->body(count($questions) . ' soal telah ditambahkan ke daftar.')
                    ->success()
                    ->send();
            });
    }

    public static function makeDownloadTemplate(): Action
    {
        return Action::make('downloadTemplate')
            ->label('Template CSV')
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray')
            ->action(fn() => self::downloadTemplate());
    }

    public static function downloadTemplate()
    {
        $headers = [
            'question_text', 'type', 'section', 'score', 'difficulty', 'correct_answer',
            'essay_guidelines', 'min_words', 'max_words',
            'option_a_text', 'option_a_value',
            'option_b_text', 'option_b_value',
            'option_c_text', 'option_c_value',
            'option_d_text', 'option_d_value',
            'option_e_text', 'option_e_value'
        ];

        $example = [
            'Apa kepanjangan dari PHP?', 'multiple_choice', 'general', '1', 'easy', 'A',
            '', '', '',
            'Hypertext Preprocessor', '1',
            'Personal Home Page', '0',
            'Private Host Page', '0',
            'Python HTML PHP', '0',
            '', ''
        ];

        $callback = function() use ($headers, $example) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, $example);
            
            // Add a blank row for essay example
            fputcsv($file, [
                'Ceritakan pengalaman Anda menggunakan Laravel.', 'essay', 'general', '5', 'medium', '',
                'Gunakan gaya bahasa formal', '50', '500',
                '', '', '', '', '', '', '', '', '', ''
            ]);
            
            fclose($file);
        };

        return response()->streamDownload($callback, 'template_soal_assessment.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
