@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="main-content clearfix col-md-12 col-xl-10">
            <div id="content-wrapper" class="right-column col-lg-12">
                <section id="main">
                    <header class="page-header">
                        <h1>
                            Paskaidrojumi
                        </h1>
                    </header>
                    <section id="content" class="page-content page-cms page-cms-11">
                        <p style="overflow-x: auto;"><img src="{{ asset('img/cms/Parametri_uz_riepas_saniem_679x480.jpg') }}" alt="" width="679" height="480"></p>
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
