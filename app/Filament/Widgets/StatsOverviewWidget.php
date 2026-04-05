<?php

namespace App\Filament\Widgets;

use App\Models\Candidate;
use App\Models\Assessment;
use App\Models\CandidateAssessment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Kandidat', Candidate::count())
                ->description('Jumlah semua kandidat')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
                
            Stat::make('Total Assessment', Assessment::count())
                ->description('Jumlah paket ujian')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('success'),
                
            Stat::make('Ujian Aktif', CandidateAssessment::where('status', 'ongoing')->count())
                ->description('Sedang berlangsung')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Selesai Dinilai', CandidateAssessment::where('status', 'reviewed')->count())
                ->description('Sudah direview')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
        ];
    }
}
