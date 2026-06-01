<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Modules\System\Models\Role;
use Modules\System\Models\User;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $storeId = auth()->user()->store_id;

        $users = User::with('role')
            ->where('store_id', $storeId)
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $inner->where('name', 'like', "%{$request->search}%")
                          ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('system::users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::where('store_id', auth()->user()->store_id)->get();

        return view('system::users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $storeId = auth()->user()->store_id;

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'role_id'   => ['nullable', 'exists:roles,id'],
            'pin'       => ['nullable', 'digits:6'],
            'is_active' => ['boolean'],
        ]);

        User::create([
            'store_id'  => $storeId,
            'role_id'   => $validated['role_id'] ?? null,
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'pin'       => $validated['pin'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $roles = Role::where('store_id', auth()->user()->store_id)->get();

        return view('system::users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email,' . $user->id],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_id'   => ['nullable', 'exists:roles,id'],
            'pin'       => ['nullable', 'digits:6'],
            'is_active' => ['boolean'],
        ]);

        $updateData = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role_id'   => $validated['role_id'] ?? null,
            'pin'       => $validated['pin'] ?? $user->pin,
            'is_active' => $request->boolean('is_active', true),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
