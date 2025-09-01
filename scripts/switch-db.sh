#!/bin/bash

# Database switching script for Laravel Client Manager
# Usage: ./scripts/switch-db.sh [mysql|sqlite]

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

# Check if argument is provided
if [ $# -eq 0 ]; then
    print_error "No database type specified"
    echo "Usage: $0 [mysql|sqlite]"
    echo ""
    echo "Examples:"
    echo "  $0 mysql   - Switch to MySQL database"
    echo "  $0 sqlite  - Switch to SQLite database"
    exit 1
fi

# Get the database type
DB_TYPE=$1

# Get current directory (should be project root)
PROJECT_ROOT=$(pwd)

# Backup current .env file
if [ -f ".env" ]; then
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    print_status "Current .env backed up"
fi

case $DB_TYPE in
    "mysql")
        print_status "Switching to MySQL database..."
        
        # Create MySQL environment configuration
        cat > .env.tmp << EOF
# Laravel Client Manager - MySQL Configuration
APP_NAME="Client Manager"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost

# Database Configuration - MySQL
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

# Broadcasting
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

# Mail Configuration
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"
EOF
        ;;
        
    "sqlite")
        print_status "Switching to SQLite database..."
        
        # Create SQLite environment configuration
        cat > .env.tmp << EOF
# Laravel Client Manager - SQLite Configuration
APP_NAME="Client Manager"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost

# Database Configuration - SQLite
DB_CONNECTION=sqlite
DB_DATABASE=${PROJECT_ROOT}/database/database.sqlite

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

# Broadcasting
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

# Mail Configuration
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"
EOF
        ;;
        
    *)
        print_error "Invalid database type: $DB_TYPE"
        echo "Valid options: mysql, sqlite"
        exit 1
        ;;
esac

# Replace .env file
mv .env.tmp .env
print_status ".env file updated for $DB_TYPE"

# Generate application key if it doesn't exist
if ! grep -q "APP_KEY=base64:" .env; then
    print_status "Generating application key..."
    php artisan key:generate
fi

# Clear Laravel caches
print_status "Clearing Laravel caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Create SQLite database file if switching to SQLite
if [ "$DB_TYPE" = "sqlite" ]; then
    if [ ! -f "database/database.sqlite" ]; then
        print_status "Creating SQLite database file..."
        touch database/database.sqlite
        chmod 664 database/database.sqlite
    fi
fi

# Test database connection
print_status "Testing database connection..."
if php artisan migrate:status >/dev/null 2>&1; then
    print_status "✓ Database connection successful"
    
    # Show migration status
    echo ""
    echo "Current migration status:"
    php artisan migrate:status
    
    echo ""
    print_status "Database successfully switched to: $DB_TYPE"
    
    if [ "$DB_TYPE" = "mysql" ]; then
        print_warning "Make sure MySQL is running and the database 'client_manager' exists"
        print_warning "If you need to create the MySQL database, run:"
        echo "  mysql -u root -p"
        echo "  CREATE DATABASE client_manager;"
        echo "  CREATE USER 'client_manager_user'@'localhost' IDENTIFIED BY 'your_strong_password';"
        echo "  GRANT ALL PRIVILEGES ON client_manager.* TO 'client_manager_user'@'localhost';"
        echo "  FLUSH PRIVILEGES;"
    fi
    
else
    print_error "Database connection failed"
    echo ""
    echo "Troubleshooting steps:"
    
    if [ "$DB_TYPE" = "mysql" ]; then
        echo "1. Make sure MySQL is running:"
        echo "   - macOS: brew services start mysql"
        echo "   - Linux: sudo systemctl start mysql"
        echo ""
        echo "2. Create the database and user (see above commands)"
        echo ""
        echo "3. Update the password in .env file"
    else
        echo "1. Check SQLite file permissions:"
        echo "   chmod 664 database/database.sqlite"
        echo "   chmod 775 database/"
        echo ""
        echo "2. Make sure the database directory exists"
    fi
    
    echo ""
    echo "4. Run migrations after fixing the connection:"
    echo "   php artisan migrate"
    
    exit 1
fi

echo ""
print_status "Switch complete! You can now run:"
echo "  php artisan migrate        # Run pending migrations"
echo "  php artisan db:seed        # Seed the database (optional)"
echo "  php artisan serve          # Start development server" 