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
        Schema::create('candidate_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('assessment_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users');
            
            // Schedule
            $table->datetime('scheduled_at');
            $table->datetime('deadline');
            
            // Execution
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->enum('status', [
                'scheduled',
                'ongoing',
                'completed',
                'expired',
                'reviewed'
            ])->default('scheduled');
            
            // Results
            $table->integer('total_score')->nullable();
            $table->integer('percentage')->nullable();
            $table->enum('result', ['pass', 'fail', 'pending'])->default('pending');
            
            // Token for access
            $table->string('access_token', 64)->unique()->nullable();
            $table->timestamp('token_expires_at')->nullable();
            
            // Tracking
            $table->json('metadata')->nullable()->comment('Browser, IP, etc');
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'scheduled_at']);
            $table->index(['candidate_id', 'assessment_id']);
            $table->index('access_token');
            $table->index('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_assessments');
    }
};
