@extends('admin.layouts.app')

@section('content')

    <div class="container-fluid">
        <div class="fade-in">
            @if (session('success'))
              <div class="alert alert-success">
                {{ session('success') }}
              </div>
            @endif
            @if (session('danger'))
              <div class="alert alert-danger">
                {{ session('danger') }}
              </div>
            @endif
            <div class="card">
                <div class="card-header"> Motociklu riepas
                    <div class="card-header-actions">
                    </div>
                </div>
                <div class="card-body">
                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="form-group row brand-settings">
                                    <label class="col-md-2 col-form-label" for="brand_select">Brends: </label>
                                    <select name="brand" class="form-control col-md-3" data-model="moto" id="brand_select">
                                        @foreach ($brands as $curr_brand)
                                            <option value="{{ $curr_brand->brand_id }}" @if (isset($tread->brand_id) && $tread->brand_id == $curr_brand->brand_id) {{ 'selected' }} @endif >{{ ucwords(strtolower($curr_brand->title)) }}</option>
                                        @endforeach
                                    </select>
                                    <form method="post" style="display: flex;">
                                      @csrf
                                      <input type="text" name="brand-name" style="width: 200px; margin-left: 10px;" disabled class="form-control brand-input">
                                      <button type="button" class="btn btn-success new-brand" style="margin-left: 10px; color: white;">Izveidot</button>
                                      <button type="button" class="btn btn-warning edit-brand" style="margin-left: 10px; color: white;">Labot</button>
                                      <button class="btn btn-danger delete-brand" name="delete-brand" value="true" style="margin-left: 10px; color: white;">Dzēst</button>
                                    </form>
                                </div>
                                <div class="form-group row make-settings">
                                    <label class="col-md-2 col-form-label" for="tread_select">Modelis: </label>
                                    <select name="tread" class="form-control col-md-3" id="tread_select" disabled></select>
                                    <form method="post" style="display: flex;">
                                      @csrf
                                      <input type="text" name="make-name" style="width: 200px; margin-left: 10px;" disabled class="form-control make-input">
                                      <button type="button" class="btn btn-success new-make" style="margin-left: 10px; color: white;">Izveidot</button>
                                      <button type="button" class="btn btn-warning edit-make" style="margin-left: 10px; color: white;">Labot</button>
                                      <button class="btn btn-danger delete-make" name="delete-make" value="true" style="margin-left: 10px; color: white;">Dzēst</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 preview-image">
                                @if (isset($tread))
{{--                                  {{ dd($tread) }}--}}
                                  {!! App\Helper\Image::showGrid('moto', $tread->tread_id, 'width: 300px; margin-bottom: 20px;') !!}

                                  <form action="{{ route('admin.moto.tires.image', $tread->tread_id) }}" method="post" enctype="multipart/form-data">
                                      @csrf
                                      <div class="row">
                                          <div class="col-md-3">
                                              <input id="file-input" type="file" name="tread_image" style="margin-bottom: 10px;">
                                          </div>
                                      </div>
                                      <div class="row">
                                          <div class="col-md-3">
                                              <button class="btn btn-md btn-primary" type="submit"> Saglabāt</button>
                                              <button type="clear" class="btn btn-md btn-info"> Attīrīt</button>
                                          </div>
                                      </div>
                                  </form>
                                @endif
                            </div>
                            @if (isset($tread))
                            <div class="tread_comment">
                              <form method="POST">
                                @csrf
{{--                                <textarea style="display: none;" class="form-control comment-text" name="comment-text" cols="80" rows="13">{!! $tread->t_comment !!}</textarea>--}}
{{--                                <textarea disabled class="form-control comment-text-disabled" cols="80" rows="13">{!! $tread->t_comment !!}</textarea>--}}
  {{--                              <div class="card">--}}
  {{--                                <div class="card-body">--}}
  {{--                                  @if ($tread->t_comment != '')--}}
  {{--                                    <span class="comment-text">{{ $tread->t_comment }}</span>--}}
  {{--                                  @endif--}}
  {{--                                </div>--}}
  {{--                              </div>--}}

                                <div class="container" style="margin-top: 10px;">
                                  <!-- Nav tabs -->
                                  <ul class="nav nav-tabs">
                                    <li class="nav-item">
                                      <a class="nav-link active" data-bs-toggle="tab" href="#tread">Modelis</a>
                                    </li>
                                    <li class="nav-item">
                                      <a class="nav-link" data-bs-toggle="tab" href="#brand">Brends</a>
                                    </li>
                                  </ul>

                                  <!-- Tab panes -->
                                  <div class="tab-content">
                                    <div class="tab-pane container fade show active" id="tread">
                                      <textarea disabled class="form-control tread-comment-text" name="tread-comment-text" cols="80" rows="13">{!! $tread->t_comment !!}</textarea>
                                    </div>
                                    <div class="tab-pane container fade" id="brand">
                                      <textarea disabled class="form-control brand-comment-text" name="brand-comment-text" cols="80" rows="13">{!! $brand->b_comment !!}</textarea>
                                    </div>
                                  </div>
                                  <button type="button" style="color: white;" class="btn btn-warning tread-comment-edit">Labot</button>
                                  <button style="display: none;" type="button" style="color: white;" class="btn btn-danger tread-comment-edit-cancel">Atcelt</button>
                                </div>
                              </form>
                            </div>
                          @endif
                        </div>
                        @if (isset($tread))
                        <form method="post" action="{{ route('admin.moto.tires.delete_all', $tread->tread_id) }}">
                        @csrf
                        <div class="row justify-content-end tires-header">
                          @if (isset($tires) && count($tires) > 0)
                          <button class="btn btn-md btn-danger" style="margin-right: 10px;" onclick="if (confirm('Tiešām vēlies dzēst?') !== true) { return false; }">Dzēst</button>
                          @endif
                          <a class="btn btn-md btn-primary new_tire text-white" href="{{ route('admin.moto.tires.create', $tread->tread_id) }}">Pievienot</a>
                        </div>
                        @endif
                        @php
                        $camera = 1;
                        @endphp
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-striped table-bordered datatable dataTable no-footer" id="DataTables_Table_0" role="grid" aria-describedby="DataTables_Table_0_info" style="border-collapse: collapse !important">
                                    @if (isset($tires))
                                        @foreach ($tires as $tire)
                                            @if ($tire->is_camera == 1) @php $camera = 1 @endphp @else @php $camera = 0 @endphp @endif
                                            @if ($loop->first)
                                                <thead>
                                                <tr role="row">
                                                  <th>
                                                    <input type="checkbox" onclick="for(c in document.getElementsByName('tire_id[]')) document.getElementsByName('tire_id[]').item(c).checked = this.checked">
                                                  </th>
                                                  <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="4" aria-label="Date registered: activate to sort column ascending" style="width: 320.609px;">Izmērs</th>
                                                  @if ($camera == 0)
                                                    <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Role: activate to sort column ascending" style="width: 151.953px;">Tips</th>
                                                  @endif
                                                  <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Role: activate to sort column ascending" style="width: 151.953px;">Veikala cena</th>
                                                  <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 167.547px;">Akcijas cena</th>
                                                  <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 167.547px;">Li</th>
                                                  <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 167.547px;">Si</th>
                                                  <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 150.391px;">Kods</th>
                                                  {{--                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Degvielas ekonomija</th>--}}
                                                  {{--                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Slapjšs segums</th>--}}
                                                  {{--                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Skaļums</th>--}}
                                                  <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Kamera</th>
                                                  <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Piezīmes</th>
                                                  <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Username: activate to sort column ascending" style="width: 372.5px;">Artikuls</th>
                                                  <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" style="width: 322.391px;"></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                            @endif
                                            <tr role="row" class="odd">
                                                <td><input type="checkbox" name="tire_id[]" value="{{ $tire->tire_id }}" ></td>
                                                <td>{{ $tire->d1 }}</td>
                                                <td>{{ $tire->d2 }}</td>
                                                <td>{{ $tire->d4 }}</td>
                                                <td>{{ $tire->d3 }}</td>
                                                @if ($tire->is_camera == 0)
                                                <td>{{ $tire->typeDesc[1] }}</td>
                                                @endif
                                                <td>{{ $tire->price1 }}</td>
                                                <td style="color: red; font-weight: 500;">{{ $tire->price2 }}</td>
                                                <td>{{ $tire->li }}</td>
                                                <td>{{ $tire->si }}</td>
{{--                                                <td>{{ $tire->code }}</td>--}}
{{--                                                <td>{{ $tire->eco }}</td>--}}
{{--                                                <td>{{ $tire->wet }}</td>--}}
                                                <td>{{ $tire->code }}</td>
                                                <td><input type="checkbox" disabled @if ($tire->is_camera) checked @endif</td>
                                                <td>{{ $tire->comment }}</td>
                                                <td>{{ $tire->article }}</td>
                                                <td>
                                                    <a class="btn btn-success" href="{{ route('admin.moto.tire.edit', $tire->tire_id) }}">
                                                        <svg class="c-icon">
                                                            <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-description"></use>
                                                        </svg>
                                                    </a>
                                                    <a onclick="if (confirm('Tiešām vēlies dzēst?') === true) { window.location.href = '{{ route('admin.moto.tire.destroy', $tire->tire_id) }}' }" class="btn btn-danger">
                                                        <svg class="c-icon">
                                                            <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-trash"></use>
                                                        </svg>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                      <thead>
                                      <tr role="row">
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="4" aria-label="Date registered: activate to sort column ascending" style="width: 320.609px;">Izmērs</th>
                                        @if ($camera == 0)
                                          <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Role: activate to sort column ascending" style="width: 151.953px;">Tips</th>
                                        @endif
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Role: activate to sort column ascending" style="width: 151.953px;">Veikala cena</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 167.547px;">Akcijas cena</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 167.547px;">Li</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 167.547px;">Si</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 150.391px;">Kods</th>
                                        {{--                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Degvielas ekonomija</th>--}}
                                        {{--                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Slapjšs segums</th>--}}
                                        {{--                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Skaļums</th>--}}
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Piezīmes</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Username: activate to sort column ascending" style="width: 372.5px;">Artikuls</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" style="width: 322.391px;"></th>
                                      </tr>
                                      </thead>
                                      <tbody>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
