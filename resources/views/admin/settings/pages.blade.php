@extends('admin.layouts.app')

@section('content')

  <div class="container-fluid">
    <div class="fade-in">
      @if (session('success'))
        <div class="alert alert-success">
          {{ session('success') }}
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
              <div class="card-header">Lapas <span style="float: right;"><a class="btn btn-primary" href="{{ route('admin.settings.pages.create') }}">Izveidot</a></span></div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-12">
                    <table class="table table-striped table-bordered">
                      <thead>
                      <tr>
                        <th scope="col">Nosaukums</th>
                        <th scope="col">URI</th>
                        <th scope="col">Darbības</th>
                      </tr>
                      </thead>
                      <tbody>
                      @foreach($pages as $page)
                        <tr>
                          <td>{{ $page->title }}</td>
                          <td>{{ $page->route }}</td>
                          <td>
                            <a class="btn btn-success" href="{{ route('admin.settings.pages.edit', $page->id) }}">
                              <svg class="c-icon">
                                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-description"></use>
                              </svg>
                            </a>
                            <a class="btn btn-danger" href="{{ route('admin.settings.pages.destroy', $page->id) }}">
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
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>

@endsection
