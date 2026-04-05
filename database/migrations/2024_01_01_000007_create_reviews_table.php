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
