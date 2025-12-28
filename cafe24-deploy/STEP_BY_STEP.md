# Cafe24 업로드 상세 가이드

## ⚠️ 중요: DB는 반드시 설정해야 합니다!

Laravel은 데이터베이스 없이 작동할 수 없습니다. 반드시 MySQL DB를 생성하고 설정해야 합니다.

---

## 📋 사전 준비

### 1. Cafe24 호스팅 정보 확인

Cafe24 관리자 페이지 (https://www.cafe24.com) 로그인 후:

1. **[나의 서비스 관리]** 클릭
2. **[호스팅 관리]** 선택
3. 다음 정보를 확인하고 메모:

```
도메인: insightmcrm.mycafe24.com
FTP 주소: insightmcrm.mycafe24.com(또는 ftp.cafe24.com)
FTP 포트: 21
FTP 아이디: insightmcrm
FTP 비밀번호: dlstkdlxm79!
```

---

## 🚀 1단계: FTP 프로그램 설치 및 접속

### FileZilla 사용 (권장)

**다운로드**: https://filezilla-project.org/download.php?type=client

**접속 설정:**
1. FileZilla 실행
2. 상단 메뉴: **[파일] → [사이트 관리자]**
3. **[새 사이트]** 클릭
4. 다음 정보 입력:

```
프로토콜: FTP - 파일 전송 프로토콜
호스트: yourdomain.cafe24.com
포트: 21
암호화: 명시적 FTP over TLS 필요시만 사용
로그온 유형: 일반
사용자: your_ftp_id
비밀번호: your_ftp_password
```

5. **[연결]** 클릭

**접속 성공 시 화면:**
```
왼쪽: 내 컴퓨터 (로컬)
오른쪽: Cafe24 서버 (원격)
```

---

## 📂 2단계: Cafe24 서버 디렉토리 구조 확인

FTP 접속 후 오른쪽 창에서 확인:

```
/home/your_cafe24_id/
├── public_html/          # 웹루트 (여기에 www 폴더 내용 업로드)
├── logs/                 # Cafe24 로그 (자동 생성)
├── www/                  # public_html 심볼릭 링크
└── (여기에 laravel 폴더 생성)
```

**중요:** `laravel/` 폴더는 `public_html`과 **같은 레벨**에 위치해야 합니다!

---

## 📤 3단계: 파일 업로드

### 3-1. laravel 폴더 업로드

1. 로컬(왼쪽 창)에서: `cafe24-deploy/laravel/` 폴더 선택
2. 원격(오른쪽 창)에서: `/home/your_cafe24_id/` 위치로 이동
3. **드래그 앤 드롭** 또는 **우클릭 → 업로드**
4. 업로드 시간: 약 5-10분 (파일 크기에 따라)

**업로드 진행 상황:**
```
파일 전송 중... 189개 파일
vendor/ 폴더: 약 50MB (가장 오래 걸림)
app/, config/, database/ 등: 각 1-2분
```

### 3-2. www 폴더 내용 업로드

1. 로컬(왼쪽 창)에서: `cafe24-deploy/www/` **폴더 안의 파일들** 선택
   - index.php
   - .htaccess
   - favicon.ico
   - robots.txt

2. 원격(오른쪽 창)에서: `/home/your_cafe24_id/public_html/` 위치로 이동

3. **드래그 앤 드롭**으로 업로드

**최종 구조:**
```
/home/your_cafe24_id/
├── public_html/
│   ├── index.php        ✅
│   ├── .htaccess        ✅
│   ├── favicon.ico      ✅
│   └── robots.txt       ✅
└── laravel/
    ├── app/             ✅
    ├── bootstrap/       ✅
    ├── config/          ✅
    ├── database/        ✅
    ├── vendor/          ✅
    └── .env.cafe24      ✅
```

---

## 🗄️ 4단계: MySQL 데이터베이스 생성 (필수!)

### Cafe24 관리자 페이지에서:

1. **[나의 서비스 관리] → [호스팅 관리]**
2. **[부가서비스] → [MySQL]** 또는 **[데이터베이스 관리]**
3. **[MySQL 추가]** 또는 **[새 데이터베이스 생성]**

### DB 생성 정보 입력:

```
데이터베이스명: mcrm_db (또는 원하는 이름)
사용자명: mcrm_user (또는 원하는 이름)
비밀번호: 강력한_비밀번호_입력
```

**중요한 정보 메모하기:**
```
DB_HOST: localhost (대부분의 경우)
DB_PORT: 3306
DB_DATABASE: mcrm_db
DB_USERNAME: mcrm_user
DB_PASSWORD: 입력한_비밀번호
```

### phpMyAdmin 접속:

1. Cafe24 관리자에서 **[phpMyAdmin 바로가기]** 클릭
2. 위에서 생성한 사용자명/비밀번호로 로그인
3. 왼쪽에서 생성한 DB 선택 (예: mcrm_db)

**현재 상태:** 빈 데이터베이스 (테이블 없음)

---

## ⚙️ 5단계: .env 파일 설정

### 5-1. .env 파일 생성

FTP에서:

1. `/home/your_cafe24_id/laravel/` 폴더로 이동
2. `.env.cafe24` 파일 찾기
3. **우클릭 → 이름 바꾸기** → `.env`로 변경

또는:

4. `.env.cafe24` 다운로드
5. 로컬에서 `.env`로 이름 변경 후 편집
6. 다시 업로드

### 5-2. .env 파일 필수 수정 항목

텍스트 에디터(VS Code, 메모장 등)로 열기:

```env
# 앱 설정
APP_NAME="M-CRM"
APP_ENV=production
APP_KEY=                            # ⚠️ 나중에 생성
APP_DEBUG=false                     # 운영환경에서는 false!
APP_URL=https://yourdomain.cafe24.com    # ⚠️ 실제 도메인으로 변경

# 데이터베이스 설정 (4단계에서 메모한 정보 입력)
DB_CONNECTION=mysql
DB_HOST=localhost                   # Cafe24는 대부분 localhost
DB_PORT=3306
DB_DATABASE=mcrm_db                 # ⚠️ 생성한 DB명
DB_USERNAME=mcrm_user               # ⚠️ 생성한 사용자명
DB_PASSWORD=your_strong_password    # ⚠️ 설정한 비밀번호

# 세션 설정
SESSION_DRIVER=database             # 파일보다 안정적
SESSION_LIFETIME=120

# CORS 설정
SANCTUM_STATEFUL_DOMAINS=yourdomain.cafe24.com    # ⚠️ 도메인 변경
SESSION_DOMAIN=.cafe24.com
```

### 5-3. APP_KEY 생성

**방법 1: SSH 접속 가능한 경우 (권장)**
```bash
ssh your_cafe24_id@yourdomain.cafe24.com
cd laravel
php artisan key:generate
```

**방법 2: SSH 불가능한 경우 (로컬에서 생성)**
```bash
# 로컬 컴퓨터에서
cd /Users/soona/Documents/인사이트/2025_MCRM/cafe24-deploy/laravel
php artisan key:generate --show
```

출력 예:
```
base64:abc123def456...
```

이 값을 복사하여 `.env` 파일의 `APP_KEY=` 뒤에 붙여넣기

**수정 후 FTP로 다시 업로드**

---

## 🗃️ 6단계: 데이터베이스 테이블 생성

### 방법 1: SSH 사용 (빠르고 안전)

```bash
ssh your_cafe24_id@yourdomain.cafe24.com
cd /home/your_cafe24_id/laravel

# 마이그레이션 실행 (테이블 생성)
php artisan migrate --force

# 초기 데이터 삽입
php artisan db:seed --force
```

**성공 메시지:**
```
Migration table created successfully.
Migrating: 2025_09_14_040330_create_users_table
Migrated: 2025_09_14_040330_create_users_table (50.23ms)
...
```

### 방법 2: SSH 불가능한 경우 (phpMyAdmin 사용)

#### 2-1. 로컬에서 SQL 파일 생성

```bash
cd /Users/soona/Documents/인사이트/2025_MCRM/mcrm-backend
php artisan migrate --pretend > migration.sql
```

#### 2-2. phpMyAdmin에서 Import

1. Cafe24 phpMyAdmin 접속
2. 왼쪽에서 생성한 DB 선택 (mcrm_db)
3. 상단 **[Import]** 탭 클릭
4. **[파일 선택]** → migration.sql 선택
5. **[실행]** 클릭

#### 2-3. 시더 데이터 삽입

```bash
# 로컬에서 SQL export
php artisan db:seed --class=ChannelCategorySeeder
# 해당 데이터를 SQL로 export하여 phpMyAdmin에서 import
```

**또는 직접 SQL 실행:**

phpMyAdmin의 SQL 탭에서 실행:

```sql
-- 채널 카테고리 데이터 삽입
INSERT INTO channel_categories (code, name, color, sort_order, active, created_at, updated_at) VALUES
('online', '온라인', '#2196F3', 1, 1, NOW(), NOW()),
('offline', '오프라인', '#FF9800', 2, 1, NOW(), NOW()),
('db', 'DB', '#4CAF50', 3, 1, NOW(), NOW());

-- 관리자 계정 생성 (비밀번호: password)
INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES
('관리자', 'admin@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANgwT8gZT4u', '슈퍼관리자', NOW(), NOW());
```

---

## 🔐 7단계: 파일 권한 설정

### SSH 접속 가능한 경우:

```bash
ssh your_cafe24_id@yourdomain.cafe24.com
cd /home/your_cafe24_id/laravel

# storage 폴더 권한
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# 소유자 변경 (필요시)
chown -R your_cafe24_id:your_cafe24_id storage
chown -R your_cafe24_id:your_cafe24_id bootstrap/cache
```

### FTP 사용하는 경우:

FileZilla에서:

1. `laravel/storage` 폴더 **우클릭**
2. **[파일 권한]** 선택
3. **755** 입력 또는 체크박스로 설정:
   ```
   ☑ 읽기  ☑ 쓰기  ☑ 실행  (소유자)
   ☑ 읽기  ☐ 쓰기  ☑ 실행  (그룹)
   ☑ 읽기  ☐ 쓰기  ☑ 실행  (공개)
   ```
4. **[하위 디렉터리에 재귀적으로 적용]** 체크
5. **[확인]**

**동일하게 적용:**
- `laravel/bootstrap/cache` 폴더도 755 권한 설정

---

## ✅ 8단계: 테스트

### 8-1. API 연결 테스트

브라우저에서 접속:

```
https://yourdomain.cafe24.com/api/health
```

**성공 응답:**
```json
{
  "status": "ok",
  "database": "connected",
  "timestamp": "2025-11-24T05:30:00Z"
}
```

**실패 시 확인:**
- 500 Error → `.env` 파일 확인, storage 권한 확인
- Database Error → DB 정보 확인
- 404 Error → .htaccess 파일 확인

### 8-2. 로그 확인

FTP로 접속:
```
/home/your_cafe24_id/laravel/storage/logs/laravel.log
```

다운로드하여 에러 메시지 확인

### 8-3. 데이터베이스 연결 확인

phpMyAdmin에서 테이블 목록 확인:
```
✅ users
✅ leads
✅ tickets
✅ appointments
✅ visits
✅ channel_categories
✅ channel_category_mappings
... (총 26개 테이블)
```

---

## 🎨 9단계: 프론트엔드 배포

### 옵션 1: Vercel 배포 (권장, 무료)

1. GitHub 저장소 생성
2. 프론트엔드 코드 push
3. Vercel (https://vercel.com) 가입
4. **[Import Project]** 클릭
5. GitHub 저장소 선택
6. 환경 변수 설정:
   ```
   NEXT_PUBLIC_API_URL=https://yourdomain.cafe24.com/api
   ```
7. **[Deploy]** 클릭

**배포 완료:** https://your-project.vercel.app

### 옵션 2: Cafe24에 Static Export

```bash
cd /Users/soona/Documents/인사이트/2025_MCRM/m-crm-project
npm run build
npm run export
```

생성된 `out/` 폴더를 FTP로 업로드:
```
out/ → /home/your_cafe24_id/public_html/app/
```

접속: `https://yourdomain.cafe24.com/app`

---

## 🐛 트러블슈팅

### 문제 1: 500 Internal Server Error

**원인:**
- APP_KEY 미설정
- storage 폴더 권한 문제
- .env 파일 오류

**해결:**
```bash
# SSH로 접속
cd /home/your_cafe24_id/laravel

# 캐시 클리어
php artisan config:clear
php artisan cache:clear

# 권한 재설정
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# 로그 확인
tail -n 50 storage/logs/laravel.log
```

### 문제 2: Database Connection Error

**체크리스트:**
```
☐ DB가 생성되었는가?
☐ .env의 DB 정보가 정확한가?
☐ DB 사용자 권한이 있는가?
☐ DB_HOST가 localhost인가?
```

**테스트:**
```bash
php artisan migrate:status
```

### 문제 3: CORS 에러 (프론트엔드에서)

**.env 확인:**
```env
SANCTUM_STATEFUL_DOMAINS=yourdomain.cafe24.com,your-frontend.vercel.app
```

**config/cors.php 확인:**
```php
'allowed_origins' => [
    'https://your-frontend.vercel.app',
    'https://yourdomain.cafe24.com',
],
```

수정 후:
```bash
php artisan config:cache
```

---

## 📝 체크리스트

배포 완료 확인:

```
☐ 1. FTP 접속 성공
☐ 2. laravel 폴더 업로드 완료
☐ 3. www 폴더 내용 → public_html 업로드 완료
☐ 4. MySQL DB 생성 완료
☐ 5. .env 파일 생성 및 수정 완료
☐ 6. APP_KEY 생성 및 설정 완료
☐ 7. 마이그레이션 실행 완료 (테이블 생성)
☐ 8. 시더 실행 완료 (초기 데이터)
☐ 9. storage 폴더 권한 755 설정 완료
☐ 10. /api/health 테스트 성공
☐ 11. phpMyAdmin에서 테이블 확인
☐ 12. 프론트엔드 배포 완료
☐ 13. CORS 설정 확인
```

---

## 🎯 배포 소요 시간

- FTP 업로드: 10-15분
- DB 생성 및 설정: 5분
- .env 설정: 5분
- 마이그레이션: 2분
- 테스트: 5분
- **총 소요 시간: 약 30-40분**

---

## 📞 추가 지원

**Cafe24 고객센터:**
- 전화: 1544-7772
- 이메일: help@cafe24.com
- 채팅: Cafe24 관리자 페이지 우측 하단

**자주 묻는 질문:**
- SSH 접속: 일부 요금제만 지원 (확인 필요)
- PHP 버전: Cafe24 관리자에서 변경 가능 (PHP 8.2+ 선택)
- 메모리 제한: php.ini 수정 필요 시 고객센터 문의

---

**배포 성공을 기원합니다! 🚀**
