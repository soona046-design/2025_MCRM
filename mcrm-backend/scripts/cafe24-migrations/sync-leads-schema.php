<?php
/**
 * leads 테이블 스키마를 로컬 마이그레이션 기준으로 동기화
 * (2025_09_14_041555_create_leads_table.php / 2025_09_25_000000_add_foreign_keys_to_leads_table.php)
 *
 * 추가 컬럼: secondary_phone, birth_date, gender, address, city,
 *           latest_visit_id, latest_ticket_id, latest_appointment_id, assigned_user_id
 * 추가 FK : assigned_user_id -> users, latest_ticket_id -> tickets,
 *           latest_appointment_id -> appointments (source_visit_id FK는 이미 존재)
 *
 * 컬럼/FK가 이미 있으면 건너뛰므로 재실행해도 안전합니다.
 *
 * FTP로 /insightmcrm/www/에 업로드 후
 * http://insightmcrm.mycafe24.com/sync-leads-schema.php 접속
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

function columnExists($table, $column) {
    return !empty(DB::select("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$column]));
}

function fkExists($constraintName) {
    return !empty(DB::select(
        "SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND CONSTRAINT_NAME = ?",
        [$constraintName]
    ));
}

try {
    section("1. 변경 전 leads 컬럼");
    foreach (DB::select("SHOW COLUMNS FROM leads") as $c) echo "{$c->Field} | {$c->Type}\n";

    section("2. 누락 컬럼 추가");
    $columns = [
        'secondary_phone' => "ALTER TABLE leads ADD COLUMN secondary_phone VARCHAR(255) NULL AFTER primary_phone",
        'birth_date' => "ALTER TABLE leads ADD COLUMN birth_date DATE NULL AFTER name",
        'gender' => "ALTER TABLE leads ADD COLUMN gender ENUM('male','female','other') NULL AFTER birth_date",
        'address' => "ALTER TABLE leads ADD COLUMN address VARCHAR(255) NULL AFTER gender",
        'city' => "ALTER TABLE leads ADD COLUMN city VARCHAR(255) NULL AFTER address",
        'latest_visit_id' => "ALTER TABLE leads ADD COLUMN latest_visit_id CHAR(36) NULL AFTER status",
        'latest_ticket_id' => "ALTER TABLE leads ADD COLUMN latest_ticket_id CHAR(36) NULL AFTER latest_visit_id",
        'latest_appointment_id' => "ALTER TABLE leads ADD COLUMN latest_appointment_id CHAR(36) NULL AFTER latest_ticket_id",
        'assigned_user_id' => "ALTER TABLE leads ADD COLUMN assigned_user_id CHAR(36) NULL AFTER latest_appointment_id",
    ];
    foreach ($columns as $col => $sql) {
        if (columnExists('leads', $col)) {
            echo "건너뜀 (이미 존재): {$col}\n";
            continue;
        }
        DB::statement($sql);
        echo "추가 완료: {$col}\n";
    }

    section("3. 외래키 추가");
    $foreignKeys = [
        'leads_assigned_user_id_foreign' =>
            "ALTER TABLE leads ADD CONSTRAINT leads_assigned_user_id_foreign FOREIGN KEY (assigned_user_id) REFERENCES users(user_id) ON DELETE SET NULL",
        'leads_latest_ticket_id_foreign' =>
            "ALTER TABLE leads ADD CONSTRAINT leads_latest_ticket_id_foreign FOREIGN KEY (latest_ticket_id) REFERENCES tickets(ticket_id) ON DELETE SET NULL",
        'leads_latest_appointment_id_foreign' =>
            "ALTER TABLE leads ADD CONSTRAINT leads_latest_appointment_id_foreign FOREIGN KEY (latest_appointment_id) REFERENCES appointments(apt_id) ON DELETE SET NULL",
    ];
    foreach ($foreignKeys as $name => $sql) {
        if (fkExists($name)) {
            echo "건너뜀 (이미 존재): {$name}\n";
            continue;
        }
        DB::statement($sql);
        echo "추가 완료: {$name}\n";
    }

    section("4. migrations 테이블 기록 갱신");
    $already = DB::select("SELECT 1 FROM migrations WHERE migration = ?", ['2025_09_25_000000_add_foreign_keys_to_leads_table']);
    if (empty($already)) {
        $maxBatch = DB::select("SELECT MAX(batch) as b FROM migrations")[0]->b;
        DB::table('migrations')->insert([
            'migration' => '2025_09_25_000000_add_foreign_keys_to_leads_table',
            'batch' => $maxBatch + 1,
        ]);
        echo "migrations 테이블에 기록 추가 (batch=" . ($maxBatch + 1) . ")\n";
    } else {
        echo "이미 기록되어 있음\n";
    }

    section("5. 변경 후 leads 컬럼");
    foreach (DB::select("SHOW COLUMNS FROM leads") as $c) echo "{$c->Field} | {$c->Type}\n";

    section("6. 변경 후 외래키");
    foreach (DB::select("SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='leads' AND REFERENCED_TABLE_NAME IS NOT NULL") as $c) {
        echo "{$c->CONSTRAINT_NAME}: {$c->COLUMN_NAME} -> {$c->REFERENCED_TABLE_NAME}\n";
    }

    section("7. funnel-dropoffs 쿼리 재현 테스트 (assigned_user_id JOIN)");
    $test = DB::table('leads')
        ->leftJoin('visits', 'leads.source_visit_id', '=', 'visits.visit_id')
        ->leftJoin('users', 'leads.assigned_user_id', '=', 'users.user_id')
        ->whereIn('leads.status', ['pending', 'rejected'])
        ->select('leads.lead_id')
        ->get();
    echo "성공, 결과 수: " . count($test) . "\n";

    echo "\n✅ 전체 완료\n";
} catch (Exception $e) {
    echo "\n❌ 에러 발생: " . $e->getMessage() . "\n";
}
