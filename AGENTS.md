# AGENTS.md

이 작업 폴더에서 일하는 AI 에이전트(Codex·Claude Code)를 위한 안내입니다. `CLAUDE.md`와 같은 내용의 사본이므로 수정할 때 둘 다 고칩니다.

**지금 상태와 대기 중인 일은 `docs/v2/작업-이어가기.md`를 먼저 읽으세요.**

## 무엇인가

M-CRM v2 — 인사이트(의료 마케팅 대행)의 통합 마케팅 성과·고객·매출 관리 CRM. **운영 중인 시스템은 v2 하나뿐입니다.**

| 경로 | 내용 | 저장소 |
|---|---|---|
| `mcrm-v2/` | 프론트 — Next.js 14 · React 18 · TypeScript · Tailwind + shadcn/ui | soona046-design/mcrm-v2 (별도 git) |
| `mcrm-v2-backend/` | 백엔드 — Laravel 13 · Sanctum · 로컬 SQLite / 운영 MySQL 8.4 | soona046-design/mcrm-v2-backend (별도 git) |
| `docs/v2/` | 명세·변경 기록·가이드 | 이 루트 저장소 (soona046-design/2025_MCRM) |
| `data/original/` | 원본 자료 (gitignore, 개인정보 — 삭제 금지) | — |
| `backups/v2-db/` | 운영 DB 덤프 로컬 사본 (gitignore, 개인정보 — 커밋 금지) | — |

`mcrm-backend/`, `m-crm-project/`, `cafe24-deploy/`, 루트의 `*.php` 스크립트, `deploy-backend.sh`는 **종료된 v1**(Vercel + Cafe24, 2026-09-11 호스팅 만료) 자료입니다. 참고용이며 수정·배포 대상이 아닙니다.

## 로컬 실행·검증

- `.claude/launch.json`: `mcrm-v2`(포트 3100), `mcrm-v2-backend`(포트 8100). 프론트는 `NEXT_PUBLIC_API_URL`이 없으면 `http://localhost:8100/api`를 씁니다.
- 로그인: 로컬 DB에서 tinker로 토큰 발급 → 브라우저 localStorage `mcrm_token`에 넣기.
- 점검: 프론트 `npx tsc --noEmit`, 백엔드 `php -l` + `php artisan migrate`.
- **SQLite(로컬)는 관대합니다.** 날짜·타입 버그가 MySQL(운영)에서만 터진 전례가 있습니다(월말 `'-31'` 하드코딩 → 9월 정기 회차 매일 복제, UUID/BIGINT). 월 경계는 `endOfMonth()`로 계산합니다.

## 배포 — VPS 49.247.138.118 (https://49-247-138-118.sslip.io)

SSH: `ssh -i ~/.ssh/mcrm_vps root@49.247.138.118`

- **백엔드**: `cd /srv/mcrm-v2-backend && git pull && docker compose -f docker-compose.prod.yml up -d --build` — 서비스명 없이 **전체 빌드**합니다. app만 빌드하면 scheduler·queue 컨테이너가 옛 이미지로 남아 정기 작업이 옛 코드로 돕니다. 배포 후 `docker ps`로 queue·scheduler 재생성 시각을 확인합니다. 마이그레이션은 app 컨테이너 기동 시 자동 실행되며 `docker logs mcrm-v2-backend-app-1`에서 DONE/FAIL을 확인합니다.
- **프론트**: `cd /srv/mcrm-v2 && git pull && docker build --build-arg NEXT_PUBLIC_API_URL=https://49-247-138-118.sslip.io/api -t mcrm-v2-front . && docker rm -f mcrm-front && docker run -d --name mcrm-front --restart unless-stopped -p 127.0.0.1:3000:3000 -e NODE_ENV=production mcrm-v2-front`
- 자격증명(광고 API 토큰 등)은 서버 `/srv/mcrm-v2-backend/.env`에만 둡니다. git에 넣지 않습니다.
- 정기 작업(scheduler 컨테이너): 광고 수집 06:00, 정기 회차 생성 06:05, 자동 보류 06:10, 휴지통 영구삭제 03:30/03:35(30일 경과분, JSON 백업 후), 익명화 03:00.
- DB 백업: 호스트 cron 매일 04:15 `/srv/backup-mcrm-db.sh` → `/srv/backups/mcrm-YYYY-MM-DD.sql.gz`(21일 회전, `keep-` 접두는 영구). 장애 대응 진입점은 `docs/v2/배포-가이드.md` §7.

## 작업 규칙

1. **운영 데이터 정정은 커밋된 멱등 마이그레이션으로 합니다.** 현재값이 정확히 일치할 때만 바꾸는 가드를 두고, `contract_logs`에 사유를 남기고, 영향 받은 달은 `MonthlySnapshots::rebuild()`로 재계산합니다. SSH로 운영 DB를 직접 수정하지 않습니다. 운영 조회(읽기 전용)는 PHP 스크립트 파일을 scp → `docker cp` → `php artisan tinker --execute='include "..."'`로 실행합니다.
2. **이력은 불변입니다.** `contract_logs`·`change_logs` 등 이력 테이블은 수정·삭제가 모델에서 차단됩니다.
3. **회계 원칙 (2026-09-03 사용자 확정)**
   - 모든 금액은 공급가액(VAT 제외)입니다. 부가세 포함 합계는 청구서 문서에만 표기합니다.
   - 확정 매출은 수금일(`paid_at`)이 속한 달에 잡힙니다. 수금일은 완료를 클릭한 날이 아니라 실제 입금일입니다.
   - 예상 매출은 청구일(`contracted_at`, 화면 명칭 '청구일', 구 '매출일')이 속한 달에 잡힙니다.
   - 비용은 발생 기준으로, 청구일이 속한 달에 차감합니다(완료 또는 지출결의서 전달 회차). 화면 용어는 '비용'이며, 서류명 '지출결의서'만 예외입니다.
   - 지출 전용 회차는 금액 0입니다(미입력 null과 구분).
   - 기대금액(유입 관리)은 추정치이므로 매출 집계에 절대 포함하지 않습니다.
4. **문서를 함께 갱신합니다.** 기능이 바뀌면 `docs/v2/기능명세서.md`(상단 구현 상태 콜아웃), `docs/v2/프론트엔드-변경사항.md`(다음 § 번호), manyfast PRD(프로젝트 `bac471a3-8a87-4c6c-b918-3d18d5d4dbe5`, MCP가 연결돼 있을 때)를 고칩니다. 계산 규칙·메뉴·분류처럼 구조가 바뀌면 `docs/v2/연동-수정-맵.md`를 체크리스트로 먼저 확인합니다. 진행 상태나 대기 항목이 바뀌면 `docs/v2/작업-이어가기.md`를 갱신합니다.
5. **여러 에이전트가 이 폴더를 함께 씁니다**(Claude Code 여러 대화, Codex). 커밋 전에 `git status`로 다른 사람의 미커밋 변경이 있는지 확인하고, `git add -A`나 `git add docs` 대신 **경로를 지정해** 커밋합니다. 모르는 변경을 발견하면 임의로 커밋하거나 지우지 말고 사용자에게 알립니다. 9/3과 9/20에 다른 세션의 변경이 섞여 커밋·배포된 전례가 있습니다.
6. 저장소 3개(루트=문서, `mcrm-v2`, `mcrm-v2-backend`)는 각각 커밋·푸시합니다.
7. 금지 명령: `migrate:fresh`, `import:real --fresh` — 실데이터와 화면 입력이 사라집니다.
8. **권한은 `App\Support\Permissions`로만 검사합니다.** 역할을 하드코딩하지 말고 `Permissions::authorize()`·화면 `can()`을 씁니다. users를 참조하는 컬럼·관계를 새로 만들면 `withTrashed()`와 `PurgeTrashedUsers::REFERENCES` 등록이 필요합니다(`연동-수정-맵.md` §2).

## 문서 지도 (docs/v2)

| 문서 | 용도 |
|---|---|
| `작업-이어가기.md` | 현재 상태·사용자 조치 대기·다음 작업 (가장 먼저 읽기) |
| `프론트엔드-변경사항.md` | 모든 변경의 시간순 기록(§ 번호) — 최근 §부터 거꾸로 읽으면 맥락 파악이 빠름 |
| `기능명세서.md` | 기능 명세 + 상단에 날짜별 구현 상태 |
| `연동-수정-맵.md` | 수정할 때 함께 고쳐야 하는 연동 지점 (🔴 = 중복 정의) |
| `입력-가이드.md` | 사용자용 입력 규칙 (회계 원칙 포함) |
| `배포-가이드.md` | 서버 구성·장애 진입점(§7) |
| `데이터-갱신-가이드.md` | 월별 원본 자료 적재 절차 |
| `백엔드-설계.md` · `IA-화면목록.md` · `유저플로우.md` | 설계 참고 |
| `전자결재-기능추가-요청서.md` | 9/17 외부에서 작성된 초안 — 실제 구현과 다른 점은 `작업-이어가기.md` §5 |
