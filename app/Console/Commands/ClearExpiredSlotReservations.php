<?php

namespace App\Console\Commands;

use App\Services\SlotReservationService;
use Illuminate\Console\Command;

class ClearExpiredSlotReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slots:clear-expired-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear expired temporary slot reservations';

    /**
     * Execute the console command.
     */
    public function handle(SlotReservationService $service): int
    {
        $count = $service->clearExpiredReservations();
        $this->info("Cleared {$count} expired slot reservations.");

        return self::SUCCESS;
    }
}

