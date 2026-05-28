<?php

namespace Modules\Core\Services;

use Modules\Core\Events\ShiftClosed;
use Modules\Core\Events\ShiftOpened;
use Modules\Core\Models\Shift;

class ShiftService
{
    /**
     * Buka shift baru di outlet.
     */
    public function openShift(int $outletId, float $cashStart, string $note = ''): Shift
    {
        $existing = Shift::where('outlet_id', $outletId)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            throw new \RuntimeException('Ada shift yang masih terbuka di outlet ini. Tutup shift terlebih dahulu.');
        }

        $shift = Shift::create([
            'outlet_id'  => $outletId,
            'user_id'    => auth()->id(),
            'opened_at'  => now(),
            'cash_start' => $cashStart,
            'status'     => 'open',
            'note'       => $note,
        ]);

        event(new ShiftOpened($shift));

        return $shift;
    }

    /**
     * Tutup shift yang sedang berjalan.
     */
    public function closeShift(Shift $shift, float $cashEnd, string $note = ''): Shift
    {
        if (!$shift->isOpen()) {
            throw new \RuntimeException('Shift ini sudah ditutup.');
        }

        $shift->update([
            'status'    => 'closed',
            'closed_at' => now(),
            'cash_end'  => $cashEnd,
            'note'      => $note ?: $shift->note,
        ]);

        $shift->refresh();

        event(new ShiftClosed($shift));

        return $shift;
    }

    /**
     * Ambil shift yang sedang aktif (status open) di outlet.
     */
    public function getActiveShift(int $outletId): ?Shift
    {
        return Shift::where('outlet_id', $outletId)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();
    }
}
