<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Models\Outlet;
use Modules\Core\Models\Rack;
use Modules\Core\Models\SubRack;

class RackController extends Controller
{
    public function index(): View
    {
        $storeId = auth()->user()->store_id;
        $racks   = Rack::with(['outlet', 'subRacks'])
            ->whereHas('outlet', fn ($q) => $q->where('store_id', $storeId))
            ->latest()
            ->paginate(20);

        return view('core::racks.index', compact('racks'));
    }

    public function create(): View
    {
        $storeId = auth()->user()->store_id;
        $outlets = Outlet::where('store_id', $storeId)->where('is_active', true)->get();
        return view('core::racks.create', compact('outlets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'outlet_id'     => 'required|exists:outlets,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'location_note' => 'nullable|string|max:255',
        ]);

        Rack::create([
            'outlet_id'     => $request->outlet_id,
            'name'          => $request->name,
            'description'   => $request->description,
            'location_note' => $request->location_note,
        ]);

        return redirect()->route('racks.index')
            ->with('success', 'Rak berhasil dibuat.');
    }

    public function edit(Rack $rack): View
    {
        $storeId = auth()->user()->store_id;
        $outlets = Outlet::where('store_id', $storeId)->where('is_active', true)->get();
        $rack->load('subRacks');
        return view('core::racks.edit', compact('rack', 'outlets'));
    }

    public function update(Request $request, Rack $rack): RedirectResponse
    {
        $request->validate([
            'outlet_id'     => 'required|exists:outlets,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'location_note' => 'nullable|string|max:255',
        ]);

        $rack->update([
            'outlet_id'     => $request->outlet_id,
            'name'          => $request->name,
            'description'   => $request->description,
            'location_note' => $request->location_note,
        ]);

        return redirect()->route('racks.index')
            ->with('success', 'Rak berhasil diperbarui.');
    }

    public function destroy(Rack $rack): RedirectResponse
    {
        $rack->delete();
        return redirect()->route('racks.index')
            ->with('success', 'Rak berhasil dihapus.');
    }

    // --- Sub-rack methods ---

    public function storeSubRack(Request $request, Rack $rack): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        SubRack::create([
            'rack_id'     => $rack->id,
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('racks.edit', $rack)
            ->with('success', 'Sub-rak berhasil ditambahkan.');
    }

    public function destroySubRack(Rack $rack, SubRack $subRack): RedirectResponse
    {
        $subRack->delete();
        return redirect()->route('racks.edit', $rack)
            ->with('success', 'Sub-rak berhasil dihapus.');
    }
}
