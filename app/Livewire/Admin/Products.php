<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class Products extends Component
{
    use WithPagination;

    // Form fields
    public string  $name = '';
    public string  $barcode = '';
    public bool    $has_barcode = false;
    public int     $category_id = 0;
    public ?int    $supplier_id = null;
    public string  $selling_price = '';
    public string  $cost_price = '';
    public int     $stock_quantity = 0;
    public int     $reorder_level = 5;
    public bool    $is_pinned = false;
    public ?int    $pin_position = null;
    public string  $author = '';
    public string  $isbn = '';
    public string  $description = '';
    public bool    $is_active = true;

    // UI state
    public ?int  $editingId = null;
    public bool  $showForm = false;
    public string $search = '';
    public string $filterCategory = '';
    public string $filterStock = '';

    protected function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'barcode'        => 'nullable|string|max:100|unique:products,barcode,' . ($this->editingId ?? 'NULL'),
            'has_barcode'    => 'boolean',
            'category_id'    => 'required|exists:categories,id',
            'supplier_id'    => 'nullable|exists:suppliers,id',
            'selling_price'  => 'required|numeric|min:0',
            'cost_price'     => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level'  => 'required|integer|min:0',
            'is_pinned'      => 'boolean',
            'pin_position'   => 'nullable|integer|min:1',
            'author'         => 'nullable|string|max:255',
            'isbn'           => 'nullable|string|max:20',
            'description'    => 'nullable|string',
            'is_active'      => 'boolean',
        ];
    }

    public function render()
    {
        $products = Product::with(['category', 'supplier'])
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%")
                  ->orWhere('barcode', 'like', "%{$this->search}%")
            )
            ->when($this->filterCategory, fn($q) =>
                $q->where('category_id', $this->filterCategory)
            )
            ->when($this->filterStock === 'low', fn($q) =>
                $q->whereColumn('stock_quantity', '<=', 'reorder_level')
            )
            ->when($this->filterStock === 'out', fn($q) =>
                $q->where('stock_quantity', 0)
            )
            ->orderBy('name')
            ->paginate(20);

        $categories = Category::active()->orderBy('name')->get();
        $suppliers  = Supplier::active()->orderBy('name')->get();

        return view('livewire.admin.products', compact('products', 'categories', 'suppliers'))
            ->layout('layouts.admin', ['title' => 'Products']);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $product = Product::findOrFail($id);

        $this->editingId      = $id;
        $this->name           = $product->name;
        $this->barcode        = $product->barcode ?? '';
        $this->has_barcode    = $product->has_barcode;
        $this->category_id    = $product->category_id;
        $this->supplier_id    = $product->supplier_id;
        $this->selling_price  = $product->selling_price;
        $this->cost_price     = $product->cost_price ?? '';
        $this->stock_quantity = $product->stock_quantity;
        $this->reorder_level  = $product->reorder_level;
        $this->is_pinned      = $product->is_pinned;
        $this->pin_position   = $product->pin_position;
        $this->author         = $product->author ?? '';
        $this->isbn           = $product->isbn ?? '';
        $this->description    = $product->description ?? '';
        $this->is_active      = $product->is_active;

        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'           => $this->name,
            'barcode'        => $this->has_barcode && $this->barcode ? $this->barcode : null,
            'has_barcode'    => $this->has_barcode,
            'category_id'    => $this->category_id,
            'supplier_id'    => $this->supplier_id ?: null,
            'selling_price'  => $this->selling_price,
            'cost_price'     => $this->cost_price ?: null,
            'stock_quantity' => $this->stock_quantity,
            'reorder_level'  => $this->reorder_level,
            'is_pinned'      => $this->is_pinned,
            'pin_position'   => $this->is_pinned ? $this->pin_position : null,
            'author'         => $this->author ?: null,
            'isbn'           => $this->isbn ?: null,
            'description'    => $this->description ?: null,
            'is_active'      => $this->is_active,
        ];

        if ($this->editingId) {
            Product::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Product updated.');
        } else {
            // SKU is auto-generated by the Product model's booted() method
            Product::create($data);
            session()->flash('success', 'Product created.');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);
    }

    public function delete(int $id): void
    {
        Product::findOrFail($id)->delete();
        session()->flash('success', 'Product deleted.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'name', 'barcode', 'has_barcode', 'category_id', 'supplier_id',
            'selling_price', 'cost_price', 'stock_quantity', 'reorder_level',
            'is_pinned', 'pin_position', 'author', 'isbn', 'description',
            'editingId',
        ]);
        $this->is_active     = true;
        $this->reorder_level = 5;
    }
}
