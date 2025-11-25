<?php

namespace App\Listeners;

use App\Events\SlaViolated;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSlaViolationNotification implements ShouldQueue
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
        // 담당자에게 알림
        Notification::create([
            'user_id' => $event->ticket->assignee_id,
            'type' => Notification::TYPE_SLA_VIOLATION,
            'title' => 'SLA 위반',
            'message' => '티켓의 SLA가 위반되었습니다.',
            'data' => [
                'ticket_id' => $event->ticket->ticket_id,
                'violation_time' => now()->toIso8601String(),
            ],
            'ticket_id' => $event->ticket->ticket_id,
        ]);

        // 관리자들에게도 알림
        $adminUsers = User::where('role', 'admin')->get();
        foreach ($adminUsers as $admin) {
            Notification::create([
                'user_id' => $admin->user_id,
                'type' => Notification::TYPE_SLA_VIOLATION,
                'title' => 'SLA 위반 알림',
                'message' => "티켓 #{$event->ticket->ticket_id}의 SLA가 위반되었습니다.",
                'data' => [
                    'ticket_id' => $event->ticket->ticket_id,
                    'assignee_id' => $event->ticket->assignee_id,
                    'violation_time' => now()->toIso8601String(),
                ],
                'ticket_id' => $event->ticket->ticket_id,
            ]);
        }
    }
}
