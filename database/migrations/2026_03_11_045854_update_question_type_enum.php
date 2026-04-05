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
        Schema::table('questions', function (Blueprint $table) {
            $table->enum('type', ['multiple_choice', 'essay', 'personality', 'true_false', 'short_answer'])
                ->default('multiple_choice')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->enum('type', ['multiple_choice', 'essay', 'personality'])
                ->default('multiple_choice')
                ->change();
        });
    }
};
