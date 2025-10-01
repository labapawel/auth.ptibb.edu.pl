@echo off
REM Laravel Auth PTI - Production Deployment Script for Windows

echo 🚀 Starting Laravel Auth PTI deployment...

REM Check if Docker is running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker is not running. Please start Docker first.
    pause
    exit /b 1
)

REM Copy environment file
if not exist .env (
    echo 📝 Creating .env file from .env.docker template...
    copy .env.docker .env
    echo ⚠️  Please edit .env file with your production values!
    echo    Especially: APP_KEY, DB_PASSWORD, LDAP settings
    pause
)

REM Build and start containers
echo 🏗️  Building Docker containers...
docker-compose down
docker-compose build --no-cache

echo 🎯 Starting services...
docker-compose up -d

REM Wait for database to be ready
echo ⏳ Waiting for database to be ready...
timeout /t 30 /nobreak >nul

REM Generate app key in container if needed
echo 🔐 Generating application key in container...
docker-compose exec app php artisan key:generate --force

REM Run migrations
echo 📊 Running database migrations...
docker-compose exec app php artisan migrate --force

REM Clear and cache
echo 🧹 Clearing and caching configuration...
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

echo ✅ Deployment completed!
echo.
echo 🌐 Your application is now available at:
echo    Main App: http://localhost
echo    phpMyAdmin: http://localhost:8080
echo.
echo 📋 Useful commands:
echo    View logs: docker-compose logs -f app
echo    Stop app: docker-compose down
echo    Restart: docker-compose restart
echo    Shell access: docker-compose exec app bash

pause