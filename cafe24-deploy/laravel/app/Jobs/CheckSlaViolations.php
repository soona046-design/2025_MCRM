<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Ticket;
use Carbon\Carbon;

class CheckSlaViolations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = Carbon::now();

        // '신규' 또는 '진행' 상태이며, 마지막 접촉으로부터 일정 시간이 지난 티켓을 찾습니다.
        $ticketsToMonitor = Ticket::whereIn('state', ['신규', '진행'])
            ->where(function ($query) {
                $query->whereNull('last_contact_at')
                      ->orWhere('last_contact_at', '<=', Carbon::now()->subMinutes(30)); // 예: 30분 미응답
            })
            ->get();

        foreach ($ticketsToMonitor as $ticket) {
            // SLA 만료 시간 설정 (예: 문의 발생 후 60분)
            $slaDueAt = $ticket->created_at->addMinutes(60); // 티켓 생성 후 60분

            // 경고 상태 (예: 30분 경과)
            $warningThreshold = $ticket->created_at->addMinutes(30);

            if ($now->greaterThanOrEqualTo($slaDueAt)) {
                // SLA 위반 상태
                if ($ticket->sla_status !== 'violated') {
                    $ticket->update([
                        'sla_status' => 'violated',
                        'sla_due_at' => $slaDueAt,
                    ]);
                    event(new \App\Events\SlaViolated($ticket)); // SLA 위반 알림 이벤트 디스패치
                }
            } elseif ($now->greaterThanOrEqualTo($warningThreshold) && $ticket->sla_status === 'normal') {
                // SLA 경고 상태
                $ticket->update([
                    'sla_status' => 'warning',
                    'sla_due_at' => $slaDueAt,
                ]);
                event(new \App\Events\SlaWarning($ticket)); // SLA 경고 알림 이벤트 디스패치
            }
        }
    }
}
