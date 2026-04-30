# M-CRM 작업 현황 정리 — 2026-04-30

## 프로젝트 요약

**M-CRM** (Medical CRM) — 치과/의료 클리닉 리드 관리 시스템  
- **프론트엔드**: Next.js 14 + Material-UI → Vercel 배포 (https://insight-mcrm.vercel.app)  
- **백엔드**: Laravel 12 API → Cafe24 서버 (https://insightmcrm.mycafe24.com)  
- **현재 브랜치**: `feature/date-range-filtering`

---

## 완료된 작업 전체 이력

### 2025-11-20 — 채널 피벗 대시보드 카테고리화

| # | 작업 | 상태 |
|---|------|------|
| Bug #1 | Laravel `groupBy()` 중첩 구조 문제 해결 | ✅ |
| Bug #2 | Lead Status Enum 한글↔영문 불일치 수정 | ✅ |
| Bug #3 | `visits.channel_category_id` 컬럼 오류 수정 | ✅ |
| Bug #4 | ROAS 필드 누락 — 백엔드 계산 로직 추가 | ✅ |
| Bug #5 | Laravel 서버 캐싱 문제 해결 (서버 재시작) | ✅ |

### 2025-11-14 — 채널 카테고리화 시스템 구축
- 채널 카테고리 분류 체계 구현 (online/offline/db)
- 피벗 대시보드 개선

### 2025-12-15 — 채널-진료 매트릭스 시스템

| # | 작업 | 상태 |
|---|------|------|
| Bug #6 | `date-fns` / `@mui/x-date-pickers` 호환성 문제 → 네이티브 input으로 교체 | ✅ |
| 기능 | `treatment_types`, `channel_treatment_records`, `marketing_insights` 테이블 설계 | ✅ |
| 기능 | 채널-진료 매트릭스 백엔드 API 구현 | ✅ |

### 2025-12-20 — 브랜치 버전 불일치 수정
- Bug #7: feature 브랜치 파일이 구버전으로 롤백되어 main 기준으로 복구
- 엑셀 업로드 기능 추가 (템플릿 다운로드 + 데이터 파싱)

### 2025-12-28 — Cafe24 배포 마이그레이션 문제 해결
- Bug #8: `errno: 121` 외래 키 중복 오류 → 신규 테이블만 직접 생성하는 `create-new-tables.php` 방식 채택
- Cafe24에 3개 테이블 성공적으로 배포

### 2025-12-29 — Leads API 연동 개선

| 작업 | 상태 |
|------|------|
| 사이드바 로그아웃 버튼 추가 | ✅ |
| Leads 삭제 기능 API 연동 (개별/일괄) | ✅ |
| 새 문의 등록 후 첫 페이지 이동 + 최신순 정렬 | ✅ |
| Bug #9: 한글 상태값 → 영문 enum 변환 매핑 추가 | ✅ |
| 401/422/500 에러 처리 및 상세 로깅 | ✅ |
| memo 컬럼 미존재 임시 우회 (memo 필드 제거) | ✅ (임시) |
| `add-memo-column.php` 스크립트 생성 | ✅ |
| `check-users.php` 스크립트 생성 | ✅ |

### 현재 브랜치 미커밋 변경사항 (feature/date-range-filtering)

**`mcrm-backend/app/Http/Controllers/Api/LeadController.php`**
- `utm_source` / `utm_campaign` null 반환값 `'N/A'` → `''` 변경
- `sla_status` null 반환값 `'N/A'` → `'-'` 변경
- `update()` validation에 `sometimes` 추가 (PATCH 방식 대응)
- `status` 필드도 `sometimes`로 변경 (부분 업데이트 허용)
- `utm_source` 수정 시 연결된 Visit 자동 업데이트 로직 추가

**`mcrm-backend/app/Models/Lead.php`**
- `getStatusAttribute()` 매핑 보완 — `new`, `pending`, `rejected` 추가
- 레거시 한글 상태값도 유지

---

## 현재 시스템 동작 상태

### 정상 작동
- ✅ 로그인/로그아웃
- ✅ 새 문의 등록 (이름, 전화번호, 상태, 점수)
- ✅ 문의 목록 조회 (최신순 정렬)
- ✅ 문의 개별/일괄 삭제
- ✅ 채널 피벗 대시보드
- ✅ 채널-진료 매트릭스 (백엔드 구현 완료)

### 알려진 미완성 항목
- ⚠️ **memo 컬럼**: Cafe24 서버에 없어 현재 API 요청에서 제외 중 → `add-memo-column.php` 실행 필요
- ⚠️ **담당자(assignee) 매핑**: `assignee_name` (string) 입력되지만 DB에 저장 안 됨 → `assigned_user_id` (UUID) 방식으로 개선 필요
- ⚠️ **진료 서비스(treatment)**: 저장 로직 미구현
- ⚠️ **문의 날짜(inquiry_date)**: 별도 컬럼 없어 저장 안 됨

---

## 핵심 파일 경로

### 프론트엔드 (Next.js)
```
m-crm-project/src/
├── app/
│   ├── leads/page.tsx               # 문의 목록 + 등록 (핵심 파일)
│   ├── login/page.tsx
│   └── dashboards/
│       ├── channel-pivot/page.tsx
│       └── channel-treatment-matrix/page.tsx
├── components/
│   ├── Sidebar.tsx                  # 로그아웃 버튼 포함
│   └── LeadListTable.tsx            # 삭제 기능 포함
├── contexts/AuthContext.tsx
└── lib/axios.ts
```

### 백엔드 (Laravel)
```
mcrm-backend/
├── app/Http/Controllers/Api/
│   ├── LeadController.php           # 현재 수정 중
│   └── ChannelPivotController.php
├── app/Models/Lead.php              # 현재 수정 중
├── routes/api.php
└── database/migrations/
```

### 배포 스크립트 (Cafe24 FTP용)
```
/
├── add-memo-column.php              # leads 테이블에 memo 컬럼 추가
└── check-users.php                  # 사용자 데이터 확인
```

---

## 배포 정보

| 항목 | 내용 |
|------|------|
| 프론트엔드 | https://insight-mcrm.vercel.app |
| 백엔드 | https://insightmcrm.mycafe24.com |
| 로컬 백엔드 | http://localhost:8000 |
| 로컬 프론트엔드 | http://localhost:3000 |

### 테스트 계정
| 역할 | ID | PW |
|------|----|----|
| 슈퍼관리자 | admin | admin123!@# |
| 상담매니저 | counselor1 | counselor123!@# |
| 지점관리자 | manager_seoul | manager123!@# |

---

## 환경 실행 명령어

```bash
# 백엔드
cd mcrm-backend
php artisan serve

# 프론트엔드
cd m-crm-project
npm run dev
```

---

## 참고 문서
- `buglog.md` — 버그 이력 (Bug #1~#9, 100% 해결)
- `DEPLOYMENT_GUIDE.md` — Cafe24 배포 가이드
- `CLAUDE.md` — 프로젝트 전반 가이드
