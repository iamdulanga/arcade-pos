<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'loyalty_points',
        'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'loyalty_points' => 'integer',
    ];

    // Relationships
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // Helpers
    public function totalSpent(): float
    {
        return $this->sales()->where('status', 'completed')->sum('total_amount');
    }

    public function addLoyaltyPoints(int $points): void
    {
        $this->increment('loyalty_points', $points);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
