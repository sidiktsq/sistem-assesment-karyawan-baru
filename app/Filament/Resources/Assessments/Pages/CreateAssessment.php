<?php

namespace App\Filament\Resources\Assessments\Pages;

use App\Filament\Resources\Assessments\AssessmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAssessment extends CreateRecord
{
    protected static string $resource = AssessmentResource::class;

    /**
     * Hook yang dipanggil setiap kali ada perubahan pada data form.
     * Kita simpan ke session agar tidak hilang saat refresh.
     */
    public function updated($property): void
    {
        // Removed parent call as it doesn't exist in Filament's CreateRecord

        if (str_starts_with($property, 'data.')) {
            session()->put('assessment_create_data', $this->data);
        }
    }

    /**
     * Hook yang dipanggil SETELAH form diisi dengan data default.
     * Jika ada data di session, kita ambil untuk memulihkan state.
     */
    protected function afterFill(): void
    {
        if (session()->has('assessment_create_data')) {
            $this->form->fill(session()->get('assessment_create_data'));
        }
    }

    /**
     * Hook yang dipanggil SETELAH record berhasil dibuat.
     * Kita bersihkan session agar tidak mengganggu pembuatan data baru berikutnya.
     */
    protected function afterCreate(): void
    {
        session()->forget('assessment_create_data');
    }
}
