# 프로젝트 To-Do 리스트

## 🔴 High Priority (즉시 구현 필요)

### 채널 카테고리화 시스템
- [ ] [CHANNEL-01] 채널 카테고리 데이터 모델 설계 (Option B - 별도 매핑 테이블)
  - **온라인 (Online)**: 네이버 키워드 광고, 네이버 플레이스 광고, 네이버 파워 콘텐츠 광고, GDN 광고, YOUTUBE 광고 등
  - **오프라인 (Offline)**: 오프라인광고, 간판, 소개 등
  - **DB (Database)**: 메타광고 (Facebook/Instagram)
  - 마이그레이션 파일: `2025_11_14_XXXXXX_create_channel_categories_table.php`
  - Seeder: `ChannelCategorySeeder.php`

- [ ] [CHANNEL-02] 백엔드 채널 카테고리 API
  - ChannelManagementController 생성
  - 채널 CRUD API (생성, 조회, 수정, 삭제, 활성화/비활성화)
  - ChannelPivotController에 카테고리별 집계 로직 추가
  - 파일: `/mcrm-backend/app/Http/Controllers/Api/ChannelManagementController.php`
  - 파일: `/mcrm-backend/app/Http/Controllers/Api/ChannelPivotController.php`

- [ ] [CHANNEL-03] 프론트엔드 카테고리 필터 UI
  - 채널 피벗 페이지에 카테고리 필터 추가
  - 카테고리별 성과 요약 카드
  - 카테고리별 색상 구분 (온라인: 파란색, 오프라인: 주황색, DB: 녹색)
  - 파일: `/m-crm-project/src/app/dashboards/channel-pivot/page.tsx`

- [ ] [CHANNEL-04] 채널 관리 페이지 구현 ⭐ NEW
  - 채널 목록 조회 (카테고리별 필터)
  - 채널 추가/수정/삭제 다이얼로그
  - 채널 활성화/비활성화 토글
  - 권한 제어 (슈퍼관리자, 마케터만 접근)
  - 파일: `/m-crm-project/src/app/settings/channels/page.tsx`
  - GNB 메뉴 추가: `/m-crm-project/src/components/SideNav.tsx`

- [ ] [CHANNEL-05] 리드 생성 시 카테고리 자동 매핑
  - UTM source 기반 자동 카테고리 할당
  - 수동 리드 생성 시 카테고리 선택 UI
  - 파일: `/m-crm-project/src/app/leads/page.tsx`

### 개발명세서 미구현 항목 (High Priority)

- [ ] [SPEC-01] 리드 자동 배정 로직
  - 라운드로빈 또는 워크로드 기반 배정
  - 가용 상담사 필터링 (active=true)
  - 고가치 리드 우선 배정
  - 파일: `/mcrm-backend/app/Http/Controllers/Api/LeadController.php`
  - 명세서 위치: 4.2절 (Line 424-440)

- [ ] [SPEC-02] 예약 캘린더 뷰 완성
  - 캘린더 뷰 렌더링 (월/주/일)
  - 예약 CRUD 기능
  - No-show 처리
  - 매출 입력
  - 파일: `/m-crm-project/src/app/appointments/page.tsx`
  - 명세서 위치: 6.1절 (Line 928-933)

- [ ] [SPEC-03] Lead 모델 전화번호 마스킹
  - primary_phone 필드 마스킹 처리
  - PIPA 컴플라이언스 준수
  - 파일: `/mcrm-backend/app/Models/Lead.php` (Line 83-84)
  - 명세서 위치: 9.1절 (Line 1621-1629)

- [ ] [SPEC-04] 홈 대시보드 실시간 KPI
  - 오늘의 신규 리드
  - 오늘의 예약
  - 이번 달 매출
  - 일별 리드 추이 차트 (선 그래프)
  - 상담사별 처리 현황 차트 (바 차트)
  - 파일: `/m-crm-project/src/app/page.tsx`
  - 명세서 위치: 6.1절 (Line 859-869)

## 🟡 Medium Priority (2-4주 내)

### 개발명세서 미구현 항목 (Medium Priority)

- [ ] [SPEC-05] LeadScoringService 독립 클래스
  - LeadController 내부 메서드를 독립 Service로 분리
  - 파일: `/mcrm-backend/app/Services/LeadScoringService.php` (신규 생성)
  - 명세서 위치: 4.2절 (Line 391-422)

- [ ] [SPEC-06] ChannelAttributionService 생성
  - Multi-touch 어트리뷰션
  - First/Last-touch 분석
  - 다채널 기여도 분석
  - 파일: `/mcrm-backend/app/Services/ChannelAttributionService.php` (신규 생성)
  - 명세서 위치: 2.1절 (Line 103)

- [ ] [SPEC-07] SLA 기준 명세서 정합성
  - 현재 60분 → 120분(2시간) 기준으로 변경
  - 우선순위별 SLA 차등 적용
  - 파일: `/mcrm-backend/app/Jobs/CheckSlaViolations.php`
  - 명세서 위치: 4.3절 (Line 458-461)

- [ ] [SPEC-08] 리드 중복 제거 고도화
  - 카카오톡 ID 매칭 추가
  - 다채널 식별자 우선순위 로직
  - 파일: `/mcrm-backend/app/Models/Lead.php` (Line 80-81)

- [ ] [SPEC-09] 스케줄 작업 시간 조정
  - SendAppointmentReminders: 매시간 → 오전 9시
  - SendRebookingSuggestions: 매일 → 매주 월요일 오전 10시
  - 파일: `/mcrm-backend/app/Console/Kernel.php`

## 🟢 Low Priority (장기 개선)

- [ ] [SPEC-10] 카카오톡 알림톡 연동
  - 예약 리마인더 자동 발송
  - 상담 완료 감사 메시지
  - 재예약 제안 메시지
  - 명세서 위치: 11.1절 (Line 1995-1999)

- [ ] [SPEC-11] AI 기반 리드 스코어링
  - 머신러닝 모델로 전환 확률 예측
  - 과거 데이터 학습
  - 명세서 위치: 11.1절 (Line 1990-1994)

- [ ] [SPEC-12] Multi-touch Attribution
  - 다채널 기여도 분석
  - First-touch vs Last-touch
  - 명세서 위치: 7.4절

## 기존 항목

### 백엔드 (BE)
- [x] [BE-01] 방문 이벤트 수집 API
- [x] [BE-02] 리드 생성 API
- [x] [BE-03] 티켓 API
- [x] [BE-04] 예약 API
- [ ] [BE-05] 광고 비용 수집 API
- [x] [BE-06] 상담자별 성과 API (상담수 및 내원전환율 포함)
- [ ] [BE-07] 감사 로그 API

### 프론트엔드 (FE)
- [x] [FE-01] AppShell 골격(Topbar + Sidebar + Main)
- [x] [FE-02] 반응형 GNB & 모바일 드로어
- [x] [FE-03] 리드 리스트 테이블
- [x] [FE-04] 리드 상세(고객 360)
- [x] [FE-05] 티켓 인박스
- [x] [FE-06] 예약 캘린더
- [x] [FE-07] 대시보드 – 퍼널
- [x] [FE-08] 대시보드 – 채널별 피벗
- [x] [FE-09] 대시보드 – 상담자별 성과
- [x] [FE-10] 광고 채널 연동 뷰

### 보안 및 규정 (SEC)
- [ ] [SEC-01] 개인정보 마스킹/보기권한 (백엔드 지원)

### 품질 보증 (QA)
- [ ] [QA-01] 접근성/품질 체크리스트
