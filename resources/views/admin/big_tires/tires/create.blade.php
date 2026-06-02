@extends('admin.layouts.app')

@section('content')

    <div class="container-fluid">
        <div class="fade-in">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('danger'))
                <div class="alert alert-danger">
                    {{ session('danger') }}
                </div>
            @endif
            <div class="card">
                <form class="form-horizontal" action="{{ route('admin.big.tires.store', $tread->tread_id) }}" method="post" enctype="multipart/form-data">
                    <div class="card-header">Pievienot riepu - {{ $brand->title . ' ' . $tread->title }}
                        <div style="float: right; position: relative; top: -7px;">
                            <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
                            <a class="btn btn-md btn-info" href="{{ route('admin.big.tires.search', $tread->tread_id) }}"> Atpakaļ</a>
                        </div>
                    </div>
                    <div class="card-body">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="d1">Platums</label>
                            <div class="col-md-9">
                                <input class="form-control" id="d1" type="text" name="d1" placeholder="Riepas platums">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="sep"></label>
                            <div class="col-md-9">
                                <input class="form-control" id="sep" type="text" name="sep">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="d2">Augstums</label>
                            <div class="col-md-9">
                                <input class="form-control" id="d2" type="text" name="d2" placeholder="Riepas augstums">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="sep2"></label>
                            <div class="col-md-9">
                                <input class="form-control" id="sep2" type="text" name="sep2">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="d3">Radiuss</label>
                            <div class="col-md-9">
                                <input class="form-control" id="d3" type="text" name="d3" placeholder="Riepas radiuss">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="tire_type">Tips</label>
                            <div class="col-md-9">
                                <select name="tire_type" id="tyre_type" class="form-control">
                                    <option value="">Izvēlies tipu</option>
                                    <option value="AGRO">Agro</option>
                                    <option value="IND">Ind</option>
                                    <option value="Truck">Truck</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="li">Li</label>
                            <div class="col-md-9">
                                <input class="form-control" id="li" type="text" name="li" placeholder="Kravnesības indeks">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="si">Si</label>
                            <div class="col-md-9">
                                <input class="form-control" id="si" type="text" name="si" placeholder="Ātruma indeks">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="code">Kods</label>
                            <div class="col-md-9">
                                <input class="form-control" id="code" type="text" name="code" placeholder="Kods">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="shop_price">Veikala cena</label>
                            <div class="col-md-9">
                                <input class="form-control" id="shop_price" type="number" name="price1" placeholder="Veikala cena">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="discount_price">Akcijas cena</label>
                            <div class="col-md-9">
                                <input class="form-control" id="discount_price" type="number" name="price2" placeholder="Akcijas cena">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="implemention">Pielietojums</label>
                            <div class="col-md-9">
                                <select name="implemention" id="implemention" class="form-control">
                                    <option value="">Izvēlies pielietojumu</option>
                                    <option value="CEĻA VELTŅI">Ceļa veltņi</option>
                                    <option value="FRONTĀLIE IEKRĀVĒJI">Frontālie iekrāvēji</option>
                                    <option value="GREIDERI">Greideri</option>
                                    <option value="IEKRĀVĒJI/ TELESKOPISKIE IEKRĀVĒJI">Iekrāvēji/ teleskopiskie iekrāvēji</option>
                                    <option value="KOMPAKTIEKRĀVĒJI">Kompaktiekrāvēji</option>
                                    <option value="Kravas/Autobuss">Kravas/autobuss</option>
                                    <option value="MEŽSAIMNIECĪBA">Mežsaimniecība</option>
                                    <option value="MOBILIE AUTOCELTŅI">Mobilie autoceltņi</option>
                                    <option value="MPT">Mpt</option>
                                    <option value="PIEKABES UN AGREGĀTI">Piekabes un agregāti</option>
                                    <option value="PILNGUMIJAS">Pilngumijas</option>
                                    <option value="PNEIMATISKĀS">Pneimatiskās</option>
                                    <option value="RITEŅU EKSKAVATORI">Riteņu ekskavatori</option>
                                    <option value="SMIDZINĀTĀJI">Smidzinātāji</option>
                                    <option value="TRAKTORI UN KOMBAINI">Traktori un kombaini</option>
                                    <option value="ZĀLES PĻĀVĒJI / RAIDERI">Zāles pļāvēji / raideri</option>
                                    <option value="ĶERRAS/ RATIŅI">Ķerras/ ratiņi</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="comment">Piezīmes</label>
                            <div class="col-md-9">
                                <input class="form-control" id="comment" type="text" name="comment" placeholder="Komentārs">
                            </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-md-3 col-form-label" for="information">Informācija</label>
                          <div class="col-md-9">
                            <input class="form-control" id="information" type="text" name="acomment" placeholder="Informācija">
                          </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="article">Artikuls</label>
                            <div class="col-md-9">
                                <input class="form-control" id="article" type="text" name="article" placeholder="Artikuls">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="quantity">Atlikums</label>
                            <div class="col-md-9">
                                <input class="form-control" id="quantity" type="number" name="quantity" placeholder="Atlikums">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="urs_quantity">Ulbrokā</label>
                            <div class="col-md-9">
                                <input class="form-control" id="urs_quantity" type="number" name="urs_quantity" placeholder="Atlikums ulbrokā">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="krs_quantity">Kalnciema ielā</label>
                            <div class="col-md-9">
                                <input class="form-control" id="krs_quantity" type="number" name="krs_quantity" placeholder="Atlikums kalnciema ielā">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="i3article">I3 artikuls</label>
                            <div class="col-md-9">
                                <input class="form-control" id="i3article" type="text" name="i3article" placeholder="I3 artikuls">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="starcoarticle">Starco artikuls</label>
                            <div class="col-md-9">
                                <input class="form-control" id="starcoarticle" type="text" name="starcoarticle" placeholder="Starco artikuls">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
                        <a class="btn btn-md btn-info" href="{{ route('admin.big.tires.search', $tread->tread_id) }}"> Atpakaļ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
