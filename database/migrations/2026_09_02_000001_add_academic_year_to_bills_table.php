<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            // Nullable keeps existing bills valid; the application requires it for new bills.
            $table->string('academic_year', 20)->nullable()->after('description')->index();
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex(['academic_year']);
            $table->dropColumn('academic_year');
        });
    }
};
