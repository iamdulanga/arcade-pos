<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function receipt(Sale $sale)
    {
        // Load everything needed for the receipt
        $sale->load([
            'items.product',
            'customer',
            'user',
            'payments',
        ]);

        return view('sales.receipt', compact('sale'));
    }
}
