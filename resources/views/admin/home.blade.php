@extends('admin.layouts.app')

@section('content')

    <div class="container-fluid">
        <div class="fade-in">
            <div class="row">
                <div class="col-sm-6 col-lg-3">
                    <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-value-lg font-weight-bold">{{ $todayOrders }}</div>
                                <div class="text-white-50">Pasūtījumi šodien</div>
                            </div>
                            <div class="text-white-50">
                                <i class="cil-cart" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-sm-6 col-lg-3">
                    <div class="card text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border: none; box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-value-lg font-weight-bold">{{ number_format($monthlyRevenue, 0, ',', ' ') }}€</div>
                                <div class="text-white-50">Ieņēmumi mēnesī</div>
                            </div>
                            <div class="text-white-50">
                                <i class="cil-euro" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-sm-6 col-lg-3">
                    <div class="card text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border: none; box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-value-lg font-weight-bold">{{ $lowStockTires }}</div>
                                <div class="text-white-50">Maz krājumu</div>
                            </div>
                            <div class="text-white-50">
                                <i class="cil-warning" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-sm-6 col-lg-3">
                    <div class="card text-white" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border: none; box-shadow: 0 4px 15px rgba(250, 112, 154, 0.3);">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-value-lg font-weight-bold">{{ $activeUsersToday }}</div>
                                <div class="text-white-50">Aktīvi lietotāji</div>
                            </div>
                            <div class="text-white-50">
                                <i class="cil-people" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row-->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="cil-cart text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h3 class="text-primary font-weight-bold">{{ $totalOrders }}</h3>
                            <p class="text-muted mb-2">Kopā pasūtījumi</p>
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: {{ $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0 }}%"></div>
                            </div>
                            <small class="text-muted">{{ $completedOrders }} pabeigti</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="cil-tire text-info" style="font-size: 3rem;"></i>
                            </div>
                            <h3 class="text-info font-weight-bold">{{ $totalTires }}</h3>
                            <p class="text-muted mb-2">Produkti katalogā</p>
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-warning" style="width: {{ $totalTires > 0 ? ($outOfStockTires / $totalTires) * 100 : 0 }}%"></div>
                            </div>
                            <small class="text-muted">{{ $outOfStockTires }} nav krājumā</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="cil-clock text-warning" style="font-size: 3rem;"></i>
                            </div>
                            <h3 class="text-warning font-weight-bold">{{ $todaySlots }}</h3>
                            <p class="text-muted mb-2">Laiki šodien</p>
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-danger" style="width: {{ $todaySlots > 0 ? ($takenSlotsToday / $todaySlots) * 100 : 0 }}%"></div>
                            </div>
                            <small class="text-muted">{{ $takenSlotsToday }} aizņemti</small>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row-->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <h6 class="mb-1 text-dark">Riepu Garāža — partneru noliktava (rg)</h6>
                                <small class="text-muted d-block">Sinhronizē <code>auto_stock</code> (<code>itype = rg</code>) no ecom <code>tyres.xml</code>.</small>
                                <small id="admin-rg-sync-last" class="text-muted d-block mt-1">
                                    @if (!empty($rgSyncLast))
                                        Pēdējoreiz: {{ $rgSyncLast }}
                                    @else
                                        Vēl nav veikta sinhronizācija.
                                    @endif
                                </small>
                            </div>
                            <div class="text-end">
                                <button type="button" id="admin-rg-sync-btn" class="btn btn-primary">Palaist rg sinhronizāciju</button>
                                <div id="admin-rg-sync-err" class="text-danger small mt-2 d-none" role="alert"></div>
                            </div>
                        </div>
                        <pre id="admin-rg-sync-out" class="d-none small mt-2 mb-0 mx-3 mx-md-4 p-2 bg-light border rounded" style="max-height: 240px; overflow: auto; white-space: pre-wrap; word-break: break-word;"></pre>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0 text-primary">
                                <i class="cil-cart mr-2"></i>Pēdējie pasūtījumi
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @if($recentOrders->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="border-0">Nr.</th>
                                                <th class="border-0">Klients</th>
                                                <th class="border-0">Summa</th>
                                                <th class="border-0">Statuss</th>
                                                <th class="border-0">Laiks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentOrders as $order)
                                                <tr>
                                                    <td class="font-weight-bold">{{ $order->order_number ?: $order->id }}</td>
                                                    <td>
                                                        <small>{{ $order->email ?: $order->phone_number ?: 'Bez kontaktiem' }}</small>
                                                    </td>
                                                    <td class="text-success font-weight-bold">{{ number_format($order->total_price, 0, ',', ' ') }}€</td>
                                                    <td>
                                                        <span class="badge badge-{{ $order->order_status == 5 ? 'success' : 'warning' }} badge-pill">
                                                            {{ $order->order_status == 5 ? 'Pabeigts' : 'Apstrādā' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ $order->created_at->format('d.m H:i') }}</small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="cil-cart text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">Nav pasūtījumu</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0 text-info">
                                <i class="cil-list mr-2"></i>Pēdējā aktivitāte
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @if($recentActivity->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="border-0">Lietotājs</th>
                                                <th class="border-0">Darbība</th>
                                                <th class="border-0">Laiks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentActivity as $activity)
                                                <tr>
                                                    <td>
                                                        @if($activity->user)
                                                            <div class="d-flex align-items-center">
                                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 30px; height: 30px;">
                                                                    <span class="text-white small font-weight-bold">{{ substr($activity->user->name, 0, 1) }}</span>
                                                                </div>
                                                                <small class="font-weight-bold">{{ $activity->user->fullName }}</small>
                                                            </div>
                                                        @else
                                                            <div class="d-flex align-items-center">
                                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 30px; height: 30px;">
                                                                    <i class="cil-user text-white small"></i>
                                                                </div>
                                                                <small class="text-muted">Viesis</small>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <small class="text-dark">{{ Str::limit($activity->audit_event, 25) }}</small>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($activity->audit_time)->format('H:i') }}</small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="cil-list text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">Nav aktivitātes</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row-->
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0 text-success">
                                <i class="cil-chart-line mr-2"></i>Pārdošana pēdējās 7 dienās
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" height="80"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row-->
        </div>
    </div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const salesData = @json($salesChart);
    
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesData.map(item => item.date),
            datasets: [{
                label: 'Pasūtījumi',
                data: salesData.map(item => item.orders),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }, {
                label: 'Ieņēmumi (€)',
                data: salesData.map(item => item.revenue),
                borderColor: '#f093fb',
                backgroundColor: 'rgba(240, 147, 251, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#f093fb',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    },
                    ticks: {
                        color: '#667eea'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        color: '#f093fb'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    },
                    ticks: {
                        color: '#666'
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
});
</script>

<script>
// Автообновление dashboard
class DashboardUpdater {
    constructor() {
        this.interval = 30000; // 30 секунд
        this.isActive = true;
        this.lastUpdate = null;
        this.start();
        this.setupVisibilityListener();
    }
    
    start() {
        // Обновляем сразу при загрузке
        setTimeout(() => this.update(), 1000);
        
        // Затем каждые 30 секунд
        setInterval(() => {
            if (this.isActive && !document.hidden) {
                this.update();
            }
        }, this.interval);
    }
    
    setupVisibilityListener() {
        document.addEventListener('visibilitychange', () => {
            this.isActive = !document.hidden;
            if (this.isActive) {
                // Обновляем сразу при возвращении на вкладку
                this.update();
            }
        });
    }
    
    async update() {
        try {
            const response = await fetch('/admin/dashboard/data');
            const data = await response.json();
            
            if (data.error) {
                console.log('Ошибка получения данных:', data.error);
                return;
            }
            
            // Обновляем карточки с анимацией
            this.updateCard('.text-value-lg', data.todayOrders, 0);
            this.updateCard('.text-value-lg', data.monthlyRevenue + '€', 1);
            this.updateCard('.text-value-lg', data.lowStockTires, 2);
            this.updateCard('.text-value-lg', data.activeUsersToday, 3);
            
            // Обновляем средние карточки
            this.updateCard('h3.text-primary', data.totalOrders);
            this.updateCard('h3.text-info', data.totalTires);
            this.updateCard('h3.text-warning', data.todaySlots);
            
            // Обновляем прогресс-бары
            this.updateProgressBar(data.totalOrders, data.completedOrders, 0);
            this.updateProgressBar(data.totalTires, data.outOfStockTires, 1);
            this.updateProgressBar(data.todaySlots, data.takenSlotsToday, 2);
            
            this.showUpdateIndicator();
            this.lastUpdate = new Date();
            
        } catch (error) {
            console.log('Ошибка обновления:', error);
        }
    }
    
    updateCard(selector, value, index = null) {
        const elements = document.querySelectorAll(selector);
        const element = index !== null ? elements[index] : elements[0];
        
        if (element && element.textContent !== value.toString()) {
            // Анимация обновления
            element.style.transition = 'all 0.3s ease';
            element.style.transform = 'scale(1.1)';
            element.style.color = '#28a745';
            
            setTimeout(() => {
                element.textContent = value;
                element.style.transform = 'scale(1)';
                element.style.color = '';
            }, 150);
        }
    }
    
    updateProgressBar(total, completed, index) {
        const progressBars = document.querySelectorAll('.progress-bar');
        if (progressBars[index] && total > 0) {
            const percentage = (completed / total) * 100;
            progressBars[index].style.width = percentage + '%';
        }
    }
    
    showUpdateIndicator() {
        // Удаляем предыдущий индикатор
        const existing = document.querySelector('.update-indicator');
        if (existing) existing.remove();
        
        const indicator = document.createElement('div');
        indicator.className = 'update-indicator';
        indicator.innerHTML = `
            <i class="cil-check"></i> 
            Atjaunots ${new Date().toLocaleTimeString('lv-LV')}
        `;
        indicator.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            z-index: 9999;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            animation: slideIn 0.3s ease;
        `;
        
        document.body.appendChild(indicator);
        
        setTimeout(() => {
            indicator.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => indicator.remove(), 300);
        }, 2000);
    }
}

// CSS для анимаций
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
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
`;
document.head.appendChild(style);

// Запускаем автообновление
document.addEventListener('DOMContentLoaded', () => {
    new DashboardUpdater();
});

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('admin-rg-sync-btn');
    const errEl = document.getElementById('admin-rg-sync-err');
    const outEl = document.getElementById('admin-rg-sync-out');
    const lastEl = document.getElementById('admin-rg-sync-last');
    if (!btn) return;
    btn.addEventListener('click', () => {
        if (errEl) {
            errEl.classList.add('d-none');
            errEl.textContent = '';
        }
        if (outEl) {
            outEl.classList.add('d-none');
            outEl.textContent = '';
        }
        btn.disabled = true;
        const label = btn.textContent;
        btn.textContent = 'Sinhronizējas…';
        fetch(@json(route('admin.sync.rg-auto')), {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/plain, text/html, */*'
            }
        })
            .then((r) => {
                if (!r.ok) {
                    return r.text().then((t) => {
                        throw new Error(t || ('HTTP ' + r.status));
                    });
                }
                return r.text();
            })
            .then((text) => {
                btn.disabled = false;
                btn.textContent = label;
                if (outEl) {
                    outEl.textContent = text.trim() || '(tukša atbilde)';
                    outEl.classList.remove('d-none');
                }
                if (lastEl) {
                    lastEl.textContent = 'Pēdējoreiz: ' + new Date().toLocaleString('lv-LV', { dateStyle: 'short', timeStyle: 'medium' });
                }
            })
            .catch((e) => {
                btn.disabled = false;
                btn.textContent = label;
                if (errEl) {
                    errEl.textContent = (e && e.message) ? String(e.message).slice(0, 800) : 'Nezināma kļūda';
                    errEl.classList.remove('d-none');
                }
            });
    });
});
</script>
@endsection

