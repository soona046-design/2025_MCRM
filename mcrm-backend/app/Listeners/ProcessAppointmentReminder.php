<?php

namespace App\Listeners;

use App\Events\AppointmentReminderSent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use App\Models\Lead;
use App\Models\User;

class ProcessAppointmentReminder implements ShouldQueue
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
    public function handle(AppointmentReminderSent $event): void
    {
        $appointment = $event->appointment;
        $lead = $appointment->lead; // Appointment 모델에 lead 관계가 정의되어 있다고 가정
        $doctor = $appointment->doctor; // Appointment 모델에 doctor 관계가 정의되어 있다고 가정

        if (!$lead) {
            // TODO: 로그 기록 - 연결된 리드가 없는 예약
            return;
        }

        // 알림 메시지 구성
        $message = "[예약 리마인더] {$lead->name} 고객님, 내일 " . \Carbon\Carbon::parse($appointment->slot_at)->format('Y년 m월 d일 H시 i분') . "에 예약되어 있습니다. ";
        if ($doctor) {
            $message .= "담당 의사: {$doctor->name}.";
        }
        $message .= "병원 방문에 차질 없으시길 바랍니다.";

        // TODO: 실제 알림톡/SMS 발송 로직 구현
        // 현재는 Slack 웹훅으로 대체
        $this->sendSlackNotification($message, $lead->primary_phone);
    }

    /**
     * Slack 웹훅을 통해 알림을 전송합니다. (SMS/알림톡 대체 예시)
     *
     * @param string $message
     * @param string|null $phoneNumber (실제 SMS/알림톡 발송 시 사용)
     * @return void
     */
    private function sendSlackNotification(string $message, ?string $phoneNumber): void
    {
        $slackWebhookUrl = env('SLACK_WEBHOOK_URL'); // .env 파일에 SLACK_WEBHOOK_URL 설정

        if (!$slackWebhookUrl) {
            // TODO: 로그 기록 또는 다른 방법으로 알림
            return;
        }

        Http::post($slackWebhookUrl, [
            'text' => $message,
        ]);
    }
}
