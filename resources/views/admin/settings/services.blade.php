@extends('admin.layouts.app')

@section('content')

    <div class="container-fluid">
        <div class="fade-in">

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <form class="form-horizontal services_form" method="post">
                            @csrf
                            <input type="hidden" name="service_id">
                            <div class="card-header">Pakalpojumi</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <input class="form-control" type="text" name="service" placeholder="Pakalpojums">
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                  <div class="col-md-12">
                                    <input class="form-control" type="text" name="pdf_service" placeholder="PDF Apraksts">
                                  </div>
                                </div>
                                <br>
                                <div class="row justify-content-between col-1 align-items-center">
                                  <label class="center" for="f_save">Glabāšana</label>
                                  <input type="checkbox" name="f_save" id="f_save">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-sm btn-primary service_add_button" type="submit"> Izveidot</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <ul class="list-group bg-white services-list">
                        @if ($services->isEmpty())
                            <li class="list-group-item d-flex justify-content-center align-items-center no-services"><b>Nav neviena pakalpojuma</b></li>
                        @else
                            @foreach ($services as $service)
                            <li id="service_{{ $service->service_id }}" class="services list-group-item d-flex justify-content-between align-items-center">
                                <span class="service_title" data-desc="{{ $service->pdf_title }}" @if (!is_null($service->f_save)) data-save="1" @endif>{{ $service->title }}</span>
                                <div class="options" style="display: inline-flex; align-items: center;">
                                    @if ($service->f_ac !== NULL)
                                      <span style="margin-right: 5px;">Pusrindā</span>
                                      <input type="checkbox" class="service_active" data-service-id="{{ $service->service_id }}" @if ($service->f_ac !== 0) checked @endif name="f_ac">
                                    @endif
                                    @if ($service->f_moto !== NULL)
                                      <span style="margin-right: 5px;">Pusrindā</span>
                                      <input type="checkbox" class="service_active" data-service-id="{{ $service->service_id }}" @if ($service->f_moto !== 0) checked @endif name="f_moto">
                                    @endif
                                    <span style="margin: 0 5px;">Ieslēgts</span>
                                    <input type="checkbox" class="service_enable" data-service-id="{{ $service->service_id }}" @if ($service->enabled) checked @endif name="service_enable">
                                    <a href="{{ route('admin.settings.services.edit', $service->service_id) }}" style="margin-left: 10px;" class="edit badge bg-primary rounded-pill service-edit">Labot</a>
                                    <a href="{{ route('admin.settings.services.destroy', $service->service_id) }}" style="margin-left: 10px;" class="destroy badge bg-primary rounded-pill service-delete">Dzēst</a>
                                </div>
                            </li>
                            @endforeach
                        @endif
                    </ul>

                </div>
            </div>
        </div>
    </div>

@endsection
