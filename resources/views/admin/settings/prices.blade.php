@extends('admin.layouts.app')

@section('content')

  <div class="container-fluid">
    <div class="fade-in">
        <div class="alert price-alert alert-success" style="display: none;">
          <span class="success-message"></span>
          <button type="button" class="close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="alert price-alert alert-warning" style="display: none;">
          <span class="warning-message"></span>
	  <button type="button" class="close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
	<div class="alert price-alert alert-danger" style="display: none;">
          <span class="danger-message"></span>
          <button type="button" class="close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <form class="form-horizontal prices_form" method="post">
              @csrf
              <div class="card-header"><h4>Cenas</h4></span></div>

              <div class="card-body">
                <div class="row">
                  <div class="col-md-12">

                    <table class="table table-striped table-bordered">
                      <thead>
                      <tr>
			<th scope="col">Skaidrojums</th>
                        <th scope="col">Nosaukums</th>
			<th scope="col">Cena</th>
                        <th scope="col">Darbības</th>
                      </tr>
                      </thead>
                      <tbody>
                        @foreach($prices as $price)
			<tr data-abbr="{{ $price->abbr }}" data-name="{{ $price->name }}" data-value="{{ $price->value }}">
			  <td style="display: none;" data-id="{{ $price->id }}"></td>
                          <td>{{ $price->abbr }}</td>
                          <td>{{ $price->name }}</td>
                          <td>{{ $price->value }}</td>
                          <td>
                            <button class="btn edit-price btn-warning" style="color: white; width: 100%;"><i class="fa-solid fa-pen-to-square"></i> Labot</button>
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
