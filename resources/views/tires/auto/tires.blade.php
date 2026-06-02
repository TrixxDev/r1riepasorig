@extends('layouts.app')

@section('body-title', 'category')
@section('title', 'lang-' . app()->getLocale() . ' country-' . app()->getLocale() . ' layout-both-columns page-category tax-display-enabled category-id-14 category-' . $season_title . ' category-id-parent-12 category-depth-level-3 dual-tire-catalog')
@php
  $categoryTitle = ucwords(str_replace('-', ' ', $season_title));
  $catalogSearchPath = $season_title;
@endphp
@section('meta_title', $categoryTitle . ' | R1 Riepu Serviss')
@section('meta_description', 'Izvēlies ' . $categoryTitle . ' no R1 Riepu Serviss katalogiem. Filtri pēc izmēra un ražotāja.')

@section('content')
  <div class="container">
    <div class="row">
      <div class="main-content clearfix col-md-12 col-xl-12">
        <div id="left-column" class="col-md-12 col-lg-3">
          <!-- begin D:\OpenServer\domains\r1old/themes/classic/modules/ps_facetedsearch/ps_facetedsearch.tpl -->
          <div id="search_filters_wrapper">
            <form method="get" action="/{{ $catalogSearchPath }}/search">
              <div id="search_filters" class="params">
                <input type="hidden" id="facet_all_val" value="Visi">
                <div class="season-select">
                  <button type="button" class="summer-tires-link season-select-link @if ($season_title === 'vasaras-riepas'){{'selected-link'}}@endif">Vasara</button>
                  <button type="button" class="winter-tires-link season-select-link @if ($season_title === 'ziemas-riepas'){{'selected-link'}}@endif">Ziema</button>
                </div>
                <div class="wrap" style="border-top-left-radius: 0px;border-top-right-radius: 0px;border-top: none;">

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

                      @if (!empty($dualSizeMode))
                      @php
                        $dualSizeFilterEnabled = request()->boolean('dual')
                          || (request()->filled('d1b') && request()->d1b !== 'Visi');
                        $rearD1 = (!empty($d1b) && $d1b !== 'Visi') ? $d1b : null;
                        $rearD2 = (!empty($d2b) && $d2b !== 'Visi') ? $d2b : null;
                        $rearD3 = (!empty($d3b) && $d3b !== 'Visi') ? $d3b : null;
                      @endphp
                      <div class="r1-select-params tire-size-filter">
                        <div style="width: 100%">
                          <div class="form-group facet">
                            <h1 class="h6 facet-title" style="margin-bottom: 0px;">Platums</h1>
                            <select class="r1-select select-title tire-width" name="d1">
                              <option class="select-list" id="Visi">Visi</option>
                              @foreach ($autoTiresD1 as $tire)
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
                              @foreach ($autoTiresD2 as $tire)
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
                              @foreach ($autoTiresD3 as $tire)
                                <option class="select-list" id="{{ $tire->d3 }}" @if ($tire->d3 == $d3) selected @endif>{{ $tire->d3 }}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="dual-size-panel">
                      <div class="dual-size-toggle-row facet">
                        <label class="facet-label dual-size-toggle-label" for="dual-size-toggle">
                          <span class="custom-checkbox">
                            <input type="checkbox" id="dual-size-toggle" class="dual-size-toggle" value="1" @if ($dualSizeFilterEnabled) checked @endif>
                            <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                          </span>
                          <span>Otrs izmērs (aizmugurējā ass)</span>
                        </label>
                      </div>

                      <div class="dual-size-second-block dual-size-filter-wrapper" @if (!$dualSizeFilterEnabled) style="display: none;" @endif>
                        <p class="h6 facet-title dual-size-label">2. izmērs — aizmugurējā ass</p>
                        <div class="r1-select-params dual-size-second-row">
                          <div style="width: 100%">
                            <div class="form-group facet">
                              <h1 class="h6 facet-title" style="margin-bottom: 0px;">Platums</h1>
                              <select class="r1-select select-title tire-width-b" name="d1b">
                                <option class="select-list" id="Visi" @if ($rearD1 === null) selected @endif>Visi</option>
                                @foreach ($autoTiresD1 as $tire)
                                  <option id="{{ $tire->d1 }}" @if ($rearD1 !== null && $tire->d1 == $rearD1) selected @endif>{{ $tire->d1 }}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div style="width: 100%">
                            <div class="form-group facet">
                              <h1 class="h6 facet-title" style="margin-bottom: 0px;">Augstums</h1>
                              <select name="d2b" class="r1-select select-title tire-height-b">
                                <option class="select-list" id="Visi" @if ($rearD2 === null) selected @endif>Visi</option>
                                @foreach ($autoTiresD2 as $tire)
                                  <option class="select-list" id="{{ $tire->d2 }}" @if ($rearD2 !== null && $tire->d2 == $rearD2) selected @endif>{{ $tire->d2 }}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div style="width: 100%">
                            <div class="form-group facet">
                              <h1 class="h6 facet-title facet-select" style="margin-bottom: 0px;">Diametrs</h1>
                              <select name="d3b" class="r1-select select-title tire-radius-b">
                                <option class="select-list" id="Visi" @if ($rearD3 === null) selected @endif>Visi</option>
                                @foreach ($autoTiresD3 as $tire)
                                  <option class="select-list" id="{{ $tire->d3 }}" @if ($rearD3 !== null && $tire->d3 == $rearD3) selected @endif>{{ $tire->d3 }}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                        </div>
                      </div>
                      </div>
                      @else
                      <div class="r1-select-params">
                        <div style="width: 100%">
                          <div class="form-group facet">
                            <h1 class="h6 facet-title" style="margin-bottom: 0px;">Platums</h1>
                            <select class="r1-select select-title tire-width" name="d1">
                              <option class="select-list" id="Visi">Visi</option>
                              @foreach ($autoTiresD1 as $tire)
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
                              @foreach ($autoTiresD2 as $tire)
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
                              @foreach ($autoTiresD3 as $tire)
                                <option class="select-list" id="{{ $tire->d3 }}" @if ($tire->d3 == $d3) selected @endif>{{ $tire->d3 }}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>
                      @endif

                      <div class="tire-fastsearch-wrap @if (!empty($dualSizeMode)) dual-size-fastsearch-wrap @endif" style="width: 100%; margin-top: -15px;@if (!empty($dualSizeMode) && !empty($dualSizeFilterEnabled)) display: none;@endif">
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
                      <section class="facet clearfix facet--availability" style="padding-top: 0">
                        <ul class="collapse">
                            <li class="show-top-checkbox-li">
                                <label class="facet-label" for="show-top-checkbox"
                                       style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                              <span class="custom-checkbox">
                                <input type="checkbox" value="top" class="tire-top-checkbox" id="show-top-checkbox" @if (request()->top) checked @endif title="" disabled>
                              <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                            </span>
                                    <span>TOP 40</span>
                                </label>
                            </li>
                          <li class="show-selected-checkbox-li">
                            <label class="facet-label" for="show-selected-checkbox"
                                   style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                              <span class="custom-checkbox">
                                <input type="checkbox" value="only_selected" class="show-selected-filter tire-table-checkbox" id="show-selected-checkbox" title="Rādīt tikai atzīmētās preces" @if (request()->show_selected) checked @endif disabled>
                              <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                            </span>
                              <span>Rādīt izvēlētos</span>
                            </label>
                          </li>
                        </ul>
                        <h1 class="h6 facet-title"><b>Pieejamība</b></h1>
                        <ul id="facet_availability" class="collapse">
                          <li>
                            <label class="facet-label" for="facet_availability_0"
                                   style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                            <span class="custom-checkbox">
                              <input id="facet_availability_0" class="green" @if (in_array('green', explode(' ', request()->availability))) checked @endif type="checkbox"
                                     data-search-url="#" value="green"
                                     data-for="dot" data-value="green" data-color="green">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'>Riepas pieejamas mūsu noliktavās</span></div>" tabindex="0">
                                    Pieejams
                                </span>
                                <span class="tippy lisi-tooltip" style="float: right;" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'>Riepas pieejamas mūsu noliktavās</span></div>" tabindex="0">
                                    <span class="dot green" style="float:right;margin-top: 3px;"></span>
                                </span>
                            </label>
                          </li>
                          <li>
                            <label class="facet-label" for="facet_availability_1"
                                   style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                                <span class="custom-checkbox">
                                  <input id="facet_availability_1" class="yellow" @if (in_array('yellow', explode(' ', request()->availability))) checked @endif type="checkbox"
                                         data-search-url="#" value="yellow"
                                         data-for="dot" data-value="yellow" data-color="yellow">
                                  <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                                </span>
                                <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'>Riepas pieejamas ražotāju noliktavās<br>Piegāde 1-5 darbadienām.</span></div>" tabindex="0">
                                    Pasūtāms
                                </span>
                                <span class="tippy lisi-tooltip" style="float: right;" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'>Riepas pieejamas ražotāju noliktavās<br>Piegāde 1-5 darbadienām.</span></div>" tabindex="0">
                                    <span class="dot yellow" style="float:right;margin-top: 3px;"></span>
                                </span>
                            </label>
                          </li>
                          <li>
                            <label class="facet-label" for="facet_availability_2"
                                   style="width: 100%;text-align: left;cursor: pointer">
                                <span class="custom-checkbox">
                                  <input id="facet_availability_2" class="red" @if (in_array('red', explode(' ', request()->availability))) checked @endif type="checkbox"
                                         data-search-url="#" value="red"
                                         data-for="dot" data-value="red" data-color="red">
                                  <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                                </span>
                                <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'>Nepieciešams pārbaudīt pieejamību.</span></div>" tabindex="0">
                                  Zvaniet!
                                </span>
                                <span class="tippy lisi-tooltip" style="float: right;" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'>Nepieciešams pārbaudīt pieejamību.</span></div>" tabindex="0">
                                  <span class="dot red" style="float:right;margin-top: 3px;"></span>
                                </span>
                            </label>
                          </li>
                        </ul>
                      </section>

                      <section class="facet clearfix facet--4">
                        <h1 class="h6 facet-title facet-hover code-dropdown-btn"><b>Kods</b></h1>
                        <div class="title hidden-md-up" data-target="#facet_11641" data-toggle="collapse">
                          <h1 class="h6 facet-title">Kods</h1>
                          <span class="float-xs-right">
                          <span class="navbar-toggler collapse-icons">
                            <i class="material-icons add"></i>
                            <i class="material-icons remove"></i>
                          </span>
                        </span>
                        </div>

                        @php
                          $code = explode(' ', request()->code);
                        @endphp

                        <ul id="facet_code">
                            <div class="row">
                                <div class="col-md-6">
                                <li data-label="RSC">
                                    <label class="facet-label" for="facet_for_rsc">
                                <span class="custom-checkbox">
                                  <input id="facet_for_rsc" data-search-url=""
                                         @if (in_array('RSC', $code)) checked="" @endif value="RSC"
                                         data-for="prod-code" data-value="RSC" type="checkbox">
                                  <span class="ps-shown-by-js">
                                    <i class="material-icons checkbox-checked"></i>
                                  </span>
                                </span>
                                        <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>Run Flat (RSC)</b> - Šāda riepa ļauj pārvietoties arī tad, ja tā tikusi pārdurta</span></div>" tabindex="0">
                                            <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">RSC</a>
                                        </span>
                                    </label>
                                </li>
                                  <li data-label="SEAL">
                                    <label class="facet-label" for="facet_for_seal">
                                <span class="custom-checkbox">
                                  <input id="facet_for_seal" data-search-url=""
                                         @if (in_array('SEAL', $code)) checked="" @endif value="SEAL"
                                         data-for="prod-code" data-value="SEAL" type="checkbox">
                                  <span class="ps-shown-by-js">
                                    <i class="material-icons checkbox-checked"></i>
                                  </span>
                                </span>
                                      <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>Riepu blīvējums (SEAL)</b> ir gumijas slānis, kas tiek uzklāts uz riepas iekšpuses. Tas palīdz novērst gaisa noplūdi no riepas un aizsargā riepas karkasu no bojājumiem.</span></div>" tabindex="0">
                                            <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">SEAL</a>
                                        </span>
                                    </label>
                                  </li>
                                <li data-label="SOUND">
                                    <label class="facet-label" for="facet_for_sound">
                                <span class="custom-checkbox">
                                  <input id="facet_for_sound" data-search-url=""
                                         @if (in_array('SOUND', $code)) checked="" @endif value="SOUND"
                                         data-for="prod-code" data-value="SOUND" type="checkbox">
                                  <span class="ps-shown-by-js">
                                    <i class="material-icons checkbox-checked"></i>
                                  </span>
                                </span>
                                        <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>SOUND</b> - Riepu tehnoloģija, kas samazina troksni automobiļa salonā līdz pat 50%</span></div>" tabindex="0">
                                            <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">SOUND</a>
                                        </span>
                                    </label>
                                </li>
                              <li data-label="XL">
                                <label class="facet-label" for="facet_for_xl">
                                <span class="custom-checkbox">
                                  <input id="facet_for_xl" data-search-url=""
                                         @if (in_array('XL', $code)) checked="" @endif value="XL"
                                         data-for="prod-code" data-value="XL" type="checkbox">
                                  <span class="ps-shown-by-js">
                                    <i class="material-icons checkbox-checked"></i>
                                  </span>
                                </span>
                                    <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>EXTRA LOAD (XL)</b> - Riepa ar paaugstinātu kravnesību</span></div>" tabindex="0">
                                        <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">XL</a>
                                    </span>
                                </label>
                              </li>
                              </div>
                                <div class="col-md-6">
                                    <li data-label="ELECT">
                                        <label class="facet-label" for="facet_for_elect">
                            <span class="custom-checkbox">
                              <input id="facet_for_elect" data-search-url=""
                                     @if (in_array('ELECT', $code)) checked="" @endif value="ELECT"
                                     data-for="prod-code" data-value="ELECT" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                    <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>ELECT</b> - Riepas paredzētas elektroauto</span></div>" tabindex="0">
                                        <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">ELECT</a>
                                    </span>
                                        </label>
                                    </li>
                                  <li data-label="MFS">
                                    <label class="facet-label" for="facet_for_mfs">
                            <span class="custom-checkbox">
                              <input id="facet_for_mfs" data-search-url=""
                                     @if (in_array('MFS', $code)) checked="" @endif value="MFS"
                                     data-for="prod-code" data-value="MFS" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                      <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>Maximum Flange Shield (MFS)</b> - Riepa ar diska aizsargmalu</span></div>" tabindex="0">
                                    <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">MFS</a>
                                </span>
                                    </label>
                                  </li>
                            <li data-label="N0">
                                <label class="facet-label" for="facet_for_n0">
                            <span class="custom-checkbox">
                              <input id="facet_for_n0" data-search-url=""
                                     @if (in_array('N0', $code)) checked="" @endif value="N0"
                                     data-for="prod-code" data-value="N0" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                    <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>N0</b> - Porsche ražotāja homologācija</span></div>" tabindex="0">
                                        <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">N0</a>
                                    </span>
                                        </label>
                                    </li>
                                  <li data-label="bmw">
                                    <label class="facet-label" for="facet_for_bmw">
                            <span class="custom-checkbox">
                              <input id="facet_for_bmw" data-search-url=""
                                     @if (in_array('*', $code)) checked="" @endif value="*"
                                     data-for="prod-code" data-value="*" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                      <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>(*)</b> - BMW ražotāja homologācija</span></div>" tabindex="0">
                                    <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">(*)</a>
                                </span>
                                    </label>
                                  </li>
                                </div>
                            </div>

{{--                          <li data-label="CURRYEAR">--}}
{{--                            <label class="facet-label" for="facet_for_curryear">--}}
{{--                            <span class="custom-checkbox">--}}
{{--                              <input id="facet_for_curryear" data-search-url=""--}}
{{--                                     @if (in_array('CURRYEAR', $code)) checked="" @endif value="CURRYEAR"--}}
{{--                                     data-for="prod-code" data-value="CURRYEAR"--}}
{{--                                     type="checkbox">--}}
{{--                              <span class="ps-shown-by-js">--}}
{{--                                <i class="material-icons checkbox-checked"></i>--}}
{{--                              </span>--}}
{{--                            </span>--}}
{{--                              <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow"">Šī gada</a>--}}
{{--                            </label>--}}
{{--                          </li>--}}
                        </ul>
                      </section>

                      @if ($season_title == 'ziemas-riepas')

                        <section class="facet clearfix facet--4">
                          <h1 class="h6 facet-title facet-hover type-dropdown-btn"><b>Tips</b></h1>
                          <div class="title hidden-md-up" data-target="#facet_11641" data-toggle="collapse">
                            <h1 class="h6 facet-title">Tips</h1>
                            <span class="float-xs-right">
                          <span class="navbar-toggler collapse-icons">
                            <i class="material-icons add"></i>
                            <i class="material-icons remove"></i>
                          </span>
                        </span>
                          </div>

                          <ul id="facet_type">
                              <li data-label="Winter">
                                  <label class="facet-label" for="facet_for_winter">
                                    <span class="custom-checkbox">
                                      <input id="facet_for_winter" data-search-url=""
                                             @if (in_array(4, explode(' ', request()->type))) checked="" @endif value="4"
                                             data-for="prod-type" data-value="Winter" type="checkbox">
                                      <span class="ps-shown-by-js">
                                        <i class="material-icons checkbox-checked"></i>
                                      </span>
                                    </span>
                                    <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'>{{ \App\Helper\Tires::codeExplain('ziemas tips') }}</span></div>" tabindex="0">
                                        <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">Ziemas <img src="/images/parsla.png"></a>
                                    </span>
                                  </label>
                              </li>
                            <li data-label="M+S">
                              <label class="facet-label" for="facet_for_ms">
                            <span class="custom-checkbox">
                              <input id="facet_for_ms" data-search-url=""
                                     @if (in_array(1, explode(' ', request()->type))) checked="" @endif value="1"
                                     data-for="prod-type" data-value="M+S" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'>{{ \App\Helper\Tires::codeExplain('ms tips') }}</span></div>" tabindex="0">
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">M+S <img src="/images/ms.png"></a>
{{--                                      <div class="png-container">--}}
{{--                                        <img src="https://hyacktire.com/_images/_icons/M+S-icon-blue_283x283.png">--}}
{{--                                      </div>--}}
                                </span>
                              </label>
                            </li>
                              <li data-label="Studs">
                                  <label class="facet-label" for="facet_for_studs">
                                    <span class="custom-checkbox">
                                      <input id="facet_for_studs" data-search-url=""
                                             @if (in_array(3, explode(' ', request()->type))) checked="" @endif value="3"
                                             data-for="prod-type" data-value="Studs" type="checkbox">
                                      <span class="ps-shown-by-js">
                                        <i class="material-icons checkbox-checked"></i>
                                      </span>
                                    </span>
                                    <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'>{{ \App\Helper\Tires::codeExplain('ar radzēm tips') }}</span></div>" tabindex="0">
                                        <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">Ar radzēm <img src="/images/radzea.png"></a>
                                    </span>
                                  </label>
                              </li>
                            <li data-label="Studdable">
                              <label class="facet-label" for="facet_for_studdable">
                                <span class="custom-checkbox">
                                  <input id="facet_for_studdable" data-search-url=""
                                         @if (in_array(2, explode(' ', request()->type))) checked="" @endif value="2"
                                         data-for="prod-type" data-value="Studdable" type="checkbox">
                                  <span class="ps-shown-by-js">
                                    <i class="material-icons checkbox-checked"></i>
                                  </span>
                                </span>
                                <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'>{{ \App\Helper\Tires::codeExplain('radžojamu tips') }}</span></div>" tabindex="0">
                                    <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">Radžojama <img src="/images/radzeb.png" alt="ms"></a>
                                </span>
                              </label>
                            </li>
                          </ul>
                        </section>

                      @endif

                      <div class="row">
                        <div class="col-md-4">
                          <section class="facet clearfix facet--8">
                            <h1 class="h6 facet-title facet-hover fuel-eco-dropdown-btn">
                              <div class="icon-tire-fuel">
                                <img src="https://i.imgur.com/77wfTHY.png" style="width: 80px; position: relative; left: -15px; top: 3px;">
                              </div>
                            </h1>
                            <div class="title hidden-md-up" data-target="#facet_70638">
                              <h1 class="h6 facet-title">Degvielas ekonomija</h1>
                              <span class="float-xs-right">
                                <span class="navbar-toggler collapse-icons">
                                  <i class="material-icons add"></i>
                                  <i class="material-icons remove"></i>
                                </span>
                              </span>
                            </div>
                            <ul id="facet_fuel_eco" class="collapse">
                              <li data-label="A">
                                <label class="facet-label" for="facet_fuel_eco_a">
                            <span class="custom-checkbox">
                              <input id="facet_fuel_eco_a" data-search-url=""
                                     @if (in_array('A', explode(' ', request()->fuel))) checked="" @endif value="A"
                                     data-for="fuel_efficiency" data-value="A" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">A</a>
                                </label>
                              </li>
                              <li data-label="B">
                                <label class="facet-label" for="facet_fuel_eco_b">
                            <span class="custom-checkbox">
                              <input id="facet_fuel_eco_b" data-search-url=""
                                     @if (in_array('B', explode(' ', request()->fuel))) checked="" @endif value="B"
                                     data-for="fuel_efficiency" data-value="B" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">B</a>
                                </label>
                              </li>
                              <li data-label="C">
                                <label class="facet-label" for="facet_fuel_eco_c">
                            <span class="custom-checkbox">
                              <input id="facet_fuel_eco_c" data-search-url=""
                                     @if (in_array('C', explode(' ', request()->fuel))) checked="" @endif value="C"
                                     data-for="fuel_efficiency" data-value="C" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">C</a>
                                </label>
                              </li>
                              <li data-label="D">
                                <label class="facet-label" for="facet_fuel_eco_d">
                            <span class="custom-checkbox">
                              <input id="facet_fuel_eco_d" data-search-url=""
                                     @if (in_array('D', explode(' ', request()->fuel))) checked="" @endif value="D"
                                     data-for="fuel_efficiency" data-value="D" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">D</a>
                                </label>
                              </li>
                              <li data-label="E">
                                <label class="facet-label" for="facet_fuel_eco_e">
                            <span class="custom-checkbox">
                              <input id="facet_fuel_eco_e" data-search-url=""
                                     @if (in_array('E', explode(' ', request()->fuel))) checked="" @endif value="E"
                                     data-for="fuel_efficiency" data-value="E" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">E</a>
                                </label>
                              </li>


                              <li data-label="F">
                                <label class="facet-label" for="facet_fuel_eco_f">
                            <span class="custom-checkbox">
                              <input id="facet_fuel_eco_f" data-search-url=""
                                     @if (in_array('F', explode(' ', request()->fuel))) checked="" @endif value="F"
                                     data-for="fuel_efficiency" data-value="F" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">F</a>
                                </label>
                              </li>

                              <li data-label="G">
                                <label class="facet-label" for="facet_fuel_eco_g">
                            <span class="custom-checkbox">
                              <input id="facet_fuel_eco_g" data-search-url=""
                                     @if (in_array('G', explode(' ', request()->fuel))) checked="" @endif value="G"
                                     data-for="fuel_efficiency" data-value="G" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">G</a>
                                </label>
                              </li>
                            </ul>
                          </section>
                        </div>
                        <div class="col-md-4">
                          <section class="facet clearfix facet--9">
                            <h1 class="h6 facet-title facet-hover wet-surface-dropdown-btn">
                              <div class="icon-tire-rain">
                                <img style="width: 80px;position: relative;left: -15px;top: 3px;" src="https://i.imgur.com/TVeVuMf.png">
                              </div>
                            </h1>
                            <div class="title hidden-md-up" data-target="#facet_8079">
                              <h1 class="h6 facet-title">Slapjš segums</h1>
                              <span class="float-xs-right">
                                <span class="navbar-toggler collapse-icons">
                                  <i class="material-icons add"></i>
                                  <i class="material-icons remove"></i>
                                </span>
                              </span>
                            </div>
                            <ul id="facet_wet" class="collapse">
                              <li data-label="A">
                                <label class="facet-label" for="facet_wet_a">
                            <span class="custom-checkbox">
                              <input id="facet_wet_a" data-search-url=""
                                     @if (in_array('A', explode(' ', request()->wet))) checked="" @endif value="A"
                                     data-for="wet_grip" data-value="A" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>

                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">A</a>
                                </label>
                              </li>
                              <li data-label="B">
                                <label class="facet-label" for="facet_wet_b">
                            <span class="custom-checkbox">
                              <input id="facet_wet_b" data-search-url=""
                                     @if (in_array('B', explode(' ', request()->wet))) checked="" @endif value="B"
                                     data-for="wet_grip" data-value="B" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>

                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">B</a>
                                </label>
                              </li>
                              <li data-label="C">
                                <label class="facet-label" for="facet_wet_c">
                            <span class="custom-checkbox">
                              <input id="facet_wet_c" data-search-url=""
                                     @if (in_array('C', explode(' ', request()->wet))) checked="" @endif value="C"
                                     data-for="wet_grip" data-value="C" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>

                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">C</a>
                                </label>
                              </li>
                              <li data-label="D">
                                <label class="facet-label" for="facet_wet_d">
                            <span class="custom-checkbox">
                              <input id="facet_wet_d" data-search-url=""
                                     @if (in_array('D', explode(' ', request()->wet))) checked="" @endif value="D"
                                     data-for="wet_grip" data-value="D" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>

                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">D</a>
                                </label>
                              </li>
                              <li data-label="E">
                                <label class="facet-label" for="facet_wet_e">
                            <span class="custom-checkbox">
                              <input id="facet_wet_e" data-search-url=""
                                     @if (in_array('E', explode(' ', request()->wet))) checked="" @endif value="E"
                                     data-for="wet_grip" data-value="E" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>

                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">E</a>
                                </label>
                              </li>
                              <li data-label="F">
                                <label class="facet-label" for="facet_wet_f">
                            <span class="custom-checkbox">
                              <input id="facet_wet_f" data-search-url=""
                                     @if (in_array('F', explode(' ', request()->wet))) checked="" @endif value="F"
                                     data-for="wet_grip" data-value="F" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>

                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">F</a>
                                </label>
                              </li>
                              <li data-label="G">
                                <label class="facet-label" for="facet_wet_g">
                            <span class="custom-checkbox">
                              <input id="facet_wet_g" data-search-url=""
                                     @if (in_array('G', explode(' ', request()->wet))) checked="" @endif value="G"
                                     data-for="wet_grip" data-value="G" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>

                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">G</a>
                                </label>
                              </li>
                            </ul>
                          </section>
                        </div>
                        <div class="col-md-4">
                          <section class="facet clearfix facet--10">
                            <div class="icon-tire-sound" title="Troksnis">
                              <img style="width: 75px;position: relative;left: -5px;margin-bottom: 4px;" src="https://i.imgur.com/fjyPUVN.png">
                            </div>
                            <div class="title hidden-md-up" data-target="#facet_8079">
                              <h1 class="h6 facet-title">Trokšņa līmenis</h1>
                              <span class="float-xs-right">
                                <span class="navbar-toggler collapse-icons">
                                  <i class="material-icons add"></i>
                                  <i class="material-icons remove"></i>
                                </span>
                              </span>
                            </div>


                            <ul id="facet_noise" class="collapse">
                              <li data-label="A">
                                <label class="facet-label" for="facet_noise_a">
                                  <span class="custom-checkbox">
                                    <input id="facet_noise_a" data-search-url=""
                                           @if (in_array('A', explode(' ', request()->noise))) checked="" @endif value="A"
                                           data-for="noise" data-value="A" type="checkbox">
                                    <span class="ps-shown-by-js">
                                      <i class="material-icons checkbox-checked"></i>
                                    </span>
                                  </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">A</a>
                                </label>
                              </li>
                              <li data-label="B">
                                <label class="facet-label" for="facet_noise_b">
                                  <span class="custom-checkbox">
                                    <input id="facet_noise_b" data-search-url=""
                                           @if (in_array('B', explode(' ', request()->noise))) checked="" @endif value="B"
                                           data-for="noise" data-value="B" type="checkbox">
                                    <span class="ps-shown-by-js">
                                      <i class="material-icons checkbox-checked"></i>
                                    </span>
                                  </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">B</a>
                                </label>
                              </li>
                              <li data-label="C">
                                <label class="facet-label" for="facet_noise_c">
                                  <span class="custom-checkbox">
                                    <input id="facet_noise_c" data-search-url=""
                                           @if (in_array('C', explode(' ', request()->noise))) checked="" @endif value="C"
                                           data-for="noise" data-value="C" type="checkbox">
                                    <span class="ps-shown-by-js">
                                      <i class="material-icons checkbox-checked"></i>
                                    </span>
                                  </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">C</a>
                                </label>
                              </li>
                            </ul>
                          </section>
                        </div>
                      </div>
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
              {{--GRID VIEW--}}
{{--              <div class="tire-image-container" style="display: none">--}}
{{--                  --}}{{--                <div style="width: auto;">BRAND NAME</div>--}}
{{--                  @php--}}
{{--                    $cbrand = '';--}}
{{--                    $index = 0;--}}
{{--                  @endphp--}}
{{--                  @foreach($tires as $tire)--}}
{{--                    @php--}}
{{--                      $brand = $tire->fullSize;--}}
{{--                      $tire->includeStock = true;--}}
{{--                      if ($cbrand!=$brand){--}}
{{--                        echo '</div><h4 class="tire-brand-name grid-t">' . $brand;--}}
{{--                        if ($index == 0){--}}
{{--                          switch ($season_id){--}}
{{--                          case 1:--}}
{{--                            echo ' <span class="tire-type-title">Vasaras riepas</span>';--}}
{{--                            break;--}}
{{--                          case 2:--}}
{{--                            echo ' <span class="tire-type-title">Ziemas riepas</span>';--}}
{{--                            break;--}}
{{--                          }--}}
{{--                        }--}}
{{--                        echo '<span style="margin: 0 auto;"></span>';--}}
{{--                        echo '<button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal">--}}
{{--                                    Filtrs (' . $filterCount . ')--}}
{{--                                  </button></h4>--}}
{{--                        <div class="row grid-ex pr-1">';--}}
{{--                        $cbrand = $brand;--}}
{{--                        $stripe = 1;--}}
{{--                      } else {--}}
{{--                          $brand = str_replace(" ", "", $brand);--}}
{{--                      }--}}
{{--                    @endphp--}}
{{--                    @if($tire->price1)--}}
{{--                      <a--}}
{{--                          href="{{ route($current_url, [\Str::slug(\Tires::getAutoTireBrand($tire->brand_id)->title), strtolower(str_replace('/', '_', $tire->t_title)), $tire->tire_id]) }}"                        class="grid-view-link"--}}
{{--                        data-article="{{ $tire->article }}">--}}
{{--                        <div class="tire-image-card sort-order">--}}
{{--                          <div class="text-center image-grid-overflow">--}}
{{--                            {!! App\Helper\Image::showGrid('auto', $tire->make_id) !!}--}}
{{--                          </div>--}}

{{--                          <div class="tire-list-caption">--}}

{{--                            <div class="card-title-text" data-toggle="tooltip" title="<div>{{$tire->title}}</div>">--}}
{{--                              {{$tire->title}}--}}
{{--                            </div>--}}

{{--                            <div class="tire-tread">--}}
{{--                              <b>{{$tire->d1}} / {{$tire->d2}} / {{$tire->d3}} </b>--}}
{{--                              <span data-toggle="tooltip"--}}
{{--                                    title="<span style='color: black'>{{ $tire->lisiDesc($tire->li, $tire->si) }}</span>">{{ $tire->li . $tire->si }}</span>--}}
{{--                              <span class="tire-image-code">{{$tire->code}}</span>--}}
{{--                            </div>--}}
{{--                            <div style="display: flex;">--}}
{{--                              <input type="checkbox" name="product_ids[]" value="{{$tire->tire_id}}"--}}
{{--                                     style="margin-right: 5px;">--}}
{{--                              <div class="rim-price-old" style="align-self: center;">€{{$tire->price1}}</div>--}}
{{--                              <div class="rim-price-red" style="align-self: center;">€{{$tire->price2}}</div>--}}
{{--                              --}}{{--                            <i class="material-icons" style="margin-left: auto;">add_shopping_cart</i>--}}
{{--                              <span style="margin-left: auto;" data-toggle="tooltip"--}}
{{--                                    title="<span style='color: black'>Pievienot grozam</span>">--}}
{{--                              <button class="grid-buy-btn" data-toggle="modal"--}}
{{--                                --}}{{--                                      @hasrole('administrators') data-target=""--}}
{{--                                --}}{{--                                      @else data-target="#blockcart-modal"--}}
{{--                                --}}{{--                                      @endhasrole data-info="{{ $tire->tire_id }}" onclick="event.preventDefault()">--}}
{{--                                --}}{{--                                <i class="material-icons">add_shopping_cart</i>--}}
{{--                                --}}{{--                              </button>--}}

{{--                              <button class="grid-buy-btn cart-shopping-button"--}}
{{--                                      data-toggle="modal"--}}
{{--                                      data-info="{{ $tire->tire_id }}"--}}
{{--                                      --}}{{--                                      data-info="{{ $currTire->tire_id }}--}}
{{--                                      onclick="event.preventDefault()"--}}
{{--                                        data-target="#">--}}
{{--                                  <i class="material-icons">add_shopping_cart</i>--}}
{{--                                  </button>--}}
{{--                            </span>--}}

{{--                              --}}{{--                            <div class="clearfix atc_div text-right">--}}
{{--                              --}}{{--                              <button class="grid-buy-btn" data-toggle="modal"--}}
{{--                              --}}{{--                                      @hasrole('administrators') data-target=""--}}
{{--                              --}}{{--                                      @else data-target="#blockcart-modal"--}}
{{--                              --}}{{--                                      @endhasrole data-info="{{ $tire->tire_id }}" onclick="event.preventDefault()">--}}
{{--                              --}}{{--                              <i class="material-icons">add_shopping_cart</i>--}}
{{--                              --}}{{--                              </button>--}}
{{--                              --}}{{--                            </div>--}}


{{--                              <span class="grid-dot {{ $tire->dotAvailable }} {{ $tire->stockCount }}"--}}
{{--                                    data-toggle="tooltip"--}}
{{--                                    data-html="true"--}}
{{--                                    onclick="event.preventDefault()"--}}
{{--                                    title="{{ $tire->stockAvailability }}">--}}
{{--                              <span class="sort-order" style="display: none;">{{ $tire->dotAvailable }}</span>--}}
{{--                            </span>--}}
{{--                            </div>--}}
{{--                          </div>--}}
{{--                          --}}{{--                        <button class="grid-shopping-button grid-cart-btn" data-toggle="modal" data-target="#blockcart-modal" data-info="148204">Pirkt--}}
{{--                          --}}{{--                        </button>--}}


{{--                        </div>--}}
{{--                      </a>--}}
{{--                    @endif--}}
{{--                    @php--}}
{{--                      $index++;--}}
{{--                    @endphp--}}
{{--                  @endforeach--}}
{{--              </div>--}}
{{--              {{ $tires->links() }}--}}
              {{-- LIST VIEW--}}
              <div id="">
                <div id="js-product-list">
                  <div class="products row title-flip">
                  </div>
                </div>
              </div>
            </section>
          </section>
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
                <div id="search_filters" class="params">
                    <input type="hidden" id="facet_all_val" value="Visi">
                  <div class="wrap hidden-sm-down">
                    <div class="sidebar-bottom">
                      <section class="facet clearfix facet--availability" style="padding-top: 0">
                        <ul class="collapse">
                            <li class="show-top-checkbox-li">
                                <label class="facet-label" for="show-top-checkbox"
                                       style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                              <span class="custom-checkbox">
                                <input type="checkbox" value="top" class="tire-top-checkbox" id="show-top-checkbox" @if (request()->top) checked @endif title="">
                              <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                            </span>
                                    <span>TOP 40</span>
                                </label>
                            </li>
                          <li class="show-selected-checkbox-li">
                            <label class="facet-label" for="show-selected-checkbox-mobile"
                                   style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                              <span class="custom-checkbox">
                                <input type="checkbox" value="only_selected" class="show-selected-filter tire-table-checkbox" id="show-selected-checkbox-mobile" title="Rādīt tikai atzīmētās preces" @if (request()->show_selected) checked @endif disabled>
                              <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                            </span>
                              <span>Rādīt izvēlētos</span>
                            </label>
                          </li>
                        </ul>
                        <h1 class="h6 facet-title"><b>Pieejamība</b></h1>
                        <ul id="facet_availability" class="collapse">
                          <li>
                            <label class="facet-label" for="facet_availability_0"
                                   style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                            <span class="custom-checkbox">
                              <input id="facet_availability_0" class="green" @if (in_array('green', explode(' ', request()->availability))) checked @endif type="checkbox"
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
                            <label class="facet-label" for="facet_availability_1"
                                   style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                            <span class="custom-checkbox">
                              <input id="facet_availability_1" class="yellow" @if (in_array('yellow', explode(' ', request()->availability))) checked @endif type="checkbox"
                                     data-search-url="#" value="yellow"
                                     data-for="dot" data-value="yellow" data-color="yellow">
                              <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                            </span>
                              Pasūtāms
                              <span class="dot yellow" style="float:right;margin-top: 3px;"></span>
                            </label>
                          </li>
                          <li>
                            <label class="facet-label" for="facet_availability_2"
                                   style="width: 100%;text-align: left;cursor: pointer">
                            <span class="custom-checkbox">
                              <input id="facet_availability_2" class="red" @if (in_array('red', explode(' ', request()->availability))) checked @endif type="checkbox"
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

                      <section class="facet clearfix facet--4">
                        <h1 class="h6 facet-title facet-hover code-dropdown-btn"><b>Kods</b></h1>
                        <div class="title hidden-md-up" data-target="#facet_11641" data-toggle="collapse">
                          <h1 class="h6 facet-title">Kods</h1>
                          <span class="float-xs-right">
                          <span class="navbar-toggler collapse-icons">
                            <i class="material-icons add"></i>
                            <i class="material-icons remove"></i>
                          </span>
                        </span>
                        </div>

                        @php
                          $code = explode(' ', request()->code);
                        @endphp

                        <ul id="facet_code">
                          <div class="row flex flex-codes">
                            <div class="col-md-6">
                              <li data-label="RSC">
                                <label class="facet-label" for="facet_for_rsc">
                                <span class="custom-checkbox">
                                  <input id="facet_for_rsc" data-search-url=""
                                         @if (in_array('RSC', $code)) checked="" @endif value="RSC"
                                         data-for="prod-code" data-value="RSC" type="checkbox">
                                  <span class="ps-shown-by-js">
                                    <i class="material-icons checkbox-checked"></i>
                                  </span>
                                </span>
                                  <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>Run Flat (RSC)</b> - Šāda riepa ļauj pārvietoties arī tad, ja tā tikusi pārdurta</span></div>" tabindex="0">
                                            <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">RSC</a>
                                        </span>
                                </label>
                              </li>
                              <li data-label="SEAL">
                                <label class="facet-label" for="facet_for_seal">
                                <span class="custom-checkbox">
                                  <input id="facet_for_seal" data-search-url=""
                                         @if (in_array('SEAL', $code)) checked="" @endif value="SEAL"
                                         data-for="prod-code" data-value="SEAL" type="checkbox">
                                  <span class="ps-shown-by-js">
                                    <i class="material-icons checkbox-checked"></i>
                                  </span>
                                </span>
                                  <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>Riepu blīvējums (SEAL)</b> ir gumijas slānis, kas tiek uzklāts uz riepas iekšpuses. Tas palīdz novērst gaisa noplūdi no riepas un aizsargā riepas karkasu no bojājumiem.</span></div>" tabindex="0">
                                            <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">SEAL</a>
                                        </span>
                                </label>
                              </li>
                              <li data-label="SOUND">
                                <label class="facet-label" for="facet_for_sound">
                                <span class="custom-checkbox">
                                  <input id="facet_for_sound" data-search-url=""
                                         @if (in_array('SOUND', $code)) checked="" @endif value="SOUND"
                                         data-for="prod-code" data-value="SOUND" type="checkbox">
                                  <span class="ps-shown-by-js">
                                    <i class="material-icons checkbox-checked"></i>
                                  </span>
                                </span>
                                  <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>SOUND</b> - Riepu tehnoloģija, kas samazina troksni automobiļa salonā līdz pat 50%</span></div>" tabindex="0">
                                            <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">SOUND</a>
                                        </span>
                                </label>
                              </li>
                              <li data-label="XL">
                                <label class="facet-label" for="facet_for_xl">
                                <span class="custom-checkbox">
                                  <input id="facet_for_xl" data-search-url=""
                                         @if (in_array('XL', $code)) checked="" @endif value="XL"
                                         data-for="prod-code" data-value="XL" type="checkbox">
                                  <span class="ps-shown-by-js">
                                    <i class="material-icons checkbox-checked"></i>
                                  </span>
                                </span>
                                  <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>EXTRA LOAD (XL)</b> - Riepa ar paaugstinātu kravnesību</span></div>" tabindex="0">
                                        <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">XL</a>
                                    </span>
                                </label>
                              </li>
                            </div>
                            <div class="col-md-6">
                              <li data-label="ELECT">
                                <label class="facet-label" for="facet_for_elect">
                            <span class="custom-checkbox">
                              <input id="facet_for_elect" data-search-url=""
                                     @if (in_array('ELECT', $code)) checked="" @endif value="ELECT"
                                     data-for="prod-code" data-value="ELECT" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>ELECT</b> - Riepas paredzētas elektroauto</span></div>" tabindex="0">
                                        <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">ELECT</a>
                                    </span>
                                </label>
                              </li>
                              <li data-label="MFS">
                                <label class="facet-label" for="facet_for_mfs">
                            <span class="custom-checkbox">
                              <input id="facet_for_mfs" data-search-url=""
                                     @if (in_array('MFS', $code)) checked="" @endif value="MFS"
                                     data-for="prod-code" data-value="MFS" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>Maximum Flange Shield (MFS)</b> - Riepa ar diska aizsargmalu</span></div>" tabindex="0">
                                    <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">MFS</a>
                                </span>
                                </label>
                              </li>
                              <li data-label="N0">
                                <label class="facet-label" for="facet_for_n0">
                            <span class="custom-checkbox">
                              <input id="facet_for_n0" data-search-url=""
                                     @if (in_array('N0', $code)) checked="" @endif value="N0"
                                     data-for="prod-code" data-value="N0" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>N0</b> - Porsche ražotāja homologācija</span></div>" tabindex="0">
                                        <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">N0</a>
                                    </span>
                                </label>
                              </li>
                              <li data-label="bmw">
                                <label class="facet-label" for="facet_for_bmw">
                            <span class="custom-checkbox">
                              <input id="facet_for_bmw" data-search-url=""
                                     @if (in_array('*', $code)) checked="" @endif value="*"
                                     data-for="prod-code" data-value="*" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'><b>(*)</b> - BMW ražotāja homologācija</span></div>" tabindex="0">
                                    <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">(*)</a>
                                </span>
                                </label>
                              </li>
                            </div>
                          </div>

                          {{--                          <li data-label="CURRYEAR">--}}
                          {{--                            <label class="facet-label" for="facet_for_curryear">--}}
                          {{--                            <span class="custom-checkbox">--}}
                          {{--                              <input id="facet_for_curryear" data-search-url=""--}}
                          {{--                                     @if (in_array('CURRYEAR', $code)) checked="" @endif value="CURRYEAR"--}}
                          {{--                                     data-for="prod-code" data-value="CURRYEAR"--}}
                          {{--                                     type="checkbox">--}}
                          {{--                              <span class="ps-shown-by-js">--}}
                          {{--                                <i class="material-icons checkbox-checked"></i>--}}
                          {{--                              </span>--}}
                          {{--                            </span>--}}
                          {{--                              <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow"">Šī gada</a>--}}
                          {{--                            </label>--}}
                          {{--                          </li>--}}
                        </ul>
                      </section>

                      @if ($season_title == 'ziemas-riepas')

                        <section class="facet clearfix facet--4">
                          <h1 class="h6 facet-title facet-hover type-dropdown-btn"><b>Tips</b></h1>
                          <div class="title hidden-md-up" data-target="#facet_11641" data-toggle="collapse">
                            <h1 class="h6 facet-title">Tips</h1>
                            <span class="float-xs-right">
                          <span class="navbar-toggler collapse-icons">
                            <i class="material-icons add"></i>
                            <i class="material-icons remove"></i>
                          </span>
                        </span>
                          </div>

                          <ul id="facet_type">
                            <li data-label="M+S">
                              <label class="facet-label" for="facet_for_ms">
                            <span class="custom-checkbox">
                              <input id="facet_for_ms" data-search-url=""
                                     @if (in_array(1, explode(' ', request()->type))) checked="" @endif value="1"
                                     data-for="prod-type" data-value="M+S" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow" style="margin-top: 0px;">M+S</a>
                              </label>
                            </li>
                            <li data-label="Studdable">
                              <label class="facet-label" for="facet_for_studdable">
                            <span class="custom-checkbox">
                              <input id="facet_for_studdable" data-search-url=""
                                     @if (in_array(2, explode(' ', request()->type))) checked="" @endif value="2"
                                     data-for="prod-type" data-value="Studdable" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">Radžojama</a>
                              </label>
                            </li>
                            <li data-label="Studs">
                              <label class="facet-label" for="facet_for_studs">
                            <span class="custom-checkbox">
                              <input id="facet_for_studs" data-search-url=""
                                     @if (in_array(3, explode(' ', request()->type))) checked="" @endif value="3"
                                     data-for="prod-type" data-value="Studs" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">Ar radzēm</a>
                              </label>
                            </li>
                            <li data-label="Winter">
                              <label class="facet-label" for="facet_for_winter">
                            <span class="custom-checkbox">
                              <input id="facet_for_winter" data-search-url=""
                                     @if (in_array(4, explode(' ', request()->type))) checked="" @endif value="4"
                                     data-for="prod-type" data-value="Winter" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">Ziemas</a>
                              </label>
                            </li>
                          </ul>
                        </section>

                      @endif

                      <div class="row flex flex-params">
                        <div class="col-md-4">
                          <section class="facet clearfix facet--8">
                            <h1 class="h6 facet-title facet-hover fuel-eco-dropdown-btn">
                              <div class="icon-tire-fuel">
                                <img src="https://i.imgur.com/77wfTHY.png" style="width: 80px; position: relative; left: -15px; top: 3px;">
                              </div>
                            </h1>
                            <div class="title hidden-md-up" data-target="#facet_70638">
                              <h1 class="h6 facet-title">Degvielas ekonomija</h1>
                              <span class="float-xs-right">
                                <span class="navbar-toggler collapse-icons">
                                  <i class="material-icons add"></i>
                                  <i class="material-icons remove"></i>
                                </span>
                              </span>
                            </div>
                            <ul id="facet_fuel_eco" class="collapse">
                              <li data-label="A">
                                <label class="facet-label" for="facet_fuel_eco_a">
                            <span class="custom-checkbox">
                              <input id="facet_fuel_eco_a" data-search-url=""
                                     @if (in_array('A', explode(' ', request()->fuel))) checked="" @endif value="A"
                                     data-for="fuel_efficiency" data-value="A" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">A</a>
                                </label>
                              </li>
                              <li data-label="B">
                                <label class="facet-label" for="facet_fuel_eco_b">
                            <span class="custom-checkbox">
                              <input id="facet_fuel_eco_b" data-search-url=""
                                     @if (in_array('B', explode(' ', request()->fuel))) checked="" @endif value="B"
                                     data-for="fuel_efficiency" data-value="B" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">B</a>
                                </label>
                              </li>
                              <li data-label="C">
                                <label class="facet-label" for="facet_fuel_eco_c">
                            <span class="custom-checkbox">
                              <input id="facet_fuel_eco_c" data-search-url=""
                                     @if (in_array('C', explode(' ', request()->fuel))) checked="" @endif value="C"
                                     data-for="fuel_efficiency" data-value="C" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">C</a>
                                </label>
                              </li>
                              <li data-label="D">
                                <label class="facet-label" for="facet_fuel_eco_d">
                            <span class="custom-checkbox">
                              <input id="facet_fuel_eco_d" data-search-url=""
                                     @if (in_array('D', explode(' ', request()->fuel))) checked="" @endif value="D"
                                     data-for="fuel_efficiency" data-value="D" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">D</a>
                                </label>
                              </li>
                              <li data-label="E">
                                <label class="facet-label" for="facet_fuel_eco_e">
                            <span class="custom-checkbox">
                              <input id="facet_fuel_eco_e" data-search-url=""
                                     @if (in_array('E', explode(' ', request()->fuel))) checked="" @endif value="E"
                                     data-for="fuel_efficiency" data-value="E" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">E</a>
                                </label>
                              </li>


                              <li data-label="F">
                                <label class="facet-label" for="facet_fuel_eco_f">
                            <span class="custom-checkbox">
                              <input id="facet_fuel_eco_f" data-search-url=""
                                     @if (in_array('F', explode(' ', request()->fuel))) checked="" @endif value="F"
                                     data-for="fuel_efficiency" data-value="F" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">F</a>
                                </label>
                              </li>

                              <li data-label="G">
                                <label class="facet-label" for="facet_fuel_eco_g">
                            <span class="custom-checkbox">
                              <input id="facet_fuel_eco_g" data-search-url=""
                                     @if (in_array('G', explode(' ', request()->fuel))) checked="" @endif value="G"
                                     data-for="fuel_efficiency" data-value="G" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">G</a>
                                </label>
                              </li>
                            </ul>
                          </section>
                        </div>
                        <div class="col-md-4">
                          <section class="facet clearfix facet--9">
                            <h1 class="h6 facet-title facet-hover wet-surface-dropdown-btn">
                              <div class="icon-tire-rain">
                                <img style="width: 80px;position: relative;left: -15px;top: 3px;" src="https://i.imgur.com/TVeVuMf.png">
                              </div>
                            </h1>
                            <div class="title hidden-md-up" data-target="#facet_8079">
                              <h1 class="h6 facet-title">Slapjš segums</h1>
                              <span class="float-xs-right">
                                <span class="navbar-toggler collapse-icons">
                                  <i class="material-icons add"></i>
                                  <i class="material-icons remove"></i>
                                </span>
                              </span>
                            </div>
                            <ul id="facet_wet" class="collapse">
                              <li data-label="A">
                                <label class="facet-label" for="facet_wet_a">
                            <span class="custom-checkbox">
                              <input id="facet_wet_a" data-search-url=""
                                     @if (in_array('A', explode(' ', request()->wet))) checked="" @endif value="A"
                                     data-for="wet_grip" data-value="A" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>

                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">A</a>
                                </label>
                              </li>
                              <li data-label="B">
                                <label class="facet-label" for="facet_wet_b">
                            <span class="custom-checkbox">
                              <input id="facet_wet_b" data-search-url=""
                                     @if (in_array('B', explode(' ', request()->wet))) checked="" @endif value="B"
                                     data-for="wet_grip" data-value="B" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>

                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">B</a>
                                </label>
                              </li>
                              <li data-label="C">
                                <label class="facet-label" for="facet_wet_c">
                            <span class="custom-checkbox">
                              <input id="facet_wet_c" data-search-url=""
                                     @if (in_array('C', explode(' ', request()->wet))) checked="" @endif value="C"
                                     data-for="wet_grip" data-value="C" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>

                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">C</a>
                                </label>
                              </li>
                              <li data-label="D">
                                <label class="facet-label" for="facet_wet_d">
                            <span class="custom-checkbox">
                              <input id="facet_wet_d" data-search-url=""
                                     @if (in_array('D', explode(' ', request()->wet))) checked="" @endif value="D"
                                     data-for="wet_grip" data-value="D" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>

                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">D</a>
                                </label>
                              </li>
                              <li data-label="E">
                                <label class="facet-label" for="facet_wet_e">
                            <span class="custom-checkbox">
                              <input id="facet_wet_e" data-search-url=""
                                     @if (in_array('E', explode(' ', request()->wet))) checked="" @endif value="E"
                                     data-for="wet_grip" data-value="E" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>

                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">E</a>
                                </label>
                              </li>
                              <li data-label="F">
                                <label class="facet-label" for="facet_wet_f">
                            <span class="custom-checkbox">
                              <input id="facet_wet_f" data-search-url=""
                                     @if (in_array('F', explode(' ', request()->wet))) checked="" @endif value="F"
                                     data-for="wet_grip" data-value="F" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>

                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">F</a>
                                </label>
                              </li>
                              <li data-label="G">
                                <label class="facet-label" for="facet_wet_g">
                            <span class="custom-checkbox">
                              <input id="facet_wet_g" data-search-url=""
                                     @if (in_array('G', explode(' ', request()->wet))) checked="" @endif value="G"
                                     data-for="wet_grip" data-value="G" type="checkbox">
                              <span class="ps-shown-by-js">
                                <i class="material-icons checkbox-checked"></i>
                              </span>
                            </span>

                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">G</a>
                                </label>
                              </li>
                            </ul>
                          </section>
                        </div>
                        <div class="col-md-4">
                          <section class="facet clearfix facet--10">
                            <div class="icon-tire-sound" title="Troksnis">
                              <img style="width: 75px;position: relative;left: -5px;margin-bottom: 4px;" src="https://i.imgur.com/fjyPUVN.png">
                            </div>
                            <div class="title hidden-md-up" data-target="#facet_8079">
                              <h1 class="h6 facet-title">Trokšņa līmenis</h1>
                              <span class="float-xs-right">
                                <span class="navbar-toggler collapse-icons">
                                  <i class="material-icons add"></i>
                                  <i class="material-icons remove"></i>
                                </span>
                              </span>
                            </div>


                            <ul id="facet_noise" class="collapse">
                              <li data-label="A">
                                <label class="facet-label" for="facet_noise_a">
                                  <span class="custom-checkbox">
                                    <input id="facet_noise_a" data-search-url=""
                                           @if (in_array('A', explode(' ', request()->noise))) checked="" @endif value="A"
                                           data-for="noise" data-value="A" type="checkbox">
                                    <span class="ps-shown-by-js">
                                      <i class="material-icons checkbox-checked"></i>
                                    </span>
                                  </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">A</a>
                                </label>
                              </li>
                              <li data-label="B">
                                <label class="facet-label" for="facet_noise_b">
                                  <span class="custom-checkbox">
                                    <input id="facet_noise_b" data-search-url=""
                                           @if (in_array('B', explode(' ', request()->noise))) checked="" @endif value="B"
                                           data-for="noise" data-value="B" type="checkbox">
                                    <span class="ps-shown-by-js">
                                      <i class="material-icons checkbox-checked"></i>
                                    </span>
                                  </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">B</a>
                                </label>
                              </li>
                              <li data-label="C">
                                <label class="facet-label" for="facet_noise_c">
                                  <span class="custom-checkbox">
                                    <input id="facet_noise_c" data-search-url=""
                                           @if (in_array('C', explode(' ', request()->noise))) checked="" @endif value="C"
                                           data-for="noise" data-value="C" type="checkbox">
                                    <span class="ps-shown-by-js">
                                      <i class="material-icons checkbox-checked"></i>
                                    </span>
                                  </span>
                                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">C</a>
                                </label>
                              </li>
                            </ul>
                          </section>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
            </div>
          </div>
        </div>
      </div>

<script src="{{ asset('js/autoTiresAjax.js') }}?rev={{ is_file(public_path('js/autoTiresAjax.js')) ? filemtime(public_path('js/autoTiresAjax.js')) : time() }}"></script>
<style>
  .dual-size-tires-table .tire-table-link {
    display: block;
    white-space: normal;
  }
</style>
@endsection
