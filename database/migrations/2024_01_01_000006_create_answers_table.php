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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_assessment_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            
            // Answer content
            $table->text('answer')->nullable();
            
            // Auto grading (for multiple choice & personality)
            $table->boolean('is_correct')->nullable();
            
            // Manual grading (for essay)
            $table->integer('score_obtained')->nullable();
            $table->text('feedback')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->datetime('reviewed_at')->nullable();
            
            // Time tracking
            $table->integer('time_spent_seconds')->nullable();
            
            // Flag for essay that needs review
            $table->boolean('needs_review')->default(false);
            
            $table->timestamps();
            
            // Unique constraint: one answer per question per attempt
            $table->unique(['candidate_assessment_id', 'question_id'], 'unique_answer');
            
            // Index for pending reviews
            $table->index(['needs_review', 'reviewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
