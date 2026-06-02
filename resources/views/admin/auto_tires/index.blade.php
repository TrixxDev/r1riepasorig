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
                <div class="card-header"> Auto riepas
                    <div class="card-header-actions">
                    </div>
                </div>
                <div class="card-body">
                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="form-group row brand-settings">
                                    <label class="col-md-2 col-form-label" for="brand_select">Brends: </label>
                                    <select name="brand" class="form-control col-md-3" data-model="auto" id="brand_select">
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
                                      <select name="make-season" style="width: 200px; margin-left: 10px; display: none;" class="form-control make-season">
                                        <option value="1" @if (isset($tread) && $tread->season == 1) selected @endif>Vasaras</option>
                                        <option value="2" @if (isset($tread) && $tread->season == 2) selected @endif>Ziemas</option>
                                      </select>
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
                                  {!! App\Helper\Image::showGrid('auto', $tread->tread_id, 'width: 300px; margin-bottom: 20px;') !!}

                                  <form action="{{ route('admin.auto.tires.image', $tread->tread_id) }}" method="post" enctype="multipart/form-data">
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
                        <form method="post" action="{{ route('admin.auto.tires.delete_all', $tread->tread_id) }}">
                        @csrf
                        <div class="row justify-content-end tires-header">
                          @if (isset($tires) && count($tires) > 0)
                            <button class="btn btn-md btn-danger" style="margin-right: 10px;" onclick="if (confirm('Tiešām vēlies dzēst?') !== true) { return false; }">Dzēst</button>
                          @endif
                          <a class="text-white btn btn-md btn-primary new_tire" href="{{ route('admin.auto.tires.create', $tread->tread_id) }}">Pievienot</a>
                        </div>
                        @endif
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-striped table-bordered datatable dataTable no-footer" id="DataTables_Table_0" role="grid" aria-describedby="DataTables_Table_0_info" style="border-collapse: collapse !important">
                                    <thead>
                                    <tr role="row">
                                        <th>
                                          <input type="checkbox" onclick="for(c in document.getElementsByName('tire_id[]')) document.getElementsByName('tire_id[]').item(c).checked = this.checked">
                                        </th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="3" aria-label="Date registered: activate to sort column ascending" style="width: 320.609px;">Izmērs</th>
                                        @if (isset($tread) && $tread->season == 2)
                                        <th rowspan="1" colspan="1" style="width: 151.953px;">Tips</th>
                                        @endif
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Role: activate to sort column ascending" style="width: 151.953px;">Veikala cena</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 167.547px;">Akcijas cena</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 167.547px;">Li</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 167.547px;">Si</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 150.391px;">Kods</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Degvielas ekonomija</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Slapjšs segums</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Skaļums</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Piezīmes</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Top40</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Username: activate to sort column ascending" style="width: 372.5px;">Artikuls</th>
                                        <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" style="width: 322.391px;"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if (isset($tires))
                                        @foreach ($tires as $tire)
                                            <tr role="row" class="odd">
                                                <td><input type="checkbox" class="tire_id" name="tire_id[]" value="{{ $tire->tire_id }}" ></td>
                                                <td>{{ $tire->d1 }}</td>
                                                <td>{{ $tire->d2 }}</td>
                                                <td>{{ $tire->d3 }}</td>
                                              @if (isset($tread) && $tread->season == 2)
                                                <td>
                                                  @switch($tire->type)
                                                    @case(1)
                                                    <span data-toggle="tooltip">
                                                      <img src="{{asset('images/ms.png')}}" alt="ms"
                                                           title="<span>Centrāleiropas tipa ziemas riepa</span>" style="margin:0;">
                                                    </span>

                                                    @break

                                                    @case(2)
                                                    <span data-toggle="tooltip">
                                                      <img src="{{asset('images/radzeb.png')}}" alt="radzojama"
                                                           title="<span>Radžojama</span>" style="margin:0;">
                                                    </span>

                                                    @break

                                                    @case(3)
                                                    <span data-toggle="tooltip">
                                                      <img src="{{asset('images/radzea.png')}}" alt="ar radzem"
                                                           title="<span>Ar radzēm</span>" style="margin:0;">
                                                    </span>

                                                    @break

                                                    @case(4)
                                                    <span data-toggle="tooltip">
                                                      <img src="{{asset('images/parsla.png')}}" alt="skandinavijas"
                                                           title="<span>Skandināvijas tipa ziemas riepa</span>" style="margin:0;">
                                                    </span>
                                                    @break

                                                  @endswitch
                                                </td>
                                                @endif
                                                <td>{{ $tire->price1 }}</td>
                                                <td style="color: red; font-weight: 500;">{{ $tire->price2 }}</td>
                                                <td>{{ $tire->li }}</td>
                                                <td>{{ $tire->si }}</td>
                                                <td>{{ $tire->code }}</td>
                                                <td>{{ $tire->eco }}</td>
                                                <td>{{ $tire->wet }}</td>
                                                <td>{{ $tire->noise }}</td>
                                                <td>{{ $tire->comment }}</td>
                                                <td style="text-align: center;"><input class="toggle-top40" type="checkbox" style="width: 30px; height: 30px;" @if ($tire->top) checked @endif></td>
                                                <td>{{ $tire->article }}</td>
                                                <td>
                                                    <a class="btn btn-success" href="{{ route('admin.auto.tire.edit', $tire->tire_id) }}">
                                                        <svg class="c-icon">
                                                            <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-description"></use>
                                                        </svg>
                                                    </a>
                                                    <a onclick="if (confirm('Tiešām vēlies dzēst?') === true) { document.location.href = '{{ route('admin.auto.tire.destroy', $tire->tire_id) }}' }" class="btn btn-danger">
                                                        <svg class="c-icon">
                                                            <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-trash"></use>
                                                        </svg>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
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
