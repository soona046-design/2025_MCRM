<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TreatmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $treatmentTypes = [
            // 보철 (Prosthetics)
            [
                'code' => 'implant',
                'name' => '임플란트',
                'category' => '보철',
                'color' => '#3b82f6',
                'sort_order' => 10,
                'description' => '임플란트 식립 및 보철',
            ],
            [
                'code' => 'crown',
                'name' => '크라운',
                'category' => '보철',
                'color' => '#60a5fa',
                'sort_order' => 20,
                'description' => '크라운 (금관, 세라믹 등)',
            ],
            [
                'code' => 'bridge',
                'name' => '브릿지',
                'category' => '보철',
                'color' => '#93c5fd',
                'sort_order' => 30,
                'description' => '브릿지 보철',
            ],
            [
                'code' => 'denture',
                'name' => '틀니',
                'category' => '보철',
                'color' => '#bfdbfe',
                'sort_order' => 40,
                'description' => '부분틀니 및 완전틀니',
            ],

            // 교정 (Orthodontics)
            [
                'code' => 'orthodontic',
                'name' => '교정',
                'category' => '교정',
                'color' => '#8b5cf6',
                'sort_order' => 50,
                'description' => '치아교정 치료',
            ],
            [
                'code' => 'invisalign',
                'name' => '투명교정',
                'category' => '교정',
                'color' => '#a78bfa',
                'sort_order' => 60,
                'description' => '인비절라인 등 투명 교정',
            ],

            // 보존 (Conservation)
            [
                'code' => 'filling',
                'name' => '충전',
                'category' => '보존',
                'color' => '#10b981',
                'sort_order' => 70,
                'description' => '충치 치료 및 레진 충전',
            ],
            [
                'code' => 'root_canal',
                'name' => '신경치료',
                'category' => '보존',
                'color' => '#34d399',
                'sort_order' => 80,
                'description' => '근관 치료 (신경 치료)',
            ],
            [
                'code' => 'extraction',
                'name' => '발치',
                'category' => '보존',
                'color' => '#6ee7b7',
                'sort_order' => 90,
                'description' => '일반 발치 및 사랑니 발치',
            ],

            // 미용 (Aesthetic)
            [
                'code' => 'whitening',
                'name' => '미백',
                'category' => '미용',
                'color' => '#f59e0b',
                'sort_order' => 100,
                'description' => '치아 미백',
            ],
            [
                'code' => 'laminate',
                'name' => '라미네이트',
                'category' => '미용',
                'color' => '#fbbf24',
                'sort_order' => 110,
                'description' => '라미네이트 (심미 보철)',
            ],
            [
                'code' => 'scaling',
                'name' => '스케일링',
                'category' => '예방',
                'color' => '#22c55e',
                'sort_order' => 120,
                'description' => '치석 제거 및 스케일링',
            ],

            // 기타
            [
                'code' => 'consultation',
                'name' => '상담',
                'category' => '기타',
                'color' => '#6b7280',
                'sort_order' => 130,
                'description' => '초진 상담',
            ],
            [
                'code' => 'checkup',
                'name' => '검진',
                'category' => '기타',
                'color' => '#9ca3af',
                'sort_order' => 140,
                'description' => '정기 검진',
            ],
        ];

        foreach ($treatmentTypes as $type) {
            \App\Models\TreatmentType::create($type);
        }
    }
}
