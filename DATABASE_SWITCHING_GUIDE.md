# Database Switching Guide: MySQL + SQLite

This guide explains how to use both MySQL and SQLite databases in your Laravel application and switch between them for different environments.

## Current Configuration

Your application already supports both databases:
- **SQLite**: Currently the default (for development/testing)
- **MySQL**: Available and configured (for production)

## Environment-Based Database Selection

### Method 1: Environment Variables (Recommended)

#### For Development (SQLite)
Create `.env.local` or keep your current `.env`:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/Applications/MAMP/htdocs/ClientManagerLaravelVue/database/database.sqlite
```

#### For Production (MySQL)
Create `.env.production`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=client_manager
DB_USERNAME=client_manager_user
DB_PASSWORD=your_strong_password
```

#### For Testing (Separate SQLite)
Create `.env.testing`:
```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
# OR use a separate test database file
# DB_DATABASE=/Applications/MAMP/htdocs/ClientManagerLaravelVue/database/testing.sqlite
```

## Quick Switch Commands

### Switch to MySQL
```bash
# Update your .env file
echo "DB_CONNECTION=mysql" > .env.tmp
echo "DB_HOST=127.0.0.1" >> .env.tmp
echo "DB_PORT=3306" >> .env.tmp
echo "DB_DATABASE=client_manager" >> .env.tmp
echo "DB_USERNAME=client_manager_user" >> .env.tmp
echo "DB_PASSWORD=your_password" >> .env.tmp

# Backup current .env and switch
cp .env .env.backup
cp .env.tmp .env
rm .env.tmp

# Clear config cache and test connection
php artisan config:clear
php artisan migrate:status
```

### Switch to SQLite
```bash
# Update your .env file
echo "DB_CONNECTION=sqlite" > .env.tmp
echo "DB_DATABASE=/Applications/MAMP/htdocs/ClientManagerLaravelVue/database/database.sqlite" >> .env.tmp

# Backup current .env and switch
cp .env .env.backup
cp .env.tmp .env
rm .env.tmp

# Clear config cache and test connection
php artisan config:clear
php artisan migrate:status
```

## Database Setup Instructions

### SQLite Setup (Already Working)
```bash
# Create database file if it doesn't exist
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

### MySQL Setup

#### 1. Install MySQL (if not already installed)
```bash
# macOS with Homebrew
brew install mysql
brew services start mysql

# Ubuntu/Debian
sudo apt update
sudo apt install mysql-server

# Windows - Download from MySQL website
```

#### 2. Create Database and User
```sql
-- Connect to MySQL as root
mysql -u root -p

-- Create database
CREATE DATABASE client_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (replace with your password)
CREATE USER 'client_manager_user'@'localhost' IDENTIFIED BY 'your_strong_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON client_manager.* TO 'client_manager_user'@'localhost';

-- Reload privileges
FLUSH PRIVILEGES;

-- Exit MySQL
EXIT;
```

#### 3. Update Environment and Migrate
```bash
# Update .env to use MySQL (see above)
# Then run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

## Data Migration Between Databases

### From SQLite to MySQL
```bash
# 1. Backup SQLite data
php artisan db:seed --class=DatabaseSeeder --database=sqlite

# 2. Switch to MySQL (update .env)
# 3. Run migrations on MySQL
php artisan migrate --database=mysql

# 4. Manually export/import data or use Laravel commands
php artisan tinker
```

### From MySQL to SQLite
```bash
# 1. Backup MySQL data
mysqldump -u client_manager_user -p client_manager > backup.sql

# 2. Switch to SQLite (update .env)
# 3. Run migrations on SQLite
php artisan migrate --database=sqlite

# 4. Import data using Laravel or manual process
```

## Multiple Database Connections

You can also use both databases simultaneously by defining multiple connections.

### Example: Using Both Databases in Code
```php
// In your models or controllers

// Use SQLite connection
$sqliteUsers = DB::connection('sqlite')->table('users')->get();

// Use MySQL connection  
$mysqlUsers = DB::connection('mysql')->table('users')->get();

// Or specify connection in model
class User extends Model 
{
    protected $connection = 'mysql'; // or 'sqlite'
}
```

## Environment-Specific Configuration

### PHPUnit Testing with SQLite
Update `phpunit.xml`:
```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
</php>
```

### Docker Configuration
```yaml
# docker-compose.yml
version: '3.8'
services:
  app:
    build: .
    environment:
      - DB_CONNECTION=mysql
      - DB_HOST=mysql
      - DB_DATABASE=client_manager
  
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: client_manager
      MYSQL_ROOT_PASSWORD: root
      MYSQL_PASSWORD: password
```

## Best Practices

### 1. Environment-Specific Configs
- **Development**: SQLite (fast, no setup required)
- **Testing**: In-memory SQLite (fastest)
- **Staging**: MySQL (production-like)
- **Production**: MySQL (scalable, robust)

### 2. Database Switching Script
Create a helper script `scripts/switch-db.sh`:
```bash
#!/bin/bash

if [ "$1" = "mysql" ]; then
    echo "Switching to MySQL..."
    cp .env.mysql .env
elif [ "$1" = "sqlite" ]; then
    echo "Switching to SQLite..."
    cp .env.sqlite .env
else
    echo "Usage: ./switch-db.sh [mysql|sqlite]"
    exit 1
fi

php artisan config:clear
php artisan migrate:status
echo "Database switched to $1"
```

### 3. Backup Strategy
```bash
# SQLite backup
cp database/database.sqlite "database/backup-$(date +%Y%m%d).sqlite"

# MySQL backup
mysqldump -u client_manager_user -p client_manager > "backup-$(date +%Y%m%d).sql"
```

## Troubleshooting

### Common Issues

#### 1. Connection Refused (MySQL)
```bash
# Check if MySQL is running
brew services list | grep mysql  # macOS
sudo systemctl status mysql      # Linux

# Start MySQL if not running
brew services start mysql        # macOS
sudo systemctl start mysql       # Linux
```

#### 2. Permission Denied (SQLite)
```bash
# Fix file permissions
chmod 664 database/database.sqlite
chmod 775 database/
```

#### 3. Migration Issues
```bash
# Check current connection
php artisan tinker
DB::connection()->getDatabaseName();

# Reset migrations if needed
php artisan migrate:reset
php artisan migrate
```

#### 4. Cache Issues
```bash
# Clear all Laravel caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

## Performance Considerations

### SQLite Pros:
- ✅ Zero configuration
- ✅ File-based (easy backup)
- ✅ Fast for small to medium datasets
- ✅ Perfect for development/testing
- ✅ No separate server required

### MySQL Pros:
- ✅ Better for large datasets
- ✅ Concurrent connections
- ✅ Advanced features (triggers, procedures)
- ✅ Better for production environments
- ✅ Horizontal scaling options

## Deployment Considerations

When deploying to production:

1. **Always use MySQL for production**
2. **Keep SQLite for development**
3. **Use environment-specific .env files**
4. **Test migrations on both databases**
5. **Have backup strategies for both**

Your application is now configured to use both databases efficiently! 