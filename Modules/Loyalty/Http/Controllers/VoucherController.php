<?php

namespace Modules\Loyalty\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Loyalty\Models\Voucher;

class VoucherController extends Controller
{
    public function index(Request $request): View
    {
        $storeId = auth()->user()->store_id;

        $vouchers = Voucher::where('store_id', $storeId)
            ->when($request->search, fn ($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('loyalty::vouchers.index', compact('vouchers'));
    }

    public function create(): View
    {
        return view('loyalty::vouchers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $storeId = auth()->user()->store_id;

        $validated = $request->validate([
            'code'         => 'required|string|max:50|unique:vouchers,code',
            'name'         => 'required|string|max:255',
            'type'         => 'required|in:percent,fixed',
            'value'        => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'quota'        => 'nullable|integer|min:1',
            'started_at'   => 'nullable|date',
            'expired_at'   => 'nullable|date|after_or_equal:started_at',
            'is_active'    => 'boolean',
        ]);

        Voucher::create([
            'store_id'     => $storeId,
            'code'         => strtoupper($validated['code']),
            'name'         => $validated['name'],
            'type'         => $validated['type'],
            'value'        => $validated['value'],
            'min_purchase' => $validated['min_purchase'] ?? null,
            'max_discount' => $validated['max_discount'] ?? null,
            'quota'        => $validated['quota'] ?? null,
            'started_at'   => $validated['started_at'] ?? null,
            'expired_at'   => $validated['expired_at'] ?? null,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return redirect()->route('vouchers.index')
            ->with('success', 'Voucher berhasil ditambahkan.');
    }

    public function show(Voucher $voucher): View
    {
        $voucher->load('usages');
        return view('loyalty::vouchers.show', compact('voucher'));
    }

    public function edit(Voucher $voucher): View
    {
        return view('loyalty::vouchers.edit', compact('voucher'));
    }

    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $validated = $request->validate([
            'code'         => 'required|string|max:50|unique:vouchers,code,' . $voucher->id,
            'name'         => 'required|string|max:255',
            'type'         => 'required|in:percent,fixed',
            'value'        => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'quota'        => 'nullable|integer|min:1',
            'started_at'   => 'nullable|date',
            'expired_at'   => 'nullable|date|after_or_equal:started_at',
            'is_active'    => 'boolean',
        ]);

        $voucher->update([
            'code'         => strtoupper($validated['code']),
            'name'         => $validated['name'],
            'type'         => $validated['type'],
            'value'        => $validated['value'],
            'min_purchase' => $validated['min_purchase'] ?? null,
            'max_discount' => $validated['max_discount'] ?? null,
            'quota'        => $validated['quota'] ?? null,
            'started_at'   => $validated['started_at'] ?? null,
            'expired_at'   => $validated['expired_at'] ?? null,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return redirect()->route('vouchers.index')
            ->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        $voucher->delete();
        return redirect()->route('vouchers.index')
            ->with('success', 'Voucher berhasil dihapus.');
    }
}
