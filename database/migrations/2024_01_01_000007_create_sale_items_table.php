<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            // Snapshot prices at time of sale — never recalculate from current product price
            $table->string('product_name');                        // Snapshot of name
            $table->string('product_sku');                         // Snapshot of SKU
            $table->decimal('unit_price', 10, 2);                  // Price at time of sale
            $table->integer('quantity');
            $table->decimal('discount_amount', 10, 2)->default(0); // Per-line discount
            $table->decimal('line_total', 10, 2);                  // (unit_price × qty) - discount

            // How it was added — useful for analytics
            // values: barcode_scan | quick_select | search
            $table->string('added_via')->default('search');

            $table->timestamps();

            $table->index('sale_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
