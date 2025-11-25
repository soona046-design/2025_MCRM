# Cafe24 PHP 경로 찾기 가이드

SSH에서 다음 명령어들을 순서대로 실행하세요:

## 1. PHP 경로 찾기

```bash
# 방법 1: which 명령어
which php
which php82
which php81
which php80

# 방법 2: whereis 명령어
whereis php

# 방법 3: find 명령어 (시간이 걸림)
find /usr -name "php*" -type f 2>/dev/null | grep bin

# 방법 4: 일반적인 경로들 확인
ls /usr/bin/php*
ls /usr/local/php*/bin/php
ls /opt/php*/bin/php
ls ~/bin/php*

# 방법 5: 환경변수 확인
echo $PATH
```

## 2. PHP 버전 관리 도구 확인

```bash
# phpenv 확인
phpenv versions

# php-config 확인
which php-config
php-config --version
```

## 3. Cafe24 특정 경로

Cafe24는 보통 다음 구조를 사용:

```bash
# 홈 디렉토리의 PHP
ls ~/bin/
ls ~/.phpenv/versions/

# 시스템 PHP
ls /bin/php*
ls /sbin/php*
```

## 4. 대체 방법: CGI로 실행

PHP를 못찾으면 웹을 통해 실행:

```bash
# artisan을 웹으로 실행할 수 있는 스크립트 생성
cat > ~/public_html/migrate.php << 'EOF'
<?php
// 보안: IP 제한
$allowed_ips = ['YOUR_IP_HERE'];
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    die('Access denied');
}

chdir(__DIR__ . '/../laravel');
require __DIR__ . '/../laravel/vendor/autoload.php';

$app = require_once __DIR__ . '/../laravel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArrayInput([
        'command' => 'migrate',
        '--force' => true,
    ]),
    new Symfony\Component\Console\Output\BufferedOutput
);

echo "Migration completed with status: " . $status;
$kernel->terminate($input, $status);
EOF
```

그 후 브라우저에서:
```
https://insightmcrm.mycafe24.com/migrate.php
```
