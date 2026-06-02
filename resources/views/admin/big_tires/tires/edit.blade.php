@extends('admin.layouts.app')

@section('content')

    <div class="container-fluid">
        <div class="fade-in">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <div class="card">
                <form class="form-horizontal" action="{{ route('admin.big.tire.update', $tire->tire_id) }}" method="post" enctype="multipart/form-data">
                    <div class="card-header">{{ 'Labot riepas info - ' . $tire->title . ' | ' . $tire->fullSize }}
                        <div style="float: right; position: relative; top: -7px;">
                            <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
                            <a class="btn btn-md btn-info" href="{{ route('admin.big.tires.search', $tire->tread->tread_id) }}"> Atpakaļ</a>
                        </div>
                    </div>
                    <div class="card-body">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="d1">Platums</label>
                            <div class="col-md-9">
                                <input class="form-control" id="d1" type="text" @if ($tire->d1) value="{{ $tire->d1 }}" @endif name="d1" placeholder="Riepas platums">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="sep"></label>
                            <div class="col-md-9">
                                <input class="form-control" id="sep" type="text" @if ($tire->sep) value="{{ $tire->sep }}" @endif name="sep">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="d2">Augstums</label>
                            <div class="col-md-9">
                                <input class="form-control" id="d2" type="text" @if ($tire->d2) value="{{ $tire->d2 }}" @endif name="d2" placeholder="Riepas augstums">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="sep2"></label>
                            <div class="col-md-9">
                                <input class="form-control" id="sep2" type="text" @if ($tire->sep2) value="{{ $tire->sep2 }}" @endif name="sep2">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="d3">Radiuss</label>
                            <div class="col-md-9">
                                <input class="form-control" id="d3" type="text" @if ($tire->d3) value="{{ $tire->d3 }}" @endif name="d3" placeholder="Riepas radiuss">
                            </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-md-3 col-form-label" for="tyre_type">Tips</label>
                          <div class="col-md-9">
                            <select name="tire_type" id="tyre_type" class="form-control">
                              <option @if ($tire->type == 'AGRO') selected @endif value="AGRO">Agro</option>
                              <option @if ($tire->type == 'IND') selected @endif value="IND">Ind</option>
                              <option @if ($tire->type == 'Truck') selected @endif value="Truck">Truck</option>
                            </select>
                          </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="li">Li</label>
                            <div class="col-md-9">
                                <input class="form-control" id="li" type="text" @if ($tire->li) value="{{ $tire->li }}" @endif name="li" placeholder="Kravnesības indeks">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="si">Si</label>
                            <div class="col-md-9">
                                <input class="form-control" id="si" type="text" @if ($tire->si) value="{{ $tire->si }}" @endif name="si" placeholder="Ātruma indeks">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="code">Kods</label>
                            <div class="col-md-9">
                                <input class="form-control" id="code" type="text" @if ($tire->code) value="{{ $tire->code }}" @endif name="code" placeholder="Kods">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="shop_price">Veikala cena</label>
                            <div class="col-md-9">
                                <input class="form-control" id="shop_price" type="number" @if ($tire->price1) value="{{ $tire->price1 }}" @endif name="price1" placeholder="Veikala cena">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="discount_price">Akcijas cena</label>
                            <div class="col-md-9">
                                <input class="form-control" id="discount_price" type="number" @if ($tire->price3) value="{{ $tire->price3 }}" @endif name="price2" placeholder="Akcijas cena">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="implemention">Pielietojums</label>
                            <div class="col-md-9">
                                <select name="implemention" id="implemention" class="form-control">
                                    <option @if ($tire->implemention == 'CEĻA VELTŅI') selected @endif value="CEĻA VELTŅI">Ceļa veltņi</option>
                                    <option @if ($tire->implemention == 'FRONTĀLIE IEKRĀVĒJI') selected @endif value="FRONTĀLIE IEKRĀVĒJI">Frontālie iekrāvēji</option>
                                    <option @if ($tire->implemention == 'GREIDERI') selected @endif value="GREIDERI">Greideri</option>
                                    <option @if ($tire->implemention == 'IEKRĀVĒJI/ TELESKOPISKIE IEKRĀVĒJI') selected @endif value="IEKRĀVĒJI/ TELESKOPISKIE IEKRĀVĒJI">Iekrāvēji/ teleskopiskie iekrāvēji</option>
                                    <option @if ($tire->implemention == 'KOMPAKTIEKRĀVĒJI') selected @endif value="KOMPAKTIEKRĀVĒJI">Kompaktiekrāvēji</option>
                                    <option @if ($tire->implemention == 'Kravas/Autobuss') selected @endif value="Kravas/Autobuss">Kravas/autobuss</option>
                                    <option @if ($tire->implemention == 'MEŽSAIMNIECĪBA') selected @endif value="MEŽSAIMNIECĪBA">Mežsaimniecība</option>
                                    <option @if ($tire->implemention == 'MOBILIE AUTOCELTŅI') selected @endif value="MOBILIE AUTOCELTŅI">Mobilie autoceltņi</option>
                                    <option @if ($tire->implemention == 'MPT') selected @endif value="MPT">Mpt</option>
                                    <option @if ($tire->implemention == 'PIEKABES UN AGREGĀTI') selected @endif value="PIEKABES UN AGREGĀTI">Piekabes un agregāti</option>
                                    <option @if ($tire->implemention == 'PILNGUMIJAS') selected @endif value="PILNGUMIJAS">Pilngumijas</option>
                                    <option @if ($tire->implemention == 'PNEIMATISKĀS') selected @endif value="PNEIMATISKĀS">Pneimatiskās</option>
                                    <option @if ($tire->implemention == 'RITEŅU EKSKAVATORI') selected @endif value="RITEŅU EKSKAVATORI">Riteņu ekskavatori</option>
                                    <option @if ($tire->implemention == 'SMIDZINĀTĀJI') selected @endif value="SMIDZINĀTĀJI">Smidzinātāji</option>
                                    <option @if ($tire->implemention == 'TRAKTORI UN KOMBAINI') selected @endif value="TRAKTORI UN KOMBAINI">Traktori un kombaini</option>
                                    <option @if ($tire->implemention == 'ZĀLES PĻĀVĒJI / RAIDERI') selected @endif value="ZĀLES PĻĀVĒJI / RAIDERI">Zāles pļāvēji / raideri</option>
                                    <option @if ($tire->implemention == 'ĶERRAS/ RATIŅI') selected @endif value="ĶERRAS/ RATIŅI">Ķerras/ ratiņi</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="comment">Piezīmes</label>
                            <div class="col-md-9">
                                <input class="form-control" id="comment" type="text" @if ($tire->comment) value="{{ $tire->comment }}" @endif name="comment" placeholder="Komentārs">
                            </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-md-3 col-form-label" for="information">Informācija</label>
                          <div class="col-md-9">
                            <input class="form-control" id="information" type="text" @if ($tire->acomment) value="{{ $tire->acomment }}" @endif name="acomment" placeholder="Informācija">
                          </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="article">Accrual artikuls</label>
                            <div class="col-md-9">
                                <input class="form-control" id="article" type="text" @if ($tire->article) value="{{ $tire->article }}" @endif name="article" placeholder="Accrual artikuls">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="quantity">Atlikums</label>
                            <div class="col-md-9">
                                <input class="form-control" id="quantity" type="number" value="{{ $tire->quantity }}" name="quantity" placeholder="Atlikums">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="urs_quantity">Ulbrokā</label>
                            <div class="col-md-9">
                                <input class="form-control" id="urs_quantity" type="number" value="{{ $tire->urs_quantity }}" name="urs_quantity" placeholder="Atlikums ulbrokā">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="krs_quantity">Kalnciema ielā</label>
                            <div class="col-md-9">
                                <input class="form-control" id="krs_quantity" type="number" value="{{ $tire->krs_quantity }}" name="krs_quantity" placeholder="Atlikums kalnciema ielā">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="i3article">I3 artikuls</label>
                            <div class="col-md-9">
                                <input class="form-control" id="i3article" type="text" @if ($i3stock) value="{{ $i3stock->article }}" @endif name="i3article" placeholder="I3 artikuls">
                            </div>
                        </div>
                        @if ($i3stock)
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="i3stocks">I3 atlikums</label>
                            <div class="col-md-9">
                                <input class="form-control" readonly style="cursor: default;" id="i3stocks" type="text" @if ($i3stock) value="{{ $i3stock->quantity }}" @endif>
                            </div>
                        </div>
                        @endif
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="starcoarticle">Starco artikuls</label>
                            <div class="col-md-9">
                                <input class="form-control" id="starcoarticle" type="text" @if ($starcostock) value="{{ $starcostock->article }}" @endif name="starcoarticle" placeholder="Starco artikuls">
                            </div>
                        </div>
                        @if ($starcostock)
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="starcostocks">Starco atlikums</label>
                            <div class="col-md-9">
                                <input class="form-control" readonly id="starcostocks" style="cursor: default;" type="text" @if ($starcostock) value="{{ $starcostock->quantity }}" @endif>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
                        <a class="btn btn-md btn-info" href="{{ route('admin.big.tires.search', $tire->tread->tread_id) }}"> Atpakaļ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
