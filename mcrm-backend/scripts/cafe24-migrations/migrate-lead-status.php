<?php
/**
 * leads.status enum에 'scheduled'(예약완료) 추가 + 기존 모호한 converted 레코드 정리
 * FTP로 /insightmcrm/www/에 업로드 후
 * http://insightmcrm.mycafe24.com/migrate-lead-status.php 접속
 *
 * 변경 내용:
 *  1. leads.status enum: new/contacted/pending/converted/rejected (5개)
 *     → new/contacted/scheduled/converted/pending/rejected (6개, scheduled 추가)
 *  2. status='converted'인데 연결된 appointment가 없는 레코드는
 *     실제로 예약/계약까지 간 적이 없는 것으로 보고 'contacted'(상담완료)로 강등
 *     (운영 DB 실측 결과 1건 확인됨: appointment 0건 환경이라 안전)
 *
 * ⚠️ 실행 후 즉시 이 파일을 삭제하세요!
 */
chdir("/insightmcrm/laravel");
require_once "/insightmcrm/laravel/vendor/autoload.php";
$app = require_once "/insightmcrm/laravel/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

header('Content-Type: text/plain; charset=utf-8');

function section($title) {
    echo "\n=== {$title} ===\n";
}

try {
    section("1. 변경 전 status 분포");
    $rows = DB::select("SELECT status, COUNT(*) as cnt FROM leads GROUP BY status");
    foreach ($rows as $r) echo "{$r->status} | count={$r->cnt}\n";

    section("2. enum에 'scheduled' 추가");
    DB::statement("ALTER TABLE leads MODIFY COLUMN status ENUM('new', 'contacted', 'scheduled', 'converted', 'pending', 'rejected') NOT NULL DEFAULT 'new'");
    echo "완료: " . DB::select("SHOW COLUMNS FROM leads WHERE Field='status'")[0]->Type . "\n";

    section("3. appointment 없는 converted 리드를 contacted로 강등");
    $targets = DB::select("
        SELECT lead_id, name FROM leads l
        WHERE l.status = 'converted'
        AND NOT EXISTS (SELECT 1 FROM appointments a WHERE a.lead_id = l.lead_id)
    ");
    foreach ($targets as $t) echo "강등 대상: {$t->lead_id} ({$t->name})\n";
    if (empty($targets)) {
        echo "(대상 없음)\n";
    } else {
        $affected = DB::update("
            UPDATE leads SET status = 'contacted'
            WHERE status = 'converted'
            AND lead_id NOT IN (SELECT lead_id FROM appointments)
        ");
        echo "{$affected}건 강등 완료\n";
    }

    section("4. 변경 후 status 분포");
    $rows = DB::select("SELECT status, COUNT(*) as cnt FROM leads GROUP BY status");
    foreach ($rows as $r) echo "{$r->status} | count={$r->cnt}\n";

} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n";
}

echo "\n⚠️ 확인 후 이 파일을 즉시 삭제하세요!\n";
