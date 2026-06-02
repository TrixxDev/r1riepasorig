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
        <form class="form-horizontal" action="{{ route('admin.quadrims.store', $tread->make_id) }}" method="post" enctype="multipart/form-data">
          <div class="card-header">Pievienot disku - {{ $brand->title . ' ' . $tread->title }}
            <div style="float: right; position: relative; top: -7px;">
              <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
              <a class="btn btn-md btn-info" href="{{ route('admin.quadrims.search', $tread->make_id) }}"> Atpakaļ</a>
            </div>
          </div>
          <div class="card-body">
            @csrf
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="d1">Diska platums collās</label>
              <div class="col-md-9">
                <input class="form-control" id="d1" type="text" name="d1" placeholder="Diska platums collās">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="d3">Radiuss</label>
              <div class="col-md-9">
                <input class="form-control" id="d3" type="text" name="d3" placeholder="Diska radiuss">
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
              <label class="col-md-3 col-form-label" for="skr">Skrūves</label>
              <div class="col-md-9">
                <input class="form-control" id="skr" type="number" name="skr" placeholder="Skrūves">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="pcd">Skrūvju attālums</label>
              <div class="col-md-9">
                <input class="form-control" id="pcd" type="number" name="pcd" placeholder="Skrūvju attālums">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="et">ET</label>
              <div class="col-md-9">
                <input class="form-control" id="et" type="text" name="et" placeholder="ET">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="color">Krāsa</label>
              <div class="col-md-9">
                <input class="form-control" id="color" type="text" name="color" placeholder="Krāsa">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="comment">Piezīmes</label>
              <div class="col-md-9">
                <input class="form-control" id="comment" type="text" name="comment" placeholder="Piezīmes">
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
          </div>
          <div class="card-footer">
            <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
            <a class="btn btn-md btn-info" href="{{ route('admin.quadrims.search', $tread->make_id) }}"> Atpakaļ</a>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection
