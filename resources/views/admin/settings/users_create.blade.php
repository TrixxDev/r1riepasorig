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
        <form class="form-horizontal" action="{{ route('admin.settings.users.store') }}" method="post" enctype="multipart/form-data">
          <div class="card-header">Lietotāja izveide
            <div style="float: right; position: relative; top: -7px;">
              <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
              <a class="btn btn-md btn-info" href="{{ route('admin.settings.users') }}"> Atpakaļ</a>
            </div>
          </div>
          <div class="card-body">
            @csrf
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="name">Vārds</label>
              <div class="col-md-9">
                <input class="form-control" id="name" type="text" name="name" placeholder="Vārds">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="surname">Uzvārds</label>
              <div class="col-md-9">
                <input class="form-control" id="surname" type="text" name="surname" placeholder="Uzvārds">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="username">Lietotājvārds</label>
              <div class="col-md-9">
                <input class="form-control" id="username" type="text" name="username" placeholder="Lietotājvārds">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="password">Parole</label>
              <div class="col-md-9">
                <input class="form-control" id="password" type="password" name="password" placeholder="Parole">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="email">E-Pasts</label>
              <div class="col-md-9">
                <input class="form-control" id="email" type="email" name="email" placeholder="E-Pasts">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="status">Statuss</label>
              <div class="col-md-9">
                <select name="status[]" id="status" multiple>
                  @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
            <a class="btn btn-md btn-info" href="{{ route('admin.settings.users') }}"> Atpakaļ</a>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection
