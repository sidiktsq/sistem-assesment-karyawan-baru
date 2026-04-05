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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('position_applied');
            $table->string('source')->nullable(); // LinkedIn, JobFair, etc
            $table->enum('status', [
                'pending',
                'assessment_scheduled',
                'assessment_ongoing',
                'assessment_completed',
                'reviewed',
                'approved',
                'probation',
                'rejected',
                'assessment_expired'
            ])->default('pending');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // CV, documents, etc
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('status');
            $table->index('position_applied');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
