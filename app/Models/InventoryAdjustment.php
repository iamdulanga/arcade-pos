<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    // Adjustment types
    const TYPE_STOCK_IN   = 'stock_in';
    const TYPE_STOCK_OUT  = 'stock_out';
    const TYPE_SALE       = 'sale';
    const TYPE_RETURN     = 'return';
    const TYPE_DAMAGE     = 'damage';
    const TYPE_CORRECTION = 'correction';

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'reference',
        'note',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper — create a stock adjustment and update the product in one call
    public static function record(
        Product $product,
        int $change,
        string $type,
        int $userId,
        ?string $reference = null,
        ?string $note = null
    ): self {
        $before = $product->stock_quantity;
        $after  = $before + $change;

        $adjustment = self::create([
            'product_id'      => $product->id,
            'user_id'         => $userId,
            'type'            => $type,
            'quantity_before' => $before,
            'quantity_change' => $change,
            'quantity_after'  => $after,
            'reference'       => $reference,
            'note'            => $note,
        ]);

        $product->update(['stock_quantity' => $after]);

        return $adjustment;
    }
}
