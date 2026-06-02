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
        <form class="form-horizontal" action="{{ route('admin.studs.update', $stud->stud_id) }}" method="post" enctype="multipart/form-data">
          <div class="card-header">{{ 'Labot radžu info - ' . $stud->fullName}}
            <div style="float: right; position: relative; top: -7px;">
              <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
              <a class="btn btn-md btn-info" href="{{ route('admin.studs.search', $stud->make_id) }}"> Atpakaļ</a>
            </div>
          </div>
          <div class="card-body">
            @csrf
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="d1">Pielietojums</label>
              <div class="col-md-9">
                <select name="application[]" id="application" class="form-control" multiple>
                  <option @if (in_array(1, $applications)) selected @endif value="1">Apaviem</option>
                  <option @if (in_array(2, $applications)) selected @endif value="2">Kvadracikliem</option>
                  <option @if (in_array(3, $applications)) selected @endif value="3">Motocikliem</option>
                  <option @if (in_array(4, $applications)) selected @endif value="4">Mini traktoriem</option>
                  <option @if (in_array(5, $applications)) selected @endif value="5">Iekrāvējiem</option>
                  <option @if (in_array(6, $applications)) selected @endif value="6">Būvniecības tehnikai</option>
                  <option @if (in_array(7, $applications)) selected @endif value="7">Agro tehnikai</option>
                  <option @if (in_array(8, $applications)) selected @endif value="8">4x4 visurgājēji</option>
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="stud_length">Radzes garums</label>
              <div class="col-md-9">
                <input class="form-control" id="stud_length" type="text" @if ($stud->stud_length) value="{{ $stud->stud_length }}" @endif name="stud_length" placeholder="Radzes garums">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="stud_length">Radžu daudzums</label>
              <div class="col-md-9">
                <input class="form-control" id="stud_count" type="text" @if ($stud->stud_count) value="{{ $stud->stud_count }}" @endif name="stud_count" placeholder="Radžu daudzums">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="shop_price">Veikala cena</label>
              <div class="col-md-9">
                <input class="form-control" id="shop_price" type="number" @if ($stud->price1) value="{{ $stud->price1 }}" @endif name="price1" placeholder="Veikala cena">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="discount_price">Akcijas cena</label>
              <div class="col-md-9">
                <input class="form-control" id="discount_price" type="number" @if ($stud->price2) value="{{ $stud->price2 }}" @endif name="price2" placeholder="Akcijas cena">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="comment">Komentārs</label>
              <div class="col-md-9">
                <input class="form-control" id="comment" type="text" @if ($stud->comment) value="{{ $stud->comment }}" @endif name="comment" placeholder="Komentārs">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="information">Informācija</label>
              <div class="col-md-9">
                <input class="form-control" id="information" type="text" @if ($stud->acomment) value="{{ $stud->acomment }}" @endif name="acomment" placeholder="Informācija">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="article">Artikuls</label>
              <div class="col-md-9">
                <input class="form-control" id="article" type="text" @if ($stud->article) value="{{ $stud->article }}" @endif name="article" placeholder="Artikuls">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="quantity">Atlikums</label>
              <div class="col-md-9">
                <input class="form-control" id="quantity" type="number" value="{{ $stud->quantity }}" name="quantity" placeholder="Atlikums">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="urs_quantity">Ulbrokā</label>
              <div class="col-md-9">
                <input class="form-control" id="urs_quantity" type="number" value="{{ $stud->urs_quantity }}" name="urs_quantity" placeholder="Atlikums ulbrokā">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="krs_quantity">Kalnciema ielā</label>
              <div class="col-md-9">
                <input class="form-control" id="krs_quantity" type="number" value="{{ $stud->krs_quantity }}" name="krs_quantity" placeholder="Atlikums kalnciema ielā">
              </div>
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
            <a class="btn btn-md btn-info" href="{{ route('admin.studs.search', $stud->make_id) }}"> Atpakaļ</a>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection
