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
                                <h1 class="h6 facet-title">Kods</h1>
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
                                <select name="currentSkr" id="" class="r1-select select-title select-rim-lugs">
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
                                  @foreach($diameters as $diameter)
                                    <option @if ($diameter == $currentDia) selected @endif value="{{$diameter}}">{{$diameter}}</option>
                                  @endforeach
                                </select>
                              </section>
                              <section class="facet clearfix">
                                <div class="r1-select-params" style="margin: -10px 0 -15px 0">
                                  <div style="width: 100%">
                                    <div class="form-group facet">
                                      <h1 class="h6 facet-title">Platums (J), No</h1>
                                      <select name="currentWid" class="r1-select select-title select-rim-width">
                                        @foreach ($widths as $width)
                                          <option @if ($width == $currentWid) selected @endif value="{{$width}}">{{$width}}</option>
                                        @endforeach
                                      </select>
                                    </div>
                                  </div>
                                  <div style="width: 100%">
                                    <div class="form-group facet">
                                      <h1 class="h6 facet-title">Līdz</h1>
                                      <select name="currentWid2" class="r1-select select-title select-rim-width2">
                                        @foreach ($widths as $width)
                                          <option @if ($width == $currentWid2) selected @endif value="{{$width}}">{{$width}}</option>
                                        @endforeach
                                      </select>
                                    </div>
                                  </div>
                                </div>
                              </section>
                              <section class="facet clearfix">
{{--                                <h1 class="h6 facet-title">Izbīdījums</h1>--}}
{{--                                <div class="title hidden-md-up" data-target="#facet_37134" data-toggle="collapse">--}}
{{--                                  <h1 class="h6 facet-title">Izbīdījums</h1>--}}
{{--                                  <span class="float-xs-right">--}}
{{--                                  <span class="navbar-toggler collapse-icons">--}}
{{--                                      <i class="material-icons add"></i>--}}
{{--                                      <i class="material-icons remove"></i>--}}
{{--                                  </span>--}}
{{--                                </span>--}}
{{--                                </div>--}}
{{--                                <select name="currentEt" id="" class="r1-select select-title select-rim-offset">--}}
{{--                                  <option value="Visi">Visi</option>--}}
{{--                                  @foreach($offsets as $offset)--}}
{{--                                    <option @if ($offset == $currentEt) selected @endif value="{{$offset}}">{{$offset}}</option>--}}
{{--                                  @endforeach--}}
{{--                                </select>--}}
                                <div class="r1-select-params" style="margin: -10px 0 -15px 0">
                                  <div style="width: 100%">
                                    <div class="form-group facet">
                                      <h1 class="h6 facet-title">Izbīdījums (ET), No</h1>
                                      <select name="currentEt" class="r1-select select-title select-rim-offset">
                                        <option value="Visi">Visi</option>
                                        @foreach ($offsets as $offset)
                                          <option @if ($offset == $currentEt) selected @endif value="{{$offset}}">{{$offset}}</option>
                                        @endforeach
                                      </select>
                                    </div>
                                  </div>
                                  <div style="width: 100%">
                                    <div class="form-group facet">
                                      <h1 class="h6 facet-title">Līdz</h1>
                                      <select name="currentEt2" class="r1-select select-title select-rim-offset2">
                                        <option value="Visi">Visi</option>
                                        @foreach ($offsets as $offset)
                                          <option @if ($offset == $currentEt2) selected @endif value="{{$offset}}">{{$offset}}</option>
                                        @endforeach
                                      </select>
                                    </div>
                                  </div>
                                </div>
                              </section>
                              <section class="facet clearfix">
                                <h1 class="h6 facet-title">Centrs</h1>
                                <div class="title hidden-md-up" data-target="#facet_37134" data-toggle="collapse">
                                  <h1 class="h6 facet-title">Centrs</h1>
                                  <span class="float-xs-right">
                                  <span class="navbar-toggler collapse-icons">
                                      <i class="material-icons add"></i>
                                      <i class="material-icons remove"></i>
                                  </span>
                                </span>
                                </div>
                                <select name="currentCenter" id="" class="r1-select select-title select-rim-center">
                                  <option value="Visi">Visi</option>
                                  @foreach($centers as $center)
                                    <option @if ($center == $currentCenter) selected @endif value="{{$center}}">{{$center}}</option>
                                  @endforeach
                                </select>
                              </section>
                            </div>
                            <section class="facet clearfix">
                              <button id="autofind_sub" class="filter-button" type="submit">Meklēt <i class="material-icons search"></i>
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
{{--                              <h1 class="h6 facet-title">Pieejamība</h1>--}}
{{--                              <ul id="facet_availability" class="collapse">--}}
{{--                                <li>--}}
{{--                                  <label class="facet-label" for="facet_availability_0" style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">--}}
{{--                                    <span class="custom-checkbox">--}}
{{--                                      <input id="facet_availability_0" class="green" --}}{{-- @if (in_array('green', $availability)) checked @endif --}}{{-- type="checkbox" name="availability[]"--}}
{{--                                             data-search-url="#" value="green"--}}
{{--                                             data-for="dot" data-value="green" data-color="green">--}}
{{--                                      <span class="ps-shown-by-js">--}}
{{--                                        <i class="material-icons checkbox-checked"></i>--}}
{{--                                      </span>--}}
{{--                                    </span>--}}
{{--                                    Pieejams--}}
{{--                                    <span class="dot green" style="float:right;margin-top: 3px;"></span>--}}
{{--                                  </label>--}}
{{--                                </li>--}}
{{--                                <li>--}}
{{--                                  <label class="facet-label" for="facet_availability_1" style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">--}}
{{--                                    <span class="custom-checkbox">--}}
{{--                                      <input id="facet_availability_1" class="yellow" --}}{{-- @if (in_array('yellow', $availability)) checked @endif --}}{{-- type="checkbox" name="availability[]"--}}
{{--                                             data-search-url="#" value="yellow"--}}
{{--                                             data-for="dot" data-value="yellow" data-color="yellow">--}}
{{--                                      <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>--}}
{{--                                    </span>--}}
{{--                                    Pasūtāms--}}
{{--                                    <span class="dot yellow" style="float:right;margin-top: 3px;"></span>--}}
{{--                                  </label>--}}
{{--                                </li>--}}
{{--                                <li>--}}
{{--                                  <label class="facet-label" for="facet_availability_2" style="width: 100%;text-align: left;cursor: pointer">--}}
{{--                                    <span class="custom-checkbox">--}}
{{--                                      <input id="facet_availability_2" class="red" --}}{{-- @if (in_array('red', $availability)) checked @endif --}}{{-- type="checkbox" name="availability[]"--}}
{{--                                     data-search-url="#" value="red"--}}
{{--                                     data-for="dot" data-value="red" data-color="red">--}}
{{--                                      <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>--}}
{{--                                    </span>--}}
{{--                                    Zvaniet!--}}
{{--                                    <span class="dot red" style="float:right;margin-top: 3px;"></span>--}}
{{--                                  </label>--}}
{{--                                </li>--}}
{{--                              </ul>--}}
                            </section>
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
                    <section id="products" class="">
                      {{-- LIST VIEW --}}
                        <div id="js-product-list">
                            <div class="products row hide-price title-flip">
                              @if(isset($rims) && count($rims))
                                <span class="text-uppercase flipped-title tire-brand-name" style="color:black;">Lietie diski</span>
                                @include('rims.auto.list-table', ['rims' => $rims])
                              @endif
                            </div>
                            <a href="https://www.wheelstock.eu/?filtered=1&attributes=349" target="_blank"><div class="alert alert-secondary" style="color: #383d41; background-color: #e2e3e5; border-color: #d6d8db;"><img src="https://images.iconfigurators.app/images/wheels/large/iconalloys-nuevo-wheel-5lug-satin-black-17x8-5-500_8683.png" style="width: 30px;"> Iet uz noliktavu</div></a>
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
            @include('components.autorimsfilter')
          </div>
        </div>
      </div>
    </div>

<script src="{{ asset('js/rimAjax.js?rev=' . time()) }}"></script>
@endsection
