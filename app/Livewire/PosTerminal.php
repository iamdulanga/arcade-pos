<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\InventoryAdjustment;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PosTerminal extends Component
{
    // -------------------------------------------------------
    // Search / scan
    // -------------------------------------------------------
    public string $searchTerm = '';
    public array  $searchResults = [];
    public bool   $showResults = false;

    // -------------------------------------------------------
    // Cart
    // Each item: [product_id, name, sku, price, qty, discount, line_total, added_via]
    // -------------------------------------------------------
    public array $cart = [];

    // -------------------------------------------------------
    // Quick-select
    // -------------------------------------------------------
    public int    $activeTab = 0;         // active category tab index
    public array  $categories = [];
    public array  $pinnedProducts = [];

    // -------------------------------------------------------
    // Customer
    // -------------------------------------------------------
    public string $customerSearch = '';
    public ?int   $customerId = null;
    public string $customerName = '';

    // -------------------------------------------------------
    // Payment
    // -------------------------------------------------------
    public bool   $showPaymentModal = false;
    public string $paymentMethod = 'cash';
    public string $tenderedAmount = '';
    public string $paymentNote = '';

    // -------------------------------------------------------
    // Totals (computed from cart)
    // -------------------------------------------------------
    public float $subtotal = 0;
    public float $discountTotal = 0;
    public float $grandTotal = 0;
    public float $changeAmount = 0;

    // -------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------
    public function mount(): void
    {
        $this->loadQuickSelect();
    }

    public function render()
    {
        return view('livewire.pos-terminal')
            ->layout('layouts.pos');
    }

    // -------------------------------------------------------
    // Quick-select grid
    // -------------------------------------------------------
    private function loadQuickSelect(): void
    {
        $this->categories = \App\Models\Category::active()
            ->whereHas('products', fn($q) => $q->where('is_pinned', true)->active())
            ->get(['id', 'name'])
            ->toArray();

        $this->loadPinnedProducts();
    }

    public function loadPinnedProducts(): void
    {
        $categoryId = $this->categories[$this->activeTab]['id'] ?? null;

        $this->pinnedProducts = Product::active()
            ->pinned()
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->get(['id', 'name', 'selling_price', 'stock_quantity'])
            ->toArray();
    }

    public function setActiveTab(int $index): void
    {
        $this->activeTab = $index;
        $this->loadPinnedProducts();
    }

    // -------------------------------------------------------
    // Search (barcode scan or name/SKU typing)
    // -------------------------------------------------------
    public function updatedSearchTerm(): void
    {
        $term = trim($this->searchTerm);

        if (strlen($term) < 2) {
            $this->searchResults = [];
            $this->showResults   = false;
            return;
        }

        $results = Product::posSearch($term)
            ->limit(8)
            ->get(['id', 'name', 'sku', 'barcode', 'selling_price', 'stock_quantity'])
            ->toArray();

        // If exactly one result and term looks like a barcode/SKU (no spaces), add instantly
        if (count($results) === 1 && !str_contains($term, ' ')) {
            $this->addToCart($results[0]['id'], SaleItem::VIA_BARCODE_SCAN);
            $this->searchTerm  = '';
            $this->showResults = false;
            return;
        }

        $this->searchResults = $results;
        $this->showResults   = count($results) > 0;
    }

    public function selectFromSearch(int $productId): void
    {
        $this->addToCart($productId, SaleItem::VIA_SEARCH);
        $this->searchTerm    = '';
        $this->searchResults = [];
        $this->showResults   = false;
    }

    public function addFromQuickSelect(int $productId): void
    {
        $this->addToCart($productId, SaleItem::VIA_QUICK_SELECT);
    }

    // -------------------------------------------------------
    // Cart logic
    // -------------------------------------------------------
    public function addToCart(int $productId, string $via = SaleItem::VIA_SEARCH): void
    {
        $product = Product::find($productId);

        if (!$product || !$product->is_active) {
            $this->dispatch('notify', type: 'error', message: 'Product not found.');
            return;
        }

        if ($product->stock_quantity <= 0) {
            $this->dispatch('notify', type: 'error', message: "{$product->name} is out of stock.");
            return;
        }

        // If already in cart, increment qty
        foreach ($this->cart as &$item) {
            if ($item['product_id'] === $productId) {
                if ($item['qty'] >= $product->stock_quantity) {
                    $this->dispatch('notify', type: 'error', message: 'Not enough stock.');
                    return;
                }
                $item['qty']++;
                $item['line_total'] = SaleItem::computeLineTotal($item['price'], $item['qty'], $item['discount']);
                $this->recalculate();
                return;
            }
        }

        // New cart line
        $this->cart[] = [
            'product_id' => $product->id,
            'name'       => $product->name,
            'sku'        => $product->sku,
            'price'      => (float) $product->selling_price,
            'qty'        => 1,
            'discount'   => 0.0,
            'line_total' => (float) $product->selling_price,
            'added_via'  => $via,
            'max_qty'    => $product->stock_quantity,
        ];

        $this->recalculate();
    }

    public function incrementQty(int $index): void
    {
        if ($this->cart[$index]['qty'] < $this->cart[$index]['max_qty']) {
            $this->cart[$index]['qty']++;
            $this->cart[$index]['line_total'] = SaleItem::computeLineTotal(
                $this->cart[$index]['price'],
                $this->cart[$index]['qty'],
                $this->cart[$index]['discount']
            );
            $this->recalculate();
        }
    }

    public function decrementQty(int $index): void
    {
        if ($this->cart[$index]['qty'] > 1) {
            $this->cart[$index]['qty']--;
            $this->cart[$index]['line_total'] = SaleItem::computeLineTotal(
                $this->cart[$index]['price'],
                $this->cart[$index]['qty'],
                $this->cart[$index]['discount']
            );
            $this->recalculate();
        } else {
            $this->removeFromCart($index);
        }
    }

    public function setQty(int $index, int $qty): void
    {
        $qty = max(1, min($qty, $this->cart[$index]['max_qty']));
        $this->cart[$index]['qty'] = $qty;
        $this->cart[$index]['line_total'] = SaleItem::computeLineTotal(
            $this->cart[$index]['price'],
            $qty,
            $this->cart[$index]['discount']
        );
        $this->recalculate();
    }

    public function removeFromCart(int $index): void
    {
        array_splice($this->cart, $index, 1);
        $this->recalculate();
    }

    public function clearCart(): void
    {
        $this->cart          = [];
        $this->customerId    = null;
        $this->customerName  = '';
        $this->tenderedAmount = '';
        $this->recalculate();
    }

    private function recalculate(): void
    {
        $this->subtotal      = array_sum(array_column($this->cart, 'line_total'));
        $this->discountTotal = array_sum(array_column($this->cart, 'discount'));
        $this->grandTotal    = $this->subtotal;

        $tendered            = (float) $this->tenderedAmount;
        $this->changeAmount  = max(0, $tendered - $this->grandTotal);
    }

    // -------------------------------------------------------
    // Customer lookup
    // -------------------------------------------------------
    public function updatedTenderedAmount(): void
    {
        $this->recalculate();
    }

    public function attachCustomer(int $id, string $name): void
    {
        $this->customerId   = $id;
        $this->customerName = $name;
    }

    public function detachCustomer(): void
    {
        $this->customerId   = null;
        $this->customerName = '';
    }

    // -------------------------------------------------------
    // Payment modal
    // -------------------------------------------------------
    public function openPayment(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', type: 'error', message: 'Cart is empty.');
            return;
        }
        $this->showPaymentModal = true;
    }

    public function closePayment(): void
    {
        $this->showPaymentModal = false;
    }

    // -------------------------------------------------------
    // Complete sale
    // -------------------------------------------------------
    public function completeSale(): void
    {
        $this->validate([
            'paymentMethod'  => 'required|in:cash,card,transfer,loyalty_points',
            'tenderedAmount' => 'required|numeric|min:' . $this->grandTotal,
        ], [
            'tenderedAmount.min' => 'Tendered amount must be at least Rs. ' . number_format($this->grandTotal, 2),
        ]);

        try {
            DB::transaction(function () {

                // 1. Create the sale record
                $sale = Sale::create([
                    'user_id'         => auth()->id(),
                    'customer_id'     => $this->customerId,
                    'subtotal'        => $this->subtotal,
                    'discount_amount' => $this->discountTotal,
                    'total_amount'    => $this->grandTotal,
                    'tendered_amount' => (float) $this->tenderedAmount,
                    'change_amount'   => $this->changeAmount,
                    'status'          => Sale::STATUS_COMPLETED,
                    'note'            => $this->paymentNote ?: null,
                    'sold_at'         => now(),
                ]);

                // 2. Create sale items & deduct stock
                foreach ($this->cart as $item) {
                    SaleItem::create([
                        'sale_id'         => $sale->id,
                        'product_id'      => $item['product_id'],
                        'product_name'    => $item['name'],
                        'product_sku'     => $item['sku'],
                        'unit_price'      => $item['price'],
                        'quantity'        => $item['qty'],
                        'discount_amount' => $item['discount'],
                        'line_total'      => $item['line_total'],
                        'added_via'       => $item['added_via'],
                    ]);

                    // Deduct stock and log the adjustment
                    $product = Product::find($item['product_id']);
                    InventoryAdjustment::record(
                        product:   $product,
                        change:    -$item['qty'],
                        type:      InventoryAdjustment::TYPE_SALE,
                        userId:    auth()->id(),
                        reference: $sale->invoice_number,
                    );
                }

                // 3. Record payment
                Payment::create([
                    'sale_id'   => $sale->id,
                    'method'    => $this->paymentMethod,
                    'amount'    => (float) $this->tenderedAmount,
                    'reference' => null,
                    'note'      => $this->paymentNote ?: null,
                ]);

                // 4. Reset terminal
                $this->clearCart();
                $this->showPaymentModal = false;

                // 5. Redirect to receipt
                // session()->flash('print_receipt', true);
                // return $this->redirect(route('sales.receipt', $sale->id));
                $this->dispatch('open-receipt', url: route('sales.receipt', $sale->id));
            });

        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Sale failed: ' . $e->getMessage());
        }
    }
}
