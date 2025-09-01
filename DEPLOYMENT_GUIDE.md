# Deployment Guide: Laravel + Vue.js on Vultr Ubuntu Server

This guide will walk you through deploying your Client Manager Laravel Vue.js application to a Vultr Ubuntu server.

## Prerequisites

- Vultr account with an Ubuntu 22.04 LTS server
- Domain name (optional but recommended)
- SSH access to your server
- Basic knowledge of Linux commands

## Step 1: Server Setup

### 1.1 Create Vultr Server
1. Log into your Vultr account
2. Deploy a new server:
   - **Server Type**: Cloud Compute - Regular Performance
   - **Location**: Choose closest to your users
   - **Server Image**: Ubuntu 22.04 LTS x64
   - **Server Size**: At least 1GB RAM (2GB+ recommended)
   - **SSH Keys**: Add your SSH key or use password

### 1.2 Initial Server Access
```bash
# Connect to your server
ssh root@YOUR_SERVER_IP

# Update system packages
apt update && apt upgrade -y

# Create a non-root user (replace 'deploy' with your preferred username)
adduser deploy
usermod -aG sudo deploy

# Switch to the new user
su - deploy
```

## Step 2: Install Required Software

### 2.1 Install Nginx
```bash
sudo apt install nginx -y
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 2.2 Install PHP 8.2 and Extensions
```bash
# Add PHP repository
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP and required extensions
sudo apt install php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-mbstring php8.2-sqlite3 php8.2-intl php8.2-bcmath -y

# Start and enable PHP-FPM
sudo systemctl start php8.2-fpm
sudo systemctl enable php8.2-fpm
```

### 2.3 Install Composer
```bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 2.4 Install Node.js and npm
```bash
# Install Node.js 18.x LTS
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verify installation
node --version
npm --version
```

### 2.5 Install MySQL (Recommended for Production)
```bash
# Install MySQL Server
sudo apt install mysql-server -y

# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

**In MySQL console, run these commands:**
```sql
-- Create database with proper charset
CREATE DATABASE client_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create dedicated user
CREATE USER 'client_manager_user'@'localhost' IDENTIFIED BY 'your_strong_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON client_manager.* TO 'client_manager_user'@'localhost';

-- Reload privileges
FLUSH PRIVILEGES;

-- Verify the database was created
SHOW DATABASES;

-- Exit MySQL
EXIT;
```

**Verify MySQL installation:**
```bash
# Check MySQL status
sudo systemctl status mysql

# Test connection
mysql -u client_manager_user -p client_manager
```

### 2.6 SQLite Setup (Alternative/Development)
```bash
# SQLite is already included with PHP
# Create database file (will be created automatically)
sudo mkdir -p /var/www/client-manager/database
sudo touch /var/www/client-manager/database/database.sqlite
sudo chmod 664 /var/www/client-manager/database/database.sqlite
sudo chown deploy:www-data /var/www/client-manager/database/database.sqlite
```

## Step 3: Deploy Your Application

### 3.1 Clone Your Project
```bash
# Navigate to web directory
cd /var/www

# Clone your repository (replace with your actual repository)
sudo git clone https://github.com/yourusername/ClientManagerLaravelVue.git
sudo mv ClientManagerLaravelVue client-manager

# Change ownership
sudo chown -R deploy:deploy client-manager
cd client-manager
```

### 3.2 Install Dependencies
```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install

# Build production assets
npm run build
```

### 3.3 Configure Environment
```bash
# Copy environment file
cp .env.example .env

# Edit environment file
nano .env
```

**Update your `.env` file for MySQL (Production):**
```env
APP_NAME="Client Manager"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://your-domain.com

# Database Configuration - MySQL (Production)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=client_manager
DB_USERNAME=client_manager_user
DB_PASSWORD=your_strong_password

# Currency Configuration
CURRENCY_SYMBOL=$
CURRENCY_CODE=USD
CURRENCY_DECIMALS=2
CURRENCY_DECIMAL_SEPARATOR=.
CURRENCY_THOUSANDS_SEPARATOR=,

# Session and Cache
SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database

# Mail Configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Alternative: SQLite Configuration (Development/Testing):**
```env
APP_NAME="Client Manager"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://your-domain.com

# Database Configuration - SQLite (Alternative)
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/client-manager/database/database.sqlite

# Currency Configuration
CURRENCY_SYMBOL=$
CURRENCY_CODE=USD
CURRENCY_DECIMALS=2
CURRENCY_DECIMAL_SEPARATOR=.
CURRENCY_THOUSANDS_SEPARATOR=,

# Session and Cache
SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database

# Mail Configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 3.4 Application Setup
```bash
# Generate application key
php artisan key:generate

# Test database connection
php artisan migrate:status

# Run database migrations
php artisan migrate --force

# Seed the database (if you have seeders)
php artisan db:seed --force

# Create storage symlink
php artisan storage:link

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3.5 Database Switching (Optional)
Your application supports both MySQL and SQLite. Use these commands to switch:

```bash
# Switch to MySQL (production)
php artisan db:switch mysql

# Switch to SQLite (development/backup)
php artisan db:switch sqlite

# Or use the shell script
chmod +x scripts/switch-db.sh
./scripts/switch-db.sh mysql
./scripts/switch-db.sh sqlite
```

**Manual switching by editing .env:**
```bash
# For MySQL
nano .env
# Set: DB_CONNECTION=mysql (and other MySQL settings)

# For SQLite  
nano .env
# Set: DB_CONNECTION=sqlite
# Set: DB_DATABASE=/var/www/client-manager/database/database.sqlite

# Clear cache after manual changes
php artisan config:clear
```

### 3.6 Set Permissions
```bash
# Set proper permissions
sudo chown -R deploy:www-data /var/www/client-manager
sudo chmod -R 755 /var/www/client-manager
sudo chmod -R 775 /var/www/client-manager/storage
sudo chmod -R 775 /var/www/client-manager/bootstrap/cache
```

## Step 4: Configure Nginx

### 4.1 Create Nginx Configuration
```bash
sudo nano /etc/nginx/sites-available/client-manager
```

**Add this configuration:**
```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/client-manager/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 4.2 Enable Site
```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/client-manager /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

## Step 5: SSL Certificate (Recommended)

### 5.1 Install Certbot
```bash
sudo apt install snapd -y
sudo snap install core; sudo snap refresh core
sudo snap install --classic certbot
sudo ln -s /snap/bin/certbot /usr/bin/certbot
```

### 5.2 Obtain SSL Certificate
```bash
# Get SSL certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

## Step 6: Set Up Process Management

### 6.1 Install Supervisor (for Queue Workers)
```bash
sudo apt install supervisor -y

# Create supervisor configuration
sudo nano /etc/supervisor/conf.d/client-manager.conf
```

**Add this configuration:**
```ini
[program:client-manager-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/client-manager/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/client-manager/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Update supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start client-manager-worker:*
```

## Step 7: Set Up Automated Backups

### 7.1 Universal Database Backup Script
```bash
# Create backup directory
sudo mkdir -p /var/backups/client-manager

# Create universal backup script
sudo nano /usr/local/bin/backup-client-manager.sh
```

**Add this universal backup script:**
```bash
#!/bin/bash

# Variables
BACKUP_DIR="/var/backups/client-manager"
PROJECT_DIR="/var/www/client-manager"
DATE=$(date +%Y%m%d_%H%M%S)

# Change to project directory
cd $PROJECT_DIR

# Read database connection from .env
DB_CONNECTION=$(grep "^DB_CONNECTION=" .env | cut -d '=' -f2)

echo "Starting backup for $DB_CONNECTION database..."

if [ "$DB_CONNECTION" = "mysql" ]; then
    # MySQL backup
    DB_NAME=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2)
    DB_USER=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2)
    DB_PASS=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2)
    
    mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/mysql_database_$DATE.sql
    echo "MySQL backup completed: mysql_database_$DATE.sql"
    
    # Keep only last 7 days of MySQL backups
    find $BACKUP_DIR -name "mysql_database_*.sql" -mtime +7 -delete
    
elif [ "$DB_CONNECTION" = "sqlite" ]; then
    # SQLite backup
    DB_FILE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2)
    
    if [ -f "$DB_FILE" ]; then
        cp "$DB_FILE" "$BACKUP_DIR/sqlite_database_$DATE.sqlite"
        echo "SQLite backup completed: sqlite_database_$DATE.sqlite"
        
        # Keep only last 7 days of SQLite backups
        find $BACKUP_DIR -name "sqlite_database_*.sqlite" -mtime +7 -delete
    else
        echo "SQLite database file not found: $DB_FILE"
    fi
else
    echo "Unknown database connection: $DB_CONNECTION"
    exit 1
fi

# Also backup the entire project (excluding node_modules, vendor)
tar -czf $BACKUP_DIR/project_$DATE.tar.gz \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='storage/logs/*.log' \
    --exclude='.git' \
    -C /var/www client-manager

echo "Project backup completed: project_$DATE.tar.gz"

# Keep only last 3 days of project backups (they're larger)
find $BACKUP_DIR -name "project_*.tar.gz" -mtime +3 -delete

echo "Backup process completed on $(date)"
```

```bash
# Make script executable
sudo chmod +x /usr/local/bin/backup-client-manager.sh

# Test the backup script
sudo /usr/local/bin/backup-client-manager.sh

# Add to crontab (daily backup at 2 AM)
sudo crontab -e
```

**Add this line to crontab:**
```
0 2 * * * /usr/local/bin/backup-client-manager.sh >> /var/log/client-manager-backup.log 2>&1
```

### 7.2 Manual Backup Commands
```bash
# Quick MySQL backup
mysqldump -u client_manager_user -p client_manager > backup_$(date +%Y%m%d).sql

# Quick SQLite backup
cp /var/www/client-manager/database/database.sqlite backup_$(date +%Y%m%d).sqlite

# Restore MySQL backup
mysql -u client_manager_user -p client_manager < backup_20240101.sql

# Restore SQLite backup
cp backup_20240101.sqlite /var/www/client-manager/database/database.sqlite
```

## Step 8: Monitoring and Maintenance

### 8.1 Log Rotation
```bash
sudo nano /etc/logrotate.d/client-manager
```

**Add this configuration:**
```
/var/www/client-manager/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 0644 deploy deploy
}
```

### 8.2 System Monitoring Script
```bash
# Create monitoring script
nano ~/monitor.sh
```

**Add this script:**
```bash
#!/bin/bash

echo "=== System Status ==="
echo "Date: $(date)"
echo "Uptime: $(uptime)"
echo ""

echo "=== Disk Usage ==="
df -h /

echo ""
echo "=== Memory Usage ==="
free -h

echo ""
echo "=== Service Status ==="
sudo systemctl status nginx --no-pager -l
sudo systemctl status php8.2-fpm --no-pager -l
sudo systemctl status mysql --no-pager -l

echo ""
echo "=== Application Logs (Last 10 lines) ==="
tail -10 /var/www/client-manager/storage/logs/laravel.log
```

```bash
chmod +x ~/monitor.sh
```

## Step 9: Security Hardening

### 9.1 Firewall Setup
```bash
# Install and configure UFW
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 9.2 Fail2ban
```bash
# Install fail2ban
sudo apt install fail2ban -y

# Create custom configuration
sudo nano /etc/fail2ban/jail.local
```

**Add this configuration:**
```ini
[DEFAULT]
bantime = 1h
findtime = 10m
maxretry = 5

[nginx-http-auth]
enabled = true

[nginx-limit-req]
enabled = true

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
```

```bash
sudo systemctl restart fail2ban
```

## Step 10: Deployment Script

### 10.1 Create Deployment Script
```bash
nano ~/deploy.sh
```

**Add this script:**
```bash
#!/bin/bash

echo "Starting deployment..."

# Navigate to project directory
cd /var/www/client-manager

# Pull latest changes
git pull origin main

# Install/update dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Run Laravel commands
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php8.2-fpm
sudo supervisorctl restart client-manager-worker:*

echo "Deployment completed!"
```

```bash
chmod +x ~/deploy.sh
```

## Troubleshooting

### Common Issues and Solutions

1. **Permission Issues**
   ```bash
   sudo chown -R deploy:www-data /var/www/client-manager
   sudo chmod -R 755 /var/www/client-manager
   sudo chmod -R 775 /var/www/client-manager/storage
   sudo chmod -R 775 /var/www/client-manager/bootstrap/cache
   ```

2. **Nginx 502 Error**
   ```bash
   sudo systemctl status php8.2-fpm
   sudo tail -f /var/log/nginx/error.log
   ```

3. **Database Connection Issues**
   ```bash
   # Check current database connection
   php artisan tinker
   config('database.default');
   DB::connection()->getDatabaseName();
   DB::connection()->getPdo();
   
   # For MySQL issues:
   sudo systemctl status mysql
   mysql -u client_manager_user -p client_manager
   
   # For SQLite issues:
   ls -la database/database.sqlite
   chmod 664 database/database.sqlite
   ```

4. **Clear All Caches**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan cache:clear
   ```

## Maintenance Commands

```bash
# Check application status
./monitor.sh

# Deploy updates
./deploy.sh

# View logs
tail -f /var/www/client-manager/storage/logs/laravel.log

# Check queue status
php artisan queue:status

# Restart queue workers
sudo supervisorctl restart client-manager-worker:*

# Database management
php artisan migrate:status          # Check migration status
php artisan db:switch mysql         # Switch to MySQL
php artisan db:switch sqlite        # Switch to SQLite

# Manual backups
sudo /usr/local/bin/backup-client-manager.sh

# MySQL specific
sudo systemctl status mysql         # Check MySQL status
mysql -u client_manager_user -p     # Connect to MySQL

# SQLite specific  
ls -la database/database.sqlite     # Check SQLite file
```

## Performance Optimization

### Enable OPcache
```bash
sudo nano /etc/php/8.2/fpm/conf.d/10-opcache.ini
```

**Add these settings:**
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### Nginx Optimization
```bash
sudo nano /etc/nginx/nginx.conf
```

**Add to http block:**
```nginx
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

client_max_body_size 100M;
```

Your Laravel + Vue.js application should now be successfully deployed and running on your Vultr Ubuntu server! 