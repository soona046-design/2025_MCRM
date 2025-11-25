<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Lead; // Lead 모델 추가
use App\Models\User; // User 모델 추가
use App\Models\Ticket; // Ticket 모델 추가
use Illuminate\Support\Str; // Str 클래스 추가
use Carbon\Carbon; // Carbon 클래스 추가

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leads = Lead::all();
        $users = User::all();

        if ($leads->isEmpty() || $users->isEmpty()) {
            // 리드나 사용자가 없으면 티켓을 생성할 수 없으므로 경고 메시지를 출력하고 종료
            $this->command->warn('Leads or Users table is empty. Skipping TicketSeeder.');
            return;
        }

        foreach ($leads as $lead) {
            // 각 리드마다 1~3개의 티켓 생성
            for ($i = 0; $i < rand(1, 3); $i++) {
                $createdAt = Carbon::now()->subDays(rand(1, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
                $slaDueAt = $createdAt->copy()->addDays(rand(1, 7));

                Ticket::create([
                    'ticket_id' => Str::uuid(),
                    'lead_id' => $lead->lead_id,
                    'assignee_id' => $users->random()->user_id, // 랜덤 사용자 할당
                    'state' => $this->getRandomState(),
                    'priority' => $this->getRandomPriority(),
                    'title' => '[문의] ' . $lead->name . '님의 상담 요청 ', // 티켓 제목 추가
                    'notes' => '고객 문의 내용 샘플입니다. 빠른 처리 부탁드립니다.',
                    'latest_message_preview' => '안녕하세요, 문의하신 내용에 대해 안내드립니다.',
                    'sla_status' => $this->getRandomSlaStatus($slaDueAt),
                    'sla_due_at' => $slaDueAt,
                    'last_contact_at' => $createdAt->copy()->addHours(rand(1, 24)),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }

    private function getRandomState(): string
    {
        $states = ['신규', '진행', '보류', '완료'];
        return $states[array_rand($states)];
    }

    private function getRandomPriority(): string
    {
        $priorities = ['low', 'medium', 'high', 'urgent'];
        return $priorities[array_rand($priorities)];
    }

    private function getRandomSlaStatus(Carbon $slaDueAt): string
    {
        $now = Carbon::now();
        if ($now->greaterThan($slaDueAt)) {
            return 'violated';
        } elseif ($now->diffInHours($slaDueAt) < 24) {
            return 'warning';
        } else {
            return 'normal';
        }
    }
}
