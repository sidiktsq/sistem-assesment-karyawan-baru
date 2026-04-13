<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adding new statuses to candidate_assessments table
        DB::statement("ALTER TABLE candidate_assessments MODIFY COLUMN status ENUM(
            'scheduled',
            'ongoing',
            'completed',
            'expired',
            'reviewed',
            'approved',
            'rejected',
            'probation',
            'pending'
        ) DEFAULT 'scheduled'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE candidate_assessments MODIFY COLUMN status ENUM(
            'scheduled',
            'ongoing',
            'completed',
            'expired',
            'reviewed'
        ) DEFAULT 'scheduled'");
    }
};
