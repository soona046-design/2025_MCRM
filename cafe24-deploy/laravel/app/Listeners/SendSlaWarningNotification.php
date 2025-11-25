<?php

namespace App\Listeners;

use App\Events\SlaWarning;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSlaWarningNotification implements ShouldQueue
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
    public function handle(SlaWarning $event): void
    {
        Notification::create([
            'user_id' => $event->ticket->assignee_id,
            'type' => Notification::TYPE_SLA_WARNING,
            'title' => 'SLA 경고',
            'message' => "SLA 위반까지 {$event->remainingMinutes}분 남았습니다.",
            'data' => [
                'ticket_id' => $event->ticket->ticket_id,
                'remaining_minutes' => $event->remainingMinutes,
            ],
            'ticket_id' => $event->ticket->ticket_id,
        ]);
    }
}