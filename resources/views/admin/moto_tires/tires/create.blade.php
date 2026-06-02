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
        <form class="form-horizontal" action="{{ route('admin.moto.tires.store', $tread->tread_id) }}" method="post" enctype="multipart/form-data">
          <div class="card-header">Pievienot riepu - {{ $brand->title . ' ' . $tread->title }}
            <div style="float: right; position: relative; top: -7px;">
              <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
              <a class="btn btn-md btn-info" href="{{ route('admin.moto.tires.search', $tread->tread_id) }}"> Atpakaļ</a>
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
              <label class="col-md-3 col-form-label" for="d2">Augstums</label>
              <div class="col-md-9">
                <input class="form-control" id="d2" type="text" name="d2" placeholder="Riepas augstums">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="d4"></label>
              <div class="col-md-9">
                <input class="form-control" id="d4" type="text" name="d4" placeholder="R / ZR / -">
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
                  <option value="">Nav</option>
                  @foreach ((new \App\Models\Moto)->types() as $index => $value)
                  <option value="{{ strtolower($value) }}">{{ $value }}</option>
                  @endforeach
                </select>
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
              <label class="col-md-3 col-form-label" for="li">Li</label>
              <div class="col-md-9">
                <input class="form-control" id="li" type="number" name="li" placeholder="Kravnesības indeks">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="si">Si</label>
              <div class="col-md-9">
                <input class="form-control" id="si" type="text" name="si" placeholder="Ātruma indeks">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="is_camera">Kamera</label>
              <div class="col-md-9">
                <input style="width: 2%;" class="form-control" id="is_camera" type="checkbox" name="is_camera" value="1">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="code">Kods</label>
              <div class="col-md-9">
                <input class="form-control" id="code" type="text" name="code" placeholder="Kods">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="comment">Komentārs</label>
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
              <label class="col-md-3 col-form-label" for="duellarticle">Duell artikuls</label>
              <div class="col-md-9">
                <input class="form-control" id="duellarticle" type="text" name="duellarticle" placeholder="Duell artikuls">
              </div>
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
            <a class="btn btn-md btn-info" href="{{ route('admin.moto.tires.search', $tread->tread_id) }}"> Atpakaļ</a>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection
