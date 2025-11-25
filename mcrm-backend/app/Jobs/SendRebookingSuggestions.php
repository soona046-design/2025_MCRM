<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Appointment;
use Carbon\Carbon;

class SendRebookingSuggestions implements ShouldQueue
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

        // 지난 7일 이내에 노쇼 또는 취소되었으며, 아직 재예약 제안이 발송되지 않은 예약을 조회
        $appointmentsToSuggestRebooking = Appointment::whereIn('status', ['noshow', 'cancelled'])
            ->where('slot_at', '>=', $now->copy()->subDays(7)->startOfDay())
            ->whereNull('rebooking_suggested_at') // 아직 재예약 제안이 발송되지 않은 경우
            ->get();

        foreach ($appointmentsToSuggestRebooking as $appointment) {
            // 재예약 제안 이벤트 디스패치
            event(new \App\Events\RebookingSuggestionSent($appointment));

            // rebooking_suggested_at 필드 업데이트
            $appointment->update(['rebooking_suggested_at' => $now]);
        }
    }
}
