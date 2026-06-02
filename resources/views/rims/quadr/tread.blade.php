@extends('layouts.app')

@section('body-title', 'category')
{{--@section('title', 'lang-' . app()->getLocale() . ' country-' . app()->getLocale() . ' layout-both-columns page-category tax-display-enabled category-id-14 category-' . $season_title . ' category-id-parent-12 category-depth-level-3')--}}
@php
  $productTitle = $currRim->fullTitle ?? $currRim->fullName ?? 'Lietie diski';
  $productDescriptionSource = $currRim->autocomment ?? '';
  $productDescription = trim(\Illuminate\Support\Str::limit(strip_tags($productDescriptionSource), 160));
@endphp
@section('meta_title', $productTitle . ' | R1 Riepu Serviss')
@section('meta_description', $productDescription ?: 'Lietie diski — R1 Riepu Serviss katalogs.')

@section('content')

  <div class="container">
    <div class="row">
      <div class="main-content clearfix col-md-12 col-xl-12">
        <div id="content-wrapper" class="right-column col-lg-12">
          <section id="main" itemscope="" itemtype="https://schema.org/Product">
            <meta itemprop="url" content="{{ url()->full() }}">
            <div class="row">
              <div class="col-md-12 col-lg-4">
                <section class="page-content" id="content">
                  <div class="images-container ">
                    {!! App\Helper\Image::treadZoom('quadr-rim', $currRim->make_id) !!}
                  </div>
                  <div class="scroll-box-arrows">
                    <i class="material-icons left"></i>
                    <i class="material-icons right"></i>
                  </div>
                </section>
              </div>
              <div class="col-md-12 col-lg-8">
                <div class="row">
                  <div class="col-sm-12 product-main-details">
                    <h1 class="h1 mt-1" itemprop="name">{{$currRim->fullTitle}}</h1>
                  </div>
                  <div class="col-sm-12 col-md-12 col-lg-6">
                    <div class="product-prices">
                      <div class="product-discount">
                        <span>Veikala cena:</span>
                        <span class="regular-price">€ {{ $currRim->price1 }}</span>
                      </div>
                      <div class="product-price h5 has-discount" itemprop="offers" itemscope="" itemtype="https://schema.org/Offer">
                        <link itemprop="availability" href="https://schema.org/InStock">
                        <meta itemprop="priceCurrency" content="EUR">

                        <div class="current-price">
                          <span>Akcijas cena:</span>
                          <span itemprop="price" content="{{ $currRim->price2 }}">€ {{ $currRim->price2 }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-12 col-md-12 col-lg-6">
                    <div class="product-add-to-cart">
                      <div class="product-quantity clearfix">
                        <div class="qty">
                          <div class="input-group bootstrap-touchspin" style="transform: none;">
                            <span class="input-group-addon bootstrap-touchspin-prefix" style="display: none;"></span>
                            <input type="hidden" name="article" class="tire_article" value="{{ $currRim->article }}">
                            <input type="hidden" name="title" class="tire_title" value="{{ $currRim->fullName }}">
                            <input type="text" name="qty" id="quantity_wanted" value="{{ $cartQty }}" class="input-group form-control" min="1" aria-label="Daudzums" style="display: block;">
                            <span class="input-group-addon bootstrap-touchspin-postfix" style="display: none;"></span>
                            <span class="input-group-btn-vertical">
                              <button class="btn btn-touchspin js-touchspin bootstrap-touchspin-up" type="button">
                                <i class="material-icons touchspin-up"></i>
                              </button>
                              <button class="btn btn-touchspin js-touchspin bootstrap-touchspin-down" type="button">
                                <i class="material-icons touchspin-down"></i>
                              </button>
                            </span>
                          </div>
                        </div>
                        <div class="add">
                          <button class="btn btn-primary add-to-cart" data-toggle="modal" @if (Auth::check() && Auth::user()->hasRole('administrators')) data-target="#" @else data-target="#blockcart-modal" @endif data-button-action="add-to-cart"
                                  data-info="{{ $currRim->rim_id }}"
                          >
                            <i class="material-icons shopping-cart"></i>
                            Pirkt
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 col-sm-12 col-md-4">
                    <table class="table">
                      <thead>
                      <tr>
                        <th>Diska platums collās</th>
                        <td>{{ $currRim->d1 }}</td>
                      </tr>
                      </thead>
                      <tbody>
                      <tr>
                        <th>Diametrs</th>
                        <td>{{ $currRim->d3 }}</td>
                      </tr>
                      <tr>
                        <th>Skrūvju attālums</th>
                        <td>{{ $currRim->pcd }}</td>
                      </tr>
                      <tr>
                        <th>Skrūvju skaits</th>
                        <td>{{ $currRim->skr }}</td>
                      </tr>
                      <tr>
                        <th>Stāvoklis</th>
                        <td>
                          @if( $currRim->used === 0)
                            {{ 'Jauns' }}
                          @else
                            {{ 'Lietots' }}
                          @endif
                        </td>
                      </tr>
                      <tr>
                        <th>Piezīmes</th>
                        <td>
                          @php
                            if($currRim->autocomment) {
                                echo $currRim->autocomment;
                            } else {
                                echo '-';
                            }
                          @endphp
                        </td>
                      </tr>
                      <tr>
                        <th>Pieejamība</th>
                        <td>{{ $currRim->available }}</td>
                      </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="col-sm-12 col-md-8">
                    <ul class="nav nav-tabs" style="border-bottom: none!important;">
                      @if ($currRim->t_comment)
                        <li class="nav-item">
                          <a class="nav-link active" data-toggle="tab" href="#tread" style="border-color: #68c0a8 #68c0a8 transparent">Apraksts</a>
                        </li>
                      @endif
                      @if ($currRim->b_comment)
                        <li class="nav-item">
                          <a class="nav-link @if (!$currRim->t_comment) active @endif" data-toggle="tab" href="#brand" style="border-color: #68c0a8 #68c0a8 transparent">Par zīmolu</a>
                        </li>
                      @endif
                    </ul>
                    <div class="tab-content">
                      @if ($currRim->t_comment)
                        <div id="tread" class="container alert tab-pane active" style="border: 1px solid #68c0a8">
                          {!! $currRim->t_comment !!}
                        </div>
                      @endif
                      @if ($currRim->b_comment)
                        <div id="brand" class="container alert tab-pane @if (!$currRim->t_comment) active @endif" style="border: 1px solid #68c0a8">
                          {!! $currRim->b_comment !!}
                        </div>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="comments_note">
            </div>

            <div class="row">
              <div class="">
                <table id="tires-table" class="table rims-tread-sorter tires-table table-hover tablesorter">
                  <thead class="tires-thead">
                  <tr>
                    <th scope="col"></th>
                    <th scope="col" class="">Nosaukums</th>
                    <th scope="col" class="text-center">Izmērs</th>
                    <th scope="col" class="hidden-sm-down text-center">Skrūvju skaits</th>
                    <th scope="col" class="hidden-sm-down text-center">Skrūvju attālums</th>
                    <th scope="col" class="hidden-sm-down text-center">ET</th>
                    <th scope="col" class="hidden-sm-down text-center">Krāsa</th>

                    <th id="store-price-button" scope="col" class="text-center">Veikala cena</th>
                    <th id="store-sale-button" scope="col" class="text-center">Akcijas cena</th>

                    <th scope="col" class="hidden-sm-down text-center">Piezīmes</th>
                    <th scope="col"></th>
                    <th scope="col">
                      <div class="tire-table-icon icon-question" title="Pieejamība" data-toggle="tooltip"></div>
                    </th>

                  </tr>
                  </thead>
                  <tbody id="tires-table-body">

                  @foreach($rims as $rim)
                    @if($rim->price2)
                      <tr @if($currRim->rim_id == $rim->rim_id) style="font-weight: bold; background-color: #e0e0e0;" @endif class="tire-table-row">
                        <th scope="row" class="tire-table-checkbox">
                          <input type="checkbox" value="{{$rim->rim_id}}" name="product_ids[]"
                                 class="tire-table-checkbox">
                        </th>

                        <td>
                          <a data-toggle="tooltip" data-html="true" class="rim-table-link">
                            {{ $rim->fullTitle }}
                          </a>
                        </td>
                        <td class="text-center">
                          {{$rim->d1}}*{{$rim->d3}}
                        </td>

                        <td class="text-center hidden-sm-down">
                          {{$rim->skr}}
                        </td>

                        <td class="text-center hidden-sm-down">
                          {{$rim->pcd}}
                        </td>

                        <td class="text-center hidden-sm-down">
                          {{ $rim->et }}
                        </td>

                        <td class="hidden-sm-down text-center">
                          {{$rim->color}}
                        </td>

                        <td id="store-price" class="text-center store-price">€ {{$rim->price2}}</td>
                        <td id="sale-price" class="text-center tire-price-red sale-price">
                          @if($rim->price3 != 0)
                            € {{$rim->price3}}
                          @endif
                        </td>
                        <td class="hidden-sm-down text-center">{{$rim->comment}}</td>

                        <td class="shopping-cart-col">
                          <div class="clearfix atc_div text-right">
                            <button class="grid-cart-btn"
                                    data-toggle="modal"
                                    @if (Auth::check() && Auth::user()->hasRole('administrators'))
                                    data-target="#"
                                    @else
                                    data-target="#blockcart-modal"
                                    @endif data-info="{{ $rim->rim_id }}"
                            >
                              <i class="material-icons">add_shopping_cart</i>
                            </button>
                          </div>
                        </td>

                        <td class="dot-availability text-center">
                          <span class="dot {{ $rim->dotAvailable }} {{ $rim->stockCount }}" data-toggle="tooltip"
                                data-html="true"
                                title="{{ $rim->stockAvailability }}">
                            <span class="sort-order">{{ $rim->dotAvailable }}</span>
                          </span>
                        </td>
                      </tr>
                    @endif
                  @endforeach
                  </tbody>
                </table>

              </div>
              <div class="modal fade js-product-images-modal" id="product-modal">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-body">
                      <figure>
                        <img class="js-modal-product-cover product-cover-modal" width="" src="" alt="" title="" itemprop="image">
                        <figcaption class="image-caption">
                          <div id="product-description-short" itemprop="description"></div>
                        </figcaption>
                      </figure>
                      <aside id="thumbnails" class="thumbnails js-thumbnails text-sm-center">
                        <div class="js-modal-mask mask  nomargin ">
                          <ul class="product-images js-modal-product-images">
                          </ul>
                        </div>

                      </aside>
                    </div>
                  </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
              </div><!-- /.modal -->
              <!-- /.modal -->
              <!-- /.modal -->



              <footer class="page-footer">

                <!-- Footer content -->

              </footer>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>

@endsection
