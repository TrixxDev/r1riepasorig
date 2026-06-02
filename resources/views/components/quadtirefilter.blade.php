<div id="search_filters_wrapper" class="hidden-sm-down">
  <div id="search_filter_controls" class="hidden-md-up"></div>
  <div id="search_filters" class="params">
    <input type="hidden" id="facet_all_val" value="Visi">
    <form method="get" action="{{ route('kvadraciklu-riepas-meklet') }}">
    <div class="wrap">

      <h4 class="text-uppercase h6 hidden-sm-down">
        <span id="search_filters_auto" class="params auto">Auto</span><span
          id="search_filters_params" class="params active">Parametri</span>
      </h4>

      <div class="can-collapse">

        <span class="show_list active" data-dismiss="modal"><i class="material-icons "></i>Saraksts</span>
        <span class="show_grid" data-dismiss="modal"><i class="material-icons "></i>Bilde</span>

        <template id="facet-template">
          <section class="facet clearfix">
            <h1 class="h6 facet-title hidden-sm-down">Kods</h1>
            <input type="text" value="" id="autofind_atr">
            <button id="autofind_sub">Meklēt <i class="material-icons search"></i>
            </button>
          </section>
        </template>
        <div class="sidebar-top">


            {{--            <section class="facet clearfix facet--0 facet-ind-0">--}}
            {{--              <h1 class="h6 facet-title hidden-sm-down">Ražotājs</h1>--}}
            {{--              <div class="title hidden-md-up" data-target="#facet_20294"--}}
            {{--                   data-toggle="collapse">--}}
            {{--                <h1 class="h6 facet-title">Ražotājs</h1>--}}
            {{--                <span class="float-xs-right">--}}
            {{--                                                <span class="navbar-toggler collapse-icons">--}}
            {{--                                                    <i class="material-icons add"></i>--}}
            {{--                                                    <i class="material-icons remove"></i>--}}
            {{--                                                </span>--}}
            {{--                                            </span>--}}
            {{--              </div>--}}
            {{--              <ul id="facet_20294" class="collapse">--}}
            {{--                <li>--}}
            {{--                  <div class="col-sm-12 col-xs-12 col-md-12 facet-dropdown dropdown size-dropdown">--}}

            {{--                    <select name="brand" class="select-title tire-brand">--}}
            {{--                      <option class="select-list" id="Visi">Visi</option>--}}
            {{--                      @foreach ($brands as $brand_id => $brand_title)--}}
            {{--                        <option class="select-list" id="{{ $brand_title }}" @if ($brand_title == $currBrand) selected @endif>{{ ucwords(strtolower($brand_title)) }}</option>--}}
            {{--                      @endforeach--}}
            {{--                    </select>--}}

            {{--                    --}}{{--                              <input type="text" readonly class="select-title tire-brand" name="brand"--}}
            {{--                    --}}{{--                                     value="{{ $currBrand }}">--}}
            {{--                    --}}{{--                              <i class="material-icons float-xs-right"></i>--}}
            {{--                    --}}{{--                              <div class="dropdown-menu">--}}
            {{--                    --}}{{--                                <a rel="nofollow" id="Visi" class="select-list">--}}
            {{--                    --}}{{--                                  Visi--}}
            {{--                    --}}{{--                                </a>--}}
            {{--                    --}}{{--                                --}}{{----}}{{--@foreach ($brands as $brand)--}}
            {{--                    --}}{{--                                  <a rel="nofollow" class="select-list" id="{{ $brand->title }}">--}}
            {{--                    --}}{{--                                    {{ $brand->title }}--}}
            {{--                    --}}{{--                                  </a>--}}
            {{--                    --}}{{--                                @endforeach--}}
            {{--                    --}}{{--                                @foreach ($brands as $brand_id => $brand_title)--}}
            {{--                    --}}{{--                                  <a rel="nofollow" class="select-list" id="{{ $brand_title }}">--}}
            {{--                    --}}{{--                                    {{ $brand_title }}--}}
            {{--                    --}}{{--                                  </a>--}}
            {{--                    --}}{{--                                @endforeach--}}

            {{--                    --}}{{--                              </div>--}}
            {{--                  </div>--}}
            {{--                </li>--}}
            {{--              </ul>--}}


            {{--            </section>--}}


            <div style="width: 100%">
              <div class="form-group facet mb-0">
                <h1 class="h6 facet-title">Ražotājs</h1>
                <select name="brand" class="r1-select select-title tire-brand">
                  <option class="select-list" id="Visi">Visi</option>
                  @foreach ($brands as $brand_id => $brand_title)
                    <option class="select-list" id="{{ $brand_title }}"
                            @if ($brand_title == $currBrand) selected @endif>{{ ucwords(strtolower($brand_title)) }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="r1-select-params">
              <div style="width: 100%">
                <div class="form-group facet">
                  <h1 class="h6 facet-title">Platums</h1>
                  <select name="d1" class="r1-select select-title tire-width">
                    <option class="select-list" id="Visi">Visi</option>
                    @foreach ($quadrTiresD1 as $tire)
                      <option class="select-list" id="{{ $tire->d1 }}"
                              @if ($tire->d1 == $d1) selected @endif>{{ $tire->d1 }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div style="width: 100%">
                <div class="form-group facet">
                  <h1 class="h6 facet-title">Augstums</h1>
                  <select name="d2" class="r1-select select-title tire-height">
                    <option class="select-list" id="Visi">Visi</option>
                    @foreach ($quadrTiresD2 as $tire)
                      <option class="select-list" id="{{ $tire->d2 }}"
                              @if ($tire->d2 == $d2) selected @endif>{{ $tire->d2 }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div style="width: 100%">
                <div class="form-group facet">
                  <h1 class="h6 facet-title facet-select">Diametrs</h1>
                  <select name="d3" class="r1-select select-title tire-radius">
                    <option class="select-list" id="Visi">Visi</option>
                    @foreach ($quadrTiresD3 as $tire)
                      <option class="select-list" id="{{ $tire->d3 }}"
                              @if ($tire->d3 == $d3) selected @endif>{{ $tire->d3 }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>

            {{--            <section class="facet clearfix facet--1 facet-ind-1">--}}
            {{--              <h1 class="h6 facet-title hidden-sm-down">Platums</h1>--}}
            {{--              <div class="title hidden-md-up" data-target="#facet_78843"--}}
            {{--                   data-toggle="collapse" aria-expanded="true">--}}
            {{--                <h1 class="h6 facet-title">Platums</h1>--}}
            {{--                <span class="float-xs-right">--}}
            {{--                                                <span class="navbar-toggler collapse-icons">--}}
            {{--                                                    <i class="material-icons add"></i>--}}
            {{--                                                    <i class="material-icons remove"></i>--}}
            {{--                                                </span>--}}
            {{--                                            </span>--}}
            {{--              </div>--}}


            {{--              <ul id="facet_78843" class="collapse in">--}}
            {{--                <li>--}}
            {{--                  <div class="col-sm-12 col-xs-12 col-md-12 facet-dropdown dropdown size-dropdown">--}}

            {{--                    <select name="d1" class="select-title tire-width">--}}
            {{--                      <option class="select-list" id="Visi">Visi</option>--}}
            {{--                      @foreach ($quadrTiresD1 as $tire)--}}
            {{--                        <option class="select-list" id="{{ $tire->d1 }}" @if ($tire->d1 == $d1) selected @endif>{{ $tire->d1 }}</option>--}}
            {{--                      @endforeach--}}
            {{--                    </select>--}}

            {{--                    --}}{{--                              <input type="text" readonly class="select-title tire-width" name="d1" value="{{ $d1 }}">--}}
            {{--                    --}}{{--                              <i class="material-icons float-xs-right"></i>--}}
            {{--                    --}}{{--                              <div class="dropdown-menu width">--}}

            {{--                    --}}{{--                                <a rel="nofollow" id="Visi" class="select-list">--}}
            {{--                    --}}{{--                                  Visi--}}
            {{--                    --}}{{--                                </a>--}}
            {{--                    --}}{{--                                @foreach ($quadrTiresD1 as $tire)--}}
            {{--                    --}}{{--                                  <a rel="nofollow" class="select-list" id="{{ $tire->d1 }}">--}}
            {{--                    --}}{{--                                    {{ $tire->d1 }}--}}
            {{--                    --}}{{--                                  </a>--}}
            {{--                    --}}{{--                                @endforeach--}}
            {{--                    --}}{{--                              </div>--}}
            {{--                  </div>--}}
            {{--                </li>--}}
            {{--              </ul>--}}


            {{--            </section>--}}


            {{--            <section class="facet clearfix facet--2 facet-ind-2">--}}
            {{--              <h1 class="h6 facet-title hidden-sm-down">Augstums</h1>--}}
            {{--              <div class="title hidden-md-up" data-target="#facet_15402"--}}
            {{--                   data-toggle="collapse" aria-expanded="true">--}}
            {{--                <h1 class="h6 facet-title">Augstums</h1>--}}
            {{--                <span class="float-xs-right">--}}
            {{--                                                <span class="navbar-toggler collapse-icons">--}}
            {{--                                                    <i class="material-icons add"></i>--}}
            {{--                                                    <i class="material-icons remove"></i>--}}
            {{--                                                </span>--}}
            {{--                                            </span>--}}
            {{--              </div>--}}


            {{--              <ul id="facet_15402" class="collapse in">--}}
            {{--                <li>--}}
            {{--                  <div class="col-sm-12 col-xs-12 col-md-12 facet-dropdown dropdown size-dropdown">--}}

            {{--                    <select name="d2" class="select-title tire-height">--}}
            {{--                      <option class="select-list" id="Visi">Visi</option>--}}
            {{--                      @foreach ($quadrTiresD2 as $tire)--}}
            {{--                        <option class="select-list" id="{{ $tire->d2 }}" @if ($tire->d2 == $d2) selected @endif>{{ $tire->d2 }}</option>--}}
            {{--                      @endforeach--}}
            {{--                    </select>--}}

            {{--                    --}}{{--                              <input type="text" class="select-title tire-height" readonly name="d2" value="{{ $d2 }}">--}}
            {{--                    --}}{{--                              <i class="material-icons float-xs-right"></i>--}}
            {{--                    --}}{{--                              <div class="dropdown-menu height">--}}

            {{--                    --}}{{--                                <a rel="nofollow" id="Visi" class="select-list">--}}
            {{--                    --}}{{--                                  Visi--}}
            {{--                    --}}{{--                                </a>--}}
            {{--                    --}}{{--                                @foreach ($quadrTiresD2 as $tire)--}}
            {{--                    --}}{{--                                  <a rel="nofollow" class="select-list" id="{{ $tire->d2 }}">--}}
            {{--                    --}}{{--                                    {{ $tire->d2 }}--}}
            {{--                    --}}{{--                                  </a>--}}
            {{--                    --}}{{--                                @endforeach--}}
            {{--                    --}}{{--                              </div>--}}
            {{--                  </div>--}}
            {{--                </li>--}}
            {{--              </ul>--}}


            {{--            </section>--}}


            {{--            <section class="facet clearfix facet--3 facet-ind-3">--}}
            {{--              <h1 class="h6 facet-title hidden-sm-down">Diametrs</h1>--}}
            {{--              <div class="title hidden-md-up" data-target="#facet_24954"--}}
            {{--                   data-toggle="collapse" aria-expanded="true">--}}
            {{--                <h1 class="h6 facet-title">Diametrs</h1>--}}
            {{--                <span class="float-xs-right">--}}
            {{--                                                <span class="navbar-toggler collapse-icons">--}}
            {{--                                                    <i class="material-icons add"></i>--}}
            {{--                                                    <i class="material-icons remove"></i>--}}
            {{--                                                </span>--}}
            {{--                                            </span>--}}
            {{--              </div>--}}


            {{--              <ul id="facet_24954" class="collapse in">--}}
            {{--                <li>--}}
            {{--                  <div class="col-sm-12 col-xs-12 col-md-12 facet-dropdown dropdown size-dropdown">--}}

            {{--                    <select name="d3" class="select-title tire-radius">--}}
            {{--                      <option class="select-list" id="Visi">Visi</option>--}}
            {{--                      @foreach ($quadrTiresD3 as $tire)--}}
            {{--                        <option class="select-list" id="{{ $tire->d3 }}" @if ($tire->d3 == $d3) selected @endif>{{ $tire->d3 }}</option>--}}
            {{--                      @endforeach--}}
            {{--                    </select>--}}

            {{--                    --}}{{--                              <input type="text" class="select-title tire-radius" readonly name="d3" value="{{ $d3 }}">--}}
            {{--                    --}}{{--                              <i class="material-icons float-xs-right"></i>--}}
            {{--                    --}}{{--                              <div class="dropdown-menu radius">--}}

            {{--                    --}}{{--                                @foreach ($quadrTiresD3 as $tire)--}}
            {{--                    --}}{{--                                  <a rel="nofollow" class="select-list" id="{{ $tire->d3 }}">--}}
            {{--                    --}}{{--                                    {{ $tire->d3 }}--}}
            {{--                    --}}{{--                                  </a>--}}
            {{--                    --}}{{--                                @endforeach--}}
            {{--                    --}}{{--                              </div>--}}
            {{--                  </div>--}}
            {{--                </li>--}}
            {{--              </ul>--}}

            {{--            </section>--}}


            <section class="facet clearfix">
              <input style="display: none;" type="text" value="" id="autofind_atr">
              <button id="autofind_sub" type="submit">Meklēt <i class="material-icons search"></i>
              </button>
            </section>
          </div>
      </div>
    </div>
    <div class="wrap">
      <div class="sidebar-bottom">

        <section class="facet clearfix facet--availability">
          <h1 class="h6 facet-title hidden-sm-down">Atlase</h1>
          <ul class="collapse">
            <li class="show-selected-checkbox-li">
              <label class="facet-label" for="show-selected-checkbox"
                     style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                          <span class="custom-checkbox">
                            <input type="checkbox" value="only_selected" class="tire-table-checkbox"
                                   id="show-selected-checkbox" name="product_ids[]" title="Rādīt tikai atzīmētās preces"
                                   disabled>
                            <span class="ps-shown-by-js">
                              <i class="material-icons checkbox-checked"></i>
                            </span>
                          </span>
                <span>Rādīt izvēlētos</span>
              </label>
            </li>
          </ul>
          <h1 class="h6 facet-title hidden-sm-down">Pieejamība</h1>
          <ul id="facet_availability" class="collapse">
            <li>
              <label class="facet-label" for="facet_availability_0"
                     style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                                      <span class="custom-checkbox">
                                        <input id="facet_availability_0" class="green" @if (in_array('green', $availability)) checked @endif type="checkbox"
                                               data-search-url="#" name="availability[]" value="green" data-for="dot"
                                               data-value="green" data-color="green">
                                        <span class="ps-shown-by-js">
                                          <i class="material-icons checkbox-checked"></i>
                                        </span>
                                      </span>
                Pieejams
                <span class="dot green" style="float:right;margin-top: 3px;"></span>
              </label>
            </li>
            <li>
              <label class="facet-label" for="facet_availability_1"
                     style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                                      <span class="custom-checkbox">
                                        <input id="facet_availability_1" class="yellow" @if (in_array('yellow', $availability)) checked @endif type="checkbox"
                                               data-search-url="#" name="availability[]" value="yellow" data-for="dot"
                                               data-value="yellow" data-color="yellow">
                                        <span class="ps-shown-by-js"><i
                                            class="material-icons checkbox-checked"></i></span>
                                      </span>
                Pasūtāms
                <span class="dot yellow" style="float:right;margin-top: 3px;"></span>
              </label>
            </li>
            <li>
              <label class="facet-label" for="facet_availability_2"
                     style="width: 100%;text-align: left;cursor: pointer">
                                      <span class="custom-checkbox">
                                        <input id="facet_availability_2" class="red" @if (in_array('red', $availability)) checked @endif type="checkbox" data-search-url="#"
                                               name="availability[]" value="red" data-for="dot" data-value="red"
                                               data-color="red">
                                        <span class="ps-shown-by-js"><i
                                            class="material-icons checkbox-checked"></i></span>
                                      </span>
                Zvaniet!
                <span class="dot red" style="float:right;margin-top: 3px;"></span>
              </label>
            </li>
          </ul>
        </section>
        <button class="filter-button" type="submit">Filtrēt <i class="material-icons search"></i></button>

      </div>
    </div>
    </form>
  </div>
</div>
