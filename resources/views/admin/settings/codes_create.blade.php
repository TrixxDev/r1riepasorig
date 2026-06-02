@extends('admin.layouts.app')

@section('content')

  <div class="container-fluid">
    <div class="fade-in">
      @if (session('success'))
        <div class="alert alert-success">
          {{ session('success') }}
        </div>
      @endif
      @if ($errors->any())
        <div class="alert alert-danger">
          There were some problems with your input.<br><br>
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
      <div class="card">
        <form class="form-horizontal" action="{{ route('admin.settings.codes.store') }}" method="post" enctype="multipart/form-data">
          @csrf
          <div class="card-header">Riepu kodu paskaidrojumi

          </div>
          <div class="card-body">

            <div class="form-group">
              <label for="name">Nosaukums</label>
              <input id="name" name="name" type="text" class="form-control" maxlength="50">
            </div>

            <div class="form-group">
              <label for="explanation">Paskaidrojums</label>
              <input id="explanation" name="explanation" type="text" class="form-control" maxlength="150">
            </div>

          </div>
          <div class="card-footer">
            <button class="btn btn-md btn-success" type="submit"> Saglabāt</button>
            <a class="btn btn-md btn-outline-secondary" href="{{ route('admin.settings.codes') }}"> Atpakaļ</a>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection
