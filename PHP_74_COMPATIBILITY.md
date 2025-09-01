# PHP 7.4 Compatibility Guide

This guide explains how to make the Client Manager application compatible with PHP 7.4.

## Current Status

**❌ Not Compatible with PHP 7.4**
- Current PHP requirement: `^8.2`
- Laravel version: `^12.0`
- Modern package versions

## Option 1: Downgrade Stack (Recommended)

### 1.1 Create PHP 7.4 Compatible Version

```bash
# Create a new branch for PHP 7.4 compatibility
git checkout -b php74-compatibility

# Backup current composer.json
cp composer.json composer.json.backup
```

### 1.2 Update composer.json for PHP 7.4

Replace the requirements in `composer.json`:

```json
{
    "require": {
        "php": "^7.4|^8.0",
        "inertiajs/inertia-laravel": "^0.6",
        "laravel/framework": "^8.0",
        "laravel/sanctum": "^2.0",
        "laravel/tinker": "^2.5",
        "tightenco/ziggy": "^1.0"
    },
    "require-dev": {
        "fakerphp/faker": "^1.9.1",
        "laravel/breeze": "^1.0",
        "laravel/pint": "^1.0",
        "laravel/sail": "^1.0.1",
        "mockery/mockery": "^1.4.2",
        "nunomaduro/collision": "^5.0",
        "phpunit/phpunit": "^9.3.3"
    }
}
```

### 1.3 Reinstall Dependencies

```bash
# Remove existing vendor directory and lock file
rm -rf vendor composer.lock

# Install compatible versions
composer install

# Update npm packages for compatibility
npm install
```

### 1.4 Code Changes Required

#### Update Laravel Features
```php
// Replace in routes/web.php
// Laravel 12 syntax:
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Laravel 8 syntax:
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
```

#### Update Inertia.js
```php
// In app/Http/Middleware/HandleInertiaRequests.php
// Remove Laravel 12 specific features
```

#### Database Migrations
```php
// Replace in migrations
// Laravel 12:
$table->foreignId('client_id')->constrained()->cascadeOnDelete();

// Laravel 8:
$table->unsignedBigInteger('client_id');
$table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
```

## Option 2: Use Docker with PHP 8.2

### 2.1 Create Dockerfile for PHP 8.2

```dockerfile
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage
```

### 2.2 Docker Compose

```yaml
version: '3.8'
services:
  app:
    build: .
    ports:
      - "8000:8000"
    volumes:
      - .:/var/www
    environment:
      - DB_CONNECTION=mysql
      - DB_HOST=mysql
    depends_on:
      - mysql
    
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: client_manager
      MYSQL_ROOT_PASSWORD: root
      MYSQL_PASSWORD: password
    ports:
      - "3306:3306"
```

## Option 3: Server-Level PHP Management

### 3.1 Multiple PHP Versions on Server

```bash
# Install multiple PHP versions (Ubuntu)
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# Install PHP 7.4 and 8.2
sudo apt install php7.4 php7.4-fpm php8.2 php8.2-fpm

# Configure Nginx for specific PHP version
# In your Nginx site config:
location ~ \.php$ {
    # For PHP 7.4 sites
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    
    # For PHP 8.2 sites  
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
}
```

### 3.2 PHP Version Switching

```bash
# Switch PHP CLI version
sudo update-alternatives --config php

# Or use specific version
/usr/bin/php7.4 artisan migrate
/usr/bin/php8.2 artisan migrate
```

## Recommended Approach

### For New Projects
- **Use PHP 8.2+** with current Laravel 12 setup
- Better performance, security, and features
- Future-proof with long-term support

### For Legacy Servers
1. **Upgrade server PHP** to 8.2 (recommended)
2. **Use Docker** to containerize with PHP 8.2
3. **Downgrade stack** only if absolutely necessary

## Migration Strategy

### Phase 1: Assessment
```bash
# Check current PHP features used
composer show --platform
php -m  # Check loaded extensions
```

### Phase 2: Testing
```bash
# Test on PHP 8.2 environment
php artisan test
php artisan migrate:fresh --seed
```

### Phase 3: Deployment
```bash
# Deploy with PHP 8.2
# Update server configuration
# Monitor for issues
```

## PHP Version Comparison

| Feature | PHP 7.4 | PHP 8.2 |
|---------|---------|----------|
| Performance | Baseline | 10-15% faster |
| Memory Usage | Higher | Optimized |
| Type System | Basic | Advanced |
| Security | EOL 2022 | Active support |
| Laravel Support | Up to 9.x | 12.x+ |

## Conclusion

**Recommendation**: Upgrade to PHP 8.2 rather than downgrading the application stack. This provides:

- ✅ Better performance and security
- ✅ Access to modern Laravel features  
- ✅ Long-term maintainability
- ✅ Community support and updates

If you must use PHP 7.4, the downgrade approach is possible but requires significant effort and loses modern features. 