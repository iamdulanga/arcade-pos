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

            // Identity
            $table->string('name');                             // Real name: "Plastic Basket 5L"
            $table->string('sku')->unique();                    // Auto-generated: "PLT-0042"
            $table->string('barcode')->nullable()->unique();    // Null for non-barcoded items
            $table->boolean('has_barcode')->default(false);     // Quick flag for filtering/UI

            // Relationships
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();

            // Pricing
            $table->decimal('selling_price', 10, 2);
            $table->decimal('cost_price', 10, 2)->nullable();

            // Stock
            $table->integer('stock_quantity')->default(0);
            $table->integer('reorder_level')->default(5);       // Alert when stock falls below this

            // POS quick-select grid
            $table->boolean('is_pinned')->default(false);       // Show on quick-select grid?
            $table->integer('pin_position')->nullable();        // Order on the grid

            // Extra details (mostly for books)
            $table->string('author')->nullable();
            $table->string('isbn')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for fast POS lookup
            $table->index('name');
            $table->index('sku');
            $table->index('is_pinned');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
