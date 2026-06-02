@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="main-content clearfix col-md-12 col-xl-10">
                <div id="content-wrapper" class="right-column col-lg-12">
                    <section id="main">
                        <div class="cart-grid row">
                            <div class="loading-indicator" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; background: rgba(255, 255, 255, 0.9); padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="mt-2">Lūdzu uzgaidiet...</div>
                            </div>
                            {{--Stepper--}}
                            <div class="stepper-wrapper">
                                <ol class="stepper">
                                    <li class="stepper-item stepper-completed">
                                        <h3 class="stepper-title hidden-md-down">Grozs</h3>
                                    </li>
                                    <li class="stepper-item stepper-completed">
                                        <h3 class="stepper-title hidden-md-down">Dati</h3>
                                    </li>
                                    <li class="stepper-item stepper-active">
                                        <h3 class="stepper-title hidden-md-down">Maksājums</h3>
                                    </li>
                                    <li class="stepper-item stepper-last">
                                        <h3 class="stepper-title hidden-md-down">Pabeigts</h3>
                                    </li>
                                </ol>
                            </div>

                            <div class="card cart-card">
                                <h1>Pasūtījuma informācija</h1>
                                <hr>
                                <!-- begin table -->
                                <div class="table-responsive checkout-table">
                                    <h4>Pamatinformācija</h4>
                                    <table class="table table-hover table-striped">
                                        <tbody>
                                        <tr class="d-flex">
                                            <td class="field">Vārds, uzvārds</td>
                                            <td>@if ($existingOrder) {{ $existingOrder->customer_name . ' ' . $existingOrder->customer_surname }} @endif</td>
                                        </tr>
                                        <tr>
                                            <td class="field">e-pasts</td>
                                            <td>@if ($existingOrder) {{ $existingOrder->email }} @endif</td>
                                        </tr>
                                        <tr>
                                            <td class="field">Tālrunis</td>
                                            <td>@if ($existingOrder) {{ $existingOrder->phone_country_code . ' ' . $existingOrder->phone_number }} @endif</td>
                                        </tr>
                                        <tr>
                                            <td class="field">Saņemšanas vieta</td>
                                            <td>
                                                @if (isset($user_data['fitting']))
                                                    @php
                                                        try {
                                                            $office = \App\Models\Office::findOrFail($existingOrder->mounting_office);
                                                            echo $office->shipping;
                                                        } catch (\Exception $e) {
                                                            echo "Lokācija nav atrasta";
                                                        }
                                                    @endphp
                                                @else
                                                    @if (isset($existingOrder->delivery_city))
                                                        {{ $existingOrder->delivery_address }}, @if ($existingOrder->delivery_city == 1) Rīga @elseif ($existingOrder->delivery_city == 3) Cits @else {{ $existingOrder->delivery_city }} @endif
                                                    @else
                                                        @php
                                                            try {
                                                                $office = \App\Models\Office::findOrFail($existingOrder->mounting_office);
                                                                echo $office->shipping;
                                                            } catch (\Exception $e) {
                                                                echo "Lokācija nav atrasta";
                                                            }
                                                        @endphp
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                        @if ($existingOrder && $existingOrder->company_reg_nr)

                                            <tr class="highlight">
                                                <td class="field">Reģistrācijas Nr.</td>
                                                <td>@if ($existingOrder && $existingOrder->company_reg_nr){{$existingOrder->company_reg_nr}}@endif</td>
                                            </tr>

                                            <tr class="highlight">
                                                <td class="field">PVN numurs</td>
                                                <td>@if ($existingOrder && $existingOrder->company_pvn_nr){{$existingOrder->company_pvn_nr}}@endif</td>
                                            </tr>

                                            <tr class="highlight">
                                                <td class="field">Uzņēmuma nosaukums</td>
                                                <td>@if ($existingOrder && $existingOrder->company_name){{$existingOrder->company_name}}@endif</td>
                                            </tr>

                                            <tr class="highlight">
                                                <td class="field">Juridiskā adrese</td>
                                                <td>@if ($existingOrder && $existingOrder->company_address){{$existingOrder->company_address}}@endif</td>
                                            </tr>
                                        @endif

                                        </tbody>
                                    </table>
                                    <hr>
                                    @if (!is_null($existingOrder->comments) || $existingOrder->email_notification > 0)
                                        <h4>Papildus informācija</h4>
                                        <table class="table table-hover">
                                            <tbody>
                                            @if (!is_null($existingOrder->comments))
                                                <tr class="highlight">
                                                    <td class="field">Piezīmes</td>
                                                    <td>{{ $existingOrder->comments }}</td>
                                                </tr>
                                            @endif
                                            @if ($existingOrder->email_notification > 0)
                                                <tr class="highlight">
                                                    <td class="field">E-pasta paziņojumi</td>
                                                    <td>Atļauju man sūtīt paziņojumus par akcijām un jaunumiem uz norādīto e-pastu</td>
                                                </tr>
                                            @endif
                                            </tbody>
                                        </table>
                                        <hr>
                                    @endif
                                    @if ($existingOrder && !is_null($existingOrder->car_details))
                                        <h4>Informācija par automašīnu</h4>
                                        <table class="table table-hover">
                                            <tbody>
                                            @if (\App\Helper\Utility::decode_info($existingOrder->car_details)->car_plate)
                                                <tr class="highlight d-flex">
                                                    <td class="field">Reģistrācijas numurs</td>
                                                    <td>{{\App\Helper\Utility::decode_info($existingOrder->car_details)->car_plate}}</td>
                                                </tr>
                                            @endif
                                            <tr class="highlight d-flex">
                                                <td class="field">Marka</td>
                                                <td>@if (\App\Helper\Utility::decode_info($existingOrder->car_details)->car_brand){{\App\Helper\Utility::decode_info($existingOrder->car_details)->car_brand}}@endif</td>
                                            </tr>
                                            <tr class="highlight">
                                                <td class="field">Modelis</td>
                                                <td>@if (\App\Helper\Utility::decode_info($existingOrder->car_details)->car_model){{\App\Helper\Utility::decode_info($existingOrder->car_details)->car_model}}@endif</td>
                                            </tr>
                                            <tr class="highlight">
                                                <td class="field">Izlaiduma gads</td>
                                                <td>@if (\App\Helper\Utility::decode_info($existingOrder->car_details)->car_release_year){{\App\Helper\Utility::decode_info($existingOrder->car_details)->car_release_year}}@endif</td>
                                            </tr>
                                            <tr class="highlight">
                                                <td class="field">Dzinēja tilpums</td>
                                                <td>@if (\App\Helper\Utility::decode_info($existingOrder->car_details)->car_engine_size){{\App\Helper\Utility::decode_info($existingOrder->car_details)->car_engine_size}}@endif</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <hr>
                                    @endif
                                    <h4>Pasūtītās preces</h4>
                                    @foreach (\App\Helper\Utility::decode_info($existingOrder->order_details)->products as $item)
                                        <div class="cart-item-table cart-item-container">
                                            <div class="item-name cart-item-name">
                                                <a href="{{ $item->url }}" data-id_customization="0" style="text-transform: uppercase;">{{ strtoupper($item->name) }}</a>
                                                <br>
                                                <span class="item-price">€ {{ $item->price }} x {{$item->quantity}}</span>
                                                <br>
                                            </div>
                                            <div class="tire-price">
                                                <div class="price">
                                                    <span class="product-price" data-product-id="{{ $item->id }}">
                                                      <strong>€ {{ round($item->price * $item->quantity) }}</strong>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if (isset($existingOrder->delivery_method) && $existingOrder->mounting_price > 0)
                                        <div class="cart-item-table cart-item-container">
                                            <div class="item-name cart-item-name">
                                                Riepu montāža
                                            </div>
                                            <div class="tire-price">
                                                <div class="price">
                                                    <span class="product-price">
                                                      <strong>€ {{ $existingOrder->mounting_price }}</strong>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if (isset($existingOrder->delivery_city))
                                        <div class="cart-item-table cart-item-container">
                                            <div class="item-name cart-item-name">
                                                Piegāde
                                            </div>
                                            <div class="tire-price">
                                                <div class="price">
                                                    <span class="product-price">
                                                        @if (is_null($existingOrder->delivery_price))
                                                            <strong>Bezmaksas!</strong>
                                                        @else
                                                            <strong>€ {{ $existingOrder->delivery_price }}</strong>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($existingOrder->discount_value > 0)
                                        <div class="cart-item-table cart-item-container">
                                            <div class="item-name cart-item-name">
                                                Atlaižu kods
                                            </div>
                                            <div class="tire-price">
                                                <div class="price">
                                                  <span class="product-price">
                                                      @if ($existingOrder->discount_type == 'fixed')
                                                          <strong>€ -{{ $existingOrder->discount_value }}</strong>
                                                      @else
                                                          <strong>€ -{{ ($existingOrder->total_price * $existingOrder->discount_value) / 100 }}</strong>
                                                      @endif
                                                  </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <hr>
                                    <div class="cart-item-table cart-item-container">
                                        <div class="item-name cart-item-name">
                                            Kopā
                                        </div>
                                        <div class="tire-price">
                                            <div class="price">
                                                <span class="product-price">
                                                    <strong>€ {{ $finalPrice }}</strong>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <form method="post" class="checkout-buttons" style="display: block!important;">
                                        @csrf
                                        @if (!isset($existingOrder->delivery_city) || $existingOrder->delivery_city == 1)
                                            <div class="form-check">
                                                <input type="radio" value="1" id="paymentCheck1" name="payment" required checked>
                                                <label for="paymentCheck1">
                                                    Apmaksa saņemšanas brīdī
                                                </label>
                                            </div>
                                        @endif
                                        <div class="form-check">
                                            <input type="radio" value="2" id="paymentCheck2" name="payment" required @if (isset($existingOrder->delivery_city) && $existingOrder->delivery_city != 1) checked @endif>
                                            <label for="paymentCheck2">
                                                Bankas pārskaitījums
                                            </label>
                                        </div>
                                        @if (count($categories) == 1 && !in_array('red', $availabilities))
                                            <div class="form-check">
                                                <input type="radio" value="3" id="paymentCheck3" name="payment" required checked>
                                                <label for="paymentCheck3">
                                                    Tiešsaistes apmaksa
                                                </label>
                                            </div>
                                        @endif
                                        <hr>
                                        <div class="btn-checkout-group" role="group">
                                            <a href="{{ route('cart') }}" class="btn-secondary btn-checkout">Labot grozu</a>
                                            <a href="{{ route('shop.credentials') }}" class="btn-secondary btn-checkout">Labot datus</a>
                                            @if (count($categories) == 1 && !in_array('red', $availabilities))
                                                <button type="submit" name="pay" value="pay" class="btn-checkout-primary btn-checkout">Apmaksāt</button>
                                            @else
                                                <button type="submit" name="end" value="end" class="btn-checkout-primary btn-checkout">Turpināt</button>
                                            @endif
                                        </div>
                                    </form>
                                </div>

                            </div>

                        </div>
                    </section>
                </div>
            </div>
            @include('components.right-sidebar')
        </div>
    </div>

    {{--  <script>--}}
    {{--    window.history.replaceState({}, '',window.location.href);--}}
    {{--  </script>--}}

@endsection

