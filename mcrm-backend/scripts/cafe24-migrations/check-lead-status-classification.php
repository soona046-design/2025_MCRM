<?php
/**
 * converted 리드의 appointment 연결 상태 분포 점검 (읽기 전용, DB 변경 없음)
 * FTP로 /insightmcrm/www/에 업로드 후
 * http://insightmcrm.mycafe24.com/check-lead-status-classification.php 접속
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
    section("1. leads.status 전체 분포");
    $rows = DB::select("SELECT status, COUNT(*) as cnt FROM leads GROUP BY status ORDER BY cnt DESC");
    foreach ($rows as $r) echo "{$r->status} | count={$r->cnt}\n";

    section("2. appointments.status 전체 분포 + revenue 유무");
    $rows = DB::select("SELECT status, COUNT(*) as cnt, SUM(CASE WHEN total_revenue IS NOT NULL THEN 1 ELSE 0 END) as with_revenue FROM appointments GROUP BY status ORDER BY cnt DESC");
    foreach ($rows as $r) echo "{$r->status} | count={$r->cnt} | revenue 있음={$r->with_revenue}\n";
    if (empty($rows)) echo "(appointments 데이터 없음)\n";

    $totalConverted = DB::select("SELECT COUNT(*) as c FROM leads WHERE status = 'converted'")[0]->c;
    section("3. status='converted' 리드 분류 (총 {$totalConverted}건)");

    $noApt = DB::select("
        SELECT COUNT(*) as c FROM leads l
        WHERE l.status = 'converted'
        AND NOT EXISTS (SELECT 1 FROM appointments a WHERE a.lead_id = l.lead_id)
    ")[0]->c;
    echo "(A) appointment 없음 → 상담완료 강등 후보: {$noApt}\n";

    $aptNotDone = DB::select("
        SELECT COUNT(DISTINCT l.lead_id) as c FROM leads l
        JOIN appointments a ON a.lead_id = l.lead_id
        WHERE l.status = 'converted'
        AND NOT EXISTS (
            SELECT 1 FROM appointments a2
            WHERE a2.lead_id = l.lead_id AND a2.status = 'done' AND a2.total_revenue IS NOT NULL
        )
    ")[0]->c;
    echo "(B) appointment 있으나 done+revenue 조건 미충족 → 예약완료 후보: {$aptNotDone}\n";

    $aptDoneRevenue = DB::select("
        SELECT COUNT(DISTINCT l.lead_id) as c FROM leads l
        JOIN appointments a ON a.lead_id = l.lead_id
        WHERE l.status = 'converted'
        AND a.status = 'done' AND a.total_revenue IS NOT NULL
    ")[0]->c;
    echo "(C) appointment done + revenue 있음 → 계약완료 유지 후보: {$aptDoneRevenue}\n";

    echo "검증: A+B+C = " . ($noApt + $aptNotDone + $aptDoneRevenue) . " (전체 {$totalConverted}건과 비교, lead당 appointment 복수일 경우 B/C 중복 제외 로직 확인용)\n";

    section("4. converted 리드별 appointment 상세 (최대 30건 샘플)");
    $rows = DB::select("
        SELECT l.lead_id, l.name, a.apt_id, a.status as apt_status, a.total_revenue
        FROM leads l
        LEFT JOIN appointments a ON a.lead_id = l.lead_id
        WHERE l.status = 'converted'
        ORDER BY l.lead_id
        LIMIT 30
    ");
    foreach ($rows as $r) {
        echo "lead={$r->lead_id} name=" . ($r->name ?? '-') . " | apt_id=" . ($r->apt_id ?? 'NULL') . " | apt_status=" . ($r->apt_status ?? '-') . " | revenue=" . ($r->total_revenue ?? '-') . "\n";
    }

} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n";
}

echo "\n⚠️ 확인 후 이 파일을 즉시 삭제하세요!\n";
