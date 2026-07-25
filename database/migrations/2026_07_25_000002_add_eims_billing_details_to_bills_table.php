<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->decimal('eims_monthly', 10, 2)->nullable()->after('total_students');
            $table->decimal('discount', 10, 2)->default(0)->after('eims_monthly');
            $table->string('student_summary_year')->nullable()->after('billing_months');
            $table->json('student_grand_totals')->nullable()->after('student_summary_year');
            $table->json('student_summary')->nullable()->after('student_grand_totals');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn([
                'eims_monthly',
                'discount',
                'student_summary_year',
                'student_grand_totals',
                'student_summary',
            ]);
        });
    }
};
