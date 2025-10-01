# Laravel Auth PTI - Docker Deployment

## 🚀 Quick Start

### Prerequisites
- Docker Desktop installed and running
- Git (for cloning the repository)

### Deployment Steps

1. **Clone the repository** (if not already done):
   ```bash
   git clone <your-repo-url>
   cd laravel-auth-pti
   ```

2. **Configure environment**:
   ```bash
   # Copy the Docker environment template
   cp .env.docker .env
   
   # Edit .env with your production values
   nano .env  # or your preferred editor
   ```

3. **Deploy the application**:
   
   **On Linux/Mac:**
   ```bash
   chmod +x deploy.sh
   ./deploy.sh
   ```
   
   **On Windows:**
   ```cmd
   deploy.bat
   ```

4. **Access your application**:
   - Main Application: http://localhost
   - phpMyAdmin: http://localhost:8080

## 📋 Environment Configuration

Edit the `.env` file with your production values:

```bash
# Application
APP_KEY=                    # Will be generated automatically
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

# Database
DB_DATABASE=laravel_auth_pti
DB_USERNAME=laravel_user
DB_PASSWORD=your_secure_password

# LDAP Configuration
LDAP_HOST=your-ldap-server.com
LDAP_PORT=389
LDAP_BASE_DN=dc=example,dc=com
LDAP_USERNAME=cn=admin,dc=example,dc=com
LDAP_PASSWORD=your-ldap-password
```

## 🐳 Docker Services

- **app**: Laravel application (PHP 8.2 + Apache)
- **db**: MySQL 8.0 database
- **phpmyadmin**: Database management interface

## 🔧 Management Commands

```bash
# View application logs
docker-compose logs -f app

# View all logs
docker-compose logs -f

# Restart the application
docker-compose restart app

# Stop all services
docker-compose down

# Rebuild and restart (after code changes)
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Access application shell
docker-compose exec app bash

# Run Laravel commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:cache
```

## 🔒 Security Considerations

1. **Change default passwords** in `.env` file
2. **Configure HTTPS** for production (use reverse proxy like nginx)
3. **Set proper APP_URL** to your domain
4. **Configure firewall** to limit database access
5. **Regular backups** of database and storage

## 📊 Database Backup

```bash
# Create backup
docker-compose exec db mysqldump -u root -p laravel_auth_pti > backup.sql

# Restore backup
docker-compose exec -T db mysql -u root -p laravel_auth_pti < backup.sql
```

## 🔍 Troubleshooting

### Common Issues:

1. **Port conflicts**: Change ports in `docker-compose.yml` if 80 or 3306 are in use
2. **Permission issues**: Run `docker-compose exec app chown -R www-data:www-data /var/www/html/storage`
3. **Database connection**: Ensure database is running: `docker-compose ps`
4. **LDAP issues**: Check network connectivity to LDAP server from container

### Logs:
```bash
# Application logs
docker-compose logs app

# Database logs  
docker-compose logs db

# All services
docker-compose logs
```

## 🔄 Updates

To update the application:

1. Pull latest code: `git pull`
2. Rebuild containers: `docker-compose build --no-cache`
3. Restart: `docker-compose up -d`
4. Run migrations: `docker-compose exec app php artisan migrate`

## 🏗️ Production Deployment

For production environments:

1. Use a reverse proxy (nginx/Apache) for HTTPS
2. Configure proper domain in APP_URL
3. Set up automated backups
4. Use Docker secrets for sensitive data
5. Configure monitoring and logging
6. Set up proper firewall rules

## 📞 Support

For issues or questions:
1. Check application logs
2. Verify environment configuration
3. Ensure all services are running
4. Check network connectivity for LDAP