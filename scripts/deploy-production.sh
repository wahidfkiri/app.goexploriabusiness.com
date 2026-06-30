#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${1:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
WEB_USER_GROUP="${WEB_USER_GROUP:-www-data:www-data}"

cd "$APP_DIR"

APP_WAS_UP=1
if ! "$PHP_BIN" artisan down --refresh=15 --secret="deploy-$(date +%s)"; then
  APP_WAS_UP=0
fi

cleanup() {
  if [ "$APP_WAS_UP" -eq 1 ]; then
    "$PHP_BIN" artisan up || true
  fi
}
trap cleanup EXIT

"$COMPOSER_BIN" install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --optimize-autoloader

if [ ! -L public/storage ]; then
  "$PHP_BIN" artisan storage:link || true
fi

"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan queue:restart || true
"$PHP_BIN" artisan horizon:terminate || true

if command -v sudo >/dev/null 2>&1; then
  sudo chown -R "$WEB_USER_GROUP" storage bootstrap/cache || true
  sudo chmod -R ug+rw storage bootstrap/cache || true
else
  chmod -R ug+rw storage bootstrap/cache || true
fi

if [ "$APP_WAS_UP" -eq 1 ]; then
  "$PHP_BIN" artisan up
fi