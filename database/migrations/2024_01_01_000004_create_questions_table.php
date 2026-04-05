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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['multiple_choice', 'essay', 'personality']);
            $table->string('section')->default('general');
            $table->text('question_text');
            $table->json('options')->nullable()->comment('For multiple choice: array of options with letters');
            $table->string('correct_answer')->nullable()->comment('For multiple choice: option letter');
            $table->integer('score')->default(1);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->text('explanation')->nullable()->comment('Explanation after answering');
            $table->json('tags')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['assessment_id', 'section']);
            $table->index(['assessment_id', 'type']);
            $table->index('difficulty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
