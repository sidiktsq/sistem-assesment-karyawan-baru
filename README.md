# SISTEM ASESMEN KARYAWAN BARU
## Fullstack dengan Laravel + FilamentPHP
### (Inti / Ringkasan)

---

## 1. KONSEP BISNIS

### 1.1. Gambaran Umum
Sistem untuk melakukan asesmen terhadap calon karyawan baru. HRD bisa membuat bank soal, menjadwalkan ujian, dan reviewer (kepala departemen) bisa menilai hasil ujian kandidat.

### 1.2. 3 Aktor Utama

| Aktor | Panel | Tugas Utama |
|-------|-------|-------------|
| **Super Admin (HRD)** | Admin Panel | • Kelola kandidat<br>• Kelola bank soal & assessment<br>• Assign assessment ke kandidat<br>• Lihat semua laporan |
| **Reviewer (Dept Head)** | Reviewer Panel | • Lihat kandidat yang sudah selesai ujian<br>• Nilai jawaban esai<br>• Beri rekomendasi (approve/probation/reject) |
| **Kandidat** | Halaman Ujian (Vue) | • Menerima email undangan<br>• Mengikuti ujian online<br>• Lihat hasil (jika diizinkan) |

### 1.3. Alur Bisnis Sederhana

```
HRD (Admin Panel)
    ↓
Input data kandidat
    ↓
Buat assessment & soal
    ↓
Assign assessment ke kandidat
    ↓
[System] Kirim email undangan + link ujian
    ↓
Kandidat buka link → Ikut ujian → Submit
    ↓
[System] Koreksi otomatis (PG & Personality)
    ↓
Reviewer login ke Reviewer Panel
    ↓
Reviewer nilai jawaban esai
    ↓
Reviewer beri rekomendasi
    ↓
HRD lihat laporan akhir
```

---

## 2. STRUKTUR DATABASE (INTI)

### 2.1. Tabel-tabel Utama

| Tabel | Fungsi |
|-------|--------|
| `users` | Admin & reviewer |
| `candidates` | Data kandidat |
| `assessments` | Paket ujian |
| `questions` | Bank soal |
| `candidate_assessments` | Penugasan ujian ke kandidat |
| `answers` | Jawaban kandidat |
| `reviews` | Hasil review dari reviewer |
| `invitations` | Undangan email + token |

### 2.2. Relasi Sederhana

```
candidates ────< candidate_assessments >──── assessments
                      │                              │
                      ├──── answers >─── questions ──┘
                      │
                      └──── reviews
```

---

## 3. FITUR FILAMENT (ADMIN PANEL)

### 3.1. Resources untuk Admin

| Resource | Fungsi |
|----------|--------|
| **CandidateResource** | CRUD kandidat, assign assessment, kirim email |
| **AssessmentResource** | CRUD paket ujian |
| **QuestionResource** | CRUD soal (PG, Esai, Personality) |
| **CandidateAssessmentResource** | Monitoring status ujian kandidat |
| **ReportWidget** | Dashboard statistik |

### 3.2. Fitur Khusus Admin

- **Bulk Assign** - Assign assessment ke banyak kandidat sekaligus
- **Duplicate Assessment** - Duplikasi paket ujian beserta soalnya
- **Export Laporan** - Export ke Excel/PDF
- **Invitation Email** - Kirim email otomatis dengan token

---

## 4. FITUR FILAMENT (REVIEWER PANEL)

### 4.1. Resources untuk Reviewer

| Resource | Fungsi |
|----------|--------|
| **PendingReviewResource** | Lihat kandidat yang perlu dinilai |
| **AnswerResource** | Lihat jawaban esai, beri nilai & feedback |
| **ReviewResource** | Beri rekomendasi akhir |

### 4.2. Fitur Khusus Reviewer

- **Grading Interface** - Interface khusus untuk nilai esai
- **Recommendation System** - Approve/Probation/Reject dengan notes
- **Aspect Scoring** - Nilai per aspek (Technical, Communication, dll)

---

## 5. FRONTEND UJIAN (VUE.JS)

### 5.1. Halaman-halaman

| Halaman | Fungsi |
|---------|--------|
| `Start.vue` | Halaman awal, validasi token |
| `TakeExam.vue` | Halaman utama ujian dengan timer |
| `Result.vue` | Hasil ujian (opsional) |

### 5.2. Fitur Ujian

- **Timer** - Hitung mundur, auto-submit jika habis
- **Auto-save** - Jawaban tersimpan otomatis
- **Navigation** - Navigasi antar soal
- **Question Types** - PG, Esai, Personality (Likert scale)

---

## 6. FITUR OTOMATIS

### 6.1. Auto-grading

| Tipe Soal | Cara Nilai |
|-----------|------------|
| Multiple Choice | Cocokkan dengan `correct_answer` |
| Personality | Simpan skala Likert, analisis nanti |
| Essay | Manual oleh reviewer |

### 6.2. Email Otomatis

| Skenario | Pengirim |
|----------|----------|
| Assign assessment | Kirim link ujian + token |
| Deadline reminder | (Opsional) |
| Hasil sudah direview | (Opsional) |

### 6.3. Token System

- Setiap kandidat dapat token unik saat diassign
- Token expires sesuai deadline
- Token digunakan untuk akses halaman ujian (tanpa login)

---

## 7. STRUKTUR FOLDER FILAMENT

```
app/Filament/
├── Admin/
│   ├── Resources/
│   │   ├── CandidateResource.php
│   │   ├── AssessmentResource.php
│   │   ├── QuestionResource.php
│   │   └── CandidateAssessmentResource.php
│   └── Widgets/
│       ├── StatsOverviewWidget.php
│       └── RecentCandidatesWidget.php
│
└── Reviewer/
    ├── Resources/
    │   ├── PendingReviewResource.php
    │   ├── AnswerResource.php
    │   └── ReviewResource.php
    └── Pages/
        └── GradeEssay.php
```

---

## 8. FITUR UNGGULAN

### 8.1. Untuk HRD (Admin)
✅ Input kandidat cepat  
✅ Buat assessment dengan berbagai tipe soal  
✅ Assign ke banyak kandidat sekaligus  
✅ Lihat dashboard real-time  
✅ Export laporan Excel/PDF  

### 8.2. Untuk Reviewer
✅ Interface grading yang nyaman  
✅ Lihat jawaban esai per kandidat  
✅ Beri nilai + feedback  
✅ Rekomendasi final dengan notes  

### 8.3. Untuk Kandidat
✅ Akses via email (tanpa register)  
✅ Timer otomatis  
✅ Auto-save jawaban  
✅ Bisa lanjutkan jika koneksi putus  

---

## 9. TEKNOLOGI YANG DIGUNAKAN

| Bagian | Teknologi |
|--------|-----------|
| Backend | Laravel 11 |
| Admin Panel | FilamentPHP 3 |
| Frontend Ujian | Vue.js 3 |
| Database | MySQL/PostgreSQL |
| Authentication | Laravel Sanctum |
| Email | Laravel Mail |
| Export | Laravel Excel, DomPDF |
| Role & Permission | Spatie Permission |
