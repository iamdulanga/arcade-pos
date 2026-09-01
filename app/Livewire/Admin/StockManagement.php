<?php

namespace App\Livewire\Admin;

use App\Models\InventoryAdjustment;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class StockManagement extends Component
{
    use WithPagination;

    // Filters
    public string $search = '';
    public string $filterCategory = '';
    public string $filterStock = '';

    // Adjustment form
    public bool   $showForm = false;
    public ?int   $selectedProductId = null;
    public string $selectedProductName = '';
    public int    $currentStock = 0;
    public string $adjustmentType = 'stock_in';
    public int    $quantity = 1;
    public string $reference = '';
    public string $note = '';

    // History modal
    public bool   $showHistory = false;
    public ?int   $historyProductId = null;
    public string $historyProductName = '';

    protected function rules(): array
    {
        return [
            'quantity'       => 'required|integer|min:1',
            'adjustmentType' => 'required|in:stock_in,stock_out,damage,correction',
            'reference'      => 'nullable|string|max:100',
            'note'           => 'nullable|string|max:255',
        ];
    }

    public function render()
    {
        $products = Product::with('category')
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%")
            )
            ->when($this->filterCategory, fn($q) =>
                $q->where('category_id', $this->filterCategory)
            )
            ->when($this->filterStock === 'low', fn($q) =>
                $q->whereColumn('stock_quantity', '<=', 'reorder_level')
                  ->where('stock_quantity', '>', 0)
            )
            ->when($this->filterStock === 'out', fn($q) =>
                $q->where('stock_quantity', 0)
            )
            ->active()
            ->orderBy('name')
            ->paginate(20);

        $categories = \App\Models\Category::active()->orderBy('name')->get();

        $history = [];
        if ($this->showHistory && $this->historyProductId) {
            $history = InventoryAdjustment::with('user')
                ->where('product_id', $this->historyProductId)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();
        }

        return view('livewire.admin.stock-management', compact('products', 'categories', 'history'))
            ->layout('layouts.admin', ['title' => 'Stock Management']);
    }

    public function openAdjust(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->selectedProductId   = $productId;
        $this->selectedProductName = $product->name;
        $this->currentStock        = $product->stock_quantity;
        $this->adjustmentType      = 'stock_in';
        $this->quantity            = 1;
        $this->reference           = '';
        $this->note                = '';
        $this->showForm            = true;
    }

    public function openHistory(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->historyProductId   = $productId;
        $this->historyProductName = $product->name;
        $this->showHistory        = true;
    }

    public function save(): void
    {
        $this->validate();

        $product = Product::findOrFail($this->selectedProductId);

        // For stock_out and damage, quantity is negative
        $change = in_array($this->adjustmentType, ['stock_out', 'damage'])
            ? -abs($this->quantity)
            : abs($this->quantity);

        // Prevent going below zero
        if ($product->stock_quantity + $change < 0) {
            $this->addError('quantity', 'Cannot remove more than current stock (' . $product->stock_quantity . ').');
            return;
        }

        InventoryAdjustment::record(
            product:   $product,
            change:    $change,
            type:      $this->adjustmentType,
            userId:    auth()->id(),
            reference: $this->reference ?: null,
            note:      $this->note ?: null,
        );

        session()->flash('success', "Stock updated for {$product->name}.");
        $this->showForm = false;
        $this->resetPage();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function closeHistory(): void
    {
        $this->showHistory      = false;
        $this->historyProductId = null;
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterCategory(): void { $this->resetPage(); }
    public function updatedFilterStock(): void { $this->resetPage(); }
}
