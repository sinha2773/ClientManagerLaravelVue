<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->string('manual_invoice')->nullable()->after('bill_number');
            $table->boolean('adjust_with_salary')->default(false)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['manual_invoice', 'adjust_with_salary']);
        });
    }
};
