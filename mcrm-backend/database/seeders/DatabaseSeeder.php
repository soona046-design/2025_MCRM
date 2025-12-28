<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // 기본 시더 실행
        $this->call([
            UserSeeder::class,
            TreatmentTypeSeeder::class, // 진료 유형 기본 데이터
        ]);

        $this->call([ // 새로운 시더들을 호출하도록 추가
            VisitSeeder::class,
            LeadSeeder::class,
            AppointmentSeeder::class,
            CostImportSeeder::class,
            TicketSeeder::class, // TicketSeeder 추가
        ]);
    }
}
