<?php

namespace App\Filament\Resources\CandidateAssessments\Pages;

use App\Filament\Resources\CandidateAssessments\CandidateAssessmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
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
                    $pdf = Pdf::loadView('exports.candidate-assessments-pdf', [
                        'records' => $records,
                    ]);
                    return response()->streamDownload(fn () => print($pdf->output()), 'candidate-assessments-' . date('Y-m-d') . '.pdf');
                }),
            CreateAction::make()
                ->label('New candidate assessment'),
        ];
    }
}
