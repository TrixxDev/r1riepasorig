<div id="search_filters_wrapper" class="hidden-sm-down">
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
                <label class="facet-label" for="show-selected-checkbox-mobile"
                       style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                          <span class="custom-checkbox">
                            <input type="checkbox" value="only_selected" class="show-selected-filter tire-table-checkbox" id="show-selected-checkbox-mobile" title="Rādīt tikai atzīmētās preces" @if (request()->show_selected) checked @endif disabled>
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

