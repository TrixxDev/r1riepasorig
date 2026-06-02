<div id="search_filters" class="params">
    <input type="hidden" id="facet_all_val" value="Visi">
    <div class="season-select">
        <a href="{{ str_replace('ziemas', 'vasaras', request()->getRequestUri()) }}" class="summer-tires-link season-select-link @if ($season_title === 'vasaras-riepas'){{'selected-link'}}@endif">Vasara</a>
        <a href="{{ str_replace('vasaras', 'ziemas', request()->getRequestUri()) }}" class="winter-tires-link season-select-link @if ($season_title === 'ziemas-riepas'){{'selected-link'}}@endif">Ziema</a>
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
      <section class="facet clearfix facet--availability" style="padding-top: 0">
        <ul class="collapse">
          <li class="show-selected-checkbox-li">
            <label class="facet-label" for="show-selected-checkbox"
                   style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                              <span class="custom-checkbox">
                                <input type="checkbox" value="only_selected" class="tire-table-checkbox" id="show-selected-checkbox" @if (request()->show_selected) checked @endif title="" disabled>
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
