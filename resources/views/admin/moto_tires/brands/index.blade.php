@extends('admin.layouts.app')

@section('content')

    <div class="container-fluid">
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
        <div class="fade-in">
            <div class="card">
                <div class="card-header"> Moto riepu brendi
                    <div class="card-header-actions">
                    </div>
                </div>
                <div class="card-body">
                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="dataTables_length" id="DataTables_Table_0_length">
                                    <label>Rādīt
                                        <select name="perPage" onchange="location = '{{ route('admin.moto.brands.search') }}/' + this.value" aria-controls="DataTables_Table_0" class="custom-select custom-select-sm form-control form-control-sm">
                                            <option value="10" @if ($paginate == 10) selected @endif>10</option>
                                            <option value="25" @if ($paginate == 25) selected @endif>25</option>
                                            <option value="50" @if ($paginate == 50) selected @endif>50</option>
                                            <option value="100" @if ($paginate == 100) selected @endif>100</option>
                                        </select> ierakstus
                                    </label>
                                    @if (Session::get('search'))
                                        <a class="btn btn-md btn-primary" style="margin-left: 100px;" href="{{ route('admin.moto.brands.search') }}">Dzēst filtru</a>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6" style="display: inline-flex; place-content: flex-end;">
                                <form method="post" action="{{ route('admin.moto.brands.search') }}">
                                    @csrf
                                    <div id="DataTables_Table_0_filter" class="dataTables_filter">
                                        <label>Meklēt:<input type="search" class="form-control form-control-sm" placeholder="" name="search" aria-controls="DataTables_Table_0"></label>
                                    </div>
                                </form>
                                <a style="float: right; height: 28.38px; line-height: 13px; margin-left: 10px;" class="btn btn-md btn-primary" href="{{ route('admin.moto.brands.add') }}"> Pievienot brendu</a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-striped table-bordered datatable dataTable no-footer" id="DataTables_Table_0" role="grid" aria-describedby="DataTables_Table_0_info" style="border-collapse: collapse !important">
                                    <thead>
                                    <tr role="row">
                                        <th>Brends</th>
{{--                                        <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="3" aria-label="Date registered: activate to sort column ascending" style="width: 320.609px;">Izmērs</th>--}}
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($brands as $brand)
                                        <tr role="row" class="odd">
                                            <td>{{ ucwords($brand->title) }}</td>
{{--                                            <td>{{ $tire->d1 }}</td>--}}
                                            <td style="width: 8.3%!important;">
                                                <a class="btn btn-success" href="{{ route('admin.moto.brands.edit', $brand->brand_id) }}">
                                                    <svg class="c-icon">
                                                        <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-description"></use>
                                                    </svg>
                                                </a>
                                                <a class="btn btn-danger brand_delete" id="{{ $brand->brand_id }}" href="{{ route('admin.moto.brands.delete', $brand->brand_id) }}">
                                                    <svg class="c-icon">
                                                        <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-trash"></use>
                                                    </svg>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            {{ $brands->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
