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
    // Each item: [product_id, name, sku, price, qty, discount, line_total, added_via, max_qty]
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
    // Discount (per cart line)
    // -------------------------------------------------------
    public ?int   $discountingIndex = null;   // which cart index is being discounted
    public bool   $showDiscountModal = false;
    public string $discountType = 'fixed';    // 'fixed' or 'percent'
    public string $discountValue = '';

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
            $this->clampDiscount($index);
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
            $this->clampDiscount($index);
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
        $this->subtotal      = array_sum(array_map(
            fn($item) => $item['price'] * $item['qty'],
            $this->cart
        ));
        $this->discountTotal = array_sum(array_column($this->cart, 'discount'));
        $this->grandTotal    = $this->subtotal - $this->discountTotal;

        $tendered            = (float) $this->tenderedAmount;
        $this->changeAmount  = max(0, $tendered - $this->grandTotal);
    }

    // -------------------------------------------------------
    // Discount (per cart line — fixed amount or percentage)
    // -------------------------------------------------------
    public function openItemDiscount(int $index): void
    {
        $this->discountingIndex = $index;
        $this->discountType     = 'fixed';
        $existing                = $this->cart[$index]['discount'] ?? 0;
        $this->discountValue    = $existing > 0 ? (string) $existing : '';
        $this->showDiscountModal = true;
    }

    public function closeDiscountModal(): void
    {
        $this->showDiscountModal = false;
        $this->discountingIndex  = null;
        $this->discountValue     = '';
    }

    public function applyItemDiscount(): void
    {
        if ($this->discountingIndex === null || !isset($this->cart[$this->discountingIndex])) {
            $this->closeDiscountModal();
            return;
        }

        $value = (float) $this->discountValue;

        if ($value < 0) {
            $this->dispatch('notify', type: 'error', message: 'Discount cannot be negative.');
            return;
        }

        $index         = $this->discountingIndex;
        $item          = $this->cart[$index];
        $lineSubtotal  = $item['price'] * $item['qty'];

        if ($this->discountType === 'percent') {
            if ($value > 100) {
                $this->dispatch('notify', type: 'error', message: 'Percentage discount cannot exceed 100%.');
                return;
            }
            $amount = round($lineSubtotal * ($value / 100), 2);
        } else {
            $amount = round($value, 2);
        }

        // Never let discount exceed the line's own subtotal
        $amount = min($amount, $lineSubtotal);

        $this->cart[$index]['discount']   = $amount;
        $this->cart[$index]['line_total'] = SaleItem::computeLineTotal(
            $item['price'],
            $item['qty'],
            $amount
        );

        $this->recalculate();
        $this->closeDiscountModal();
    }

    public function removeItemDiscount(int $index): void
    {
        if (!isset($this->cart[$index])) {
            return;
        }

        $this->cart[$index]['discount']   = 0.0;
        $this->cart[$index]['line_total'] = SaleItem::computeLineTotal(
            $this->cart[$index]['price'],
            $this->cart[$index]['qty'],
            0.0
        );

        $this->recalculate();
    }

    /**
     * If qty changes and the line's discount now exceeds the new subtotal
     * (e.g. a fixed discount larger than 1x price, after qty was reduced),
     * clamp it down so we never discount below zero.
     */
    private function clampDiscount(int $index): void
    {
        $item         = $this->cart[$index];
        $lineSubtotal = $item['price'] * $item['qty'];

        if ($item['discount'] > $lineSubtotal) {
            $this->cart[$index]['discount'] = $lineSubtotal;
        }
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
                $this->redirect(route('sales.receipt', $sale->id));
            });

        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Sale failed: ' . $e->getMessage());
        }
    }
}
