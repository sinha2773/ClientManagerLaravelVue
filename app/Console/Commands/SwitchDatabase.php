<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SwitchDatabase extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'db:switch {type : Database type (mysql or sqlite)} {--force : Force switch without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Switch between MySQL and SQLite databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = strtolower($this->argument('type'));
        $force = $this->option('force');

        if (!in_array($type, ['mysql', 'sqlite'])) {
            $this->error('Invalid database type. Use "mysql" or "sqlite".');
            return 1;
        }

        // Show current database info
        $this->showCurrentDatabase();

        // Confirm switch unless forced
        if (!$force && !$this->confirm("Switch to {$type} database?")) {
            $this->info('Database switch cancelled.');
            return 0;
        }

        // Backup current .env file
        $this->backupEnvFile();

        // Update .env file
        $this->updateEnvFile($type);

        // Clear caches
        $this->clearCaches();

        // Test connection
        if ($this->testConnection($type)) {
            $this->info("✅ Successfully switched to {$type} database!");
            $this->showMigrationStatus();
            $this->showNextSteps($type);
        } else {
            $this->error("❌ Failed to connect to {$type} database!");
            $this->showTroubleshooting($type);
            return 1;
        }

        return 0;
    }

    /**
     * Show current database information
     */
    private function showCurrentDatabase()
    {
        try {
            $connection = config('database.default');
            $database = DB::connection()->getDatabaseName();
            
            $this->info("Current database: {$connection}");
            if ($database) {
                $this->info("Database name: {$database}");
            }
        } catch (\Exception $e) {
            $this->warn('Could not determine current database connection.');
        }

        $this->newLine();
    }

    /**
     * Backup current .env file
     */
    private function backupEnvFile()
    {
        $envPath = base_path('.env');
        $backupPath = base_path('.env.backup.' . date('Y-m-d_H-i-s'));

        if (File::exists($envPath)) {
            File::copy($envPath, $backupPath);
            $this->info("Backed up current .env to: " . basename($backupPath));
        }
    }

    /**
     * Update .env file with new database configuration
     */
    private function updateEnvFile($type)
    {
        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        if ($type === 'mysql') {
            // Switch to MySQL
            $envContent = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=mysql', $envContent);
            $envContent = preg_replace('/^DB_HOST=.*/m', 'DB_HOST=127.0.0.1', $envContent);
            $envContent = preg_replace('/^DB_PORT=.*/m', 'DB_PORT=3306', $envContent);
            $envContent = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=client_manager', $envContent);
            $envContent = preg_replace('/^DB_USERNAME=.*/m', 'DB_USERNAME=client_manager_user', $envContent);
            $envContent = preg_replace('/^DB_PASSWORD=.*/m', 'DB_PASSWORD=your_strong_password', $envContent);

            // Add missing fields if they don't exist
            if (!str_contains($envContent, 'DB_HOST=')) {
                $envContent = str_replace('DB_CONNECTION=mysql', "DB_CONNECTION=mysql\nDB_HOST=127.0.0.1", $envContent);
            }
            if (!str_contains($envContent, 'DB_PORT=')) {
                $envContent = str_replace('DB_HOST=127.0.0.1', "DB_HOST=127.0.0.1\nDB_PORT=3306", $envContent);
            }
            if (!str_contains($envContent, 'DB_USERNAME=')) {
                $envContent = str_replace('DB_DATABASE=client_manager', "DB_DATABASE=client_manager\nDB_USERNAME=client_manager_user", $envContent);
            }
            if (!str_contains($envContent, 'DB_PASSWORD=')) {
                $envContent = str_replace('DB_USERNAME=client_manager_user', "DB_USERNAME=client_manager_user\nDB_PASSWORD=your_strong_password", $envContent);
            }

        } else {
            // Switch to SQLite
            $envContent = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=sqlite', $envContent);
            $dbPath = base_path('database/database.sqlite');
            $envContent = preg_replace('/^DB_DATABASE=.*/m', "DB_DATABASE={$dbPath}", $envContent);
            
            // Remove MySQL-specific fields (comment them out)
            $envContent = preg_replace('/^DB_HOST=.*/m', '# DB_HOST=127.0.0.1', $envContent);
            $envContent = preg_replace('/^DB_PORT=.*/m', '# DB_PORT=3306', $envContent);
            $envContent = preg_replace('/^DB_USERNAME=.*/m', '# DB_USERNAME=client_manager_user', $envContent);
            $envContent = preg_replace('/^DB_PASSWORD=.*/m', '# DB_PASSWORD=your_strong_password', $envContent);

            // Create SQLite file if it doesn't exist
            if (!File::exists($dbPath)) {
                File::put($dbPath, '');
                chmod($dbPath, 0664);
                $this->info('Created SQLite database file.');
            }
        }

        File::put($envPath, $envContent);
        $this->info("Updated .env file for {$type} database.");
    }

    /**
     * Clear Laravel caches
     */
    private function clearCaches()
    {
        $this->info('Clearing Laravel caches...');
        
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->call('cache:clear');
    }

    /**
     * Test database connection
     */
    private function testConnection($type)
    {
        try {
            // Reload configuration
            $this->call('config:clear');
            
            // Test connection
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            $this->error("Connection failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Show current migration status
     */
    private function showMigrationStatus()
    {
        $this->newLine();
        $this->info('Current migration status:');
        $this->call('migrate:status');
    }

    /**
     * Show next steps based on database type
     */
    private function showNextSteps($type)
    {
        $this->newLine();
        $this->info('Next steps:');

        if ($type === 'mysql') {
            $this->warn('⚠️  Make sure to:');
            $this->line('1. Update the DB_PASSWORD in .env with your actual MySQL password');
            $this->line('2. Ensure MySQL is running');
            $this->line('3. Create the database if it doesn\'t exist:');
            $this->line('   mysql -u root -p');
            $this->line('   CREATE DATABASE client_manager;');
            $this->line('   CREATE USER \'client_manager_user\'@\'localhost\' IDENTIFIED BY \'your_password\';');
            $this->line('   GRANT ALL PRIVILEGES ON client_manager.* TO \'client_manager_user\'@\'localhost\';');
        }

        $this->newLine();
        $this->info('Run migrations if needed:');
        $this->line('php artisan migrate');
        
        $this->newLine();
        $this->info('Optionally seed the database:');
        $this->line('php artisan db:seed');
    }

    /**
     * Show troubleshooting information
     */
    private function showTroubleshooting($type)
    {
        $this->newLine();
        $this->error('Troubleshooting:');

        if ($type === 'mysql') {
            $this->line('1. Start MySQL service:');
            $this->line('   - macOS: brew services start mysql');
            $this->line('   - Linux: sudo systemctl start mysql');
            $this->line('');
            $this->line('2. Create database and user (see above commands)');
            $this->line('');
            $this->line('3. Update password in .env file');
        } else {
            $this->line('1. Check file permissions:');
            $this->line('   chmod 664 database/database.sqlite');
            $this->line('   chmod 775 database/');
            $this->line('');
            $this->line('2. Ensure database directory exists');
        }

        $this->newLine();
        $this->line('After fixing issues, test connection with:');
        $this->line('php artisan db:switch ' . $type . ' --force');
    }
} 