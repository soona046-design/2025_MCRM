#!/bin/bash

# Cafe24 배포 패키지 생성 스크립트

echo "🚀 Cafe24 배포 패키지 생성 중..."

# 패키지 디렉토리 생성
PACKAGE_DIR="cafe24-package-$(date +%Y%m%d_%H%M%S)"
mkdir -p "$PACKAGE_DIR"

echo "📦 파일 복사 중..."

# www 폴더 복사
cp -r www "$PACKAGE_DIR/"

# laravel 폴더 복사 (vendor 제외)
rsync -av --exclude='vendor' laravel/ "$PACKAGE_DIR/laravel/"

echo "✅ 파일 복사 완료"

# 압축
echo "🗜️  압축 중..."
tar -czf "${PACKAGE_DIR}.tar.gz" "$PACKAGE_DIR"

# 정리
rm -rf "$PACKAGE_DIR"

echo "✅ 배포 패키지 생성 완료: ${PACKAGE_DIR}.tar.gz"
echo ""
echo "📤 다음 단계:"
echo "1. Cafe24 서버에 업로드"
echo "2. 압축 해제: tar -xzf ${PACKAGE_DIR}.tar.gz"
echo "3. composer install 실행"
echo "4. DEPLOY_GUIDE.md 참고하여 설정"
