<?php

// Simulate the logic in SmartImportAction.php
function mock_smart_import($mode, $content) {
    echo "Testing Mode: $mode\n";
    echo "Content: ".substr($content, 0, 50)."...\n";
    
    $modeInstructions = match($mode) {
        'everything' => "TUGAS: Ekstrak semua data (soal, pilihan, kunci jawaban, dan skor) dari teks. Jika skor tidak ada, buatkan skor yang logis (1-10).",
        'questions_only' => "TUGAS: Ekstrak atau buatkan soal saja. Kosongkan 'correct_answer' dan set 'options' dengan teks kosong jika memang hanya soal saja yang diinginkan.",
        'answers_only' => "TUGAS: Temukan soal dalam teks, dan GENERATE kunci jawaban serta pilihan jawaban yang benar (jika belum ada). Fokus pada keakuratan jawaban.",
        'scores_only' => "TUGAS: Analisis tingkat kesulitan soal dalam teks dan tentukan 'score' (1-10) serta 'difficulty' (easy, medium, hard).",
        default => "TUGAS: Ekstrak soal secara lengkap.",
    };

    $prompt = "Tujuan: Konversi teks berikut menjadi format JSON soal assessment.
    Mode: $mode
    $modeInstructions

    ATURAN FORMAT:
    1. Output HARUS berupa array JSON valid.
    2. JANGAN sertakan teks tambahan apa pun sebelum atau sesudah JSON block.
    3. Field 'type' harus 'multiple_choice' atau 'essay'.
    4. Field 'difficulty' harus 'easy', 'medium', atau 'hard'.
    5. Field 'section' bawaan adalah 'general' jika tidak terdeteksi.

    STRUKTUR JSON YANG DIHARAPKAN:
    [
      {
        \"question_text\": \"Teks soal\",
        \"type\": \"multiple_choice\" atau \"essay\",
        \"section\": \"Nama Section (jika terdeteksi)\",
        \"score\": integer (1-10),
        \"difficulty\": \"easy\", \"medium\", atau \"hard\",
        \"options\": [
           {\"option\": \"A\", \"text\": \"Teks opsi\", \"value\": 1 atau 0},
           {\"option\": \"B\", \"text\": \"Teks opsi\", \"value\": 0}
        ],
        \"correct_answer\": \"Huruf opsi benar (misal: A)\",
        \"essay_guidelines\": \"Panduan penilaian jika essay\",
        \"min_words\": 0,
        \"max_words\": 1000
      }
    ]

    TEKS INPUT:
    " . $content;

    echo "PROMPT GENERATED (First 200 chars):\n" . substr($prompt, 0, 200) . "...\n\n";
}

mock_smart_import('everything', '1. Apa itu PHP?');
mock_smart_import('answers_only', 'Apa kepanjangan dari CSS?');
mock_smart_import('questions_only', 'Dasar-Dasar HTML');
mock_smart_import('scores_only', '1. Jelaskan cara kerja DNS.');
