<?php
/**
 * 채널 데이터 분포/일관성 점검 (읽기 전용, DB 변경 없음)
 * FTP로 /insightmcrm/www/에 업로드 후
 * http://insightmcrm.mycafe24.com/check-channel-data.php 접속
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
    section("1. visits.utm_source / channel_category 분포");
    $rows = DB::select("SELECT utm_source, channel_category, COUNT(*) as cnt FROM visits GROUP BY utm_source, channel_category ORDER BY cnt DESC");
    foreach ($rows as $r) {
        echo ($r->utm_source ?? 'NULL') . " | category=" . ($r->channel_category ?? 'NULL') . " | count={$r->cnt}\n";
    }
    if (empty($rows)) echo "(데이터 없음)\n";

    section("2. leads 중 source_visit_id NULL 비율");
    $total = DB::select("SELECT COUNT(*) as c FROM leads")[0]->c;
    $nullVisit = DB::select("SELECT COUNT(*) as c FROM leads WHERE source_visit_id IS NULL")[0]->c;
    echo "전체 리드: {$total}, source_visit_id NULL(=채널 분석에서 누락): {$nullVisit}\n";

    section("3. leads + visits JOIN 시 보이는 utm_source 분포 (channel-pivot/funnel이 실제로 보는 데이터)");
    $rows = DB::select("SELECT v.utm_source, l.status, COUNT(*) as cnt FROM leads l JOIN visits v ON l.source_visit_id = v.visit_id GROUP BY v.utm_source, l.status ORDER BY cnt DESC");
    foreach ($rows as $r) {
        echo ($r->utm_source ?? 'NULL') . " | status={$r->status} | count={$r->cnt}\n";
    }
    if (empty($rows)) echo "(데이터 없음 - JOIN 결과가 비어있음)\n";

    section("4. channel_categories 마스터 데이터");
    $rows = DB::select("SELECT id, code, name, active FROM channel_categories");
    foreach ($rows as $r) echo "{$r->id} | {$r->code} | {$r->name} | active={$r->active}\n";

    section("5. channel_category_mappings (utm_source -> category 매핑 규칙)");
    $rows = DB::select("SELECT m.utm_source, c.code, c.name, m.active, m.priority FROM channel_category_mappings m JOIN channel_categories c ON m.category_id=c.id ORDER BY m.priority");
    foreach ($rows as $r) echo "{$r->utm_source} -> {$r->code} ({$r->name}) | active={$r->active} priority={$r->priority}\n";
    if (empty($rows)) echo "(매핑 데이터 없음 - 전부 규칙기반 fallback으로 분류됨)\n";

    section("6. channel_treatment_records의 채널 카테고리 분포");
    $rows = DB::select("SELECT cc.code, cc.name, ctr.input_type, COUNT(*) as cnt FROM channel_treatment_records ctr JOIN channel_categories cc ON ctr.channel_category_id = cc.id GROUP BY cc.code, cc.name, ctr.input_type");
    foreach ($rows as $r) echo "{$r->code} | {$r->name} | input_type={$r->input_type} | count={$r->cnt}\n";
    if (empty($rows)) echo "(데이터 없음)\n";

    section("7. ad_metrics platform/channel_type 분포");
    $rows = DB::select("SELECT platform, channel_type, COUNT(*) as cnt FROM ad_metrics GROUP BY platform, channel_type");
    foreach ($rows as $r) echo "{$r->platform} | {$r->channel_type} | count={$r->cnt}\n";
    if (empty($rows)) echo "(데이터 없음)\n";

    section("8. leads.status 값 분포 (실제 저장된 값)");
    $rows = DB::select("SELECT status, COUNT(*) as cnt FROM leads GROUP BY status");
    foreach ($rows as $r) echo "{$r->status} | count={$r->cnt}\n";

} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n";
}

echo "\n⚠️ 확인 후 이 파일을 즉시 삭제하세요!\n";
