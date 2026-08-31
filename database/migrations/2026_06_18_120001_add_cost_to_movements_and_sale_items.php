<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Signed cost value: positive for stock-in, negative (COGS) for stock-out.
            $table->decimal('cost_total', 12, 2)->default(0)->after('balance');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            // Cost of goods sold for this line (FIFO), used for profit reporting.
            $table->decimal('cost_total', 12, 2)->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('cost_total');
        });
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('cost_total');
        });
    }
};
