# M-CRM TODO 리스트

## 🔥 긴급 (즉시 수행 필요)

- [ ] **Cafe24 서버에 memo 컬럼 추가** (10분)
  - FTP로 `add-memo-column.php` 업로드
  - http://insightmcrm.mycafe24.com/add-memo-column.php 실행
  - 성공 확인 후 파일 삭제
  - 프론트엔드에서 memo 필드 주석 해제
  - 커밋 및 배포

- [ ] **Cafe24 서버 사용자 데이터 확인** (5분)
  - FTP로 `check-users.php` 업로드
  - http://insightmcrm.mycafe24.com/check-users.php 실행
  - 사용자 없으면 UserSeeder 실행 필요

---

## ⭐ 중요 (이번 주 완료 목표)

- [ ] **담당자 매핑 시스템 구현** (30분)
  - 사용자 목록 API 연동
  - 담당자 선택 드롭다운 추가
  - assignee_name → assigned_user_id 변경
  - 테스트 및 검증

- [ ] **문의 날짜 컬럼 추가** (20분)
  - leads 테이블에 inquiry_date 컬럼 추가 마이그레이션
  - API 요청에 inquiry_date 포함
  - 프론트엔드 코드 수정
  - 테스트

---

## 📌 일반 (시간 날 때)

- [ ] **진료 서비스 데이터 구조 개선** (1시간)
  - lead_treatments 중간 테이블 생성
  - Lead-TreatmentType 다대다 관계 구현
  - API 및 프론트엔드 수정

- [ ] **통합 테스트 수행** (30분)
  - 전체 플로우 테스트 (로그인 → 등록 → 조회 → 삭제)
  - 체크리스트 작성 및 검증

- [ ] **buglog.md 업데이트** (15분)
  - memo 컬럼 추가 작업 기록
  - 담당자 매핑 구현 기록
  - 새로운 버그 발견 시 기록

---

## ✅ 완료된 작업 (2025-12-29)

- [x] 로그아웃 버튼 추가
- [x] Leads 삭제 기능 API 연동
- [x] 새 문의 등록 후 첫 페이지 이동
- [x] 최신순 정렬
- [x] Leads API 필드 매핑 수정
- [x] 상태 값 한글→영문 변환
- [x] 401/422/500 에러 처리
- [x] memo 필드 임시 제거
- [x] Bug #9 문서화

---

## 📚 참고

**상세 문서**: `/Users/soona/Documents/인사이트/2025_MCRM/WORK_SESSION_2025-12-29.md`

**다음 시작 시**:
1. 백엔드 서버 실행: `cd mcrm-backend && php artisan serve`
2. 프론트엔드 서버 실행: `cd m-crm-project && npm run dev`
3. 우선순위 1번부터 시작

**테스트 계정**: admin / admin123!@#

**배포 URL**: https://insight-mcrm.vercel.app
