<?php
/**
 * leads.status enum에 'scheduled' 값 추가
 * FTP로 /insightmcrm/www/에 업로드 후
 * http://insightmcrm.mycafe24.com/add-scheduled-status-enum.php 접속
 *
 * ⚠️ 실행 후 즉시 이 파일을 삭제하세요!
 */

chdir("/insightmcrm/laravel");
require_once "/insightmcrm/laravel/vendor/autoload.php";

$app = require_once "/insightmcrm/laravel/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<h1>leads.status enum에 'scheduled' 추가</h1>";

try {
    $column = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM leads LIKE 'status'")[0];
    echo "<p>변경 전 status 컬럼 타입:</p><pre>" . htmlspecialchars($column->Type) . "</pre>";

    if (strpos($column->Type, "'scheduled'") !== false) {
        echo "<p style='color: orange; font-weight: bold;'>✅ 'scheduled' 값이 이미 enum에 존재합니다. 변경하지 않았습니다.</p>";
    } else {
        \Illuminate\Support\Facades\DB::statement(
            "ALTER TABLE leads MODIFY COLUMN status ENUM('new', 'contacted', 'scheduled', 'converted', 'pending', 'rejected') NOT NULL DEFAULT 'new'"
        );
        echo "<p style='color: green; font-weight: bold;'>✅ status enum에 'scheduled' 값을 추가했습니다.</p>";

        $migrationName = '2026_06_20_141533_add_scheduled_to_leads_status_enum';
        $already = \Illuminate\Support\Facades\DB::table('migrations')->where('migration', $migrationName)->exists();
        if (!$already) {
            $batch = \Illuminate\Support\Facades\DB::table('migrations')->max('batch') + 1;
            \Illuminate\Support\Facades\DB::table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => $batch,
            ]);
            echo "<p>migrations 테이블에 기록 완료 (batch {$batch})</p>";
        }
    }

    echo "<hr><h2>변경 후 status 컬럼:</h2>";
    $column = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM leads LIKE 'status'")[0];
    echo "<pre>" . htmlspecialchars($column->Type) . "</pre>";

} catch (\Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ 오류 발생:</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

echo "<hr>";
echo "<p style='color: red; font-weight: bold;'>⚠️ 주의: 확인 후 이 파일을 즉시 삭제하세요!</p>";
