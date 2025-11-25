\# 📘 UI/UX Design Specification – 의료 CRM (Material Design 기반)

\#\# 1\. 정보구조 (IA) & 네비게이션  
\#\#\# 1.1 좌측 Navigation Drawer  
\- Home  
\- Leads  
\- Tickets  
\- Appointments  
\- Dashboards  
\- Channels  
\- Profiles  
\- Settings  
\- Audit
1
\> \*\*스타일\*\*: Material Navigation Drawer \+ Icon \+ Label    
\> 활성화 상태는 \*\*Primary 강조 \+ Indicator bar\*\*

\#\#\# 1.2 상단 App Bar  
\- 좌측: 햄버거 버튼(GNB 토글), 화면 타이틀  
\- 우측: 검색, 알림, 도움말, 프로필 아바타

\---

\#\# 2\. 디자인 시스템  
\#\#\# 2.1 컬러 시스템 (Material 3 Tokens)  
\- Primary: Blue 600 (\#1E88E5), Primary Container: Blue 50  
\- Secondary: Teal 600 (\#00897B)  
\- Error: Red 600 (\#D32F2F)  
\- Surface: \#FFFFFF, Background: \#F8FAFC  
\- Outline: \#E0E3E7

\> \*\*명시적 대비\*\*: Text on Primary ≥ 4.5:1

\#\#\# 2.2 타이포그래피 (Font Guide)  
\- \*\*국문\*\*: Pretendard (가독성 \+ 다양한 굵기 지원)  
\- \*\*영문/숫자\*\*: Lato (라운드 감성, Pretendard와 조화)    
   또는 DIN (숫자·데이터 위주의 KPI/대시보드에서 권장)    
\- \*\*Text Scale (Material 3 기준)\*\*    
  \- Display (대시보드 KPI): 32/40px    
  \- Headline: 24px    
  \- Body Large: 16px    
  \- Label Medium: 14px (테이블 헤더, 버튼)    
\- \*\*숫자 데이터\*\*: tabular-nums (숫자 자리 정렬 안정)

\#\#\# 2.3 Elevation & Shape  
\- Card: Elevation 1 → Shadow subtle  
\- Dialog/Modal: Elevation 3  
\- Shape: Rounded 12dp (카드, 버튼, 필드)

\#\#\# 2.4 레이아웃 그리드  
\- 기본: 12 Column Grid, Gutter 24px  
\- 컨테이너: 1280px (대시보드 1440px)  
\- GNB: 240px, 우측 패널: 320px  
\- 반응형 Breakpoint: ≥1280 / 768–1279 / \<768

\#\#\# 2.5 Bento Grid System (대시보드/홈)  
\- \*\*구조\*\*: 카드형 위젯을 2–3단 컬럼으로 배열    
\- \*\*가로 비율\*\*: 1:1, 2:1, 1:2 모듈 조합    
\- \*\*용도\*\*:    
  \- 상단: KPI 카드(소형) 2–3개    
  \- 중단: Funnel/채널 성과(중형 카드)    
  \- 하단: 상담자별 성과/히트맵(대형 카드)    
\- \*\*이점\*\*: 시각적 리듬감 \+ 우선순위별 정보 구분 \+ 재배치 유연성  

\---

\#\# 3\. 주요 화면 설계  
\#\#\# 3.1 Leads List  
\- Material DataTable  
\- 열: 체크박스 | 이름(마스킹) | 상태 | 채널 | 최근접점 | 스코어 | 담당자 | SLA  
\- 행 Hover → Contextual Actions (보기, 배정, 보류)  
\- Empty state: Illustration \+ “필터 초기화”

\#\#\# 3.2 Lead Detail  
\- 좌측: Profile Card  
\- 중앙: Timeline (List \+ Divider)  
\- 우측: Supporting Panel (Tabs: 티켓/메모/첨부)

\#\#\# 3.3 Ticket Inbox  
\- Tab bar: 내 담당 / SLA임박 / 미응답 / 완료  
\- Ticket Card: Title, 최근 메시지, SLA 타이머 (Chip)  
\- Reply Panel: TextField \+ Template Chip \+ Send FAB

\#\#\# 3.4 Appointments  
\- Material Calendar (Week/Day toggle)  
\- 슬롯 카드: Patient Name(마스킹) \+ Procedure \+ Reminder Chip  
\- 노쇼 처리: Modal(Dialog)

\#\#\# 3.5 Dashboards (Bento Grid 기반)  
\- KPI Cards (소형, 상단 행)    
\- Funnel Chart (중형, 2칸)    
\- Channel Performance Bar Chart (중형)    
\- 상담자별 성과 Data Table (대형, 하단 전체 폭)

\---

\#\# 4\. 컴포넌트 시스템 (Material)  
\- Navigation Drawer / App Bar  
\- Data Table  
\- Card  
\- Chip (상태/태그/리마인드)  
\- Dialog / Snackbar  
\- FAB: “새 티켓”, “새 예약”  
\- Chart (Bar, Donut, Funnel, Heatmap)

\---

\#\# 5\. 인터랙션 & 상태  
\- Loading: Material Skeleton  
\- Empty: Illustration \+ CTA  
\- Error: Snackbar \+ Retry  
\- 단축키: f(필터), s(검색), n(신규 티켓)  
\- Motion: Material easing & duration (200–400ms)

\---

\#\# 6\. 마이크로카피 & 의료법 가드  
\- 톤: Neutral, Informative  
\- 금지어: “최고/보장/확실”  
\- 템플릿 전송 전 Dialog Guard

\---

\#\# 7\. 데이터 시각화  
\- Funnel → Stepper/도넛  
\- Channel → Bar chart  
\- 상담자별 성과 → Data Table \+ KPI  
\- Cohort → Grid Table

\---

\#\# 8\. 반응형 규칙  
\- 모바일: Bottom Navigation (Home/Leads/Tickets/예약/더보기)  
\- 테이블 → 카드 스택  
\- 입력 폼 → 단계 분할, Native Picker

\---

\#\# 9\. 보안 & 프라이버시  
\- 전화/이메일 마스킹 → 보기 버튼(권한 확인)  
\- Export → 워터마크(User/Time/IP)  
\- 세션 Timeout → Snackbar \+ Modal

\---

\#\# 10\. 상담자별 성과  
\- 대시보드 Tab: 상담자별  
\- 지표: 상담건수, 응답속도, 예약전환, 내원, SLA 위반율, 매출  
\- Export 지원

