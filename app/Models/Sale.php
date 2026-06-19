<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    const STATUS_PENDING   = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_VOIDED    = 'voided';
    const STATUS_REFUNDED  = 'refunded';

    protected $fillable = [
        'invoice_number',
        'user_id',
        'customer_id',
        'subtotal',
        'discount_amount',
        'total_amount',
        'tendered_amount',
        'change_amount',
        'status',
        'note',
        'sold_at',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'tendered_amount' => 'decimal:2',
        'change_amount'   => 'decimal:2',
        'sold_at'         => 'datetime',
    ];

    // Auto-generate invoice number on create
    // Format: INV-20240118-0042
    protected static function booted(): void
    {
        static::creating(function (Sale $sale) {
            if (empty($sale->invoice_number)) {
                $sale->invoice_number = self::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $date  = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;

        return 'INV-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('sold_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('sold_at', now()->month)
                     ->whereYear('sold_at', now()->year);
    }
}
