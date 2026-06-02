@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="main-content clearfix col-md-12 col-xl-10">
                <div id="content-wrapper" class="right-column col-lg-12">
                    <section id="main">
                        <header class="page-header">
                            <h1>
                                Uzņēmuma rekvizīti
                            </h1>
                        </header>
                        <section id="content" class="page-content">
                            <aside id="notifications">
                                <div class="container">
                                @if (session('success'))
                                  <article class="alert alert-success" role="alert" data-alert="warning">
                                    <ul>
                                      <li>{!! session('success') !!}</li>
                                    </ul>
                                  </article>
                                @endif
                                </div>
                            </aside>
                            <div class="address-form">
                                <div class="js-address-form">
                                    <form method="POST" action="{{ route('address_update') }}">
                                    @csrf
                                        <section class="form-fields">
                                            <div class="form-group row ">
                                                <label class="col-md-3 form-control-label">
                                                    Uzņēmuma nosaukums
                                                </label>
                                                <div class="col-md-6">
                                                    <input class="form-control" name="company" type="text" value="{{ Auth::user()->company_name }}" maxlength="100">
                                                </div>
                                                <div class="col-md-3 form-control-comment"></div>
                                            </div>
                                            <div class="form-group row ">
                                                <label class="col-md-3 form-control-label">
                                                    PVN numurs
                                                </label>
                                                <div class="col-md-6">
                                                    <input class="form-control" name="vat_number" type="text" maxlength="13" value="{{ Auth::user()->company_vat }}">
                                                </div>
                                                <div class="col-md-3 form-control-comment"></div>
                                            </div>
                                            <div class="form-group row ">
                                                <label class="col-md-3 form-control-label required">
                                                    Adrese
                                                    <span class="required-field"> *</span>
                                                </label>
                                                <div class="col-md-6">
                                                    <input class="form-control @error('address') is-invalid @enderror" name="address" type="text" value="{{ Auth::user()->company_address }}" required="">
                                                    @error('address')
                                                    <span class="invalid-feedback" role="alert">
                                                      <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-3 form-control-comment">
                                                </div>
                                            </div>
                                            <div class="form-group row ">
                                                <label class="col-md-3 form-control-label required">
                                                    Pasta indekss
                                                    <span class="required-field"> *</span>
                                                </label>
                                                <div class="col-md-6">
                                                    <input class="form-control @error('postcode') is-invalid @enderror" name="postcode" type="text" value="LV-{{ Auth::user()->company_postcode }}" maxlength="12" required="">
                                                    @error('postcode')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                      </span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-3 form-control-comment">
                                                </div>
                                            </div>
                                            <div class="form-group row ">
                                                <label class="col-md-3 form-control-label required">
                                                    Pilsēta
                                                    <span class="required-field"> *</span>
                                                </label>
                                                <div class="col-md-6">
                                                    <input class="form-control @error('city') is-invalid @enderror" name="city" type="text" value="{{ Auth::user()->company_city }}" maxlength="64" required="">
                                                    @error('city')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                      </span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-3 form-control-comment">
                                                </div>
                                            </div>
                                        </section>
                                        <footer class="form-footer clearfix">
                                            <input type="hidden" name="submitAddress" value="1">

                                            <button class="btn btn-primary float-xs-right" type="submit">
                                                Saglabāt
                                            </button>
                                        </footer>
                                    </form>
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
