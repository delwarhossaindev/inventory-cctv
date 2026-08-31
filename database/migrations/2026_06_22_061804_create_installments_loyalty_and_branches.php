<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Installment/EMI plans
        Schema::create('installment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained();
            $table->decimal('total_amount', 14, 2);
            $table->decimal('down_payment', 14, 2)->default(0);
            $table->integer('num_installments');
            $table->decimal('installment_amount', 14, 2);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('installment_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_plan_id')->constrained()->cascadeOnDelete();
            $table->integer('installment_no');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        // Loyalty points
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('loyalty_points')->default(0)->after('status');
        });

        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->integer('points');
            $table->string('type');
            $table->string('description');
            $table->timestamps();
        });

        // Multi-store / branch
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
        Schema::dropIfExists('loyalty_transactions');
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('loyalty_points');
        });
        Schema::dropIfExists('installment_payments');
        Schema::dropIfExists('installment_plans');
    }
};
