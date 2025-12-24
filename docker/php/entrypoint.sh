#!/bin/sh
set -e

# Entrypoint script for PHP-FPM container
# Handles application initialization and startup

echo "[PHP-FPM Entrypoint] Starting application initialization..."

# Wait for database to be ready
echo "[PHP-FPM] Waiting for MySQL to be ready..."
while ! nc -z mysql 3306 2>/dev/null; do
    sleep 1
done
echo "[PHP-FPM] MySQL is ready!"

# Wait for Redis to be ready
echo "[PHP-FPM] Waiting for Redis to be ready..."
while ! nc -z redis 6379 2>/dev/null; do
    sleep 1
done
echo "[PHP-FPM] Redis is ready!"

# Install dependencies if not already installed (development mode)
if [ ! -f "/app/vendor/autoload.php" ] && [ -f "/app/composer.json" ]; then
    echo "[PHP-FPM] Installing Composer dependencies..."
    composer install --prefer-dist --no-interaction --no-progress || {
        echo "[PHP-FPM] Warning: Composer install failed. Container will continue but you need to install dependencies manually."
        echo "[PHP-FPM] Run: docker compose exec php composer install"
    }
fi

# Only run Laravel commands if autoload.php exists
if [ -f "/app/vendor/autoload.php" ]; then
    # Generate app key if Laravel is initialized and APP_KEY is not set
    if [ -f "/app/artisan" ]; then
        if ! grep -q "APP_KEY=" /app/.env 2>/dev/null || grep -q "APP_KEY=$" /app/.env; then
            echo "[PHP-FPM] Generating application key..."
            php artisan key:generate --force || echo "[PHP-FPM] Warning: Failed to generate app key"
        fi
    fi

    # Run pending migrations
    if [ -f "/app/artisan" ]; then
        echo "[PHP-FPM] Running database migrations..."
        php artisan migrate --force || echo "[PHP-FPM] Warning: Migration failed"
    fi
else
    echo "[PHP-FPM] Warning: /app/vendor directory not found. Skipping Laravel initialization."
    echo "[PHP-FPM] Please run: docker compose exec php composer install"
fi

echo "[PHP-FPM] Application initialization complete!"
echo "[PHP-FPM] Starting PHP-FPM server..."

# Execute the main command passed to the container
exec "$@"
