@extends('layouts.app')

@section('body-title', 'category')
@section('title', 'lang-' . app()->getLocale() . ' country-' . app()->getLocale() . ' layout-both-columns page-category tax-display-enabled category-id-21 category-jauni-lietie-diski category-id-parent-20 category-depth-level-3')
@section('meta_title', 'Lietie diski | R1 Riepu Serviss')
@section('meta_description', 'Lietie diski ar filtriem pēc parametriem un ražotājiem. R1 Riepu Serviss katalogs.')


@section('content')

  <div class="container">
    <div class="row">
      <div class="main-content clearfix col-md-12">
        <div id="left-column" class="col-md-12 col-lg-3">
          <!-- begin D:\OpenServer\domains\r1old/themes/classic/modules/ps_facetedsearch/ps_facetedsearch.tpl -->
          <div id="search_filters_wrapper">
            <form method="get" action="{{ route('lietie-diski-meklet') }}">
              <div id="search_filters" class="auto">
                <input type="hidden" id="facet_all_val" value="Visi">
                <div class="wrap">
                  <h4 class="text-uppercase h6">
                    {{--        <span id="search_filters_auto" class="params auto active">Auto</span>--}}
                    <span id="search_filters_params" class="params params-solo" style="width: 100%!important;">Parametri</span>
                  </h4>
                  <div class="can-collapse">
                    <span class="show_list active" data-dismiss="modal"><i class="material-icons"></i>Saraksts</span>
                    <span class="show_grid" data-dismiss="modal"><i class="material-icons"></i>Bilde</span>
                    <template id="facet-template">
                      <section class="facet clearfix">
                        <h1 class="h6 facet-title hidden-sm-down">Kods</h1>
                        <input type="text" value="" id="autofind_atr">
                        <button id="autofind_sub">Meklēt <i class="material-icons search"></i></button>
                      </section>
                    </template>
                    {{-- Change from top to auto --}}
                    <div class="sidebar-top">
                      <section class="facet clearfix">
                        <h1 class="h6 facet-title">Disku diametrs</h1>
                        <div class="title hidden-md-up" data-target="#facet_auto-dia" data-toggle="collapse" aria-expanded="true">
                          <h1 class="h6 facet-title">Disku diametrs</h1>
                          <span class="float-xs-right">
                            <span class="navbar-toggler collapse-icons">
                              <i class="material-icons add"></i>
                              <i class="material-icons remove"></i>
                            </span>
                          </span>
                        </div>
                        <select name="" id="" class="r1-select select-title">
                          <option value="Visi">Visi</option>
                          @foreach($diameters as $diameter)
                            <option value="{{$diameter}}">{{$diameter}}</option>
                          @endforeach
                        </select>
                      </section>
                    </div>
                    {{-- Change from auto to top --}}
                    <div class="sidebar-auto">
                      <section class="facet clearfix">
                        <h1 class="h6 facet-title">Skrūvju skaits</h1>
                        <div class="title hidden-md-up" data-target="#facet_53885" data-toggle="collapse">
                          <h1 class="h6 facet-title">Skrūvju skaits</h1>
                          <span class="float-xs-right">
                            <span class="navbar-toggler collapse-icons">
                              <i class="material-icons add"></i>
                              <i class="material-icons remove"></i>
                            </span>
                          </span>
                        </div>
                        <select name="currentSkr" id="" class="r1-select select-title tire-width select-rim-lugs">
                          <option value="Visi">Visi</option>
                          @foreach($lugs as $lug)
                            <option @if ($lug == $currentSkr) selected @endif value="{{$lug}}">{{$lug}}</option>
                          @endforeach
                        </select>
                      </section>
                      <section class="facet clearfix">
                        <h1 class="h6 facet-title">Attālums starp skrūvēm</h1>
                        <div class="title hidden-md-up" data-target="#facet_30026" data-toggle="collapse">
                          <h1 class="h6 facet-title">Attālums starp skrūvēm</h1>
                          <span class="float-xs-right">
                            <span class="navbar-toggler collapse-icons">
                              <i class="material-icons add"></i>
                              <i class="material-icons remove"></i>
                            </span>
                          </span>
                        </div>
                        <select name="currentPcd" id="" class="r1-select select-title select-rim-spread">
                          <option value="Visi">Visi</option>
                          @foreach($studs_spread as $stud_spread)
                            <option @if ($stud_spread == $currentPcd) selected @endif value="{{$stud_spread}}">{{$stud_spread}}</option>
                          @endforeach
                        </select>
                      </section>
                      <section class="facet clearfix">
                        <h1 class="h6 facet-title">Disku diametrs</h1>
                        <div class="title hidden-md-up" data-target="#facet_23486" data-toggle="collapse">
                          <h1 class="h6 facet-title">Disku diametrs</h1>
                          <span class="float-xs-right">
                            <span class="navbar-toggler collapse-icons">
                              <i class="material-icons add"></i>
                              <i class="material-icons remove"></i>
                            </span>
                          </span>
                        </div>
                        <select name="currentDia" id="" class="r1-select select-title select-rim-diameter">
                          <option value="Visi">Visi</option>
                          @foreach($diameters as $diameter)
                            <option @if ($diameter == $currentDia) selected @endif value="{{$diameter}}">{{$diameter}}</option>
                          @endforeach
                        </select>
                      </section>
                      <section class="facet clearfix">
                        <h1 class="h6 facet-title">Izbīdījums</h1>
                        <div class="title hidden-md-up" data-target="#facet_37134" data-toggle="collapse">
                          <h1 class="h6 facet-title">Izbīdījums</h1>
                          <span class="float-xs-right">
                            <span class="navbar-toggler collapse-icons">
                                <i class="material-icons add"></i>
                                <i class="material-icons remove"></i>
                            </span>
                          </span>
                        </div>
                        <select name="currentEt" id="" class="r1-select select-title select-rim-offset">
                          <option value="Visi">Visi</option>
                          @foreach($offsets as $offset)
                            <option @if ($offset == $currentEt) selected @endif value="{{$offset}}">{{$offset}}</option>
                          @endforeach
                        </select>
                      </section>
                    </div>
                    <section class="facet clearfix">
                      <button id="autofind_sub" type="submit">Meklēt <i class="material-icons search"></i>
                      </button>
                    </section>
                  </div>
                </div>
                <div class="wrap hidden-sm-down">
                  <div class="sidebar-bottom">
                    <section class="facet clearfix facet--availability">
                      <h1 class="h6 facet-title">Atlase</h1>
                      <ul class="collapse">
                        <li class="show-selected-checkbox-li">
                          <label class="facet-label" for="show-selected-checkbox" style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                                      <span class="custom-checkbox">
                                        <input type="checkbox" class="tire-table-checkbox" id="show-selected-checkbox"
                                               title="Rādīt tikai atzīmētās preces" disabled>
                                        <span class="ps-shown-by-js">
                                          <i class="material-icons checkbox-checked"></i>
                                        </span>
                                      </span>
                            <span>Rādīt izvēlētos</span>
                          </label>
                        </li>
                      </ul>
                      <h1 class="h6 facet-title">Pieejamība</h1>
                      <ul id="facet_availability" class="collapse">
                        <li>
                          <label class="facet-label" for="facet_availability_0" style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                                    <span class="custom-checkbox">
                                      <input id="facet_availability_0" class="green" {{-- @if (in_array('green', $availability)) checked @endif --}} type="checkbox" name="availability[]"
                                             data-search-url="#" value="green"
                                             data-for="dot" data-value="green" data-color="green">
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
                                      <input id="facet_availability_1" class="yellow" {{-- @if (in_array('yellow', $availability)) checked @endif --}} type="checkbox" name="availability[]"
                                             data-search-url="#" value="yellow"
                                             data-for="dot" data-value="yellow" data-color="yellow">
                                      <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                                    </span>
                            Pasūtāms
                            <span class="dot yellow" style="float:right;margin-top: 3px;"></span>
                          </label>
                        </li>
                        <li>
                          <label class="facet-label" for="facet_availability_2" style="width: 100%;text-align: left;cursor: pointer">
                                    <span class="custom-checkbox">
                                      <input id="facet_availability_2" class="red" {{-- @if (in_array('red', $availability)) checked @endif --}} type="checkbox" name="availability[]"
                                             data-search-url="#" value="red"
                                             data-for="dot" data-value="red" data-color="red">
                                      <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                                    </span>
                            Zvaniet!
                            <span class="dot red" style="float:right;margin-top: 3px;"></span>
                          </label>
                        </li>
                      </ul>
                    </section>
                  </div>
                </div>
              </div>
            </form>
          </div>

        </div>
        <div id="content-wrapper" class="col-md-12 col-lg-9">
          <section id="main">
            <section id="products" class="">
              <div class="tire-image-container" style="display: none">
                <div class="tire-image-cards">
                  {{-- GRID VIEW --}}
                  @php
                    $cbrand = '';
                    $index = 0;
                  @endphp
                  @foreach($rims as $rim)
                    @php
                      $brand = $rim->brandTitle;
                      $rim->includeStock = true;
                      if ($cbrand != $brand){
                        if ($index == 0) {
                          echo '</div><h4 class="tire-brand-name grid-t">' . $brand;
                          echo ' <span class="tire-type-title">Lietie diski</span><span style="margin: 0 auto;"></span><button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal">
                                    Filtrs
                            </button></h4></h4><div class="row grid-ex pr-1">';
                        } else {
                          echo '</div><h4 class="tire-brand-name grid-t">' . $brand;
                          echo '</h4><div class="row grid-ex pr-1">';
                        }
                        $cbrand = $brand;
                        $stripe = 1;
                      } else {
                        $brand = str_replace(" ", "", $brand);
                      }
                    @endphp
                    <a href="{{ route('kvadru-disks', [\Str::slug($rim->brandTitle), strtolower(str_replace('/', '_', $rim->treadTitle)), $rim->rim_id]) }}"
                       class="grid-view-link"
                       data-article="{{ $rim->article }}">
                      <div class="tire-image-card sort-order">
                        <div class="text-center image-grid-overflow">
                          {!! App\Helper\Image::showGrid('quadr-rim', $rim->make_id) !!}
                        </div>

                        <div class="tire-list-caption">

                          <div class="card-title-text" data-toggle="tooltip" title="<div>{{$rim->title}}</div>">
                            {{$rim->title}}
                          </div>

                          <div class="rim-tread">
                            <b>{{ $rim->d1 }}*{{ $rim->d3 }} ({{ $rim->skr }}*{{$rim->pcd}} et{{$rim->et}})</b>
                          </div>
                          <div style="display: flex;">
                            <input type="checkbox" name="product_ids[]" value="{{$rim->rim_id}}" style="margin-right: 5px;">
                            <div class="rim-price-old" style="align-self: center;">€{{$rim->price1}}</div>
                            <div class="rim-price-red" style="align-self: center;">€{{$rim->price2}}</div>

                            <span style="margin-left: auto;" data-toggle="tooltip" data-html="true"
                                  title="<span style='color: black'>Pievienot grozam</span>">

                                      <button class="grid-buy-btn cart-shopping-button"
                                              data-toggle="modal"
                                              data-info="{{ $rim->tire_id }}"
                                              onclick="event.preventDefault()"
                                              @hasrole('administrators')
                                                data-target="#"
                                              @else
                                data-target="#blockcart-modal"
                                @endhasrole>
                                <i class="material-icons">add_shopping_cart</i>
                                </button>
                                    </span>

                            <span class="grid-dot {{ $rim->dotAvailable }} {{ $rim->stockCount }}"
                                  data-toggle="tooltip"
                                  data-html="true"
                                  onclick="event.preventDefault()"
                                  title="<span>{{ $rim->stockAvailability }}</span>">
                                      <span class="sort-order" style="display: none;">{{ $rim->dotAvailable }}</span>
                                    </span>
                          </div>
                        </div>
                      </div>
                    </a>
                    @php
                      $index++;
                    @endphp
                  @endforeach
                </div>
              </div>
              {{-- LIST VIEW --}}
              <div id="js-product-list">
                <div class="products row hide-price title-flip">
                  @php
                    $cbrand = '';
                    $index = 0;
                  @endphp
                  @foreach ($rims as $rim)
                    @php
                      $brand = $rim->d3;
                      $rim->includeStock = true;
                      if ($cbrand!=$brand){



                      $cbrand = $brand;
                      $stripe = 1;
                    @endphp
                    <table id="tires-table" class="table table-striped rims-sorter tires-table table-hover tablesorter">
                      <thead class="tires-thead sticky-top">
                      <tr>
                        <th scope="col"></th>
                        <th scope="col">Nosaukums</th>
                        <th scope="col" class="text-center">Izmērs</th>
                        <th scope="col" class="hidden-sm-down text-center">Skrūvju attālums</th>
                        <th scope="col" class="hidden-sm-down text-center">ET</th>
                        <th scope="col" class="hidden-sm-down text-center">Krāsa</th>

                        <th id="store-price-button" scope="col" class="text-center">Veikala cena</th>
                        <th id="store-sale-button" scope="col" class="text-center">Akcijas cena</th>

                        <th scope="col" class="hidden-sm-down text-center">Piezīmes</th>
                        <th scope="col"></th>
                        <th scope="col"
                            data-toggle="tooltip"
                            data-html="true"
                            title="<span style='color: black'>Pieejamība</span>">
                          <span class="tire-table-icon icon-question"></span>
                        </th>

                      </tr>
                      </thead>
                      <tbody id="tires-table-body">
                      @if ($loop->first) <button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal" style="margin-left: auto;">Filtrs</button><h4 class="tire-brand-name"><span class="text-uppercase flipped-title tire-brand-name" style="color:black;">Kvadru diski</span> R{{ $brand }} </h4>@endif
                        @if (!$loop->first) <h4 class="tire-brand-name"><span class="text-uppercase flipped-title tire-brand-name" style="color:black;">Kvadru diski</span> R{{ $brand }} </h4>@endif
                        @php
                          $cbrand = $brand;
                          $stripe = 1;
                        }
                        @endphp
                        <tr class="tire-table-row">
                          <th scope="row" class="tire-table-checkbox">
                            <input type="checkbox" value="{{$rim->rim_id}}" name="product_ids[]"
                                   class="tire-table-checkbox">
                          </th>

                          <td class="table-tire-name-cell">
                            <a data-toggle="tooltip" data-html="true" class="tire-table-link"
                               title='{!! App\Helper\Image::show('quadr-rim', $rim->make_id) !!}'
                               href="{{ route('kvadru-disks', [\Str::slug($rim->brandTitle), strtolower(str_replace('/', '_', $rim->treadTitle)), $rim->rim_id]) }}"
                               data-content="{{ $rim->fullName }}"
                               data-article="{{ $rim->article }}"
                               data-quantity="{{ $cartQty }}">
                              {{ $rim->fullTitle }}
                            </a>
                          </td>
                          <td class="text-center">
                            {{$rim->d1}}*{{$rim->d3}}
                          </td>

                          <td class="text-center hidden-sm-down">
                            {{$rim->skr}} * {{$rim->pcd}}
                          </td>

                          <td class="text-center hidden-sm-down">
                            {{ $rim->et }}
                          </td>

                          <td class="hidden-sm-down text-center">
                            {{$rim->color}}
                          </td>

                          <td id="store-price" class="text-center store-price">€ {{$rim->price2}}</td>
                          <td id="sale-price" class="text-center tire-price-red sale-price">€ {{$rim->price3}}</td>
                          <td class="hidden-sm-down text-center">{{$rim->comment}}</td>

                          <td class="shopping-cart-col">
                            <div class="clearfix atc_div text-right">
                              {{--                                    <button class="cart-shopping-button grid-cart-btn" data-toggle="modal">--}}
                              {{--                                      <i class="material-icons">add_shopping_cart</i>--}}
                              {{--                                    </button>--}}
                              <button class="cart-shopping-button" data-toggle="modal"
                                      @if (Auth::check() && Auth::user()->hasRole('administrators')) data-target="#" @else data-target="#blockcart-modal"
                                      @endif data-info="{{ $rim->rim_id }}"><i
                                  class="material-icons">add_shopping_cart</i>
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
                          </td>
                        </tr>
                      @php
                        $index++;
                      @endphp
                      @endforeach
                      </tbody>
                    </table>
                </div>
                <nav class="pagination">
                  <div class="col-md-12">
                  </div>
                </nav>
                {{--                      {{ $rims->links() }}--}}
              </div>
              <div id="js-product-list-bottom">
                <div id="js-product-list-bottom"></div>
              </div>
            </section>
          </section>
        </div>

        {{--                <div id="content-wrapper" class="col-md-12 col-lg-9">--}}
        {{--                    <section id="main">--}}
        {{--                        <section id="products">--}}
        {{--                            <div id="" class="hidden-sm-down">--}}
        {{--                                <section id="js-active-search-filters" class="hide">--}}
        {{--                                    <h1 class="h6 hidden-xs-up">Active filters</h1>--}}
        {{--                                </section>--}}
        {{--                            </div>--}}

        {{--                             GRID VIEW--}}
        {{--                              <h4 class="rims-title text-uppercase" style="color: black">Lietie diski</h4>--}}
        {{--                              <div class="tire-image-container" style="display: none">--}}
        {{--                                <div class="tire-image-cards">--}}
        {{--                                  @php--}}
        {{--                                    $cbrand = '';--}}
        {{--                                  @endphp--}}
        {{--                                  @foreach($rims as $rim)--}}
        {{--                                    @php--}}
        {{--                                      $brand = $rim->brand_title;--}}
        {{--                                      if ($cbrand!=$brand){--}}
        {{--                                        echo '</div><h4 class="tire-brand-name grid-t">' . $brand . '</h4><div class="row grid-ex pr-1">';--}}
        {{--                                        $cbrand = $brand;--}}
        {{--                                        $stripe = 1;--}}
        {{--                                      } else {--}}
        {{--                                          $brand = str_replace(" ", "", $brand);--}}
        {{--                                      }--}}
        {{--                                    @endphp--}}
        {{--                                    <a href="{{ route('lietais-disks', [\Str::slug($rim->brand_title), \Str::slug($rim->title), $rim->rim_id]) }}" class="">--}}
        {{--                                      <div class="tire-image-card sort-order card">--}}

        {{--                                        <div class="text-center">--}}
        {{--                                          {!! \Image::showGrid('auto-rim', $rim->make_id) !!}--}}
        {{--                                        </div>--}}

        {{--                                        <div class="tire-list-caption">--}}
        {{--                                          <div class="card-title-text">{{$rim->title}}</div>--}}
        {{--                                          <div class="rim-tread">--}}
        {{--                                            {{ $rim->d1 }}*{{ $rim->d3 }} ({{ $rim->skr }}*{{$rim->pcd}} et{{$rim->et}})--}}
        {{--                                          </div>--}}
        {{--                                          <div style="display: inline-flex">--}}
        {{--                                            <div class="rim-price-old">€{{$rim->price2}}</div>--}}
        {{--                                            <div class="rim-price-red">€{{$rim->price3}}</div>--}}
        {{--                                          </div>--}}

        {{--                                        </div>--}}
        {{--                                      </div>--}}
        {{--                                    </a>--}}
        {{--                                  @endforeach--}}
        {{--                                </div>--}}
        {{--                              </div>--}}

        {{--                               LIST VIEW--}}
        {{--                              <div id="js-product-list">--}}
        {{--                                <table id="tires-table" class="table rims-sorter tires-table table-hover tablesorter">--}}
        {{--                                  <thead class="tires-thead">--}}
        {{--                                  <tr>--}}
        {{--                                    <th scope="col"></th>--}}
        {{--                                    <th scope="col">Nosaukums</th>--}}
        {{--                                    <th scope="col" class="text-center">Izmērs</th>--}}
        {{--                                    <th scope="col" class="hidden-sm-down text-center">Skrūvju attālums</th>--}}
        {{--                                    <th scope="col" class="hidden-sm-down text-center">ET</th>--}}
        {{--                                    <th scope="col" class="hidden-sm-down text-center">Centrs</th>--}}
        {{--                                    <th scope="col" class="hidden-sm-down text-center">Krāsa</th>--}}

        {{--                                    <th id="store-price-button" scope="col" class="text-center">Veikala cena</th>--}}
        {{--                                    <th id="store-sale-button" scope="col" class="text-center">Akcijas cena</th>--}}

        {{--                                    <th scope="col" class="hidden-sm-down text-center">Piezīmes</th>--}}
        {{--                                    <th scope="col"></th>--}}
        {{--                                    <th scope="col">--}}
        {{--                                      <div class="tire-table-icon icon-question" title="Pieejamība" data-toggle="tooltip"></div>--}}
        {{--                                    </th>--}}

        {{--                                  </tr>--}}
        {{--                                  </thead>--}}
        {{--                                  <tbody id="tires-table-body">--}}
        {{--                                  @foreach($rims as $rim)--}}

        {{--                                    <tr class="tire-table-row">--}}
        {{--                                      <th scope="row" class="tire-table-checkbox">--}}
        {{--                                        <input type="checkbox" value="{{$rim->rim_id}}" name="product_ids[]"--}}
        {{--                                               class="tire-table-checkbox">--}}
        {{--                                      </th>--}}

        {{--                                      <td>--}}
        {{--                                        <a data-toggle="tooltip" data-html="true" class="rim-table-link tire-table-link"--}}
        {{--                                           @if (\Image::exists('auto-rim', $rim->make_id))--}}
        {{--                                           title="{{ \Image::show('auto-rim', $rim->make_id) }}"--}}
        {{--                                           @else--}}
        {{--                                           title="<img src='{{ asset('img/p/en-default-home_default.jpg') }}'>"--}}
        {{--                                           @endif--}}
        {{--                                           href="{{ route('lietais-disks', [\Str::slug($rim->brand_title), \Str::slug($rim->title), $rim->rim_id]) }}"--}}
        {{--                                        >--}}
        {{--                                          {{ $rim->brand_title . ' ' . $rim->title }}--}}
        {{--                                        </a>--}}
        {{--                                      </td>--}}
        {{--                                      <td class="text-center">--}}
        {{--                                          {{$rim->d1}}*{{$rim->d3}}--}}
        {{--                                      </td>--}}

        {{--                                      <td class="text-center hidden-sm-down">--}}
        {{--                                        {{$rim->skr}} * {{$rim->pcd}}--}}
        {{--                                      </td>--}}

        {{--                                      <td class="text-center hidden-sm-down">--}}
        {{--                                        et{{ $rim->et }}--}}
        {{--                                      </td>--}}

        {{--                                      <td class="text-center hidden-sm-down">--}}
        {{--                                        {{$rim->dc}}--}}
        {{--                                      </td>--}}

        {{--                                      <td class="hidden-sm-down text-center">--}}
        {{--                                        {{$rim->color}}--}}
        {{--                                      </td>--}}

        {{--                                      <td id="store-price" class="text-center store-price">€ {{$rim->price2}}</td>--}}
        {{--                                      <td id="sale-price" class="text-center tire-price-red sale-price">€ {{$rim->price3}}</td>--}}
        {{--                                      <td class="hidden-sm-down text-center"></td>--}}

        {{--                                      <td class="shopping-cart-col">--}}
        {{--                                        <div class="clearfix atc_div text-right">--}}
        {{--                                          <button class="cart-shopping-button grid-cart-btn" data-toggle="modal">--}}
        {{--                                            <i class="material-icons">add_shopping_cart</i>--}}
        {{--                                          </button>--}}
        {{--                                        </div>--}}
        {{--                                      </td>--}}

        {{--                                      <td class="dot-availability text-center">--}}
        {{--                                        <span class="dot red" data-toggle="tooltip"--}}
        {{--                                              data-html="true"--}}
        {{--                                              title="red">--}}
        {{--                                          <span class="sort-order">red</span>--}}
        {{--                                        </span>--}}
        {{--                                      </td>--}}
        {{--                                    </tr>--}}
        {{--                                  @endforeach--}}
        {{--                                  </tbody>--}}
        {{--                                </table>--}}
        {{--                              </div>--}}
        {{--                          {{ $rims->links() }}--}}
        {{--                            <div id="js-product-list-bottom">--}}
        {{--                                <div id="js-product-list-bottom"></div>--}}
        {{--                            </div>--}}
        {{--                        </section>--}}
        {{--                    </section>--}}
        {{--                </div>--}}


      </div>
      {{--            @include('components.right-sidebar')--}}
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
          @include('components.quadrrimsfilter')
        </div>
      </div>
    </div>
  </div>
@endsection
