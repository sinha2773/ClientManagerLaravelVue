# Laravel + Vue.js (Inertia.js) Deployment Guide for Nginx

This guide will help you deploy your Client Manager Laravel Vue application to a server running Nginx.

## Prerequisites

- Ubuntu/Debian server with root/sudo access
- Domain name pointing to your server (optional but recommended)
- Basic knowledge of Linux command line

## Server Requirements

- **PHP**: 8.2 or higher
- **Node.js**: 18+ and npm
- **MySQL**: 8.0+ or PostgreSQL 13+
- **Nginx**: Latest version
- **Composer**: Latest version

## Step 1: Server Setup

### 1.1 Update Server Packages
```bash
sudo apt update && sudo apt upgrade -y
```

### 1.2 Install Required Software
```bash
# Install Nginx
sudo apt install nginx -y

# Install PHP and required extensions
sudo apt install php8.2-fpm php8.2-mysql php8.2-xml php8.2-gd php8.2-curl php8.2-mbstring php8.2-zip php8.2-intl php8.2-bcmath -y

# Install MySQL
sudo apt install mysql-server -y

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 1.3 Secure MySQL Installation
```bash
sudo mysql_secure_installation
```

## Step 2: Create Database and User

```bash
# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE client_manager;
CREATE USER 'client_manager_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON client_manager.* TO 'client_manager_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Step 3: Upload Project Files

### 3.1 Create Project Directory
```bash
sudo mkdir -p /var/www/client-manager
sudo chown -R $USER:$USER /var/www/client-manager
```

### 3.2 Upload Files (Choose one method)

**Method A: Using SCP/RSYNC (Recommended)**
```bash
# From your local machine, run this command:
rsync -avz --exclude 'node_modules' --exclude 'vendor' --exclude '.git' /Applications/MAMP/htdocs/ClientManagerLaravelVue/ username@your-server-ip:/var/www/client-manager/
```

**Method B: Using Git**
```bash
# On your server
cd /var/www/client-manager
git clone https://github.com/yourusername/your-repo.git .
```

## Step 4: Configure Environment

### 4.1 Create Environment File
```bash
cd /var/www/client-manager
cp .env.example .env
```

### 4.2 Edit Environment Configuration
```bash
nano .env
```

Update the following values:
```env
APP_NAME="Client Manager"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=client_manager
DB_USERNAME=client_manager_user
DB_PASSWORD=your_secure_password

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@domain.com
```

## Step 5: Install Dependencies and Build

### 5.1 Install PHP Dependencies
```bash
cd /var/www/client-manager
composer install --optimize-autoloader --no-dev
```

### 5.2 Install Node Dependencies and Build Assets
```bash
npm install
npm run build
```

### 5.3 Generate Application Key
```bash
php artisan key:generate
```

### 5.4 Run Database Migrations
```bash
php artisan migrate --force
```

### 5.5 Run Seeders (if needed)
```bash
php artisan db:seed --force
```

### 5.6 Optimize Application
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Step 6: Set File Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/client-manager

# Set directory permissions
sudo find /var/www/client-manager -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/client-manager -type f -exec chmod 644 {} \;

# Set specific permissions for storage and bootstrap/cache
sudo chmod -R 775 /var/www/client-manager/storage
sudo chmod -R 775 /var/www/client-manager/bootstrap/cache
```

## Step 7: Configure Nginx

### 7.1 Create Nginx Configuration
```bash
sudo nano /etc/nginx/sites-available/client-manager
```

Add the following configuration:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
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
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 7.2 Enable the Site
```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/client-manager /etc/nginx/sites-enabled/

# Remove default site (optional)
sudo rm /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

## Step 8: Configure PHP-FPM

### 8.1 Optimize PHP-FPM
```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Update these values for better performance:
```ini
pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
pm.max_requests = 500
```

### 8.2 Restart PHP-FPM
```bash
sudo systemctl restart php8.2-fpm
```

## Step 9: SSL Certificate (Highly Recommended)

### 9.1 Install Certbot
```bash
sudo apt install certbot python3-certbot-nginx -y
```

### 9.2 Obtain SSL Certificate
```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

## Step 10: Set Up Cron Jobs

Laravel requires a cron job for scheduled tasks:

```bash
sudo crontab -e
```

Add this line:
```bash
* * * * * cd /var/www/client-manager && php artisan schedule:run >> /dev/null 2>&1
```

## Step 11: Configure Queue Workers (Optional)

If your application uses queues:

### 11.1 Create Systemd Service
```bash
sudo nano /etc/systemd/system/client-manager-worker.service
```

```ini
[Unit]
Description=Client Manager Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/client-manager/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

### 11.2 Enable and Start Service
```bash
sudo systemctl enable client-manager-worker
sudo systemctl start client-manager-worker
```

## Step 12: Configure Firewall

```bash
# Enable UFW firewall
sudo ufw enable

# Allow SSH, HTTP, and HTTPS
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
```

## Step 13: Final Steps

### 13.1 Create Storage Link (if needed)
```bash
cd /var/www/client-manager
php artisan storage:link
```

### 13.2 Test Your Application
Visit your domain in a web browser to test the application.

### 13.3 Monitor Logs
```bash
# Laravel logs
tail -f /var/www/client-manager/storage/logs/laravel.log

# Nginx logs
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log
```

## Maintenance Commands

### Update Application
```bash
cd /var/www/client-manager

# Pull latest changes (if using Git)
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear and rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

### Backup Database
```bash
mysqldump -u client_manager_user -p client_manager > backup_$(date +%Y%m%d_%H%M%S).sql
```

## Troubleshooting

### Common Issues

1. **500 Internal Server Error**
   - Check PHP error logs: `sudo tail -f /var/log/nginx/error.log`
   - Verify file permissions
   - Check `.env` file configuration

2. **Database Connection Issues**
   - Verify database credentials in `.env`
   - Check if MySQL service is running: `sudo systemctl status mysql`

3. **Assets Not Loading**
   - Ensure `npm run build` completed successfully
   - Check if assets exist in `public/build/` directory

4. **Permission Denied Errors**
   - Check file ownership: `sudo chown -R www-data:www-data /var/www/client-manager`
   - Verify storage permissions: `sudo chmod -R 775 /var/www/client-manager/storage`

## Security Considerations

1. **Keep Software Updated**: Regularly update server packages, PHP, and application dependencies
2. **Use Strong Passwords**: For database users and application users
3. **Configure Fail2Ban**: To protect against brute force attacks
4. **Regular Backups**: Automate database and file backups
5. **Monitor Logs**: Set up log monitoring and alerting

## Performance Optimization

1. **Enable OPcache**: Configure PHP OPcache for better performance
2. **Use Redis**: For session storage and caching
3. **Configure HTTP/2**: In Nginx for faster loading
4. **Optimize Database**: Use indexes and query optimization
5. **CDN**: Consider using a CDN for static assets

Your Client Manager application should now be successfully deployed and accessible via your domain! 