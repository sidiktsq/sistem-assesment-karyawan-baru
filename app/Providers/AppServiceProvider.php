<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Models\Candidate;
use App\Models\Assessment;
use App\Models\CandidateAssessment;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for older MySQL versions
        Schema::defaultStringLength(191);

        // Share global data with views
        View::composer(['layouts.candidate', 'exam.*'], function ($view) {
            $view->with([
                'totalCandidates' => Candidate::count(),
                'totalAssessments' => Assessment::count(),
                'activeAssessments' => CandidateAssessment::where('status', 'ongoing')->count(),
            ]);
        });
    }
}
