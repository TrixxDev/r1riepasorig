<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slot Lock Demo - Modālo logu demonstrācija</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; padding: 40px; }
        h1 { text-align: center; margin-bottom: 30px; color: #1f2937; }
        .demo-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; max-width: 1200px; margin: 0 auto; }
        .demo-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .demo-card h3 { margin-bottom: 15px; color: #374151; font-size: 16px; }
        .demo-card button { width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 14px; cursor: pointer; margin-bottom: 10px; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-success { background: #10b981; color: white; }
        .btn-pink { background: #ec4899; color: white; }
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-width: 500px; width: 90%; overflow: hidden; }
        .modal-body { padding: 40px 30px; text-align: center; }
        .modal-body p { font-size: 18px; color: #4a5568; margin-bottom: 30px; line-height: 1.6; }
        .modal-buttons { display: flex; gap: 15px; justify-content: center; }
        .modal-buttons button { min-width: 120px; padding: 12px 30px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; font-weight: 500; }
        .btn-gray { background: #e5e7eb; color: #4a5568; }
        .emoji { font-size: 50px; margin-bottom: 20px; }
        .timer { font-weight: bold; color: #ef4444; }
    </style>
</head>
<body>
    <h1>🔒 Slot Lock - Modālo logu demonstrācija</h1>
    
    <div class="demo-grid">
        <!-- Карточка 1: Предупреждение о времени -->
        <div class="demo-card">
            <h3>1. Brīdinājums par sesijas beigām (30 sek)</h3>
            <button class="btn-warning" onclick="showWarningModal(30)">Rādīt brīdinājumu (30 sek)</button>
            <button class="btn-warning" onclick="showWarningModal(10)">Rādīt brīdinājumu (10 sek)</button>
        </div>
        
        <!-- Карточка 2: Сессия истекла -->
        <div class="demo-card">
            <h3>2. Sesija ir beigusies</h3>
            <button class="btn-danger" onclick="showExpiredModal()">Rādīt "Sesija beigusies"</button>
        </div>
        
        <!-- Карточка 3: Слот занят (для клиента) -->
        <div class="demo-card">
            <h3>3. Laiks ir aizņemts (klientam)</h3>
            <button class="btn-primary" onclick="showSlotTakenModal()">Rādīt "Laiks aizņemts"</button>
        </div>
        
        <!-- Карточка 4: Слот резервируется (для админа) -->
        <div class="demo-card">
            <h3>4. Slots ir rezervēts (adminam)</h3>
            <button class="btn-success" onclick="showAdminReservedModal()">Rādīt ar taimeri</button>
        </div>
    </div>

    <!-- Модальное окно: Предупреждение -->
    <div class="modal-overlay" id="warning-modal">
        <div class="modal-content">
            <div class="modal-body">
                <p>Jūsu sesija beigsies pēc <strong id="warning-time">00:30 sekundēm</strong>.<br>Vai vēlaties turpināt sesiju?</p>
                <div class="modal-buttons">
                    <button class="btn-gray" onclick="closeModal('warning-modal')">Iziet</button>
                    <button class="btn-pink" onclick="closeModal('warning-modal'); showNotification('Rezervācija pagarināta!')">Turpināt</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно: Сессия истекла -->
    <div class="modal-overlay" id="expired-modal">
        <div class="modal-content">
            <div class="modal-body">
                <p>Jūsu rezervācijas laiks ir beidzies.<br>Lūdzu, izvēlieties laiku vēlreiz.</p>
                <div class="modal-buttons">
                    <button class="btn-pink" onclick="closeModal('expired-modal')">Labi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно: Слот занят -->
    <div class="modal-overlay" id="taken-modal">
        <div class="modal-content">
            <div class="modal-body">
                <div class="emoji">😔</div>
                <h2 style="font-size: 24px; margin-bottom: 15px; color: #ef4444;">Laiks ir aizņemts</h2>
                <p style="font-size: 16px;">Laiku <strong>10:30</strong> jau rezervē cits lietotājs.</p>
                <div class="modal-buttons">
                    <button class="btn-primary" onclick="closeModal('taken-modal')">Izvēlēties citu laiku</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно: Для админа с таймером -->
    <div class="modal-overlay" id="admin-modal">
        <div class="modal-content">
            <div class="modal-body">
                <div class="emoji">⏳</div>
                <h2 style="font-size: 22px; margin-bottom: 15px; color: #f59e0b;">Slots ir rezervēts</h2>
                <p style="font-size: 16px; margin-bottom: 10px;">Šo laiku <strong>10:30</strong> pašlaik rezervē cits klients.</p>
                <p style="font-size: 14px; color: #6b7280;">Rezervācija beigsies pēc: <strong class="timer" id="admin-timer">2 min 45 sek</strong></p>
                <div class="modal-buttons" style="margin-top: 20px;">
                    <button class="btn-primary" onclick="closeModal('admin-modal'); clearInterval(adminTimerInterval);">Sapratu</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Уведомление -->
    <div id="notification" style="position: fixed; top: 20px; right: 20px; background: #10b981; color: white; padding: 15px 25px; border-radius: 8px; display: none; z-index: 2000;"></div>

    <script>
        let adminTimerInterval;
        
        function showWarningModal(seconds) {
            document.getElementById('warning-time').textContent = seconds === 30 ? '00:30 sekundēm' : '00:10 sekundēm';
            document.getElementById('warning-modal').style.display = 'flex';
        }
        
        function showExpiredModal() {
            document.getElementById('expired-modal').style.display = 'flex';
        }
        
        function showSlotTakenModal() {
            document.getElementById('taken-modal').style.display = 'flex';
        }
        
        function showAdminReservedModal() {
            let remaining = 165; // 2:45
            document.getElementById('admin-modal').style.display = 'flex';
            
            function updateTimer() {
                let min = Math.floor(remaining / 60);
                let sec = remaining % 60;
                document.getElementById('admin-timer').textContent = min > 0 ? `${min} min ${sec} sek` : `${sec} sek`;
            }
            
            updateTimer();
            adminTimerInterval = setInterval(function() {
                remaining--;
                if (remaining <= 0) {
                    clearInterval(adminTimerInterval);
                    closeModal('admin-modal');
                    showNotification('Slots atbrīvots!');
                    return;
                }
                updateTimer();
            }, 1000);
        }
        
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }
        
        function showNotification(msg) {
            let n = document.getElementById('notification');
            n.textContent = msg;
            n.style.display = 'block';
            setTimeout(() => n.style.display = 'none', 3000);
        }
        
        // Закрытие по клику на overlay
        document.querySelectorAll('.modal-overlay').forEach(m => {
            m.addEventListener('click', e => {
                if (e.target === m) {
                    m.style.display = 'none';
                    if (adminTimerInterval) clearInterval(adminTimerInterval);
                }
            });
        });
    </script>
</body>
</html>
