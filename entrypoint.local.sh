#!/usr/bin/env bash
set -euo pipefail

log() { echo "[$(date +'%Y-%m-%d %H:%M:%S')] $*"; }

term_handler() {
  log "Stopping background workers..."
  jobs -p | xargs -r kill -TERM || true
  wait || true
  exit 0
}
trap term_handler SIGTERM SIGINT

# Check and install composer dependencies if autoload.php is missing
if [ ! -f "/app/vendor/autoload.php" ]; then
  log "Vendor autoload not found. Installing composer dependencies..."
  composer install --no-scripts --no-interaction
  log "Composer dependencies installed successfully."
  log "Note: composer-patches plugin will automatically apply patches during install."
fi

# Đảm bảo chokidar có trong project (phòng trường hợp bind mount đè node_modules)
if ! node -e "require('chokidar')" >/dev/null 2>&1; then
  log "Installing chokidar (dev dependency)..."
  npm i -D chokidar
fi

# Khuyến nghị bật polling khi chạy trong Docker để watch qua bind mount ổn định
export CHOKIDAR_USEPOLLING="${CHOKIDAR_USEPOLLING:-1}"
export CHOKIDAR_INTERVAL="${CHOKIDAR_INTERVAL:-200}"

if [[ "${RUN_QUEUE:-0}" = "1" ]]; then
  log "Starting Laravel queue worker..."
  php artisan queue:work "${QUEUE_CONNECTION:-database}" \
    --queue="${QUEUE_NAMES:-default}" \
    --tries="${QUEUE_TRIES:-3}" \
    --max-jobs="${QUEUE_MAX_JOBS:-500}" \
    --max-time="${QUEUE_MAX_TIME:-3600}" \
    --memory="${QUEUE_MEMORY:-256}" &
fi

if [[ "${RUN_SCHEDULER:-0}" = "1" ]]; then
  log "Starting Laravel scheduler..."
  php artisan schedule:work &
fi

PORT="${PORT:-8000}"
HOST="${HOST:-0.0.0.0}"

log "Starting Octane + FrankenPHP with --watch (and --poll) on ${HOST}:${PORT}..."
# --poll giúp watch ổn định qua network FS trong container
exec php artisan octane:frankenphp --host="${HOST}" --port="${PORT}" --watch --poll
