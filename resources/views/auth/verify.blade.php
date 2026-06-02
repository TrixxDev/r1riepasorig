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
                <h1 class="done-cart-title">{{ __('Apstipriniet savu e-pasta adresi') }}</h1>
              </div>

              @if (session('resent'))
                <h2>Jauna verifikācijas saite izsūtīta uz jūsu e-pastu.</h2>
              @endif

              <div class="done-cart-subtext">
                Pirms turpinām, pārbaudiet savu e-pastu un verificējiet sevi<br>
                Ja neesat saņēmuši vēstuli,
                <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                  @csrf
                  <button type="submit" class="btn btn-link p-0 m-0 align-baseline">nospiediet šeit, lai saņemtu jaunu saiti</button>.
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
