# M-CRM — 통합 마케팅 성과·고객 관리 CRM 툴 소개
### (Google Ads API 심사 제출용 / For Google Ads API application review)

> 문서 목적: 인사이트(Insight)가 사내에서 사용하는 CRM 툴의 개요와, Google Ads API를 어떤 목적·범위로 사용하는지 설명합니다.
> 작성 기준: 2026-07-20 실제 배포·운영 중인 버전.

---

## 1. 한 줄 정의 / One-line summary

**한글:** 인사이트가 자사 온라인 광고 성과와 고객 유입·계약 데이터를 한 화면에서 관리·분석하는 **사내 전용** 마케팅 성과 CRM 툴.

**English:** An **internal-only** marketing-performance CRM that lets Insight consolidate and analyze its own online-advertising results together with lead-to-contract data in a single dashboard.

---

## 2. 회사·사용자 / Company & audience

- **회사(Company):** 인사이트(Insight) — 병·의원 대상 온라인 마케팅 대행 및 성과 관리 회사. Insight is a marketing agency that plans and runs online advertising for medical clinics and manages the resulting performance.
- **사용 대상(Audience):** 인사이트 **내부 구성원만** — 마케팅팀(담당자·팀장)과 경영진(대표·이사). 계정은 관리자가 직접 등록하며 공개 가입은 없습니다. Internal staff only (marketing team + executives). Accounts are created by an administrator; there is no public sign-up.
- **판매 여부(Distribution):** 외부에 판매·제공하지 않는 **사내 도구**입니다 (팀 5~10명 규모). Not sold or provided to any third party.

---

## 3. 주요 기능 / Key features (현재 구현 기준)

| 영역 | 기능 |
|---|---|
| 통합 대시보드 | 매출·수익률·목표 달성률·광고 성과를 한 화면에 요약. 월 선택으로 지난달 실적 조회. Revenue, profit-margin, goal attainment, and ad performance in one view; month picker for past months. |
| 광고 성과 수집 | 채널별(네이버·메타·구글) 광고비·노출·클릭·전환 집계. 네이버는 API 자동 수집 운영 중. 메타·구글은 현재 스프레드시트/화면 수기 입력, **구글은 본 API 연동으로 자동 수집 예정**. |
| 고객 유입 관리 | 문의(병원명·담당자·연락처)부터 상담→견적→계약까지 5단계 퍼널 관리. 수기 입력·CSV 업로드·편집 지원. |
| 광고 퍼널 | 광고 클릭 → 온라인 문의 → 상담 → 계약 전환율 분석. |
| 매출 관리 | 계약 등록, 월별 목표 대비 달성률, 매출/수익 추이, 채널 기여도. |
| 보고서 | 기간·채널 조건으로 성과 보고서 생성·버전 관리. |
| 계정·권한 | 로그인(관리자 등록 방식), 역할별 접근(대표·팀장·담당자 등), 모든 변경의 감사 로그. |

---

## 4. Google Ads API 사용 목적·범위 / Google Ads API usage

> 심사 핵심 3가지: **자사 계정만 · 조회 전용(read-only) · 사내 보고 용도**

**목적 (Purpose):**
인사이트 **자사** 구글 광고 계정의 성과 지표(광고비 cost, 노출 impressions, 클릭 clicks, 전환 conversions)를 **매일 1회 자동으로** 사내 CRM 대시보드에 수집합니다. 현재는 이 수치를 스프레드시트로 수기 관리하고 있으며, 본 API 연동으로 자동화하려는 것입니다.
We pull our **own** Google Ads performance metrics (cost, impressions, clicks, conversions) into our internal CRM dashboard **once per day, automatically**. Today these numbers are maintained manually via spreadsheet; this integration automates that.

**범위 (Scope):**
- **조회 전용(read-only)** — 캠페인·광고·예산의 생성·수정·삭제를 하지 않습니다. Read-only only; we never create, update, or delete campaigns, ads, or budgets.
- 수집 대상은 **인사이트가 소유·관리하는 광고 계정**뿐이며, 관리자(MCC) 계정을 통해 인증합니다. Only accounts owned/managed by Insight, authenticated through our manager (MCC) account.
- 조회한 지표는 채널·월 단위로 집계하여 사내 대시보드·보고서에만 사용합니다. Retrieved metrics are aggregated by channel/month and used solely in internal dashboards and reports.

**사용 대상 (Audience):**
인사이트 내부 마케팅팀·경영진의 광고 성과 보고와 ROI 분석. 외부에 재판매·제공하지 않습니다. Internal marketing team and executives only; not resold or shared externally.

**호출 규모 (Volume):** 계정 소수·일 1회 정기 수집 수준의 소량 호출. Low volume — a few accounts, one scheduled pull per day.

---

## 5. 기술 구성 / Technical setup (요약)

- 백엔드 Laravel(PHP) + MySQL, 프론트 Next.js. VPS(단일 서버, Docker)에서 운영, HTTPS + 로그인으로 접근 통제.
- 광고 수집은 서버의 스케줄러가 매일 실행하며, 채널별 커넥터 구조(네이버 실 API 운영 중, 구글은 본 심사 완료 후 동일 구조로 추가 예정).
- 조회한 광고 데이터는 지표(집계)만 저장하며, 광고 계정 자격증명(토큰)은 서버 환경변수로만 보관하고 소스코드·버전관리에 포함하지 않습니다.

---

문의: 인사이트 (dw.park@realinsight.co.kr)
