@extends('admin.layouts.app')

@section('content')

  <div class="container">
    <div class="fade-in">
      @if (session('danger'))
        <div class="alert alert-danger">
          {{ session('danger') }}
        </div>
      @endif
      <div class="card">
        <form class="form-horizontal" action="{{ route('admin.settings.roles.insert') }}" method="post" enctype="multipart/form-data">
          <div class="card-header">Lomas izveide
            <div style="float: right; position: relative; top: -7px;">
              <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
              <a class="btn btn-md btn-info" href="{{ route('admin.settings.roles') }}"> Atpakaļ</a>
            </div>
          </div>
          <div class="card-body">
            @csrf
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="role">Lomas nosaukums</label>
              <div class="col-md-9">
                <input class="form-control" id="role" type="text" required name="role" placeholder="Lomas nosaukums">
              </div>
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
            <a class="btn btn-md btn-info" href="{{ route('admin.settings.roles') }}"> Atpakaļ</a>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection
