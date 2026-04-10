<?php

namespace App\Filament\Resources\Assessments\Actions;

use App\Models\Assessment;
use App\Models\CandidateAssessment;
use Filament\Actions\Action;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportAction
{
    public static function make(): Action
    {
        return Action::make('export')
            ->label('Export Laporan')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('info')
            ->form([
                \Filament\Forms\Components\Select::make('format')
                    ->label('Format Export')
                    ->required()
                    ->options([
                        'excel' => 'Excel (.xlsx)',
                        'pdf' => 'PDF',
                        'csv' => 'CSV',
                    ])
                    ->default('excel'),
                    
                \Filament\Forms\Components\CheckboxList::make('include')
                    ->label('Include Data')
                    ->options([
                        'candidates' => 'Data Kandidat',
                        'scores' => 'Nilai & Hasil',
                        'answers' => 'Detail Jawaban',
                        'reviews' => 'Hasil Review',
                    ])
                    ->default(['candidates', 'scores'])
                    ->columns(2),
            ])
            ->action(function (array $data, Assessment $record) {
                $format = $data['format'];
                $include = $data['include'];
                
                // Get assessment data
                $candidateAssessments = CandidateAssessment::with(['candidate', 'answers.question', 'reviews.reviewer'])
                    ->where('assessment_id', $record->id)
                    ->get();
                
                switch ($format) {
                    case 'excel':
                        return self::exportExcel($record, $candidateAssessments, $include);
                    case 'pdf':
                        return self::exportPdf($record, $candidateAssessments, $include);
                    case 'csv':
                        return self::exportCsv($record, $candidateAssessments, $include);
                }
                
                \Filament\Notifications\Notification::make()
                    ->title('Export Berhasil')
                    ->body('Laporan assessment telah diexport')
                    ->success()
                    ->send();
            })
            ->modalHeading('Export Laporan Assessment')
            ->modalDescription('Pilih format dan data yang ingin diexport')
            ->modalSubmitActionLabel('Export');
    }
    
    private static function exportExcel(Assessment $assessment, $candidateAssessments, array $include)
    {
        $filename = "assessment_report_{$assessment->id}_" . date('Y-m-d_H-i-s') . ".xlsx";
        
        // Implementation would use Laravel Excel package
        // For now, return a simple CSV as placeholder
        return response()->streamDownload(function () use ($candidateAssessments, $include) {
            $handle = fopen('php://output', 'w');
            
            // Header
            fputcsv($handle, ['Nama Kandidat', 'Email', 'Status', 'Skor', 'Persentase', 'Tanggal Selesai']);
            
            foreach ($candidateAssessments as $ca) {
                fputcsv($handle, [
                    $ca->candidate->name,
                    $ca->candidate->email,
                    $ca->status,
                    $ca->total_score ?? 0,
                    $ca->percentage ?? 0,
                    $ca->completed_at?->format('Y-m-d H:i:s') ?? '-'
                ]);
            }
            
            fclose($handle);
        }, $filename);
    }
    
    private static function exportPdf(Assessment $assessment, $candidateAssessments, array $include)
    {
        $filename = "assessment_report_{$assessment->id}_" . date('Y-m-d_H-i-s') . ".pdf";
        
        $fpdf = new \Codedge\Fpdf\Fpdf\Fpdf();
        $fpdf->AddPage();
        $fpdf->SetFont('Arial', 'B', 16);
        $fpdf->Cell(190, 10, 'Laporan Assessment: ' . $assessment->title, 0, 1, 'C');
        $fpdf->SetFont('Arial', '', 10);
        $fpdf->Cell(190, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
        $fpdf->Ln(10);

        // Header Tabel
        $fpdf->SetFont('Arial', 'B', 10);
        $fpdf->SetFillColor(230, 230, 230);
        $fpdf->Cell(60, 10, 'Nama Kandidat', 1, 0, 'C', true);
        $fpdf->Cell(50, 10, 'Status', 1, 0, 'C', true);
        $fpdf->Cell(40, 10, 'Skor', 1, 0, 'C', true);
        $fpdf->Cell(40, 10, 'Persentase', 1, 1, 'C', true);

        // Isi Tabel
        $fpdf->SetFont('Arial', '', 10);
        foreach ($candidateAssessments as $ca) {
            $fpdf->Cell(60, 10, substr($ca->candidate->name, 0, 30), 1);
            $fpdf->Cell(50, 10, ucfirst($ca->status), 1, 0, 'C');
            $fpdf->Cell(40, 10, ($ca->total_score ?? 0) . ' / ' . ($ca->max_score ?? 0), 1, 0, 'C');
            $fpdf->Cell(40, 10, ($ca->percentage ?? 0) . '%', 1, 1, 'C');
        }

        return response()->streamDownload(
            fn () => print($fpdf->Output('S')),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
    
    private static function exportCsv(Assessment $assessment, $candidateAssessments, array $include)
    {
        $filename = "assessment_report_{$assessment->id}_" . date('Y-m-d_H-i-s') . ".csv";
        
        return response()->streamDownload(function () use ($candidateAssessments, $include) {
            $handle = fopen('php://output', 'w');
            
            // Header
            fputcsv($handle, ['Nama Kandidat', 'Email', 'Status', 'Skor', 'Persentase', 'Tanggal Selesai']);
            
            foreach ($candidateAssessments as $ca) {
                fputcsv($handle, [
                    $ca->candidate->name,
                    $ca->candidate->email,
                    $ca->status,
                    $ca->total_score ?? 0,
                    $ca->percentage ?? 0,
                    $ca->completed_at?->format('Y-m-d H:i:s') ?? '-'
                ]);
            }
            
            fclose($handle);
        }, $filename);
    }
}
