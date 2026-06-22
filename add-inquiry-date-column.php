<?php
/**
 * Leads 테이블에 inquiry_date 컬럼 추가
 * FTP로 /insightmcrm/www/에 업로드 후
 * http://insightmcrm.mycafe24.com/add-inquiry-date-column.php 접속
 *
 * ⚠️ 실행 후 즉시 이 파일을 삭제하세요!
 */

// Laravel 부트스트랩
chdir("/insightmcrm/laravel");
require_once "/insightmcrm/laravel/vendor/autoload.php";

$app = require_once "/insightmcrm/laravel/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<h1>Leads 테이블 inquiry_date 컬럼 추가</h1>";

try {
    // inquiry_date 컬럼이 이미 있는지 확인
    $columns = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM leads LIKE 'inquiry_date'");

    if (count($columns) > 0) {
        echo "<p style='color: orange; font-weight: bold;'>✅ inquiry_date 컬럼이 이미 존재합니다.</p>";
        echo "<pre>";
        print_r($columns[0]);
        echo "</pre>";
    } else {
        echo "<p>inquiry_date 컬럼이 없습니다. 추가하는 중...</p>";

        // inquiry_date 컬럼 추가 (memo 컬럼 다음에)
        \Illuminate\Support\Facades\DB::statement("
            ALTER TABLE `leads`
            ADD COLUMN `inquiry_date` DATE NULL AFTER `memo`
        ");

        echo "<p style='color: green; font-weight: bold;'>✅ inquiry_date 컬럼이 성공적으로 추가되었습니다!</p>";

        // migrations 테이블에 기록 (로컬 마이그레이션 파일명과 일치시킴)
        $migrationName = '2026_06_22_041127_add_inquiry_date_to_leads_table';
        $exists = \Illuminate\Support\Facades\DB::table('migrations')
            ->where('migration', $migrationName)
            ->exists();

        if (!$exists) {
            $nextBatch = (int) \Illuminate\Support\Facades\DB::table('migrations')->max('batch') + 1;
            \Illuminate\Support\Facades\DB::table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => $nextBatch,
            ]);
            echo "<p style='color: green;'>✅ migrations 테이블에 기록 추가 (batch {$nextBatch})</p>";
        }
    }

    // 확인
    echo "<hr>";
    echo "<h2>현재 leads 테이블 구조:</h2>";
    $columns = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM leads");

    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column->Field . "</td>";
        echo "<td>" . $column->Type . "</td>";
        echo "<td>" . $column->Null . "</td>";
        echo "<td>" . $column->Key . "</td>";
        echo "<td>" . ($column->Default ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (\Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ 오류 발생:</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p style='color: red; font-weight: bold;'>⚠️ 주의: 확인 후 이 파일을 즉시 삭제하세요!</p>";
