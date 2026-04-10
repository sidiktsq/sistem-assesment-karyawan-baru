<?php

namespace App\Filament\Resources\CandidateAssessments\Pages;

use App\Filament\Resources\CandidateAssessments\CandidateAssessmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Actions\Action;
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Support\Collection;
use pxlrbt\FilamentExcel\Columns\Column as ExcelColumn;

class ListCandidateAssessments extends ListRecords
{
    protected static string $resource = CandidateAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Excel')
                ->exports([
                    ExcelExport::make()
                        ->withColumns([
                            ExcelColumn::make('candidate.name')->heading('Candidate'),
                            ExcelColumn::make('assessment.title')->heading('Assessment'),
                            ExcelColumn::make('assigner.name')->heading('Assigned By'),
                            ExcelColumn::make('scheduled_at')->heading('Scheduled At'),
                            ExcelColumn::make('deadline')->heading('Deadline'),
                            ExcelColumn::make('status')->heading('Status'),
                            ExcelColumn::make('total_score')->heading('Score'),
                            ExcelColumn::make('percentage')->heading('Percentage'),
                            ExcelColumn::make('result')->heading('Result'),
                        ])
                        ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d')),
                ])
                ->color('success')
                ->icon('heroicon-o-document-arrow-down'),
            Action::make('exportPdf')
                ->label('PDF')
                ->color('danger')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    $records = $this->getFilteredTableQuery()->get();
                    
                    $fpdf = new Fpdf();
                    $fpdf->AddPage();
                    $fpdf->SetFont('Arial', 'B', 16);
                    $fpdf->Cell(190, 10, 'Candidate Assessments Report', 0, 1, 'C');
                    $fpdf->SetFont('Arial', '', 10);
                    $fpdf->Cell(190, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
                    $fpdf->Ln(5);

                    // Table Header
                    $fpdf->SetFont('Arial', 'B', 9);
                    $fpdf->SetFillColor(240, 240, 240);
                    $fpdf->Cell(35, 10, 'Candidate', 1, 0, 'C', true);
                    $fpdf->Cell(35, 10, 'Assessment', 1, 0, 'C', true);
                    $fpdf->Cell(25, 10, 'Scheduled', 1, 0, 'C', true);
                    $fpdf->Cell(25, 10, 'Deadline', 1, 0, 'C', true);
                    $fpdf->Cell(20, 10, 'Status', 1, 0, 'C', true);
                    $fpdf->Cell(30, 10, 'Score', 1, 0, 'C', true);
                    $fpdf->Cell(20, 10, 'Result', 1, 1, 'C', true);

                    // Table Body
                    $fpdf->SetFont('Arial', '', 8);
                    foreach ($records as $record) {
                        $candidate = substr($record->candidate->name ?? 'N/A', 0, 20);
                        $assessment = substr($record->assessment->title ?? 'N/A', 0, 20);
                        $scheduled = $record->scheduled_at ? $record->scheduled_at->format('Y-m-d H:i') : '-';
                        $deadline = $record->deadline ? $record->deadline->format('Y-m-d H:i') : '-';
                        $status = ucfirst($record->status);
                        
                        $scoreText = '-';
                        if ($record->total_score !== null) {
                            $scoreText = "{$record->total_score}/{$record->max_score} ({$record->percentage}%)";
                        }
                        $result = ucfirst($record->result ?? 'Pending');

                        $fpdf->Cell(35, 8, $candidate, 1);
                        $fpdf->Cell(35, 8, $assessment, 1);
                        $fpdf->Cell(25, 8, $scheduled, 1);
                        $fpdf->Cell(25, 8, $deadline, 1);
                        $fpdf->Cell(20, 8, $status, 1);
                        $fpdf->Cell(30, 8, $scoreText, 1);
                        $fpdf->Cell(20, 8, $result, 1, 1);
                    }

                    return response()->streamDownload(
                        fn () => print($fpdf->Output('S')), 
                        'candidate-assessments-' . date('Y-m-d') . '.pdf',
                        ['Content-Type' => 'application/pdf']
                    );
                }),
            CreateAction::make()
                ->label('New candidate assessment'),
        ];
    }
}
