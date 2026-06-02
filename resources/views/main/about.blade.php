@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="main-content clearfix col-md-12 col-xl-10">
                <div id="content-wrapper" class="right-column col-lg-12">
                    <section id="main">
                        <header class="page-header">
                            <h1>
                                Par i-veikalu
                            </h1>
                        </header>
                        <section id="content" class="page-content page-cms page-cms-12">
                            <h4><b>Garantija:</b></h4>
                            <p>Visām jaunām precēm ir 2 gadu garantija.</p>
                            <h4><b>Apmaksa:</b></h4>
                            <p>Pircēju ērtībām piedāvājam vairākus preces apmaksas veidus:</p>
                            <table>
                                <tbody>
                                <tr>
                                    <td><b>R1 filiālēs:</b></td>
                                    <td>Skaidrā naudā vai ar karti - samaksa tiek veikta preces saņemšanas brīdī</td>
                                </tr>
                                <tr>
                                    <td><b>Piegāde Rīgā:</b></td>
                                    <td>Skaidrā naudā</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>Bankas pārskaitījums</td>
                                </tr>
                                <tr>
                                    <td><b>Piegāde ārpus Rīgas:</b></td>
                                    <td>Bankas pārskaitījums</td>
                                </tr>
                                </tbody>
                            </table>
                            <h4><b>Piegāde:</b></h4>
                            <p>Rīgā un Salaspilī preces vērtību virs 115.00 EUR piegāde bez maksas!</p>
                            <p>Rīgā un Salaspilī preces vērtību zem 115.00 EUR piegāde par 5.00 EUR.</p>
                            <p>Preču piegāde Rīgā un Salaspilī tiek veikta darba dienās no 09:00 līdz 18:00, sestdien no 10:00 līdz 15.00.</p>
                            <h4><b>Piegādes izmaksas ārpus Rīgas un Salaspils:</b></h4>
                            <ul>
                                <li>1 motorollera riepa vai moto kamera – 5.00 EUR</li>
                                <li>1 moto riepa – 7.00 EUR</li>
                                <li>1 auto riepa – 10.00 EUR</li>
                                <li>2 auto riepas (vieglajam auto) – 12.00 EUR</li>
                                <li>2 auto riepas (4x4 vai C) – 15.00 EUR</li>
                                <li>4 riepas (vieglajam auto) – 17.00 EUR</li>
                                <li>4 riepas (4x4 vai C) – 21.00 EUR</li>
                                <li>4 vieglmetāla diski – 17.00 EUR</li>
                                <li>4 vieglmetā diski + 4 riepas – 25.00 EUR</li>
                            </ul>
                            <h4><b>Kā iepirkties?</b></h4>
                            <p>Iepirkties R1 i-veikalā ir droši, viegli un ērti.</p>
                            <ol><ol>
                                    <li><b>Atrodiet vajadzīgo preci.</b></li>
                                    <ol>
                                        <li><b>Atrodiet vajadzīgo preci.</b></li>
                                        <ul>
                                            <li>Jāieiet vajadzīgajā sadaļā (ziemas riepas, vasaras riepas vai jauni lietie diski) un jāatrod nepieciešama prece.</li>
                                        </ul>
                                        <li><b>Pievienojiet preci iepirkumu grozam.</b></li>
                                        <ul>
                                            <li>Lai pasūtītu izvēlēto preci nospiediet uz iepirkuma ratiņiem, kas atrodas pretī preces nosaukumam labajā pusē.</li>
                                        </ul>
                                        <li><b>Rediģēt pasūtījuma saturu.</b></li>
                                        <ol>
                                            <li>Papildināt iepirkumu grozu ar jaunu preci nospiediet pogu „Turpināt iepirkties”</li>
                                            <li>Izlabot izvēlētai precei nepieciešamo daudzumu – ierakstiet vēlamo daudzumu ailē „Daudzums” un nospiediet pogu „Pārrēķināt”.</li>
                                            <li>Lai izņemtu preci no iepirkuma groza nospiediet pogu „x”, kas atrodas pretī precei labajā pusē.</li>
                                            <li>Turpināt pasūtījumu ir jānospiež poga „Pasūtīt”.</li>
                                        </ol>
                                        <li><b>Pasūtījuma noformēšana.</b></li>
                                        <ol>
                                            <li>Jāaizpilda pasūtījuma forma. Ailītes ar zvaigznīti „*” ir jāaizpilda obligāti. <br>(Jūsu sniegtā informācija ir konfidenciāla un netiks izpausta trešajām personām vai izmantota kādā citā veidā).</li>
                                        </ol>
                                        <li><b>Pasūtījumu apstiprināšana.</b></li>
                                        <ol>
                                            <li>I-veikala klientu menedžeri sazināsies ar Jums iespējami ātrākā laikā, lai apstiprinātu pasūtījumu un vienotos par Jums ērtāko piegādes laiku un vietu.<br> (I-veikala klientu menedžeri strādā darba dienās no 9.00 līdz 18.00; sestdien no 10.00 līdz 15.00; svētdien – brīvs).</li>
                                        </ol></ol></ol></ol>
                            <p></p>
                            <p>Gadījumā, ja prece, ko pircējs vēlas iegādāties neatrodas noliktavā, to iespējams pasūtīt no ražotāja. Šajā gadījumā pircējam ir nepieciešams veikt priekšapmaksu 25% apmērā no preces kopējās vērtības.</p>
                            <p>Atgādinām, ka priekšapmaksa tiek uzskatīta par garantijas summu un netiek atgriezta gadījumā, ja pircējs atsakās no pasūtītās preces.</p>
                            <h4><b>Visas Internet veikala cenas norādītas ar PVN 21%.</b></h4>
                            <p>Jautājumu vai neskaidrību gadījumā, lūdzam sazināties ar mums pa tālruni 67910555 vai rakstot e-pastu uz adresi<a href="mailto:info@r1riepas.lv"> info@r1riepas.lv</a></p>
                            <h4 align="center">VEIKSMĪGUS PIRKUMUS! :)</h4>
                            <h4></h4>
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
