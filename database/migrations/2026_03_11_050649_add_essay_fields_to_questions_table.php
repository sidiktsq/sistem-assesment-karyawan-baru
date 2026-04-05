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
            $table->text('essay_guidelines')->nullable()->after('false_option');
            $table->integer('min_words')->default(0)->nullable()->after('essay_guidelines');
            $table->integer('max_words')->default(1000)->nullable()->after('min_words');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['essay_guidelines', 'min_words', 'max_words']);
        });
    }
};
