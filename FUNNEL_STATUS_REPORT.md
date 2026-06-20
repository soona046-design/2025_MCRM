# M-CRM 퍼널/리드 상태 정합화 — 작업 보고서

> 작성일: 2026-06-21
> 브랜치: `feature/date-range-filtering`
> 관련 커밋: `0d90db5`, `b693fd4`, `1e90f34`(이전 작업) → `6871938`, `1fc6d62`, `be74075`, `cd6f9b5`(이번 세션)

---

## 1. 프로젝트 목적

M-CRM은 치과/의료 클리닉을 위한 리드 관리 CRM이다. 환자 유입부터 매출까지 전체 퍼널을 추적하는 것이 핵심 목표다:

```
노출(Impression) → 클릭(Click) → 문의(Inquiry) → 상담(Consultation) → 예약(Appointment) → 계약(Contract)
```

마케팅 비용(광고 노출/클릭) 대비 실제 매출(계약)까지 연결해서 채널별 ROI를 계산하고, 어느 단계에서 리드가 이탈하는지 파악해 상담/마케팅 전략을 개선하는 것이 이 시스템의 존재 이유다.

이번 작업 전까지는 **퍼널 단계 정의와 리드의 실제 상태값(`leads.status`)이 어긋나 있어서**, 채널피벗/퍼널 대시보드의 상담·예약·계약 수치가 부정확하거나 항상 0으로 나오는 문제가 있었다. 이번 세션은 이 정합성을 맞추고, 이탈 분석 기능을 추가하고, 배포 체계를 정리하는 데 집중했다.

---

## 2. 핵심 알고리즘 — 리드 상태 ↔ 퍼널 단계

### 2.1 상태값 모델 (현재)

`mcrm-backend/database/migrations/2026_06_20_141533_add_scheduled_to_leads_status_enum.php`로 `leads.status` enum이 6개 값으로 확정됨:

| DB 값 (영문, 저장값) | 한글 라벨 (화면 표시) | 퍼널 단계 | 비고 |
|---|---|---|---|
| `new` | 신규 | 문의 | 리드 생성 시 기본값 |
| `contacted` | 상담완료 | 상담 | |
| `scheduled` | 예약완료 | 예약 | 이번 세션에 신규 추가된 값 |
| `converted` | 계약완료 | 계약 | 최종 성공 상태 |
| `pending` | 보류 | (이탈 분기) | 퍼널 진행에서 제외 |
| `rejected` | 거절 | (이탈 분기) | 퍼널 진행에서 제외 |

**핵심 원칙**: API는 항상 영문 enum 원본값을 그대로 내려준다. 한글 변환은 프론트엔드 전담 (`m-crm-project/src/lib/leadStatus.ts`의 `STATUS_EN_TO_KR`/`STATUS_KR_TO_EN`).

> 이전에는 `Lead.php`의 `getStatusAttribute()` accessor가 서버에서 영문→한글로 자동 변환했는데, 이게 일부 컨트롤러(영문 비교)와 일부 코드(한글 비교)가 혼용되며 카운트가 항상 0이 되는 버그의 근본 원인이었다. 이 accessor는 제거했다.

### 2.2 퍼널 단계별 카운팅 — 누적(cumulative) 모델

`mcrm-backend/app/Http/Controllers/Api/ChannelPivotController.php`에서 채널/카테고리/캠페인별로 리드를 집계할 때, 각 단계는 "그 단계까지 **도달한 적이 있는** 리드 수"를 누적으로 계산한다 (현재 그 상태인 리드 수가 아님):

```
문의(leads)        = 전체 리드 수 (status 무관, new/pending/rejected 포함 전부)
상담(tickets)      = status ∈ {contacted, scheduled, converted}
예약(appointments) = status ∈ {scheduled, converted}
계약(contracts)    = status == converted
```

**왜 누적 모델인가**: 만약 "문의"를 `status==='new'`로만 한정하면, 리드가 상담완료로 넘어가는 순간 문의 카운트에서 빠지고 상담 카운트에 더해져 `상담 > 문의` 역전이 생길 수 있다. 퍼널은 항상 `문의 ≥ 상담 ≥ 예약 ≥ 계약` 순으로 줄어들어야 의미가 있으므로, 각 단계는 반드시 누적(이전 단계를 포함하는 상위집합)이어야 한다.

이 로직은 `ChannelPivotController.php` 내 4개 집계 블록(`channelPerformanceData`, `categoryPerformanceData`, `channelDetails`, `pivotTableData`)에 동일하게 적용되어 있다. 예전에는 이 4곳이 영문/한글 status를 혼용 비교해서 일부는 항상 0이 나오는 버그가 있었는데, accessor 제거 + 비교 기준 통일로 해결됨.

### 2.3 "신규" 상태와 "문의" 단계는 다른 개념

- **문의**(퍼널 단계) = 이벤트/흐름(flow) 지표. 누적 카운터라서 절대 줄어들지 않음.
- **신규**(리드 상태) = 재고/잔량(stock) 지표. "지금 이 순간 아직 아무도 손 안 댄 리드가 몇 명인가"를 보는 운영 지표. 누군가 상담을 시작하면 신규에서 빠져나감.

→ 둘은 포함관계(신규 ⊂ 문의)이며 동일한 것이 아니다. 퍼널에 "신규" 단계를 추가하면 누적 불변식이 깨지므로, 신규는 별도의 운영 알림성 지표로 다루는 게 맞다고 판단함 (이번 세션에서는 별도 지표 미구현, 향후 검토 항목).

### 2.4 퍼널 단계별 이탈 분석 (신규 기능)

리드가 `pending`/`rejected`로 빠지면, **어느 단계에서 멈췄는지**는 `leads.status` 한 컬럼만으로는 알 수 없다(현재값만 남기 때문). 이를 해결하기 위해 기존에 이미 동작 중이던 감사 로그(`audit_logs`, `Lead` 모델의 `Auditable` 트레이트가 모든 status 변경을 `old_values`/`new_values`로 자동 기록 중)를 활용했다:

```
알고리즘 (ChannelPivotController::dropoffs()):
1. status가 pending 또는 rejected인 리드를 모두 조회
2. 각 리드에 대해, audit_logs에서 new_values->status가 현재 status와 같은
   가장 최근 변경 이벤트를 찾음 (= "이탈로 빠진 그 순간"의 기록)
3. 그 이벤트의 old_values->status = "마지막으로 도달했던 단계"
4. 그런 이벤트가 없으면(생성 시점부터 pending/rejected) → "문의" 단계에서
   즉시 이탈한 것으로 fallback, 이탈 시각은 created_at
5. "이탈 사유"는 leads.memo 텍스트를 그대로 노출 (상담매니저가 이미 기록 중)
```

새 테이블/컬럼을 추가하지 않고 기존 인프라(`audit_logs`, `memo`)만으로 구현. API: `GET /api/dashboards/funnel-dropoffs`. 프론트엔드 `FunnelDropoffTable.tsx`가 이 결과를 마지막 도달 단계별(문의/상담/예약)로 그룹화해서 `/funnel` 페이지 하단에 표로 보여준다.

---

## 3. 이번 세션에서 변경된 파일

### 백엔드 (`mcrm-backend/`)
| 파일 | 변경 내용 |
|---|---|
| `database/migrations/2026_06_20_141533_add_scheduled_to_leads_status_enum.php` | `leads.status` enum에 `scheduled` 추가 |
| `app/Models/Lead.php` | 한글 변환 accessor(`getStatusAttribute`) 제거 |
| `app/Http/Controllers/Api/LeadController.php` | validation에 `scheduled` 추가, 상태 기반 카운팅 로직 정리 |
| `app/Http/Controllers/Api/ChannelPivotController.php` | 4개 집계 블록 status 비교 기준 통일(영문, scheduled 반영), `dropoffs()` 메서드 신규 추가 |
| `routes/api.php` | `GET /api/dashboards/funnel-dropoffs` 라우트 추가 |

### 프론트엔드 (`m-crm-project/`)
| 파일 | 변경 내용 |
|---|---|
| `src/lib/leadStatus.ts` | 영문↔한글 상태 매핑 공유 상수 (신규 파일) |
| `src/app/leads/page.tsx` | API 응답 en→kr 변환, 필터 전송 kr→en 변환, "미팅완료"→"예약완료" |
| `src/components/LeadListTable.tsx` | 자체 statusMap 버그(미팅완료/계약완료 둘 다 converted) 수정 |
| `src/app/leads/[leadId]/page.tsx` | 중복/잔재 상태 옵션 정리 |
| `src/app/funnel/page.tsx` | 죽은 localStorage 계약 카운트 제거, API `contracts` 필드 집계로 교체, 이탈 분석표 섹션 추가 |
| `src/components/FunnelDropoffTable.tsx` | 단계별 이탈 리드 분석표 (신규 파일) |

### 문서/배포
| 파일 | 변경 내용 |
|---|---|
| `TODOS.md` | 발견된 이슈(상담 카운트가 실제 Ticket이 아닌 status 기반) 보류 섹션에 기록 |
| `DEPLOYMENT_GUIDE.md` | 프론트엔드 배포 방식을 `vercel --prod` 수동 → GitHub 연동 자동배포로 갱신 |
| (인프라) | `m-crm-project`를 독립 GitHub 레포(`soona046-design/m-crm-project`, private)로 분리, Vercel `insight-mcrm` 프로젝트와 Git 연동 완료, push 시 자동배포 실제 검증함 |

---

## 4. 검증한 내용

- 로컬 마이그레이션 적용 후 enum이 6개 값으로 정상 변경됨을 확인
- `scheduled` 상태로 리드 생성/검증 통과, `closed`(레거시 값)는 거부됨을 Validator로 확인
- `/api/dashboards/channel-pivot` 실제 호출 → 상담/예약/계약 카운트가 0이 아닌 값으로 정상 집계됨을 확인
- `contacted→pending` 전이 시나리오로 `/api/dashboards/funnel-dropoffs` 호출 → "상담 단계 이탈"로 정확히 분류됨을 확인. 생성 시점부터 `rejected`인 경우 "문의 단계 이탈"로 정상 fallback됨도 확인
- 프론트엔드 변경 파일들 `tsc --noEmit` 통과 (기존에 있던 무관한 타입 에러 외 신규 에러 없음)
- README 테스트 커밋을 실제 push해서 Vercel 자동배포가 트리거되고(`*-git-main-*` 별칭 생성) 배포 결과가 HTTP 200으로 응답함을 확인

---

## 5. 남은 항목 (미해결, 백로그)

`TODOS.md`에 상세 기록되어 있음. 이번 세션과 직접 관련된 것만 발췌:

- **[조사 필요] "상담" 카운트가 실제 `Ticket` 레코드가 아닌 `Lead.status`만으로 집계됨** — 필드명은 `tickets_count`이지만 실제 `Ticket` 테이블을 조회하지 않음. 상담원이 실제로 티켓을 만들었는지 여부로 맞출지는 보류 중 (`ChannelPivotController.php`)
- **[검토] "신규" 리드를 위한 별도 운영 지표(처리 대기 알림 등)** — 퍼널 단계로는 추가하지 않기로 했으나, 별도 KPI 카드/위젯으로 만들지는 미정
- **[배포 대기]** 이번 세션의 백엔드 변경(`Lead.php`, `LeadController.php`, `ChannelPivotController.php`, 새 마이그레이션)은 로컬/GitHub에만 반영됨. Cafe24 운영 서버에는 `migrate-lead-status.php` 웹 스크립트를 FTP 업로드 → 브라우저 실행 → 즉시 삭제하는 절차가 아직 필요함
- 그 외 채널 카테고리 분류 이중 구현, `channel_category_mappings` 비활성 매핑 정리 등은 이번 세션과 무관한 기존 백로그 (`TODOS.md` 참고)
