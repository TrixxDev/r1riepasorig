@extends('admin.layouts.app')

@section('content')

    <div class="container-fluid">
        @if (session('danger'))
          <div class="alert alert-danger">
            {{ session('danger') }}
          </div>
        @endif
        <div class="fade-in">
            <div class="card">
                <form class="form-horizontal" action="{{ route('admin.quadr.brands.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card-header">Pievienot jaunu moto riepu brendu</div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="hf-email">Brenda nosaukums</label>
                            <div class="col-md-9">
                                <input class="form-control" id="text-input" type="text" name="brand_title" placeholder="Brenda nosaukums">
                            </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-md-3 col-form-label" for="description-input">Brenda apraksts</label>
                          <div class="col-md-9">
                            <textarea name="brand_desc" class="form-control" id="description-input" cols="30" rows="10" placeholder="Brenda apraksts"></textarea>
                          </div>
                        </div>
{{--                        <div class="form-group row">--}}
{{--                            <label class="col-md-3 col-form-label" for="file-input">Brenda bilde</label>--}}
{{--                            <div class="col-md-9">--}}
{{--                                <input id="file-input" type="file" name="brand_image">--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="form-group row">--}}
{{--                            <div class="col-md-3"></div>--}}
{{--                            <div class="preview-image col-md-9">--}}
{{--                                <img style="width: 300px; height: 300px;" src="/storage/app/public/no_image.png">--}}
{{--                            </div>--}}
{{--                        </div>--}}
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
                        <a class="btn btn-md btn-info" href="{{ route('admin.quadr.brands') }}"> Atpakaļ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
