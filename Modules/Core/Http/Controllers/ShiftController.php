<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Models\Outlet;
use Modules\Core\Models\Shift;
use Modules\Core\Services\ShiftService;

class ShiftController extends Controller
{
    public function __construct(private readonly ShiftService $shiftService) {}

    public function index(): View
    {
        $storeId = auth()->user()->store_id;
        $shifts  = Shift::whereHas('outlet', fn ($q) => $q->where('store_id', $storeId))
            ->with(['outlet', 'user'])
            ->latest('opened_at')
            ->paginate(15);

        $outlets = Outlet::where('store_id', $storeId)->where('is_active', true)->get();

        return view('core::shifts.index', compact('shifts', 'outlets'));
    }

    public function open(Request $request): RedirectResponse
    {
        $request->validate([
            'outlet_id'  => 'required|exists:outlets,id',
            'cash_start' => 'required|numeric|min:0',
            'note'       => 'nullable|string|max:500',
        ]);

        try {
            $this->shiftService->openShift(
                (int) $request->outlet_id,
                (float) $request->cash_start,
                $request->note ?? ''
            );

            return redirect()->route('kasir.index')
                ->with('success', 'Shift berhasil dibuka. Selamat bekerja!');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function close(Request $request, Shift $shift): RedirectResponse
    {
        $request->validate([
            'cash_end' => 'required|numeric|min:0',
            'note'     => 'nullable|string|max:500',
        ]);

        try {
            $this->shiftService->closeShift(
                $shift,
                (float) $request->cash_end,
                $request->note ?? ''
            );

            return redirect()->route('shifts.index')
                ->with('success', 'Shift berhasil ditutup.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
