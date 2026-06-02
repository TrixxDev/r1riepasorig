<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Office;
use App\Models\Workingday;
use App\Models\Slot;
use Illuminate\Support\Collection;

class SlotService
{
    protected $daysOfWeek = [
        'Monday' => 'Pirmdiena',
        'Tuesday' => 'Otrdiena',
        'Wednesday' => 'Trešdiena',
        'Thursday' => 'Ceturtdiena',
        'Friday' => 'Piektdiena',
        'Saturday' => 'Sestdiena',
        'Sunday' => 'Svētdiena'
    ];

    public function getDaysToShow($visibleDays = 7): Collection
    {
        $days = collect();
        $currentDate = Carbon::now()->startOfDay();

        // Skip to next day if current time is past cutoff
        if ($currentDate->format('H') >= 17) {
            $currentDate->addDay();
        }

        for ($i = 0; $i < $visibleDays; $i++) {
            $date = $currentDate->copy()->addDays($i);
            $days->push([
                'date' => $date->format('d.m.Y'),
                'day_name' => $this->daysOfWeek[$date->format('l')],
                'date_db' => $date->format('Y-m-d'),
                'slots' => $this->getSlotsForDate($date)
            ]);
        }

        return $days;
    }

    protected function getSlotsForDate($date): Collection
    {
        // Получаем все офисы
        $offices = Office::all();
        $collection = collect();

        foreach ($offices as $office) {
            // Для каждого офиса создаем нужное количество очередей
            for ($queueId = 1; $queueId <= $office->queue_count; $queueId++) {
                // Получаем настройки очереди
                $queue = \App\Models\Queue::where('queue_id', $queueId)->first();
                if (!$queue) continue;

                // Определяем время работы (обычное или для выходных)
                $isWeekend = Carbon::parse($date)->isWeekend();
                $timeOpen = $isWeekend ? $queue->wtimeopen : $queue->timeopen;
                $timeClose = $isWeekend ? $queue->wtimeclose : $queue->timeclose;

                $workingDay = Workingday::firstOrCreate(
                    [
                        'date' => $date->format('Y-m-d'),
                        'queue_id' => $queueId,
                        'office_id' => $office->office_id
                    ],
                    [
                        'weekday' => $date->format('N'),
                        'timeopen' => $timeOpen,
                        'timeclose' => $timeClose,
                        'timeStep' => 15,
                        'is_opened' => $queue->is_visible,
                        'is_half' => null,
                        'ac_toggle' => null,
                        'moto_toggle' => null,
                        'title' => $queue->title,
                        'iorder' => $queue->iorder
                    ]
                );

                // Если воскресенье, закрываем очередь
                if ($workingDay->weekday == 7) {
                    $workingDay->is_opened = 0;
                    $workingDay->save();
                }

                if (!$workingDay->slots()->exists()) {
                    $this->generateDefaultSlots($workingDay);
                }

                $slots = $workingDay->slots()
                    ->orderBy('iorder')
                    ->get()
                    ->map(function ($slot) use ($queue, $workingDay) {
                        // Calculate the time for this slot based on opentime and iorder
                        $startTime = Carbon::createFromTimeString($workingDay->timeopen);
                        $slotTime = $startTime->copy()->addMinutes($slot->iorder * $workingDay->timeStep ?? 15)->format('H:i');
                        
                        // Parse the takenby JSON data if it exists
                        $takenInfo = null;
                        if ($slot->takenby) {
                            $takenInfo = json_decode($slot->takenby, true);
                            // Transform the data to match the expected format in the view
                            // for compatibility with the second picture's display format
                            if (!isset($takenInfo['vehicleMake']) && isset($takenInfo['vehicleModel'])) {
                                $takenInfo['vehicleMake'] = $takenInfo['vehicleModel'];
                            }
                            
                            if (!isset($takenInfo['vehicleMake']) && isset($takenInfo['car_brand'])) {
                                $takenInfo['vehicleMake'] = $takenInfo['car_brand'];
                            }
                            
                            if (!isset($takenInfo['plate']) && isset($takenInfo['lic_plate'])) {
                                $takenInfo['plate'] = $takenInfo['lic_plate'];
                            }
                        }
                        
                        return [
                            'iorder' => $slot->iorder,
                            'time' => $slotTime,
                            'is_available' => !$slot->takenby,
                            'taken_info' => $takenInfo,
                            'status' => $slot->status,
                            'queue_id' => $slot->queue_id,
                            'title' => $queue->title
                        ];
                    });

                $collection = $collection->merge($slots);
            }
        }

        return $collection;
    }

    protected function generateDefaultSlots(Workingday $workingDay)
    {
        $startTime = Carbon::createFromTimeString($workingDay->timeopen);
        $endTime = Carbon::createFromTimeString($workingDay->timeclose);
        $interval = $workingDay->timeStep; // minutes
        $iorder = 0;

        while ($startTime < $endTime) {
            Slot::create([
                'date' => $workingDay->date,
                'queue_id' => $workingDay->queue_id,
                'iorder' => $iorder++,
                'status' => 1,
                'newIorder' => null,
                'takenby' => null,
                'createtime' => Carbon::now(),
                'createuser' => -1,
                'edittime' => Carbon::now(),
                'edituser' => -1,
                'is_mobile' => 0
            ]);

            $startTime->addMinutes($interval);
        }
    }

    public function getOffices(): Collection
    {
        return Office::orderBy('id')->get();
    }

    public function isSlotAvailable(Slot $slot): bool
    {
        return !$slot->takenby;
    }

    public function getSlotInfo($date, $iorder, $officeId)
    {
        $workingDay = Workingday::where('date', $date)->first();
        if (!$workingDay) return null;

        $slot = Slot::where('date', $workingDay->date)
            ->where('queue_id', $workingDay->queue_id)
            ->where('iorder', $iorder)
            ->first();

        if (!$slot || !$slot->takenby) return null;

        return json_decode($slot->takenby, true);
    }
} 