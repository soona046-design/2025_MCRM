# M-CRM Cafe24 배포 패키지

Medical Customer Relationship Management System의 Cafe24 웹호스팅 배포 패키지입니다.

## 📦 패키지 내용

- **www/**: 웹루트 파일 (Cafe24 public_html에 업로드)
- **laravel/**: Laravel 백엔드 소스코드
- **DEPLOY_GUIDE.md**: 상세 배포 가이드

## 🚀 빠른 시작

### 1. Composer 의존성 설치

```bash
cd laravel
composer install --optimize-autoloader --no-dev
```

### 2. FTP 업로드

```
www/     → /public_html/
laravel/ → /laravel/
```

### 3. 환경 설정

```bash
cp laravel/.env.cafe24 laravel/.env
# .env 파일 편집 (DB 정보, APP_KEY 등)
```

### 4. 데이터베이스 마이그레이션

Cafe24 phpMyAdmin에서 SQL import 또는 SSH로 마이그레이션 실행

### 5. 테스트

```
https://yourdomain.cafe24.com/api/health
```

## 📖 상세 가이드

전체 배포 절차는 [DEPLOY_GUIDE.md](./DEPLOY_GUIDE.md)를 참고하세요.

## 🔧 시스템 요구사항

- PHP 8.2 이상
- MySQL 8.0 이상
- Composer
- Apache (mod_rewrite 활성화)

## 📁 디렉토리 구조

```
/home/yourid/
├── public_html/          # 웹루트
│   ├── index.php
│   └── .htaccess
└── laravel/              # Laravel 소스
    ├── app/
    ├── config/
    ├── database/
    ├── storage/
    └── vendor/
```

## ⚠️ 주의사항

1. `.env` 파일에 실제 DB 정보 입력 필수
2. `storage/` 폴더 권한 755 설정
3. `APP_KEY` 반드시 생성하여 설정
4. 프로덕션 모드에서는 `APP_DEBUG=false` 설정

## 📞 지원

문제 발생 시 DEPLOY_GUIDE.md의 트러블슈팅 섹션을 참고하세요.

---

**생성일:** 2025-11-20
**버전:** 1.0
**Laravel:** 12.x
**PHP:** 8.2+
