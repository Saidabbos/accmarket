#!/bin/bash

# =============================================================================
# PRODUCTION DEPLOYMENT SCRIPT
# =============================================================================
# AccMarket - Digital Marketplace Platform
# Usage: ./deploy.sh [command]
#
# Commands:
#   deploy    - Full deployment (default)
#   update    - Update application only (no rebuild)
#   rollback  - Rollback to previous version
#   status    - Show deployment status
#   logs      - Show application logs
#   backup    - Create database backup
#   restore   - Restore from backup
# =============================================================================

set -e

# Configuration
APP_NAME="accmarket"
COMPOSE_FILE="docker-compose.prod.yml"
BACKUP_DIR="./backups"
LOG_FILE="./deploy.log"
MAINTENANCE_FILE="./storage/framework/down"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# =============================================================================
# Helper Functions
# =============================================================================

log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

check_requirements() {
    log "Checking requirements..."

    if ! command -v docker &> /dev/null; then
        error "Docker is not installed"
    fi

    if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
        error "Docker Compose is not installed"
    fi

    if [ ! -f ".env" ]; then
        error ".env file not found. Copy .env.production to .env and configure it"
    fi

    success "All requirements met"
}

# =============================================================================
# Maintenance Mode
# =============================================================================

enable_maintenance() {
    log "Enabling maintenance mode..."
    docker compose -f "$COMPOSE_FILE" exec -T php php artisan down --retry=60 --refresh=5 || true
    success "Maintenance mode enabled"
}

disable_maintenance() {
    log "Disabling maintenance mode..."
    docker compose -f "$COMPOSE_FILE" exec -T php php artisan up || true
    success "Maintenance mode disabled"
}

# =============================================================================
# Backup Functions
# =============================================================================

backup_database() {
    log "Creating database backup..."

    mkdir -p "$BACKUP_DIR"

    BACKUP_FILE="$BACKUP_DIR/db_backup_$(date '+%Y%m%d_%H%M%S').sql.gz"

    docker compose -f "$COMPOSE_FILE" exec -T mysql mysqldump \
        -u root -p"${DB_ROOT_PASSWORD}" \
        --single-transaction \
        --quick \
        --lock-tables=false \
        "${DB_DATABASE}" | gzip > "$BACKUP_FILE"

    success "Database backup created: $BACKUP_FILE"
}

backup_storage() {
    log "Creating storage backup..."

    mkdir -p "$BACKUP_DIR"

    BACKUP_FILE="$BACKUP_DIR/storage_backup_$(date '+%Y%m%d_%H%M%S').tar.gz"

    tar -czf "$BACKUP_FILE" ./storage/app

    success "Storage backup created: $BACKUP_FILE"
}

# =============================================================================
# Deployment Functions
# =============================================================================

pull_latest() {
    log "Pulling latest code from repository..."
    git fetch origin
    git pull origin main
    success "Code updated"
}

build_containers() {
    log "Building Docker containers..."
    docker compose -f "$COMPOSE_FILE" build --no-cache
    success "Containers built"
}

start_containers() {
    log "Starting containers..."
    docker compose -f "$COMPOSE_FILE" up -d
    success "Containers started"
}

stop_containers() {
    log "Stopping containers..."
    docker compose -f "$COMPOSE_FILE" down
    success "Containers stopped"
}

install_dependencies() {
    log "Installing Composer dependencies..."
    docker compose -f "$COMPOSE_FILE" exec -T php composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader
    success "Dependencies installed"
}

run_migrations() {
    log "Running database migrations..."
    docker compose -f "$COMPOSE_FILE" exec -T php php artisan migrate --force
    success "Migrations completed"
}

clear_caches() {
    log "Clearing caches..."
    docker compose -f "$COMPOSE_FILE" exec -T php php artisan config:clear
    docker compose -f "$COMPOSE_FILE" exec -T php php artisan route:clear
    docker compose -f "$COMPOSE_FILE" exec -T php php artisan view:clear
    docker compose -f "$COMPOSE_FILE" exec -T php php artisan cache:clear
    success "Caches cleared"
}

optimize_application() {
    log "Optimizing application..."
    docker compose -f "$COMPOSE_FILE" exec -T php php artisan config:cache
    docker compose -f "$COMPOSE_FILE" exec -T php php artisan route:cache
    docker compose -f "$COMPOSE_FILE" exec -T php php artisan view:cache
    docker compose -f "$COMPOSE_FILE" exec -T php php artisan event:cache
    success "Application optimized"
}

build_assets() {
    log "Building frontend assets..."
    docker compose -f "$COMPOSE_FILE" exec -T php npm ci
    docker compose -f "$COMPOSE_FILE" exec -T php npm run build
    success "Assets built"
}

restart_queue() {
    log "Restarting queue workers..."
    docker compose -f "$COMPOSE_FILE" restart queue
    success "Queue workers restarted"
}

health_check() {
    log "Running health check..."

    sleep 10

    HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health)

    if [ "$HTTP_STATUS" = "200" ]; then
        success "Health check passed (HTTP $HTTP_STATUS)"
    else
        error "Health check failed (HTTP $HTTP_STATUS)"
    fi
}

# =============================================================================
# Main Commands
# =============================================================================

deploy() {
    log "Starting full deployment..."

    check_requirements

    # Create backups before deployment
    if docker compose -f "$COMPOSE_FILE" ps | grep -q "Up"; then
        backup_database
        backup_storage
    fi

    # Enable maintenance mode if containers are running
    if docker compose -f "$COMPOSE_FILE" ps | grep -q "Up"; then
        enable_maintenance
    fi

    pull_latest
    build_containers
    start_containers

    # Wait for containers to be ready
    log "Waiting for containers to be ready..."
    sleep 30

    install_dependencies
    run_migrations
    clear_caches
    optimize_application
    restart_queue

    disable_maintenance
    health_check

    success "Deployment completed successfully!"
}

update() {
    log "Starting application update..."

    check_requirements
    enable_maintenance

    pull_latest
    install_dependencies
    run_migrations
    clear_caches
    optimize_application
    restart_queue

    disable_maintenance
    health_check

    success "Update completed successfully!"
}

rollback() {
    log "Starting rollback..."

    # Get previous commit
    PREVIOUS_COMMIT=$(git rev-parse HEAD~1)

    warning "Rolling back to commit: $PREVIOUS_COMMIT"

    enable_maintenance

    git reset --hard "$PREVIOUS_COMMIT"

    install_dependencies
    run_migrations
    clear_caches
    optimize_application
    restart_queue

    disable_maintenance

    success "Rollback completed!"
}

status() {
    log "Deployment Status:"
    echo ""
    docker compose -f "$COMPOSE_FILE" ps
    echo ""

    log "Resource Usage:"
    docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}"
}

logs() {
    SERVICE=${2:-""}

    if [ -z "$SERVICE" ]; then
        docker compose -f "$COMPOSE_FILE" logs -f --tail=100
    else
        docker compose -f "$COMPOSE_FILE" logs -f --tail=100 "$SERVICE"
    fi
}

# =============================================================================
# Script Entry Point
# =============================================================================

COMMAND=${1:-"deploy"}

case "$COMMAND" in
    deploy)
        deploy
        ;;
    update)
        update
        ;;
    rollback)
        rollback
        ;;
    status)
        status
        ;;
    logs)
        logs "$@"
        ;;
    backup)
        backup_database
        backup_storage
        ;;
    restore)
        error "Restore command not implemented yet"
        ;;
    maintenance-on)
        enable_maintenance
        ;;
    maintenance-off)
        disable_maintenance
        ;;
    build)
        build_containers
        ;;
    start)
        start_containers
        ;;
    stop)
        stop_containers
        ;;
    restart)
        stop_containers
        start_containers
        ;;
    *)
        echo "Usage: $0 {deploy|update|rollback|status|logs|backup|restore|maintenance-on|maintenance-off|build|start|stop|restart}"
        exit 1
        ;;
esac
