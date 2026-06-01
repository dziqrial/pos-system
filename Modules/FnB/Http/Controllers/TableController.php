<?php

namespace Modules\FnB\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Models\Outlet;
use Modules\FnB\Models\Table;

class TableController extends Controller
{
    public function index(): View
    {
        $storeId = auth()->user()->store_id;
        $tables  = Table::whereHas('outlet', fn ($q) => $q->where('store_id', $storeId))
            ->with('outlet')
            ->orderBy('name')
            ->get()
            ->groupBy('outlet_id');

        $outlets = Outlet::where('store_id', $storeId)->where('is_active', true)->get();

        return view('fnb::tables.index', compact('tables', 'outlets'));
    }

    public function create(): View
    {
        $storeId = auth()->user()->store_id;
        $outlets = Outlet::where('store_id', $storeId)->where('is_active', true)->get();
        return view('fnb::tables.create', compact('outlets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'name'      => 'required|string|max:100',
            'capacity'  => 'required|integer|min:1',
        ]);

        Table::create($request->only('outlet_id', 'name', 'capacity'));

        return redirect()->route('tables.index')->with('success', 'Meja berhasil ditambahkan.');
    }

    public function edit(Table $table): View
    {
        $storeId = auth()->user()->store_id;
        $outlets = Outlet::where('store_id', $storeId)->where('is_active', true)->get();
        return view('fnb::tables.edit', compact('table', 'outlets'));
    }

    public function update(Request $request, Table $table): RedirectResponse
    {
        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'name'      => 'required|string|max:100',
            'capacity'  => 'required|integer|min:1',
            'status'    => 'required|in:available,occupied,reserved',
        ]);

        $table->update($request->only('outlet_id', 'name', 'capacity', 'status'));

        return redirect()->route('tables.index')->with('success', 'Meja berhasil diperbarui.');
    }

    public function destroy(Table $table): RedirectResponse
    {
        $table->delete();
        return redirect()->route('tables.index')->with('success', 'Meja berhasil dihapus.');
    }
}
