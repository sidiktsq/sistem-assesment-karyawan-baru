<?php

namespace App\Filament\Resources\Assessments\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class SmartImportAction
{
    public static function make(): Action
    {
        return Action::make('smartImport')
            ->label('Smart AI Import (Paste)')
            ->icon('heroicon-o-sparkles')
            ->color('primary')
            ->modalHeading('Import Soal dengan AI')
            ->modalDescription('Tempel teks soal dari PDF, Word, atau sumber mana pun. AI akan otomatis mendeteksi soal, pilihan, dan kunci jawaban.')
            ->form([
                Textarea::make('content')
                    ->label('Teks Soal')
                    ->placeholder('Contoh: 1. Apa itu PHP? A. Bahasa Pemrograman B. Sayuran...')
                    ->rows(15)
                    ->required()
                    ->helperText('Gunakan Copy-Paste dari file PDF atau Word Anda ke sini.'),
            ])
            ->action(function (array $data, $get, $set) {
                $apiKey = config('services.gemini.key');
                
                if (!$apiKey) {
                    Notification::make()
                        ->title('API Key Gemini belum diset')
                        ->body('Harap tambahkan GEMINI_API_KEY di file .env Anda.')
                        ->danger()
                        ->send();
                    return;
                }

                $content = $data['content'];

                $prompt = "Tujuan: Konversi teks berikut menjadi format JSON soal assessment yang sesuai untuk sistem kustom.
                Teks input bisa berupa soal pilihan ganda atau essay dalam format apapun.
                
                ATURAN PENTING:
                1. Output HARUS berupa array JSON valid.
                2. JANGAN sertakan teks tambahan apa pun sebelum atau sesudah JSON block.
                3. Jika ada pilihan ganda, deteksi teks soal, opsi (A-E), dan value (1 untuk benar, 0 untuk salah).
                4. Jika ada essay, deteksi guideline-nya jika ada.
                5. Field 'type' harus 'multiple_choice' atau 'essay'.
                6. Field 'difficulty' harus 'easy', 'medium', atau 'hard'.
                
                STRUKTUR JSON:
                [
                  {
                    \"question_text\": \"Teks soal\",
                    \"type\": \"multiple_choice\" atau \"essay\",
                    \"section\": \"general\",
                    \"score\": 1,
                    \"difficulty\": \"easy\", \"medium\", atau \"hard\",
                    \"options\": [
                       {\"option\": \"A\", \"text\": \"Teks opsi\", \"value\": 1 atau 0},
                       {\"option\": \"B\", \"text\": \"Teks opsi\", \"value\": 0}
                    ],
                    \"correct_answer\": \"A\" (hanya untuk multiple_choice),
                    \"essay_guidelines\": \"...\" (opsional),
                    \"min_words\": 0,
                    \"max_words\": 1000
                  }
                ]
                
                TEKS INPUT:
                " . $content;

                try {
                    $response = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}", [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ]
                    ]);

                    if ($response->failed()) {
                        throw new \Exception('Gagal menghubungi Gemini API: ' . ($response->json()['error']['message'] ?? 'Unknown Error'));
                    }

                    $result = $response->json();
                    $responseText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    
                    // Cleanup possible markdown backticks
                    $responseText = str_replace(['```json', '```'], '', $responseText);
                    $questions = json_decode($responseText, true);

                    if (!is_array($questions)) {
                        throw new \Exception('Format respon AI tidak valid.');
                    }

                    // Ensure all imported questions are active by default and have an order
                    foreach ($questions as &$question) {
                        if (!isset($question['is_active'])) {
                            $question['is_active'] = true;
                        }

                        if (!isset($question['order'])) {
                            $question['order'] = 0;
                        }
                    }

                    $currentState = $get('questions') ?? [];
                    
                    // Filter out empty placeholder questions before merging
                    $currentState = array_filter($currentState, function ($question) {
                        return !empty($question['question_text']) || !empty($question['options']) || !empty($question['id']);
                    });

                    $set('questions', array_merge($currentState, $questions));

                    Notification::make()
                        ->title('Berhasil Memproses Soal')
                        ->body(count($questions) . ' soal telah terdeteksi dan dimasukkan ke daftar.')
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Oops! Terjadi kesalahan')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
