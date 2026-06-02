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
        <div class="card-header"> Kvadru diski
          <div class="card-header-actions">
          </div>
        </div>
        <div class="card-body">
          <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
            <div class="row">
              <div class="col-sm-12 col-md-6">
                <div class="form-group row brand-settings">
                  <label class="col-md-2 col-form-label" for="brand_select">Brends: </label>
                  <select name="brand" class="form-control col-md-3" data-model="quadrims" id="brand_select">
                    @foreach ($brands as $curr_brand)
                      <option value="{{ $curr_brand->brand_id }}" @if (isset($tread->brand_id) && $tread->brand_id == $curr_brand->brand_id) {{ 'selected' }} @endif >{{ ucwords(strtolower($curr_brand->b_title)) }}</option>
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
{{--                                                    {{ dd($tread) }}--}}
                  {!! App\Helper\Image::showGrid('quadr-rim', $tread->make_id, 'width: 300px; margin-bottom: 20px;') !!}

                  <form action="{{ route('admin.quadrims.image', $tread->make_id) }}" method="post" enctype="multipart/form-data">
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
              <div class="row justify-content-end tires-header">
                <a class="btn btn-md btn-primary new_tire text-white" href="{{ route('admin.quadrims.create', $tread->make_id) }}">Pievienot</a>
              </div>
            @endif
            <div class="row">
              <div class="col-sm-12">
                <table class="table table-striped table-bordered datatable dataTable no-footer" id="DataTables_Table_0" role="grid" aria-describedby="DataTables_Table_0_info" style="border-collapse: collapse !important">
                  <thead>
                  <tr role="row">
                    <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="2" aria-label="Date registered: activate to sort column ascending" style="width: 320.609px;">Izmērs</th>
                    <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Role: activate to sort column ascending" style="width: 151.953px;">Veikala cena</th>
                    <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 167.547px;">Akcijas cena</th>
                    <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 167.547px;">Skrūves</th>
                    <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 167.547px;">Skrūvju attālums</th>
                    <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 167.547px;">ET</th>
                    <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Krāsa</th>
                    <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 67.547px;">Piezīmes</th>
                    <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Username: activate to sort column ascending" style="width: 372.5px;">Artikuls</th>
                    <th tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" style="width: 160.391px;"></th>
                  </tr>
                  </thead>
                  <tbody>
                  @if (isset($rims))
                    @foreach ($rims as $rim)
                      <tr role="row" class="odd">
                        <td>{{ $rim->d1 }}</td>
                        <td>{{ $rim->d3 }}</td>
                        <td>{{ $rim->price1 }}</td>
                        <td style="color: red; font-weight: 500;">{{ $rim->price2 }}</td>
                        <td>{{ $rim->skr }}</td>
                        <td>{{ $rim->pcd }}</td>
                        <td>{{ $rim->et }}</td>
                        <td>{{ $rim->color }}</td>
                        <td>{{ $rim->comment }}</td>
                        <td>{{ $rim->article }}</td>
                        <td>
                          <a class="btn btn-success" href="{{ route('admin.quadrims.edit', $rim->rim_id) }}">
                            <svg class="c-icon">
                              <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-description"></use>
                            </svg>
                          </a>
                          <a onclick="if (confirm('Tiešām vēlies dzēst?') === true) { window.location.href = '{{ route('admin.quadrims.destroy', $rim->rim_id) }}' }" class="btn btn-danger">
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
          </div>
          @include('admin.quadrims._import-duell')
        </div>
      </div>
    </div>
  </div>

@endsection
