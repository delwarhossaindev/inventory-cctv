<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('model')->nullable();

            $table->foreignId('main_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->string('image_url')->nullable();

            $table->longText('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->longText('advantages')->nullable();
            $table->longText('specifications')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // array of image URLs
            $table->json('gallery_images')->nullable();
            // array of { question, answer }
            $table->json('faqs')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
