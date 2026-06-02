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
              <select name="application" id="" class="r1-select select-title">
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

              <select name="stud_length" id="" class="r1-select select-title">
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
