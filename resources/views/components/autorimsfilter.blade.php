<div id="search_filters_wrapper" class="hidden-sm-down">
  <form method="get" action="{{ route('lietie-diski-meklet') }}">
  <div id="search_filters" class="auto">
    <input type="hidden" id="facet_all_val" value="Visi">
    <div class="wrap">
      <h4 class="text-uppercase h6 hidden-sm-down">
{{--        <span id="search_filters_auto" class="params auto active">Auto</span>--}}
        <span id="search_filters_params" class="
        params
        params-solo" style="width: 100%!important;">Parametri</span>
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
              <h1 class="h6 facet-title hidden-sm-down">Marka</h1>
              <div class="title hidden-md-up" data-target="#facet_auto-make" data-toggle="collapse" aria-expanded="true">
                <h1 class="h6 facet-title">Marka</h1>
                <span class="float-xs-right">j
                  <span class="navbar-toggler collapse-icons">
                    <i class="material-icons add"></i>
                    <i class="material-icons remove"></i>
                  </span>
                </span>
              </div>
              <select name="" id="" class="r1-select select-title">
                <option value="visi">Visi</option>
                @foreach($makes as $make)
                  <option value="{{$make}}">{{$make}}</option>
                @endforeach
              </select>

            </section>
            <section class="facet clearfix">
              <h1 class="h6 facet-title hidden-sm-down">Modelis</h1>
              <div class="title hidden-md-up" data-target="#facet_auto-model" data-toggle="collapse" aria-expanded="true">
                <h1 class="h6 facet-title">Modelis</h1>
                <span class="float-xs-right">
                  <span class="navbar-toggler collapse-icons">
                    <i class="material-icons add"></i>
                    <i class="material-icons remove"></i>
                  </span>
                </span>
              </div>
              <select name="" id="" class="r1-select select-title">
                <option value="visi">Visi</option>
                @foreach($models as $model)
                  <option value="{{$model}}">{{$model}}</option>
                @endforeach
              </select>
            </section>
            <section class="facet clearfix">
              <h1 class="h6 facet-title hidden-sm-down">Disku diametrs</h1>
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
              <h1 class="h6 facet-title hidden-sm-down">Skrūvju skaits</h1>
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
              <h1 class="h6 facet-title hidden-sm-down">Attālums starp skrūvēm</h1>
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
              <h1 class="h6 facet-title hidden-sm-down">Disku diametrs</h1>
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
              <h1 class="h6 facet-title hidden-sm-down">Izbīdījums</h1>
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
            <section class="facet clearfix">
              <h1 class="h6 facet-title hidden-sm-down">Centrs</h1>
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
                    <input id="facet_availability_0" type="checkbox" data-search-url="#" data-color="green">
                    <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                </span>
                Pieejams
                <span class="dot green" style="float:right;margin-top: 3px;"></span>
              </label>
            </li>
            <li>
              <label class="facet-label" for="facet_availability_1" style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                <span class="custom-checkbox">
                    <input id="facet_availability_1" type="checkbox" data-search-url="#" data-color="yellow">
                    <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                </span>
                Pasutams
                <span class="dot yellow" style="float:right;margin-top: 3px;"></span>
              </label>
            </li>
            <li>
              <label class="facet-label" for="facet_availability_2" style="width: 100%;text-align: left;cursor: pointer">
                <span class="custom-checkbox">
                    <input id="facet_availability_2" type="checkbox" data-search-url="#" data-color="red">
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
