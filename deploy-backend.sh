#!/bin/bash
# Cafe24 백엔드 SFTP 자동 배포
# 사전 준비: 이 파일과 같은 위치에 .env.deploy 생성
#   CAFE24_SFTP_HOST=insightmcrm.mycafe24.com
#   CAFE24_SFTP_PORT=22
#   CAFE24_SFTP_USER=insightmcrm
#   CAFE24_SFTP_PASSWORD=실제비밀번호
#
# 사용법: ./deploy-backend.sh path/to/local/file.php /insightmcrm/laravel/path/to/remote/file.php [...반복 가능]

set -euo pipefail
cd "$(dirname "$0")"

if [ ! -f .env.deploy ]; then
  echo "❌ .env.deploy 파일이 없습니다. CAFE24_SFTP_HOST/PORT/USER/PASSWORD를 설정하세요." >&2
  exit 1
fi
# shellcheck disable=SC1091
source .env.deploy

if [ "$#" -eq 0 ] || [ "$(($# % 2))" -ne 0 ]; then
  echo "사용법: $0 <local1> <remote1> [<local2> <remote2> ...]" >&2
  exit 1
fi

LFTP_CMDS=""
while [ "$#" -gt 0 ]; do
  LOCAL="$1"; REMOTE="$2"; shift 2
  if [ ! -f "$LOCAL" ]; then
    echo "❌ 로컬 파일 없음: $LOCAL" >&2
    exit 1
  fi
  echo "📤 $LOCAL → $REMOTE"
  LFTP_CMDS+="put \"$LOCAL\" -o \"$REMOTE\";"
done

lftp -u "$CAFE24_SFTP_USER","$CAFE24_SFTP_PASSWORD" sftp://"$CAFE24_SFTP_HOST":"${CAFE24_SFTP_PORT:-22}" \
  -e "set sftp:auto-confirm yes; $LFTP_CMDS bye"

echo "✅ 업로드 완료"
