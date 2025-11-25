# Cafe24 배포 가이드

M-CRM 시스템을 Cafe24 웹호스팅에 배포하는 방법입니다.

## 📦 배포 패키지 구조

```
cafe24-deploy/
├── www/                    # 웹루트 (Cafe24 public_html에 업로드)
│   ├── index.php          # Laravel 진입점
│   ├── .htaccess          # Apache 설정
│   ├── favicon.ico
│   └── robots.txt
├── laravel/                # Laravel 소스 코드 (웹루트 밖)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── storage/
│   ├── .env.cafe24        # Cafe24용 환경 설정 예제
│   └── composer.json
└── DEPLOY_GUIDE.md        # 이 파일
```

## 🚀 배포 순서

### 1. Cafe24 FTP 접속

Cafe24 호스팅 관리자 페이지에서 FTP 계정 정보를 확인합니다.

```
FTP 주소: yourdomain.cafe24.com
포트: 21
사용자명: your_ftp_username
비밀번호: your_ftp_password
```

### 2. 파일 업로드

**FTP 클라이언트 (FileZilla 등) 사용:**

```
로컬                          →  Cafe24 서버
cafe24-deploy/www/           →  /public_html/
cafe24-deploy/laravel/       →  /laravel/
```

**중요:** `laravel/` 폴더는 `public_html`과 같은 레벨에 위치해야 합니다.

```
/home/yourid/
├── public_html/     # www 폴더 내용
└── laravel/         # laravel 폴더 전체
```

### 3. Composer 의존성 설치

Cafe24는 SSH 접속이 제한적이므로, 로컬에서 의존성을 설치하고 업로드합니다.

**로컬에서 실행:**
```bash
cd cafe24-deploy/laravel
composer install --optimize-autoloader --no-dev
```

그 후 `vendor/` 폴더를 FTP로 업로드합니다.

### 4. 환경 설정 (.env)

1. `.env.cafe24` 파일을 복사하여 `.env`로 이름 변경
2. 데이터베이스 정보 수정:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_cafe24_db_name
DB_USERNAME=your_cafe24_db_user
DB_PASSWORD=your_cafe24_db_password
```

3. APP_KEY 생성 (로컬에서 실행 후 복사):
```bash
php artisan key:generate --show
```

생성된 키를 `.env` 파일에 붙여넣기:
```env
APP_KEY=base64:생성된키값
```

4. APP_URL 설정:
```env
APP_URL=https://yourdomain.cafe24.com
```

### 5. 데이터베이스 설정

Cafe24 호스팅 관리자에서:
1. MySQL 데이터베이스 생성
2. 데이터베이스 사용자 생성 및 권한 부여
3. phpMyAdmin 접속하여 SQL 파일 import

**마이그레이션 실행 (SSH 가능한 경우):**
```bash
php artisan migrate --force
php artisan db:seed --force
```

**SSH 불가능한 경우:**
- 로컬에서 마이그레이션 SQL을 export하여 phpMyAdmin으로 import

### 6. 권한 설정

**SSH 접속 가능한 경우:**
```bash
chmod -R 755 /home/yourid/laravel/storage
chmod -R 755 /home/yourid/laravel/bootstrap/cache
```

**FTP 클라이언트 사용:**
- `laravel/storage/` 폴더: 755 권한
- `laravel/bootstrap/cache/` 폴더: 755 권한

### 7. 캐시 최적화

**SSH 접속 가능한 경우:**
```bash
cd /home/yourid/laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 8. 테스트

브라우저에서 접속:
```
https://yourdomain.cafe24.com/api/health
```

응답 확인:
```json
{
  "status": "ok",
  "timestamp": "2025-11-20T16:30:00Z"
}
```

## 🔧 프론트엔드 배포 (Next.js)

### 옵션 1: Vercel 배포 (권장)

Next.js는 Vercel에 배포하고, 백엔드 API만 Cafe24에서 운영하는 것을 권장합니다.

1. GitHub에 프론트엔드 코드 push
2. Vercel 계정 연결
3. 환경변수 설정:
```
NEXT_PUBLIC_API_URL=https://yourdomain.cafe24.com/api
```

### 옵션 2: Static Export (API 요청 제한 있음)

```bash
cd m-crm-project
npm run build
```

빌드된 `out/` 폴더를 Cafe24 `public_html/app/`에 업로드

### 옵션 3: Cafe24 Node.js 호스팅

Cafe24의 Node.js 호스팅 상품을 이용하여 Next.js 서버 실행

## 🐛 트러블슈팅

### 500 Internal Server Error

1. `.env` 파일 확인
2. `storage/` 폴더 권한 확인 (755)
3. `bootstrap/cache/` 폴더 권한 확인 (755)
4. Laravel 로그 확인: `laravel/storage/logs/laravel.log`

### Database Connection Error

1. `.env`의 DB 정보 확인
2. Cafe24 DB 호스트가 `localhost`인지 확인
3. DB 사용자 권한 확인

### CORS 에러

`.env` 파일에서 CORS 설정:
```env
SANCTUM_STATEFUL_DOMAINS=yourdomain.cafe24.com
SESSION_DOMAIN=.cafe24.com
```

`config/cors.php` 확인

## 📝 유지보수

### 로그 확인

FTP로 접속하여 로그 파일 다운로드:
```
/home/yourid/laravel/storage/logs/laravel.log
```

### 코드 업데이트

1. 로컬에서 코드 수정
2. FTP로 변경된 파일만 업로드
3. 캐시 클리어 (SSH 가능 시):
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 백업

정기적으로 백업:
1. 데이터베이스: phpMyAdmin에서 Export
2. 파일: FTP로 전체 다운로드
3. `.env` 파일: 별도 보관

## 📞 지원

문제 발생 시:
1. Laravel 로그 확인
2. Cafe24 고객센터 문의
3. 프로젝트 이슈 트래커 등록

---

**배포 완료 체크리스트:**
- [ ] FTP로 파일 업로드 완료
- [ ] Composer 의존성 설치
- [ ] .env 파일 설정 완료
- [ ] APP_KEY 생성 및 설정
- [ ] 데이터베이스 연결 확인
- [ ] 마이그레이션 실행
- [ ] storage 폴더 권한 설정
- [ ] API 테스트 성공
- [ ] 프론트엔드 배포 (Vercel 등)
- [ ] CORS 설정 확인

**생성일:** 2025-11-20
**버전:** 1.0
