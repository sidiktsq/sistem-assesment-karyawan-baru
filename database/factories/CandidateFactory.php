<?php

namespace Database\Factories;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Candidate>
 */
class CandidateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'position_applied' => fake()->jobTitle(),
            'source' => fake()->randomElement(['LinkedIn', 'Job Portal', 'Referral', 'Company Website']),
            'status' => fake()->randomElement(['pending', 'assessment_scheduled', 'assessment_ongoing', 'assessment_completed', 'reviewed', 'approved', 'probation', 'rejected']),
            'notes' => fake()->sentence(),
            'created_by' => 1,
        ];
    }
}
