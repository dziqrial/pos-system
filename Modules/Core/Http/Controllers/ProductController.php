<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Imports\ProductImport;
use Modules\Core\Models\Category;
use Modules\Core\Models\Product;
use Modules\Core\Models\ProductVariant;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $storeId = auth()->user()->store_id;
        $query   = Product::where('store_id', $storeId)->with('category');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('stock_type')) {
            $query->where('stock_type', $request->stock_type);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $products   = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::where('store_id', $storeId)->orderBy('name')->get();

        return view('core::products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $storeId    = auth()->user()->store_id;
        $categories = Category::where('store_id', $storeId)->orderBy('name')->get();

        return view('core::products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'barcode'             => 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'category_id'         => 'nullable|exists:categories,id',
            'stock_type'          => 'required|in:normal,serial,bulk',
            'variant_name'        => 'required|string|max:255',
            'variant_sku'         => 'nullable|string|max:100|unique:product_variants,sku',
            'variant_price'       => 'required|numeric|min:0',
            'variant_cost'        => 'nullable|numeric|min:0',
            'variant_unit'        => 'required|string|max:20',
            'variant_unit_type'   => 'required|in:pcs,weight',
            'variant_barcode'     => 'nullable|string|max:100',
        ]);

        $product = Product::create([
            'store_id'    => auth()->user()->store_id,
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'barcode'     => $request->barcode,
            'description' => $request->description,
            'stock_type'  => $request->stock_type,
            'has_variants' => false,
            'is_active'   => true,
        ]);

        $sku = $request->variant_sku ?: strtoupper(Str::slug($product->name, '-')) . '-' . $product->id;

        ProductVariant::create([
            'product_id' => $product->id,
            'name'       => $request->variant_name,
            'sku'        => $sku,
            'barcode'    => $request->variant_barcode ?: $request->barcode,
            'price'      => $request->variant_price,
            'cost'       => $request->variant_cost ?? 0,
            'unit'       => $request->variant_unit,
            'unit_type'  => $request->variant_unit_type,
            'is_active'  => true,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dibuat.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'variants']);
        return view('core::products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $storeId    = auth()->user()->store_id;
        $categories = Category::where('store_id', $storeId)->orderBy('name')->get();
        $product->load('variants');

        return view('core::products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'barcode'     => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'stock_type'  => 'required|in:normal,serial,bulk',
            'is_active'   => 'boolean',
        ]);

        $product->update([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'barcode'     => $request->barcode,
            'description' => $request->description,
            'stock_type'  => $request->stock_type,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('products.edit', $product)
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Tambah varian baru ke produk.
     */
    public function storeVariant(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'sku'       => 'nullable|string|max:100|unique:product_variants,sku',
            'barcode'   => 'nullable|string|max:100',
            'price'     => 'required|numeric|min:0',
            'cost'      => 'nullable|numeric|min:0',
            'unit'      => 'required|string|max:20',
            'unit_type' => 'required|in:pcs,weight',
        ]);

        $sku = $request->sku ?: strtoupper(Str::slug($product->name . '-' . $request->name, '-')) . '-' . time();

        ProductVariant::create([
            'product_id' => $product->id,
            'name'       => $request->name,
            'sku'        => $sku,
            'barcode'    => $request->barcode,
            'price'      => $request->price,
            'cost'       => $request->cost ?? 0,
            'unit'       => $request->unit,
            'unit_type'  => $request->unit_type,
            'is_active'  => true,
        ]);

        $product->update(['has_variants' => $product->variants()->count() > 1]);

        return redirect()->route('products.edit', $product)
            ->with('success', 'Varian berhasil ditambahkan.');
    }

    /**
     * Update varian produk.
     */
    public function updateVariant(Request $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'sku'       => 'nullable|string|max:100|unique:product_variants,sku,' . $variant->id,
            'barcode'   => 'nullable|string|max:100',
            'price'     => 'required|numeric|min:0',
            'cost'      => 'nullable|numeric|min:0',
            'unit'      => 'required|string|max:20',
            'unit_type' => 'required|in:pcs,weight',
            'is_active' => 'boolean',
        ]);

        $variant->update([
            'name'      => $request->name,
            'sku'       => $request->sku ?: $variant->sku,
            'barcode'   => $request->barcode,
            'price'     => $request->price,
            'cost'      => $request->cost ?? 0,
            'unit'      => $request->unit,
            'unit_type' => $request->unit_type,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('products.edit', $product)
            ->with('success', 'Varian berhasil diperbarui.');
    }

    public function importTemplate(): Response
    {
        $headers = ['name', 'barcode', 'category', 'stock_type', 'price', 'cost', 'unit', 'unit_type', 'sku', 'description'];
        $example = ['Contoh Produk', '8991234567890', 'Minuman', 'normal', '15000', '10000', 'pcs', 'pcs', '', 'Deskripsi opsional'];

        $csv  = implode(',', $headers) . "\n";
        $csv .= implode(',', $example) . "\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template-import-produk.csv"',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new ProductImport(auth()->user()->store_id);

        Excel::import($import, $request->file('file'));

        if ($import->errors) {
            return back()->with('warning', $import->imported . ' produk berhasil diimpor. ' . count($import->errors) . ' baris gagal: ' . implode(' | ', array_slice($import->errors, 0, 5)));
        }

        return redirect()->route('products.index')
            ->with('success', $import->imported . ' produk berhasil diimpor.');
    }

    /**
     * Hapus varian produk.
     */
    public function destroyVariant(Product $product, ProductVariant $variant): RedirectResponse
    {
        if ($product->variants()->count() <= 1) {
            return back()->with('error', 'Produk harus memiliki minimal satu varian.');
        }

        $variant->delete();
        $product->update(['has_variants' => $product->fresh()->variants()->count() > 1]);

        return redirect()->route('products.edit', $product)
            ->with('success', 'Varian berhasil dihapus.');
    }
}
