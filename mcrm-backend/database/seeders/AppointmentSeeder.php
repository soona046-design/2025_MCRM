<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\Lead;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leads = Lead::all();

        $leads->each(function ($lead) {
            Appointment::factory()->count(rand(1, 3))->create([
                'lead_id' => $lead->lead_id,
                'total_revenue' => rand(10000, 1000000),
            ]);
        });
    }
}
