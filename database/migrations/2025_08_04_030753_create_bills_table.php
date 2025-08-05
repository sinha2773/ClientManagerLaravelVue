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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->enum('service_type', ['domain', 'hosting', 'ssl_certificate']);
            $table->unsignedBigInteger('service_id'); // ID of the service (domain_id, hosting_service_id, ssl_certificate_id)
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->enum('status', ['draft', 'sent', 'overdue', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Add index for better performance
            $table->index(['service_type', 'service_id']);
            $table->index(['payment_status']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
