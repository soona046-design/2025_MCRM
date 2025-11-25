<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\CheckSlaViolations;
use App\Jobs\SendAppointmentReminders;
use App\Jobs\SendRebookingSuggestions;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new CheckSlaViolations)->everyMinute();
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
