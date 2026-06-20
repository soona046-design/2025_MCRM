# M-CRM TODO

_최종 업데이트: 2026-06-20_

---

## 🔥 긴급 — 퍼널/채널 데이터 정합성 버그 (2026-06-20 코드+실DB 확인)

> 운영 DB 직접 조회로 확인: `visits` 10건 존재하지만 `leads`(2건) 전부 `source_visit_id NULL` → 채널피벗/퍼널 집계 결과 0건. 아래 1·2가 직접 원인.

- [x] **[BE-CRITICAL] 새 리드 등록 시 utm_source가 검증 규칙에 없어서 저장 안 됨** — 2026-06-20 코드 수정 완료, **배포 전(로컬 커밋만)**
  파일: `mcrm-backend/app/Http/Controllers/Api/LeadController.php` (`store()`, `update()`)
  `store()` 검증 규칙에 `utm_source` 추가 + Visit 생성/연결 로직 추가(`update()`와 동일 패턴), `ChannelCategoryHelper`로 `channel_category` 자동 분류도 같이 적용. 새 리드 생성 시 `source_visit_id`가 한 번도 set 안 되던 버그(=latest_visit_id만 저장되고 실제 귀속 컬럼은 항상 NULL)도 같이 수정.
  로컬에서 검증: 신규 리드(`utm_source: naver`) 등록 → `source_visit_id` 연결 + `channel_category: online` 자동 분류 + `/api/dashboards/channel-pivot` 집계에 정상 반영 확인.
  **남은 작업**: Cafe24 FTP로 `LeadController.php` 배포 필요 (운영은 현재 이 파일 기준 더 오래된 버전 실행 중 — `status` 필드 required/sometimes 차이로 이미 한 번 확인됨)

- [ ] **[BE-CRITICAL] ChannelPivotController가 INNER JOIN으로 Visit 없는 리드를 전부 누락**
  파일: `mcrm-backend/app/Http/Controllers/Api/ChannelPivotController.php:63` (leads), `:96-97` (appointments)
  `Lead::join('visits', 'leads.source_visit_id', '=', 'visits.visit_id')` — INNER JOIN이라 `source_visit_id NULL`인 리드/예약이 결과에서 완전히 제외됨 (실측: JOIN 결과 0건)
  **해결 방향**: 1번 해결 전까지는 최소 `leftJoin`으로 바꿔서 미연결 리드도 "채널 미확인"으로 집계에 포함

- [ ] **[FE] 퍼널 대시보드 "계약완료" 카운트 로직이 죽어있음**
  파일: `m-crm-project/src/app/funnel/page.tsx:190-205`
  `localStorage.getItem('mcrm_leads')`에서만 읽음(실 데이터는 API 소스라 항상 비어있음) + `lead.status === '계약완료' || lead.status === 'closed'` 비교(백엔드엔 두 값 다 존재하지 않음, 실제 enum은 new/contacted/pending/converted/rejected)
  → 계약완료 수치가 항상 0 또는 무의미
  **해결 방향**: 백엔드 API 응답(`leads` state)에서 `status === 'converted'` 기준으로 직접 카운트

- [ ] **[BE+FE] 채널 카테고리(온라인/오프라인/DB) 분류 로직 이중 구현 + 키워드 불일치**
  백엔드: `mcrm-backend/app/Helpers/ChannelCategoryHelper.php:41-93`
  프론트: `m-crm-project/src/app/channel-pivot/page.tsx:185-207`
  확인된 충돌 사례: "재방문"(백엔드 db / 프론트 offline), "지인추천"(백엔드 offline / 프론트 db), "이벤트"·"거리홍보"·"지나가다"(백엔드 offline / 프론트 online 기본값)
  **해결 방향**: 분류 로직을 백엔드 단일 소스로 통합, 프론트는 API가 내려주는 `category_code`/`category_name`만 사용

- [ ] **[DB] `channel_category_mappings`에 비활성(`active=0`) 매핑이 방치됨**
  실측: `naver`, `facebook`, `instagram` 매핑이 `active=0` — 관리 화면에서 활성화해도 반영 안 되는 죽은 설정으로 보일 수 있음. 우연히 규칙기반 fallback과 결과가 같아서 지금은 안 드러남
  **해결 방향**: 불필요하면 삭제, 필요하면 active=1로 정리

- [ ] **[조사 필요] `visits.channel_category`에 'direct' → 'offline'로 저장된 건 4건**
  `ChannelCategoryHelper`의 규칙대로면 'direct'는 매칭 키워드가 없어 기본값 'online'이어야 하는데 실제론 'offline'으로 저장돼 있음. Helper를 거치지 않고 다른 경로/과거 로직으로 들어간 데이터로 추정 — 원인 미확인
  **해결 방향**: `VisitController`가 저장 시점에 실제로 Helper를 호출하는지 코드 추적 필요

- [ ] **[참고] 상태값 의미 붕괴 — "미팅완료"·"계약완료"가 둘 다 'converted'로 매핑**
  파일: `m-crm-project/src/app/leads/page.tsx` statusMap
  저장 후 두 단계를 구분할 수 없음. 백엔드 enum에 별도 값 추가 검토 필요

- [ ] **[조사 필요] `Lead.php` 모델에 status를 영문→한글로 되돌리는 accessor가 있는데 운영엔 미배포 상태로 보임**
  파일: `mcrm-backend/app/Models/Lead.php:67-82` (`getStatusAttribute()`)
  로컬에서 리드 생성 시 응답이 `"status":"신규"`로 옴(accessor 적용) vs 운영 API 직접 호출 시 `"status":"new"`로 옴(accessor 미적용) — 운영이 이 모델의 더 오래된 버전을 실행 중인 것으로 추정.
  이게 `ChannelPivotController`에서 전에 발견한 "상위 집계는 영문 status, 세부 집계는 한글 status 체크" 불일치의 근본 원인일 가능성 높음(Eloquent 모델 경유 시엔 accessor가 적용돼 한글로 보이고, Query Builder/raw 비교 시엔 영문 그대로라 동시에 혼재).
  **주의**: `Lead.php`를 그대로 운영에 FTP 배포하면 모든 리드 API 응답의 status가 갑자기 한글로 바뀌어 프론트엔드 표시/필터링이 깨질 수 있음 — 배포 전 영향 범위 확인 필요

---

## 🔥 긴급 — 즉시 처리

- [ ] **[BE] 미커밋 변경사항 커밋**  
  `LeadController.php` + `Lead.php` 수정 내용이 미커밋 상태  
  커밋 후 Cafe24 FTP 배포 필요

- [x] **[인프라] Cafe24 서버 memo 컬럼 추가** — 2026-06-20 완료, API로 정상 저장 확인

- [ ] **[인프라] Cafe24 사용자 데이터 확인**  
  1. FTP로 `check-users.php` 업로드  
  2. 브라우저 실행 → 사용자 없으면 UserSeeder 실행 필요  
  3. 확인 후 즉시 파일 삭제

---

## ⭐ 이번 주 목표

- [ ] **[FE] 담당자(assignee) 매핑 시스템 구현** (30분)  
  현재: `assignee_name` (string) 입력 → DB에 미저장  
  목표: 사용자 목록 API 연동 → UUID 기반 `assigned_user_id` 저장  
  파일: `m-crm-project/src/app/leads/page.tsx`

- [ ] **[BE+FE] inquiry_date 컬럼 추가** (20분)  
  BE: `leads` 테이블에 `inquiry_date date nullable` 추가 (웹 스크립트 방식)  
  FE: API 요청 body에 `inquiry_date` 포함

- [ ] **[브랜치] feature/date-range-filtering → main 머지**  
  로컬 브랜치 작업 내용 main에 병합 후 Vercel 재배포

---

## 📌 중요 — 기능 완성도

- [ ] **[SPEC-01] 리드 자동 배정 로직**  
  라운드로빈/워크로드 기반 배정, 가용 상담사 필터링 (active=true)  
  파일: `mcrm-backend/app/Http/Controllers/Api/LeadController.php`

- [ ] **[SPEC-02] 예약 캘린더 뷰 완성**  
  캘린더 뷰 렌더링 (월/주/일), 예약 CRUD, No-show 처리, 매출 입력  
  파일: `m-crm-project/src/app/appointments/page.tsx`

- [ ] **[SPEC-03] Lead 모델 전화번호 마스킹**  
  `primary_phone` 필드 마스킹 처리 (PIPA 컴플라이언스)  
  파일: `mcrm-backend/app/Models/Lead.php`

- [ ] **[SPEC-04] 홈 대시보드 실시간 KPI**  
  오늘의 신규 리드/예약, 이번 달 매출, 일별 리드 추이 차트  
  파일: `m-crm-project/src/app/page.tsx`

- [ ] **[BE-05] 광고 비용 수집 API**  
  `POST /api/webhooks/ads/{platform}` — 배치 동기화 (15분), 재시도 큐

- [ ] **[BE-07] 감사 로그 API**  
  `GET /api/audit?actor&target&date` — 조회 필터, CSV 내보내기, RBAC

- [ ] **[SEC-01] 개인정보 마스킹/보기 권한**  
  전화/이메일 기본 마스킹, 보기 버튼 클릭 시 권한 확인 후 전체 표시

---

## 📊 마케팅 대시보드 — 데이터 구조 개선

- [ ] **[DB] 인라인 편집 DB 저장 (localStorage → DB)**  
  `marketing_pivot_overrides` 테이블 생성  
  `PUT /api/pivot/overrides` API 구현  
  프론트엔드 localStorage 제거 후 DB 저장으로 전환

- [ ] **[DB] raw_metrics 테이블 생성**  
  광고 API 원본 데이터 저장, upsert 정책 구현  
  플랫폼별 (`naver`, `google`, `meta`) 표준화 매핑

- [ ] **[BE] 증감률 계산 기능**  
  전기 대비 ROI/CTR/CVR 증감률 반환  
  프론트엔드: ↑12.5% (녹색) / ↓-5.3% (빨강) Chip 표시

- [ ] **[BE] CTR / CVR 지표 추가** (ChannelPivotController)  
  `CTR = (clicks/impressions) × 100`, `CVR = (leads/clicks) × 100`

---

## 🗓 일반 — 시간 날 때

- [ ] **[DB] 진료 서비스(treatment) 데이터 구조화**  
  현재: memo 텍스트에 임시 저장  
  목표: `lead_treatments` 중간 테이블 (Lead ↔ TreatmentType 다대다)  
  예상 시간: 1시간

- [ ] **[SPEC-05] LeadScoringService 독립 클래스**  
  LeadController 내부 메서드 → `app/Services/LeadScoringService.php` 분리

- [ ] **[SPEC-06] ChannelAttributionService 생성**  
  Multi-touch 어트리뷰션, First/Last-touch 분석

- [ ] **[SPEC-07] SLA 기준 명세서 정합성**  
  현재 60분 → 120분(2시간) 기준으로 변경  
  파일: `mcrm-backend/app/Jobs/CheckSlaViolations.php`

- [ ] **[TEST] 전체 플로우 통합 테스트** (30분)  
  - [ ] 로그인 → 새 문의 등록 → 목록 확인  
  - [ ] 담당자 선택 → 저장 → DB 확인  
  - [ ] memo 입력 → 저장 → DB 확인  
  - [ ] 문의 날짜 입력 → 저장 → DB 확인  
  - [ ] 개별/일괄 삭제 → DB 확인  
  - [ ] 페이지네이션 동작 확인  
  - [ ] 채널 피벗 대시보드 정상 동작

- [ ] **[DOCS] buglog.md 업데이트**  
  memo 컬럼 추가 및 담당자 매핑 구현 내용 Bug #10, #11로 기록

- [ ] **[BE] Visit 없는 Lead 처리 검증**  
  `LeadController.php` update()에 추가된 Visit 자동 생성 로직 실제 테스트

---

## 🔮 장기 로드맵

- [ ] **[SPEC-10] 카카오톡 알림톡 연동**  
  예약 리마인더, 상담 완료 감사, 재예약 제안 자동 발송

- [ ] **[SPEC-11] AI 기반 리드 스코어링**  
  머신러닝 모델로 전환 확률 예측, 과거 데이터 학습

- [ ] **[SPEC-12] Multi-touch Attribution**  
  다채널 기여도 분석, First-touch vs Last-touch

- [ ] **[QA-01] 접근성/품질 체크리스트**  
  Lighthouse 접근성 ≥90, 모바일 Safari/Chrome 교차검증  
  단축키 f/s/n/a/? 동작 확인

---

## ✅ 완료 이력

### 2025-12-29
- [x] 사이드바 로그아웃 버튼 추가
- [x] Leads 삭제 API 연동 (개별/일괄)
- [x] 새 문의 등록 후 첫 페이지 이동 + 최신순 정렬
- [x] Bug #9: 한글 상태값 → 영문 enum 변환
- [x] 401/422/500 에러 처리
- [x] memo 필드 임시 제거 (500 에러 긴급 우회)
- [x] `add-memo-column.php` / `check-users.php` 스크립트 생성

### 2025-12-28
- [x] Bug #8: Cafe24 마이그레이션 외래 키 오류 → 신규 테이블만 직접 생성
- [x] Cafe24에 `treatment_types`, `channel_treatment_records`, `marketing_insights` 배포

### 2025-12-15~20
- [x] 채널-진료 매트릭스 시스템 백엔드 구현
- [x] Bug #6: date-fns 호환성 → 네이티브 input 교체
- [x] Bug #7: 브랜치 버전 불일치 수정

### 2025-11-14~20
- [x] 채널 카테고리화 시스템 (온라인/오프라인/DB) 구축
- [x] ChannelCategoryHelper 클래스 구현
- [x] 채널 피벗 카테고리별 성과 집계 UI
- [x] Bug #1~#5: 피벗 대시보드 버그 전체 해결

### 2025-11-11
- [x] 퍼널 대시보드 개선 (localStorage 수동 캠페인 통합)
- [x] 리드 상태 관리 시스템 개선 (누적 카운팅)
- [x] 리드 테이블 UI/UX 개선 (8열로 최적화, 상태 색상 코딩)
- [x] GNB 접기/펼치기 토글 구현

---

## 빠른 재시작 가이드

```bash
# 1. 백엔드 실행
cd /Users/soona/Documents/인사이트/2025_MCRM/mcrm-backend
php artisan serve

# 2. 프론트엔드 실행
cd /Users/soona/Documents/인사이트/2025_MCRM/m-crm-project
npm run dev

# 3. 배포 상태 확인
# https://insight-mcrm.vercel.app/login
# ID: admin / PW: admin123!@#
```
