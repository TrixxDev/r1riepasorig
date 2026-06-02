/**
 * Simple slot locking
 * No WebSocket, only AJAX checks
 */

class SimpleSlotLock {
    constructor() {
        this.currentReservation = null;
        this.reservationTimer = null;
        this.isAdminPage = false;
        this.isProcessing = false; // Flag that a click is being processed
        this.extensionCount = 0; // Extension counter (max 2)
        this.maxExtensions = 2;  // Max number of extensions
        this.warningShown = false; // Flag that warning was already shown
        this.init();
    }
    
    init() {
        // Check if this is admin reservations page or client page
        this.isAdminPage = window.location.pathname.includes('/rezervacijas') || 
                          window.location.pathname.includes('/admin/');
        
        // Do not intercept clicks on admin pages (handled elsewhere)
        if (this.isAdminPage) {
            console.log('Simple Slot Lock: admin page detected — locking disabled');
            return;
        }
        
        // Click handler for free slots (clients only)
        document.addEventListener('click', async (e) => {
            const mobileSlot = e.target.closest('.reservation .time-list .time-slot .available.active.slot');
            if (mobileSlot && !this.isProcessing) {
                this.isProcessing = true;
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                await this.handleMobileSlotClick(mobileSlot);
                this.isProcessing = false;
                return false;
            }

            // For clients: clicks on free slots (button or container)
            const clickableSelector = '.free-slot-link, .available-slot, button.status.slot-free, button.status.slot-offer';
            const timeStatus = e.target.closest('.time-status');
            const slotButton = e.target.closest(clickableSelector) || (timeStatus ? timeStatus.querySelector(clickableSelector) : null);
            const isFreeSlot = timeStatus && (timeStatus.classList.contains('time-free') || timeStatus.classList.contains('time-offer'));
            
            if (slotButton && isFreeSlot && !slotButton.disabled && !this.isProcessing) {
                // Mark click as in progress
                this.isProcessing = true;
                
                // Stop the event
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                // Handle the click
                await this.handleSlotClick(slotButton);
                
                this.isProcessing = false;
                return false;
            }
        }, true); // capture: true - intercept before client.js
        
        // Cancel on page close
        window.addEventListener('beforeunload', () => {
            if (this.currentReservation) {
                this.cancelReservationOnUnload();
            }
        });
        
    }
    
    /**
     * Handle slot click
     */
    async handleSlotClick(slotButton) {
        // Get slot data
        const timeStatus = slotButton.closest('.time-status');
        const table = slotButton.closest('.table');
        const dateContainer = table.closest('[data-date]');
        
        if (!timeStatus || !table || !dateContainer) {
            console.error('Neizdevās atrast slota datus');
            return;
        }
        
        const queueId = table.dataset.queueId;
        const date = dateContainer.dataset.date;
        const iorder = timeStatus.dataset.iorder;
        const timeSlot = timeStatus.querySelector('.time-slot');
        const time = timeSlot ? timeSlot.textContent.trim() : '';
        
        console.log('Klikšķis uz slotu:', { queueId, date, iorder, time });
        
        // Save original button state
        const originalText = slotButton.innerHTML;
        const originalBg = slotButton.style.backgroundColor;
        const originalColor = slotButton.style.color;
        const originalDisabled = slotButton.disabled;
        
        // Show loading state
        slotButton.disabled = true;
        slotButton.innerHTML = 'Pārbaude...';
        
        try {
            // Check and reserve slot
            const response = await fetch('/pieraksts/reserve-slot', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    queue_id: queueId,
                    date: date,
                    iorder: iorder
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Slot successfully reserved
                this.currentReservation = {
                    queueId,
                    date,
                    iorder,
                    time,
                    slotId: result.slot.slot_id,
                    version: result.slot.version,
                    expiresAt: new Date(result.reserved_until),
                    // Save for restoration
                    slotButton: slotButton,
                    originalText: originalText,
                    originalBg: originalBg,
                    originalColor: originalColor
                };
                
                // Mark slot as reserved
                slotButton.innerHTML = 'Jūsu rezervācija';
                slotButton.style.backgroundColor = '#3b82f6';
                slotButton.style.color = 'white';
                
                // Open booking form
                this.openBookingForm();
                
                // Start timer
                this.startReservationTimer();
                
            } else {
                // Slot is taken or error - restore button
                slotButton.innerHTML = originalText;
                slotButton.style.backgroundColor = originalBg;
                slotButton.style.color = originalColor;
                slotButton.disabled = originalDisabled;
                
                this.showSlotTakenModal(result.message, time);
            }
            
        } catch (error) {
            console.error('Rezervācijas kļūda:', error);
            // Restore button on error
            slotButton.innerHTML = originalText;
            slotButton.style.backgroundColor = originalBg;
            slotButton.style.color = originalColor;
            slotButton.disabled = originalDisabled;
            
            this.showError('Savienojuma kļūda ar serveri. Mēģiniet vēlreiz.');
        }
    }

    /**
     * Handle slot click on mobile
     */
    async handleMobileSlotClick(slotButton) {
        const timeSlot = slotButton.closest('.time-slot');
        const timeList = slotButton.closest('.time-list');

        if (!timeSlot || !timeList) {
            console.error('Neizdevās atrast slota datus (mobile)');
            return;
        }

        const queueId = timeSlot.dataset.queueId;
        const iorder = timeSlot.dataset.iorder;
        const date = timeList.dataset.date;
        const timeSpan = timeSlot.querySelector('.time-span');
        const time = timeSpan ? timeSpan.textContent.trim() : '';

        const originalHtml = slotButton.innerHTML;
        const originalBg = slotButton.style.backgroundColor;
        const originalColor = slotButton.style.color;

        slotButton.style.pointerEvents = 'none';
        const slotTextEl = slotButton.querySelector('.slot-text');
        if (slotTextEl) {
            slotTextEl.textContent = 'Pārbaude...';
        } else {
            slotButton.innerHTML = 'Pārbaude...';
        }

        try {
            const response = await fetch('/pieraksts/reserve-slot', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    queue_id: queueId,
                    date: date,
                    iorder: iorder
                })
            });

            const result = await response.json();

            if (result.success) {
                this.currentReservation = {
                    queueId,
                    date,
                    iorder,
                    time,
                    slotId: result.slot.slot_id,
                    version: result.slot.version,
                    expiresAt: new Date(result.reserved_until),
                    slotButton: slotButton,
                    originalText: originalHtml,
                    originalBg: originalBg,
                    originalColor: originalColor
                };

                if (slotTextEl) {
                    slotTextEl.textContent = 'Rezervēts';
                } else {
                    slotButton.innerHTML = 'Rezervēts';
                }
                slotButton.style.backgroundColor = '#3b82f6';
                slotButton.style.color = 'white';

                this.openMobileBookingForm(slotButton);
                this.startReservationTimer();
            } else {
                slotButton.innerHTML = originalHtml;
                slotButton.style.backgroundColor = originalBg;
                slotButton.style.color = originalColor;
                slotButton.style.pointerEvents = '';

                this.showSlotTakenModal(result.message, time);
            }
        } catch (error) {
            console.error('Rezervācijas kļūda (mobile):', error);
            slotButton.innerHTML = originalHtml;
            slotButton.style.backgroundColor = originalBg;
            slotButton.style.color = originalColor;
            slotButton.style.pointerEvents = '';

            this.showError('Savienojuma kļūda ar serveri. Mēģiniet vēlreiz.');
        }
    }
    
    /**
     * Open booking form
     */
    openBookingForm() {
        const res = this.currentReservation;
        const slotButton = res.slotButton;
        const timeStatus = slotButton.closest('.time-status');
        
        // Programmatically click .time-status so client.js sets its state
        // and opens the modal with full logic
        if (typeof $ !== 'undefined') {
            // Temporarily disable our handler to avoid re-capture
            this.isProcessing = true;
            
            // Trigger click on time-status - client.js will handle it
            $(timeStatus).trigger('click');
            
            // Restore handler
            setTimeout(() => {
                this.isProcessing = false;
            }, 100);
        } else if (timeStatus) {
            timeStatus.dispatchEvent(new MouseEvent('click', { bubbles: true }));
        }
    }

    /**
     * Open booking form for mobile
     */
    openMobileBookingForm(slotButton) {
        if (typeof $ !== 'undefined') {
            this.isProcessing = true;
            $(slotButton).trigger('click');
            setTimeout(() => {
                this.isProcessing = false;
            }, 100);
        } else if (slotButton) {
            slotButton.dispatchEvent(new MouseEvent('click', { bubbles: true }));
        }
    }
    
    /**
     * Close booking form
     */
    closeBookingForm() {
        const modal = document.getElementById('reservation');
        if (modal && typeof $ !== 'undefined' && $(modal).modal) {
            $(modal).modal('hide');
        }
    }
    
    /**
     * Reservation timer (by server reserved_until)
     */
    startReservationTimer() {
        this.warningShown = false;
        
        // Update every second
        this.reservationTimer = setInterval(() => {
            if (!this.currentReservation) {
                this.stopReservationTimer();
                return;
            }
            
            const expiresAt = this.currentReservation.expiresAt instanceof Date
                ? this.currentReservation.expiresAt.getTime()
                : null;

            if (!expiresAt || Number.isNaN(expiresAt)) {
                // No server expiry info — safest fallback is to force end.
                this.handleReservationExpired();
                return;
            }

            const msLeft = expiresAt - Date.now();
            const secondsLeft = Math.max(0, Math.ceil(msLeft / 1000));
            
            // Show warning 60 seconds before expiry
            if (msLeft <= 60000 && msLeft > 0 && !this.warningShown) {
                this.warningShown = true;
                this.showTimeWarningModal(secondsLeft);
            }
            
            // Update timer in warning modal
            if (this.warningShown && msLeft > 0) {
                this.updateWarningTimer(secondsLeft);
            }
            
            // Expired
            if (msLeft <= 0) {
                this.handleReservationExpired();
            }
            
        }, 1000);
    }
    
    /**
     * Stop timer
     */
    stopReservationTimer() {
        if (this.reservationTimer) {
            clearInterval(this.reservationTimer);
            this.reservationTimer = null;
        }
        
        this.hideReservationTimer();
    }

    /**
     * Call after /pieraksts/fillSlot succeeds. Server clears the soft lock; the client must
     * stop the reservation timer — otherwise it still fires and shows "rezervācijas laiks ir beidzies"
     * on top of the success message while the modal stays open.
     * Does not restore the slot button (client.js already marks the slot as taken).
     */
    onFillSlotSuccess() {
        const warning = document.getElementById('time-warning-modal');
        if (warning) warning.remove();
        const expired = document.getElementById('expired-modal');
        if (expired) expired.remove();
        if (this.reservationTimer) {
            clearInterval(this.reservationTimer);
            this.reservationTimer = null;
        }
        this.currentReservation = null;
        this.extensionCount = 0;
        this.warningShown = false;
    }
    
    /**
     * Hide timer
     */
    hideReservationTimer() {
        const warningModal = document.getElementById('time-warning-modal');
        if (warningModal) warningModal.remove();
    }
    
    /**
     * Show time warning modal with live timer
     */
    showTimeWarningModal(secondsLeft) {
        // Remove previous warning if present
        const existing = document.getElementById('time-warning-modal');
        if (existing) existing.remove();
        
        const modal = document.createElement('div');
        modal.id = 'time-warning-modal';
        modal.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            z-index: 10002;
            min-width: 400px;
            max-width: 500px;
            overflow: hidden;
        `;
        
        
        modal.innerHTML = `
            <div style="padding: 40px 30px; text-align: center;">
                <div style="font-size: 18px; color: #4a5568; margin-bottom: 20px; line-height: 1.6;">
                    Jūsu pieraksta sesijas laiks beigsies pēc <strong id="warning-timer-seconds">${secondsLeft}</strong> sekundēm!<br>
                    Vai vēlaties turpināt pierakstu?
                </div>
                <div style="display: flex; gap: 15px; justify-content: center;">
                    <button onclick="window.simpleSlotLock.closeTimeWarning()" style="
                        background: #e5e7eb;
                        color: #4a5568;
                        border: none;
                        padding: 12px 30px;
                        border-radius: 8px;
                        font-size: 16px;
                        cursor: pointer;
                        font-weight: 500;
                        min-width: 120px;
                    ">Iziet</button>
                    <button onclick="window.simpleSlotLock.extendReservation()" style="
                        background: #ec4899;
                        color: white;
                        border: none;
                        padding: 12px 30px;
                        border-radius: 8px;
                        font-size: 16px;
                        cursor: pointer;
                        font-weight: 500;
                        min-width: 120px;
                    ">Turpināt</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Vibrate on mobile
        if (navigator.vibrate) {
            navigator.vibrate([200, 100, 200]);
        }
    }
    
    /**
     * Update warning modal timer
     */
    updateWarningTimer(seconds) {
        const timerEl = document.getElementById('warning-timer-seconds');
        if (timerEl) {
            timerEl.textContent = seconds;
        }
    }
    
    /**
     * Close warning
     */
    closeTimeWarning() {
        const modal = document.getElementById('time-warning-modal');
        if (modal) modal.remove();
        
        // Cancel reservation
        this.cancelReservation();
        this.closeBookingForm();
    }
    
    /**
     * Extend reservation (reset inactivity timer)
     */
    async extendReservation() {
        const modal = document.getElementById('time-warning-modal');
        if (modal) modal.remove();
        
        if (!this.currentReservation) return;

        this.warningShown = false;
        
        // Extend on server (server enforces limits)
        try {
            const response = await fetch('/pieraksts/extend-reservation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    slot_id: this.currentReservation.slotId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Sync counter with server
                this.extensionCount = this.maxExtensions - result.extensions_left;
                if (result.reserved_until) {
                    this.currentReservation.expiresAt = new Date(result.reserved_until);
                }
                this.showNotification(result.message, result.extensions_left > 0 ? 'success' : 'warning');
            } else {
                // Server rejected - likely limit reached
                if (result.extensions_exhausted) {
                    // Client tried to bypass - force end
                    this.extensionCount = this.maxExtensions;
                    this.showError(result.message);
                    this.handleReservationExpired();
                } else {
                    this.showError(result.message || 'Neizdevās atjaunot sesiju');
                    this.handleReservationExpired();
                }
            }
        } catch (error) {
            console.error('Extend error:', error);
            this.showError('Savienojuma kļūda');
        }
    }
    
    /**
     * Handle reservation expiration
     */
    handleReservationExpired() {
        // Show expiration modal (as designed)
        this.showExpiredModal();
        this.cancelReservation();
        this.clearReservation();
    }
    
    /**
     * Show expiration modal (as designed)
     */
    showExpiredModal() {
        // Remove warning if present
        const warning = document.getElementById('time-warning-modal');
        if (warning) warning.remove();
        
        const modal = document.createElement('div');
        modal.id = 'expired-modal';
        modal.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            z-index: 10002;
            min-width: 400px;
            max-width: 500px;
            overflow: hidden;
        `;
        
        modal.innerHTML = `
            <div style="padding: 40px 30px; text-align: center;">
                <div style="font-size: 18px; color: #4a5568; margin-bottom: 30px; line-height: 1.6;">
                    Jūsu rezervācijas laiks ir beidzies.<br>
                    Lūdzu, izvēlieties laiku vēlreiz.
                </div>
                <button onclick="window.simpleSlotLock.closeExpiredModal()" style="
                    background: #ec4899;
                    color: white;
                    border: none;
                    padding: 12px 40px;
                    border-radius: 8px;
                    font-size: 16px;
                    cursor: pointer;
                    font-weight: 500;
                    min-width: 150px;
                ">Labi</button>
            </div>
        `;
        
        document.body.appendChild(modal);
    }
    
    /**
     * Close expiration modal
     */
    closeExpiredModal() {
        const modal = document.getElementById('expired-modal');
        if (modal) modal.remove();
        
        this.closeBookingForm();
        this.resetMobileSelection();
        
        // Reload page
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }

    /**
     * Reset branch selection and mobile form
     */
    resetMobileSelection() {
        const mobileMain = document.getElementById('mobile-main');
        if (!mobileMain) return;

        const filialeInputs = mobileMain.querySelectorAll('#mobile-filiale input.filiale_radio');
        filialeInputs.forEach((input) => {
            input.checked = false;
        });

        const slotsChoice = mobileMain.querySelector('#mobile-slots-choice .reservation');
        if (slotsChoice) {
            slotsChoice.innerHTML = '';
        }

        const slotsContainer = mobileMain.querySelector('#mobile-slots-choice');
        if (slotsContainer) {
            slotsContainer.style.display = 'none';
        }

        const form = mobileMain.querySelector('#mobile-reservation-form');
        if (form) {
            form.style.display = 'none';
        }

        const hiddenFields = mobileMain.querySelectorAll('input[type="hidden"]');
        hiddenFields.forEach((input) => {
            input.value = '';
        });
    }
    
    /**
     * Cancel reservation
     */
    async cancelReservation() {
        if (!this.currentReservation) return;
        
        try {
            await fetch('/pieraksts/cancel-reservation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    slot_id: this.currentReservation.slotId
                }),
                keepalive: true
            });
        } catch (error) {
            console.error('Cancel reservation error:', error);
        }
        
        this.clearReservation();
    }

    /**
     * Best-effort cancel for page unload (sendBeacon).
     */
    cancelReservationOnUnload() {
        if (!this.currentReservation) return;
        const tokenEl = document.querySelector('meta[name="csrf-token"]');
        const token = tokenEl ? tokenEl.content : '';

        if (navigator.sendBeacon && token) {
            const params = new URLSearchParams();
            params.set('_token', token);
            params.set('slot_id', String(this.currentReservation.slotId));
            navigator.sendBeacon('/pieraksts/cancel-reservation', params);
        } else {
            // Fallback: keepalive fetch (may still be dropped by browser)
            try {
                fetch('/pieraksts/cancel-reservation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        ...(token ? { 'X-CSRF-TOKEN': token } : {})
                    },
                    body: JSON.stringify({ slot_id: this.currentReservation.slotId }),
                    keepalive: true
                });
            } catch (e) {
                // ignore
            }
        }
    }
    
    /**
     * Clear reservation
     */
    clearReservation() {
        // Restore original button appearance
        if (this.currentReservation && this.currentReservation.slotButton) {
            const btn = this.currentReservation.slotButton;
            btn.innerHTML = this.currentReservation.originalText;
            btn.style.backgroundColor = this.currentReservation.originalBg || '';
            btn.style.color = this.currentReservation.originalColor || '';
            btn.disabled = false;
            btn.style.pointerEvents = '';
        }
        
        this.currentReservation = null;
        this.extensionCount = 0; // Reset extension counter
        this.lastActivityTime = null;
        this.warningShown = false;
        this.stopReservationTimer();
    }
    
    /**
     * Show "Slot taken" modal
     */
    showSlotTakenModal(message, time) {
        const existing = document.getElementById('slot-taken-modal');
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = 'slot-taken-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10001;
        `;
        
        let messageHtml = message || '';
        messageHtml = messageHtml
            .replace(/Mēģiniet[^.!?]*[.!?]?/gi, '')
            .replace(/sekund\w*/gi, '')
            .replace(/\s{2,}/g, ' ')
            .trim();
        if (!messageHtml) {
            messageHtml = 'Laiku pašlaik rezervē cits lietotājs.';
        }

        modal.innerHTML = `
            <div style="background: white; padding: 30px; border-radius: 15px; max-width: 500px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
                <div style="font-size: 60px; margin-bottom: 20px;"></div>
                <h2 style="font-size: 24px; margin-bottom: 15px; color: #ef4444;">Laiks ir aizņemts</h2>
                <p style="font-size: 18px; margin-bottom: 10px;">Laiku <strong>${time}</strong> jau rezervē cits lietotājs.</p>
                <p style="font-size: 16px; color: #6b7280; margin-bottom: 25px;">${messageHtml}</p>
                <button id="slot-taken-close" style="background: #3b82f6; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-size: 16px; cursor: pointer; font-weight: 500;">
                    Izvēlēties citu laiku
                </button>
            </div>
        `;
        
        document.body.appendChild(modal);

        const closeModal = () => {
            modal.remove();
        };

        const closeButton = modal.querySelector('#slot-taken-close');
        if (closeButton) {
            closeButton.addEventListener('click', closeModal);
        }
        
        // Close on click outside the modal
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
    }
    
    /**
     * Show validation errors
     */
    showErrors(errors) {
        let errorHtml = '<ul style="text-align: left; color: #ef4444;">';
        for (const field in errors) {
            errorHtml += errors[field];
        }
        errorHtml += '</ul>';
        
        this.showNotification(errorHtml, 'error');
    }
    
    /**
     * Notifications
     */
    showNotification(message, type = 'info') {
        const containerId = 'simple-slot-lock-toasts';
        let container = document.getElementById(containerId);
        if (!container) {
            container = document.createElement('div');
            container.id = containerId;
            container.style.cssText = `
                position: fixed;
                right: 20px;
                bottom: 20px;
                z-index: 10003;
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-width: 420px;
            `;
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const colors = {
            info: { bg: '#111827', fg: '#ffffff' },
            success: { bg: '#059669', fg: '#ffffff' },
            warning: { bg: '#d97706', fg: '#ffffff' },
            error: { bg: '#dc2626', fg: '#ffffff' }
        };
        const c = colors[type] || colors.info;

        toast.style.cssText = `
            background: ${c.bg};
            color: ${c.fg};
            padding: 12px 14px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            font-size: 14px;
            line-height: 1.4;
            word-break: break-word;
        `;

        // Allow HTML (server messages may contain small formatting)
        toast.innerHTML = message;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 250ms ease';
            setTimeout(() => toast.remove(), 300);
        }, 4500);
    }
    
    showError(message) {
        this.showNotification(message, 'error');
    }
    
    showSuccess(message) {
        this.showNotification(message, 'success');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.simpleSlotLock = new SimpleSlotLock();
    console.log('Simple Slot Lock inicializēts');
    
    // Track Bootstrap modal close for #reservation
    if (typeof $ !== 'undefined') {
        $('#reservation').on('hidden.bs.modal', function() {
            if (window.simpleSlotLock && window.simpleSlotLock.currentReservation) {
                console.log('Modālais logs aizvērts - atceļam rezervāciju');
                window.simpleSlotLock.cancelReservation();
            }
        });
    }
});

// Styles for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
