@extends('layouts.app')

@section('body-title', 'category')
{{--@section('title', 'lang-' . app()->getLocale() . ' country-' . app()->getLocale() . ' layout-both-columns page-category tax-display-enabled category-id-14 category-' . $season_title . ' category-id-parent-12 category-depth-level-3')--}}
@php
  $productTitle = $currRim->fullTitle ?? $currRim->fullName ?? 'Lietie diski';
  $productDescriptionSource = $currRim->autocomment ?? '';
  $productDescription = trim(\Illuminate\Support\Str::limit(strip_tags($productDescriptionSource), 160));
  $isRimAdmin = Auth::check() && Auth::user()->hasRole('administrators');
  $rimCartModalTarget = $isRimAdmin ? '#' : '#blockcart-modal';
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
                      {!! App\Helper\Image::treadZoom('auto-rim', $currRim->make_id) !!}
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
{{--                    {{ dd($tread, $brand) }}--}}
                    <h1 class="h1 mt-1" itemprop="name">{{$currRim->fullTitle}}</h1>
{{--                    <h1 class="h1 mt-1" itemprop="name">{{ dd($rims[0]) }}</h1>--}}
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

                        @php
                          $currentSalePrice = $currRim->price2;
                        @endphp
                        <div class="current-price">
                          <span>Akcijas cena:</span>
                          <span itemprop="price" content="{{ $currentSalePrice }}">€ {{ $currentSalePrice }}</span>
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
{{--                          {{ dd($currRim) }}--}}
                          <button class="btn btn-primary add-to-cart" data-toggle="modal" data-target="{{ $rimCartModalTarget }}" data-button-action="add-to-cart"
                                  data-info="{{ $currRim->rim_id }}"
                          >
                            <i class="material-icons shopping-cart"></i>
                            Pirkt
                          </button>
                        </div>
                      </div>
                      {{--                                    <p class="product-minimal-quantity">--}}
                      {{--                                    </p>--}}
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 col-sm-12 col-md-4">
                    <table class="table">
                      <thead>
                      <tr>
                        <th>Platums</th>
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
                        <th>Centrālais caurums</th>
                        <td>{{ $currRim->dc }}</td>
                      </tr>
                      <tr>
                        <th>Skrūvju skaits</th>
                        <td>{{ $currRim->skr }}</td>
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
                      @if ($currRim->treadComment)
                        <li class="nav-item">
                          <a class="nav-link active" data-toggle="tab" href="#tread" style="border-color: #68c0a8 #68c0a8 transparent">Apraksts</a>
                        </li>
                      @endif
                      @if ($currRim->brandComment)
                        <li class="nav-item">
                          <a class="nav-link @if (!$currRim->treadComment) active @endif" data-toggle="tab" href="#brand" style="border-color: #68c0a8 #68c0a8 transparent">Par zīmolu</a>
                        </li>
                      @endif
                    </ul>
                    <div class="tab-content">
                      @if ($currRim->treadComment)
                        <div id="tread" class="container alert tab-pane active" style="border: 1px solid #68c0a8">
                          {!! $currRim->treadComment !!}
                        </div>
                      @endif
                      @if ($currRim->brandComment)
                        <div id="brand" class="container alert tab-pane @if (!$currRim->treadComment) active @endif" style="border: 1px solid #68c0a8">
                          {!! $currRim->brandComment !!}
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
                    <th scope="col" class="hidden-sm-down text-center">Centrs</th>
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
                    @php
                      $storePrice = $rim->price1 ?? 0;
                      $salePrice = $rim->price2;
                    @endphp
                    @if($storePrice || $salePrice)
                      <tr @if($currRim->rim_id == $rim->rim_id) style="font-weight: bold; background-color: #e0e0e0;" @endif class="tire-table-row">
                        <th class="tire-info" style="display: none;" data-article="{{ $rim->article }}" data-content="{{ $rim->fullName }}" data-quantity="{{ $cartQty }}"></th>
                        <th scope="row" class="tire-table-checkbox">
                          <input type="checkbox" value="{{$rim->rim_id}}" name="product_ids[]"
                                 class="tire-table-checkbox">
                        </th>

                        <td class="table-tire-name-cell">
                          <a data-toggle="tooltip"
                             data-html="true"
                             class="rim-table-link tire-table-link"
                             data-content="{{ $rim->fullName }}"
                             data-article="{{ $rim->article }}"
                             data-quantity="{{ $cartQty }}">
                            {{ $rim->fullTitle }}
                          </a>
                        </td>
                        <td class="text-center">
                            {{$rim->d1}}x{{$rim->d3}}
                        </td>

                        <td class="text-center hidden-sm-down">
                          {{$rim->skr}}
                        </td>

                        <td class="text-center hidden-sm-down">
                          {{$rim->pcd}}
                        </td>

                        <td class="text-center hidden-sm-down">
                          et{{ $rim->et }}
                        </td>

                        <td class="text-center hidden-sm-down">
                          {{$rim->dc}}
                        </td>

                        <td class="hidden-sm-down text-center">
                          {{ strtoupper($rim->color) }}
                        </td>


                        <td id="store-price" class="text-center store-price">
                          @if($storePrice)
                            € {{$storePrice}}
                          @endif
                        </td>
                        <td id="sale-price" class="text-center tire-price-red sale-price">
                          @if($salePrice)
                            € {{$salePrice}}
                          @endif
                        </td>
                        <td class="hidden-sm-down text-center">{{$rim->comment}}</td>

                        <td class="shopping-cart-col">
                          <div class="clearfix atc_div text-right">
                            <button class="grid-cart-btn"
                                    data-toggle="modal"
                                    data-target="{{ $rimCartModalTarget }}"
                                    data-info="{{ $rim->rim_id }}"
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
              {{--                            <div class="col-lg-3 col-md-12 float-lg-left">--}}
              {{--                                <div id="productCommentsBlock">--}}

              {{--                                    <div class="tabs">--}}
              {{--                                        <div class="clearfix pull-right">--}}
              {{--                                            <a class="open-comment-form btn btn-primary" href="#new_comment_form">Rakstīt komentāru</a>--}}
              {{--                                        </div>--}}
              {{--                                        <div id="new_comment_form_ok" class="alert alert-success" style="display:none;padding:15px 25px"></div>--}}
              {{--                                        <div id="product_comments_block_tab">--}}


              {{--                                        </div>--}}
              {{--                                    </div>--}}

              {{--                                    <!-- Fancybox -->--}}
              {{--                                    <div style="display:none">--}}
              {{--                                        <div id="new_comment_form" style="display: none;">--}}
              {{--                                            <form id="id_new_comment_form" action="#">--}}
              {{--                                                <div class="new_comment_form_content">--}}
              {{--                                                    <h2>Rakstīt komentāru</h2>--}}
              {{--                                                    <div id="new_comment_form_error" class="error" style="display:none;padding:15px 25px">--}}
              {{--                                                        <ul></ul>--}}
              {{--                                                    </div>--}}
              {{--                                                    <label>Vārds<sup class="required">*</sup></label>--}}
              {{--                                                    <input id="commentCustomerName" name="customer_name" type="text" value="">--}}

              {{--                                                    <label for="comment_title">Nosaukums<sup class="required">*</sup></label>--}}
              {{--                                                    <input id="comment_title" name="title" type="text" value="">--}}

              {{--                                                    <label for="content">Komentārs<sup class="required">*</sup></label>--}}
              {{--                                                    <textarea id="content" name="content"></textarea>--}}
              {{--                                                    <div id="new_comment_form_footer">--}}
              {{--                                                        <input id="id_product_comment_send" name="id_product" type="hidden" value="351">--}}
              {{--                                                        <p class="fl required"><sup>*</sup> Obligāts</p>--}}
              {{--                                                        <p class="fr">--}}
              {{--                                                            <button class="btn btn-primary" id="submitNewMessage" name="submitMessage" type="submit">Sūtīt</button>&nbsp;--}}
              {{--                                                            vai&nbsp;<a href="#" onclick="$.fancybox.close();">Aizvert</a>--}}
              {{--                                                        </p>--}}
              {{--                                                        <div class="clearfix"></div>--}}
              {{--                                                    </div>--}}
              {{--                                                </div>--}}
              {{--                                            </form><!-- /end new_comment_form_content -->--}}
              {{--                                        </div>--}}
              {{--                                    </div>--}}
              {{--                                    <!-- End fancybox -->--}}
              {{--                                </div>--}}
              {{--                            </div>--}}
              {{--                        </div>--}}
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

<script src="{{ asset('js/rimProductPageCart.js') }}"></script>

@endsection

