@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="main-content clearfix col-md-12 col-xl-10">
                <div id="content-wrapper" class="right-column col-lg-12">
                    <section id="main">
                        <header class="page-header">
                            <h1>
                                Pasūtījumu vēsture
                            </h1>
                        </header>
                        <section id="content" class="page-content">
                            <aside id="notifications">
                                <div class="container">
                                    @if (count($orders) <= 0)
                                        <article class="alert alert-warning" role="alert" data-alert="warning">
                                            <ul>
                                                <li>Jums nav neviena pasūtījuma</li>
                                            </ul>
                                        </article>
                                    @endif
                                </div>
                            </aside>
                            @if (count($orders) >= 1)
                                <h6>Šie ir pasūtījumi, kurus Jūs esiet veicis (veikusi) kopš konta izveides</h6>
                                <div class="content-block-content orders-table">
                                    <div class="orders-table__status">
                                        <div class="orders-table__header">
                                            <div class="orders-table__cell orders-table__cell--number">
                                                Pasūtījuma nr.
                                            </div>
                                            <div class="orders-table__cell orders-table__cell--submission-date">
                                                Iesniegšanas datums
                                            </div>
                                            <div class="orders-table__cell orders-table__cell--amount-and-payment-method">
                                                Summa un maksājuma veids
                                            </div>
                                            <div class="orders-table__cell orders-table__cell--state">
                                                Status
                                            </div>
                                        </div>
                                        @foreach ($orders as $order)
                                            @php
                                                $item_count = [];
                                                $item_sum = [];
                                                @$items = unserialize($order->info);
                                                //unset($items['data']);
                                                  if (isset($items['items'])) {
                                                      foreach ($items['items'] as $item) {
                                                        if (!isset($item['quantity'])) continue;
                                                        array_push($item_sum, ($item['price'] * $item['quantity']));
                                                      }
                                                  }
                                                $item_sum = array_sum($item_sum);
                                                if ($order->used_promo != 0) {
                                                    $promo = \App\Models\Promo::where('promo_id', $order->used_promo)->first();
                                                    if ($promo->status === '1') {
                                                      $item_sum = $item_sum * (1 - $promo->value / 100);
                                                    } else {
                                                      $item_sum = $item_sum - $promo->value;
                                                    }
                                                    $item_sum = round($item_sum);
                                                }
                                                if ($order->delivery_price > 0) {
                                                  $item_sum = $item_sum + (int) substr($order->delivery_price, 0, -2);
                                                } else if ($order->fit_price > 0) {
                                                  $item_sum = $item_sum + (int) substr($order->fit_price, 0, -2);
                                                }
                                            @endphp
                                            <div class="orders-table__content" data-hj-suppress="" id="orders-table-orders">
                                                <div class="orders-table__row cursor-pointer" onclick="window.location = '/my-account/history/{{ $order->id }}'">
                                                    <div class="clearfix">
                                                        <div class="orders-table__cell orders-table__cell--number">
                                                        <span class="bolded">
                                                        {{ $order->id }}
                                                        </span>
                                                        </div>
                                                        <div class="orders-table__cell orders-table__cell--submission-date">
                                                        <span>
                                                        {{ date('d.m.Y', strtotime($order->created_at)) }}.
                                                        </span>
                                                        </div>
                                                        <div class="orders-table__cell orders-table__cell--amount-and-payment-method">
                                                        <span class="bolded">
                                                            {{ $item_sum }} €
                                                        </span>
                                                            <span>
                                                            {{ $pay_enum[$order->payment] }}
                                                        </span>
                                                        </div>
                                                        <div class="orders-table__cell orders-table__cell--state">
                                                            <div class="ck-labels">
                                                                <div class="label-yellow">
                                                                    {{ $status_enum[$order->status] }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </section>
                        <footer class="page-footer">
                            <a href="{{ route('my-account') }}" class="account-link">
                                <i class="material-icons"></i>
                                <span>Atpakaļ uz Jūsu kontu</span>
                            </a>
                            <a href="{{ route('home') }}" class="account-link">
                                <i class="material-icons"></i>
                                <span>Sākumlapa</span>
                            </a>
                        </footer>
                    </section>
                </div>
            </div>
            @include('components.right-sidebar')
        </div>
    </div>

@endsection
