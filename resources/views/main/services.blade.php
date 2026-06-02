@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="main-content clearfix col-md-12 col-xl-10">
                <div id="content-wrapper" class="right-column col-lg-12">
                        <section id="content" class="page-content page-cms page-cms-8 p-1">
                            <p class="overflow-image text-center"><img src="{{ asset('img/cms/Darbs3.jpg') }}" alt="" width="700" height="991"></p>
                            <ul>
                                <li>Riepu uzglabāšana - 20.00 EUR/6mēn.</li>
                                <li>Riepu utilizācija - 2.00 EUR/gab.</li>
                                <li>Riepu maisi - 0.50 EUR/gab. Pērkot riepas pie mums riepu maisi bez maksas.</li>
                                <li>Riepu radžošana - 8.00 EUR/riepa.</li>
                                <li>Diska borta tīrīšana - 3.00 EUR/disks.</li>
                                <li>Riepu uzpildīšana ar slāpekli -3.00 EUR/gab.</li>
                                <li>Disku remonts - spiest šeit</li>
                                <li>Kondicionieru apkope/diagnostika - 15.00 EUR</li>
                                <li>Kondicionieru uzpilde - 20.00 EUR</li>
                            </ul>
                            <table>
                                <tbody></tbody>
                            </table>
                        </section>
                        <footer class="page-footer">
                            <!-- Footer content -->
                        </footer>
                </div>
            </div>
            @include('components.right-sidebar')
        </div>
    </div>

@endsection
