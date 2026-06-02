@extends('admin.layouts.app')

@section('content')

  @include('admin.components.tippy-styles')

  <style>
    .orders-container {
      background: #f8f9fa;
      min-height: 100vh;
      padding: 24px 0;
    }

    .orders-card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      border: 2px solid #e9ecef;
      overflow: hidden;
    }

    .orders-header {
      background: white;
      color: #212529;
      padding: 32px;
      border-bottom: 1px solid #e9ecef;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
    }

    .orders-header h1 {
      margin: 0;
      font-size: 24px;
      font-weight: 600;
      color: #212529;
    }

    .orders-header-actions {
      display: flex;
      gap: 10px;
      align-items: center;
      flex-wrap: wrap;
    }

    .logs-button {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #f8f9fa;
      border: 1px solid #ced4da;
      border-radius: 6px;
      padding: 10px 16px;
      color: #212529;
      font-weight: 500;
      transition: all 0.15s ease-in-out;
      text-decoration: none;
    }

    .logs-button:hover {
      background: #e9ecef;
      color: #212529;
      text-decoration: none;
      transform: translateY(-1px);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: white;
      border-radius: 8px;
      padding: 25px;
      text-align: center;
      border: 1px solid #e9ecef;
      transition: all 0.15s ease-in-out;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 8px;
      color: #007bff;
    }

    .stat-label {
      color: #6c757d;
      font-weight: 500;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .filter-controls {
      background: white;
      padding: 24px;
      border-radius: 8px;
      margin-bottom: 24px;
      border: 2px solid #e9ecef;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .search-container {
      position: relative;
    }

    .search-input {
      border: 2px solid #dee2e6;
      border-radius: 4px;
      padding: 8px 40px 8px 12px;
      transition: border-color 0.15s ease-in-out;
      background: white;
      font-size: 14px;
      width: 100%;
    }

    .search-input:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
      outline: none;
    }

    .search-icon {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
    }

    .filter-buttons {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 16px;
    }

    .filter-btn {
      border: 1px solid #dee2e6;
      background: white;
      color: #495057;
      border-radius: 4px;
      padding: 6px 12px;
      font-weight: 500;
      transition: all 0.15s ease-in-out;
      font-size: 14px;
    }

    .filter-btn:hover, .filter-btn.active {
      background: #007bff;
      color: white;
      border-color: #007bff;
    }

    .date-input {
      border: 2px solid #dee2e6;
      border-radius: 4px;
      padding: 8px 12px;
      transition: border-color 0.15s ease-in-out;
      background: white;
      font-size: 14px;
    }

    .date-input:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
      outline: none;
    }

    .btn-modern {
      border-radius: 4px;
      padding: 8px 16px;
      font-weight: 500;
      font-size: 14px;
      transition: all 0.15s ease-in-out;
      border: 1px solid transparent;
    }

    .btn-success {
      background: #28a745;
      color: white;
      border-color: #28a745;
    }

    .btn-success:hover {
      background: #218838;
      border-color: #1e7e34;
    }

    .btn-primary {
      background: #007bff;
      color: white;
      border-color: #007bff;
    }

    .btn-primary:hover {
      background: #0069d9;
      border-color: #0062cc;
    }

    .orders-table {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .orders-table thead {
      background: #e9ecef;
      color: #212529;
    }

    .orders-table thead th {
      border: none;
      padding: 16px;
      font-weight: 600;
      font-size: 14px;
      color: #212529;
      border-bottom: 2px solid #dee2e6;
    }

    .orders-table tbody tr {
      transition: background-color 0.15s ease-in-out;
      border: none;
      border-bottom: 1px solid #e9ecef;
    }

    .orders-table tbody tr:hover {
      background: #f8f9fa;
    }

    .orders-table tbody tr:nth-child(even) {
      background: #fafbfc;
    }

    .orders-table tbody tr:nth-child(even):hover {
      background: #f1f3f4;
    }

    .orders-table tbody td {
      border: none;
      padding: 16px;
      vertical-align: middle;
      font-size: 14px;
    }

    .order-id {
      background: #007bff;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-weight: 600;
      font-size: 12px;
      display: inline-block;
    }

    .customer-card {
      background: #f8f9fa;
      border-radius: 4px;
      padding: 12px;
      border-left: 4px solid #007bff;
    }

    .customer-name {
      font-weight: 600;
      color: #212529;
      margin-bottom: 4px;
    }

    .customer-details {
      font-size: 12px;
      color: #6c757d;
      line-height: 1.4;
    }

    .phone-number {
      color: #007bff;
      font-weight: 500;
    }

    .total-amount {
      font-weight: 600;
      color: #28a745;
    }

    .status-badge {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border: 1px solid transparent;
    }

    .status-pending {
      background: #fff3cd;
      color: #856404;
      border-color: #ffeaa7;
    }

    .status-processing {
      background: #cce5ff;
      color: #004085;
      border-color: #99d6ff;
    }

    .status-completed {
      background: #d4edda;
      color: #155724;
      border-color: #c3e6cb;
    }

    .status-cancelled {
      background: #f8d7da;
      color: #721c24;
      border-color: #f5c6cb;
    }

    /* Специальные стили для конкретных статусов */
    .status-1 {
      background: #fff3cd;
      color: #856404;
      border-color: #ffeaa7;
    }

    .status-2 {
      background: #d1ecf1;
      color: #0c5460;
      border-color: #bee5eb;
    }

    .status-5 {
      background: #d4edda;
      color: #155724;
      border-color: #c3e6cb;
    }

    .status-3 {
      background: #f8d7da;
      color: #721c24;
      border-color: #f5c6cb;
    }

    /* Новые статусы */
    .status-new {
      background: #d1ecf1;
      color: #0c5460;
      border-color: #bee5eb;
    }

    .status-waiting {
      background: #fff3cd;
      color: #856404;
      border-color: #ffeaa7;
    }

    .status-unavailable {
      background: #e2e3e5;
      color: #383d41;
      border-color: #d6d8db;
    }

    .status-error {
      background: #f8d7da;
      color: #721c24;
      border-color: #f5c6cb;
    }

    .status-unreachable {
      background: #f8d7da;
      color: #721c24;
      border-color: #f5c6cb;
    }

    /* Стили для конкретных ID статусов */
    .status-9 {
      background: #cce5ff;
      color: #004085;
      border-color: #99d6ff;
    }

    .status-4 {
      background: #fff3cd;
      color: #856404;
      border-color: #ffeaa7;
    }

    .status-8 {
      background: #fff3cd;
      color: #856404;
      border-color: #ffeaa7;
    }

    .status-6 {
      background: #e2e3e5;
      color: #383d41;
      border-color: #d6d8db;
    }

    .status-7 {
      background: #f8d7da;
      color: #721c24;
      border-color: #f5c6cb;
    }

    .status-10 {
      background: #f8d7da;
      color: #721c24;
      border-color: #f5c6cb;
    }

    .status-11 {
      background: #f8d7da;
      color: #721c24;
      border-color: #f5c6cb;
    }

    /* Стили для badge-secondary (способ оплаты) */
    .badge-secondary {
      background: #6c757d;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .action-buttons {
      display: flex;
      gap: 4px;
      justify-content: center;
    }

    .btn-action {
      width: 32px;
      height: 32px;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.15s ease-in-out;
      border: 1px solid transparent;
      font-size: 14px;
    }

    .btn-edit {
      background: #ffc107;
      color: #212529;
      border-color: #ffc107;
    }

    .btn-edit:hover {
      background: #e0a800;
      border-color: #d39e00;
    }

    .btn-delete {
      background: #dc3545;
      color: white;
      border-color: #dc3545;
    }

    .btn-delete:hover {
      background: #c82333;
      border-color: #bd2130;
    }

    .btn-duplicate {
      background: #17a2b8;
      color: white;
      border-color: #17a2b8;
    }

    .btn-duplicate:hover {
      background: #138496;
      border-color: #117a8b;
    }

    .btn-notify {
      background: #28a745;
      color: white;
      border-color: #28a745;
    }

    .btn-notify:hover {
      background: #1e7e34;
      border-color: #1c7430;
    }

    .results-info {
      background: #e3f2fd;
      border: 1px solid #bbdefb;
      border-radius: 4px;
      padding: 8px 12px;
      color: #1976d2;
      font-weight: 500;
      font-size: 14px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .pagination-container {
      display: flex;
      justify-content: center;
      margin-top: 32px;
      padding: 24px;
    }

    .pagination-container .pagination {
      border-radius: 4px;
      border: 1px solid #e9ecef;
    }

    .pagination-container .page-link {
      border: none;
      padding: 8px 12px;
      color: #007bff;
      background: white;
      transition: all 0.15s ease-in-out;
      font-size: 14px;
    }

    .pagination-container .page-link:hover {
      background: #e9ecef;
      color: #0056b3;
    }

    .pagination-container .page-item.active .page-link {
      background: #007bff;
      color: white;
      border: none;
    }

    .alert-modern {
      border: 1px solid transparent;
      border-radius: 4px;
      padding: 16px;
      margin-bottom: 24px;
    }

    .alert-success {
      background: #d4edda;
      color: #155724;
      border-color: #c3e6cb;
    }

    .alert-danger {
      background: #f8d7da;
      color: #721c24;
      border-color: #f5c6cb;
    }

    .custom-select {
      border: 2px solid #dee2e6;
      border-radius: 4px;
      padding: 6px 10px;
      background: white;
      transition: border-color 0.15s ease-in-out;
      font-size: 14px;
    }

    .custom-select:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
      outline: none;
    }

    .form-inline {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }

    .form-inline label {
      font-weight: 500;
      color: #495057;
      margin: 0;
      font-size: 14px;
    }

    .fade-in {
      animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(8px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 768px) {
      .orders-header {
        padding: 24px;
      }
      
      .orders-header h1 {
        font-size: 20px;
      }
      
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
      }
      
      .form-inline {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
      }
      
      .filter-buttons {
        justify-content: center;
      }
      
      .orders-table {
        font-size: 13px;
      }
      
      .orders-table th,
      .orders-table td {
        padding: 12px 8px;
      }
      
      .action-buttons {
        flex-direction: column;
        gap: 4px;
      }
    }
  </style>

  <div class="orders-container">
  <div class="container-fluid">
    <div class="fade-in">
      @if (session('success'))
          <div class="alert alert-success alert-modern">
            <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
        </div>
      @endif
      @if (session('error'))
          <div class="alert alert-danger alert-modern">
            <i class="fa-solid fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
      @endif
      
      <div class="row">
        <div class="col-md-12">
            <div class="orders-card">
              <div class="orders-header">
                <h1><i class="fa-solid fa-shopping-cart"></i> Pasūtījumi</h1>
                <div class="orders-header-actions">
                  <a href="{{ route('admin.orders.logs') }}" class="logs-button" target="_blank" rel="noopener">
                    <i class="fa-solid fa-file-lines"></i> Pasūtījumu žurnāls
                  </a>
                </div>
              </div>

              <!-- Statistikas kartes -->
              <div class="stats-grid">
                <div class="stat-card">
                  <div class="stat-number">{{ $orders->total() }}</div>
                  <div class="stat-label">Kopā pasūtījumi</div>
                </div>
                <div class="stat-card">
                  <div class="stat-number">{{ $orders->where('order_status', 2)->count() }}</div>
                  <div class="stat-label">Jauni pasūtījumi</div>
                </div>
                <div class="stat-card">
                  <div class="stat-number">{{ $orders->whereNotIn('order_status', [2, 5])->count() }}</div>
                  <div class="stat-label">Nepabeigti</div>
                </div>
                <div class="stat-card">
                  <div class="stat-number">{{ $orders->where('order_status', 5)->count() }}</div>
                  <div class="stat-label">Pabeigti</div>
                </div>
              </div>

              <!-- Meklēšana -->
              <div class="filter-controls">
                <div class="row mb-3">
                  <div class="col-md-6">
                    <div class="search-container">
                      <input type="text" class="search-input" id="search-orders" placeholder="Meklēt pēc Nr., vārda, e-pasta...">
                      <i class="fa-solid fa-magnifying-glass search-icon" id="search-icon"></i>
                      <i class="fa-solid fa-spinner fa-spin search-icon" id="search-spinner" style="display: none;"></i>
                      <i class="fa-solid fa-times search-icon" id="clear-search" style="display: none; cursor: pointer; right: 40px;" title="Notīrīt meklēšanu"></i>
                    </div>
                  </div>
                  <div class="col-md-6 text-right">
                    <div class="results-info">
                      <i class="fa-solid fa-list"></i>
                      Rādīt: <span id="visible-count">{{ $orders->count() }}</span> no <span id="total-count">{{ $orders->total() }}</span>
                    </div>
                  </div>
                </div>
              </div>
            <form class="form-horizontal services_form" id="ordersForm" method="get">
              <input type="hidden" disabled name="csrf_token" value="{{ csrf_token() }}">
              <input type="hidden" disabled name="service_id">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-12">
                    <table class="table orders-table" id="orders-table">
                        <thead>
                      <tr>
                        <th scope="col" data-sortable="true" data-column="created_at">
                          <i class="fa-solid fa-calendar"></i> Datums
                        </th>
                        <th scope="col" data-sortable="true" data-column="id">
                          <i class="fa-solid fa-hashtag"></i> Pas. Nr.
                        </th>
                        <th scope="col" data-sortable="true" data-column="customer_name">
                          <i class="fa-solid fa-user"></i> Klients
                        </th>
                        <th scope="col" data-sortable="true" data-column="total_sum">
                          <i class="fa-solid fa-euro-sign"></i> Summa
                        </th>
                        <th scope="col">
                          <i class="fa-solid fa-filter"></i> Statuss
                          <select name="admin-order-status-select" id="admin-order-status-select" class="custom-select mt-1">
                            <option @if (empty($filteredStatus)) selected @endif value="">Visi</option>
                            @foreach($status_enum as $id => $title)
                              <option @if ($filteredStatus == $id) selected @endif value="{{ $id }}">{{$title}}</option>
                            @endforeach
                          </select>
                        </th>
                        <th scope="col" data-sortable="true" data-column="payment_method">
                          <i class="fa-solid fa-credit-card"></i> Apmaksa
                        </th>
                        <th scope="col">
                          <i class="fa-solid fa-user-edit"></i> Pēdējais labojums
                          <select name="admin-order-editor-select" id="admin-order-editor-select" class="custom-select mt-1">
                            <option @if (empty($filteredEditor)) selected @endif value="">Visi</option>
                            @foreach($users ?? [] as $user)
                              <option @if ($filteredEditor == $user->id) selected @endif value="{{ $user->id }}">{{ $user->fullName }}</option>
                            @endforeach
                          </select>
                        </th>
                        <th scope="col">
                          <i class="fa-solid fa-cog"></i> Darbības
                        </th>
                      </tr>
                      </thead>
                      <tbody>
                        @foreach ($orders as $order)
                        @php
                          $orderData = [
                            'item_sum' => 0,
                            'item_count' => 0,
                            'customer' => [
                              'name' => $order->customer_name ?? '',
                              'surname' => $order->customer_surname ?? '',
                              'phone_country_code' => $order->phone_country_code ?? '+371',
                              'phone_number' => $order->phone_number ?? ''
                            ]
                          ];
                          
                          $details = json_decode($order->order_details);
                          
                          if (isset($details->products)) {
                              foreach ($details->products as $product) {
                              $orderData['item_sum'] += $product->price * $product->quantity;
                              $orderData['item_count'] += $product->quantity;
                              }
                          }

                          if ($order->delivery_price > 0) {
                            $orderData['item_sum'] += (int) substr($order->delivery_price, 0, -2);
                          } elseif ($order->mounting_price > 0) {
                            $orderData['item_sum'] += (int) substr($order->mounting_price, 0, -2);
                          }
                        @endphp
                          <tr class="order-row" data-order-id="{{ $order->id }}"
                              data-customer="{{ $orderData['customer']['name'] }} {{ $orderData['customer']['surname'] }}"
                              data-email="{{ $order->email }}"
                              data-amount="{{ $order->total_sum }}"
                              data-status="{{ $order->order_status }}"
                              data-date="{{ $order->created_at->format('Y-m-d') }}">
                            <td>
                              <div class="d-flex flex-column">
                                <span class="font-weight-bold">{{ $order->created_at->format('d.m.Y') }}</span>
                                <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                              </div>
                            </td>
                            <td>
                              <span class="order-id">{{ $order->order_number ? '#' . $order->order_number : '-' }}</span>
                            </td>
                            <td>
                              <div class="customer-card">
                                <div class="customer-name">{{ $orderData['customer']['name'] }} {{ $orderData['customer']['surname'] }}</div>
                                <div class="customer-details">
                                  <div><i class="fa-solid fa-envelope"></i> {{ $order->email }}</div>
                                  @if (!empty($orderData['customer']['phone_number']))
                                    <div class="phone-number">
                                      <i class="fa-solid fa-phone"></i>
                                      {{ $orderData['customer']['phone_country_code'] }} {{ $orderData['customer']['phone_number'] }}
                                    </div>
                                @endif
                                  <div><i class="fa-solid fa-box"></i> {{ $orderData['item_count'] }} preces</div>
                                </div>
                              </div>
                            </td>
                            <td>
                              <span class="total-amount">{{ $order->total_sum }} €</span>
                            </td>
                            <td>
                                @if (!empty($order->admin_info))
                                    <span class="status-badge status-{{ $getStatusClass($order->order_status) }}">{{ $status_enum[$order->order_status] }}</span>
                                    <span class="tippy lisi-tooltip dot" 
                                          data-color="info" 
                                          data-tippy-content='<div style="padding: 5px; text-align: left;"><span style="color: white; font-size: 15px; line-height: 28px;">{!! nl2br(e($order->admin_info)) !!}</span></div>'
                                          style="background-color: #4facfe; cursor: pointer; margin-left: 5px;">
                                        <i class="fa-solid fa-info" style="color: white; font-size: 10px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"></i>
                                    </span>
                                @else
                                    <span class="status-badge status-{{ $getStatusClass($order->order_status) }}">{{ $status_enum[$order->order_status] }}</span>
                                @endif
                            </td>
                            <td>
                              <span class="badge badge-secondary">
                                {{ $pay_enum[$order->payment_method] ?? 'Nav norādīts' }}
                              </span>
                            </td>
                            <td>
                              @if ($order->editor)
                                <div class="d-flex flex-column">
                                  <span class="font-weight-bold">{{ $order->editor->fullName }}</span>
                                  <small class="text-muted">{{ $order->updated_at->format('d.m.Y H:i') }}</small>
                                </div>
                              @else
                                <span class="text-muted">Neviens nav veicis labojumus</span>
                              @endif
                            </td>
                            <td>
                              <div class="action-buttons">
                                <a href="{{ route('admin.order', $order->id) }}" class="btn-action btn-edit" title="Rediģēt">
                                  <i class="fa-solid fa-pencil"></i>
                                </a>
                                <button class="btn-action btn-notify" onclick="sendNotification({{ $order->id }})" title="Sūtīt paziņojumu">
                                  <i class="fa-solid fa-envelope"></i>
                              </button>
                                <a onclick="return confirm('Tiešām vēlies dzēst?')" href="{{ route('admin.order.delete', $order->id) }}" class="btn-action btn-delete" title="Dzēst">
                                  <i class="fa-solid fa-trash"></i>
                                </a>
                              </div>
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="pagination-container">{{ $orders->links() }}</div>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>

@endsection

@section('scripts')
<script>
  // Передаем данные из PHP в JavaScript
  const statusClassMap = @json($status_class_map);
  
  document.addEventListener('DOMContentLoaded', function() {
  // Инициализация tippy для всех элементов с data-tippy-content
  tippy('[data-tippy-content]', {
    theme: 'light',
    arrow: true,
    arrowType: 'sharp',
    placement: 'top',
    animation: 'scale',
    interactive: true,
    delay: [0, 100],
    maxWidth: 500,
    allowHTML: true
  });

  // Массивы для хранения данных
  let allOrders = [];
  let filteredOrders = [];

  // Сбор данных о заказах
  document.querySelectorAll('.order-row').forEach(function(row) {
    allOrders.push({
      element: row,
      id: row.dataset.orderId,
      customer: row.dataset.customer.toLowerCase(),
      email: row.dataset.email.toLowerCase(),
      amount: parseFloat(row.dataset.amount) || 0,
      status: parseInt(row.dataset.status),
      date: row.dataset.date
    });
  });

  filteredOrders = [...allOrders];
  updateResultsCount();

  // Поиск по базе данных
  let searchTimeout;
  const searchInput = document.getElementById('search-orders');
  const clearSearchBtn = document.getElementById('clear-search');
  
  searchInput.addEventListener('keyup', function() {
    const searchTerm = this.value.trim();
    
    // Показываем/скрываем кнопку очистки
    if (searchTerm) {
      clearSearchBtn.style.display = 'block';
    } else {
      clearSearchBtn.style.display = 'none';
    }
    
    // Очищаем предыдущий таймер
    clearTimeout(searchTimeout);
    
    // Если поле пустое, показываем все заказы
    if (searchTerm === '') {
      location.reload();
      return;
    }
    
    // Устанавливаем задержку для избежания частых запросов
    searchTimeout = setTimeout(() => {
      searchInDatabase(searchTerm);
    }, 300);
  });
  
  // Обработчик кнопки очистки
  clearSearchBtn.addEventListener('click', function() {
    searchInput.value = '';
    clearSearchBtn.style.display = 'none';
    location.reload();
  });
  
  function searchInDatabase(searchTerm) {
    // Показываем индикатор загрузки
    const searchInput = document.getElementById('search-orders');
    const searchIcon = document.getElementById('search-icon');
    const searchSpinner = document.getElementById('search-spinner');
    const originalPlaceholder = searchInput.placeholder;
    
    searchInput.placeholder = 'Meklē...';
    searchInput.disabled = true;
    searchIcon.style.display = 'none';
    searchSpinner.style.display = 'block';
    
    // AJAX запрос для поиска
    const formData = new FormData();
    formData.append('search', searchTerm);
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch('{{ route("admin.orders.search") }}', {
      method: 'POST',
      headers: {
        'Accept': 'application/json'
      },
      body: formData
    })
    .then(response => {
      console.log('Response status:', response.status);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('Search response:', data);
      if (data.success) {
        updateTableWithSearchResults(data.orders);
      } else {
        console.error('Meklēšanas kļūda:', data.message);
        showSearchError(data.message || 'Nezināma meklēšanas kļūda');
      }
    })
    .catch(error => {
      console.error('AJAX kļūda:', error);
      showSearchError('Savienojuma kļūda ar serveri');
    })
    .finally(() => {
      // Восстанавливаем поле поиска
      searchInput.placeholder = originalPlaceholder;
      searchInput.disabled = false;
      searchIcon.style.display = 'block';
      searchSpinner.style.display = 'none';
    });
  }
  
  function updateTableWithSearchResults(orders) {
    const tbody = document.querySelector('#orders-table tbody');
    const visibleCount = document.getElementById('visible-count');
    const totalCount = document.getElementById('total-count');
    
    // Очищаем таблицу
    tbody.innerHTML = '';
    
    if (orders.length === 0) {
      tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Pasūtījumi nav atrasti</td></tr>';
      visibleCount.textContent = '0';
      totalCount.textContent = '0';
      return;
    }
    
    // Заполняем таблицу результатами поиска
    orders.forEach(order => {
      const row = createOrderRow(order);
      tbody.appendChild(row);
    });
    
    // Обновляем счетчики
    visibleCount.textContent = orders.length;
    totalCount.textContent = orders.length;
    
    // Инициализируем tippy для новых элементов
    tippy('[data-tippy-content]', {
      theme: 'light',
      arrow: true,
      arrowType: 'sharp',
      placement: 'top',
      animation: 'scale',
      interactive: true,
      delay: [0, 100],
      maxWidth: 350,
      allowHTML: true
    });
    
    // Обновляем массив заказов для других фильтров
    updateOrdersArray(orders);
  }
  
  function createOrderRow(order) {
    const tr = document.createElement('tr');
    tr.className = 'order-row';
    tr.dataset.orderId = order.id;
    tr.dataset.customer = `${order.customer_name} ${order.customer_surname}`;
    tr.dataset.email = order.email;
    tr.dataset.amount = order.total_sum;
    tr.dataset.status = order.order_status;
    tr.dataset.date = order.created_at.split(' ')[0];
    
    tr.innerHTML = `
      <td>
        <div class="d-flex flex-column">
          <span class="font-weight-bold">${formatDate(order.created_at)}</span>
          <small class="text-muted">${formatTime(order.created_at)}</small>
        </div>
      </td>
      <td>
        <span class="order-id">${order.order_number ? '#' + order.order_number : '-'}</span>
      </td>
      <td>
        <div class="customer-card">
          <div class="customer-name">${order.customer_name} ${order.customer_surname}</div>
          <div class="customer-details">
            <div><i class="fa-solid fa-envelope"></i> ${order.email}</div>
            ${order.phone_number ? `<div class="phone-number"><i class="fa-solid fa-phone"></i> ${formatPhone(order.phone_country_code, order.phone_number)}</div>` : ''}
            <div><i class="fa-solid fa-box"></i> ${order.item_count || 0} preces</div>
          </div>
        </div>
      </td>
      <td>
        <span class="total-amount">${order.total_sum} €</span>
      </td>
      <td>
        <span class="status-badge status-${getStatusClass(order.order_status)}">${order.status_name}</span>
        ${order.admin_info ? `<span class="tippy lisi-tooltip dot" data-color="info" data-tippy-content='<div style="padding: 5px; text-align: left;"><span style="color: white; font-size: 15px; line-height: 28px;">${order.admin_info.replace(/"/g, '&quot;').replace(/\n/g, '<br>')}</span></div>' style="background-color: #4facfe; cursor: pointer; margin-left: 5px;"><i class="fa-solid fa-info" style="color: white; font-size: 10px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"></i></span>` : ''}
      </td>
      <td>
        <span class="badge badge-secondary">${order.payment_method_name || 'Nav norādīts'}</span>
      </td>
      <td>
        ${order.editor_name ? `
          <div class="d-flex flex-column">
            <span class="font-weight-bold">${order.editor_name}</span>
            <small class="text-muted">${formatDate(order.updated_at)} ${formatTime(order.updated_at)}</small>
          </div>
        ` : '<span class="text-muted">Neviens nav veicis labojumus</span>'}
      </td>
      <td>
        <div class="action-buttons">
          <a href="/admin/order/${order.id}" class="btn-action btn-edit" title="Rediģēt">
            <i class="fa-solid fa-pencil"></i>
          </a>
          <button class="btn-action btn-notify" onclick="sendNotification(${order.id})" title="Sūtīt paziņojumu">
            <i class="fa-solid fa-envelope"></i>
          </button>
          <a onclick="return confirm('Tiešām vēlies dzēst?')" href="/admin/order/delete/${order.id}" class="btn-action btn-delete" title="Dzēst">
            <i class="fa-solid fa-trash"></i>
          </a>
        </div>
      </td>
    `;
    
    return tr;
  }
  
  function updateOrdersArray(orders) {
    allOrders = orders.map(order => ({
      element: document.querySelector(`tr[data-order-id="${order.id}"]`),
      id: order.id.toString(),
      customer: `${order.customer_name} ${order.customer_surname}`.toLowerCase(),
      email: order.email.toLowerCase(),
      amount: parseFloat(order.total_sum) || 0,
      status: parseInt(order.order_status),
      date: order.created_at.split(' ')[0]
    }));
    
    filteredOrders = [...allOrders];
  }
  
  function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('lv-LV', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
  }
  
  function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('lv-LV', {
      hour: '2-digit',
      minute: '2-digit'
    });
  }
  
  function formatPhone(countryCode, phoneNumber) {
    if (countryCode === '+371' && phoneNumber.length === 8) {
      return `${countryCode} ${phoneNumber.substring(0, 2)} ${phoneNumber.substring(2, 5)} ${phoneNumber.substring(5, 8)}`;
    }
    return `${countryCode} ${phoneNumber}`;
  }
  
  function getStatusClass(status) {
    return statusClassMap[status] || 'pending';
  }
  
  
  function showSearchError(message = 'Meklēšanas kļūda. Mēģiniet vēlreiz.') {
    const tbody = document.querySelector('#orders-table tbody');
    tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">${message}</td></tr>`;
    
    // Обновляем счетчики
    const visibleCount = document.getElementById('visible-count');
    const totalCount = document.getElementById('total-count');
    if (visibleCount) visibleCount.textContent = '0';
    if (totalCount) totalCount.textContent = '0';
  }

  // Фильтр по статусу
  document.getElementById('admin-order-status-select').addEventListener('change', function() {
    const status = this.value;
    if (status === '') {
      filteredOrders = [...allOrders];
    } else {
      filteredOrders = allOrders.filter(order => order.status == status);
    }
    applyFilters();
  });

  // Фильтр по редактору
  document.getElementById('admin-order-editor-select').addEventListener('change', function() {
    const editor = this.value;
    if (editor === '') {
      filteredOrders = [...allOrders];
    } else {
      filteredOrders = allOrders.filter(order => {
        const row = order.element;
        const editorCell = row.querySelector('td:nth-child(7)'); // Колонка редактора
        return editorCell && editorCell.textContent.includes(editor);
      });
    }
    applyFilters();
  });

  function applyFilters() {
    // Скрываем все строки
    allOrders.forEach(order => order.element.style.display = 'none');

    // Показываем только отфильтрованные строки
    filteredOrders.forEach(order => order.element.style.display = '');

    updateResultsCount();
  }

  function updateResultsCount() {
    document.getElementById('visible-count').textContent = filteredOrders.length;
    document.getElementById('total-count').textContent = allOrders.length;
  }

  // Функции для дополнительных действий

  window.sendNotification = function(orderId) {
    if (confirm('Sūtīt paziņojumu klientam?')) {
      // Здесь будет AJAX запрос для отправки уведомления
      alert('Funkcija tiks pievienota nākamajā versijā');
    }
  };

  // Сортировка таблицы
  document.querySelectorAll('[data-sortable="true"]').forEach(th => {
    th.addEventListener('click', function() {
      const column = this.dataset.column;
      const tbody = this.closest('table').querySelector('tbody');
      const rows = Array.from(tbody.querySelectorAll('tr'));
      
      const sortedRows = rows.sort((a, b) => {
        const aVal = a.dataset[column] || a.cells[Array.from(this.parentNode.children).indexOf(this)].textContent;
        const bVal = b.dataset[column] || b.cells[Array.from(this.parentNode.children).indexOf(this)].textContent;
        
        if (column === 'amount' || column === 'id') {
          return parseFloat(aVal) - parseFloat(bVal);
        }
        return aVal.localeCompare(bVal);
      });
      
      sortedRows.forEach(row => tbody.appendChild(row));
    });
  });

  // Анимация появления элементов
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  });

  document.querySelectorAll('.stat-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(card);
    });
  });
</script>
@endsection

