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
        Schema::create('hosting_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('domain_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('package_name');
            $table->date('start_date');
            $table->date('renewal_date');
            $table->decimal('price', 10, 2);
            $table->string('payment_status')->default('unpaid');
            $table->string('status')->default('active');
            $table->string('server_ip')->nullable();
            $table->string('control_panel_url')->nullable();
            $table->string('username');
            $table->string('password');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_services');
    }
};
