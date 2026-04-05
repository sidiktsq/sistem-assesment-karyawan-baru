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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->default(60);
            $table->integer('passing_score')->nullable()->comment('Minimum score to pass (percentage)');
            $table->enum('type', [
                'programming',
                'marketing',
                'finance',
                'hr',
                'general',
                'personality',
                'technical'
            ])->default('general');
            $table->json('sections')->nullable()->comment('Array of sections with names and durations');
            $table->boolean('is_active')->default(true);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('show_result_immediately')->default(true);
            $table->integer('max_attempts')->default(1);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
