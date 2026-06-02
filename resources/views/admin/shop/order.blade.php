@extends('admin.layouts.app')

@section('content')

  @include('admin.components.tippy-styles')

  <style>
    .order-container {
      background: #f8f9fa;
      min-height: 100vh;
      padding: 24px 0;
    }

    .order-card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      border: 2px solid #e9ecef;
      overflow: hidden;
      margin-bottom: 24px;
    }

    .order-header {
      background: white;
      color: #212529;
      padding: 32px;
      border-bottom: 1px solid #e9ecef;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .order-header h1 {
      margin: 0;
      font-size: 24px;
      font-weight: 600;
      color: #212529;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .order-header .order-id {
      background: #007bff;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-weight: 600;
      font-size: 12px;
      display: inline-block;
    }

    .order-content {
      padding: 32px;
    }

    .form-section {
      background: white;
      border-radius: 8px;
      padding: 24px;
      margin-bottom: 24px;
      border: 1px solid #e9ecef;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .form-section h3,
    .form-section h4 {
      margin: 0 0 20px 0;
      color: #212529;
      font-weight: 600;
      padding-bottom: 12px;
      border-bottom: 2px solid #007bff;
    }

    .form-section h4 {
      font-size: 18px;
      border-bottom: 1px solid #e9ecef;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group:last-child {
      margin-bottom: 0;
    }

    .form-label {
      font-weight: 500;
      color: #495057;
      margin-bottom: 8px;
      font-size: 14px;
    }

    .form-control,
    .custom-select {
      border: 2px solid #dee2e6;
      border-radius: 4px;
      padding: 10px 12px;
      transition: border-color 0.15s ease-in-out;
      background: white;
      font-size: 14px;
      color: #495057;
      min-height: 42px;
      line-height: 1.5;
    }

    .form-control:focus,
    .custom-select:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
      outline: none;
    }

    .form-control:disabled {
      background: #f8f9fa;
      color: #6c757d;
    }
    
    .custom-select option {
      color: #495057;
      background-color: white;
      padding: 8px 12px;
      font-size: 14px;
    }
    
    .custom-select option:checked {
      background-color: #007bff;
      color: white;
    }

    .btn-group {
      display: flex;
      gap: 12px;
      justify-content: flex-end;
      margin-top: 32px;
      padding: 24px;
      background: #f8f9fa;
      border-radius: 8px;
    }

    .btn-group-compact {
      display: flex;
      gap: 8px;
      justify-content: flex-end;
      margin-top: 16px;
    }

    .btn {
      border-radius: 4px;
      padding: 10px 20px;
      font-weight: 500;
      font-size: 14px;
      transition: all 0.15s ease-in-out;
      border: 1px solid transparent;
    }

    .btn-compact {
      border-radius: 4px;
      padding: 6px 12px;
      font-weight: 500;
      font-size: 12px;
      transition: all 0.15s ease-in-out;
      border: 1px solid transparent;
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

    .btn-secondary {
      background: #6c757d;
      color: white;
      border-color: #6c757d;
    }

    .btn-secondary:hover {
      background: #5a6268;
      border-color: #545b62;
    }

    .alert {
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

    .contact-link {
      color: #007bff;
      text-decoration: none;
      font-weight: 500;
    }

    .contact-link:hover {
      color: #0056b3;
      text-decoration: underline;
    }

    .order-details {
      background: #f8f9fa;
      border-radius: 4px;
      padding: 16px;
      margin-top: 8px;
    }

    .order-details h5 {
      margin: 0 0 12px 0;
      color: #495057;
      font-weight: 600;
    }

    .order-details p {
      margin: 4px 0;
      color: #6c757d;
      font-size: 14px;
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

    .gap-3 {
      gap: 1rem;
    }

    @media (max-width: 768px) {
      .order-content {
        padding: 20px;
      }
      
      .order-header {
        padding: 24px;
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
      }
      
      .order-header h1 {
        font-size: 20px;
      }
      
      .btn-group {
        flex-direction: column;
      }
      
      .btn-group-compact {
        align-self: flex-end;
      }
    }
  </style>

  <div class="order-container">
    <div class="container">
      @if (session('success'))
        <div class="alert alert-success">
          <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
        </div>
      @endif
      @if (session('danger'))
        <div class="alert alert-danger">
          <i class="fa-solid fa-exclamation-circle"></i> {{ session('danger') }}
        </div>
      @endif

      <div class="order-card">
        <div class="order-header">
          <h1>
            <i class="fa-solid fa-shopping-cart"></i>
            Pasūtījuma informācija
            <span class="order-id">#{{ $order->order_number }}</span>
          </h1>
          <div class="btn-group-compact">
            <button type="submit" form="orderUpdate" class="btn btn-primary btn-compact">
              <i class="fa-solid fa-save"></i> Saglabāt
            </button>
            <a href="{{ route('admin.orders') }}" class="btn btn-secondary btn-compact">
              <i class="fa-solid fa-arrow-left"></i> Atgriezties
            </a>
          </div>
        </div>

        <div class="order-content">
          <form id="orderUpdate" method="POST" action="{{ route('admin.order.update', $order->id) }}">
            @csrf
            
            <div class="form-section">
              <h3><i class="fa-solid fa-edit"></i> Admin piezīmes</h3>
              <div class="form-group">
                <label class="form-label">Admin piezīmes</label>
                <textarea name="admin_info" class="form-control" cols="30" rows="5" placeholder="Ievadiet admin piezīmes...">{{ $order->admin_info }}</textarea>
              </div>
            </div>

            <div class="form-section">
              <h3><i class="fa-solid fa-info-circle"></i> Pasūtījuma informācija</h3>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Rēķina numurs</label>
                    <input class="form-control" disabled type="text" value="{{ $order->order_number }}">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Pasūtīšanas datums</label>
                    <input class="form-control" type="text" disabled value="{{ $order->created_at }}">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Statuss</label>
                    <select name="order_status" class="custom-select">
                      @foreach ($status_enum as $status_id => $status_name)
                        <option value="{{ $status_id }}" @if ($order->order_status == $status_id) selected @endif>{{ $status_name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Kopsumma</label>
                    <input class="form-control" type="text" disabled value="{{ $item_sum }} € @if(isset($display_payment)) - {{ $display_payment }} @elseif(isset($order->payment_method) && isset($pay_enum[$order->payment_method])) - {{ $pay_enum[$order->payment_method] }} @endif">
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h4><i class="fa-solid fa-user"></i> Pamatinformācija</h4>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Vārds, uzvārds</label>
                    <input class="form-control" name="customer_name" type="text" value="{{ trim($order->customer_name . ' ' . $order->customer_surname) }}" placeholder="Ievadiet vārdu un uzvārdu...">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">E-pasts</label>
                    <div class="form-control" style="background: #f8f9fa; border: 1px solid #e9ecef; display: flex; align-items: center; min-height: 42px;">
                      <a href="mailto:{{ $order->email }}" class="contact-link" style="display: flex; align-items: center; gap: 8px; text-decoration: none;">
                        <i class="fa-solid fa-envelope"></i> {{ $order->email }}
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Tālrunis</label>
                <input class="form-control" name="phone_number" type="text" value="{{ $order->phone_country_code }} {{ $order->phone_number }}" placeholder="+371 XXXXXXXX">
              </div>
            </div>

            <div class="form-section">
              <h4><i class="fa-solid fa-truck"></i> Piegāde un saņemšana</h4>
              
              <div class="form-group">
                <label class="form-label">Saņemšanas vieta</label>
                <select name="delivery_method" class="custom-select" style="color: black; padding: 0 10px!important;" disabled>
                  @foreach ($offices as $office)
                    <option value="{{ $office->office_id }}" @if ($order->mounting_office == $office->office_id) selected @endif>{{ $office->shipping }}</option>
                  @endforeach
                  <option value="3" @if ($order->delivery_method == 2) selected @endif>Piegāde</option>
                </select>
              </div>

	      @php
                $isDeliverySelected = (int) ($order->delivery_method ?? 0) === 2;
              @endphp
              <div class="row" data-delivery-fields @if(!$isDeliverySelected) style="display: none;" @endif>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Pilsēta</label>
                    <select name="delivery_city" class="custom-select" style="color: black; padding: 0 10px!important;" disabled>
                      <option value="1" @if ($order->delivery_city == 1) selected @endif>Rīga</option>
                      <option value="3" @if ($order->delivery_city == 3) selected @endif>Cits</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-8">
                  <div class="form-group">
                    <label class="form-label">Piegādes adrese</label>
                    <input class="form-control" name="delivery_address" type="text" value="{{ $order->delivery_address }}" placeholder="Ievadiet pilnu adresi..." style="background-color: #d8dbe0; color: black; padding: 0 10px!important; cursor: text;" disabled>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Piezīmes</label>
                <textarea name="notes" class="form-control" cols="30" rows="3" placeholder="Papildu piezīmes par pasūtījumu...">@if (!empty($userData->notes)){{ $userData->notes }}@endif</textarea>
              </div>
            </div>



      @php
        $hasCompanyData = property_exists($userData, 'company_registration_number');
      @endphp

        <div>

        <div class="form-group row">
          <label class="col-md-3 form-control-label text-left text-md-right">
            <h4>Uzņēmuma informācija</h4>
          </label>
          <div class="col-md-6">

          </div>
          <div class="col-md-3 form-control-comment">
          </div>
        </div>

        <div class="form-group row">
          <label class="col-md-3 form-control-label text-left text-md-right">
            Reģistrācijas Nr.
          </label>
          <div class="col-md-6">
            <input class="form-control" name="company_reg_nr" type="text" value="{{ $order->company_reg_nr }}">
          </div>
          <div class="col-md-3 form-control-comment">
          </div>
        </div>

          <div class="form-group row">
          <label class="col-md-3 form-control-label text-left text-md-right">
            PVN numurs
          </label>
          <div class="col-md-6">
            <input class="form-control" name="company_pvn_nr" type="text" value="{{ $order->company_pvn_nr }}">
          </div>
          <div class="col-md-3 form-control-comment">
          </div>
        </div>

        <div class="form-group row">
          <label class="col-md-3 form-control-label text-left text-md-right">
            Uzņēmuma nosaukums
          </label>
          <div class="col-md-6">
            <input class="form-control" name="company_name" type="text" value="{{ $order->company_name }}">
          </div>
          <div class="col-md-3 form-control-comment">
          </div>
        </div>

        <div class="form-group row">
          <label class="col-md-3 form-control-label text-left text-md-right">
            Juridiskā adrese
          </label>
          <div class="col-md-6">
            <input class="form-control" name="company_address" type="text" value="{{ $order->company_address }}">
          </div>
          <div class="col-md-3 form-control-comment">
          </div>
        </div>

        </div>

      <div class="form-group row">
        <label class="col-md-3 form-control-label text-left text-md-right">
          <h4>Auto dati</h4>
        </label>
        <div class="col-md-6">

        </div>
        <div class="col-md-3 form-control-comment">
        </div>
      </div>

      @php
        $carDetails = $order->car_details ? json_decode($order->car_details, true) : [];
      @endphp

      <div class="form-group row">
        <label class="col-md-3 form-control-label text-left text-md-right">
          Reģistrācijas numurs
        </label>
        <div class="col-md-6">
          <input type="text" class="form-control" name="car_plate" value="{{ $carDetails['car_plate'] ?? '' }}">
        </div>
      </div>

      <div class="form-group row">
        <label class="col-md-3 form-control-label text-left text-md-right">
          Auto brends
        </label>
        <div class="col-md-6">
          <input type="text" class="form-control" name="car_brand" value="{{ $carDetails['car_brand'] ?? '' }}">
        </div>
      </div>

      <div class="form-group row">
        <label class="col-md-3 form-control-label text-left text-md-right">
          Auto modelis
        </label>
        <div class="col-md-6">
          <input type="text" class="form-control" name="car_model" value="{{ $carDetails['car_model'] ?? '' }}">
        </div>
      </div>

      <div class="form-group row">
        <label class="col-md-3 form-control-label text-left text-md-right">
          Auto izlaiduma gads
        </label>
        <div class="col-md-6">
          <input type="text" class="form-control" name="car_release_year" value="{{ $carDetails['car_release_year'] ?? '' }}">
        </div>
      </div>

      <div class="form-group row">
        <label class="col-md-3 form-control-label text-left text-md-right">
          Auto dzinēja izmērs
        </label>
        <div class="col-md-6">
          <input type="text" class="form-control" name="car_engine_size" value="{{ $carDetails['car_engine_size'] ?? '' }}">
        </div>
      </div>

      </form>

      <div class="form-group row">
        <label class="col-md-3 form-control-label text-left">
          <h4 class="float-md-right">Pasūtītās preces</h4>
        </label>
        <div class="col-md-6">

        </div>
        <div class="col-md-3 form-control-comment">
        </div>
      </div>
            <div class="form-section">
              <h4><i class="fa-solid fa-box"></i> Pasūtījuma saturs</h4>
              
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead class="table-light">
                    <tr>
                      <th scope="col">ID</th>
                      <th scope="col">Nosaukums</th>
                      <th scope="col">Skaits</th>
                      <th scope="col">Cena</th>
                      <th scope="col">Kopā</th>
                      <th scope="col" class="text-center">Pieejamība</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($tires as $tire)
                      <tr>
                        <td><span class="badge bg-secondary">{{ $tire->tire_id }}</span></td>
                        <td>
                          @if (isset($tire->url))
                            <a target="_blank" href="{{ $tire->url }}" class="text-decoration-none">{!! $tire->title !!}</a>
                          @else
                            {!! $tire->title !!}
                          @endif
                        </td>
                        <td>{{ $tire->quantity }}</td>
                        <td>{{ $tire->price }} €</td>
                        <td><strong>{{ $tire->quantity * $tire->price }} €</strong></td>
                        <td class="text-center">
                          <span class="tippy lisi-tooltip dot {{ $tire->dotAvailable }}" 
                                data-color="{{ $tire->dotAvailable }}" 
                                data-tippy-content='<div style="padding: 5px; text-align: left;"><span style="color: white; font-size: 15px; line-height: 28px;">{{ $tire->stockAvailability }}</span></div>'>
                          </span>
                        </td>
                      </tr>
                    @endforeach
                    
                    @if ($order->delivery_price > 0 || $order->mounting_price > 0)
                      <tr class="table-info">
                        <td></td>
                        <td>
                          @if ($order->mounting_price > 0)
                            <i class="fa-solid fa-wrench"></i> Montāža
                          @endif
                          @if ($order->delivery_price > 0)
                            <i class="fa-solid fa-truck"></i> Piegāde
                          @endif
                        </td>
                        <td></td>
                        <td></td>
                        <td><strong>{{ $order->mounting_price > 0 ? $order->mounting_price : $order->delivery_price }} €</strong></td>
                        <td></td>
                      </tr>
                    @endif
                    
                    @if ($promo)
                      <tr class="table-warning">
                        <td></td>
                        <td>
                          @if ($promo->discount_type === 'percentage')
                            <i class="fa-solid fa-percent"></i> Atlaižu kods (-{{ $promo->discount_value }}%) (Kods - {{ $promo->promo_code }})
                          @else
                            <i class="fa-solid fa-percent"></i> Atlaižu kods (-{{ $promo->discount_value }} €) (Kods - {{ $promo->promo_code }})
                          @endif
                        </td>
                        <td></td>
                        <td></td>
                        <td><strong>
                          @if ($promo->discount_type === 'percentage')
                            -{{ round($order->total_price * ($promo->discount_value / 100)) }} €
                          @else
                            -{{ $promo->discount_value }} €
                          @endif
                        </strong></td>
                        <td></td>
                      </tr>
                    @endif
                    
                    <tr class="table-success">
                      <td colspan="4" class="text-end"><strong>Kopsumma:</strong></td>
                      <td colspan="2"><strong>{{ $item_sum }} €</strong></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="btn-group">
              <button type="submit" form="orderUpdate" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Saglabāt
              </button>
              <a href="{{ route('admin.orders') }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Atgriezties
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

@endsection

@section('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const deliveryMethodSelect = document.querySelector('select[name="delivery_method"]');
      const deliveryFieldsContainer = document.querySelector('[data-delivery-fields]');

      if (!deliveryMethodSelect || !deliveryFieldsContainer) {
        return;
      }

      const toggleDeliveryFields = () => {
        if (deliveryMethodSelect.value === '3') {
          deliveryFieldsContainer.style.display = '';
        } else {
          deliveryFieldsContainer.style.display = 'none';
        }
      };

      toggleDeliveryFields();
      deliveryMethodSelect.addEventListener('change', toggleDeliveryFields);
    });
  </script>
@endsection
