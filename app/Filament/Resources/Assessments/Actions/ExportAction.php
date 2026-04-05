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
    
    private static function exportExcel(Assessment $assessment, $candidateAssessments, array $include): BinaryFileResponse
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
    
    private static function exportPdf(Assessment $assessment, $candidateAssessments, array $include): BinaryFileResponse
    {
        // Implementation would use DomPDF package
        $filename = "assessment_report_{$assessment->id}_" . date('Y-m-d_H-i-s') . ".pdf";
        
        return response()->streamDownload(function () use ($assessment, $candidateAssessments) {
            echo "<h1>Laporan Assessment: {$assessment->title}</h1>";
            echo "<table border='1'>";
            echo "<tr><th>Nama</th><th>Status</th><th>Skor</th></tr>";
            
            foreach ($candidateAssessments as $ca) {
                echo "<tr>";
                echo "<td>{$ca->candidate->name}</td>";
                echo "<td>{$ca->status}</td>";
                echo "<td>{$ca->percentage}%</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
    
    private static function exportCsv(Assessment $assessment, $candidateAssessments, array $include): BinaryFileResponse
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
