<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();         // e.g. INV-20240118-0042

            $table->foreignId('user_id')->constrained()->restrictOnDelete();          // Cashier
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); // Optional

            // Amounts
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('tendered_amount', 10, 2)->default(0);  // Cash handed over
            $table->decimal('change_amount', 10, 2)->default(0);

            // Status: pending | completed | voided | refunded
            $table->string('status')->default('completed');

            $table->text('note')->nullable();
            $table->timestamp('sold_at')->useCurrent();
            $table->timestamps();

            $table->index('invoice_number');
            $table->index('sold_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
