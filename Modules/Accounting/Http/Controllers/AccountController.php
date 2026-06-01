<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\Account;

class AccountController extends Controller
{
    public function index()
    {
        $storeId  = auth()->user()->store_id;
        $accounts = Account::where('store_id', $storeId)
            ->orderBy('code')
            ->get()
            ->groupBy('type');

        return view('accounting::accounts.index', compact('accounts'));
    }

    public function create()
    {
        $storeId = auth()->user()->store_id;
        $parents = Account::where('store_id', $storeId)->whereNull('parent_id')->orderBy('code')->get();

        return view('accounting::accounts.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'      => 'required|string|max:20',
            'name'      => 'required|string|max:100',
            'type'      => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:accounts,id',
        ]);

        $data['store_id'] = auth()->user()->store_id;
        $data['is_active'] = true;

        Account::create($data);

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil ditambahkan.');
    }

    public function edit(Account $account)
    {
        $storeId = auth()->user()->store_id;
        $parents = Account::where('store_id', $storeId)
            ->whereNull('parent_id')
            ->where('id', '!=', $account->id)
            ->orderBy('code')
            ->get();

        return view('accounting::accounts.edit', compact('account', 'parents'));
    }

    public function update(Request $request, Account $account)
    {
        $data = $request->validate([
            'code'      => 'required|string|max:20',
            'name'      => 'required|string|max:100',
            'type'      => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'is_active' => 'boolean',
        ]);

        $account->update($data);

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(Account $account)
    {
        if ($account->journalLines()->exists()) {
            return back()->with('error', 'Akun tidak bisa dihapus karena sudah digunakan di jurnal.');
        }

        $account->delete();

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil dihapus.');
    }
}
