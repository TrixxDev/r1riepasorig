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
                        <div class="card-header">Promo kodi</div>
                        <form class="form-horizontal services_form" id="ordersForm" method="get">
                            <input type="hidden" disabled name="csrf_token" value="{{ csrf_token() }}">
                            <input type="hidden" disabled name="service_id">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                            <tr>
                                                <th scope="col">Nosaukums</th>
                                                <th scope="col">Kods</th>
                                                <th scope="col">Atlaide</th>
                                                <th scope="col">Beigu datums</th>
                                                <th scope="col">Var izmantot</th>
                                                <th scope="col">Izmantots</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Izveidots</th>
                                                <th scope="col">
                                                    <a href="{{ route('admin.promo.create') }}" class="btn btn-secondary" style="width: 100%;">Pievienot</a>
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                @if ($promo_codes->count() <= 0)
                                                    <tr>
                                                        <td colspan="8" style="text-align: center;">Nav neviena ieraksta</td>
                                                    </tr>
                                                @endif
                                                @foreach ($promo_codes as $promo)
                                                <tr>
                                                    <td>{!! $promo->name !!}</td>
                                                    <td>{!! $promo->code !!}</td>
                                                    <td>
                                                        @if ($promo->status == 1)
                                                            -{{ $promo->value }}%
                                                        @else
                                                            -{{ $promo->value }} Eiro
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (is_null($promo->end_date))
                                                            Nav beigu datuma
                                                        @else
                                                            {{ date('d-m-Y', strtotime($promo->end_date)) }}
{{--                                                            {{ date_format($promo->end_date, 'd-m-Y') }}--}}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (is_null($promo->can_use))
                                                            Neierobežoti
                                                        @else
                                                            {{ $promo->can_use }}
                                                            @if ($promo->can_use != 1)
                                                                reizes
                                                            @else
                                                                reizi
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>{{ $promo->used }} @if ($promo->used != 1) reizes @else reizi @endif</td>
                                                    <td>
                                                        @if ($promo->active > 0)
                                                            Aktīvs
                                                        @else
                                                            Beidzies
                                                        @endif
                                                    </td>
                                                    <td>{{ $promo->created_at }}</td>
                                                    <td style="width: 153px;">
                                                        <a href="{{ route('admin.promo.edit', $promo->promo_id) }}" class="btn btn-warning">
                                                            <i class="fa-solid fa-pencil" style="color:#fff;"></i>
                                                        </a>
                                                        <a href="{{ route('admin.promo.delete', $promo->promo_id) }}" onclick="if(!confirm('Tiešām dzēst promo kodu?')) { return false; }" class="btn btn-danger">
                                                            <i class="fa-solid fa-trash" style="color:#fff;"></i>
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