<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Models\Outlet;

class OutletController extends Controller
{
    public function index(): View
    {
        $outlets = Outlet::where('store_id', auth()->user()->store_id)
            ->latest()
            ->paginate(20);

        return view('core::outlets.index', compact('outlets'));
    }

    public function create(): View
    {
        return view('core::outlets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone'   => ['nullable', 'string', 'max:20'],
        ]);

        Outlet::create([
            'store_id'  => auth()->user()->store_id,
            'name'      => $request->name,
            'address'   => $request->address,
            'phone'     => $request->phone,
            'is_active' => true,
        ]);

        return redirect()->route('outlets.index')
            ->with('success', 'Outlet berhasil dibuat.');
    }

    public function edit(Outlet $outlet): View
    {
        return view('core::outlets.edit', compact('outlet'));
    }

    public function update(Request $request, Outlet $outlet): RedirectResponse
    {
        $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone'   => ['nullable', 'string', 'max:20'],
        ]);

        $outlet->update([
            'name'    => $request->name,
            'address' => $request->address,
            'phone'   => $request->phone,
        ]);

        return redirect()->route('outlets.index')
            ->with('success', 'Outlet berhasil diperbarui.');
    }

    public function destroy(Outlet $outlet): RedirectResponse
    {
        $outlet->delete();

        return redirect()->route('outlets.index')
            ->with('success', 'Outlet berhasil dihapus.');
    }
}
