<?php
/**
 * Leads 테이블에 memo 컬럼 추가
 * FTP로 /insightmcrm/www/에 업로드 후
 * http://insightmcrm.mycafe24.com/add-memo-column.php 접속
 *
 * ⚠️ 실행 후 즉시 이 파일을 삭제하세요!
 */

// Laravel 부트스트랩
chdir("/insightmcrm/laravel");
require_once "/insightmcrm/laravel/vendor/autoload.php";

$app = require_once "/insightmcrm/laravel/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<h1>Leads 테이블 memo 컬럼 추가</h1>";

try {
    // memo 컬럼이 이미 있는지 확인
    $columns = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM leads LIKE 'memo'");

    if (count($columns) > 0) {
        echo "<p style='color: orange; font-weight: bold;'>✅ memo 컬럼이 이미 존재합니다.</p>";
        echo "<pre>";
        print_r($columns[0]);
        echo "</pre>";
    } else {
        echo "<p>memo 컬럼이 없습니다. 추가하는 중...</p>";

        // memo 컬럼 추가 (score 컬럼 다음에)
        \Illuminate\Support\Facades\DB::statement("
            ALTER TABLE `leads`
            ADD COLUMN `memo` TEXT NULL AFTER `score`
        ");

        echo "<p style='color: green; font-weight: bold;'>✅ memo 컬럼이 성공적으로 추가되었습니다!</p>";
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
