<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileSlotsController extends Controller
{
    public function liftSpots(Request $request)
    {
        $request->validate([
            'office_id' => 'nullable|integer|in:1,2',
        ]);

        $q = DB::table('slots')
            ->select('slots.lift_spot', 'slots.slot_id', 'slots.mobile_status')
            ->whereNotNull('slots.lift_spot');

        if ($request->filled('office_id')) {
            $queueIds = $this->queueIdsForOffice((int) $request->input('office_id'));
            if ($queueIds->isEmpty()) {
                $slots = collect();
            } else {
                $q->whereIn('slots.queue_id', $queueIds);
                $slots = $q->get();
            }
        } else {
            $slots = $q->get();
        }

        $liftSpots = [];
        for ($i = 1; $i <= 10; $i++) {
            $occupied = $slots->firstWhere('lift_spot', $i);

            $liftSpots[] = [
                'id' => $i,
                'isOccupied' => $occupied && (int) $occupied->mobile_status === 2,
                'occupiedBySlotId' => $occupied ? $occupied->slot_id : null,
                'occupiedByStatus' => $occupied ? $occupied->mobile_status : null,
            ];
        }

        return response()->json(['lift_spots' => $liftSpots]);
    }

    public function slots(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'queue_id' => 'nullable|integer',
            'office_id' => 'nullable|integer|in:1,2',
        ]);

        $q = DB::table('slots')
            ->where('date', $validated['date'])
            ->orderByRaw('CASE WHEN iorder IS NULL THEN 1 ELSE 0 END')
            ->orderBy('iorder')
            ->orderBy('queue_id')
            ->orderBy('slot_id');

        if ($request->filled('queue_id')) {
            $q->where('queue_id', (int) $request->input('queue_id'));
        } elseif ($request->filled('office_id')) {
            $queueIds = $this->queueIdsForOffice((int) $request->input('office_id'));
            if ($queueIds->isEmpty()) {
                return response()->json(['slots' => []]);
            }
            $q->whereIn('queue_id', $queueIds);
        }

        $slots = $q->limit(500)->get();

        $enriched = $this->enrichSlotsWithWallTime($slots, $validated['date']);

        return response()->json(['slots' => $this->sortSlotsForDisplay($enriched)]);
    }

    /**
     * Порядок по времени: сначала wall_time (HH:MM), затем iorder, очередь, slot_id.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $slots
     * @return \Illuminate\Support\Collection
     */
    private function sortSlotsForDisplay($slots)
    {
        return $slots->sortBy(function ($slot) {
            $w = $slot->wall_time ?? null;
            $hasWall = $w !== null && $w !== '';
            $timeKey = $hasWall ? (string) $w : '99:99';
            $iorder = (int) ($slot->iorder ?? 2147483647);
            $queueId = (int) ($slot->queue_id ?? 0);
            $slotId = (int) $slot->slot_id;

            return [$hasWall ? 0 : 1, $timeKey, $iorder, $queueId, $slotId];
        })->values();
    }

    /**
     * Время слота на сетке — та же модель, что и в Node TableGenerator::generateTimeSlots
     * (frontend /api/table: timeLabel для строки с dbSlotIndex = iorder).
     * Минуты: startTotalMinutes + slotIndex * step; метка только если slotTotalMinutes < endTotalMinutes.
     */
    private function enrichSlotsWithWallTime($slots, string $date)
    {
        if ($slots->isEmpty()) {
            return $slots;
        }

        $queueIds = $slots->pluck('queue_id')->unique()->filter()->values()->all();
        if ($queueIds === []) {
            return $slots;
        }

        $workingDays = DB::table('new_workingdays')
            ->where('date', $date)
            ->whereIn('queue_id', $queueIds)
            ->where('is_opened', 1)
            ->get()
            ->keyBy('queue_id');

        return $slots->map(function ($slot) use ($workingDays) {
            $wallTime = null;
            $wd = $workingDays->get($slot->queue_id ?? null);
            if ($wd !== null && isset($slot->iorder)) {
                $step = (int) ($wd->timeStep ?? 15);
                if ($step < 1) {
                    $step = 15;
                }
                $i = (int) $slot->iorder;
                $wallTime = $this->wallTimeLikeTableGenerator(
                    (string) $wd->timeopen,
                    (string) $wd->timeclose,
                    $step,
                    $i
                );
            }
            $slot->wall_time = $wallTime;

            return $slot;
        });
    }

    /** Зеркало backend/src/services/TableGenerator.ts → generateTimeSlots (timeLabel + граница end). */
    private function wallTimeLikeTableGenerator(
        string $timeopen,
        string $timeclose,
        int $timestep,
        int $slotIndex
    ): ?string {
        $startTotalMinutes = $this->timeStringToMinutes($timeopen);
        $endTotalMinutes = $this->timeStringToMinutes($timeclose);
        $step = max($timestep, 1);
        $slotTotalMinutes = $startTotalMinutes + $slotIndex * $step;
        if ($slotTotalMinutes < $startTotalMinutes || $slotTotalMinutes >= $endTotalMinutes) {
            return null;
        }
        $hours = (int) floor($slotTotalMinutes / 60) % 24;
        $minutes = $slotTotalMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    private function timeStringToMinutes(string $time): int
    {
        $parts = explode(':', trim($time));

        return ((int) ($parts[0] ?? 0)) * 60 + ((int) ($parts[1] ?? 0));
    }

    /**
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function queueIdsForOffice(int $officeId)
    {
        return DB::table('queues')
            ->where('office_id', $officeId)
            ->pluck('queue_id');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'mobile_status' => 'required|integer',
            'lift_spot' => 'nullable|integer|min:1|max:10',
        ]);

        $liftSpot = $validated['lift_spot'] ?? null;

        DB::table('slots')->where('slot_id', $id)->update([
            'mobile_status' => $validated['mobile_status'],
            'lift_spot' => $liftSpot,
        ]);

        $this->notifyNodeBroadcast((int) $id);

        return response()->json(['success' => true]);
    }

    /**
     * Atjaunina `slots.ic_status` un opcionāli `lift_spot` (kā web `PATCH /api/cells/:col/:idx/status`).
     * Body: { ic_status: string, lift_spot?: int|null }
     */
    public function updateIcStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'ic_status' => 'required|string|max:32',
            'lift_spot' => 'nullable|integer|min:1|max:10',
            // Optional: mobile sends wall time so D → P always persists a visible edit stamp.
            'appo_edittime' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        // Mirror Node `CellRepository.updateIcStatusAndLiftSpot`: refresh status edit time for UI;
        // clear timestamp when status is reset to `0`.
        $icStatus = (string) $validated['ic_status'];
        $first = strtoupper(trim(explode(' ', $icStatus, 2)[0] ?? ''));
        if ($first === '0') {
            $editTime = null;
        } elseif (! empty($validated['appo_edittime'] ?? null)) {
            $editTime = (string) $validated['appo_edittime'];
        } else {
            $editTime = now()->format('Y-m-d H:i:s');
        }
        $update = [
            'ic_status' => $icStatus,
            'appo_edittime' => $editTime,
        ];
        if (array_key_exists('lift_spot', $validated)) {
            $update['lift_spot'] = $validated['lift_spot'];
        }

        DB::table('slots')->where('slot_id', $id)->update($update);

        $this->notifyNodeBroadcast((int) $id);

        return response()->json(['success' => true]);
    }

    /**
     * Atjaunina `slots.ic_planned_tasks` (kā web `PATCH /api/cells/:col/:idx/planned-tasks`).
     * Body: { ic_planned_tasks: string|null }
     */
    public function updatePlannedTasks(Request $request, $id)
    {
        $validated = $request->validate([
            'ic_planned_tasks' => 'nullable|string|max:255',
        ]);

        DB::table('slots')->where('slot_id', $id)->update([
            'ic_planned_tasks' => $validated['ic_planned_tasks'] ?? null,
        ]);

        $this->notifyNodeBroadcast((int) $id);

        return response()->json(['success' => true]);
    }

    /**
     * Partial update of `slots.takenby` JSON (car / client fields used by mobile & web).
     * Only keys present in the request body are applied; empty string or null removes the key.
     */
    public function updateClientData(Request $request, $id)
    {
        $allowed = [
            'car_brand',
            'car_model',
            'lic_plate',
            'temp_nr',
            'user_comment',
            'name',
            'phone_number',
            'email',
            'service',
            'vieta',
            'rimsWith',
            'f_purpose',
            'purpose',
        ];

        $rules = [];
        foreach ($allowed as $key) {
            $rules[$key] = 'sometimes|nullable|string|max:2000';
        }
        $validated = $request->validate($rules);

        if ($validated === []) {
            return response()->json(['message' => 'No fields to update'], 422);
        }

        $row = DB::table('slots')->select('takenby')->where('slot_id', $id)->first();
        if (! $row) {
            return response()->json(['message' => 'Slot not found'], 404);
        }

        $data = [];
        if ($row->takenby !== null && $row->takenby !== '') {
            $decoded = json_decode((string) $row->takenby, true);
            $data = is_array($decoded) ? $decoded : [];
        }

        foreach ($validated as $key => $value) {
            if ($value === null || $value === '') {
                unset($data[$key]);
            } else {
                $data[$key] = $value;
            }
        }

        DB::table('slots')->where('slot_id', $id)->update([
            'takenby' => json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);

        $this->notifyNodeBroadcast((int) $id);

        return response()->json(['success' => true]);
    }

    public function services()
    {
        return response()->json(DB::table('services')->get());
    }

    /**
     * Best-effort uzziņo Node backend par izmaiņām, lai Socket.IO klienti (web + mobile)
     * saņem `table_updated`. Izsaucas pēc slota atjaunināšanas.
     */
    private function notifyNodeBroadcast(int $slotId): void
    {
        $notifyUrl = (string) config('services.appointment.notify_url', '');
        $secret = (string) config('services.appointment.socket_secret', '');
        if ($notifyUrl === '' || $secret === '') {
            return;
        }

        $row = DB::table('slots')
            ->select('slot_id', 'date', 'queue_id', 'iorder')
            ->where('slot_id', $slotId)
            ->first();
        if (! $row || ! $row->date || $row->queue_id === null || $row->iorder === null) {
            return;
        }

        $payload = json_encode([
            'date' => (string) $row->date,
            'columnId' => (int) $row->queue_id,
            'slotIndex' => (int) $row->iorder,
            'secret' => $secret,
        ]);
        if ($payload === false) {
            return;
        }

        $url = rtrim($notifyUrl, '/') . '/api/notify-record';

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 2,
                'ignore_errors' => true,
            ],
        ]);

        @file_get_contents($url, false, $ctx);
    }
}
