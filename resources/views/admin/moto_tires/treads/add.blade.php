@extends('admin.layouts.app')

@section('content')

    <div class="container-fluid">
        <div class="fade-in">
            <div class="card">
                <form class="form-horizontal" action="{{ route('admin.moto.treads.store') }}" method="post" enctype="multipart/form-data">
                    <div class="card-header">Pievienot riepas modeli</div>
                    <div class="card-body">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="text-input">Modeļa nosaukums</label>
                            <div class="col-md-9">
                                <input class="form-control" id="text-input" type="text" name="tread_title" placeholder="Modeļa nosaukums">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="brand-input">Brends</label>
                            <div class="col-md-9">
                                <select name="tread_brand" class="form-control" id="brand-input">
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->brand_id }}">{{ strtoupper($brand->title) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="description-input">Modeļa apraksts</label>
                            <div class="col-md-9">
                                <textarea name="tread_desc" class="form-control" id="description-input" cols="30" rows="10"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="file-input">Modeļa bilde</label>
                            <div class="col-md-9">
                                <input id="file-input" type="file" name="tread_image">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-3"></div>
                            <div class="preview-image col-md-9">
                                <img style="width: 300px; height: 300px;" src="/storage/app/public/no_image.png">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
                        <a class="btn btn-md btn-info" href="{{ route('admin.moto.treads') }}"> Atpakaļ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
