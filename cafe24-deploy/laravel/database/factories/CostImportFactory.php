<?php

namespace Database\Factories;

use App\Models\CostImport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CostImport>
 */
class CostImportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CostImport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'platform' => fake()->randomElement(['네이버', '구글', '메타']), // channel 대신 platform 사용
            'campaign_code' => fake()->word(), // campaign 대신 campaign_code 사용
            'cost' => fake()->numberBetween(1000, 500000),
            'date' => fake()->date(),
        ];
    }
}
