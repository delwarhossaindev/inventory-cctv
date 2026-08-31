<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            // purchase | sale | adjustment
            $table->string('type');
            // signed: positive = stock in, negative = stock out
            $table->integer('quantity');
            $table->integer('balance')->nullable(); // stock after this movement
            $table->nullableMorphs('reference'); // reference_type / reference_id
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
