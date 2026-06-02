@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="main-content clearfix col-md-12 col-xl-10">
                <div id="content-wrapper" class="right-column col-lg-12">
                    <section id="main">
                        <section id="content" class="page-content">
                            <div class="profile-content site-content-main profile-content__orders-width">
                                <div class="orders-header">
                                    <div class="orders-header__content clearfix" data-hj-suppress="">
                                        <div class="orders-header__title">
                                            <h2 class="orders-header__title-text">
                                                Pasūtījuma nr. {{ $order->id }}
                                            </h2>
                                            <p class="orders-header__title-date">
                                                Iesniegšanas datums:
                                                {{ date('d.m.Y.', strtotime($order->created_at)) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="orders-header__delivery">
                                        <span class="orders-header__delivery-text">
                                        {{ $receipt }}
                                        </span>
                                    </div>
                                </div>

                                <div class="orders-info" data-hj-suppress="">
                                    <div class="orders-info__column">
                                        <h5 class="orders-title">
                                            {{ $receipt }}
                                        </h5>
                                        <p class="orders-info__column-text">{{ $address }}</p>
                                    </div>
                                    <div class="orders-info__column">
                                        <h5 class="orders-title">
                                            maksājuma veids
                                        </h5>
                                        <div class="form-style">
                                            <p>
                                                {{ $pay_enum[$order->payment] }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="orders-info__column">
                                        <h5 class="orders-title">
                                            Status
                                        </h5>
                                        <div class="form-style">
                                            <p>
                                                {{ $status_enum[$order->status] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="orders-items">
                                    <div class="detailed-cart-block">
                                        <h4>
                                            Pasūtītās preces
                                        </h4>
                                        @foreach ($tires as $tire)
                                        <div class="detailed-cart-block__header clearfix">
                                            <div class="detailed-cart-block__header-column detailed-cart-block__header-column--title">
                                                Preces
                                            </div>

                                            <div class="detailed-cart-block__header-column detailed-cart-block__header-column--price">
                                                Cena
                                                <span>
                                                (gab.)
                                                </span>
                                            </div>
                                            <div class="detailed-cart-block__header-column detailed-cart-block__header-column--quantity">
                                                Daudzums
                                            </div>
                                            <div class="detailed-cart-block__header-column detailed-cart-block__header-column--total">
                                                Kopā
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @foreach ($tires as $tire)
                                    @php
                                        if (isset($tire->article)) {
                                          $tireObj = App\Models\Autotire::where('article', $tire->article)->first();
                                          if (!$tireObj) $tireObj = App\Models\Moto::where('article', $tire->article)->first();
                                          if (!$tireObj) $tireObj = App\Models\Quadr::where('article', $tire->article)->first();
                                          if (!$tireObj) $tireObj = App\Models\Bigtire::where('article', $tire->article)->first();
                                          if (!$tireObj) $tireObj = App\Models\Stud::where('article', $tire->article)->first();
                                          if (!$tireObj) $tireObj = App\Models\Rim::where('article', $tire->article)->first();
                                        }
                                    @endphp
                                    <div class="detailed-cart-block__item clearfix">

                                        @if (isset($tireObj))
                                            <div class="detailed-cart-block__item-column detailed-cart-block__item-column--title">
                                                <p class="detailed-cart-block__name">
                                                    <a target="_blank" href="{{ $tireObj->link }}">{!! $tireObj->fullName!!}</a>
                                                </p>
                                            </div>
                                        @else
                                            <div class="detailed-cart-block__item-column detailed-cart-block__item-column--title">
                                                <p class="detailed-cart-block__name">
                                                    {!! $tire->title!!}
                                                </p>
                                            </div>
                                        @endif
                                        <div class="detailed-cart-block__item-column detailed-cart-block__item-column--price">
                                            <p class="detailed-cart-block__price">
                                                {{ $tire->price }} €
                                            </p>
                                        </div>
                                        <div class="detailed-cart-block__item-column detailed-cart-block__item-column--quantity">
                                            <p class="detailed-cart-block__amount">
                                                {{$tire->quantity}} gab.
                                            </p>
                                        </div>
                                        <div class="detailed-cart-block__item-column detailed-cart-block__item-column--total">
                                            <p class="detailed-cart-block__sum-price">
                                                {{$tire->quantity * $tire->price}} €
                                            </p>
                                        </div>
                                    </div>
                                    @endforeach
                                    <div class="orders-summary">
                                        <table class="orders-summary__table">
                                            <tbody>
                                            <tr>
                                                <td class="orders-summary__table-cell">
                                                    Kopējā summa: {{ $order->price }} €
                                                </td>
                                            </tr>
                                            @if (isset($userData->shipping_city))
                                            <tr>
                                                <td class="orders-summary__table-cell">
                                                    Piegāde: {{ ($order->delivery_price != 0) ? substr($order->delivery_price, 0, -2) . ' €' : 'Bezmaksas' }}
                                                </td>
                                            </tr>
                                            @endif
                                            @if (isset($userData->fitting) && $userData->fitting == true)
                                            <tr>
                                                <td class="orders-summary__table-cell">
                                                    Montāža: {{ ($order->fit_price != 0) ? substr($order->fit_price, 0, -2) . ' €' : 'Bezmaksas' }}
                                                </td>
                                            </tr>
                                            @endif
                                            @if ($order->used_promo != 0)
                                            <tr>
                                                <td class="orders-summary__table-cell">
                                                    Atlaide: -20 €
                                                    @if ($promo->status === '1')
                                                        -{{ $order->price - $item_sum }} €
                                                    @else
                                                        @if (!is_null($promo))
                                                            -{{ $promo->value }} €
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                            @endif
                                            <tr class="orders-summary__final-price">
                                                <td class="orders-summary__table-cell">
                                                    Kopā: {{ $item_sum }} €
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
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
