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
        <form class="form-horizontal" action="{{ route('admin.settings.pages.update', $page->id) }}" method="post" enctype="multipart/form-data">
          <div class="card-header">Lapas izveide
            <div style="float: right; position: relative; top: -7px;">
              <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
              <a class="btn btn-md btn-info" href="{{ route('admin.settings.pages') }}"> Atpakaļ</a>
            </div>
          </div>
          <div class="card-body">
            @csrf
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="name">Nosaukums</label>
              <div class="col-md-9">
                <input class="form-control" id="name" type="text" name="name" @if ($page->title) value="{{ $page->title }}" @endif placeholder="Nosaukums">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="uri">URI</label>
              <div class="col-md-9">
                <input class="form-control" id="uri" type="text" name="uri" @if ($page->route) value="{{ $page->route }}" @endif placeholder="URI">
              </div>
            </div>

            <div class="form-group row">
              <label class="col-md-3 col-form-label" for="editor">Saturs</label>
              <div class="col-md-9">
                <textarea name="page_content" id="editor" cols="30" rows="30">@if (!empty($doc)) {!! $doc !!} @endif</textarea>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
            <a class="btn btn-md btn-info" href="{{ route('admin.settings.pages') }}"> Atpakaļ</a>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection
