@extends('admin.layouts.app')

@section('content')

  <div class="container-fluid">
    <div class="fade-in">
      <img src="{{asset('/banners/KSVTfTeeaD5F8uZfJYFPOG8VudVMEiF4TCCorTZ7.jpg')}}" alt="">
      @error('image')
      <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
      @enderror
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
      @if (session('status'))
        <div class="alert alert-success">
          {{ session('status') }}
        </div>
      @endif
      @error('formFile')
      <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
      @enderror
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <input type="hidden" name="service_id">
            <div class="card-header">Skrienošā josla</div>
            <div class="card-body">
              <form class="form-horizontal fdjhifudhfds alloy_rims" method="POST" enctype="multipart/form-data" {{ route('admin.settings.banners') }}>
                @csrf
              {{-- IF NO BANNERS --}}
                <label id="no-banner-label" for="formFile">
                  <div class="no-codes">
                    @if( count($banners) == 0 )
                    <i class="fa-sharp fa-solid fa-eye-slash no-codes-icon"></i>
                      <div class="no-codes-title">Pašlaik nav pievienots neviens banneris! (540x80)</div>
                    @else
                      <i class="fa-solid fa-plus" style="font-size: 32px;"></i>
                    @endif
                    <div>Spied šeit lai pievienotu papildus bannerus (540x80)</div>
                  </div>
                </label>

                <input type="file" id="formFile" name="formFile" style="display: none;">

              </form>
              <div class="row">
                <div class="col-md-12">


                  <div class="admin-banner-images">
                      @foreach($banners as $banner)
                        {!! App\Helper\Image::showBanner($banner->id) !!}
                        <div class="options" style="display: flex">
                          <input type="checkbox" data-banner-id="{{ $banner->id }}" class="enable-banner" style="margin-right: 4px;" @if ($banner->enabled) checked @endif name="enable">
                          <div class="col-3">
                            <div class="row">
{{--                              <input type="text" class="form-control" value="{{ $banner->url }}">--}}
                              <span class="banner-link form-control">{{ $banner->url }}</span>
                            </div>
                          </div>
                          <form class="edit-form" action="{{ route('admin.settings.banners.update', $banner->id)}}" method="post">
                            @csrf
                            <input type="hidden" name="url">
                            <button class="btn btn-warning ml-2 edit-banner">Labot</button>
                          </form>
                          <form class="delete-form" action="{{ route('admin.settings.banners.delete', $banner->id)}}" method="post">
                            @csrf
                            <button class="btn btn-danger ml-2 delete-banner">Dzēst</button>
                          </form>
                        </div>
                      @endforeach
{{--                    <img class="banner-image" src="http://1.bp.blogspot.com/_KkBJ9yLOsXc/S6zmtAEypuI/AAAAAAAAEXI/VY3TJH5yUGU/s1600/blackbirdandlollybanner.jpg" alt="banner"><button class="btn btn-danger ml-4">Dzēst</button>--}}
{{--                    <img class="banner-image" src="http://3.bp.blogspot.com/_KkBJ9yLOsXc/S6zmu4t9_ZI/AAAAAAAAEXg/SGqH_LZKrwQ/s1600/FlutterbySky.jpg" alt="banner"><button class="btn btn-danger ml-4">Dzēst</button>--}}
{{--                    <img class="banner-image" src="https://www.everythingetsy.com/wp-content/uploads/2010/02/everythingetsyfreebanner32.png" alt="banner"><button class="btn btn-danger ml-4">Dzēst</button>--}}
                  </div>

                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

@endsection
