<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'has_barcode',
        'category_id',
        'supplier_id',
        'selling_price',
        'cost_price',
        'stock_quantity',
        'reorder_level',
        'is_pinned',
        'pin_position',
        'author',
        'isbn',
        'description',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'has_barcode'     => 'boolean',
        'is_pinned'       => 'boolean',
        'is_active'       => 'boolean',
        'selling_price'   => 'decimal:2',
        'cost_price'      => 'decimal:2',
    ];

    // -------------------------------------------------------
    // Auto-generate SKU on create
    // e.g. Category "Plastics" with prefix "PLT" → PLT-0042
    // -------------------------------------------------------
    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->sku)) {
                $product->sku = self::generateSku($product->category_id);
            }
        });
    }

    public static function generateSku(int $categoryId): string
    {
        $category = Category::find($categoryId);
        $prefix   = strtoupper($category?->prefix ?? 'GEN');

        // Count existing products in this category and pad to 4 digits
        $count = self::where('category_id', $categoryId)->count() + 1;

        return $prefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function inventoryAdjustments()
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true)->orderBy('pin_position');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'reorder_level');
    }

    // -------------------------------------------------------
    // POS unified search — barcode, SKU, or name
    // Called from the Livewire POS component
    // -------------------------------------------------------
    public function scopePosSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('barcode', $term)                      // exact barcode match
              ->orWhere('sku', $term)                        // exact SKU match
              ->orWhere('name', 'like', "%{$term}%");        // partial name match
        })->active();
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    public function decrementStock(int $qty): void
    {
        $this->decrement('stock_quantity', $qty);
    }

    public function incrementStock(int $qty): void
    {
        $this->increment('stock_quantity', $qty);
    }
}
