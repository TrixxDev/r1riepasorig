@extends('layouts.app')

@section('body-title', 'authentication')
@section('title', 'lang-' . app()->getLocale() . ' country-' . app()->getLocale() . ' layout-right-column page-authentication tax-display-enabled page-customer-account')

@section('content')
  <div class="container">
    <div class="row">
      <div class="main-content clearfix col-md-12 col-xl-10">
        <div id="content-wrapper" class="right-column col-lg-12">
          <section id="main">
            <section id="content" class="page-content card card-block">
              <section class="register-form">
                <h1 class="registration-form-title text-center">
                  Izveidot profilu
                </h1>
                <form action="{{ route('register') }}" id="customer-form" class="js-customer-form" method="post">
                  <section>
                    @csrf
                    <div class="form-group row ">
                      <label class="col-md-3 form-control-label required">
                        Vārds
                      </label>
                      <div class="col-md-6">
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                               name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                        @error('name')
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
                        Uzvārds
                      </label>
                      <div class="col-md-6">
                        <input id="surname" type="text" class="form-control @error('surname') is-invalid @enderror"
                               name="surname" value="{{ old('surname') }}" required autocomplete="surname" autofocus>

                        @error('surname')
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
                        Epasts
                      </label>
                      <div class="col-md-6">
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" required autocomplete="email">

                        @error('email')
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
                        Kontakttālrunis
                      </label>
                      <div class="col-md-6">
                        <input id="email" type="text" class="form-control @error('phone') is-invalid @enderror"
                               name="phone" value="{{ old('phone') }}" required autocomplete="phone">

                        @error('phone')
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
                        Parole
                      </label>
                      <div class="col-md-6 password-show">
                        <input type="password" id="password" class="form-control password-confirmation" @error('password') is-invalid
                               @enderror name="password" required autocomplete="new-password">
                        <div class="input-group-append password-eye">
                          <span class="input-group-text">
                            <i class="fa fa-eye-slash"></i>
                          </span>
                        </div>
                      </div>
                      <div class="col-md-3 form-control-comment">
                      </div>
                    </div>
                    <div class="form-group row ">
                      <label class="col-md-3 form-control-label required">
                        Parole vēlreiz
                      </label>
                      <div class="col-md-6">
                        <div class="input-group js-parent-focus">
                          <input id="password-confirm" type="password" class="form-control password-confirmation" name="password_confirmation"
                                 required autocomplete="new-password">
                        </div>
                      </div>
                      <div class="col-md-3 form-control-comment">
                      </div>
                    </div>
                    <div class="form-group row password-error" style="display: none;">

                      <div class="col-md-3 form-control-label"></div>
                      <div class="col-md-6">
                        <span class="invalid-feedback" role="alert">
                          <strong>Paroles Nesakrīt!</strong>
                        </span>
                      </div>
                      <div class="col-md-3 form-control-comment">
                      </div>
                    </div>

                    <div class="form-group row short-password" style="display: none;">
                      <div class="col-md-3"></div>
                      <div class="col-md-6">
                        <span class="invalid-feedback" role="alert">
                          <strong>Minimālais paroles garums 8 rakstzīmes!</strong>
                        </span>
                      </div>
                      <div class="col-md-3">
                      </div>
                    </div>

                    <div class="form-group row invalid-password">
                      <div class="col-md-3"></div>
                      <div class="col-md-6">
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                          <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                      </div>
                      <div class="col-md-3">
                      </div>
                    </div>
                  </section>
                  <footer class="form-footer clearfix text-center">
                    <input type="hidden" name="submitCreate" value="1">
                    <button class="btn btn-primary form-control-submit" data-link-action="save-customer" type="submit"
                            disabled>
                      Izveidot profilu
                    </button>
                  </footer>
                </form>
                <p class="text-center pt-1">Jau ir profils? <a href="{{ route('login') }}">Ienākt!</a></p>
              </section>
            </section>
          </section>
        </div>
      </div>
      @include('components.right-sidebar')
    </div>
  </div>
@endsection


