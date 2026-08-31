<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('warranty_days')->nullable()->after('alert_quantity');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->date('warranty_expires')->nullable()->after('cost_total');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('warranty_expires');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('warranty_days');
        });
    }
};
