<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\Sale;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        // Today's stats
        $todayRevenue      = Sale::completed()->today()->sum('total_amount');
        $todayCount        = Sale::completed()->today()->count();
        $todayAverage      = $todayCount > 0 ? $todayRevenue / $todayCount : 0;

        // Month stats
        $monthRevenue      = Sale::completed()->thisMonth()->sum('total_amount');

        // Recent sales
        $recentSales       = Sale::with(['user', 'customer', 'payments'])
                                ->completed()
                                ->orderByDesc('sold_at')
                                ->limit(8)
                                ->get();

        // Low stock
        $lowStockProducts  = Product::active()
                                ->whereColumn('stock_quantity', '<=', 'reorder_level')
                                ->with('category')
                                ->orderBy('stock_quantity')
                                ->limit(8)
                                ->get();

        $outOfStock        = Product::active()->where('stock_quantity', 0)->count();
        $totalProducts     = Product::active()->count();

        return view('livewire.admin.dashboard', compact(
            'todayRevenue',
            'todayCount',
            'todayAverage',
            'monthRevenue',
            'recentSales',
            'lowStockProducts',
            'outOfStock',
            'totalProducts',
        ))->layout('layouts.admin', ['title' => 'Dashboard']);
    }
}
