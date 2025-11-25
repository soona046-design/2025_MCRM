<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Lead;
use App\Models\Visit;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $visits = Visit::all();

        Lead::factory()->count(20)->create(); // 20개의 리드 생성

        // 각 방문에 대해 최소 1개의 리드를 연결 (선택 사항)
        $visits->each(function ($visit) {
            Lead::factory()->create([
                'source_visit_id' => $visit->visit_id,
            ]);
        });
    }
}
