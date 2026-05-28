<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Models\Supplier;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $storeId = auth()->user()->store_id;
        $query   = Supplier::where('store_id', $storeId);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('contact_name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $suppliers = $query->latest()->paginate(20)->withQueryString();

        return view('core::suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('core::suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:255',
            'address'      => 'nullable|string',
        ]);

        Supplier::create([
            'store_id'     => auth()->user()->store_id,
            'name'         => $request->name,
            'contact_name' => $request->contact_name,
            'phone'        => $request->phone,
            'email'        => $request->email,
            'address'      => $request->address,
            'is_active'    => true,
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('core::suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:255',
            'address'      => 'nullable|string',
            'is_active'    => 'boolean',
        ]);

        $supplier->update([
            'name'         => $request->name,
            'contact_name' => $request->contact_name,
            'phone'        => $request->phone,
            'email'        => $request->email,
            'address'      => $request->address,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }
}
