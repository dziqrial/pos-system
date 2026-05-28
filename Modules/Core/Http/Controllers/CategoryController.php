<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $storeId    = auth()->user()->store_id;
        $query      = Category::where('store_id', $storeId)->with('parent');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $categories = $query->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString();

        return view('core::categories.index', compact('categories'));
    }

    public function create(): View
    {
        $storeId = auth()->user()->store_id;
        $parents = Category::where('store_id', $storeId)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('core::categories.create', compact('parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'parent_id'  => 'nullable|exists:categories,id',
            'icon'       => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        Category::create([
            'store_id'   => auth()->user()->store_id,
            'parent_id'  => $request->parent_id,
            'name'       => $request->name,
            'icon'       => $request->icon,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil dibuat.');
    }

    public function edit(Category $category): View
    {
        $storeId = auth()->user()->store_id;
        $parents = Category::where('store_id', $storeId)
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('core::categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'parent_id'  => 'nullable|exists:categories,id',
            'icon'       => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category->update([
            'parent_id'  => $request->parent_id,
            'name'       => $request->name,
            'icon'       => $request->icon,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
