<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AppointmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Appointment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lead = Lead::inRandomOrder()->first();
        $doctor = User::where('role', '의사')->inRandomOrder()->first(); // 의사 역할 사용자

        return [
            'apt_id' => Str::uuid(), // appointment_id 대신 apt_id 사용
            'lead_id' => $lead ? $lead->lead_id : Lead::factory(),
            'doctor_id' => $doctor ? $doctor->user_id : User::factory(),
            'slot_at' => fake()->dateTimeBetween('-1 week', '+1 month'), // appointment_at 대신 slot_at 사용
            'type' => fake()->randomElement(['온라인', '오프라인', '전화']),
            'status' => fake()->randomElement(['예정', '완료', '취소']),
            'notes' => fake()->sentence(),
            'rebooking_suggested_at' => fake()->boolean(20) ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'total_revenue' => fake()->numberBetween(10000, 1000000),
        ];
    }
}
