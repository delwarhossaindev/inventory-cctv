<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity');            // original quantity received
            $table->integer('remaining');           // quantity still available (FIFO consumes this)
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->timestamp('received_at');       // FIFO order key
            $table->nullableMorphs('reference');    // purchase / adjustment etc.
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'remaining']);
            $table->index(['product_id', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
