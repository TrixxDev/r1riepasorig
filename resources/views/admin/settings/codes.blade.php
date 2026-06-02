@extends('admin.layouts.app')

@section('content')

  <div class="container-fluid">
    <div class="fade-in">
      @if (session('success'))
        <div class="alert alert-success">
          {{ session('success') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      @endif
      @if (session('error'))
        <div class="alert alert-danger">
          {{ session('error') }}
        </div>
      @endif
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <form class="form-horizontal services_form" method="post">
              @csrf
              <input type="hidden" name="service_id">
              <div class="card-header">Lapas @if($codes->first())<span style="float: right;"><a class="btn btn-success" href="{{ route('admin.settings.codes.create') }}"><i class="fa-solid fa-plus"></i> Pievienot</a></span>@endif</div>

              <div class="card-body">
                <div class="row">
                  <div class="col-md-12">
{{--                    {{dd($codes)}}--}}
                    @if($codes->first())

                    <table class="table table-striped table-bordered">
                      <thead>
                      <tr>
                        <th scope="col">Nosaukums</th>
                        <th scope="col">Paskaidrojums</th>
                        <th scope="col">Darbības</th>
                      </tr>
                      </thead>
                      <tbody>
                        @foreach($codes as $code)
                        <tr>
                          <td>{{$code->name}}</td>
                          <td>{{$code->explanation}}</td>
                          <td style="text-align: right; width: 150px;">
                            <form action="{{ route('admin.settings.codes.destroy',$code->code_id) }}" method="POST">
                              <a class="btn btn-warning" style="color: white" href="
                                {{ route('admin.settings.codes.edit',$code->code_id) }}
                              "><i class="fa-solid fa-pen-to-square"></i> Labot</a>

                              <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                    @else
                      <a class="no-codes-link" href="{{ route('admin.settings.codes.create') }}">
                      <div class="no-codes">
                        <i class="fa-sharp fa-solid fa-eye-slash no-codes-icon"></i>
                        <div class="no-codes-title">Pagaidām vēl nav neviena ieraksta!</div>
                        <div>spied šeit lai pievienotu</div>
                      </div>
                      </a>
                    @endif
                  </div>
                </div>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>

  @if (isset($code))
  <!-- Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Uzmanību!</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Vai tiešām vēlaties dzēst šo kodu <b>{{$code->name}}</b>?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Atcelt</button>
          <form action="{{ route('admin.settings.codes.destroy',$code->code_id) }}" method="POST">
            @csrf
            @method('DELETE')

            <button type="submit" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">Dzēst</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  @endif
@endsection
