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
        <form class="form-horizontal" action="{{ route('admin.rims.update', $rim->rim_id) }}" method="post" enctype="multipart/form-data">
          <div class="card-header">{{ 'Labot info - ' . $rim->fullTitle . ' | ' . $rim->d1 . '*' . $rim->d3 }}
            <div style="float: right; position: relative; top: -7px;">
              <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
              <a class="btn btn-md btn-info" href="{{ route('admin.quadrims.search', $rim->make_id) }}"> Atpakaļ</a>
            </div>
          </div>
          <div class="card-body">
            @csrf
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="d1">Diska platums collās</label>
              <div class="col-md-9">
                <input class="form-control" id="d1" type="text" @if ($rim->d1) value="{{ $rim->d1 }}" @endif name="d1" placeholder="Diska platums collās">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="d3">Radiuss</label>
              <div class="col-md-9">
                <input class="form-control" id="d3" type="text" @if ($rim->d3) value="{{ $rim->d3 }}" @endif name="d3" placeholder="Diska radiuss">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="shop_price">Veikala cena</label>
              <div class="col-md-9">
                <input class="form-control" id="shop_price" type="number" @if ($rim->price1) value="{{ $rim->price1 }}" @endif name="price1" placeholder="Veikala cena">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="discount_price">Akcijas cena</label>
              <div class="col-md-9">
                <input class="form-control" id="discount_price" type="number" @if ($rim->price2) value="{{ $rim->price2 }}" @endif name="price2" placeholder="Akcijas cena">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="skr">Skrūves</label>
              <div class="col-md-9">
                <input class="form-control" id="skr" type="number" @if ($rim->skr) value="{{ $rim->skr }}" @endif name="skr" placeholder="Skrūves">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="pcd">Skrūvju attālums</label>
              <div class="col-md-9">
                <input class="form-control" id="pcd" type="number" @if ($rim->pcd) value="{{ $rim->pcd }}" @endif name="pcd" placeholder="Skrūvju attālums">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="et">ET</label>
              <div class="col-md-9">
                <input class="form-control" id="et" type="text" @if ($rim->et) value="{{ $rim->et }}" @endif name="et" placeholder="ET">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="color">Krāsa</label>
              <div class="col-md-9">
                <input class="form-control" id="color" type="text" @if ($rim->color) value="{{ $rim->color }}" @endif name="color" placeholder="Krāsa">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="comment">Piezīmes</label>
              <div class="col-md-9">
                <input class="form-control" id="comment" type="text" @if ($rim->comment) value="{{ $rim->comment }}" @endif name="comment" placeholder="Piezīmes">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="information">Informācija</label>
              <div class="col-md-9">
                <input class="form-control" id="information" type="text" @if ($rim->acomment) value="{{ $rim->acomment }}" @endif name="acomment" placeholder="Informācija">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="article">Artikuls</label>
              <div class="col-md-9">
                <input class="form-control" id="article" type="text" @if ($rim->article) value="{{ $rim->article }}" @endif name="article" placeholder="Artikuls">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="quantity">Atlikums</label>
              <div class="col-md-9">
                <input class="form-control" id="quantity" type="number" value="{{ $rim->quantity }}" name="quantity" placeholder="Atlikums">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="urs_quantity">Ulbrokā</label>
              <div class="col-md-9">
                <input class="form-control" id="urs_quantity" type="number" value="{{ $rim->urs_quantity }}" name="urs_quantity" placeholder="Atlikums ulbrokā">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="krs_quantity">Kalnciema ielā</label>
              <div class="col-md-9">
                <input class="form-control" id="krs_quantity" type="number" value="{{ $rim->krs_quantity }}" name="krs_quantity" placeholder="Atlikums kalnciema ielā">
              </div>
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
            <a class="btn btn-md btn-info" href="{{ route('admin.quadrims.search', $rim->make_id) }}"> Atpakaļ</a>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection
