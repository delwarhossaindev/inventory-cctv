<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('model');
            $table->decimal('purchase_price', 12, 2)->default(0)->after('sku');
            $table->decimal('sale_price', 12, 2)->default(0)->after('purchase_price');
            $table->integer('stock_quantity')->default(0)->after('sale_price');
            $table->integer('alert_quantity')->default(0)->after('stock_quantity');
            $table->string('unit')->default('pcs')->after('alert_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sku', 'purchase_price', 'sale_price', 'stock_quantity', 'alert_quantity', 'unit']);
        });
    }
};
