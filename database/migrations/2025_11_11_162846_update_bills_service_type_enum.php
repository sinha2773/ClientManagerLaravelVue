<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL doesn't support modifying ENUMs directly, so we need to use raw SQL
        DB::statement("ALTER TABLE bills MODIFY COLUMN service_type ENUM('domain', 'hosting', 'ssl_certificate', 'eims_fee') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove eims_fee from enum
        DB::statement("ALTER TABLE bills MODIFY COLUMN service_type ENUM('domain', 'hosting', 'ssl_certificate') NOT NULL");
    }
};
