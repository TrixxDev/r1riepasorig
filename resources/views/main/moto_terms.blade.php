@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="main-content clearfix col-md-12 col-xl-10">
                <div id="content-wrapper" class="right-column col-lg-12">
                    <section id="main">
                        <header class="page-header">
                            <h1>
                                Moto pārvadājumi
                            </h1>
                        </header>
                        <section id="content" class="page-content page-cms page-cms-13">
                            <h4><b>AR SAVU MOCI PA EIROPU</b></h4>
                            <p>Pavasarīgs sveiciens visiem Motoceļotājiem! Jau ceturto gadu organizējam moto pārvadājumus uz Milānu.<br> Sīkāka informācija apskatāma zemāk.<br> Šeit apskatāma viena brauciena atskaite:<br> <a href="http://www.motopower.lv/member_gallery.php?gal_id=11654" target="_blank">Skatīt rakstu</a><br> cita brauciena video:<br> <a href="https://www.youtube.com/watch?v=VsDVOovVZI0" target="_blank">Skatīt video</a><br> un šeit diskusija par šo tēmu:<br> <a href="http://www.motopower.lv/forum_topic.php?topic=16468&amp;start=0" target="_blank">Skatīt rakstu</a></p>
                            <p></p>
                            <h4 style="font-weight: normal;">Zemāk ir šā gada braucienu plāns. Ja Jums ir kāda interese par mūsu piedāvāto pakalpojumu, vēlmes pēc citiem datumiem vai kādi citi piedāvājumi – rakstiet: <a href="mailto:info@r1.com.lv">info@r1.com.lv</a><br> Spēks ir virzuļos un riepās!<br> Baudāmu motosezonu novēl R1 kolektīvs un Artūrs Skuja</h4>
                            <p><img src="{{ asset('img/cms/B2web15.jpg') }}" alt="" width="720" height="1946"></p>
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
