<?php

namespace App\Listeners;

use App\Events\SlaViolated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class SendSlaNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SlaViolated $event): void
    {
        $ticket = $event->ticket;

        // 알림 메시지 구성
        $message = "[SLA 위반 알림] 티켓 ID: {$ticket->ticket_id}, 리드 ID: {$ticket->lead_id}, 상태: {$ticket->state}, 우선순위: {$ticket->priority}, 마지막 접촉: {$ticket->last_contact_at}";

        // 담당자 또는 관리자에게 알림 전송 (예시: Slack 웹훅)
        $this->sendSlackNotification($message, $ticket);

        // TODO: 푸시 알림, 이메일 등 다른 알림 채널 추가
    }

    /**
     * Slack 웹훅을 통해 알림을 전송합니다.
     *
     * @param string $message
     * @param \App\Models\Ticket $ticket
     * @return void
     */
    private function sendSlackNotification(string $message, Ticket $ticket): void
    {
        $slackWebhookUrl = env('SLACK_WEBHOOK_URL'); // .env 파일에 SLACK_WEBHOOK_URL 설정

        if (!$slackWebhookUrl) {
            // TODO: 로그 기록 또는 다른 방법으로 알림 (예: 에러 로그)
            // Log::error('Slack webhook URL not configured.');
            return;
        }

        // 담당자 정보가 있다면 해당 담당자에게 멘션
        $assigneeName = null;
        if ($ticket->assignee_id) {
            $assignee = User::find($ticket->assignee_id);
            if ($assignee) {
                $assigneeName = $assignee->name; // User 모델에 name 필드가 있다고 가정
                $message .= " (담당자: {$assigneeName})";
            }
        }

        Http::post($slackWebhookUrl, [
            'text' => $message,
            // 추가적인 Slack 메시지 포맷팅 (attachments, blocks 등) 가능
        ]);
    }
}
