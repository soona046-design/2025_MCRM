<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
// [SLA 기능 비활성화 2026-06-22] use App\Jobs\CheckSlaViolations;
use App\Jobs\SendAppointmentReminders;
use App\Jobs\SendRebookingSuggestions;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * 주의: 이 클래스는 현재 Laravel 부트스트랩(bootstrap/app.php)에 바인딩돼 있지 않아
     * 실제로는 호출되지 않음 — 실제 스케줄은 routes/console.php에 등록돼 있음.
     */
    protected function schedule(Schedule $schedule): void
    {
        // [SLA 기능 비활성화 2026-06-22] $schedule->job(new CheckSlaViolations)->everyMinute();
        $schedule->job(new SendAppointmentReminders)->hourly();
        $schedule->job(new SendRebookingSuggestions)->dailyAt('01:00'); // 매일 새벽 1시에 실행
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
