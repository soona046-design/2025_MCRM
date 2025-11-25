# Cafe24 배포 패키지 정보

## 📦 패키지 구성

```
cafe24-deploy/
├── www/                          # 웹루트 (5개 파일)
│   ├── index.php                 # Laravel 진입점 (경로 수정됨)
│   ├── .htaccess                 # Apache Rewrite 규칙
│   ├── favicon.ico
│   └── robots.txt
│
├── laravel/                      # Laravel 백엔드 (전체 소스)
│   ├── app/                      # 애플리케이션 로직
│   │   ├── Console/             # CLI 명령어
│   │   ├── Events/              # 이벤트
│   │   ├── Helpers/             # 헬퍼 (ChannelCategoryHelper)
│   │   ├── Http/                # 컨트롤러 & 미들웨어
│   │   │   └── Controllers/Api/ # API 컨트롤러들
│   │   ├── Jobs/                # 백그라운드 작업
│   │   ├── Listeners/           # 이벤트 리스너
│   │   ├── Models/              # Eloquent 모델
│   │   ├── Providers/           # 서비스 프로바이더
│   │   ├── Services/            # 비즈니스 로직
│   │   └── Traits/              # 재사용 가능 Trait
│   │
│   ├── bootstrap/               # 부트스트랩
│   │   ├── app.php
│   │   └── cache/               # 부트스트랩 캐시
│   │
│   ├── config/                  # 설정 파일
│   │   ├── database.php
│   │   ├── cors.php
│   │   ├── sanctum.php
│   │   └── ...
│   │
│   ├── database/                # 데이터베이스
│   │   ├── migrations/          # 마이그레이션 (26개)
│   │   ├── seeders/             # 시더 (10개)
│   │   └── factories/           # 팩토리 (5개)
│   │
│   ├── routes/                  # 라우트
│   │   ├── api.php              # API 라우트
│   │   ├── web.php              # Web 라우트
│   │   └── console.php
│   │
│   ├── storage/                 # 저장소
│   │   ├── app/
│   │   ├── framework/           # 캐시, 세션, 뷰
│   │   └── logs/                # 로그 파일
│   │
│   ├── vendor/                  # Composer 의존성 ✅ 설치됨
│   ├── .env.cafe24              # Cafe24용 환경설정 예제
│   ├── composer.json            # 의존성 정의
│   └── artisan                  # CLI 도구
│
├── README.md                    # 빠른 시작 가이드
├── DEPLOY_GUIDE.md              # 상세 배포 가이드
├── create-package.sh            # 패키징 스크립트
└── .gitignore                   # Git 제외 파일
```

## ✅ 설치 상태

- [x] Laravel 소스 코드 복사 완료
- [x] Composer 의존성 설치 완료 (88개 패키지)
- [x] index.php 경로 수정 완료
- [x] .env.cafe24 설정 파일 준비 완료
- [x] 배포 가이드 작성 완료
- [x] storage 디렉토리 생성 완료

## 📊 통계

- **총 파일 수**: 약 200개
- **Laravel 버전**: 12.x
- **PHP 요구사항**: 8.2+
- **Composer 패키지**: 88개
- **마이그레이션**: 26개
- **Seeder**: 10개
- **API 컨트롤러**: 13개

## 🚀 즉시 배포 가능

이 패키지는 다음만 수행하면 즉시 배포 가능합니다:

1. **FTP 업로드**
   ```
   www/     → /public_html/
   laravel/ → /laravel/
   ```

2. **.env 설정**
   ```bash
   cd laravel
   cp .env.cafe24 .env
   # DB 정보 및 APP_KEY 입력
   ```

3. **데이터베이스 설정**
   - Cafe24에서 MySQL DB 생성
   - phpMyAdmin으로 마이그레이션 실행

4. **권한 설정**
   ```bash
   chmod -R 755 laravel/storage
   chmod -R 755 laravel/bootstrap/cache
   ```

## 📝 주요 기능

### API 엔드포인트
- `/api/dashboards/channel-pivot` - 채널 피벗 대시보드
- `/api/dashboards/funnel` - 퍼널 분석
- `/api/dashboards/agent-performance` - 상담원 성과
- `/api/leads` - 리드 관리
- `/api/tickets` - 티켓 관리
- `/api/appointments` - 예약 관리
- `/api/users` - 사용자 관리
- `/api/visits` - 방문자 추적
- `/api/auth/*` - 인증

### 데이터베이스 테이블
- users, leads, tickets, appointments
- visits, communications, audit_logs
- channel_categories, channel_category_mappings
- ad_metrics, cost_imports
- notifications, sessions

### 주요 기능
- ✅ 채널 카테고리화 (온라인/오프라인/DB)
- ✅ 날짜 범위 필터링
- ✅ ROI 분석
- ✅ 퍼널 추적
- ✅ 상담원 성과 관리
- ✅ 감사 로그
- ✅ 실시간 알림

## 🔧 필수 설정 항목

### .env 파일에서 반드시 수정해야 할 항목:

```env
APP_KEY=                          # php artisan key:generate로 생성
APP_URL=https://yourdomain.cafe24.com

DB_DATABASE=your_database_name    # Cafe24 DB명
DB_USERNAME=your_database_user    # Cafe24 DB 사용자
DB_PASSWORD=your_database_password # Cafe24 DB 비밀번호

SANCTUM_STATEFUL_DOMAINS=yourdomain.cafe24.com
SESSION_DOMAIN=.cafe24.com
```

## 📂 Cafe24 서버 구조

```
/home/your_cafe24_id/
├── public_html/          # 웹루트 (www 폴더 내용)
│   ├── index.php
│   └── .htaccess
├── laravel/              # Laravel 소스 (laravel 폴더 전체)
│   ├── app/
│   ├── storage/         # 755 권한 필수
│   ├── vendor/
│   └── .env             # 설정 파일
└── logs/                # Cafe24 로그
```

## 🔍 테스트 방법

배포 후 다음 URL로 확인:

```
https://yourdomain.cafe24.com/api/health
```

응답:
```json
{
  "status": "ok",
  "timestamp": "2025-11-20T16:30:00Z"
}
```

## 📞 지원

- **배포 가이드**: DEPLOY_GUIDE.md
- **빠른 시작**: README.md
- **프로젝트 이슈**: GitHub Issues

## 📅 생성 정보

- **생성일**: 2025-11-20
- **버전**: 1.0.0
- **Laravel**: 12.x
- **PHP**: 8.2+
- **패키지 크기**: ~50MB (vendor 포함)

---

**준비 완료!** 이제 Cafe24에 배포하실 수 있습니다. 🚀
