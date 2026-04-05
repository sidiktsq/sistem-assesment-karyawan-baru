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
            $table->text('expected_answer')->nullable()->after('correct_answer');
            $table->string('true_option')->default('True')->nullable()->after('expected_answer');
            $table->string('false_option')->default('False')->nullable()->after('true_option');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['expected_answer', 'true_option', 'false_option']);
        });
    }
};
