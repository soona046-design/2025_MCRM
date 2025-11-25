<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LeadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $visit = Visit::inRandomOrder()->first();

        return [
            'primary_phone' => fake()->e164PhoneNumber(),
            'email_hash' => hash('sha256', fake()->unique()->safeEmail()), // Unique email hash
            'name' => fake()->name(),
            'consent_flags' => json_encode(['email_marketing' => fake()->boolean(), 'sms_marketing' => fake()->boolean()]),
            'source_visit_id' => $visit ? $visit->visit_id : null,
            'status' => fake()->randomElement(['new', 'qualified', 'converted', 'rejected']),
            'score' => fake()->numberBetween(0, 100),
        ];
    }
}
