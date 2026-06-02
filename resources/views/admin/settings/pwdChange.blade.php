@extends('admin.layouts.app')

@section('content')

  <div class="container">
    <div class="fade-in">
      @if (session('success'))
        <div class="alert alert-success">
          {{ session('success') }}
        </div>
      @endif
      <div class="card">
        <form class="form-horizontal" action="{{ route('admin.settings.user.pwdChange', Auth::user()->id) }}" method="post" enctype="multipart/form-data">
          <div class="card-header">Paroles maiņa
            <div style="float: right; position: relative; top: -7px;">
              <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
              <a class="btn btn-md btn-info" onclick="history.back()"> Atpakaļ</a>
            </div>
          </div>
          <div class="card-body">
            @csrf
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="password">Jaunā parole</label>
              <div class="col-md-9">
                <input class="form-control" id="password" type="password" required name="password" placeholder="Jaunā parole">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="password_again">Vēlreiz jauno paroli</label>
              <div class="col-md-9">
                <input class="form-control" id="password_again" type="password" required name="password_again" placeholder="Vēlreiz jauno paroli">
              </div>
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
            <a class="btn btn-md btn-info" onclick="history.back()"> Atpakaļ</a>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection
