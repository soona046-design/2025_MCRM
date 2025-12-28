3# 🚀 M-CRM 배포 가이드

> **작성일**: 2025-11-25
> **프로젝트**: Insight M-CRM
> **프론트엔드**: Vercel (자동 배포)
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

### ✅ 완전 자동 배포!

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
vercel --prod
```

### 결과

✅ **1-2분 후 자동으로 배포 완료!**

**Production URL:**
```
https://insight-mcrm-xto4fa1t6-soona046-gmailcoms-projects.vercel.app
```

### 배포 확인

- Vercel CLI가 배포 URL 표시
- 브라우저에서 URL 접속하여 확인
- Console 창에서 에러 없는지 확인

---

## 🔧 백엔드 배포 (Cafe24)

### 📦 Git + FTP 하이브리드 방식

백엔드는 **Git으로 버전 관리** + **FTP로 서버 배포**

### Step 1: Git 커밋 (Claude가 실행)

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

### Step 2: FTP 업로드 (사용자가 실행)

**Claude가 알려주는 정보:**
```
📤 FileZilla로 다음 파일을 업로드하세요:

파일: app/Http/Controllers/Api/LeadController.php
위치: /insightmcrm/laravel/app/Http/Controllers/Api/
```

**사용자 작업:**

1. **FileZilla 실행**
2. **Cafe24 서버 접속**
   - 호스트: `insightmcrm.mycafe24.com`
   - 사용자: `insightmcrm`
   - 포트: `22` (SFTP)
3. **원격 경로 이동:** `/insightmcrm/laravel/`
4. **수정된 파일 업로드:**
   - 로컬: `mcrm-backend/app/Http/Controllers/Api/LeadController.php`
   - 서버: `/insightmcrm/laravel/app/Http/Controllers/Api/LeadController.php`
   - 드래그 앤 드롭으로 덮어쓰기
5. **완료!** ✅

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
✅ Vercel 배포 시작...
✅ 배포 완료!
   https://insight-mcrm-xto4fa1t6-soona046-gmailcoms-projects.vercel.app
   1-2분 후 확인하세요!
```

**작업 시간:** 2분

---

### 예시 2: 백엔드 API 수정

**시나리오:** LeadController에 새 메서드 추가

```
사용자: "LeadController에 새 메서드 추가했어. 커밋해줘"

Claude:
✅ Git 커밋 완료: "LeadController에 새 메서드 추가"
✅ GitHub 푸시 완료!

📤 FileZilla로 다음 파일을 업로드하세요:

파일: app/Http/Controllers/Api/LeadController.php
위치: /insightmcrm/laravel/app/Http/Controllers/Api/

업로드 후 알려주시면 테스트 도와드릴게요!

사용자: "업로드 완료!"

Claude:
✅ 테스트해볼까요?
   https://insightmcrm.mycafe24.com/api/leads
   브라우저에서 확인해보세요!
```

**작업 시간:** 3-5분 (FTP 업로드 포함)

---

### 예시 3: 여러 파일 동시 수정

**시나리오:** 3개 컨트롤러 수정

```
사용자: "리드, 티켓, 예약 컨트롤러 3개 수정했어. 커밋해줘"

Claude:
✅ Git 커밋 완료: "리드, 티켓, 예약 컨트롤러 수정"
✅ GitHub 푸시 완료!

📤 FileZilla로 다음 3개 파일을 업로드하세요:

1. app/Http/Controllers/Api/LeadController.php
2. app/Http/Controllers/Api/TicketController.php
3. app/Http/Controllers/Api/AppointmentController.php

모두 → /insightmcrm/laravel/app/Http/Controllers/Api/ 폴더에 업로드
```

**작업 시간:** 5-7분 (FTP 업로드 포함)

---

## 🛠️ FileZilla 설정

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
| **방식** | 완전 자동 | Git + FTP 하이브리드 |
| **소요 시간** | 1-2분 | 3-5분 |
| **사용자 작업** | ✅ 없음 | FTP 업로드 |
| **Claude 작업** | Git + Vercel 배포 | Git 커밋 + 파일 목록 안내 |
| **롤백** | `vercel rollback` | FTP로 이전 파일 재업로드 |
| **환경 변수** | Vercel 대시보드 | `.env` 파일 (FTP) |

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
Production URL: https://insight-mcrm-xto4fa1t6-soona046-gmailcoms-projects.vercel.app
대시보드: https://vercel.com/soona046-gmailcoms-projects/insight-mcrm
```

### GitHub 저장소

```
백엔드: https://github.com/soona046-design/2025_MCRM.git
브랜치: main
백업 브랜치: backup-before-cafe24-static
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

# Vercel 배포
vercel --prod

# Git 커밋 (Claude가 실행)
git add .
git commit -m "메시지"
git push origin main
```

### 요청 템플릿

```
✅ "Claude, 프론트엔드 배포해줘"
✅ "Claude, 백엔드 커밋해줘"
✅ "Claude, UI 수정했으니까 배포해줘"
✅ "Claude, API 추가했어, 커밋해줘"
```

---

**작성일**: 2025-11-25
**최종 수정**: 2025-11-25
**버전**: 1.0.0
