<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Component;

class Categories extends Component
{
    public string $name = '';
    public string $prefix = '';
    public string $description = '';
    public bool   $is_active = true;

    public ?int $editingId = null;
    public bool $showForm = false;

    public string $search = '';

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|max:100|unique:categories,name,' . ($this->editingId ?? 'NULL'),
            'prefix'      => 'required|string|max:5|unique:categories,prefix,' . ($this->editingId ?? 'NULL'),
            'description' => 'nullable|string|max:100',
            'is_active'   => 'boolean',
        ];
    }

    public function render()
    {
        $categories = Category::when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('prefix', 'like', "%{$this->search}%")
            )
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.categories', compact('categories'))
            ->layout('layouts.admin', ['title' => 'Categories']);
    }

    public function updatedName(): void
    {
        // Auto-suggest prefix from name if not editing
        if (!$this->editingId && empty($this->prefix)) {
            $this->prefix = strtoupper(substr(Str::slug($this->name, ''), 0, 3));
        }
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'prefix', 'description', 'is_active', 'editingId']);
        $this->is_active = true;
        $this->showForm  = true;
    }

    public function openEdit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->editingId   = $id;
        $this->name        = $category->name;
        $this->prefix      = $category->prefix;
        $this->description = $category->description ?? '';
        $this->is_active   = $category->is_active;
        $this->showForm    = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'        => $this->name,
            'prefix'      => strtoupper($this->prefix),
            'slug'        => Str::slug($this->name),
            'description' => $this->description ?: null,
            'is_active'   => $this->is_active,
        ];

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Category updated.');
        } else {
            Category::create($data);
            session()->flash('success', 'Category created.');
        }

        $this->showForm = false;
        $this->reset(['name', 'prefix', 'description', 'editingId']);
    }

    public function toggleActive(int $id): void
    {
        $category = Category::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);
    }

    public function delete(int $id): void
    {
        $category = Category::withCount('products')->findOrFail($id);

        if ($category->products_count > 0) {
            session()->flash('error', 'Cannot delete — this category has products. Deactivate it instead.');
            return;
        }

        $category->delete();
        session()->flash('success', 'Category deleted.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->reset(['name', 'prefix', 'description', 'editingId']);
        $this->resetValidation();
    }
}
