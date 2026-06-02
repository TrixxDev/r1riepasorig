<?php

namespace App\Services;

use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Reassigns slot iorder when working-day open time or step changes, preserving wall-clock slot start time.
 */
class SlotWallTimeReorderService
{
    public const IORDER_TEMP_OFFSET = 1_000_000;

    /**
     * Compute new iorder for a slot index given old/new schedule (pure math; used in tests).
     */
    public static function computeNewIorder(
        string $date,
        string $oldOpenTime,
        int $oldStep,
        int $currentIorder,
        string $newOpenTime,
        int $newStep
    ): int {
        $oldStep = max(1, $oldStep);
        $newStep = max(1, $newStep);

        $absolute = self::absoluteSlotStart($date, $oldOpenTime, $currentIorder, $oldStep);
        $newBase = self::absoluteSlotStart($date, $newOpenTime, 0, $newStep);
        $minutesFromNewOpen = ($absolute->getTimestamp() - $newBase->getTimestamp()) / 60;

        return (int) round($minutesFromNewOpen / $newStep);
    }

    public static function absoluteSlotStart(string $date, string $openTime, int $iorder, int $step): Carbon
    {
        $step = max(1, $step);
        $base = Carbon::parse($date.' '.self::normalizeTime($openTime));

        return $base->copy()->addMinutes($iorder * $step);
    }

    /**
     * Recompute all slots for a queue/day. Uses two-phase iorder update to avoid unique collisions in app logic.
     */
    public function recomputeSlotsForScheduleChange(
        string $date,
        int $queueId,
        string $oldOpenTime,
        int $oldStep,
        string $newOpenTime,
        int $newStep
    ): void {
        $oldStep = max(1, $oldStep);
        $newStep = max(1, $newStep);

        $slots = Slot::where('date', $date)
            ->where('queue_id', $queueId)
            ->orderBy('slot_id')
            ->get();

        if ($slots->isEmpty()) {
            return;
        }

        $assignments = [];
        foreach ($slots as $slot) {
            $assignments[$slot->slot_id] = self::computeNewIorder(
                $date,
                $oldOpenTime,
                $oldStep,
                (int) $slot->iorder,
                $newOpenTime,
                $newStep
            );
        }

        $assignments = $this->resolveDuplicateIorders($assignments);

        DB::transaction(function () use ($date, $queueId, $slots, $assignments) {
            Slot::where('date', $date)
                ->where('queue_id', $queueId)
                ->update(['iorder' => DB::raw('iorder + '.self::IORDER_TEMP_OFFSET)]);

            foreach ($slots as $slot) {
                $newIorder = $assignments[$slot->slot_id];
                Slot::where('slot_id', $slot->slot_id)->update(['iorder' => $newIorder]);
            }
        });
    }

    /**
     * If two slots round to the same iorder, bump later slot_ids (stable order).
     *
     * @param  array<int, int>  $slotIdToOrder
     * @return array<int, int>
     */
    private function resolveDuplicateIorders(array $slotIdToOrder): array
    {
        ksort($slotIdToOrder);
        $used = [];
        $out = [];
        foreach ($slotIdToOrder as $slotId => $io) {
            while (isset($used[$io])) {
                $io++;
            }
            $used[$io] = true;
            $out[$slotId] = $io;
        }

        return $out;
    }

    private static function normalizeTime(string $time): string
    {
        $time = trim($time);
        if ($time === '') {
            return '00:00:00';
        }
        if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
            return $time.':00';
        }

        return $time;
    }
}
