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
                <form class="form-horizontal" action="{{ route('admin.moto.tire.update', $tire->tire_id) }}" method="post" enctype="multipart/form-data">
                    <div class="card-header">{{ 'Labot riepas info - ' . $tire->title . ' | ' . $tire->fullSize }}
                        <div style="float: right; position: relative; top: -7px;">
                            <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
                            <a class="btn btn-md btn-info" href="{{ route('admin.moto.tires.search', $tire->tread->tread_id) }}"> Atpakaļ</a>
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
                            <label class="col-md-3 col-form-label" for="d2">Augstums</label>
                            <div class="col-md-9">
                                <input class="form-control" id="d2" type="text" @if ($tire->d2) value="{{ $tire->d2 }}" @endif name="d2" placeholder="Riepas augstums">
                            </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-md-3 col-form-label" for="d4"></label>
                          <div class="col-md-9">
                            <input class="form-control" id="d4" type="text" @if ($tire->d4) value="{{ $tire->d4 }}" @endif name="d4" placeholder="Z / ZR / -">
                          </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="d3">Radiuss</label>
                            <div class="col-md-9">
                                <input class="form-control" id="d3" type="text" @if ($tire->d3) value="{{ $tire->d3 }}" @endif name="d3" placeholder="Riepas radiuss">
                            </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-md-3 col-form-label" for="tyre_type">Tips {{ $tire->type }}</label>
                          <div class="col-md-9">
                            <select name="tire_type" id="tyre_type" class="form-control">
                              <option value="">Nav</option>
                              @foreach ($tire->types() as $index => $value)
                              <option @if (strtolower($value) == strtolower($tire->type)) selected @endif value="{{ strtolower($value) }}">{{ $value }}</option>
{{--                              <option @if (strpos($value, $tire->type) !== false) selected @endif value="{{ strtolower($value) }}">{{ $value }}</option>--}}
                              @endforeach
                            </select>
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
                                <input class="form-control" id="discount_price" type="number" @if ($tire->price2) value="{{ $tire->price2 }}" @endif name="price2" placeholder="Akcijas cena">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="li">Li</label>
                            <div class="col-md-9">
                                <input class="form-control" id="li" type="number" @if ($tire->li) value="{{ $tire->li }}" @endif name="li" placeholder="Kravnesības indeks">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="si">Si</label>
                            <div class="col-md-9">
                                <input class="form-control" id="si" type="text" @if ($tire->si) value="{{ $tire->si }}" @endif name="si" placeholder="Ātruma indeks">
                            </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-md-3 col-form-label" for="is_camera">Kamera</label>
                          <div class="col-md-9">
                            <input style="width: 2%;" class="form-control" id="is_camera" @if ($tire->is_camera) checked @endif type="checkbox" name="is_camera" value="1">
                          </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="code">Kods</label>
                            <div class="col-md-9">
                                <input class="form-control" id="code" type="text" @if ($tire->code) value="{{ $tire->code }}" @endif name="code" placeholder="Kods">
                            </div>
                        </div>
{{--                        <div class="form-group row">--}}
{{--                            <label class="col-md-3 col-form-label" for="eco">Degvielas ekonomija</label>--}}
{{--                            <div class="col-md-9">--}}
{{--                                <input class="form-control" id="eco" type="text" @if ($tire->eco) value="{{ $tire->eco }}" @endif name="eco" placeholder="Degvielas ekonomija">--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="form-group row">--}}
{{--                            <label class="col-md-3 col-form-label" for="wet">Slapjšs segums</label>--}}
{{--                            <div class="col-md-9">--}}
{{--                                <input class="form-control" id="wet" type="text" @if ($tire->wet) value="{{ $tire->wet }}" @endif name="wet" placeholder="Slapjšs segums">--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="form-group row">--}}
{{--                            <label class="col-md-3 col-form-label" for="noise">Skaļums</label>--}}
{{--                            <div class="col-md-9">--}}
{{--                                <input class="form-control" id="noise" type="text" @if ($tire->noise) value="{{ $tire->noise }}" @endif name="noise" placeholder="Skaļums">--}}
{{--                            </div>--}}
{{--                        </div>--}}
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="comment">Komentārs</label>
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
                            <label class="col-md-3 col-form-label" for="article">Artikuls</label>
                            <div class="col-md-9">
                                <input class="form-control" id="article" type="text" @if ($tire->article) value="{{ $tire->article }}" @endif name="article" placeholder="Artikuls">
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
                            <label class="col-md-3 col-form-label" for="i3quantity">I3 atlikums</label>
                            <div class="col-md-9">
                              <input class="form-control" id="i3quantity" type="number" style="cursor: default;" readonly value="{{ $i3stock->quantity }}">
                            </div>
                          </div>
                        @endif
                        <div class="form-group row">
                          <label class="col-md-3 col-form-label" for="duellarticle">Duell artikuls</label>
                          <div class="col-md-9">
                            <input class="form-control" id="duellarticle" type="text" @if ($duellstock) value="{{ $duellstock->article }}" @endif name="duellarticle" placeholder="Duell artikuls">
                          </div>
                        </div>
                        @if ($duellstock)
                          <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="duellquantity">Duell atlikums</label>
                            <div class="col-md-9">
                              <input class="form-control" id="duellquantity" type="number" style="cursor: default;" readonly value="{{ $duellstock->quantity }}">
                            </div>
                          </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
                        <a class="btn btn-md btn-info" href="{{ route('admin.moto.tires.search', $tire->tread->tread_id) }}"> Atpakaļ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
