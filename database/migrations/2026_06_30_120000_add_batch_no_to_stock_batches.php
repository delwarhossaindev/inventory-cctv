<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->string('batch_no')->nullable()->after('product_id')->index();
        });

        // Backfill existing batches with a generated batch number.
        foreach (DB::table('stock_batches')->whereNull('batch_no')->pluck('id') as $id) {
            DB::table('stock_batches')->where('id', $id)
                ->update(['batch_no' => 'B' . str_pad($id, 6, '0', STR_PAD_LEFT)]);
        }
    }

    public function down(): void
    {
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->dropColumn('batch_no');
        });
    }
};
