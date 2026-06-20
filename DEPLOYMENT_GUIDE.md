3# 🚀 M-CRM 배포 가이드

> **작성일**: 2025-11-25
> **프로젝트**: Insight M-CRM
> **프론트엔드**: Vercel (GitHub 연동 자동배포 — `git push` 한 번으로 끝)
> **백엔드**: Cafe24 (FTP 배포)

---

## 📋 목차

1. [배포 구조](#배포-구조)
2. [프론트엔드 배포 (Vercel)](#프론트엔드-배포-vercel)
3. [백엔드 배포 (Cafe24)](#백엔드-배포-cafe24)
4. [실제 사용 예시](#실제-사용-예시)
5. [FileZilla 설정](#filezilla-설정)
6. [문제 해결](#문제-해결)

---

## 🏗️ 배포 구조

```
┌─────────────────────────────────────────┐
│  프론트엔드 (Vercel)                     │
│  https://insight-mcrm-xto4fa1t6-       │
│  soona046-gmailcoms-projects.vercel.app │
└──────────────┬──────────────────────────┘
               │ API 요청
               ↓
┌─────────────────────────────────────────┐
│  백엔드 (Cafe24)                         │
│  https://insightmcrm.mycafe24.com       │
│                                          │
│  ├── Laravel API ✅                      │
│  ├── MariaDB (23 tables) ✅             │
│  └── 광고 API (Naver/Meta/Google) ✅    │
└─────────────────────────────────────────┘
```

### 로컬 개발 환경

```
/Users/soona/Documents/인사이트/2025_MCRM/
├── mcrm-backend/          ⭐ 백엔드 개발 폴더
├── m-crm-project/         ⭐ 프론트엔드 개발 폴더
└── cafe24-deploy/         ❌ 사용 안 함 (1회용 패키지)
```

---

## 🌐 프론트엔드 배포 (Vercel)

### ✅ GitHub 연동 자동배포 (2026-06-21부터)

`m-crm-project`는 이제 자체 GitHub 레포(`soona046-design/m-crm-project`, private)를 가지고 있고, 이 레포가 Vercel `insight-mcrm` 프로젝트에 **Git 연동**되어 있습니다. 더 이상 `vercel --prod`를 수동으로 실행할 필요 없이 **`main` 브랜치에 push만 하면 Vercel이 자동으로 빌드·배포**합니다.

> ⚠️ **구조 변경 주의**: `m-crm-project`는 부모 레포(`2025_MCRM`)의 하위 폴더이면서 동시에 자기 자신의 독립된 GitHub 레포를 가진 이중 구조입니다(정식 git submodule은 아니고, 폴더 안에 별도 `.git`이 존재). 부모 레포에는 여전히 폴더 내용이 추적되지만(`m-crm-project` 변경 시 부모 레포에서도 "modified content" 로 보임), **배포 트리거는 오직 `m-crm-project` 자체 레포의 push**입니다. 부모 레포만 push해서는 Vercel이 배포되지 않습니다.

### 배포 명령어

Claude에게 요청:
```
"Claude, 프론트엔드 배포해줘"
또는
"Claude, UI 수정했으니까 배포해줘"
```

### Claude가 실행하는 명령어

```bash
cd /Users/soona/Documents/인사이트/2025_MCRM/m-crm-project
git add .
git commit -m "사용자가 요청한 수정 내용"
git push origin main
```

### 결과

✅ **push 직후 Vercel이 자동으로 빌드 시작, 1-2분 후 배포 완료!**

**Production URL (고정 별칭):**
```
https://insight-mcrm.vercel.app
```

**Git 연동 전용 별칭** (이 브랜치 배포만 확인하고 싶을 때):
```
https://insight-mcrm-git-main-soona046-gmailcoms-projects.vercel.app
```

### 배포 확인

```bash
vercel ls insight-mcrm        # 최근 배포 목록/상태 확인
vercel inspect <배포 URL>      # 특정 배포 상세 확인
```
- 브라우저에서 Production URL 접속하여 확인
- Console 창에서 에러 없는지 확인
- (참고) `vercel inspect`의 status가 `UNKNOWN`으로 보여도 실제 사이트가 200으로 응답하면 정상 배포된 것 — CLI 표시상의 사소한 이슈일 뿐

### (예전 방식, 더 이상 기본 사용 안 함)

급할 때 로컬에서 강제로 한 번 더 배포하고 싶으면 `vercel --prod`도 여전히 동작하지만, 평소엔 git push만으로 충분합니다.

---

## 🔧 백엔드 배포 (Cafe24)

### ✅ Git + SFTP 자동 업로드 (2026-06-21부터)

백엔드는 **Git으로 버전 관리** + **SFTP로 서버 배포**. `deploy-backend.sh`(lftp 기반)와 `.env.deploy`(SFTP 자격증명, git에는 절대 커밋 안 됨 — `.gitignore` 처리)를 구축해서, 코드 파일 업로드는 Claude가 직접 스크립트로 자동 처리합니다.

**사용 명령:**
```
"Claude, 백엔드 배포해줘"
```
Claude가 실행하는 것:
```bash
cd /Users/soona/Documents/인사이트/2025_MCRM/mcrm-backend
git add . && git commit -m "..." && git push origin <branch>
cd ..
./deploy-backend.sh \
  mcrm-backend/app/Http/Controllers/Api/XxxController.php /insightmcrm/laravel/app/Http/Controllers/Api/XxxController.php \
  ...(수정된 파일만 반복)
```

⚠️ **자동화 범위 밖**: DB 스키마 변경(컬럼/enum 추가 등)은 SSH로 `php artisan migrate` 실행이 안 되는 환경이라 여전히 웹 기반 마이그레이션 스크립트(`/insightmcrm/www/`에 1회용 PHP 업로드 → 브라우저 실행 → 즉시 삭제) 방식을 그대로 사용합니다. Laravel 캐시(`config:cache`/`route:cache`)도 현재 운영에는 적용 안 돼 있어 캐시 클리어 없이 파일 교체만으로 반영됩니다(`bootstrap/cache`에 `routes-v7.php`/`config.php` 없는 것으로 확인, 2026-06-21).

**최초 1회 설정 (`.env.deploy` 생성, 사용자가 직접 — 비밀번호가 대화 기록에 남지 않도록):**
```bash
cd /Users/soona/Documents/인사이트/2025_MCRM
printf 'CAFE24_SFTP_HOST=insightmcrm.mycafe24.com\n' > .env.deploy
printf 'CAFE24_SFTP_PORT=22\n' >> .env.deploy
printf 'CAFE24_SFTP_USER=insightmcrm\n' >> .env.deploy
printf 'CAFE24_SFTP_PASSWORD=%s\n' '실제비밀번호' >> .env.deploy
chmod 600 .env.deploy
```

### (예전 방식) Git 커밋 후 FileZilla로 수동 업로드

**사용자 요청:**
```
"Claude, 백엔드 커밋해줘"
```

**Claude 실행 명령어:**
```bash
cd /Users/soona/Documents/인사이트/2025_MCRM/mcrm-backend
git add .
git commit -m "사용자가 요청한 수정 내용"
git push origin main  # GitHub에 백업
```

### Step 2: SFTP 업로드 (Claude가 `deploy-backend.sh`로 자동 실행)

`.env.deploy`가 설정돼 있으면 Claude가 직접 업로드하고 결과를 알려줍니다. 사용자가 FileZilla를 열 필요 없음.

```bash
./deploy-backend.sh \
  mcrm-backend/app/Http/Controllers/Api/LeadController.php /insightmcrm/laravel/app/Http/Controllers/Api/LeadController.php
```

(`.env.deploy`가 없거나 자격증명이 만료된 경우에만 아래 FileZilla 수동 업로드로 대체)

### Step 3: 배포 확인

```
https://insightmcrm.mycafe24.com/api/엔드포인트
```

브라우저나 Postman에서 API 동작 확인

---

## 📝 실제 사용 예시

### 예시 1: 프론트엔드 UI 수정

**시나리오:** 대시보드 색상 변경

```
사용자: "대시보드 UI 색상 바꿨어. 배포해줘"

Claude:
✅ Git 커밋 완료: "대시보드 UI 색상 변경"
✅ m-crm-project 레포로 push 완료 → Vercel이 자동으로 빌드 시작
✅ 배포 완료!
   https://insight-mcrm.vercel.app
   1-2분 후 확인하세요!
```

**작업 시간:** 2분

---

### 예시 2: 백엔드 API 수정

**시나리오:** LeadController에 새 메서드 추가

```
사용자: "LeadController에 새 메서드 추가했어. 배포해줘"

Claude:
✅ Git 커밋 완료: "LeadController에 새 메서드 추가"
✅ GitHub 푸시 완료!
✅ deploy-backend.sh로 SFTP 업로드 완료 (바이트 일치 확인)

테스트해볼까요?
   https://insightmcrm.mycafe24.com/api/leads
```

**작업 시간:** 1-2분 (자동 업로드)

---

### 예시 3: 여러 파일 동시 수정

**시나리오:** 3개 컨트롤러 수정

```
사용자: "리드, 티켓, 예약 컨트롤러 3개 수정했어. 배포해줘"

Claude:
✅ Git 커밋 완료: "리드, 티켓, 예약 컨트롤러 수정"
✅ GitHub 푸시 완료!
✅ deploy-backend.sh로 3개 파일 SFTP 업로드 완료
```

**작업 시간:** 1-2분 (자동 업로드)

---

## 🛠️ FileZilla 설정 (예전 방식, `.env.deploy` 없을 때만 사용)

### 초기 설정 (1회만)

1. **FileZilla 실행**

2. **사이트 관리자 열기**
   - 메뉴: `파일` > `사이트 관리자`
   - 단축키: `Ctrl+S` (Windows) / `Cmd+S` (Mac)

3. **새 사이트 추가**
   - 버튼: `새 사이트` 클릭
   - 이름: `Cafe24 - Insight MCRM`

4. **연결 정보 입력**
   ```
   프로토콜: SFTP - SSH File Transfer Protocol
   호스트: insightmcrm.mycafe24.com
   포트: 22
   로그온 유형: 일반
   사용자: insightmcrm
   비밀번호: [Cafe24 비밀번호]
   ```

5. **연결 테스트**
   - 버튼: `연결` 클릭
   - 첫 연결 시 호스트 키 신뢰 확인 → `예` 선택

6. **책갈피 추가 (빠른 접근)**
   - 원격 경로를 `/insightmcrm/laravel/` 로 이동
   - 메뉴: `책갈피` > `책갈피 추가`
   - 이름: `Laravel Root`

### 빠른 업로드 방법

**방법 1: 드래그 앤 드롭**
```
좌측 (로컬) → 우측 (서버) 드래그
```

**방법 2: 우클릭 메뉴**
```
파일 선택 → 우클릭 → "업로드"
```

**방법 3: 더블클릭**
```
파일 더블클릭 → 자동 업로드
```

### 유용한 팁

**타임스탬프 비교:**
- 설정: `전송` > `파일이 있을 때` > `타임스탬프가 다르면 덮어쓰기`
- 효과: 수정된 파일만 자동 감지

**전송 대기열:**
- 여러 파일을 드래그해서 대기열에 추가
- 한 번에 업로드 시작

**동기화 탐색:**
- 메뉴: `보기` > `동기화 탐색`
- 효과: 로컬과 서버 폴더 동시 이동

---

## ❓ 문제 해결

### Vercel 배포 실패

**증상:** `vercel --prod` 명령어 실패

**해결:**
```bash
# 1. 빌드 로그 확인
vercel logs

# 2. 로컬 빌드 테스트
npm run build

# 3. TypeScript 에러 무시 설정 확인
# next.config.js에 다음 설정이 있는지 확인:
typescript: {
  ignoreBuildErrors: true,
}
```

---

### FTP 연결 실패

**증상:** FileZilla 연결 안 됨

**해결:**
```
1. 프로토콜이 SFTP인지 확인
2. 포트 번호가 22인지 확인
3. Cafe24 관리자 페이지에서 SSH 활성화 확인
4. 비밀번호 재확인
```

---

### 파일 업로드 후 반영 안됨

**증상:** 파일 업로드했는데 API 변경 안 됨

**해결:**
```bash
# SSH로 접속해서 확인
ssh insightmcrm@insightmcrm.mycafe24.com
cd /insightmcrm/laravel/app/Http/Controllers/Api
ls -la LeadController.php  # 파일 타임스탬프 확인
cat LeadController.php | head -20  # 내용 확인

# Laravel 캐시 클리어 (필요시)
/usr/local/bin/php82 artisan config:clear
/usr/local/bin/php82 artisan cache:clear
```

---

### 데이터베이스 마이그레이션 실패 (SSH 불가 시)

**증상:** SSH 접속 불가로 `php artisan migrate` 실행 불가

**상황:**
- Cafe24 일부 요금제에서 SSH 접속 미지원
- 새로운 마이그레이션 파일을 서버에 적용해야 함
- 외래 키 중복 오류 (errno: 121) 발생 가능

**해결: 웹 기반 마이그레이션 스크립트**

1. **스크립트 작성** (로컬에서 생성)
```php
<?php
// create-tables.php
echo "<pre>";
try {
    chdir("/insightmcrm/laravel");
    require_once "/insightmcrm/laravel/vendor/autoload.php";
    $app = require_once "/insightmcrm/laravel/bootstrap/app.php";
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    // 신규 테이블만 생성 (기존 테이블 건드리지 않음)
    $exists = \Illuminate\Support\Facades\DB::select("SHOW TABLES LIKE 'new_table'");
    if (empty($exists)) {
        \Illuminate\Support\Facades\DB::statement("
            CREATE TABLE `new_table` (
                -- 테이블 정의
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "✅ new_table created\n";
    }

    // migrations 테이블에 기록
    \Illuminate\Support\Facades\DB::table('migrations')->insert([
        'migration' => '2025_xx_xx_create_new_table',
        'batch' => \Illuminate\Support\Facades\DB::table('migrations')->max('batch') + 1
    ]);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
echo "</pre>";
```

2. **FTP 업로드**
   - 파일: `create-tables.php`
   - 위치: `/insightmcrm/www/`

3. **브라우저 실행**
   ```
   http://insightmcrm.mycafe24.com/create-tables.php
   ```

4. **보안: 즉시 삭제**
   - 실행 완료 후 FTP로 파일 삭제 필수!

**주의사항:**
- 전체 마이그레이션 실행 시 외래 키 중복 오류 발생 가능
- **신규 테이블만 직접 생성**하는 방식 권장
- 테이블 존재 여부 먼저 확인 (`SHOW TABLES LIKE`)

**참고:** buglog.md - Bug #8 참조 (2025-12-28)

---

### API CORS 에러

**증상:** 프론트엔드에서 API 호출 시 CORS 에러

**해결:**
```php
// mcrm-backend/config/cors.php 확인
'allowed_origins' => [
    'https://insight-mcrm-xto4fa1t6-soona046-gmailcoms-projects.vercel.app',
],
```

수정 후 `config/cors.php` 파일을 FTP로 업로드

---

## 📊 배포 비교표

| 항목 | 프론트엔드 (Vercel) | 백엔드 (Cafe24) |
|------|-------------------|----------------|
| **방식** | GitHub 연동 완전 자동 (`git push`만 하면 끝) | Git + `deploy-backend.sh`(SFTP) 자동 업로드 |
| **소요 시간** | 1-2분 | 1-2분 |
| **사용자 작업** | ✅ 없음 | ✅ 없음 (최초 1회 `.env.deploy` 설정만) |
| **Claude 작업** | Git 커밋 + push (`m-crm-project` 자체 레포로) | Git 커밋 + push + `deploy-backend.sh`로 SFTP 업로드 |
| **롤백** | `vercel rollback` 또는 이전 커밋으로 git revert 후 push | 이전 커밋 파일을 `deploy-backend.sh`로 재업로드 |
| **환경 변수** | Vercel 대시보드 | `mcrm-backend/.env` (서버에 직접, 배포 스크립트 대상 아님) |
| **DB 스키마 변경** | (해당 없음) | 여전히 수동: 웹 마이그레이션 스크립트 1회용 업로드→실행→삭제 |

---

## 🔐 서버 정보

### Cafe24 서버

```
FTP/SSH 주소: insightmcrm.mycafe24.com
FTP/SSH 아이디: insightmcrm
FTP 포트: 21
SSH 포트: 22
Laravel 경로: /insightmcrm/laravel/
웹 루트: /insightmcrm/www/
```

### Vercel 프로젝트

```
프로젝트명: insight-mcrm
Production URL (고정 별칭): https://insight-mcrm.vercel.app
Git 연동: soona046-design/m-crm-project (main 브랜치)
대시보드: https://vercel.com/soona046-gmailcoms-projects/insight-mcrm
```

### GitHub 저장소

```
백엔드 (+ 모노레포 루트):  https://github.com/soona046-design/2025_MCRM.git
  브랜치: main, feature/date-range-filtering(작업중)

프론트엔드 (독립 레포, private): https://github.com/soona046-design/m-crm-project.git
  브랜치: main(배포 대상), feature/date-range-filtering, backup-before-cafe24-static
  ⚠️ Vercel 배포는 이 레포의 main push에 의해 트리거됨
```

---

## 📚 참고 자료

- [Next.js 배포 문서](https://nextjs.org/docs/deployment)
- [Vercel CLI 문서](https://vercel.com/docs/cli)
- [Laravel 배포 가이드](https://laravel.com/docs/deployment)
- [FileZilla 사용 가이드](https://wiki.filezilla-project.org/)

---

## 🎯 Quick Reference

### 자주 사용하는 명령어

```bash
# 프론트엔드 로컬 개발
cd m-crm-project
npm run dev

# 백엔드 로컬 개발
cd mcrm-backend
composer run dev

# 프론트엔드 배포 (m-crm-project 자체 레포로 push → Vercel 자동 배포)
cd m-crm-project
git add .
git commit -m "메시지"
git push origin main

# 백엔드 배포 (커밋 + push + SFTP 자동 업로드)
cd ../mcrm-backend
git add .
git commit -m "메시지"
git push origin <branch>   # 모노레포(2025_MCRM) 쪽 origin
cd ..
./deploy-backend.sh <로컬파일1> <원격경로1> [<로컬파일2> <원격경로2> ...]
```

### 요청 템플릿

```
✅ "Claude, 프론트엔드 배포해줘"
✅ "Claude, 백엔드 배포해줘"
✅ "Claude, UI 수정했으니까 배포해줘"
✅ "Claude, API 추가했어, 배포해줘"
```

---

**작성일**: 2025-11-25
**최종 수정**: 2026-06-21 — 백엔드 배포를 FileZilla 수동 업로드에서 `deploy-backend.sh`(lftp+SFTP) 자동 업로드로 전환(DB 스키마 변경은 여전히 웹 마이그레이션 스크립트 수동 방식 유지)
**버전**: 2.1.0
