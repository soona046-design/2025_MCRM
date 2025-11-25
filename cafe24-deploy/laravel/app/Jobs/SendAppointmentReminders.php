<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Appointment;
use Carbon\Carbon;

class SendAppointmentReminders implements ShouldQueue
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

        // 내일 예정된 예약 중 아직 리마인더가 발송되지 않은 예약을 조회
        $appointmentsToRemind = Appointment::where('slot_at', '>=', $now->copy()->addDay()->startOfDay())
            ->where('slot_at', '<=', $now->copy()->addDay()->endOfDay())
            ->where('reminder_sent', false)
            ->where('status', 'booked') // 예약 상태가 'booked'인 경우만
            ->get();

        foreach ($appointmentsToRemind as $appointment) {
            // 리마인더 발송 이벤트 디스패치
            event(new \App\Events\AppointmentReminderSent($appointment));

            // reminder_sent 필드 업데이트
            $appointment->update(['reminder_sent' => true]);
        }
    }
}
