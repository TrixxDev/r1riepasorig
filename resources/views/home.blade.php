@extends('layouts.app')

@section('body-title', 'index')
@section('title', 'lang-' . app()->getLocale() . ' country-' . app()->getLocale() . ' layout-right-column page-index tax-display-enabled')
@section('meta_title', 'R1 Riepu Serviss | Riepas un diski')
@section('meta_description', 'R1 Riepu Serviss — riepas, diski un serviss. Online katalogs un e‑pieraksts.')

@section('content')
    <div class="container">
        <div class="row">
            <div class="main-content clearfix col-md-12 col-xl-10">

                <div id="content-wrapper" class="right-column col-lg-12">

                    <section id="main">

                        <section id="content" class="page-home">

                            <div class="home-custom-banner">

                                <section class="slider" style="height: 482.043px;">
                                    <div class="navContainer">
                                        <div class="leftClass"></div>
                                    </div>

                                    <img
                                        src="{{ asset('images/slide1.jpg') }}"
                                        alt="" width="100%" height="100%" class="left">

                                    <figcaption class="caption" style="display:none !important;">
                                        <h2 class="display-1 text-uppercase">1-slide</h2>
                                        <div class="caption-description"></div>
                                    </figcaption>


                                    <img
                                        src="{{ asset('images/slide2.jpg') }}"
                                        alt="" width="100%" height="100%" class="center">

                                    <figcaption class="caption" style="display:none !important;">
                                        <h2 class="display-1 text-uppercase">2-slide</h2>
                                        <div class="caption-description"></div>
                                    </figcaption>


                                    <img
                                        src="{{ asset('images/slide3.jpg') }}"
                                        alt="" width="100%" height="100%" class="right">

                                    <figcaption class="caption" style="display:none !important;">
                                        <h2 class="display-1 text-uppercase">3-slide</h2>
                                        <div class="caption-description"></div>
                                    </figcaption>


                                    <div class="navContainer right">
                                        <div class="rightClass"></div>
                                    </div>
                                </section>

                            </div>

                            <link rel="stylesheet" type="text/css" href="{{ asset('css/sliderstyle.css') }}">
                            <script type="text/javascript"
                                    src="https://cdnjs.cloudflare.com/ajax/libs/jquery.transit/0.9.12/jquery.transit.min.js"></script>
                            <script type="text/javascript" src="{{ asset('js/slider.jquery.js') }}"></script>

                        </section>


                        <footer class="page-footer">

                            <!-- Footer content -->

                        </footer>

                    </section>

                </div>

            </div>

            @include('components.right-sidebar')
        </div>
    </div>
@endsection
