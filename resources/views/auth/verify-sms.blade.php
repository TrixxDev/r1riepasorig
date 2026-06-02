@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="main-content clearfix col-md-12 col-xl-10">
                <div id="content-wrapper" class="right-column col-lg-12">
                    <section id="main">
                        <div class="cart-grid row">

                            <div class="card cart-card text-center">
                                <div style="display: inline-flex;">
                                    <h1 class="done-cart-title">{{ __('Apstipriniet savu telefona numuru') }}</h1>
                                </div>

                                @if (session('resent'))
                                    <h2>Jauns verifikācijas kods izsūtīts uz jūsu telefona numuru.</h2>
                                @endif

                                <div class="done-cart-subtext">

                                    <form method="POST" action="{{ route('verification.verify') }}">
                                        @csrf

                                        <div class="form-group">
                                            <label for="sms_code">{{ __('Verifikācijas kods') }}</label>
                                            <input id="sms_code" type="text" class="form-control @error('sms_code') is-invalid @enderror" name="sms_code" required autofocus>

                                            @error('sms_code')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">
                                                Apstiprināt
                                            </button>
                                        </div>
                                    </form>

                                    Pirms turpinām, pārbaudiet savā mobilajā ierīcē īsziņu ar verifikācijas kodu un verificējiet sevi<br>
                                    Ja neesat saņēmuši kodu,
                                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">nospiediet šeit, lai saņemtu jaunu kodu</button>.
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

@endsection