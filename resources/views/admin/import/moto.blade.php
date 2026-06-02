@extends('admin.layouts.app')

@section('content')

    <div class="container-fluid">
        <div class="fade-in">

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <form class="form-horizontal" action="{{ route('admin.moto.import.post') }}" method="post">
                            @csrf
                            <div class="card-header">Moto riepu imports</div>
                            <div class="card-body">
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <textarea class="form-control" id="textarea-input" name="rows" rows="12"></textarea>
                                    </div>
                                </div>
                              @if (session('out'))
                                {!!  session('out') !!}
                              @endif
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-sm btn-primary" type="submit"> Importēt</button>
                                <button class="btn btn-sm btn-danger" type="reset"> Attīrīt</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
