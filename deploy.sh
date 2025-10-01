#!/bin/bash

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

# Build and start containers
echo "🏗️  Building Docker containers..."
docker-compose down
docker-compose build --no-cache

echo "🎯 Starting services..."
docker-compose up -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 30

# Generate app key in container if needed
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔐 Generating application key in container..."
    docker-compose exec app php artisan key:generate --force
fi

# Run migrations
echo "📊 Running database migrations..."
docker-compose exec app php artisan migrate --force

# Clear and cache
echo "🧹 Clearing and caching configuration..."
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

echo "✅ Deployment completed!"
echo ""
echo "🌐 Your application is now available at:"
echo "   Main App: http://localhost"
echo "   phpMyAdmin: http://localhost:8080"
echo ""
echo "📋 Useful commands:"
echo "   View logs: docker-compose logs -f app"
echo "   Stop app: docker-compose down"
echo "   Restart: docker-compose restart"
echo "   Shell access: docker-compose exec app bash"