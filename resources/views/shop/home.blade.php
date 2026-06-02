@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="main-content clearfix col-md-12 col-xl-10">
                <div id="content-wrapper" class="right-column col-lg-12">
                    <section id="main">
                        <div class="stepper-wrapper">
                        <ol class="stepper">
                          <li class="stepper-item stepper-active">
                            <h3 class="stepper-title hidden-md-down">Grozs</h3>
                          </li>
                          <li class="stepper-item">
                            <h3 class="stepper-title hidden-md-down">Dati</h3>
                          </li>
                          <li class="stepper-item">
                            <h3 class="stepper-title hidden-md-down">Maksājums</h3>
                          </li>
                          <li class="stepper-item stepper-last">
                            <h3 class="stepper-title hidden-md-down">Pabeigts</h3>
                          </li>
                        </ol>
                      </div>
                      <div class="cart-grid row">
                            <!-- Left Block: cart product informations & shpping -->
                            <div class="cart-grid-body @if ($cartCount > 0)col-xs-12 col-lg-8 @else col-xs-12 col-lg-12 @endif">
                                <!-- cart products detailed -->
                                <div class="card cart-container">
                                    <div class="card-block">
                                        <h1 class="h1">Iepirkšanās grozs</h1>
                                    </div>
                                    <hr class="separator">
                                    @if(session()->has('cart') && count(session('cart')['products']) > 0)
                                        @foreach(session('cart')['products'] as $product)
                                        <div class="cart-item-table cart-item-container">
                                          <div class="item-name cart-item-name">
                                              <a href="{{ $product['url'] }}" data-id_customization="0" style="text-transform: uppercase;">{{ $product['name'] }}</a>
                                              <br>
                                              <span class="item-price">€ {{ $product['price'] }}</span>
                                            <br>
                                          </div>
                                          <div class="qty-item">
                                            <div class="input-group bootstrap-touchspin">
                                              <span class="input-group-addon bootstrap-touchspin-prefix" style="display: none;"></span>
                                              <input class="js-cart-line-product-quantity form-control" data-down-url="" data-up-url="" data-update-url="" data-item-price="{{ $product['price'] }}" data-product-id="{{ $product['id'] ?? '' }}" type="text" value="{{ $product['quantity'] }}" name="product-quantity-spin" min="1" style="display: block;">
                                              <span class="input-group-addon bootstrap-touchspin-postfix" style="display: none;"></span>
                                              <span class="input-group-btn-vertical">
                                                  <button class="btn btn-touchspin js-touchspin js-increase-product-quantity bootstrap-touchspin-up" type="button">
                                                      <i class="material-icons touchspin-up"></i>
                                                  </button>
                                                  <button class="btn btn-touchspin js-touchspin js-decrease-product-quantity bootstrap-touchspin-down" type="button">
                                                      <i class="material-icons touchspin-down"></i>
                                                  </button>
                                              </span>
                                            </div>
                                            </div>
                                          <div class="tire-price">
                                            <div class="price">
                                              <span class="product-price" data-product-id="{{ $product['id'] ?? '' }}">
                                                <strong>€ {{ $product['quantity'] * $product['price'] }}</strong>
                                              </span>
                                            </div>
                                          </div>
                                          <div class="cart-trash">
                                            <div class="cart-line-product-actions">
                                              @if($product['id'])
                                              <a class="remove-from-cart" rel="nofollow" href="{{ route('shop.removeItem', $product['id']) }}" data-link-action="delete-from-cart" data-id-product="{{ $product['id'] }}">
                                                <i class="material-icons float-xs-left" title="Dzēst">delete</i>
                                              </a>
                                              @else
                                              <a class="remove-from-cart" rel="nofollow" href="#" data-link-action="delete-from-cart" data-id-product="{{ $product['id'] }}">
                                                <i class="material-icons float-xs-left" title="Dzēst">delete</i>
                                              </a>
                                              @endif
                                            </div>
                                          </div>
                                        </div>
                                      <hr class="separator">
                                      @endforeach
                                    @else
                                        <div class="cart-overview js-cart">
                                            <span class="no-items">Jūsu grozs ir tukšs</span>
                                        </div>
                                    @endif
                                </div>
                                <a class="label" href="@if (isset($_SERVER['HTTP_REFERER'])) {{ $_SERVER['HTTP_REFERER'] }} @else {{ 'javascript:history.back(-1)' }} @endif">
                                    <i class="material-icons">chevron_left</i>Turpināt iepirkties
                                </a>
                                <!-- shipping informations -->
                              </div>
                              @if ($cartCount > 0)
                                <form method="POST" id="cart-home-form">
                                @csrf
                                <input type="hidden" name="delivery">
                                <input type="hidden" name="fitting">
                                <input type="hidden" name="delivery_price">
                                <input type="hidden" name="fitting_price">
                                <div class="cart-grid-right col-xs-12 col-lg-4">
                                  <div class="card cart-summary">
                                    <div class="cart-delivery-choice">
                                        <h4>Saņemšanas vieta</h4>
                                        <div class="cart-options">
                                          <label class="cart-delivery-label">
                                            <input type="radio" name="data[cart_delivery_radio]" @if ($existingOrder && $existingOrder->mounting_office == 1) checked @elseif (!$existingOrder) checked @endif value="1">
                                            <span>Ulbroka, Acones iela 2A</span>
                                          </label>
                                          <label class="cart-delivery-label">
                                            <input type="radio" name="data[cart_delivery_radio]" @if ($existingOrder && $existingOrder->mounting_office == 2) checked @endif value="2">
                                            <span>Rīga, Kalnciema iela 39</span>
                                          </label>
                                          <label class="cart-delivery-label">
                                            <input type="radio" name="data[cart_delivery_radio]" @if ($existingOrder && $existingOrder->delivery_method == 2) checked @endif value="3" id="cart_delivery">
                                            <span>Piegāde</span>
                                          </label>
                                        </div>
                                        <div class="cart-montage-choice">
                                          <hr>
                                          <h4>Montāža</h4>
                                          <div class="cart-delivery-options">
                                            <label class="cart-delivery-label">
                                              <input type="radio" name="cart-montage-radio" @if ($existingOrder && !is_null($existingOrder->mounting_price)) checked @endif value="1">
                                              <span>{{ $cartCount }} {{ ($cartCount == 1) ? 'Riepai' : 'Riepām' }}</span>
                                            </label>
                                            <label class="cart-delivery-label">
                                              <input type="radio" name="cart-montage-radio" @if ($existingOrder && is_null($existingOrder->mounting_price)) checked @elseif (!$existingOrder) checked @endif value="0">
                                              <span>Montāža nebūs nepieciešama vai par to maksāšu uz vietas</span>
                                            </label>
                                          </div>
                                        </div>
                                        <div class="cart-delivery-option">
                                          <div class="form-group flex" style="flex-wrap: wrap">
                                            <label for="shipping_city" style="width: 100%; text-align: left">Piegādes pilsēta<span class="required-field"> *</span></label><br>
                                            <div id="shipping-city-group">
                                              <label class="cart-delivery-label">
                                                <input type="radio" name="data[shipping_city]" value="1" id="shipping_city_riga" title="Rīga" @if($existingOrder && $existingOrder->delivery_city == 1) checked @endif>
                                                <span>Rīga</span>
                                              </label>
                                              <label class="cart-delivery-label">
                                                <input type="radio" name="data[shipping_city]" value="3" id="shipping_city_other" title="Cits" @if($existingOrder && $existingOrder->delivery_city == 3) checked @endif>
                                                <span>Cits</span>
                                              </label>
                                            </div>
                                          </div>
                                          <div id="shipping-city-error" style="color:red"></div>
                                          <div class="form-group">
                                            <div id="shipping-address-block">
                                              <label for="shipping_address">Piegādes adrese<span class="required-field"> *</span></label>
                                              <input type="text" class="form-control" name="data[shipping_address]" id="shipping_address" value="@if ($existingOrder && !is_null($existingOrder->delivery_address)){{$existingOrder->delivery_address}}@endif" placeholder="Piegādes adrese" required>
                                            </div>
                                          </div>
                                        </div>
                                    </div>
                                  </div>
    {{--                            </div>--}}

    {{--                            <div class="cart-grid-right col-xs-12 col-lg-4">--}}
                                    <div class="card cart-summary">
                                      <div class="cart-detailed-totals">
                                        <div class="card-block">
                                          <div class="cart-summary-line" id="cart-subtotal-products">
                                                    <span class="label js-subtotal">
                                                        {{ $cartCount }} {{ ($cartCount == 1) ? 'Prece' : 'Preces' }}
                                                    </span>
                                            <span class="value">€ {{ $totalSum }}</span>
                                          </div>
                                          <div class="cart-summary-line" id="cart-subtotal-montage">
                                                      <span class="label">
                                                          Montāža
                                                      </span>
                                            <span id="shipping_price" class="value">@if ($existingOrder && $existingOrder->mounting_price) € {{ $existingOrder->mounting_price }} @else Nav @endif</span>
                                            <div><small class="value"></small></div>
                                          </div>
                                          <div class="cart-summary-line" id="cart-subtotal-shipping">
                                                    <span class="label">
                                                        Piegāde
                                                    </span>
                                            <span id="shipping_price" class="value">@if ($existingOrder && $existingOrder->delivery_price) € {{ $existingOrder->delivery_price }} @else Bezmaksas @endif</span>
                                            <div><small class="value"></small></div>
                                          </div>
                                            @if ($existingOrder && $existingOrder->discount_type == 'fixed')
                                                <div class="cart-summary-line" id="cart-subtotal-discount" style="display: block;">
                                                    <span class="label">Atlaižu kods</span>
                                                    <span id="shipping_price" class="value">€ -{{ $existingOrder->discount_value }}</span>
                                                    <div><small class="value"></small></div>
                                                </div>
                                            @elseif ($existingOrder && $existingOrder->discount_type == 'percentage')
                                                <div class="cart-summary-line" id="cart-subtotal-discount" style="display: block;">
                                                    <span class="label">Atlaižu kods</span>
                                                    <span id="shipping_price" class="value">€ {{ round($existingOrder->total_price * (1 - $existingOrder->discount_value / 100)) - $existingOrder->total_price }}</span>
                                                    <div><small class="value"></small></div>
                                                </div>
                                            @endif
                                        </div>
{{--                                        @if (!isset($existingOrder->discount_value))--}}
{{--                                            <hr class="separator">--}}
{{--                                              <div class="card-block">--}}
{{--                                                  <div class="cart-summary-line">--}}
{{--                                                      <span class="label">Atlaižu kods</span>--}}
{{--                                                      <span class="value"></span>--}}
{{--                                                  </div>--}}

{{--                                                  <div class="cart-summary-line">--}}
{{--                                                      <input type="text" class="form-control" name="data[promo_code]" @if (session('promo_error')) style="border: 1px solid #ef7272;" @endif value="" title="">--}}
{{--                                                      @if (session('promo_error'))<span class="label promo_validation" style="color: red">Kods nav derīgs</span>@endif--}}
{{--                                                      <br>--}}
{{--                                                      <button type="button" class="btn btn-primary btn-block check_promo"><span>Pārbaudīt</span></button>--}}
{{--                                                      <button type="button" class="btn btn-secondary btn-block remove_promo" style="display: none;"><span>Noņemt kodu</span></button>--}}
{{--                                                  </div>--}}
{{--                                              </div>--}}
{{--                                        @endif--}}
                                        <hr class="separator">
                                        <div class="card-block">
                                          <div class="cart-summary-line cart-total">
                                            <span class="label">Pavisam kopā: (ar PVN)</span>
                                            @if ($existingOrder)
                                                @if ($existingOrder->discount_type == 'fixed')
                                                    <span class="value">€ {{ (($existingOrder->total_price + $existingOrder->delivery_price + $existingOrder->mounting_price) - $existingOrder->discount_value) }}</span>
                                                @elseif ($existingOrder->discount_type == 'percentage')
                                                    <span class="value">€ {{ (round($existingOrder->total_price * (1 - $existingOrder->discount_value / 100)) + $existingOrder->delivery_price + $existingOrder->mounting_price) }}</span>
                                                @else
                                                    <span class="value">€ {{ $existingOrder->total_price }}</span>
                                                @endif
                                            @else
                                                  <span class="value">€ {{ $totalSum }}</span>
                                            @endif
                                          </div>

                                          <div class="cart-summary-line">
                                            <small class="label"></small>
                                            <small class="value"></small>
                                          </div>
                                        </div>
                                        <hr class="separator">
                                      </div>
                                          @if (0 === 0)
{{--                                            @if (Auth::check())--}}
                                                <div class="checkout text-sm-center card-block checkout-button">
                                                    <button type="submit" class="btn btn-primary btn-block"><span>Turpināt</span></button>
                                                </div>
{{--                                            @else--}}
{{--                                                <div class="checkout text-sm-center card-block checkout-button">--}}
{{--                                                    <div class="checkout-buttons">--}}
{{--                                                        <a href="/login" class="btn btn-primary"><span>Ienākt</span></a>--}}
{{--                                                        <a href="/register" class="btn btn-primary"><span>Reģistrēties</span></a>--}}
{{--                                                    </div>--}}
{{--                                                    <button type="button" style="font-size: 14px;" class="btn btn-primary btn-block btn-submit-form"><span>Pasūtīt nereģistrējoties</span></button>--}}
{{--                                                </div>--}}
{{--                                            @endif--}}
                                          @else
                                            <div class="checkout text-sm-center card-block checkout-button">
                                              <button type="button" class="btn btn-primary disabled" disabled="">Noformēt pasūtījumu</button>
                                            </div>
                                          @endif
                                    </div>
                                  </div>
                                </form>
                              @endif
                            <!-- Right Block: cart subtotal & cart total -->

                        </div>
                    </section>
                </div>
            </div>
        @include('components.right-sidebar')
        </div>
    </div>

@endsection

