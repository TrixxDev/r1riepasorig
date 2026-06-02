<?php

namespace Tests\Unit;

use App\Services\SlotWallTimeReorderService;
use PHPUnit\Framework\TestCase;

class SlotWallTimeReorderServiceTest extends TestCase
{
    public function test_compute_new_iorder_preserves_wall_time_when_open_shifts_later(): void
    {
        // Old grid: 08:00 open, step 15, iorder 4 -> 09:00
        // New open 08:30, step 15 -> same 09:00 is iorder 2
        $io = SlotWallTimeReorderService::computeNewIorder(
            '2026-03-30',
            '08:00',
            15,
            4,
            '08:30',
            15
        );
        $this->assertSame(2, $io);
    }

    public function test_compute_new_iorder_signed_when_open_moves_earlier(): void
    {
        // Slot at 09:00 (iorder 4 from 08:00); new open 09:00 -> iorder 0
        $io = SlotWallTimeReorderService::computeNewIorder(
            '2026-03-30',
            '08:00',
            15,
            4,
            '09:00',
            15
        );
        $this->assertSame(0, $io);
    }

    public function test_compute_new_iorder_changes_with_step_only(): void
    {
        // 08:00 + 2*15 = 08:30. New open 08:00 step 30 -> iorder 1 for same wall time
        $io = SlotWallTimeReorderService::computeNewIorder(
            '2026-03-30',
            '08:00',
            15,
            2,
            '08:00',
            30
        );
        $this->assertSame(1, $io);
    }
}
