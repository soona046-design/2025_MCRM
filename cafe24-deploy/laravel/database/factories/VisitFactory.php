<?php

namespace Database\Factories;

use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VisitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Visit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'visit_id' => Str::uuid(),
            'client_id' => Str::random(20),
            'session_id' => Str::random(10),
            'utm_source' => \fake()->randomElement(['네이버', 'Google Ads', 'Facebook Ads', 'Instagram']),
            'utm_medium' => \fake()->randomElement(['cpc', 'viral', 'display', 'organic']),
            'utm_campaign' => \fake()->word(),
            'utm_content' => \fake()->word(),
            'utm_term' => \fake()->word(),
            'referrer' => \fake()->url(),
            'landing_path' => \fake()->url(),
            'first_seen_at' => \fake()->dateTimeBetween('-1 month', 'now'),
            'ip' => \fake()->ipv4(),
            'ua' => \fake()->userAgent(),
        ];
    }
}
