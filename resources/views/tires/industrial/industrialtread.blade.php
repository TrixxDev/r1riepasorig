@extends('layouts.app')

@section('body-title', 'product')
@section('title', 'lang-' . app()->getLocale() . ' country-' . app()->getLocale() . ' layout-right-column page-product tax-display-enabled product-id-351 product-antares-ingens-a1- product-id-category-14 product-id-manufacturer-59 product-id-supplier-0 product-available-for-order')
@php
  $productTitle = $currTire->fullName ?? $pageHeading ?? 'Riepas';
  $productDescriptionSource = $currTire->t_comment ?: ($currBrand->b_comment ?? '');
  $productDescription = trim(\Illuminate\Support\Str::limit(strip_tags($productDescriptionSource), 160));
@endphp
@section('meta_title', $productTitle . ' | R1 Riepu Serviss')
@section('meta_description', $productDescription ?: 'Lielas riepas — R1 Riepu Serviss katalogs.')

@section('content')

  <div class="container">
    <div class="row">
      <div class="main-content clearfix col-md-12 col-xl-12">
        <div id="content-wrapper" class="right-column col-lg-12">
          <section id="main" itemscope="" itemtype="https://schema.org/Product">
            <meta itemprop="url" content="{{ url()->full() }}">
            <div class="row">
              <div class="col-md-4">
                <section class="page-content" id="content">
                  <div class="images-container">
                    {!! App\Helper\Image::treadZoom('big', $currTire->make_id) !!}
                    <div class="js-qv-mask mask">
                      <ul class="product-images js-qv-product-images">
                      </ul>
                    </div>
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
                    <h1 class="h1 mt-1" itemprop="name">{{ $pageHeading }}</h1>
                  </div>
                  <div class="col-sm-12 col-md-12 col-lg-6">
                    <div class="product-prices">
                      <div class="product-discount">
                        <span>Veikala cena:</span>
                        <span class="regular-price">€ {{ $currTire->price1 }}</span>
                      </div>
                      <div class="product-price h5 has-discount" itemprop="offers" itemscope="" itemtype="https://schema.org/Offer">
                        <link itemprop="availability" href="https://schema.org/InStock">
                        <meta itemprop="priceCurrency" content="EUR">

                        <div class="current-price">
                          <span>Akcijas cena:</span>
                          <span itemprop="price" content="{{ $currTire->price3 }}">€ {{ $currTire->price3 }}</span>
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
                            <input type="hidden" name="article" class="tire_article" value="{{ $currTire->article }}">
                            <input type="hidden" name="title" class="tire_title" value="{{ $pageHeading }}">
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
			  <button type="button" class="btn btn-primary add-to-cart" data-button-action="add-to-cart" data-info="{{ $currTire->tire_id }}" data-link="/lielas-riepas" data-url="{{ url()->current() }}">
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
                        <td>{{ $currTire->d1 }}</td>
                      </tr>
                      </thead>
                      <tbody>
                      <tr>
                        <th>Augstums</th>
                        <td>{{ $currTire->d2 }}</td>
                      </tr>
                      <tr>
                        <th>Diametrs</th>
                        <td>{{ $currTire->d3 }}</td>
                      </tr>
                      <tr>
                        <th>Li/Si</th>
                        <td>
                          <span data-toggle="tooltip"
                                title="<span style='color: black'>{{ $currTire->lisiDesc($currTire->li, $currTire->si) }}</span>">{{ $currTire->li . ' ' . $currTire->si }}
                          </span>
                        </td>
                      </tr>
                      <tr>
                        <th>Kods</th>
                        <td>{{ $currTire->code }}</td>
                      </tr>
                      <tr>
                        <th>Piezīmes</th>
                        <td>
                          @php
                            if($currTire->autocomment) {
                                echo $currTire->autocomment;
                            } else {
                                echo '-';
                            }
                          @endphp
                        </td>
                      </tr>
                      <tr>
                        <th>Pieejamība</th>
                        <td>{{ $currTire->available }}</td>
                      </tr>
                      </tbody>
                    </table>
                  </div>
                    <div class="col-sm-12 col-md-8">
                        <ul class="nav nav-tabs" style="border-bottom: none!important;">
                            @if ($currTire->t_comment)
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#tread" style="border-color: #68c0a8 #68c0a8 transparent">Apraksts</a>
                                </li>
                            @endif
                            @if ($currBrand->b_comment)
                                <li class="nav-item">
                                    <a class="nav-link @if (!$currTire->t_comment) active @endif" data-toggle="tab" href="#brand" style="border-color: #68c0a8 #68c0a8 transparent">Par zīmolu</a>
                                </li>
                            @endif
                        </ul>
                        <div class="tab-content">
                            @if ($currTire->t_comment)
                                <div id="tread" class="container alert tab-pane active" style="border: 1px solid #68c0a8">
                                    {!! $currTire->t_comment !!}
                                </div>
                            @endif
                            @if ($currBrand->b_comment)
                                <div id="brand" class="container alert tab-pane @if (!$currTire->t_comment) active @endif" style="border: 1px solid #68c0a8">
                                    {!! $currBrand->b_comment !!}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="">
                <table id="tires-table" class="table moto-tread-sorter tires-table table-hover tablesorter">
                  <thead class="tires-thead" style="position:sticky; top: -1px;">
                  <tr>
                    <th scope="col"></th>
                    <th scope="col" class="">Izmērs</th>
                    <th scope="col" class="hidden-sm-down text-center">Ass</th>
                    <th scope="col" class="hidden-sm-down text-center">LI/SI</th>
                    <th scope="col" class="hidden-sm-down text-center">Kods</th>
                    <th id="store-price-button" scope="col" class="text-center">Veikala cena</th>
                    <th id="store-sale-button" scope="col" class="text-center">Akcijas cena</th>
                    <th scope="col" class="hidden-sm-down text-center">Piezīmes</th>
                    <th scope="col">Grozs</th>
                    <th scope="col">
                      <div class="tire-table-icon icon-question" title="Pieejamība" data-toggle="tooltip"></div>
                    </th>

                  </tr>
                  </thead>
                  <tbody id="tires-table-body">
                  @foreach ($tires as $tire)

                    @php
                      $tire->includeStock = true;
                    @endphp

                    <tr @if($currTire->tire_id == $tire->tire_id) style="font-weight: bold; background-color: #e0e0e0;" @endif class="tire-table-row">
                      <th scope="row" class="t read-tire-table-checkbox">
                        <input type="checkbox" value="{{ $tire->tire_id }}" name="product_ids[]"
                               class="tire-table-checkbox">
                      </th>

                      <td class="big-tire-tread-name-cell-size">
                        {{ $tire->fullSize }}
                      </td>
                      <td class="hidden-sm-down text-center">
                        @if ($tire->axis)
                          {{ $tire->axis }}
                        @endif
                      </td>
                      <td class="hidden-sm-down text-center">
                        <span data-toggle="tooltip"
                              title="<span style='color: black'>{{ $tire->lisiDesc($tire->li, $tire->si) }}</span>">{{ $tire->li . ' ' . $tire->si }}
                        </span>
                      </td>

                      <td class="hidden-sm-down text-center tread-code-cell-size">
                        @if (!empty($tire->code)) {{ $tire->code . 'PR' }} @endif
                      </td>

                      <td id="store-price" class="text-center store-price">€ {{ $tire->price1 }}</td>
                      <td id="sale-price" class="text-center tire-price-red sale-price">€ {{ $tire->price3 }}</td>
                      <td class="hidden-sm-down text-center tread-comment-cell-size">{{$tire->comment}}</td>
                      <td class="shopping-cart-col">
                        <div class="clearfix atc_div text-right">
                          <button type="button" class="cart-shopping-button grid-cart-btn"
                                  data-info="{{ $tire->tire_id }}" data-url="{{ route('lielas-riepa', [strtolower(\Tires::getBigTireBrand($tire->tread->brand_id)->title), strtolower(str_replace('/', '_', $tire->tread->title)), $tire->tire_id]) }}" data-link="/lielas-riepas"><i
			      class="material-icons">add_shopping_cart</i>
                          </button>
                        </div>
                      </td>

                      <td class="dot-availability text-center">
                        <span class="dot {{ $tire->dotAvailable }}" data-toggle="tooltip"
                              data-html="true"
                              title="{{ $tire->stockAvailability }}">
                          <span class="sort-order">{{ $tire->dotAvailable }}</span>
                        </span>
                      </td>
                    </tr>
                  @endforeach
                  </tbody>
                </table>
                <nav class="pagination ct_pagination">
                  <ul id="ct_pagination" class="page-list clearfix text-xs-center"></ul>
                </nav>
              </div>
            </div>
            <div class="comments_note">
            </div>
{{--            <div class="row">--}}
{{--              <div class="col-lg-9 col-md-12 float-lg-right">--}}

{{--                <table id="ct_matrix">--}}
{{--                  <thead>--}}
{{--                  <tr class="ct_matrix_head">--}}
{{--                    <th style="user-select: none;">Izmērs</th>--}}
{{--                    <th class="hidden-sm-down">Ass</th>--}}
{{--                    <th class="hidden-sm-down">Segums</th>--}}
{{--                    <th class="hidden-sm-down" aria-sort="none" style="user-select: none;">LI/SI</th>--}}
{{--                    <th class="hidden-sm-down" aria-sort="none" style="user-select: none;">Kods</th>--}}
{{--                    <th class="cth_price" style="user-select: none;">Veikala cena</th>--}}
{{--                    <th class="cth_price" style="user-select: none;">Akcijas cena</th>--}}
{{--                    <th class="hidden-sm-down" style="user-select: none;">Piezīmes</th>--}}
{{--                    <th class="cth_addtocart" style="user-select: none;">Grozs</th>--}}
{{--                    <th class="cth_availability quadr" style="user-select: none;"></th>--}}
{{--                  </tr>--}}
{{--                  </thead>--}}
{{--                  <tbody>--}}
{{--                  @foreach ($tires as $tire)--}}

{{--                    @php--}}
{{--                      $tire->includeStock = true;--}}
{{--                    @endphp--}}

{{--                    <tr class="ct_matrix_row ctr0 @if ($currTire->tire_id == $tire->tire_id) {{ 'current' }} @endif" id="ctrcombid{{ $tire->tire_id }}" role="row">--}}
{{--                      <td>--}}
{{--                        <input type="checkbox" value="{{ $tire->tire_id }}" name="product_ids2[]"> {{ $tire->fullSize }}--}}
{{--                      </td>--}}
{{--                      <td class="hidden-sm-down">asd</td>--}}
{{--                      <td class="hidden-sm-down">asd</td>--}}
{{--                      <td class="hidden-sm-down">{{ $tire->lisi }}</td>--}}
{{--                      <td class="hidden-sm-down">{{ $tire->code }}</td>--}}
{{--                      <td data-label="Veikala cena" class="ctd_price ctd_attr_group_price">--}}
{{--                        € {{ $tire->price1 }}--}}
{{--                      </td>--}}
{{--                      <td data-label="Akcijas cena" class="ctd_price ctd_attr_group_price">--}}
{{--                        <strong class="strongprice">€ {{ $tire->price2 }}</strong>--}}
{{--                      </td>--}}
{{--                      <td class="hidden-sm-down">{{ $tire->autocomment }}</td>--}}
{{--                      <td class="ctd_addtocart" data-label="Grozs">--}}
{{--                        <input alt="ct_matrix_{{ $tire->tire_id }}" name="qty" class="qty" id="ct_matrix_{{ $tire->tire_id }}_idQty" value="4 " type="text" style="display:none!important;">--}}
{{--                        <div class="ct_submit btn btn-sm btn-primary" id="ct_matrix_{{ $tire->tire_id }}_submit" data-toggle="modal" data-target="#blockcart-modal" data-info="{{ $tire->tire_id }}">--}}
{{--                          <i class="material-icons">add_shopping_cart</i>--}}
{{--                        </div>--}}
{{--                      </td>--}}
{{--                      <td class="ctd_availability">--}}
{{--                        <span class="clearfix atc_div">--}}
{{--                          <span class="dot {{ $tire->dotAvailable }}" data-toggle="tooltip" data-html="true" title="{{ $tire->stockAvailability }}"><span class="sort-order">6</span></span>--}}
{{--                        </span>--}}
{{--                      </td>--}}
{{--                    </tr>--}}
{{--                  @endforeach--}}
{{--                  </tbody>--}}
{{--                </table>--}}
{{--              </div>--}}
{{--            </div>--}}
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
          </section>
        </div>
      </div>
    </div>
  </div>

<script src="{{ asset('js/bigTiresAjax.js?rev=' . (file_exists(public_path('js/bigTiresAjax.js')) ? filemtime(public_path('js/bigTiresAjax.js')) : 0)) }}"></script>
@endsection
