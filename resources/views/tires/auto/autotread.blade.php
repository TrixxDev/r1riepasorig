@extends('layouts.app')

@section('body-title', 'product')
@section('title', 'lang-' . app()->getLocale() . ' country-' . app()->getLocale() . ' layout-right-column page-product tax-display-enabled product-id-351 product-antares-ingens-a1- product-id-category-14 product-id-manufacturer-59 product-id-supplier-0 product-available-for-order')
@php
  $productTitle = $currTire->fullName ?? $currTire->title ?? 'Riepas';
  $productDescriptionSource = $currTire->t_comment ?: ($currBrand->b_comment ?? '');
  $productDescription = trim(\Illuminate\Support\Str::limit(strip_tags($productDescriptionSource), 160));
@endphp
@section('meta_title', $productTitle . ' | R1 Riepu Serviss')
@section('meta_description', $productDescription ?: 'Riepas un diski — R1 Riepu Serviss katalogs.')

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
                                    <div class="images-container">
                                        {!! App\Helper\Image::treadZoom('auto', $currTire->make_id) !!}
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
                                  <h1 class="h1 mt-1" itemprop="name">{{ $currTire->title }}</h1>
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
                                        <span itemprop="price" content="{{ $currTire->price2 }}">€ {{ $currTire->price2 }}</span>
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
                                          <input type="hidden" name="title" class="tire_title" value="{{ $currTire->fullName }}">
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
                                        <button class="btn btn-primary add-to-cart" data-toggle="modal" @if (Auth::user()) data-target="#" @else data-target="#blockcart-modal" @endif data-button-action="add-to-cart" data-info="{{ $currTire->tire_id }}">
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
                                      <th>Kods</th>
                                      <td>{{ $currTire->code }}</td>
                                    </tr>
                                    <tr>
                                      <th>Li/Si</th>
                                      <td>
                                        <span data-toggle="tooltip"
                                              title="<span style='color: black'>{{ $currTire->lisiDesc($currTire->li, $currTire->si) }}</span>">{{ $currTire->li . $currTire->si }}
                                        </span>
                                      </td>
                                    </tr>
                                    <tr>
                                      <th>Degvielas ekonomija</th>
                                      <td>{{ $currTire->eco }}</td>
                                    </tr>
                                    <tr>
                                      <th>Slapjš segums</th>
                                      <td>{{ $currTire->wet }}</td>
                                    </tr>
                                    <tr>
                                      <th>Skaņa</th>
                                      <td>{{ $currTire->noise }}</td>
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
                        <div class="comments_note">
                        </div>

                        <div class="row">
                            <div class="">

                              <table id="tires-table" class="table table-striped summer-sorter tires-table table-hover tablesorter">
                                <thead class="tires-thead" style="position:sticky; top: -1px;">
                                <tr>
                                  <th scope="col" class="text-center tread-tire-table-checkbox">
                                    <input type="checkbox" value="only_selected" id="show-selected-tread-checkbox" class="tire-table-checkbox" title="Rādīt tikai atzīmētās preces" @if (request()->show_selected) checked @endif disabled>
                                  </th>
                                  <th scope="col" class="text-center">Izmērs</th>
                                  <th scope="col" class="hidden-sm-down text-center">LI/SI</th>
                                  <th scope="col" class="hidden-sm-down text-center">Kods</th>

                                  <th scope="col" class="hidden-sm-down">
                                    <div class="tire-table-icon icon-tire-fuel" title="Degvielas ekonomija"></div>
                                  </th>

                                  <th scope="col" class="hidden-sm-down">
                                    <div class="tire-table-icon icon-tire-rain" title="Slapjš segums"></div>
                                  </th>

                                  <th scope="col" class="hidden-sm-down">
                                    <div class="tire-table-icon icon-tire-sound" title="Troksnis"></div>
                                  </th>

                                  <th id="store-price-button" scope="col" class="text-center">
                                    Veikala cena
                                  </th>

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
                                  <tr @if($currTire->tire_id == $tire->tire_id) style="font-weight: bold; background-color: #cbcbcb;"@endif class="tire-table-row @if (in_array($tire->tire_id, $selectedTires)) {{ 'selected' }} @endif">
                                  <th class="tire-info" style="display: none;"
                                      data-article="{{ $tire->article }}"
                                      data-content="{{ $tire->fullName }}"
                                      data-quantity="{{ $cartQty }}"></th>
                                  <th scope="row" class="tread-tire-table-checkbox text-center">
                                    <input type="checkbox" value="{{ $tire->tire_id }}" @if (in_array($tire->tire_id, $selectedTires)) checked @endif name="product_ids[]"
                                           class="tire-table-checkbox">
                                  </th>

                                  <td class="tread-name-cell-size text-center">
                                    {{ $tire->fullSize }}
                                  </td>

                                  <td class="hidden-sm-down text-center">
                                    <span data-toggle="tooltip"
                                      title="<span style='color: black'>{{ $tire->lisiDesc($tire->li, $tire->si) }}</span>">{{ $tire->li . $tire->si }}
                                    </span>
                                  </td>

                                  <td class="hidden-sm-down text-center tread-code-cell-size">
                                    <span data-toggle="tooltip" title="<span style='color: black'>
                                      @php $codes = explode(' ', $tire->code); @endphp
                                      @foreach ($codes as $code)
                                              @if (isset($code_array[$code]))
                                                            {!! $code_array[$code] . '<br>' !!}
                                                    @endif
                                      @endforeach
                                      @if (strpos($tire->code, 'DOT') !== false)
                                        {!! $code_array['DOT'] !!}
                                      @endif
                                      </span>" class="hidden-sm-down table-cell prod-code">{{ $tire->code }}
                                    </span>
                                  </td>

                                  <td class="hidden-sm-down text-center">
                                    <span data-toggle="tooltip"
                                          title="<span style='color: black'>{{ $tire->eco }}</span>">{{ $tire->eco }}</span>
                                  </td>

                                  <td class="hidden-sm-down text-center">
                                    <span data-toggle="tooltip"
                                          title="<span style='color: black'>{{ $tire->wet }}</span>">{{ $tire->wet }}</span>
                                  </td>

                                  <td class="hidden-sm-down text-center">
                                    <span data-toggle="tooltip"
                                          title="<span style='color: black'>{{ $tire->noise }}</span>">{{ $tire->noise }}</span>
                                  </td>

                                  <td id="store-price" class="text-center store-price">€ {{ $tire->price1 }}</td>
                                  <td id="sale-price" class="text-center tire-price-red sale-price">€ {{ $tire->price2 }}</td>
                                  <td class="hidden-sm-down text-center tread-comment-cell-size">{{$tire->comment}}</td>
                                  <td class="shopping-cart-col">
                                    <div class="clearfix atc_div text-right">
                                      <button class="grid-cart-btn" data-toggle="modal"
                                              @if (Auth::user()) data-target="#" @else data-target="#blockcart-modal"
                                              @endif data-info="{{ $tire->tire_id }}" data-url="{{ $tire->link }}"><i
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

                    </section>



                </div>

            </div>

        </div>
    </div>

<script src="{{ asset('js/productPageCart.js?rev=' . time()) }}"></script>
@endsection

