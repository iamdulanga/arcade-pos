<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    // How the item was added at the POS counter
    const VIA_BARCODE_SCAN  = 'barcode_scan';
    const VIA_QUICK_SELECT  = 'quick_select';
    const VIA_SEARCH        = 'search';

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'product_sku',
        'unit_price',
        'quantity',
        'discount_amount',
        'line_total',
        'added_via',
    ];

    protected $casts = [
        'unit_price'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'line_total'      => 'decimal:2',
        'quantity'        => 'integer',
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Compute line total
    public static function computeLineTotal(float $unitPrice, int $qty, float $discount = 0): float
    {
        return round(($unitPrice * $qty) - $discount, 2);
    }
}
