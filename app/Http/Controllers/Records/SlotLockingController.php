<?php

namespace App\Http\Controllers\Records;

use App\Http\Controllers\Controller;
use App\Services\SlotReservationService;
use Illuminate\Http\Request;

class SlotLockingController extends Controller
{
    protected $reservationService;
    
    public function __construct(SlotReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }
    
    /**
     * Reserve a slot (called when user clicks a slot).
     */
    public function reserve(Request $request)
    {
        $request->validate([
            'queue_id' => 'required|integer',
            'date' => 'required|date',
            'iorder' => 'required|integer'
        ]);
        
        $result = $this->reservationService->reserveSlot(
            $request->queue_id,
            $request->date,
            $request->iorder,
            session()->getId()
        );
        
        return response()->json($result);
    }
    
    /**
     * Extend reservation (server enforces limits).
     */
    public function extend(Request $request)
    {
        $request->validate([
            'slot_id' => 'required|integer'
        ]);
        
        $result = $this->reservationService->extendReservation(
            $request->slot_id,
            session()->getId()
        );
        
        return response()->json($result);
    }
    
    /**
     * Cancel reservation.
     */
    public function cancel(Request $request)
    {
        $request->validate([
            'slot_id' => 'required|integer'
        ]);
        
        $success = $this->reservationService->cancelReservation(
            $request->slot_id,
            session()->getId()
        );
        
        return response()->json([
            'success' => $success
        ]);
    }
    
    /**
     * Check slot availability.
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'queue_id' => 'required|integer',
            'date' => 'required|date',
            'iorder' => 'required|integer'
        ]);
        
        $result = $this->reservationService->checkSlotAvailability(
            $request->queue_id,
            $request->date,
            $request->iorder,
            session()->getId()
        );
        
        return response()->json($result);
    }
}
