#!/bin/bash

# Laravel + Vue.js Deployment Script for Production Server
# Usage: ./deploy.sh

set -e

echo "🚀 Starting deployment process..."

# Configuration
PROJECT_DIR="/var/www/client-manager"
NGINX_CONFIG="/etc/nginx/sites-available/client-manager"
PHP_VERSION="8.2"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "This script should not be run as root for security reasons"
   exit 1
fi

# Check if we're in the project directory
if [ ! -f "composer.json" ]; then
    print_error "Please run this script from your Laravel project directory"
    exit 1
fi

print_status "Entering maintenance mode..."
php artisan down --refresh=15 --retry=60 --secret="deployment-secret" || true

print_status "Pulling latest changes from Git..."
git pull origin main

print_status "Installing/updating PHP dependencies..."
composer install --optimize-autoloader --no-dev

print_status "Installing/updating Node.js dependencies..."
npm ci

print_status "Building frontend assets..."
npm run build

print_status "Running database migrations..."
php artisan migrate --force

print_status "Clearing and rebuilding cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

print_status "Creating storage link..."
php artisan storage:link || true

print_status "Setting proper file permissions..."
sudo chown -R www-data:www-data $PROJECT_DIR
sudo find $PROJECT_DIR -type d -exec chmod 755 {} \;
sudo find $PROJECT_DIR -type f -exec chmod 644 {} \;
sudo chmod -R 775 $PROJECT_DIR/storage
sudo chmod -R 775 $PROJECT_DIR/bootstrap/cache

print_status "Restarting PHP-FPM..."
sudo systemctl reload php${PHP_VERSION}-fpm

print_status "Restarting Nginx..."
sudo systemctl reload nginx

print_status "Exiting maintenance mode..."
php artisan up

print_status "Deployment completed successfully! 🎉"

print_warning "Don't forget to:"
echo "  • Test your application at your domain"
echo "  • Check error logs if issues occur"
echo "  • Monitor application performance"

echo ""
print_status "Useful commands for monitoring:"
echo "  • Laravel logs: tail -f $PROJECT_DIR/storage/logs/laravel.log"
echo "  • Nginx access logs: sudo tail -f /var/log/nginx/access.log"
echo "  • Nginx error logs: sudo tail -f /var/log/nginx/error.log"
echo "  • PHP-FPM logs: sudo tail -f /var/log/php${PHP_VERSION}-fpm.log" 