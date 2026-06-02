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
        <form action="{{ route('admin.settings.users.update', $user->id) }}" class="form-horizontal" method="post" enctype="multipart/form-data">
          <div class="card-header">{{ 'Lietotāja labošana: ' . ucfirst($user->name) . ' ' . ucfirst($user->surname) }}
            <div style="float: right; position: relative; top: -7px;">
              @if ($user->id === Auth::user()->id)
              <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
              @endif
              <a class="btn btn-md btn-info" href="{{ route('admin.settings.users') }}"> Atpakaļ</a>
            </div>
          </div>
          <div class="card-body">
            @csrf
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="name">Vārds</label>
              <div class="col-md-9">
                <input class="form-control" id="name" type="text" name="name" @if ($user->name) {{ 'value=' . $user->name  }} @endif placeholder="Vārds">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="surname">Uzvārds</label>
              <div class="col-md-9">
                <input class="form-control" id="surname" type="text" name="surname" @if ($user->surname) {{ 'value=' . $user->surname  }} @endif placeholder="Uzvārds">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="username">Lietotājvārds</label>
              <div class="col-md-9">
                <input class="form-control" id="username" type="text" name="username" @if ($user->username) {{ 'value=' . $user->username }} @endif placeholder="Lietotājvārds">
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
                <input class="form-control" id="email" type="email" name="email" @if ($user->email) {{ 'value=' . $user->email  }} @endif placeholder="E-Pasts">
              </div>
            </div>
            @if ($user->id !== Auth::user()->id)
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="status">Statuss</label>
              <div class="col-md-9">
                <select name="status[]" id="status" multiple>
                  @foreach($roles as $role)
                    <option value="{{ $role->id }}" @if ($user->hasRole($role->name)) selected @endif>{{ ucfirst($role->name) }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            @endif
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
