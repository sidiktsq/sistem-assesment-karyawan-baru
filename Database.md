# SISTEM ASESMEN KARYAWAN BARU
## Laravel Migration Tables (Lengkap)

---

## DAFTAR MIGRATION

| No | Nama File | Tabel | Fungsi |
|----|-----------|-------|--------|
| 1 | `2014_10_12_000000_create_users_table.php` | `users` | Data admin & reviewer |
| 2 | `2014_10_12_100000_create_password_reset_tokens_table.php` | `password_reset_tokens` | Reset password |
| 3 | `2019_12_14_000001_create_personal_access_tokens_table.php` | `personal_access_tokens` | Token API (Sanctum) |
| 4 | `2024_01_01_000001_create_permission_tables.php` | `roles`, `permissions`, dll | Spatie Permission |
| 5 | `2024_01_01_000002_create_candidates_table.php` | `candidates` | Data kandidat |
| 6 | `2024_01_01_000003_create_assessments_table.php` | `assessments` | Paket ujian |
| 7 | `2024_01_01_000004_create_questions_table.php` | `questions` | Bank soal |
| 8 | `2024_01_01_000005_create_candidate_assessments_table.php` | `candidate_assessments` | Penugasan ujian |
| 9 | `2024_01_01_000006_create_answers_table.php` | `answers` | Jawaban kandidat |
| 10 | `2024_01_01_000007_create_reviews_table.php` | `reviews` | Review reviewer |
| 11 | `2024_01_01_000008_create_invitations_table.php` | `invitations` | Undangan email |
| 12 | `2024_01_01_000009_create_failed_jobs_table.php` | `failed_jobs` | Queue failed jobs |
| 13 | `2024_01_01_000010_create_jobs_table.php` | `jobs` | Queue jobs |

---

## 1. TABEL USERS

**File:** `database/migrations/2014_10_12_000000_create_users_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('profile_photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

**Kolom:**
- `id` - Primary key
- `name` - Nama lengkap
- `email` - Email (unique)
- `email_verified_at` - Timestamp verifikasi email
- `password` - Password hash
- `department` - Departemen (contoh: IT, HRD, Marketing)
- `position` - Jabatan (contoh: IT Manager, HR Staff)
- `profile_photo` - Foto profil
- `is_active` - Status aktif (bisa dinonaktifkan)
- `remember_token` - Token remember me
- `timestamps` - created_at & updated_at

---

## 2. TABEL PASSWORD RESET TOKENS

**File:** `database/migrations/2014_10_12_100000_create_password_reset_tokens_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
```

**Kolom:**
- `email` - Email user (primary key)
- `token` - Token reset password
- `created_at` - Waktu pembuatan token

---

## 3. TABEL PERSONAL ACCESS TOKENS (SANCTUM)

**File:** `database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
```

**Kolom:**
- `id` - Primary key
- `tokenable_id` & `tokenable_type` - Polymorphic relation ke user
- `name` - Nama token (contoh: "auth-token")
- `token` - Token unik (hash)
- `abilities` - Kemampuan token (contoh: ["*"] untuk semua)
- `last_used_at` - Terakhir digunakan
- `expires_at` - Waktu kadaluarsa
- `timestamps` - created_at & updated_at

---

## 4. TABEL PERMISSIONS (SPATIE)

**File:** `database/migrations/2024_01_01_000001_create_permission_tables.php`

*(Ini adalah file dari package Spatie Permission, dijalankan dengan command:
`php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`)*

**Tabel yang dibuat:**
- `roles` - Daftar role (admin, reviewer)
- `permissions` - Daftar permission
- `role_has_permissions` - Relasi role & permission
- `model_has_roles` - Relasi user & role
- `model_has_permissions` - Relasi user & permission

---

## 5. TABEL CANDIDATES

**File:** `database/migrations/2024_01_01_000002_create_candidates_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('position_applied');
            $table->string('source')->nullable(); // LinkedIn, JobFair, etc
            $table->enum('status', [
                'pending',
                'assessment_scheduled',
                'assessment_ongoing',
                'assessment_completed',
                'reviewed',
                'approved',
                'probation',
                'rejected'
            ])->default('pending');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // CV, documents, etc
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('status');
            $table->index('position_applied');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
```

**Kolom:**
- `id` - Primary key
- `name` - Nama lengkap kandidat
- `email` - Email (unique)
- `phone` - Nomor telepon
- `position_applied` - Posisi yang dilamar
- `source` - Sumber rekrutmen (LinkedIn, JobFair, dll)
- `status` - Status kandidat (enum)
- `notes` - Catatan internal
- `metadata` - JSON untuk data tambahan (CV, dokumen)
- `created_by` - Foreign key ke users (admin yang input)
- `timestamps` - created_at & updated_at

**Index:**
- `status` - Untuk filtering by status
- `position_applied` - Untuk filtering by posisi
- `created_at` - Untuk sorting by tanggal

---

## 6. TABEL ASSESSMENTS

**File:** `database/migrations/2024_01_01_000003_create_assessments_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->default(60);
            $table->integer('passing_score')->nullable()->comment('Minimum score to pass (percentage)');
            $table->enum('type', [
                'programming',
                'marketing',
                'finance',
                'hr',
                'general',
                'personality',
                'technical'
            ])->default('general');
            $table->json('sections')->nullable()->comment('Array of sections with names and durations');
            $table->boolean('is_active')->default(true);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('show_result_immediately')->default(true);
            $table->integer('max_attempts')->default(1);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
```

**Kolom:**
- `id` - Primary key
- `title` - Judul assessment
- `description` - Deskripsi
- `duration_minutes` - Durasi total (menit)
- `passing_score` - Nilai minimal kelulusan (0-100)
- `type` - Tipe assessment
- `sections` - JSON array: [{"name": "PHP", "duration": 30, "questions": 10}]
- `is_active` - Status aktif
- `shuffle_questions` - Acak soal atau tidak
- `show_result_immediately` - Tampilkan hasil langsung setelah ujian
- `max_attempts` - Maksimal percobaan
- `created_by` - Foreign key ke users (pembuat)
- `timestamps` - created_at & updated_at

**Index:**
- `type` - Filter by type
- `is_active` - Filter active assessments

**Contoh JSON sections:**
```json
[
    {"name": "PHP Basics", "duration": 20, "description": "Basic PHP questions"},
    {"name": "Laravel", "duration": 30, "description": "Laravel framework"},
    {"name": "Database", "duration": 20, "description": "SQL & Database design"}
]
```

---

## 7. TABEL QUESTIONS

**File:** `database/migrations/2024_01_01_000004_create_questions_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['multiple_choice', 'essay', 'personality']);
            $table->string('section')->default('general');
            $table->text('question_text');
            $table->json('options')->nullable()->comment('For multiple choice: array of options with letters');
            $table->string('correct_answer')->nullable()->comment('For multiple choice: option letter');
            $table->integer('score')->default(1);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->text('explanation')->nullable()->comment('Explanation after answering');
            $table->json('tags')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['assessment_id', 'section']);
            $table->index(['assessment_id', 'type']);
            $table->index('difficulty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
```

**Kolom:**
- `id` - Primary key
- `assessment_id` - Foreign key ke assessments (cascade delete)
- `type` - Tipe soal
- `section` - Bagian/kelompok soal
- `question_text` - Teks soal
- `options` - JSON untuk pilihan ganda atau personality
- `correct_answer` - Jawaban benar (untuk multiple choice)
- `score` - Bobot nilai
- `difficulty` - Tingkat kesulitan
- `explanation` - Pembahasan
- `tags` - JSON array untuk tagging
- `order` - Urutan soal
- `is_active` - Status aktif
- `timestamps` - created_at & updated_at

**Index:**
- `assessment_id + section` - Untuk grouping by section
- `assessment_id + type` - Untuk filter by type
- `difficulty` - Untuk filter kesulitan

**Contoh JSON options (Multiple Choice):**
```json
[
    {"option": "A", "text": "Personal Home Page"},
    {"option": "B", "text": "PHP: Hypertext Preprocessor"},
    {"option": "C", "text": "Private Hosting Protocol"},
    {"option": "D", "text": "Public Hypertext Processor"}
]
```

**Contoh JSON options (Personality - Likert Scale):**
```json
[
    {"option": "1", "text": "Strongly Disagree", "value": 1},
    {"option": "2", "text": "Disagree", "value": 2},
    {"option": "3", "text": "Neutral", "value": 3},
    {"option": "4", "text": "Agree", "value": 4},
    {"option": "5", "text": "Strongly Agree", "value": 5}
]
```

---

## 8. TABEL CANDIDATE ASSESSMENTS

**File:** `database/migrations/2024_01_01_000005_create_candidate_assessments_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('candidate_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('assessment_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users');
            
            // Schedule
            $table->datetime('scheduled_at');
            $table->datetime('deadline');
            
            // Execution
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->enum('status', [
                'scheduled',
                'ongoing',
                'completed',
                'expired',
                'reviewed'
            ])->default('scheduled');
            
            // Results
            $table->integer('total_score')->nullable();
            $table->integer('percentage')->nullable();
            $table->enum('result', ['pass', 'fail', 'pending'])->default('pending');
            
            // Token for access
            $table->string('access_token', 64)->unique()->nullable();
            $table->timestamp('token_expires_at')->nullable();
            
            // Tracking
            $table->json('metadata')->nullable()->comment('Browser, IP, etc');
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'scheduled_at']);
            $table->index(['candidate_id', 'assessment_id']);
            $table->index('access_token');
            $table->index('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_assessments');
    }
};
```

**Kolom:**
- `id` - Primary key
- `candidate_id` - Foreign key ke candidates
- `assessment_id` - Foreign key ke assessments
- `assigned_by` - Admin yang assign
- `scheduled_at` - Jadwal mulai
- `deadline` - Batas waktu
- `started_at` - Waktu mulai ujian
- `completed_at` - Waktu selesai ujian
- `status` - Status ujian
- `total_score` - Total nilai
- `percentage` - Persentase nilai
- `result` - Hasil (pass/fail/pending)
- `access_token` - Token unik untuk akses ujian
- `token_expires_at` - Kadaluarsa token
- `metadata` - Data tambahan (IP, browser)
- `timestamps` - created_at & updated_at

**Index:**
- `status + scheduled_at` - Untuk monitoring jadwal
- `candidate_id + assessment_id` - Unique combination
- `access_token` - Quick lookup by token
- `deadline` - Untuk cek expired

---

## 9. TABEL ANSWERS

**File:** `database/migrations/2024_01_01_000006_create_answers_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_assessment_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            
            // Answer content
            $table->text('answer')->nullable();
            
            // Auto grading (for multiple choice & personality)
            $table->boolean('is_correct')->nullable();
            
            // Manual grading (for essay)
            $table->integer('score_obtained')->nullable();
            $table->text('feedback')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->datetime('reviewed_at')->nullable();
            
            // Time tracking
            $table->integer('time_spent_seconds')->nullable();
            
            // Flag for essay that needs review
            $table->boolean('needs_review')->default(false);
            
            $table->timestamps();
            
            // Unique constraint: one answer per question per attempt
            $table->unique(['candidate_assessment_id', 'question_id'], 'unique_answer');
            
            // Index for pending reviews
            $table->index(['needs_review', 'reviewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
```

**Kolom:**
- `id` - Primary key
- `candidate_assessment_id` - Foreign key ke candidate_assessments
- `question_id` - Foreign key ke questions
- `answer` - Jawaban (teks untuk esai, option letter untuk PG)
- `is_correct` - Flag benar/salah (PG)
- `score_obtained` - Nilai yang diperoleh
- `feedback` - Feedback reviewer
- `reviewed_by` - Reviewer yang menilai
- `reviewed_at` - Waktu review
- `time_spent_seconds` - Waktu pengerjaan soal
- `needs_review` - Flag perlu review (untuk esai)
- `timestamps` - created_at & updated_at

**Index:**
- `unique_answer` - Mencegah duplikasi jawaban
- `needs_review + reviewed_at` - Untuk antrian review

---

## 10. TABEL REVIEWS

**File:** `database/migrations/2024_01_01_000007_create_reviews_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_assessment_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users');
            
            // Review result
            $table->enum('recommendation', ['approved', 'probation', 'rejected']);
            $table->text('notes')->nullable();
            
            // Scores per aspect (JSON)
            $table->json('aspect_scores')->nullable()->comment('Scores for technical, communication, etc');
            
            // Final decision
            $table->datetime('reviewed_at');
            $table->timestamps();
            
            $table->index(['candidate_assessment_id', 'reviewer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
```

**Kolom:**
- `id` - Primary key
- `candidate_assessment_id` - Foreign key ke candidate_assessments
- `reviewer_id` - Reviewer yang melakukan review
- `recommendation` - Rekomendasi (approved/probation/rejected)
- `notes` - Catatan reviewer
- `aspect_scores` - JSON nilai per aspek
- `reviewed_at` - Waktu review
- `timestamps` - created_at & updated_at

**Contoh JSON aspect_scores:**
```json
{
    "technical": 85,
    "communication": 70,
    "problem_solving": 90,
    "team_work": 75
}
```

---

## 11. TABEL INVITATIONS

**File:** `database/migrations/2024_01_01_000008_create_invitations_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_assessment_id')->constrained()->onDelete('cascade');
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->datetime('sent_at');
            $table->datetime('expires_at');
            $table->datetime('accepted_at')->nullable();
            $table->enum('status', ['pending', 'accepted', 'expired'])->default('pending');
            $table->integer('reminder_count')->default(0);
            $table->datetime('last_reminder_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'expires_at']);
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
```

**Kolom:**
- `id` - Primary key
- `candidate_assessment_id` - Foreign key ke candidate_assessments
- `email` - Email tujuan
- `token` - Token unik untuk link undangan
- `sent_at` - Waktu pengiriman
- `expires_at` - Waktu kadaluarsa
- `accepted_at` - Waktu diterima (klik link)
- `status` - Status undangan
- `reminder_count` - Jumlah reminder yang sudah dikirim
- `last_reminder_at` - Waktu reminder terakhir
- `timestamps` - created_at & updated_at

---

## 12. TABEL FAILED JOBS

**File:** `database/migrations/2024_01_01_000009_create_failed_jobs_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
    }
};
```

**Kolom:**
- `id` - Primary key
- `uuid` - Unique identifier
- `connection` - Nama koneksi queue
- `queue` - Nama queue
- `payload` - Data job
- `exception` - Error exception
- `failed_at` - Waktu gagal

---

## 13. TABEL JOBS

**File:** `database/migrations/2024_01_01_000010_create_jobs_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
```

**Kolom:**
- `id` - Primary key
- `queue` - Nama queue
- `payload` - Data job
- `attempts` - Jumlah percobaan
- `reserved_at` - Waktu direserved
- `available_at` - Waktu tersedia
- `created_at` - Waktu dibuat

---

## RINGKASAN RELASI

```
users
  ├── hasMany candidates (created_by)
  ├── hasMany assessments (created_by)
  ├── hasMany reviews (reviewer_id)
  └── hasMany candidate_assessments (assigned_by)

candidates
  └── hasMany candidate_assessments

assessments
  ├── hasMany questions
  └── hasMany candidate_assessments

questions
  └── belongsTo assessment
  └── hasMany answers

candidate_assessments
  ├── belongsTo candidate
  ├── belongsTo assessment
  ├── hasMany answers
  ├── hasMany reviews
  └── hasOne invitation

answers
  ├── belongsTo candidate_assessment
  ├── belongsTo question
  └── belongsTo reviewer (reviewed_by)

reviews
  ├── belongsTo candidate_assessment
  └── belongsTo reviewer

invitations
  └── belongsTo candidate_assessment
```

---

## CARA MENJALANKAN MIGRATION

```bash
# Run all migrations
php artisan migrate

# Run specific migration with seeders
php artisan migrate --seed

# Refresh database (drop all tables and re-run)
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Check migration status
php artisan migrate:status
```
