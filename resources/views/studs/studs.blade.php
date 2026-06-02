@extends('layouts.app')

@section('body-title', 'category')
@section('title', 'lang-' . app()->getLocale() . ' country-' . app()->getLocale() . ' layout-both-columns page-category tax-display-enabled category-id-21 category-jauni-lietie-diski category-id-parent-20 category-depth-level-3')
@section('meta_title', 'Radzes | R1 Riepu Serviss')
@section('meta_description', 'Skrūvējamas radzes ar filtriem pēc parametriem. R1 Riepu Serviss katalogs.')

@section('content')

<div class="container">
    <div class="row">
      <div class="main-content clearfix col-md-12">
        <div id="left-column" class="col-md-12 col-lg-3">
          <div id="search_filters_wrapper">
            <form method="get" action="{{ route('radzes-meklet') }}">
              <div id="search_filters" class="auto">
                <input type="hidden" id="facet_all_val" value="Visi">
                <div class="wrap">
                  <h6 class="text-uppercase h6">
                    Filtrs
                  </h6>
                  <div class="can-collapse">
                    <span class="show_list active" data-dismiss="modal"><i class="material-icons"></i>Saraksts</span>
                    <span class="show_grid" data-dismiss="modal"><i class="material-icons"></i>Bildes</span>
                    <template id="facet-template">
                      <section class="facet clearfix">
                        <h1 class="h6 facet-title hidden-sm-down">Kods</h1>
                        <input type="text" value="" id="autofind_atr">
                        <button id="autofind_sub">Meklēt <i class="material-icons search"></i></button>
                      </section>
                    </template>
                    <div class="sidebar-auto">
                      <section class="facet clearfix">
                        <h1 class="h6 facet-title">Pielietojums</h1>
                        <div class="title hidden-md-up" data-target="#facet_auto-make" data-toggle="collapse" aria-expanded="true">
                          <h1 class="h6 facet-title">Pielietojums</h1>
                          <span class="float-xs-right">
                  <span class="navbar-toggler collapse-icons">
                      <i class="material-icons add"></i>
                      <i class="material-icons remove"></i>
                  </span>
                </span>
                        </div>
                        <select name="application" id="" class="r1-select select-title select-application">
                          <option value="Visi">Visi</option>
                          @foreach($applications as $application)
                            <option @if ($application == $currBrand) selected @endif value="{{$application}}">{{$application}}</option>
                          @endforeach
                        </select>

                      </section>
                      <section class="facet clearfix">
                        <h1 class="h6 facet-title">Garums</h1>
                        <div class="title hidden-md-up" data-target="#facet_auto-model" data-toggle="collapse" aria-expanded="true">
                          <h1 class="h6 facet-title">Garums</h1>
                          <span class="float-xs-right">
                            <span class="navbar-toggler collapse-icons">
                              <i class="material-icons add"></i>
                              <i class="material-icons remove"></i>
                            </span>
                          </span>
                        </div>

                        <select name="stud_length" id="" class="r1-select select-title select-studs-length">
                          <option value="Visi">Visi</option>
                          @foreach($stud_lengths as $stud_length_id => $stud_length)
                            <option @if ($curr_length == $stud_length) selected @endif value="{{$stud_length}}">{{$stud_length}}</option>
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
                      <h1 class="h6 facet-title hidden-sm-down">Atlase</h1>
                      <ul class="collapse">
                        <li class="show-selected-checkbox-li">
                          <label class="facet-label" for="show-selected-checkbox" style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                <span class="custom-checkbox">
                  <input type="checkbox" value="only_selected" class="tire-table-checkbox" id="show-selected-checkbox" name="product_ids[]" title="Rādīt tikai atzīmētās preces" disabled>
                  <span class="ps-shown-by-js">
                    <i class="material-icons checkbox-checked"></i>
                  </span>
                </span>
                            <span>Atrādīt izvēlētos</span>
                          </label>
                        </li>
                      </ul>
                      <h1 class="h6 facet-title hidden-sm-down">Pieejamība</h1>
                      <ul id="facet_availability" class="collapse">
                        <li>
                          <label class="facet-label" for="facet_availability_0" style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                <span class="custom-checkbox">
                    <input id="facet_availability_0" type="checkbox" data-search-url="#" data-color="green" class="green">
                    <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                </span>
                            Pieejams
                            <span class="dot green" style="float:right;margin-top: 3px;"></span>
                          </label>
                        </li>
                        <li>
                          <label class="facet-label" for="facet_availability_1" style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                <span class="custom-checkbox">
                    <input id="facet_availability_1" type="checkbox" data-search-url="#" data-color="yellow" class="yellow">
                    <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                </span>
                            Pasutams
                            <span class="dot yellow" style="float:right;margin-top: 3px;"></span>
                          </label>
                        </li>
                        <li>
                          <label class="facet-label" for="facet_availability_2" style="width: 100%;text-align: left;cursor: pointer">
                <span class="custom-checkbox">
                    <input id="facet_availability_2" type="checkbox" data-search-url="#" data-color="red" class="red">
                    <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                </span>
                            Zvaniet!
                            <span class="dot red" style="float:right;margin-top: 3px;"></span>
                          </label>
                        </li>
                      </ul>
                      <button class="filter-button" type="submit">Filtrēt <i class="material-icons search"></i></button>
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
              @if(!$studs->isEmpty())
              <div class="tire-image-container" style="display: none">
                <div class="tire-image-cards">
                  <div style="display: flex; padding: 5px 0;">
                    <h4 class="text-uppercase tire-brand-name text-black" style="color: black;">Skrūvējamas radzes</h4>
                    <span style="margin: 0 auto;"></span>
                    <button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal">Filtrs</button>
                  </div>
                  <div class="row grid-ex pr-1" style="padding:0!important;">
                  @foreach($studs as $stud)
                  @if($stud->price1)
                    <a
                      href="{{ route('radze', [$stud->brand, strtolower(str_replace('/', '_', $stud->t_title)), $stud->stud_id]) }}"
                      class="grid-view-link"
                      data-article="{{ $stud->article }}">
                      <div class="tire-image-card sort-order">
                        <div class="text-center image-grid-overflow">
                          {!! App\Helper\Image::showGrid('studs', $stud->make_id) !!}
                        </div>

                        <div class="tire-list-caption">

                          <div class="card-title-text" data-toggle="tooltip" title="<div>{{$stud->fullName}}</div>">
                            {{$stud->fullName}}
                          </div>

                          <div class="tire-tread">
                            <b>{{$stud->stud_length}}mm</b>
{{--                            <b>{{$stud->application}}</b>--}}
                            <span data-toggle="tooltip"
                                  ></span>
                            <span class="tire-image-code">{{$stud->code}}</span>
                          </div>
                          <div style="display: flex;">
                            <input type="checkbox" name="product_ids[]" value="{{$stud->stud_id}}"
                                   style="margin-right: 5px;">
                            <div class="rim-price-old" style="align-self: center;">€{{$stud->price1}}</div>
                            <div class="rim-price-red" style="align-self: center;">€{{$stud->price2}}</div>
                            {{--                            <i class="material-icons" style="margin-left: auto;">add_shopping_cart</i>--}}
                            <span style="margin-left: auto;" data-toggle="tooltip"
                                  title="<span style='color: black'>Pievienot grozam</span>">
    {{--                              <button class="grid-buy-btn" data-toggle="modal"--}}
                              {{--                                      @hasrole('administrators') data-target=""--}}
                              {{--                                      @else data-target="#blockcart-modal"--}}
                              {{--                                      @endhasrole data-info="{{ $tire->tire_id }}" onclick="event.preventDefault()">--}}
                              {{--                                <i class="material-icons">add_shopping_cart</i>--}}
                              {{--                              </button>--}}

                              <button class="grid-buy-btn cart-shopping-button"
                                      data-toggle="modal"
                                      data-info="{{ $stud->stud_id }}"
                                      {{--                                      data-info="{{ $currTire->tire_id }}--}}
                                      onclick="event.preventDefault()"
                                      @hasrole('administrators')
                                        data-target="#"
                                      @else
                                data-target="#blockcart-modal"
                                @endhasrole>
                                <i class="material-icons">add_shopping_cart</i>
                                </button>
                            </span>

                            {{--                            <div class="clearfix atc_div text-right">--}}
                            {{--                              <button class="grid-buy-btn" data-toggle="modal"--}}
                            {{--                                      @hasrole('administrators') data-target=""--}}
                            {{--                                      @else data-target="#blockcart-modal"--}}
                            {{--                                      @endhasrole data-info="{{ $tire->tire_id }}" onclick="event.preventDefault()">--}}
                            {{--                              <i class="material-icons">add_shopping_cart</i>--}}
                            {{--                              </button>--}}
                            {{--                            </div>--}}

                            <span class="grid-dot {{ $stud->dotAvailable }}"
                                  data-toggle="tooltip"
                                  data-html="true"
                                  onclick="event.preventDefault()"
                                  title="{{ $stud->stockAvailability }}">
                              <span class="sort-order" style="display: none;">{{ $stud->dotAvailable }}</span>
                            </span>
                          </div>
                        </div>
                        {{--                        <button class="grid-shopping-button grid-cart-btn" data-toggle="modal" data-target="#blockcart-modal" data-info="148204">Pirkt--}}
                        {{--                        </button>--}}


                      </div>
                    </a>
                  @endif
                  @endforeach
                  </div>
                </div>
              </div>

              {{-- LIST VIEW --}}


              <div id="js-product-list">
                <div style="display: flex; padding: 5px 0;">
                  <h4 class="text-uppercase tire-brand-name text-black" style="color: black;">Skrūvējamas radzes</h4>
                  <span style="margin: 0 auto;"></span>
                  <button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal">Filtrs</button>
                </div>
{{--                <h4 class="text-uppercase tire-brand-name text-black" style="color: black;">Radzes</h4>--}}
                <table id="tires-table"
                       class="table table-striped studs-sorter tires-table table-hover tablesorter">
                  <thead class="tires-thead sticky-table">
                  <tr>
                    <th scope="col"></th>
                    <th scope="col" class="table-tire-name-cell">Brends / modelis</th>
                    <th scope="col" class="hidden-sm-down text-center">Radzes garums</th>
                    <th scope="col" class="hidden-sm-down text-center">Daudzums</th>
                    <th id="store-price-button" scope="col" class="text-center">
                      Veikala cena
                    </th>
                    <th id="store-sale-button" scope="col" class="text-center">Akcijas cena</th>
                    <th scope="col" class="hidden-sm-down text-center">Piezīmes</th>
                    <th scope="col"></th>
                    <th scope="col">
                      <div class="tire-table-icon icon-question" title="Pieejamība" data-toggle="tooltip"></div>
                    </th>

                  </tr>
                  </thead>
                  <tbody id="tires-table-body">

                  @foreach ($studs as $stud)
                  <tr class="tire-table-row">
                    <th scope="row" class="tire-table-checkbox">
                      <input type="checkbox" value="{{ $stud->stud_id }}" name="product_ids[]"
                             class="tire-table-checkbox">
                    </th>

                    <td class="table-tire-name-cell">
                      <a data-toggle="tooltip" data-html="true" class="tire-table-link" title='{!! App\Helper\Image::show('studs', $stud->make_id) !!}'
                         href="{{ route('radze', [$stud->brand, strtolower(str_replace('/', '_', $stud->t_title)), $stud->stud_id]) }}"
                         data-content="{{ $stud->fullName }}"
                         data-article="{{ $stud->article }}"
                         data-quantity="{{ $cartQty }}">
                        <div class="table-link-title">{{ $stud->fullName }}</div>
                      </a>
                    </td>

                    <td class="hidden-sm-down text-center">{{ $stud->stud_length }} mm</td>
                    <td class="hidden-sm-down text-center">{{ $stud->stud_count }}</td>

                    <td id="store-price" class="text-center store-price">€ {{ $stud->price1 }}</td>
                    <td id="sale-price" class="text-center tire-price-red sale-price">€ {{ $stud->price2 }}</td>
                    <td class="hidden-sm-down text-center">{{$stud->comment}}</td>

                    <td class="shopping-cart-col">
                      <div class="clearfix atc_div text-right">
                        <button class="cart-shopping-button" data-toggle="modal"
                                @hasrole('administrators') data-target="#" @else data-target="#blockcart-modal" @endhasrole data-info="{{ $stud->stud_id }}"><i
                          class="material-icons">add_shopping_cart</i>
                        </button>
                      </div>
                    </td>

                    <td class="dot-availability text-center">
                            <span class="dot {{ $stud->dotAvailable }} {{ $stud->stockCount }}" data-toggle="tooltip"
                                  data-html="true"
                                  title="{{ $stud->stockAvailability }}">
                              <span class="sort-order">{{ $stud->dotAvailable }}</span>
                            </span>
                    </td>

                  </tr>
                  @endforeach
                  </tbody>
                </table>
              </div>
              @endif
              <div id="js-product-list-bottom">

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
        <div id="search_filters_wrapper" class="hidden-sm-down">
          <form method="get" action="{{ route('radzes-meklet') }}">
            <div id="search_filters" class="auto">
              <input type="hidden" id="facet_all_val" value="Visi">
              <div class="wrap">
                <h6 class="text-uppercase h6 hidden-sm-down">
                  Filtrs
                </h6>
                <div class="can-collapse">
                  <span class="show_list active" data-dismiss="modal"><i class="material-icons"></i>Saraksts</span>
                  <span class="show_grid" data-dismiss="modal"><i class="material-icons"></i>Bildes</span>
                  <template id="facet-template">
                    <section class="facet clearfix">
                      <h1 class="h6 facet-title hidden-sm-down">Kods</h1>
                      <input type="text" value="" id="autofind_atr">
                      <button id="autofind_sub">Meklēt <i class="material-icons search"></i></button>
                    </section>
                  </template>
                  <div class="sidebar-auto">
                    <section class="facet clearfix">
                      <h1 class="h6 facet-title hidden-sm-down">Pielietojums</h1>
                      <div class="title hidden-md-up" data-target="#facet_auto-make" data-toggle="collapse" aria-expanded="true">
                        <h1 class="h6 facet-title">Pielietojums</h1>
                        <span class="float-xs-right">
                  <span class="navbar-toggler collapse-icons">
                      <i class="material-icons add"></i>
                      <i class="material-icons remove"></i>
                  </span>
                </span>
                      </div>
                      <select name="application" id="" class="r1-select select-title select-application">
                        <option value="Visi">Visi</option>
                        @foreach($applications as $application)
                          <option @if ($application == $currBrand) selected @endif value="{{$application}}">{{$application}}</option>
                        @endforeach
                      </select>

                    </section>
                    <section class="facet clearfix">
                      <h1 class="h6 facet-title hidden-sm-down">Garums</h1>
                      <div class="title hidden-md-up" data-target="#facet_auto-model" data-toggle="collapse" aria-expanded="true">
                        <h1 class="h6 facet-title">Garums</h1>
                        <span class="float-xs-right">
                  <span class="navbar-toggler collapse-icons">
                      <i class="material-icons add"></i>
                      <i class="material-icons remove"></i>
                  </span>
                </span>
                      </div>

                      <select name="stud_length" id="" class="r1-select select-title  select-studs-length">
                        <option value="Visi">Visi</option>
                        @foreach($stud_lengths as $stud_length_id => $stud_length)
                          <option @if ($curr_length == $stud_length) selected @endif value="{{$stud_length}}">{{$stud_length}}</option>
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
              <div class="wrap">
                <div class="sidebar-bottom">
                  <section class="facet clearfix facet--availability">
                    <h1 class="h6 facet-title hidden-sm-down">Atlase</h1>
                    <ul class="collapse">
                      <li class="show-selected-checkbox-li">
                        <label class="facet-label" for="show-selected-checkbox" style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                <span class="custom-checkbox">
                  <input type="checkbox" value="only_selected" class="tire-table-checkbox" id="show-selected-checkbox" name="product_ids[]" title="Rādīt tikai atzīmētās preces" disabled>
                  <span class="ps-shown-by-js">
                    <i class="material-icons checkbox-checked"></i>
                  </span>
                </span>
                          <span>Atrādīt izvēlētos</span>
                        </label>
                      </li>
                    </ul>
                    <h1 class="h6 facet-title hidden-sm-down">Pieejamība</h1>
                    <ul id="facet_availability" class="collapse">
                      <li>
                        <label class="facet-label" for="facet_availability_0" style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                <span class="custom-checkbox">
                    <input id="facet_availability_0" type="checkbox" data-search-url="#" data-color="green" class="green">
                    <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                </span>
                          Pieejams
                          <span class="dot green" style="float:right;margin-top: 3px;"></span>
                        </label>
                      </li>
                      <li>
                        <label class="facet-label" for="facet_availability_1" style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                <span class="custom-checkbox">
                    <input id="facet_availability_1" type="checkbox" data-search-url="#" data-color="yellow" class="yellow">
                    <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                </span>
                          Pasutams
                          <span class="dot yellow" style="float:right;margin-top: 3px;"></span>
                        </label>
                      </li>
                      <li>
                        <label class="facet-label" for="facet_availability_2" style="width: 100%;text-align: left;cursor: pointer">
                <span class="custom-checkbox">
                    <input id="facet_availability_2" type="checkbox" data-search-url="#" data-color="red" class="red">
                    <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                </span>
                          Zvaniet!
                          <span class="dot red" style="float:right;margin-top: 3px;"></span>
                        </label>
                      </li>
                    </ul>
                    <button class="filter-button" type="submit">Filtrēt <i class="material-icons search"></i></button>
                  </section>
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
