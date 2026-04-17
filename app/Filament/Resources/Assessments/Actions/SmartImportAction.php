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
                        'desc' => 'EKSTRAK SEMUA: question_text, type, section, score, difficulty, options, correct_answer, essay_guidelines. Jika tidak ada di teks, buatkan yang logis.',
                    ],
                    'questions_only' => [
                        'role' => 'QUESTION_GENERATOR',
                        'desc' => 'EKSTRAK HANYA: question_text, type, section. DILARANG KERAS mengisi: options (set []), correct_answer (set null), essay_guidelines (set null), score (set 0), difficulty (set "medium").',
                    ],
                    'answers_only' => [
                        'role' => 'EXPERT_ANSWERER',
                        'desc' => 'EKSTRAK: question_text. LENGKAPI/GENERATE: options, correct_answer, essay_guidelines. KOSONGKAN: score (set 0), difficulty (set "medium").',
                    ],
                    'scores_only' => [
                        'role' => 'ASSESSMENT_GRADER',
                        'desc' => 'EKSTRAK: question_text. TENTUKAN: score (1-10), difficulty. KOSONGKAN: options (set []), correct_answer (set null), essay_guidelines (set null).',
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
                2. PRIORITAS UTAMA: Jika teks input memiliki kunci jawaban (misal: 'Jawaban: A' atau 'Kunci: B'), Anda WAJIB mengekstraknya secara akurat tanpa mengubahnya. Jangan mencoba menjadikannya jawaban lain meskipun Anda merasa itu salah. Jadilah pengekstraksi yang setia.
                3. Gunakan field 'type' (multiple_choice, essay), 'difficulty' (easy, medium, hard), dan 'score'.
                
                STRUKTUR JSON:
                [
                  {
                    \"question_text\": \"...\",
                    \"type\": \"...\",
                    \"score\": integer,
                    \"difficulty\": \"...\",
                    \"options\": [{\"option\": \"A\", \"text\": \"...\", \"value\": 1}],
                    \"correct_answer\": \"A\",
                    \"essay_guidelines\": \"...\"
                  }
                ]

                PENTING: Selalu patuhi instruksi 'Mode' di atas. Jika mode adalah 'questions_only', field selain soal (options, correct_answer, essay_guidelines, score) WAJIB kosong (set [], 0, atau null).";

                try {
                    $response = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}", [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.1,
                            'response_mime_type' => 'application/json',
                        ],
                    ]);

                    if ($response->failed()) {
                        throw new \Exception('Gagal menghubungi Gemini API: ' . ($response->json()['error']['message'] ?? 'Unknown Error'));
                    }

                    $result = $response->json();
                    $responseText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '[]';
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

                    // Logika Penggabungan (Merge vs Update)
                    if (in_array($mode, ['scores_only', 'answers_only']) && !empty($currentQuestions) && count($questions) >= count($currentQuestions)) {
                         // Mode Update: Ganti semua dengan hasil dari AI
                         $set('questions', $questions);
                    } else {
                         // Mode Import: Tambahkan ke yang sudah ada
                         $set('questions', array_merge($currentQuestions, $questions));
                    }

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
