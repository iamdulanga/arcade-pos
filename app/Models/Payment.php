<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    const METHOD_CASH           = 'cash';
    const METHOD_CARD           = 'card';
    const METHOD_TRANSFER       = 'transfer';
    const METHOD_LOYALTY_POINTS = 'loyalty_points';

    protected $fillable = [
        'sale_id',
        'method',
        'amount',
        'reference',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
