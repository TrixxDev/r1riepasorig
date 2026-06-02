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
                        <div class="card-header">Jauns promo kods</div>
                        <form class="form-horizontal" method="post" action="{{ route('admin.promo.store') }}">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        @if (isset($errors) && is_array($errors))
                                        <div class="form-group row">
                                            <ul>
                                            @foreach ($errors as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                            </ul>
                                        </div>
                                        @endif
                                        <div class="form-group row">
                                            <label class="col-md-3 col-form-label" for="name">Nosaukums</label>
                                            <div class="col-md-9">
                                                <input class="form-control" id="name" type="text" name="name" @if (is_array($errors) && isset($errors['name'])) style="border: 1px solid red;" @endif @if (\Request::input('name')) value="{{ \Request::input('name') }}" @endif>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-md-3 col-form-label" for="name">Promo kods</label>
                                            <div class="col-md-9">
                                                <input class="form-control" id="code" type="text" name="code" @if (is_array($errors) && isset($errors['code'])) style="border: 1px solid red;" @endif placeholder="">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-md-3 col-form-label" for="end_date">Beigu datums</label>
                                            <div class="col-md-9">
                                                <input class="form-control" id="end_date" type="date" name="end_date" placeholder="">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-md-3 col-form-label" for="status">Formāts</label>
                                            <div class="col-md-9">
                                                <select name="status" class="form-control" @if (is_array($errors) && isset($errors['status'])) style="border: 1px solid red;" @endif id="status">
                                                    <option disabled selected>Izvēlēties</option>
                                                    <option value="1">Procentuāli %</option>
                                                    <option value="2">Naudas vērtībā €</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-md-3 col-form-label" for="value">Vērtība</label>
                                            <div class="col-md-9">
                                                <input class="form-control" id="value" type="number" min="0" name="value" @if (is_array($errors) && isset($errors['value'])) style="border: 1px solid red;" @endif placeholder="">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-md-3 col-form-label" for="active">Aktīvs</label>
                                            <div class="col-md-9">
                                                <input style="float: left;" class="form-control" id="active" type="checkbox" checked name="active" placeholder="">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-md-3 col-form-label" for="can_use">Var lietot (x reizes)</label>
                                            <div class="col-md-9">
                                                <input class="form-control" id="can_use" type="number" value="0" name="can_use" placeholder="" min="0">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-md-3">
                                                <button class="btn btn-info">
                                                    <a href="#" style="color: white; text-decoration: none;">Atpakaļ</a>
                                                </button>
                                            </div>
                                            <div class="col-md-3">

                                            </div>
                                            <div class="col-md-3">

                                            </div>
                                            <div class="col-md-3">
                                                <button type="submit" class="btn btn-success" style="float: right;">Izveidot</button>
                                            </div>
                                        </div>
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