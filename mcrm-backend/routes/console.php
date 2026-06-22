<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 네이버 광고비를 매일 새벽 2시에 자동 수집해 cost_imports에 누적 저장 (최근 7일 롤링 윈도우로 보정 반영)
// 주의: app/Console/Kernel.php는 이 Laravel 12 앱 구조에서 바인딩되지 않는 죽은 코드라
// 실제로 동작하는 스케줄은 여기(routes/console.php)에 등록해야 함
Schedule::command('ads:collect-costs')->dailyAt('02:00')->withoutOverlapping();
