<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();  // Who made the adjustment

            // Type: stock_in | stock_out | sale | return | damage | correction
            $table->string('type');

            $table->integer('quantity_before');
            $table->integer('quantity_change');   // Positive = added, negative = removed
            $table->integer('quantity_after');

            $table->string('reference')->nullable();   // e.g. purchase order number
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
