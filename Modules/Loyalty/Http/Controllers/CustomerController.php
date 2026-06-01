<?php

namespace Modules\Loyalty\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Loyalty\Models\Customer;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $storeId = auth()->user()->store_id;

        $customers = Customer::where('store_id', $storeId)
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $inner->where('name', 'like', "%{$request->search}%")
                          ->orWhere('phone', 'like', "%{$request->search}%")
                          ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('loyalty::customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('loyalty::customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $storeId = auth()->user()->store_id;

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:20', 'unique:customers,phone'],
            'email'      => ['nullable', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
        ]);

        Customer::create([
            'store_id'   => $storeId,
            'name'       => $validated['name'],
            'phone'      => $validated['phone'] ?? null,
            'email'      => $validated['email'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil ditambahkan.');
    }

    public function show(Customer $customer): View
    {
        $customer->load(['loyaltyPoints' => function ($q) {
            $q->orderByDesc('created_at')->limit(50);
        }]);

        return view('loyalty::customers.show', compact('customer'));
    }

    public function edit(Customer $customer): View
    {
        return view('loyalty::customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:20', 'unique:customers,phone,' . $customer->id],
            'email'      => ['nullable', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
        ]);

        $customer->update([
            'name'       => $validated['name'],
            'phone'      => $validated['phone'] ?? null,
            'email'      => $validated['email'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil dihapus.');
    }
}
