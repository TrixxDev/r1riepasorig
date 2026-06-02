<?php

namespace App\Services;

use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SlotReservationService
{
    const RESERVATION_TIMEOUT = 5; // minutes
    const MAX_EXTENSIONS = 2; // max 2 extensions
    
    /**
     * Temporary slot reservation (soft lock).
     * 
     * @param int $queueId
     * @param string $date
     * @param int $iorder
     * @param string $reservedBy (session_id or user_id)
     * @return array ['success' => bool, 'slot' => Slot|null, 'message' => string]
     */
    public function reserveSlot($queueId, $date, $iorder, $reservedBy = null)
    {
        $reservedBy = $reservedBy ?? session()->getId();
        $reservedUntil = Carbon::now()->addMinutes(self::RESERVATION_TIMEOUT);
        
        DB::beginTransaction();
        
        try {
            // Cleanup expired reservations
            $this->clearExpiredReservations();
            
            // Fetch slot with row lock
            $slot = Slot::where('queue_id', $queueId)
                ->where('date', $date)
                ->where('iorder', $iorder)
                ->lockForUpdate()
                ->first();
            
            // Create slot if missing
            if (!$slot) {
                $slot = new Slot();
                $slot->timestamps = false;
                $slot->queue_id = $queueId;
                $slot->date = $date;
                $slot->iorder = $iorder;
                $slot->status = 0;
                $slot->version = 0;
            }

            // If the slot is already reserved by the SAME session and still valid,
            // do NOT reset extension_count (prevents bypass by repeatedly calling reserve-slot).
            if (
                $slot->reserved_until &&
                Carbon::parse($slot->reserved_until)->isFuture() &&
                $slot->reserved_by === $reservedBy
            ) {
                DB::commit();

                return [
                    'success' => true,
                    'slot' => $slot,
                    'message' => 'Laiks jau ir rezervēts',
                    'reserved_until' => Carbon::parse($slot->reserved_until)->toIso8601String(),
                ];
            }
            
            // Check availability (already booked)
            if ($slot->status == 1 && !empty($slot->takenby)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'slot' => null,
                    'message' => 'Laiks jau ir aizņemts'
                ];
            }
            
            // Check if reserved by another user
            if ($slot->reserved_until && 
                Carbon::parse($slot->reserved_until)->isFuture() && 
                $slot->reserved_by !== $reservedBy) {
                DB::rollBack();
                return [
                    'success' => false,
                    'slot' => null,
                    'message' => 'Laiku pašlaik rezervē cits lietotājs. Mēģiniet pēc ' . 
                             Carbon::parse($slot->reserved_until)->diffInSeconds() . ' sekundēm.'
                ];
            }

            // Reserve slot
            $slot->reserved_until = $reservedUntil;
            $slot->reserved_by = $reservedBy;
            $slot->extension_count = 0; // reset extension counter
            $slot->version++;
            $slot->save();
            
            DB::commit();
            
            return [
                'success' => true,
                'slot' => $slot,
                'message' => 'Laiks rezervēts uz ' . self::RESERVATION_TIMEOUT . ' minūtēm',
                'reserved_until' => $reservedUntil->toIso8601String()
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'slot' => null,
                'message' => 'Rezervācijas kļūda: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Confirm booking (hard lock).
     * 
     * @param int $slotId
     * @param array $bookingData
     * @param int $expectedVersion
     * @param string $reservedBy
     * @return array
     */
    public function confirmBooking($slotId, $bookingData, $expectedVersion, $reservedBy = null)
    {
        $reservedBy = $reservedBy ?? session()->getId();
        
        DB::beginTransaction();
        
        try {
            // Fetch slot with lock
            $slot = Slot::where('slot_id', $slotId)
                ->lockForUpdate()
                ->first();
            
            if (!$slot) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Slots nav atrasts'
                ];
            }
            
            // Version check (optimistic locking)
            if ($slot->version != $expectedVersion) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Slots tika izmainīts citā sesijā. Lūdzu, atsvaidziniet lapu.'
                ];
            }
            
            // Reservation owner check
            if ($slot->reserved_by !== $reservedBy) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Rezervācijas laiks ir beidzies vai slotu rezervē cits lietotājs'
                ];
            }
            
            // Ensure slot is still free
            if ($slot->status == 1 && !empty($slot->takenby)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Slots jau ir aizņemts'
                ];
            }
            
            // Confirm booking
            $slot->status = 1;
            $slot->takenby = json_encode($bookingData);
            $cid = $bookingData['cancelId'] ?? $bookingData['cancel_id'] ?? null;
            $slot->cancel_id = ($cid !== null && $cid !== '') ? (string) $cid : null;
            $slot->createtime = now();
            $slot->createuser = auth()->id() ?? -1;
            $slot->version++;
            
            // Clear reservation lock fields
            $slot->reserved_until = null;
            $slot->reserved_by = null;
            
            $slot->save();
            
            DB::commit();
            
            return [
                'success' => true,
                'slot' => $slot,
                'message' => 'Pieraksts veiksmīgi izveidots'
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Kļūda veidojot pierakstu: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Extend reservation (server-enforced limit).
     */
    public function extendReservation($slotId, $reservedBy = null)
    {
        $reservedBy = $reservedBy ?? session()->getId();
        
        DB::beginTransaction();
        
        try {
            // Fetch slot with lock
            $slot = Slot::where('slot_id', $slotId)
                ->where('reserved_by', $reservedBy)
                ->where('reserved_until', '>', Carbon::now())
                ->lockForUpdate()
                ->first();
            
            if (!$slot) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Rezervācija nav atrasta vai ir beigusies'
                ];
            }
            
            // Enforce extension limit on server
            $currentExtensions = $slot->extension_count ?? 0;
            
            if ($currentExtensions >= self::MAX_EXTENSIONS) {
                // Limit reached: cancel reservation immediately (free the slot) and force user to pick again.
                $slot->reserved_until = null;
                $slot->reserved_by = null;
                $slot->extension_count = 0;
                $slot->version++;
                $slot->save();

                // If the slot is otherwise empty, remove it to keep DB clean (same logic as cancelReservation).
                if ($slot->status == 0 && empty($slot->takenby) && $slot->comment === null) {
                    $slot->delete();
                }

                DB::commit();

                return [
                    'success' => false,
                    'message' => 'Pagarināšanas iespējas ir izmantotas',
                    'extensions_exhausted' => true,
                    'reservation_cancelled' => true
                ];
            }
            
            // Extend reservation
            $reservedUntil = Carbon::now()->addMinutes(self::RESERVATION_TIMEOUT);
            
            $slot->reserved_until = $reservedUntil;
            $slot->extension_count = $currentExtensions + 1;
            $slot->version++;
            $slot->save();
            
            DB::commit();
            
            $extensionsLeft = self::MAX_EXTENSIONS - $slot->extension_count;
            
            return [
                'success' => true,
                'extensions_left' => $extensionsLeft,
                'reserved_until' => $reservedUntil->toIso8601String(),
                'message' => $extensionsLeft > 0 
                    ? "Sesija atjaunota (vēl {$extensionsLeft} reize" . ($extensionsLeft > 1 ? 's' : '') . ")"
                    : "Sesija atjaunota (pēdējā reize)"
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Kļūda: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Cancel reservation.
     */
    public function cancelReservation($slotId, $reservedBy = null)
    {
        $reservedBy = $reservedBy ?? session()->getId();
        
        $slot = Slot::where('slot_id', $slotId)
            ->where('reserved_by', $reservedBy)
            ->first();
        
        if ($slot) {
            $slot->reserved_until = null;
            $slot->reserved_by = null;
            $slot->extension_count = 0; // reset counter
            $slot->save();

            if ($slot->status == 0 && empty($slot->takenby) && $slot->comment === null) {
                $slot->delete();
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Cleanup expired reservations.
     */
    public function clearExpiredReservations()
    {
        $expiredSlots = Slot::where('reserved_until', '<', Carbon::now())
            ->whereNotNull('reserved_until')
            ->get();

        foreach ($expiredSlots as $slot) {
            $slot->reserved_until = null;
            $slot->reserved_by = null;
            $slot->extension_count = 0;
            $slot->save();

            if ($slot->status == 0 && empty($slot->takenby) && $slot->comment === null) {
                $slot->delete();
            }
        }

        return $expiredSlots->count();
    }
    
    /**
     * Check slot availability.
     */
    public function isSlotAvailable($queueId, $date, $iorder, $reservedBy = null)
    {
        $reservedBy = $reservedBy ?? session()->getId();
        
        $slot = Slot::where('queue_id', $queueId)
            ->where('date', $date)
            ->where('iorder', $iorder)
            ->first();
        
        if (!$slot) {
            return true; // no slot row = available
        }
        
        // Booked
        if ($slot->status == 1 && !empty($slot->takenby)) {
            return false;
        }
        
        // Reserved by someone else
        if ($slot->reserved_until && 
            Carbon::parse($slot->reserved_until)->isFuture() && 
            $slot->reserved_by !== $reservedBy) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Detailed slot availability check (for admins).
     */
    public function checkSlotAvailability($queueId, $date, $iorder, $reservedBy = null)
    {
        $reservedBy = $reservedBy ?? session()->getId();
        
        $slot = Slot::where('queue_id', $queueId)
            ->where('date', $date)
            ->where('iorder', $iorder)
            ->first();
        
        if (!$slot) {
            return [
                'available' => true,
                'reserved_by_other' => false
            ];
        }
        
        // Booked
        if ($slot->status == 1 && !empty($slot->takenby)) {
            return [
                'available' => false,
                'reserved_by_other' => false,
                'reason' => 'booked'
            ];
        }
        
        // Reserved by someone else
        if ($slot->reserved_until && 
            Carbon::parse($slot->reserved_until)->isFuture() && 
            $slot->reserved_by !== $reservedBy) {
            
            $expiresIn = Carbon::now()->diffInSeconds(Carbon::parse($slot->reserved_until));
            
            return [
                'available' => false,
                'reserved_by_other' => true,
                'expires_in' => $expiresIn,
                'reason' => 'reserved'
            ];
        }
        
        return [
            'available' => true,
            'reserved_by_other' => false
        ];
    }
}
