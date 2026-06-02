@extends('layouts.app')

@section('body-title', 'my-account')
@section('title', 'lang-' . app()->getLocale() . ' country-' . app()->getLocale() . ' layout-right-column page-my-account tax-display-enabled')

@section('content')

    <div class="container">
        <div class="row">
            <div class="main-content clearfix col-md-12 col-xl-10">
                <div id="content-wrapper" class="right-column col-lg-12">
                    <section id="main">
                        <header class="page-header">
                            <h1>
                                Jūsu konts
                            </h1>
                        </header>
                        <section id="content" class="page-content">
                            <aside id="notifications">
                                <div class="container">
                                </div>
                            </aside>
                            <div class="row">
                                <div class="links">

                                    <a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="identity-link" href="{{ route('identity') }}">
                                        <span class="link-item">
                                            <i class="material-icons"></i>
                                            Profila informācija
                                        </span>
                                    </a>

                                    <a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="address-link" href="{{ route('address') }}">
                                        <span class="link-item">
                                            <i class="material-icons"></i>
                                            Rekvizīti
                                        </span>
                                    </a>

                                    <a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="history-link" href="{{ route('history') }}">
                                        <span class="link-item">
                                            <i class="material-icons"></i>
                                            Pasūtījumu vēsture un detaļas
                                        </span>
                                    </a>

                                    <a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="order-slips-link" href="{{ route('order-slip') }}">
                                        <span class="link-item">
                                            <i class="material-icons"></i>
                                            Vēlmju saraksts
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </section>
                        <footer class="page-footer">
                            <div class="text-sm-center">
                                <a href="{{ route('logout') }}">
                                    Iziet
                                </a>
                            </div>
                        </footer>
                    </section>
                </div>
            </div>
            @include('components.right-sidebar')
        </div>
    </div>

@endsection
