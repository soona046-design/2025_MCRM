# 디자인 시스템 문서

> 출처: Figma – "제목 없음" 파일 / Frame "대시보드 - 네이버 광고" (node-id: 1:977)
> [https://www.figma.com/design/0mojeZbpxGko71laR5CHet/제목-없음?node-id=1-977](https://www.figma.com/design/0mojeZbpxGko71laR5CHet/제목-없음?node-id=1-977)

이 문서는 피그마 프레임에서 추출한 실제 스타일 값(색상, 타이포그래피, 스페이싱, 컴포넌트)을 기준으로 정리한 디자인 시스템입니다. 별도의 Figma Variables(디자인 토큰)는 파일에 정의되어 있지 않아, 화면에 사용된 값을 그대로 토큰화했습니다.

---

## 1. 색상 (Color)

### Primary


| 토큰                      | 값               | 용도                                        |
| ----------------------- | --------------- | ----------------------------------------- |
| `color-primary`         | `#FF5B2C`       | 주요 버튼 배경, 링크, 강조 텍스트, 활성 탭/메뉴 텍스트         |
| `color-primary-hover`   | `#B2401F` (600) | Primary 버튼 hover/active, 흰 텍스트와 대비 5.76:1 |
| `color-primary-text`    | `#8C3218` (700) | 옅은 배경 위 강조 텍스트/뱃지 (대비 7.58:1)             |
| `color-primary-surface` | `#FFF5F2` (50)  | 보조 버튼 배경, 강조 카드 배경                        |


> **사용 비중 가이드**: `color-primary` 계열은 화면 전체 시각 비중의 **30%를 넘지 않도록 제한**한다. Primary CTA 버튼, 활성 내비게이션 상태, 핵심 KPI 1개 등 "포인트" 요소에만 쓰고, 나머지 UI(카드, 테이블, 보조 버튼, 나머지 KPI)는 Neutral/Surface 톤을 기본으로 유지한다.
> 흰 텍스트를 `#FF5B2C` 배경에 바로 얹으면 명암비 3.1:1로 본문 텍스트 기준에는 부족하므로, 작은 텍스트에는 `color-primary-hover`(600)를 쓰거나 굵고 큰 텍스트(SemiBold 16px 이상)로 제한한다.



### Neutral / Text


| 토큰                   | 값         | 용도                            |
| -------------------- | --------- | ----------------------------- |
| `color-text-primary` | `#000000` | 기본 본문 텍스트                     |
| `color-text-heading` | `#2E2E2E` | 헤더 로고, 사이드바/메뉴 텍스트            |
| `color-text-muted`   | `#737373` | 보조 설명, 비활성 탭 텍스트, placeholder |




### Border / Divider


| 토큰                     | 값                  | 용도                      |
| ---------------------- | ------------------ | ----------------------- |
| `color-border-default` | `rgba(0,0,0,0.08)` | 기본 구분선, 카드 보더           |
| `color-border-strong`  | `rgba(0,0,0,0.15)` | 점선 구분선(Horizontal Rule) |
| `color-border-subtle`  | `rgba(0,0,0,0.10)` | 페이지네이션 버튼 보더            |
| `color-border-table`   | `#E5E5E5`          | 테이블 셀 보더                |
| `color-divider-line`   | `rgba(5,5,5,0.06)` | 헤더 내 세로 구분선             |




### Surface / Background


| 토큰                 | 값         | 용도                     |
| ------------------ | --------- | ---------------------- |
| `color-bg-page`    | `#F7F7F7` | 검색창 등 옅은 배경            |
| `color-bg-subtle`  | `#F8F9FA` | 세그먼트 컨트롤 배경, 테이블 헤더 배경 |
| `color-bg-surface` | `#FFFFFF` | 카드/패널 배경               |




### 데이터 시각화 / 시맨틱 컬러


| 토큰                   | 값         | 용도              |
| -------------------- | --------- | --------------- |
| `color-data-red`     | `#F4361E` | KPI "총 노출수" 강조색 |
| `color-data-orange`  | `#FD9A06` | KPI "총 클릭수" 강조색 |
| `color-data-blue`    | `#2196F3` | KPI "총 전환수" 강조색 |
| `color-badge-border` | `#91CAFF` | "필수" 뱃지 보더      |
| `color-badge-text`   | `#0958D9` | "필수" 뱃지 텍스트     |


---



## 2. 타이포그래피 (Typography)



### 폰트 패밀리

- **Pretendard** (Regular / Medium / SemiBold / Bold) — 본문, UI 전반의 기본 폰트
- **NanumSquareExtraBold** — 브랜드 로고/타이틀 전용 ("광고주센터")
- **Inter** — 차트 축 라벨(숫자/영문)



### 크기 스케일


| 크기   | Weight             | Tracking | 용도                           |
| ---- | ------------------ | -------- | ---------------------------- |
| 20px | ExtraBold (Nanum)  | -0.5px   | 브랜드 로고 타이틀                   |
| 18px | Bold               | -0.3px   | 섹션 타이틀 ("광고 성과지표", "공지사항" 등) |
| 16px | SemiBold / Regular | -0.3px   | 버튼 텍스트, 사이드바 1depth 메뉴       |
| 15px | Regular / Medium   | -0.3px   | 본문 텍스트, 테이블 셀, 메뉴 텍스트        |
| 14px | SemiBold           | -        | 보조 버튼 텍스트, 탭 라벨              |
| 13px | SemiBold / Regular | -0.3px   | 세그먼트 컨트롤 라벨                  |
| 12px | Regular            | -        | 캡션, 차트 축 라벨(Inter)           |
| 11px | SemiBold           | -        | 뱃지 텍스트                       |
| 22px | Medium             | -0.3px   | KPI 숫자 강조                    |


---



## 3. Spacing & Radius



### Border Radius


| 토큰            | 값              | 용도                       |
| ------------- | -------------- | ------------------------ |
| `radius-sm`   | 6px            | 보조/아웃라인 버튼               |
| `radius-md`   | 8px            | 기본 버튼, 인풋, 카드(소), KPI 카드 |
| `radius-lg`   | 12px           | 대형 카드/패널                 |
| `radius-pill` | 999px / 9999px | 검색창, 세그먼트 컨트롤, 뱃지(pill)  |




### Spacing 스케일 (8px 그리드 기준)

`4 · 6 · 8 · 10 · 12 · 16 · 20 · 24` px — 컴포넌트 내부 padding, 요소 간 gap에 반복적으로 사용. 버튼류는 좌우 padding 13~17px로 광학 보정.

### 레이아웃 치수


| 영역         | 치수                                                |
| ---------- | ------------------------------------------------- |
| 헤더(Header) | 높이 56px, 전체 너비                                    |
| 좌측 사이드바    | 외곽 300px / 내부 콘텐츠 280px, 메뉴 아이템 높이 44px           |
| 메인 콘텐츠     | 좌측 컬럼 1154px, 우측 패널 382px, 컨텐츠 상단 padding 24~36px |


---



## 4. 컴포넌트 (Components)



### Button

- **Primary**: 배경 `#FF5B2C`, 텍스트 흰색(SemiBold 16px 이상 권장), radius 8~~10px, 높이 40~~48px — 화면당 1개 원칙 (예: "광고 만들기", "변경 내용 바로가기")
- **Secondary (Tint)**: 배경 `#FFF5F2`, 텍스트 `#8C3218`, radius 8px (예: "다운로드", "전체 캠페인 보기")
- **Outline/Ghost**: 배경 흰색 또는 투명, 보더 없음~옅은 보더, 텍스트 블랙 (예: "충전하기", "전체보기")
- **Icon Button**: 정사각형(24~48px), radius 8px, 아이콘 중앙 정렬



### Search Input

- 배경 `#F7F7F7`, radius 999px(pill), 높이 40px, 좌측 라벨+아이콘 + 우측 placeholder 텍스트(`#737373`, 16px)



### Sidebar Menu Item

- 높이 44px, radius 8px
- 1depth: padding-left 16~24px, 아이콘(24px) + 텍스트(16px)
- 2depth(하위메뉴): padding-left 48px, 아이콘 없이 텍스트만(16px, Regular)
- Active 상태: 텍스트 `#FF5B2C` + SemiBold / Inactive: `#2E2E2E` + Regular (전체 메뉴 중 1개만 active)



### Segmented Control

- 컨테이너 배경 `#F8F9FA`, radius 9999px(pill), padding 4px
- Active 탭: 배경 흰색 + drop-shadow, 텍스트 `#2E2E2E` SemiBold 13px
- Inactive 탭: 배경 없음, 텍스트 `#737373` Regular 13px



### KPI / Stat Card

- 보더 `rgba(0,0,0,0.08)`, radius 8px, padding 13px
- 상단: 색상 dot(8~12px) + 라벨(16px Medium) + info 아이콘
- 점선 구분선(`rgba(0,0,0,0.08)`, dashed)
- 하단: 강조 숫자 22px Medium, 시맨틱 컬러 적용



### Data Table

- 헤더 셀: 배경 `#F8F9FA`, 보더 `#E5E5E5`, 텍스트 15px Medium
- 바디 셀: 배경 흰색, 보더 `#E5E5E5`, 텍스트 15px Regular
- 강조 값(예: 증감률)은 시맨틱 컬러 적용, Primary(`#FF5B2C`)는 행 전체가 아닌 단일 강조 값에만 제한적으로 사용



### Card / Panel

- 배경 흰색, radius 12px, padding 16~20px
- 상단: 타이틀(18px Bold) + info 아이콘 + 우측 액션 버튼(Secondary Tint)
- 리스트/차트/빈 상태 콘텐츠를 포함하는 공용 컨테이너



### Empty State

- 중앙 정렬, 타이틀 15px SemiBold + 설명 15px Regular, 텍스트 컬러 블랙, 상하 padding 36px



### Badge / Tag

- Pill 형태, radius 14px, 보더 `#91CAFF`, 배경 흰색, 텍스트 `#0958D9` 11px SemiBold (예: "필수")



### Pagination

- 원형 아웃라인 버튼(38px, radius 19px), 보더 `rgba(0,0,0,0.1)`
- 현재 페이지 숫자: `#FF5B2C` SemiBold 16px



### Divider

- 실선: `rgba(0,0,0,0.08)` 1px — 카드/섹션 구분
- 점선: `rgba(0,0,0,0.08~0.15)` dashed — 리스트 내부 구분
- 세로선: `rgba(5,5,5,0.06)` 1px — 헤더 내 아이템 구분

---



## 5. 아이콘

크기 스케일: `14 · 16 · 18 · 20 · 24` px. 대부분 라인 아이콘 스타일(1.5px stroke 추정), 버튼/메뉴 아이템 내부에 인라인으로 배치.

---



## 6. 레이아웃 구조 요약

```
Frame (1924 x 1521)
├─ Header (전체 너비 x 56px)
│  ├─ 좌: 로고 + 검색창
│  └─ 우: 계정정보 + 비즈머니 + 로그아웃 + 서비스 아이콘
└─ Body (전체 너비 x 1465px)
   ├─ Sidebar (300px)
   │  ├─ "광고 만들기" CTA 버튼
   │  ├─ 비즈니스 진단 배너
   │  ├─ 대시보드 / 전체 캠페인 메뉴
   │  ├─ 세그먼트 컨트롤(검색광고/디스플레이 광고)
   │  └─ 광고 관리 / 보고서 / 도구 / 구성요소 관리 메뉴
   └─ Main Content (1624px)
      ├─ 좌측 컬럼 (1154px)
      │  ├─ 기간 선택 + 계정 전환 버튼
      │  ├─ 광고 성과지표 카드 (KPI 3종 + 차트)
      │  ├─ 광고비 집중 캠페인 카드 (테이블 + 페이지네이션)
      │  └─ 공지사항 카드 (리스트)
      └─ 우측 패널 (382px)
         ├─ 변경 내용 바로가기 카드
         └─ 알림 카드 (Empty State)
```

