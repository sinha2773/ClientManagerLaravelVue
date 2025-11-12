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
        Schema::create('pay_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->decimal('salary_amount', 10, 2);
            $table->string('month_year'); // Format: YYYY-MM
            $table->enum('salary_source', ['CodeGaon', 'MSBJBD', 'SinhdBD']);
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_partial')->default(false);
            $table->boolean('is_due')->default(false);
            $table->boolean('is_advance')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_salaries');
    }
};
