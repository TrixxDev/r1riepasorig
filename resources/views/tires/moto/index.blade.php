@extends('layouts.app')

@section('body-title', 'category')
@section('title', 'lang-' . app()->getLocale() . ' country-' . app()->getLocale() . ' layout-both-columns page-category tax-display-enabled category-id-16 category-motociklu-riepas category-id-parent-12 category-depth-level-3')
@section('meta_title', 'Motociklu riepas | R1 Riepu Serviss')
@section('meta_description', 'Motociklu riepas ar filtriem pēc izmēra un ražotāja. R1 Riepu Serviss katalogs.')

@section('content')

  <div class="container">
    <div class="row">
      <div class="main-content clearfix col-md-12 col-xl-12">

        <div id="left-column" class="col-md-12 col-lg-3">
          <!-- begin D:\OpenServer\domains\r1old/themes/classic/modules/ps_facetedsearch/ps_facetedsearch.tpl -->
          <div id="search_filters_wrapper">
            <form method="get" action="{{ route('motociklu-riepas-meklet') }}">
            <div id="search_filters" class="params">
              <input type="hidden" id="facet_all_val" value="Visi">
              <div class="wrap">

                <div class="can-collapse">

                  <div class="flex" style="justify-content: space-around;">
                    <span class="show_list active" data-dismiss="modal"><i class="material-icons "></i>Saraksts</span>
                    <span class="show_grid" data-dismiss="modal"><i class="material-icons "></i>Bildes</span>
                  </div>

                  <div class="sidebar-top">
                    <div style="width: 100%; margin-top: -.625rem;">
                      <div class="form-group facet mb-0">
                        <select name="brand" class="r1-select select-title tire-brand">
                          <option class="select-list" id="Visi">Ražotājs</option>
                          @foreach ($brands as $brand_id => $brand_title)
                            <option class="select-list" id="{{ $brand_id }}" @if (ucwords(strtolower($brand_title)) == $currBrand) selected @endif>{{ ucwords(strtolower($brand_title)) }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>

                    <div class="r1-select-params">
                      <div style="width: 100%">
                        <div class="form-group facet">
                          <h1 class="h6 facet-title" style="margin-bottom: 0px;">Platums</h1>
                          <select class="r1-select select-title tire-width" name="d1">
                            <option class="select-list" id="Visi">Visi</option>
                            @foreach ($motoTiresD1 as $tire)
                              <option id="{{ $tire->d1 }}" @if ($tire->d1 == $d1) selected @endif>{{ $tire->d1 }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      <div style="width: 100%">
                        <div class="form-group facet">
                          <h1 class="h6 facet-title" style="margin-bottom: 0px;">Augstums</h1>
                          <select name="d2" class="r1-select select-title tire-height">
                            <option class="select-list" id="Visi">Visi</option>
                            @foreach ($motoTiresD2 as $tire)
                              <option class="select-list" id="{{ $tire->d2 }}" @if ($tire->d2 == $d2) selected @endif>{{ $tire->d2 }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      <div style="width: 100%">
                        <div class="form-group facet">
                          <h1 class="h6 facet-title facet-select" style="margin-bottom: 0px;">Diametrs</h1>
                          <select name="d3" class="r1-select select-title tire-radius">
                            <option class="select-list" id="Visi">Visi</option>
                            @foreach ($motoTiresD3 as $tire)
                              <option class="select-list" id="{{ $tire->d3 }}" @if ($tire->d3 == $d3) selected @endif>{{ $tire->d3 }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                    </div>

                    <div style="width: 100%; margin-top: -15px;">
                      <div class="form-group facet mb-0">
                        <select class="r1-select-input" multiple="multiple"></select>
                      </div>
                    </div>

                    <section class="facet clearfix">
                      <button id="autofind_sub" class="filter-button" type="submit">
                        Meklēt <i class="material-icons search"></i>
                      </button>
                    </section>

                  </div>
                </div>
              </div>

              <div class="wrap hidden-sm-down">
                <div class="sidebar-bottom">

                  <section class="facet clearfix facet--availability" style="padding-top: 0;">
                    <ul class="collapse">
                      <li class="show-selected-checkbox-li">
                        <label class="facet-label" for="show-selected-checkbox"
                               style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                          <span class="custom-checkbox">
                            <input type="checkbox" value="only_selected" class="show-selected-filter tire-table-checkbox" id="show-selected-checkbox" title="Rādīt tikai atzīmētās preces" @if (request()->show_selected) checked @endif disabled>
                            <span class="ps-shown-by-js">
                              <i class="material-icons checkbox-checked"></i>
                            </span>
                          </span>
                          <span>Rādīt izvēlētos</span>
                        </label>
                      </li>
                    </ul>
                    <h1 class="h6 facet-title hidden-sm-down"><b>Pieejamība</b></h1>
                    <ul id="facet_availability" class="collapse">
                      <li>
                        <label class="facet-label" for="facet_availability_0" style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                          <span class="custom-checkbox">
                            <input id="facet_availability_0" class="green" @if (in_array('green', explode(' ', request()->availability))) checked @endif type="checkbox" data-search-url="#" name="availability[]" value="green" data-for="dot" data-value="green" data-color="green">
                            <span class="ps-shown-by-js">
                              <i class="material-icons checkbox-checked"></i>
                            </span>
                          </span>
                          Pieejams
                          <span class="dot green" style="float:right;margin-top: 3px;"></span>
                        </label>
                      </li>
                      <li>
                        <label class="facet-label" for="facet_availability_1" style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                          <span class="custom-checkbox">
                            <input id="facet_availability_1" class="yellow" @if (in_array('yellow', explode(' ', request()->availability))) checked @endif type="checkbox" data-search-url="#" name="availability[]" value="yellow" data-for="dot" data-value="yellow" data-color="yellow">
                            <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                          </span>
                          Pasūtāms
                          <span class="dot yellow" style="float:right;margin-top: 3px;"></span>
                        </label>
                      </li>
                      <li>
                        <label class="facet-label" for="facet_availability_2" style="width: 100%;text-align: left;cursor: pointer">
                          <span class="custom-checkbox">
                            <input id="facet_availability_2" class="red" @if (in_array('red', explode(' ', request()->availability))) checked @endif type="checkbox" data-search-url="#" name="availability[]" value="red" data-for="dot" data-value="red" data-color="red">
                            <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                          </span>
                          Zvaniet!
                          <span class="dot red" style="float:right;margin-top: 3px;"></span>
                        </label>
                      </li>
                    </ul>
                  </section>

                  <section class="facet clearfix facet--4">
                    <h1 class="h6 facet-title hidden-sm-down facet-hover type-dropdown-btn"><b>Tips</b></h1>
                    <div class="title hidden-md-up" data-target="#facet_11641" data-toggle="collapse">
                      <h1 class="h6 facet-title">Tips</h1>
                      <span class="float-xs-right">
                        <span class="navbar-toggler collapse-icons">
                          <i class="material-icons add"></i>
                          <i class="material-icons remove"></i>
                        </span>
                      </span>
                    </div>


                    <ul id="facet_type" class="collapse">
                      @php $selectedMotoTypes = \App\Models\Moto::parseTypeFilterParam(request()->type ?? null); @endphp
                      @foreach ($types as $index => $value)
                        @php
                          $typeSlug = str_replace(' ', '', strtolower($value));
                          $typeInputId = preg_replace('/[^a-z0-9]+/', '', $typeSlug) ?: 'type';
                        @endphp
                        <li data-label="{{ $typeSlug }}">
                          <label class="facet-label" for="facet_for_{{ $typeInputId }}">
                          <span class="custom-checkbox">
                            <input id="facet_for_{{ $typeInputId }}" data-search-url="" @if (in_array($typeSlug, $selectedMotoTypes)) checked="" @endif value="{{ $value }}" data-for="prod-type" data-value="{{ $typeSlug }}" type="checkbox">
                            <span class="ps-shown-by-js">
                              <i class="material-icons checkbox-checked"></i>
                            </span>
                          </span>
                            <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">{{ $value }}</a>
                          </label>
                        </li>
                      @endforeach
                      <li data-label="kamera">
                        <label class="facet-label" for="facet_for_kamera">
                          <span class="custom-checkbox">
                            <input id="facet_for_kamera" data-search-url="" @if (in_array((string) request()->camera, ['1', 'true'], true)) checked="" @endif value="1" data-for="prod-camera" data-value="1" type="checkbox">
                            <span class="ps-shown-by-js">
                              <i class="material-icons checkbox-checked"></i>
                            </span>
                          </span>
                          <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">Kamera</a>
                        </label>
                      </li>
                    </ul>
                  </section>

                  @include('components.moto-code-filter')
                </div>
              </div>
            </div>
            </form>
          </div>

        </div>
        <div class="loading-block-content" style="display: none; position:absolute;"><div class="loading-content"><svg class="machine" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 645 526">
              <defs></defs>
              <g>
                <path x="-173,694" y="-173,694" class="large-shadow" d="M645 194v-21l-29-4c-1-10-3-19-6-28l25-14 -8-19 -28 7c-5-8-10-16-16-24L602 68l-15-15 -23 17c-7-6-15-11-24-16l7-28 -19-8 -14 25c-9-3-18-5-28-6L482 10h-21l-4 29c-10 1-19 3-28 6l-14-25 -19 8 7 28c-8 5-16 10-24 16l-23-17L341 68l17 23c-6 7-11 15-16 24l-28-7 -8 19 25 14c-3 9-5 18-6 28l-29 4v21l29 4c1 10 3 19 6 28l-25 14 8 19 28-7c5 8 10 16 16 24l-17 23 15 15 23-17c7 6 15 11 24 16l-7 28 19 8 14-25c9 3 18 5 28 6l4 29h21l4-29c10-1 19-3 28-6l14 25 19-8 -7-28c8-5 16-10 24-16l23 17 15-15 -17-23c6-7 11-15 16-24l28 7 8-19 -25-14c3-9 5-18 6-28L645 194zM471 294c-61 0-110-49-110-110S411 74 471 74s110 49 110 110S532 294 471 294z"></path>
              </g>
              <g>
                <path x="-136,996" y="-136,996" class="medium-shadow" d="M402 400v-21l-28-4c-1-10-4-19-7-28l23-17 -11-18L352 323c-6-8-13-14-20-20l11-26 -18-11 -17 23c-9-4-18-6-28-7l-4-28h-21l-4 28c-10 1-19 4-28 7l-17-23 -18 11 11 26c-8 6-14 13-20 20l-26-11 -11 18 23 17c-4 9-6 18-7 28l-28 4v21l28 4c1 10 4 19 7 28l-23 17 11 18 26-11c6 8 13 14 20 20l-11 26 18 11 17-23c9 4 18 6 28 7l4 28h21l4-28c10-1 19-4 28-7l17 23 18-11 -11-26c8-6 14-13 20-20l26 11 11-18 -23-17c4-9 6-18 7-28L402 400zM265 463c-41 0-74-33-74-74 0-41 33-74 74-74 41 0 74 33 74 74C338 430 305 463 265 463z"></path>
              </g>
              <g>
                <path x="-100,136" y="-100,136" class="small-shadow" d="M210 246v-21l-29-4c-2-10-6-18-11-26l18-23 -15-15 -23 18c-8-5-17-9-26-11l-4-29H100l-4 29c-10 2-18 6-26 11l-23-18 -15 15 18 23c-5 8-9 17-11 26L10 225v21l29 4c2 10 6 18 11 26l-18 23 15 15 23-18c8 5 17 9 26 11l4 29h21l4-29c10-2 18-6 26-11l23 18 15-15 -18-23c5-8 9-17 11-26L210 246zM110 272c-20 0-37-17-37-37s17-37 37-37c20 0 37 17 37 37S131 272 110 272z"></path>
              </g>
              <g>
                <path x="-100,136" y="-100,136" class="small" d="M200 236v-21l-29-4c-2-10-6-18-11-26l18-23 -15-15 -23 18c-8-5-17-9-26-11l-4-29H90l-4 29c-10 2-18 6-26 11l-23-18 -15 15 18 23c-5 8-9 17-11 26L0 215v21l29 4c2 10 6 18 11 26l-18 23 15 15 23-18c8 5 17 9 26 11l4 29h21l4-29c10-2 18-6 26-11l23 18 15-15 -18-23c5-8 9-17 11-26L200 236zM100 262c-20 0-37-17-37-37s17-37 37-37c20 0 37 17 37 37S121 262 100 262z"></path>
              </g>
              <g>
                <path x="-173,694" y="-173,694" class="large" d="M635 184v-21l-29-4c-1-10-3-19-6-28l25-14 -8-19 -28 7c-5-8-10-16-16-24L592 58l-15-15 -23 17c-7-6-15-11-24-16l7-28 -19-8 -14 25c-9-3-18-5-28-6L472 0h-21l-4 29c-10 1-19 3-28 6L405 9l-19 8 7 28c-8 5-16 10-24 16l-23-17L331 58l17 23c-6 7-11 15-16 24l-28-7 -8 19 25 14c-3 9-5 18-6 28l-29 4v21l29 4c1 10 3 19 6 28l-25 14 8 19 28-7c5 8 10 16 16 24l-17 23 15 15 23-17c7 6 15 11 24 16l-7 28 19 8 14-25c9 3 18 5 28 6l4 29h21l4-29c10-1 19-3 28-6l14 25 19-8 -7-28c8-5 16-10 24-16l23 17 15-15 -17-23c6-7 11-15 16-24l28 7 8-19 -25-14c3-9 5-18 6-28L635 184zM461 284c-61 0-110-49-110-110S401 64 461 64s110 49 110 110S522 284 461 284z"></path>
              </g>
              <g>
                <path x="-136,996" y="-136,996" class="medium" d="M392 390v-21l-28-4c-1-10-4-19-7-28l23-17 -11-18L342 313c-6-8-13-14-20-20l11-26 -18-11 -17 23c-9-4-18-6-28-7l-4-28h-21l-4 28c-10 1-19 4-28 7l-17-23 -18 11 11 26c-8 6-14 13-20 20l-26-11 -11 18 23 17c-4 9-6 18-7 28l-28 4v21l28 4c1 10 4 19 7 28l-23 17 11 18 26-11c6 8 13 14 20 20l-11 26 18 11 17-23c9 4 18 6 28 7l4 28h21l4-28c10-1 19-4 28-7l17 23 18-11 -11-26c8-6 14-13 20-20l26 11 11-18 -23-17c4-9 6-18 7-28L392 390zM255 453c-41 0-74-33-74-74 0-41 33-74 74-74 41 0 74 33 74 74C328 420 295 453 255 453z"></path>
              </g>
            </svg></div></div>
        <div id="content-wrapper" class="col-md-12 col-lg-9">
          <section id="main">
            <section id="products">
{{--              <div class="tire-image-container" style="display: none">--}}
{{--                <div class="tire-image-cards">--}}
{{--                  --}}{{-- GRID VIEW --}}
{{--                  @php--}}
{{--                    $cbrand = '';--}}
{{--                    $index = 0;--}}
{{--                  @endphp--}}
{{--                  @foreach($tires as $tire)--}}

{{--                    @php--}}
{{--                      if (!$tire->tread) continue;--}}
{{--                      $brand = $tire->fullSize;--}}
{{--                      $tire->includeStock = true;--}}
{{--                      if ($cbrand!=$brand){--}}
{{--                        if ($index == 0) {--}}
{{--                          echo '</div><h4 class="tire-brand-name grid-t">' . $brand;--}}
{{--                          echo ' <span class="tire-type-title">Motociklu riepas</span><span style="margin: 0 auto;"></span><button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal">--}}
{{--                                    Filtrs (' . $filterCount . ')--}}
{{--                                  </button></h4><div class="row grid-ex pr-1">';--}}
{{--                        } else {--}}
{{--                          echo '</div><h4 class="tire-brand-name grid-t">' . $brand;--}}
{{--                          echo '</h4><div class="row grid-ex pr-1">';--}}
{{--                        }--}}

{{--                        $cbrand = $brand;--}}
{{--                        $stripe = 1;--}}
{{--                      }--}}

{{--                    @endphp--}}
{{--                    @if($tire->price1)--}}
{{--                      <a href="{{ route('motociklu-riepa', [strtolower(\Tires::getMotoTireBrand($tire->tread->brand_id)->title), strtolower(str_replace('/', '_', $tire->tread->title)), $tire->tire_id]) }}" class="grid-view-link">--}}
{{--                        <div class="tire-image-card sort-order">--}}
{{--                          <div class="text-center image-grid-overflow">--}}
{{--                            {!! App\Helper\Image::showGrid('moto', $tire->make_id) !!}--}}
{{--                          </div>--}}

{{--                          <div class="tire-list-caption">--}}

{{--                            <div class="card-title-text" data-toggle="tooltip" title="<div>{{$tire->title}}</div>">--}}
{{--                              {{$tire->title}}--}}
{{--                            </div>--}}

{{--                            <div class="tire-tread">--}}
{{--                              <b>{{$tire->d1}} / {{$tire->d2}} / {{$tire->d3}} </b>--}}
{{--                              <span data-toggle="tooltip" title="<span style='color: black'>{{ $tire->lisiDesc($tire->li, $tire->si) }}</span>">{{ $tire->li . $tire->si }}</span>--}}
{{--                              <span class="tire-image-code">{{$tire->code}}</span>--}}
{{--                            </div>--}}
{{--                            <div style="display: flex;">--}}
{{--                              <input type="checkbox" name="product_ids[]" value="{{$tire->tire_id}}" style="margin-right: 5px;">--}}
{{--                              <div class="rim-price-old" style="align-self: center;">€{{$tire->price1}}</div>--}}
{{--                              <div class="rim-price-red" style="align-self: center;">€{{$tire->price2}}</div>--}}

{{--                              <button style="margin-left: auto;" class="grid-buy-btn cart-shopping-button"--}}
{{--                                      data-toggle="modal"--}}
{{--                                      data-info="{{ $tire->tire_id }}"--}}
{{--                                      --}}{{--                                      data-info="{{ $currTire->tire_id }}--}}
{{--                                      onclick="event.preventDefault()"--}}
{{--                                      @hasrole('administrators')--}}
{{--                                        data-target="#"--}}
{{--                                      @else--}}
{{--                                        data-target="#blockcart-modal"--}}
{{--                                      @endhasrole>--}}
{{--                                <i class="material-icons">add_shopping_cart</i>--}}
{{--                              </button>--}}

{{--                              <span class="grid-dot {{ $tire->dotAvailable }} {{ $tire->stockCount }}" data-toggle="tooltip"--}}
{{--                                    data-html="true"--}}
{{--                                    onclick="event.preventDefault()"--}}
{{--                                    title="{{ $tire->stockAvailability }}">--}}
{{--                              <span class="sort-order" style="display: none;">{{ $tire->dotAvailable }}</span>--}}
{{--                            </span>--}}
{{--                            </div>--}}
{{--                          </div>--}}

{{--                        </div>--}}
{{--                      </a>--}}
{{--                    @endif--}}
{{--                    @php--}}
{{--                      $index++;--}}
{{--                    @endphp--}}
{{--                  @endforeach--}}
{{--                </div>--}}
{{--              </div>--}}
              <div id="">
                <div id="js-product-list">
                  <div class="products row title-flip">
                  </div>
{{--                  <div class="products row hide-price title-flip">--}}

{{--                    @php--}}
{{--                      $cbrand = '';--}}
{{--                      $index = 0;--}}
{{--                    @endphp--}}
{{--                    @foreach ($tires as $tire)--}}
{{--                      @php--}}
{{--                        if (!$tire->tread) continue;--}}
{{--                        $brand = $tire->fullSize;--}}
{{--                        $tire->includeStock = true;--}}
{{--                        if ($cbrand!=$brand){--}}
{{--                          if($index == 0) {--}}
{{--                            echo '<button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal">--}}
{{--                                    Filtrs ('. $filterCount .')--}}
{{--                                  </button><div class="filters" style="margin: 0 auto;"></div>';--}}
{{--                            echo '<h4 class="tire-brand-name">' . $cbrand . '<span class="tire-type-title flipped-title">Motociklu riepas</span></h4>';--}}
{{--                          } else {--}}
{{--                            echo '<h4 class="tire-brand-name">' . $cbrand . '</h4>';--}}
{{--                          }--}}
{{--                      @endphp--}}

{{--                    --}}{{--LIST VIEW--}}
{{--                    <table id="tires-table" class="table table-striped moto-sorter tires-table table-hover tablesorter">--}}
{{--                        <thead class="tires-thead sticky-table">--}}
{{--                        <tr>--}}
{{--                          <th scope="col"></th>--}}
{{--                          <th scope="col" class="table-tire-name-cell">Brends / modelis</th>--}}
{{--                          <th scope="col" class="hidden-sm-down text-center">Tips</th>--}}
{{--                          <th scope="col" class="hidden-sm-down text-center">LI/SI</th>--}}
{{--                          <th scope="col" class="hidden-sm-down text-center">Kods</th>--}}

{{--                          <th id="store-price-button" scope="col" class="text-center">Veikala cena</th>--}}
{{--                          <th id="store-sale-button" scope="col" class="text-center">Akcijas cena</th>--}}
{{--                          <th scope="col" class="hidden-sm-down">Piezīmes</th>--}}
{{--                          <th scope="col"></th>--}}
{{--                          <th scope="col">--}}
{{--                            <div class="tire-table-icon icon-question" title="Pieejamība" data-toggle="tooltip"></div>--}}
{{--                          </th>--}}

{{--                        </tr>--}}
{{--                        </thead>--}}
{{--                        <tbody id="tires-table-body">--}}
{{--                      @php--}}
{{--                        $cbrand = $brand;--}}
{{--                        $stripe = 1;--}}
{{--                      }--}}
{{--                      @endphp--}}
{{--                      @if ($loop->last) <h4 class="tire-brand-name">{!! $brand !!}</h4> @endif--}}

{{--                      <tr class="tire-table-row">--}}
{{--                        <th scope="row" class="tire-table-checkbox">--}}
{{--                          <input type="checkbox" value="{{ $tire->tire_id }}" name="product_ids[]"--}}
{{--                                 class="tire-table-checkbox">--}}
{{--                        </th>--}}

{{--                        <td class="table-tire-name-cell">--}}
{{--                          <a class="tire-table-link tippy"--}}
{{--                             data-tippy-content="<div><img data-src='{{ App\Helper\Image::showAd('moto', $tire->make_id) }}'></div>"--}}
{{--                             href="{{ route('motociklu-riepa', [strtolower(\Tires::getMotoTireBrand($tire->tread->brand_id)->title), strtolower(str_replace('/', '_', $tire->tread->title)), $tire->tire_id]) }}"--}}
{{--                             data-content="{{ $tire->fullName }}" data-article="{{ $tire->article }}" data-quantity="{{ $cartQty }}">--}}
{{--                            <div class="table-link-title">{{ $tire->title }}</div>--}}
{{--                          </a>--}}
{{--                        </td>--}}

{{--                        <td class="hidden-sm-down text-center">--}}
{{--                          <span data-toggle="tooltip"--}}
{{--                                title="<span style='color: black'>@if (isset($tire->typeDesc[1])) {{ $tire->typeDesc[1] }} @endif</span>">{{ $tire->motoType }}--}}
{{--                              </span>--}}
{{--                        </td>--}}

{{--                        <td class="hidden-sm-down text-center">--}}
{{--                          <span data-toggle="tooltip"--}}
{{--                                title="<span style='color: black'>{{ $tire->lisiDesc($tire->li, $tire->si) }}</span>">{{ $tire->li . $tire->si }}--}}
{{--                          </span>--}}
{{--                        </td>--}}

{{--                        <td class="hidden-sm-down text-center">--}}
{{--                            <span data-toggle="tooltip" title="<span style='color: black'>--}}
{{--                                                @php $codes = explode(' ', $tire->code); @endphp--}}
{{--                                                @foreach ($codes as $code)--}}
{{--                                                        @if (isset($code_array[$code]))--}}
{{--                                                                {!! $code_array[$code] . '<br>' !!}--}}
{{--                                                        @endif--}}
{{--                                                @endforeach--}}
{{--                                                @if (strpos($tire->code, 'DOT') !== false)--}}
{{--                                                  {!! $code_array['DOT'] !!}--}}
{{--                                                @endif--}}
{{--                                               </span>" class="hidden-sm-down table-cell prod-code">{{ $tire->code }}--}}
{{--                                    </span>--}}

{{--                        </td>--}}

{{--                        <td id="store-price" class="text-center store-price">€ {{ $tire->price1 }}</td>--}}
{{--                        <td id="sale-price" class="text-center tire-price-red">€ {{ $tire->price2 }}</td>--}}
{{--                        <td class="hidden-sm-down text-center @if($tire->comment == 'Izpārdošana!' || $tire->priceoffer == 1){{ 'sellout' }}@endif">{{$tire->comment}}</td>--}}

{{--                        <td class="shopping-cart-col">--}}
{{--                          <div class="clearfix atc_div text-right">--}}
{{--                            <button class="cart-shopping-button" data-toggle="modal"--}}
{{--                                    @if (Auth::user()) data-target="#" @else data-target="#blockcart-modal"--}}
{{--                                    @endif data-info="{{ $tire->tire_id }}"><i--}}
{{--                                class="material-icons">add_shopping_cart</i>--}}
{{--                            </button>--}}
{{--                          </div>--}}
{{--                        </td>--}}

{{--                        <td class="dot-availability text-center">--}}

{{--                            <span class="dot {{ $tire->dotAvailable }}" data-toggle="tooltip"--}}
{{--                                  data-html="true"--}}
{{--                                  title="{{ $tire->stockAvailability }}">--}}
{{--                              <span class="sort-order">{{ $tire->dotAvailable }}</span>--}}
{{--                            </span>--}}
{{--                        </td>--}}

{{--                      </tr>--}}
{{--                      @php--}}
{{--                        $index++;--}}
{{--                      @endphp--}}
{{--                      @endforeach--}}
{{--                        </tbody>--}}
{{--                      </table>--}}
{{--                  </span>--}}
{{--                  <nav class="pagination">--}}
{{--                    <div class="col-md-12">--}}
{{--                    </div>--}}
{{--                  </nav>--}}
{{--                </div>--}}
{{--                {{ $tires->links() }}--}}
              </div>
            </section>
          </section>
        </div>
      </div>
    </div>
  </div>
  <div class="hidden-md-up text-xs-right up">
    <a href="#header" class="back-to-top-button">
      <i class="material-icons"></i>
    </a>
  </div>
  <div class="modal fade" id="mobileFilterModal" tabindex="-1" role="dialog"
       aria-labelledby="mobileFilterModalTitle" aria-hidden="true">
    <div class="modal-dialog mobile-filter-modal" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          @include('components.mototirefilter')
        </div>
      </div>
    </div>
  </div>

<script>
  window.motoFilterCodeAliases = @json($motoFilterCodeAliases);
</script>
<script src="{{ asset('js/motoTiresAjax.js?rev=' . time()) }}"></script>
@endsection
