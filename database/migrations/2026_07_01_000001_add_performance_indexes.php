<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('status');                         // scopeActive() and POS
            $table->index(['status', 'name']);               // POS orderBy name filter
            $table->index(['stock_quantity', 'alert_quantity']); // scopeLowStock()
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->index('sale_date');                      // dashboard date/month queries
            $table->index('payment_method');                 // sales list filter
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->index('purchase_date');                  // dashboard month query
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['status', 'name']);
            $table->dropIndex(['stock_quantity', 'alert_quantity']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['sale_date']);
            $table->dropIndex(['payment_method']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['purchase_date']);
        });
    }
};
