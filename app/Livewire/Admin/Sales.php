<?php

namespace App\Livewire\Admin;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

class Sales extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterDate = '';

    public function render()
    {
        $sales = Sale::with(['user', 'customer', 'items', 'payments'])
            ->when($this->search, fn($q) =>
                $q->where('invoice_number', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', fn($q) =>
                      $q->where('name', 'like', "%{$this->search}%")
                  )
            )
            ->when($this->filterStatus, fn($q) =>
                $q->where('status', $this->filterStatus)
            )
            ->when($this->filterDate, fn($q) =>
                $q->whereDate('sold_at', $this->filterDate)
            )
            ->orderByDesc('sold_at')
            ->paginate(20);

        return view('livewire.admin.sales', compact('sales'))
            ->layout('layouts.admin', ['title' => 'Sales History']);
    }

    public function void(int $id): void
    {
        $sale = Sale::findOrFail($id);

        if ($sale->status !== Sale::STATUS_COMPLETED) {
            session()->flash('error', 'Only completed sales can be voided.');
            return;
        }

        // Restore stock for each item
        foreach ($sale->items as $item) {
            $product = $item->product;
            if ($product) {
                \App\Models\InventoryAdjustment::record(
                    product:   $product,
                    change:    $item->quantity,
                    type:      \App\Models\InventoryAdjustment::TYPE_RETURN,
                    userId:    auth()->id(),
                    reference: $sale->invoice_number,
                    note:      'Sale voided',
                );
            }
        }

        $sale->update(['status' => Sale::STATUS_VOIDED]);
        session()->flash('success', "Sale {$sale->invoice_number} voided and stock restored.");
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterDate(): void { $this->resetPage(); }
}
