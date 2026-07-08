# Insight Design System (인사이트 디자인 시스템)

병원 전문 마케팅 브랜드 **insight**를 위한 모바일 우선 디자인 시스템.
원본 *"UI Design_Style Guide"* Figma를 토큰 · 컴포넌트 · 가이드라인으로 정리한 단일 정의 문서입니다.

- **기본 폰트:** Pretendard (원본 Figma는 Spoqa Han Sans Neo 사용 → 클라이언트 요청으로 Pretendard 대체)
- **영문 디스플레이:** Roboto
- **언어/톤:** 한국어, 정중·중립체, 짧은 명사/동사형 라벨
- **핵심 모티프:** 모노크롬 + 단일 포인트 오렌지 `#FF5B2C` / 아이콘 라인(기본)→채움(선택)

---

## 1. 컬러 (Color)

### 포인트 / 브랜드
| 토큰 | HEX | 역할 |
|---|---|---|
| `--insight-orange` | `#FF5B2C` | 메인 포인트 · CTA 강조 · 배지 |
| `--insight-blue` | `#7DADFF` | 서브 01 · 정보 강조 · 카운트 배지 |
| `--insight-amber` | `#FFBD69` | 서브 02 · 보조 강조 |
| `--insight-guide` | `#86B3FF` | 가이드/주석 액센트 |

### 뉴트럴 램프
| 토큰 | HEX | 용도 |
|---|---|---|
| `--black` | `#000000` | 본문 텍스트 · 기본 버튼 배경 |
| `--gray-900` | `#222222` | 버튼 press · 강한 텍스트 |
| `--gray-600` | `#666666` | 보조 텍스트 |
| `--gray-500` | `#999999` | 3차 텍스트 · placeholder |
| `--gray-400` | `#BBBBBB` | 비활성 버튼 배경 |
| `--gray-300` | `#CCCCCC` | 비활성 텍스트 · 약한 아이콘 |
| `--gray-200` | `#EAEAEA` | 기본 라인/디바이더 |
| `--gray-150` | `#F1F1F1` | 라이트 라인 |
| `--gray-100` | `#F6F6F6` | 약한 표면 / fill |
| `--white` | `#FFFFFF` | 기본 표면 |

### 시맨틱
| 토큰 | 값 | 토큰 | 값 |
|---|---|---|---|
| `--text-primary` | black | `--surface-base` | white |
| `--text-secondary` | gray-600 | `--surface-muted` | gray-100 |
| `--text-tertiary` | gray-500 | `--surface-dark` | gray-900 |
| `--text-disabled` | gray-300 | `--surface-point` | orange |
| `--text-point` | orange | `--line-default` | gray-200 |
| `--text-on-dark` | white | `--line-strong` | gray-300 |
| `--text-link` | black | `--line-light` | gray-150 |

> **규칙:** 화면은 거의 모노크롬(흰 배경·검정 텍스트·그레이 위계). 오렌지는 형광펜처럼 **소량 강조**에만 — 큰 면적/버튼 배경에는 사용하지 않음. **그라데이션 없음.**

---

## 2. 타이포그래피 (Typography)

- **Base:** `Pretendard Variable` (한글 + 라틴 UI 전체)
- **Display:** `Roboto` (영문 대형 헤딩 전용)
- **Weights:** Regular 400 / Medium 500 / Bold 700
- **Tracking:** 기본 **-2.5%**(`-0.025em`), 밀집 라벨/링크 **-5%**(`-0.05em`)

| 스타일 | 클래스 | 굵기 | 크기 / 행간 | 용도 |
|---|---|---|---|---|
| headline.01 | `.t-headline` | Regular | 20 / 30 | 섹션·페이지 헤드라인 |
| title.02 | `.t-title-02` | Medium | 18 / 26 | 카드·리스트 제목 |
| title.01 | `.t-title-01` | Bold | 13 / 20 | 강한 라벨·버튼 |
| body.03 | `.t-body-03` | Medium | 15 / 22 | 강조 본문 |
| body.02 | `.t-body-02` | Medium | 13 / 20 | 기본 본문(강조) |
| body.01 | `.t-body-01` | Regular | 13 / 20 | 기본 본문 |
| caption | `.t-caption` | Regular | 12 / 22 | 표·그래프·메타 |
| gnb label | `.t-gnb` | Bold | 10 / 12 | 탭바 라벨 |
| display | `.t-display` | Medium | 40 / 1 | 영문 커버 헤딩 |

---

## 3. 스페이싱 · 레이아웃 · 형태

### 스페이싱 (4px 베이스)
`--space-2/4/6/8/10/12/16/20/24/30/40` → 2·4·6·8·10·12·16·20·24·30·40px

### 레이아웃
| 토큰 | 값 | 의미 |
|---|---|---|
| `--screen-gutter` | 20px | 기본 좌우 페이지 패딩 |
| `--gnb-margin-left` | 41px | 탭바 좌측 고정 마진 |
| `--gnb-margin-right` | 42px | 탭바 우측 고정 마진 |
| `--gnb-height` | 100px | 탭바 높이 |
| `--control-height` | 60px | 풀폭 버튼 높이 |
| `--tap-min` | 44px | 최소 터치 타겟 |

### 라운드 (Radius)
| 토큰 | 값 |
|---|---|
| `--radius-sm` | 6px |
| `--radius-md` | 10px |
| `--radius-button` | 15px |
| `--radius-card` | 16px |
| `--radius-pill` | 999px |
| `--radius-circle` | 50% |

### 아이콘 사이즈 (맥락별)
| 토큰 | 값 | 맥락 |
|---|---|---|
| `--icon-gnb` | 26px | GNB 탭 |
| `--icon-top` | 36px | 홈 상단 벨 |
| `--icon-30` | 30px | 서브탑·홈 카드·키패드·MY |
| `--icon-card` | 20px | 카드·프로필 인라인 |

### 그림자 (Elevation) — 절제됨
| 토큰 | 값 | 용도 |
|---|---|---|
| `--shadow-gnb` | `0 4px 10px rgba(0,0,0,.10)` | 탭바 (시그니처) |
| `--shadow-card` | `0 2px 8px rgba(0,0,0,.06)` | 카드 |
| `--shadow-float` | `0 6px 20px rgba(0,0,0,.12)` | 플로팅 요소 |

> 내부 그림자 없음. outline 카드는 1px `#EAEAEA` 헤어라인으로 대체. 배경은 항상 플랫(텍스처·패턴·그라데이션 없음).

---

## 4. 컴포넌트 (Components)

번들 네임스페이스: `window.InsightDesignSystem_c6bded`

### Button — 풀폭 태스크 버튼
검정 fill · Bold 13px 흰 라벨 · radius 15 · 높이 60. press 시 `#000→#222`, 비활성 `#BBBBBB`.
```jsx
<Button>생성하기</Button>
<Button disabled>생성하기</Button>
<Button fullWidth={false}>확인</Button>
```
props: `disabled?`, `fullWidth?` (기본 true), `type?`

### TextLink — 인라인 텍스트 액션
```jsx
<TextLink icon="more">알림 더보기</TextLink>
<TextLink underline weight="medium">달력 보기</TextLink>
<TextLink icon="edit">일정 추가</TextLink>
```
props: `icon?`, `underline?`, `weight?` ("bold"|"medium")

### Badge — 카운트 / 닷
```jsx
<Badge count={1} />              {/* 오렌지 (기본) */}
<Badge count={2} tone="info" />  {/* 블루 */}
<Badge dot />
```
props: `count?`, `tone?` ("primary"|"info"), `dot?`, `max?` (기본 99 → `99+`)

### Card — 콘텐츠 표면
radius 16 · 패딩 20. `variant="shadow"`(기본) | `"outline"`(헤어라인). `onClick` 시 전체가 탭 가능.
```jsx
<Card><div className="t-title-02">제목</div></Card>
<Card variant="outline" onClick={open}>탭 가능한 카드</Card>
```

### TabBar (GNB) — 하단 글로벌 내비
선택 상태 = 라인 아이콘 → `-fill` 아이콘 교체 (색 변화·밑줄 없음). 고정 41/42px 마진, 100px 높이, 상단 소프트 섀도.
```jsx
<TabBar active={tab} onChange={setTab} tabs={[
  { key:"home", label:"HOME", icon:"home" },
  { key:"office", label:"OFFICE", icon:"office", badge:1 },
  { key:"board", label:"BOARD", icon:"board", badge:2, badgeTone:"info" },
  { key:"my", label:"MY", icon:"my" },
]} />
```

### TopBar — 서브페이지 앱바
중앙 정렬 title.02, 높이 56. leading 기본 `back`, 30px 액션 아이콘. 벨에는 오렌지 닷.
```jsx
<TopBar title="알림" leading="back" onLeading={goBack}
  actions={[{ icon:"search" }, { icon:"settings" }]} />
```

### Icon — 40 글리프 인라인 SVG
커스텀 모노크롬 세트, `currentColor`로 채색. 라인(기본) + `-fill`(선택) 페어.
```jsx
<Icon name="bell" size={30} />
<Icon name="home-fill" size={26} color="var(--insight-orange)" />
```
**글리프 목록:** home, office, board, my, edit (+ `-fill` 버전) · back, search, more, close, settings, bell, bell-off · calendar, comments, location, image, hash · grid, link, chat, heart, star-fill, doc-fill, seat-fill, parking-fill, alert-fill, alert-muted, chat-dots-fill · id-card, pin, clock-fill, location-fill, phone-fill, mail-fill

---

## 5. 콘텐츠 가이드 (Content Fundamentals)

- **언어:** 한국어. 짧고 명사 중심, 행동 지향.
- **톤:** 친근-전문. 이름으로 인사("좋은 아침이에요, OOO 님 👋"), 그 외엔 실용적.
- **존댓말:** 문장은 -요/합쇼체("…업데이트되었습니다"). 버튼·라벨은 어미 없는 명사/동사("생성하기", "예약하기", "달력 보기").
- **인칭:** 사용자는 "…님". 앱은 기능명으로 3인칭 지칭, "우리/we" 사용 안 함.
- **케이싱:** 내비게이션 라틴 라벨은 **대문자**(HOME·OFFICE·BOARD·MY), HEX 대문자(`#FF5B2C`).
- **숫자/시간:** 24시간제, 엔대시 범위("14:00 — 15:00"), 피드는 상대시간("방금", "1시간 전", "어제").
- **이모지:** 사람 목소리 카피(인사·캐주얼 게시글)에만 **소량**. 시스템 메시지·라벨·버튼엔 사용 안 함.
- **분위기:** 차분·정돈·실행 중심. 여백 충분, 화면당 명확한 1개 액션.

---

## 6. 비주얼 가이드 (Visual Foundations)

- **컬러:** 모노크롬 + 단일 오렌지 포인트. 그라데이션 없음.
- **타입:** Pretendard 전반, Roboto는 영문 디스플레이만. 한글 타이트 트래킹.
- **형태:** 카드 16 / 버튼 15 라운드, 칩·태그는 풀 pill. 아이콘은 12–16 라운드 사각 컨테이너.
- **그림자:** GNB 시그니처 섀도 + 가벼운 카드 섀도만. outline은 헤어라인.
- **배경:** 플랫(흰색/`#F6F6F6`). 텍스처·패턴·사진·일러스트 없음.
- **아이콘:** 라인=기본 / 채움=선택 — 핵심 인터랙션 모티프(GNB 탭에서 라인→채움 교체).
- **모션:** 최소. 버튼 press 시 다크닝(스케일 바운스 없음), 트랜지션 ~120ms 컬러 페이드. 장식용 루프 애니메이션 없음.
- **상태:** 터치 우선. press=다크닝(버튼) 또는 뮤트-그레이 배경(리스트 행). 비활성=`#BBBBBB` fill + 흰 라벨.

---

## 7. 아이코노그래피 (Iconography)

- **커스텀 모노크롬 세트(40 글리프).** 아이콘 폰트·서드파티 세트 없음 — 전부 자체 SVG.
- **스타일:** 기하학적, 둥근 캡, ~1.5px 스트로크. 라인(기본) + `-fill`(선택) 페어.
- **사이즈:** 26(GNB) · 36(홈 상단 벨) · 30(서브탑/카드/키패드/MY) · 20(인라인).
- `currentColor`로 채색. 유니코드 글리프를 UI 아이콘으로 쓰지 않음 — 항상 `Icon` 사용.

---

## 8. 사용법 (Usage)

```html
<link rel="stylesheet" href="styles.css" />
<script src="_ds_bundle.js"></script>
<script type="text/babel">
  const { Icon, Button, TabBar, Card, Badge, TextLink, TopBar }
    = window.InsightDesignSystem_c6bded;
</script>
```

텍스트 스타일은 플레인 클래스로도 사용 가능: `t-headline`, `t-title-02`, `t-title-01`, `t-body-03/02/01`, `t-caption`, `t-gnb`, `t-display`.

### 파일 구조
| 경로 | 내용 |
|---|---|
| `styles.css` | 글로벌 엔트리 — 모든 토큰 `@import` |
| `tokens/colors.css · typography.css · spacing.css · fonts.css` | 토큰 정의 |
| `components/icon · button · badge · card · navigation/` | 컴포넌트 (.jsx/.d.ts/.prompt.md) |
| `guidelines/*.card.html` | 파운데이션 스펙 카드 |
| `ui_kits/insight-app/` | 인터랙티브 4탭 앱 재현 |
| `assets/icons/*.svg · logo-mark*.svg` | 아이콘 40종 · 로고 변형 |

---

*이 문서는 디자인 시스템 정의의 단일 요약본입니다. 실행 가능한 토큰/컴포넌트는 프로젝트 파일에 있습니다.*
