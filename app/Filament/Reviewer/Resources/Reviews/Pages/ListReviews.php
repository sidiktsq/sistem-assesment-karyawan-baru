<?php

namespace App\Filament\Reviewer\Resources\Reviews\Pages;

use App\Filament\Reviewer\Resources\Reviews\ReviewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make()
                ->label('Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success'),
            \Filament\Actions\Action::make('exportPdf')
                ->label('PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $records = \App\Models\Review::with(['candidateAssessment.candidate', 'reviewer'])->get();
                    
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.reviews-pdf', [
                        'records' => $records,
                    ]);
                    
                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->stream();
                    }, 'final-reviews-' . date('Y-m-d') . '.pdf');
                }),
            CreateAction::make(),
        ];
    }
}
