<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * 역할(Role) 정의:
     * - super_admin: 슈퍼관리자 (전체 시스템 관리, 모든 권한)
     * - branch_manager: 지점관리자 (소속 지점의 모든 데이터 및 사용자 관리)
     * - counselor: 상담매니저 (리드/상담/예약 관리, 메모/태그 편집)
     * - marketer: 마케터 (캠페인/소재 코드, 리포트 접근)
     * - doctor: 의사 (일정·진료결과 요약 열람, 민감정보 최소화)
     */
    public function run(): void
    {
        // 슈퍼관리자 계정 생성
        User::firstOrCreate(
            ['login_id' => 'admin'],
            [
                'user_id' => Str::uuid(),
                'name' => '슈퍼관리자',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123!@#'),
                'role' => 'super_admin',
                'active' => true,
                'clinic_id' => null, // 전체 시스템 관리
            ]
        );

        // 지점관리자 계정들
        User::firstOrCreate(
            ['login_id' => 'manager_seoul'],
            [
                'user_id' => Str::uuid(),
                'name' => '서울지점 관리자',
                'email' => 'manager.seoul@example.com',
                'password' => Hash::make('manager123!@#'),
                'role' => 'branch_manager',
                'clinic_id' => 'clinic_seoul',
                'phone' => '02-1234-5678',
                'active' => true,
            ]
        );

        User::firstOrCreate(
            ['login_id' => 'manager_busan'],
            [
                'user_id' => Str::uuid(),
                'name' => '부산지점 관리자',
                'email' => 'manager.busan@example.com',
                'password' => Hash::make('manager123!@#'),
                'role' => 'branch_manager',
                'clinic_id' => 'clinic_busan',
                'phone' => '051-1234-5678',
                'active' => true,
            ]
        );

        // 상담매니저 계정들
        User::firstOrCreate(
            ['login_id' => 'counselor1'],
            [
                'user_id' => Str::uuid(),
                'name' => '김상담',
                'email' => 'counselor1@example.com',
                'password' => Hash::make('counselor123!@#'),
                'role' => 'counselor',
                'clinic_id' => 'clinic_seoul',
                'phone' => '010-1111-1111',
                'active' => true,
            ]
        );

        User::firstOrCreate(
            ['login_id' => 'counselor2'],
            [
                'user_id' => Str::uuid(),
                'name' => '이상담',
                'email' => 'counselor2@example.com',
                'password' => Hash::make('counselor123!@#'),
                'role' => 'counselor',
                'clinic_id' => 'clinic_seoul',
                'phone' => '010-2222-2222',
                'active' => true,
            ]
        );

        User::firstOrCreate(
            ['login_id' => 'counselor3'],
            [
                'user_id' => Str::uuid(),
                'name' => '박상담',
                'email' => 'counselor3@example.com',
                'password' => Hash::make('counselor123!@#'),
                'role' => 'counselor',
                'clinic_id' => 'clinic_busan',
                'phone' => '010-3333-3333',
                'active' => true,
            ]
        );

        // 마케터 계정
        User::firstOrCreate(
            ['login_id' => 'marketer1'],
            [
                'user_id' => Str::uuid(),
                'name' => '최마케터',
                'email' => 'marketer1@example.com',
                'password' => Hash::make('marketer123!@#'),
                'role' => 'marketer',
                'clinic_id' => null, // 전체 캠페인 관리
                'phone' => '010-4444-4444',
                'active' => true,
            ]
        );

        // 의사 계정
        User::firstOrCreate(
            ['login_id' => 'doctor1'],
            [
                'user_id' => Str::uuid(),
                'name' => '홍원장',
                'email' => 'doctor1@example.com',
                'password' => Hash::make('doctor123!@#'),
                'role' => 'doctor',
                'clinic_id' => 'clinic_seoul',
                'phone' => '010-5555-5555',
                'active' => true,
            ]
        );

        // 레거시 호환용 (기존 'admin', 'agent' role)
        User::firstOrCreate(
            ['login_id' => 'agent1'],
            [
                'user_id' => Str::uuid(),
                'name' => '상담원1',
                'email' => 'agent1@example.com',
                'password' => Hash::make('agent123!@#'),
                'role' => 'counselor', // agent -> counselor로 매핑
                'active' => true,
            ]
        );
    }
}
