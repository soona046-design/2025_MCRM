<?php
/**
 * 사용자 테이블 확인 스크립트
 * FTP로 /insightmcrm/www/에 업로드 후
 * http://insightmcrm.mycafe24.com/check-users.php 접속
 */

// Laravel 부트스트랩
chdir("/insightmcrm/laravel");
require_once "/insightmcrm/laravel/vendor/autoload.php";

$app = require_once "/insightmcrm/laravel/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<h1>사용자 테이블 확인</h1>";

try {
    // Users 테이블 확인
    $users = \Illuminate\Support\Facades\DB::table('users')->get();

    echo "<h2>총 사용자 수: " . count($users) . "</h2>";

    if (count($users) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>login_id</th><th>name</th><th>email</th><th>role</th><th>active</th></tr>";

        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user->login_id) . "</td>";
            echo "<td>" . htmlspecialchars($user->name) . "</td>";
            echo "<td>" . htmlspecialchars($user->email) . "</td>";
            echo "<td>" . htmlspecialchars($user->role) . "</td>";
            echo "<td>" . ($user->active ? '활성' : '비활성') . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>⚠️ 사용자 데이터가 없습니다! php artisan db:seed --class=UserSeeder 실행 필요</p>";
    }

    echo "<hr>";
    echo "<h2>테스트용 계정 정보</h2>";
    echo "<ul>";
    echo "<li><strong>슈퍼관리자:</strong> login_id=admin, password=admin123!@#</li>";
    echo "<li><strong>상담매니저:</strong> login_id=counselor1, password=counselor123!@#</li>";
    echo "</ul>";

} catch (\Exception $e) {
    echo "<p style='color: red;'>오류 발생: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><strong>주의:</strong> 확인 후 이 파일을 삭제하세요!</p>";
