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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_assessment_id')->constrained()->onDelete('cascade');
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->datetime('sent_at');
            $table->datetime('expires_at');
            $table->datetime('accepted_at')->nullable();
            $table->enum('status', ['pending', 'accepted', 'expired'])->default('pending');
            $table->integer('reminder_count')->default(0);
            $table->datetime('last_reminder_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'expires_at']);
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
