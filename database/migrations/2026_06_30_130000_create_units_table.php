<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('short_name')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // Seed a few common units.
        $now = now();
        Schema::disableForeignKeyConstraints();
        \Illuminate\Support\Facades\DB::table('units')->insert([
            ['name' => 'Piece', 'short_name' => 'pcs', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Box', 'short_name' => 'box', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Set', 'short_name' => 'set', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ]);
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
