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

# ====== Chạy queue worker ======
log "Starting Laravel queue worker (RabbitMQ)..."
php artisan queue:work rabbitmq \
    --queue="${QUEUE_NAMES:-default}" \
    --tries="${QUEUE_TRIES:-3}" \
    --max-jobs="${QUEUE_MAX_JOBS:-500}" \
    --max-time="${QUEUE_MAX_TIME:-3600}" \
    --memory="${QUEUE_MEMORY:-256}" &


# ====== Start scheduler ======
log "Starting Laravel scheduler..."
php artisan schedule:work &
PORT="${PORT:-8000}"
HOST="${HOST:-0.0.0.0}"

log "Starting Octane + FrankenPHP on ${HOST}:${PORT}..."
exec php artisan octane:frankenphp --host="${HOST}" --port="${PORT}"
