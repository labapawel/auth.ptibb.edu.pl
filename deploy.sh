#!/bin/bash

set -euo pipefail

# Laravel Auth PTI - Production Deployment Script
echo "🚀 Starting Laravel Auth PTI deployment..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker first."
    exit 1
fi

# Copy environment file
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.docker template..."
    cp .env.docker .env
    echo "⚠️  Please edit .env file with your production values!"
    echo "   Especially: APP_KEY, DB_PASSWORD, LDAP settings"
    read -p "Press Enter after editing .env file..."
fi

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔐 Generating application key..."
    # We'll generate this inside the container after build
fi

# Build and start containers (use Docker Compose v2)
echo "🏗️  Building Docker containers..."
docker compose down || true
if ! docker compose build --no-cache; then
    echo "❌ Build failed. Check the output above (composer/npm build, PHP extensions, etc.)."
    exit 1
fi

echo "🎯 Starting services..."

# Optional: check HTTP port availability to avoid confusing nginx bind errors
HOST_PORT=${HOST_HTTP_PORT:-80}
if ss -ltn 2>/dev/null | grep -q ":${HOST_PORT} "; then
    echo "⚠️  Host port ${HOST_PORT} is already in use. Nginx may fail to start."
    echo "   Set HOST_HTTP_PORT in .env to a free port (e.g., 8080) and rerun if needed."
fi

docker compose up -d

# Wait for database to be ready (poll mysqladmin inside the db container)
echo "⏳ Waiting for database to be ready..."
until docker compose exec db bash -lc 'mysqladmin ping -uroot -p"$MYSQL_ROOT_PASSWORD" >/dev/null 2>&1' ; do
    printf "."
    sleep 2
done
echo "\n✅ Database is ready."

# Generate app key in container if needed
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔐 Generating application key in container..."
    docker-compose exec app php artisan key:generate --force
fi

# Pre-migration cache cleanup to avoid stale caches (esp. routes with Closures)
echo "🧽 Clearing caches before migrations..."
# Remove cached routes at filesystem level first to avoid boot errors from cached closures
docker compose exec -u root app bash -lc 'rm -f /var/www/html/bootstrap/cache/routes-*.php || true'
# Then clear the rest via artisan (may still fail harmlessly, so ignore errors)
docker compose exec app bash -lc 'php artisan config:clear || true && php artisan cache:clear || true && php artisan view:clear || true && php artisan route:clear || true'

# Run migrations
echo "📊 Running database migrations..."
docker compose exec app php artisan migrate --force

# Clear and cache
echo "🧹 Clearing and caching configuration..."
docker compose exec app php artisan config:clear
docker compose exec app php artisan config:cache

# Route caching: only run if there are no route action Closures (anonymous functions as handlers)
echo "🔎 Checking routes for Closures..."
if grep -R -nE "Route::(get|post|put|patch|delete|options|any|match)\s*\([^)]*function\s*\(" --include="*.php" routes app >/dev/null 2>&1; then
    echo "⚠️  Route action Closures detected — skipping route:cache (closures cannot be serialized)."
else
    echo "✅ No route action Closures found — caching routes."
    docker compose exec app php artisan route:cache
fi

docker compose exec app php artisan view:cache

echo "✅ Deployment completed!"
echo ""
echo "🌐 Your application is now available at:"
echo "   Main App: http://localhost"
echo "   phpMyAdmin: http://localhost:8080"
echo ""
echo "📋 Useful commands:"
echo "   View logs: docker compose logs -f app"
echo "   Stop app: docker compose down"
echo "   Restart: docker compose restart"
echo "   Shell access: docker compose exec app bash"