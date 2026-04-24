<?php

namespace App\Filament\Resources\Assessments\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Radio;
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
                Radio::make('mode')
                    ->label('Mode Import')
                    ->options([
                        'everything' => 'Semua (Soal, Jawaban, & Nilai)',
                        'questions_only' => 'Hanya Soal (Generate/Extract Soal Sahaja)',
                        'answers_only' => 'Hanya Jawaban (Generate Jawaban untuk Soal)',
                        'scores_only' => 'Hanya Nilai (Generate Score & Difficulty)',
                    ])
                    ->default('everything')
                    ->required(),
                Textarea::make('content')
                    ->label('Teks Input / Topik')
                    ->placeholder('Tempel soal atau ketik topik (misal: "Dasar-Dasar Keamanan Siber")')
                    ->rows(15)
                    ->required()
                    ->helperText('Anda bisa menempelkan soal mentah atau hanya mengetik sebuah topik untuk dibuatkan soal baru oleh AI.'),
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
                $mode = $data['mode'];
                $currentQuestions = $get('questions') ?? []; // Ambil soal yang sudah ada di form

                $modeInstructions = match($mode) {
                    'everything' => [
                        'role' => 'ASSESSMENT_ARCHITECT',
                        'desc' => 'EKSTRAK SEMUA: question_text, type, section, score, difficulty, options, correct_answer, essay_guidelines, min_words, max_words. Jika tidak ada di teks, buatkan yang logis.',
                    ],
                    'questions_only' => [
                        'role' => 'QUESTION_GENERATOR',
                        'desc' => 'FOKUS HANYA PADA: question_text, type, section. Jangan mengubah atau mengosongkan field lainnya.',
                    ],
                    'answers_only' => [
                        'role' => 'EXPERT_ANSWERER',
                        'desc' => 'FOKUS HANYA PADA: options, correct_answer, essay_guidelines. Jangan mengubah atau mengosongkan field lainnya.',
                    ],
                    'scores_only' => [
                        'role' => 'ASSESSMENT_GRADER',
                        'desc' => 'FOKUS HANYA PADA: score (1-10), difficulty. Jangan mengubah atau mengosongkan field lainnya.',
                    ],
                    default => [
                        'role' => 'GENERAL_ASSISTANT',
                        'desc' => 'Ekstrak soal secara lengkap.',
                    ],
                };

                $prompt = "ANDA ADALAH: " . $modeInstructions['role'] . "\n" .
                "PERINTAH KHUSUS: " . $modeInstructions['desc'] . "\n\n" .
                "KONTEKS SOAL SAAT INI (Jika ada): \n" . json_encode($currentQuestions) . "\n\n" .
                "TEKS INPUT DARI USER (Bisa berupa soal mentah atau instruksi): \n" . $content . "\n\n" .
                "ATURAN OUTPUT:
                1. Kembalikan HANYA array JSON soal yang valid.
                2. PRIORITAS UTAMA: Jika teks input memiliki kunci jawaban (misal: 'Jawaban: A' atau 'Kunci: B'), Anda WAJIB mengekstraknya secara akurat tanpa mengubahnya.
                3. Jika Anda memperbarui atau mengubah soal tertentu (misal: \"ubah order 1\"), Anda WAJIB menyertakan field \"order\" dengan nilai integer yang sesuai (misal: \"order\": 1).
                4. HANYA sertakan soal yang datanya berubah atau diminta untuk diubah di dalam JSON. Jangan mengirimkan soal yang tidak ada perubahannya.
                5. Jika mode adalah 'scores_only' atau 'answers_only', kembalikan HANYA field yang diminta saja di dalam JSON agar tidak menimpa data lain.
                
                STRUKTUR JSON:
                [
                  {
                    \"question_text\": \"...\",
                    \"type\": \"...\",
                    \"score\": integer,
                    \"difficulty\": \"...\",
                    \"options\": [{\"option\": \"A\", \"text\": \"...\", \"value\": 1}],
                    \"correct_answer\": \"A\",
                    \"essay_guidelines\": \"...\",
                    \"min_words\": integer,
                    \"max_words\": integer
                  }
                ]

                PENTING: Selalu patuhi instruksi 'Mode' di atas. Jangan mengembalikan nilai kosong ([]) untuk field yang tidak diminta.";

                try {
                    $response = Http::timeout(60)
                        ->retry(3, 2000)
                        ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}", [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.1,
                        ],
                    ]);

                    if ($response->failed()) {
                        throw new \Exception('Gagal menghubungi Gemini API: ' . ($response->json()['error']['message'] ?? 'Unknown Error'));
                    }

                    $result = $response->json();
                    $responseText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '[]';

                    // Robust JSON Extraction (Menghapus markdown code blocks jika ada)
                    if (preg_match('/\[.*\]/s', $responseText, $matches)) {
                        $responseText = $matches[0];
                    }

                    $questions = json_decode($responseText, true);

                    if (!is_array($questions) || empty($questions)) {
                        throw new \Exception('AI tidak mengembalikan data soal yang valid atau input tidak bisa diproses.');
                    }

                    // Logika Penggabungan (Smart Merge) & Deteksi Perubahan
                    $modifiedOrders = [];
                    $currentQuestionsCollection = collect($currentQuestions);
                    $newQuestions = [];
                    $updatedQuestionsMap = []; // Map of [index => updated_question]

                    foreach ($questions as $aiIndex => $aiQuestion) {
                        $aiOrder = $aiQuestion['order'] ?? 0;
                        $filteredAiQuestion = array_filter($aiQuestion, function($value) {
                            return $value !== null && $value !== [] && $value !== '';
                        });

                        // Cari apakah ada soal dengan order yang sama
                        $existingQuestionKey = $currentQuestionsCollection->search(function ($item) use ($aiOrder) {
                            return (int)($item['order'] ?? -1) === (int)$aiOrder;
                        });

                        if ($existingQuestionKey !== false) {
                            // Deteksi Perubahan
                            $hasChanges = false;
                            foreach ($filteredAiQuestion as $key => $value) {
                                if (!isset($currentQuestions[$existingQuestionKey][$key]) || $currentQuestions[$existingQuestionKey][$key] != $value) {
                                    $hasChanges = true;
                                    break;
                                }
                            }

                            if ($hasChanges) {
                                $modifiedOrders[] = (int)$aiOrder;
                            }
                            
                            // Update Soal yang Ada (Hanya jika ada perubahan untuk efisiensi)
                            if ($hasChanges) {
                                $updatedQuestionsMap[$existingQuestionKey] = array_merge($currentQuestions[$existingQuestionKey], $filteredAiQuestion);
                            }
                        } else {
                            // Tambah Soal Baru
                            $aiQuestion['is_active'] = $aiQuestion['is_active'] ?? true;
                            if ($aiOrder == 0) {
                                $lastOrder = $currentQuestionsCollection->max('order') ?? 0;
                                $aiQuestion['order'] = $lastOrder + count($newQuestions) + 1;
                            }
                            $newQuestions[] = $aiQuestion;
                            $modifiedOrders[] = $aiQuestion['order'];
                        }
                    }

                    // Terapkan update ke array asli
                    $finalQuestions = $currentQuestions;
                    foreach ($updatedQuestionsMap as $key => $updatedData) {
                        $finalQuestions[$key] = $updatedData;
                    }

                    // Gabungkan dengan soal baru
                    $finalQuestions = array_merge($finalQuestions, $newQuestions);

                    $set('questions', $finalQuestions);

                    // Hanya ambil data unik dan urutkan
                    $orderList = collect($modifiedOrders)->unique()->sort()->implode(', ');
                    
                    // Jika tidak ada yang berubah, gunakan jumlah total sebagai fallback
                    if (empty($orderList)) {
                        $orderList = count($questions);
                    }

                    $notificationData = match($mode) {
                        'everything' => [
                            'title' => 'Berhasil Memproses Soal',
                            'body' => 'Seluruh data soal telah berhasil dimasukkan kembali.',
                            'color' => 'success',
                            'icon' => 'heroicon-o-check-circle',
                        ],
                        'questions_only' => [
                            'title' => "Soal order {$orderList} sudah diubah",
                            'body' => 'Struktur pertanyaan telah berhasil diperbarui.',
                            'color' => 'info',
                            'icon' => 'heroicon-o-document-text',
                        ],
                        'answers_only' => [
                            'title' => "Jawaban untuk order {$orderList} sudah diubah",
                            'body' => 'Kunci jawaban telah berhasil diperbarui.',
                            'color' => 'warning',
                            'icon' => 'heroicon-o-key',
                        ],
                        'scores_only' => [
                            'title' => "Nilai untuk order {$orderList} sudah diubah",
                            'body' => 'Skor dan tingkat kesulitan telah berhasil diperbarui.',
                            'color' => 'info',
                            'icon' => 'heroicon-o-chart-bar',
                        ],
                        default => [
                            'title' => 'Berhasil Memproses Soal',
                            'body' => "Soal dengan nomor urut {$orderList} telah diproses.",
                            'color' => 'success',
                            'icon' => 'heroicon-o-check',
                        ],
                    };

                    Notification::make()
                        ->title($notificationData['title'])
                        ->body($notificationData['body'])
                        ->color($notificationData['color'])
                        ->icon($notificationData['icon'])
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Gagal Menghubungi AI')
                        ->body("Detail: " . $e->getMessage() . ". Pastikan model yang digunakan aktif di region Anda.")
                        ->danger()
                        ->persistent()
                        ->send();
                }
            });
    }
}
