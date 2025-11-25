<?php
// migrate.php - Laravel 마이그레이션 실행 파일
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<h2>Laravel 마이그레이션 시작</h2>";
echo "<pre>";

try {
    echo "1. 데이터베이스 마이그레이션 시작...\n";
    $kernel->call('migrate', ['--force' => true]);
    echo "✅ 마이그레이션 완료!\n\n";
    
    echo "2. 시더 데이터 입력 시작...\n";
    $kernel->call('db:seed', ['--force' => true]);
    echo "✅ 시더 완료!\n\n";
    
    echo "3. 캐시 클리어 시작...\n";
    $kernel->call('config:cache');
    $kernel->call('route:cache');
    $kernel->call('view:cache');
    echo "✅ 캐시 클리어 완료!\n\n";
    
    echo "🎉 모든 설정이 완료되었습니다!\n";
    echo "이제 https://insightmcrm.mycafe24.com 에서 사이트에 접속할 수 있습니다.\n";
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . "\n";
    echo "라인: " . $e->getLine() . "\n";
}

echo "</pre>";
echo "<p><strong>보안을 위해 이 파일을 삭제하세요!</strong></p>";
?>