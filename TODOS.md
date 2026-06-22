# M-CRM TODO

_최종 업데이트: 2026-06-22_

---

## 🟡 보류 — 추후 검토 (2026-06-20)

- [ ] **[조사 필요] "상담" 단계가 실제 Ticket 레코드가 아닌 Lead.status로 집계됨**
  `ChannelPivotController.php`의 문의/상담/예약/계약 카운트는 모두 같은 `leads` 테이블을 status 값으로 누적 필터링한 것 (61-88행에서 `$leads` 한 번만 조회, 별도 `Ticket` 테이블 조회 없음). `tickets_count`라는 필드명 때문에 실제 상담 티켓 수처럼 보이지만 실제론 `status ∈ {contacted, scheduled, converted}`인 리드 수일 뿐.
  실제 `Ticket` 테이블 레코드 존재 여부 기준으로 맞출지(= 상담원이 실제로 티켓을 만들었는지) 여부는 보류 — 현재는 리드 상태값 통일 작업(6단계 퍼널 정렬)만 완료된 상태.
  파일: `mcrm-backend/app/Http/Controllers/Api/ChannelPivotController.php`

---

## 🔥 긴급 — 퍼널/채널 데이터 정합성 버그 (2026-06-20 코드+실DB 확인)

> 운영 DB 직접 조회로 확인: `visits` 10건 존재하지만 `leads`(2건) 전부 `source_visit_id NULL` → 채널피벗/퍼널 집계 결과 0건. 아래 1·2가 직접 원인.

- [x] **[BE-CRITICAL] 새 리드 등록 시 utm_source가 검증 규칙에 없어서 저장 안 됨** — 2026-06-20 코드 수정, **2026-06-21 Cafe24 배포 완료**
  파일: `mcrm-backend/app/Http/Controllers/Api/LeadController.php` (`store()`, `update()`)
  `store()` 검증 규칙에 `utm_source` 추가 + Visit 생성/연결 로직 추가(`update()`와 동일 패턴), `ChannelCategoryHelper`로 `channel_category` 자동 분류도 같이 적용. 새 리드 생성 시 `source_visit_id`가 한 번도 set 안 되던 버그(=latest_visit_id만 저장되고 실제 귀속 컬럼은 항상 NULL)도 같이 수정.
  로컬에서 검증: 신규 리드(`utm_source: naver`) 등록 → `source_visit_id` 연결 + `channel_category: online` 자동 분류 + `/api/dashboards/channel-pivot` 집계에 정상 반영 확인.

- [x] **[BE-CRITICAL] ChannelPivotController가 INNER JOIN으로 Visit 없는 리드를 전부 누락** — 2026-06-20 코드 수정, **2026-06-21 Cafe24 배포 완료**
  파일: `mcrm-backend/app/Http/Controllers/Api/ChannelPivotController.php` (leads/appointments 쿼리)
  `leads`→`visits` join을 `leftJoin`으로 변경, `utm_source`를 `COALESCE(visits.utm_source, '채널 미확인')`으로 select해서 미연결 리드도 별도 채널로 집계됨. `appointments`→`visits` join도 동일하게 변경.
  로컬에서 검증: `source_visit_id=NULL`인 리드 생성 → `/api/dashboards/channel-pivot` 응답에 `"채널 미확인"` 항목으로 정상 집계 확인 (수정 전엔 결과에서 완전히 사라졌음).
  **함께 배포됨**: `Lead.php`(한글 변환 accessor 제거로 영문/한글 status 혼용 버그 해소), `routes/api.php`(퍼널 이탈 분석 API 라우트), `leads.status` enum에 `scheduled` 추가(웹 마이그레이션 스크립트로 적용, 운영 컬럼이 원래 `varchar(255)`였던 것도 함께 enum으로 정리됨).

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

## 🔥 긴급 — 광고비 연동 API 전부 깨짐 (2026-06-21 코드+.env 직접 확인)

> 채널피벗 대시보드 광고비/ROI가 항상 0 또는 더미값인 근본 원인. 보고받은 내용을 코드와 `.env` 대조로 직접 재확인함.

- [x] **[BE-CRITICAL] 네이버 검색광고 API 엔드포인트/헤더가 실제 스펙과 다름 (HTTP 404)** — 2026-06-21 수정 완료, 실제 자격증명으로 end-to-end 검증
  파일: `mcrm-backend/app/Services/Ads/NaverAdsApiService.php`, `config/ads.php`, `.env`
  공식 레포(`naver/searchad-apidoc`)의 php-sample/java-sample 코드를 직접 대조해서 수정:
  - `base_url` 기본값이 `https://api.naver.com/naver-searchad-api/v2`(완전히 잘못된 도메인+경로)였음 → `https://api.searchad.naver.com`로 수정 (`.env`의 `NAVER_ADS_BASE_URL`도 같이 수정)
  - 헤더 `X-CUSTOMER-ID` → `X-Customer`로 수정
  - 엔드포인트를 `/stats/campaign`(존재하지 않음) → `GET /stats`로 수정. `/stats`는 `ids`(캠페인/그룹 등 객체 ID) 파라미터가 필수라서, 먼저 `GET /ncc/campaigns`로 캠페인 목록을 가져온 뒤 캠페인별로 `/stats` 호출하도록 구조 변경
  - **실험으로 발견한 계정 제약**: `timeIncrement`(일별 분할) 파라미터를 보내면 `11001 지원하지 않는 기능입니다` 에러 — 이 계정에서는 일별 통계 분할이 막혀있음. 대신 `since=until=같은 날짜`로 날짜별로 반복 호출하는 방식으로 우회
  - 응답 파싱도 실제 응답 형태(`{"data":[{"id","impCnt","clkCnt","salesAmt","ccnt"}],"compTm",...}`)에 맞춰 재작성
  - `CostImport` 모델의 실제 `$fillable`(`platform`,`campaign_code`,`date`,`impressions`,`clicks`,`cost`)과 안 맞던 반환 키(`channel`,`campaign`)도 같이 수정 — 이전엔 API가 200을 받아도 `CostImport::create()`가 두 필드를 조용히 버렸을 것
  - 또한 `config('services.naver_ads.*')`(존재하지 않는 키, env 기본값으로 우연히 동작 중)를 `config('ads.naver.*')`로 통일해 Google/Meta와 일치시킴
  - **검증**: 실제 자격증명으로 `getAdCosts('2026-06-19','2026-06-20')` 호출 → 캠페인 3개(`인사이트`/`파워컨텐츠#1`/`플레이스`) × 2일 = 6건, 실제 노출/클릭/비용 데이터 정상 반환 확인
  - mockMode 미적용 + 매 호출 문제는 바로 아래 항목에서 해결됨

- [x] **[BE-CRITICAL] 채널피벗 페이지를 열 때마다 mock 설정과 무관하게 네이버 API를 실제로 호출** — 2026-06-22 수정 완료, Cafe24 배포 완료
  파일: `mcrm-backend/app/Services/Ads/NaverAdsApiService.php` (`getAdCosts()`)
  `getAdCosts()` 시작부에 `if ($this->mockMode) { return []; }` 체크를 직접 추가(호출부인 `ChannelPivotController::index()`를 바꾸는 대신 메서드 자체를 안전하게 만드는 방향 선택). 운영 `.env`는 `ADS_MOCK=false`라 mock 분기는 타지 않지만, 실호출 결과를 `Cache::remember()`로 15분 캐싱 추가해서 같은 기간을 반복 조회할 때(페이지 새로고침 등) 매번 네이버 서버를 다시 두드리지 않도록 함.

- [ ] **[BE] Google/Meta Ads 자격증명이 비어있음 (코드 구조 자체는 정상)**
  Google: `.env`의 `GOOGLE_ADS_CLIENT_ID`/`CLIENT_SECRET`/`REFRESH_TOKEN`/`DEVELOPER_TOKEN`/`CUSTOMER_ID`가 전부 빈 값 → 토큰 발급 단계에서 `Missing required parameter: refresh_token`으로 실패
  Meta: `.env`에 `META_ACCESS_TOKEN`/`AD_ACCOUNT_ID`/`APP_ID`/`APP_SECRET` 자체가 없음(빈 값이 아니라 줄 자체가 없음)
  `GoogleAdsClient.php`(OAuth+GAQL), `MetaClient.php`(Graph API) 구현 자체는 구조적으로 문제 없어 보임 — 자격증명 발급은 사용자가 직접 해야 하는 부분
  **해결 방향**: 어떤 플랫폼 광고 계정 접근 권한을 갖고 있는지 먼저 확인 필요 (사용자 자격증명 발급 의존)

- [ ] **[DB] 광고비 데이터가 `cost_imports`/`ad_metrics` 두 테이블로 이원화**
  파일: `ChannelPivotController.php:44-52,116`(`CostImport`), `:136`(`AdMetric`, `MarketingStatsController`/`FetchAdStats` 커맨드에서도 사용)
  같은 컨트롤러 안에서 `CostImport`(플랫폼명 `'네이버'`/`'Facebook'` 문자열 키)와 `AdMetric`(코드 키)을 별도로 조회 — 어느 쪽이 진실 소스인지 불명확
  **해결 방향**: `ad_metrics`를 단일 소스로 통합, `cost_imports` 용도 정리 또는 폐기

- [x] **[BE] 채널명→광고 플랫폼 매핑 배열이 같은 파일에 4번 중복 하드코딩 + cost_imports.platform 표기 불일치로 비용이 항상 0** — 2026-06-21 수정 완료
  파일: `ChannelPivotController.php`, `NaverAdsApiService.php`
  네이버 API 연동을 고치고 나서야 드러난 버그: `$costImports->where('platform', $channel)`에서 `$channel`은 `visits.utm_source` 원본값(예: `naver`, 한글 `네이버`가 섞여 들어옴)인데, `NaverAdsApiService::getAdCosts()`는 `cost_imports.platform`에 한글 `'네이버'`를 저장하고 있어서 **절대 일치하지 않음** — 실데이터가 들어와도 화면엔 항상 ₩0으로 보임. 반면 `AdWebhookController`(웹훅으로 들어오는 비용)는 이미 `strtolower($platform)`로 영문 코드(`naver`/`google`/`meta`)를 쓰고 있었어서, `cost_imports.platform`의 올바른 컨벤션은 영문 코드였음이 확인됨.
  - `NaverAdsApiService::getAdCosts()`가 저장하는 `platform` 값을 `'네이버'` → `'naver'`로 수정 (`ad_metrics`/`AdWebhookController`와 동일한 컨벤션으로 통일)
  - `ChannelPivotController`의 4곳에 중복돼 있던 `$platformMapping = ['네이버' => 'naver', ...]` 배열을 `self::PLATFORM_MAPPING` 클래스 상수 하나로 통합(`naver`/`google`/`meta`/한글 표기 별칭 추가)하고, 비용 합산 쿼리(`$costImports->where('platform', ...)`) 4곳 모두 `$platformMapping[$channel] ?? $channel`로 변환해서 비교하도록 수정 (매핑 없는 값은 기존처럼 원본 그대로 비교해서 수동 입력 비용과의 호환성 유지)
  - 테스트 중 잘못된 컨벤션(`'네이버'`)으로 쌓인 임시 `cost_imports` 행 231건 로컬 DB에서 정리
  - **검증**: 실제 네이버 API 데이터를 가져와서 채널피벗 API 응답에 `"channel":"naver","cost":196378,"roi":-100` 식으로 비용이 정상 반영되는 것까지 확인
  - `channel_category_mappings` 테이블을 단일 소스로 활용하는 더 근본적인 통합은 여전히 미적용(아래 항목들과 연결된 더 큰 작업) — 이번엔 당장 비용이 0으로 보이는 문제만 스코프로 좁혀서 수정

- [x] **[FE] 채널피벗 상단 요약 카드("총 광고비" 등)가 채널 카테고리별 카드는 정상인데 0으로 표시** — 2026-06-21 수정 완료
  파일: `m-crm-project/src/app/channel-pivot/page.tsx`
  요약 카드(총 광고비/총 수익/평균 ROI/평균 전환율) 계산이 `pivotTableData`(캠페인 단위, `combinedData`)에서 `cost`/`revenue`를 합산하고 있었는데, 백엔드 `pivotTableData` 집계는 `cost_imports`를 `platform` + `campaign_code`(=utm_campaign)로 같이 매칭함 — `NaverAdsApiService`가 저장하는 실제 캠페인명("인사이트" 등)과 리드의 `utm_campaign` 값이 거의 일치하지 않아 캠페인 단위 비용은 거의 항상 0. 반면 `channelPerformance`(채널 단위, platform만 매칭)는 위 항목에서 이미 정상화됨.
  **해결**: 요약 카드 합산을 `response.data.channelPerformance`에서 직접 하도록 변경(`combinedData` 대신). 실제로 "총 광고비 ₩2,177,769"로 정상 표시되는 것 확인.
  **남은 캐벗**: 캠페인 단위(`pivotTableData`/"상세 성과" 탭의 캠페인별 비용)는 여전히 0으로 보일 수 있음 — `utm_campaign` vs 실제 광고 캠페인명 매칭 문제는 미해결 (별도 작업 필요) → 바로 아래 항목에서 해결함

- [x] **[BE] "상세 성과" 탭 캠페인별 비용이 utm_campaign↔실제 캠페인명 불일치로 항상 0** — 2026-06-21 수정 완료
  파일: `mcrm-backend/app/Http/Controllers/Api/ChannelPivotController.php` (pivotTableData 집계)
  실데이터 확인: `visits.utm_campaign`은 마케터가 임의로 입력하는 추적값(`"campaign_0"`, `"campaign_1"`...)인데, `cost_imports.campaign_code`는 네이버 실제 캠페인명(`"인사이트"`, `"파워컨텐츠#1"`, `"플레이스"`)이라 **두 값이 같을 일이 구조적으로 없음** — `->where('campaign_code', $campaign)` 정확매칭은 항상 0건. 현재 데이터로는 캠페인 단위 비용을 정확히 추적할 방법이 없음(실제 네이버 캠페인 ID를 클릭 추적 시점에 저장하는 인프라가 없음).
  **해결(근사치)**: 채널 단위 총비용을 먼저 계산해두고, 같은 채널 내 캠페인 그룹의 **리드 수 비율**로 분배(`channelTotalCost * (campaignLeads / channelTotalLeads)`) — 채널 내 캠페인별 비용 합 = 채널 총비용이 되도록 보장. 정확한 캠페인별 귀속은 아니지만 "항상 0"보다는 의미 있는 근사치.
  **검증**: "상세 성과" 탭에서 총비용 ₩2,169,375로 정상 표시되는 것 화면으로 확인.
  **진짜 근본 해결책(미적용)**: 네이버 광고 클릭 시 `nccCampaignId`를 추적 URL에 심어서 `visits`에 저장하는 인프라를 만들어야 정확한 캠페인 단위 귀속이 가능함 — SPEC 레벨 작업, 이번 스코프 밖.

---

## 🔥 긴급 — 즉시 처리

- [x] **[BE-CRITICAL] LeadController::store() 중복 탐색 로직이 phone/email 둘 다 없으면 임의 리드와 병합** — 2026-06-22 발견 및 수정 완료
  파일: `mcrm-backend/app/Http/Controllers/Api/LeadController.php` (`store()`, 62행 부근)
  `primary_phone`과 `email`이 둘 다 요청에 없으면 `where()` 클로저 안에 조건이 하나도 안 붙어 빈 WHERE가 되고, `->first()`가 테이블의 임의 리드(보통 첫 행)를 "기존 리드"로 오인해 그 리드에 강제로 병합(`update()`)되던 버그.
  **수정**: `$hasPhone`(빈 문자열도 false 처리) 또는 `$emailHash`가 실제로 있을 때만 중복 탐색 쿼리를 실행, 둘 다 없으면 `$existingLead = null`로 무조건 신규 생성 분기로 보냄.
  **검증**: (1) phone/email 없이 리드 생성 → 기존 리드 총 개수가 정상적으로 +1 증가(병합 안 됨) 확인. (2) 동일 `primary_phone`으로 두 번 생성 → 두 번째 요청이 첫 번째 `lead_id`로 정상 병합되는 기존 동작은 그대로 유지되는 것 확인(회귀 없음). 테스트 데이터 삭제 완료.



- [x] **[인프라] Cafe24 서버 memo 컬럼 추가** — 2026-06-20 완료, API로 정상 저장 확인

- [x] **[인프라] 백엔드 FTP 배포 자동화** — 2026-06-21 완료
  `deploy-backend.sh`(lftp 기반) + `.env.deploy`(SFTP 자격증명, gitignore 처리) 구축. `Claude, 백엔드 배포해줘"`로 파일 업로드까지 자동 처리 가능해짐 (DB 스키마 변경은 여전히 웹 스크립트 방식 필요).

- [x] **[인프라] Cafe24 사용자 데이터 확인** — 2026-06-22 확인 완료
  `check-users.php`를 `/insightmcrm/www/`에 업로드 → 브라우저(curl)로 실행 → **사용자 60명 정상 존재**(admin 포함, 전부 활성) 확인. UserSeeder 재실행 불필요. 확인 즉시 원격 파일 삭제(404 확인됨).

---

## ⭐ 이번 주 목표

- [x] **[FE] 담당자(assignee) 매핑 시스템 구현** — 2026-06-22 확인: 이미 구현 완료 상태였음
  `fetchUsers()`(`leads/page.tsx:590`)가 `/api/users`에서 실사용자 목록 조회, `handleAssigneeChange`가 `assigned_user_id`(UUID) 세팅, 저장 payload에 포함. 백엔드 `LeadController::store()/update()`도 `assigned_user_id` 검증·저장 + `assignee()` 관계로 `assignee_name` 응답에 반영.
  실제 로그인 토큰으로 리드 생성 API 호출해 `assigned_user_id` 정상 저장 확인(테스트 데이터 삭제 완료). 코드 변경 불필요했음.
  **별개로 발견된 버그**: `LeadController::store()`의 중복 리드 탐색 로직이 `primary_phone`/`email`을 둘 다 안 보내면 빈 WHERE 조건이 되어 테이블의 임의 리드(첫 번째 행)와 강제로 "병합"됨 — phone 없이 들어오는 외부 연동(웹훅 등)에서 발생 가능. 별도 TODO로 분리 필요.

- [x] **[BE+FE] inquiry_date 컬럼 추가** — 2026-06-22 완료 (로컬 적용, Cafe24 배포는 별도)
  BE: 마이그레이션 `2026_06_22_041127_add_inquiry_date_to_leads_table.php`로 `leads.inquiry_date date nullable` 추가 + `Lead.php` fillable/casts(date)에 추가 + `LeadController::store()/update()` validation(`nullable|date`)에 추가
  FE: `leads/page.tsx` 저장 payload에 `inquiry_date` 포함, `Lead` 인터페이스에 `inquiry_date?` 추가, 수정 모달 진입 시 (이전엔 항상 `created_at` 기반 placeholder였던) `lead.inquiry_date`를 우선 사용하도록 `handleEditLeadClick` 변경
  **검증**: API로 리드 생성(`inquiry_date:"2026-06-15"`) → 저장 확인, PUT으로 `"2026-06-18"`로 수정 → 정상 반영 확인. 테스트 데이터 삭제 완료.
  **남은 작업**: 로컬 마이그레이션만 적용됨 — Cafe24 운영 반영은 CLAUDE.md의 웹 스크립트 방식(`SHOW TABLES LIKE` 체크 후 컬럼 추가 + migrations 테이블 수동 갱신)으로 별도 배포 필요

- [x] **[브랜치] feature/date-range-filtering → main 머지** — 2026-06-22 확인: 머지 불필요, 브랜치 정리 완료
  `git diff main...feature/date-range-filtering`가 비어있고 `main..feature` 쪽에 고유 커밋이 0개 — 해당 브랜치는 이미 main의 조상 커밋이라 병합할 내용이 없었음. 사용자 확인 후 로컬+리모트(origin) 브랜치 삭제로 정리.

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
