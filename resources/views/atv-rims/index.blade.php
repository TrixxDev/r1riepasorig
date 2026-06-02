@extends('layouts.app')



@section('body-title', 'category')

@section('title', 'lang-' . app()->getLocale() . ' country-' . app()->getLocale() . ' layout-both-columns page-category tax-display-enabled category-kvadru-diski category-id-parent-20 category-depth-level-3 atv-rims-catalog')

@section('meta_title', 'Kvadraciklu lietie diski | R1 Riepu Serviss')

@section('meta_description', 'Kvadraciklu diskas ar filtru pēc bolt circle (PCD). Saraksts un attēlu režīms.')



@section('content')

  <div class="container atv-rims-page atv-mode-list" id="atv-rims-catalog">

    <div class="row">

      <div class="main-content clearfix col-md-12">

        <div id="left-column" class="col-md-12 col-lg-3">

          <div id="search_filters_wrapper">

            <form method="get" action="{{ route('kvadraciklu-diski-meklet') }}" id="atv-rims-filter-form">

              <div id="search_filters" class="auto">

                <input type="hidden" id="facet_all_val" value="Visi">

                <div class="wrap">

                  <h4 class="text-uppercase h6">

                    <span id="search_filters_params" class="params params-solo" style="width: 100%!important;">Parametri</span>

                  </h4>

                  <div class="can-collapse">

                    <span class="show_list active" data-dismiss="modal"><i class="material-icons"></i>Saraksts</span>

                    <span class="show_grid" data-dismiss="modal"><i class="material-icons"></i>Bilde</span>

                    <div class="sidebar-auto">

                      <section class="facet clearfix">

                        <h1 class="h6 facet-title">Attālums starp skrūvēm</h1>

                        <div class="title hidden-md-up" data-target="#facet_atv_pcd" data-toggle="collapse">

                          <h1 class="h6 facet-title">Attālums starp skrūvēm</h1>

                          <span class="float-xs-right">

                            <span class="navbar-toggler collapse-icons">

                              <i class="material-icons add"></i>

                              <i class="material-icons remove"></i>

                            </span>

                          </span>

                        </div>

                        <select name="currentPcd" class="r1-select select-title select-rim-spread">

                          <option value="Visi" @if(($currentPcdForView === 'Visi') || $currentPcdForView === null) selected @endif>Visi</option>

                          @foreach ($pcdValues as $pcdOption)

                            <option value="{{ $pcdOption }}" @if((string) $currentPcdForView === (string) $pcdOption) selected @endif>{{ $pcdOption }}</option>

                          @endforeach

                        </select>

                      </section>

                    </div>

                    <section class="facet clearfix">

                      <button id="autofind_sub" class="filter-button" type="submit">Meklēt <i class="material-icons search"></i></button>

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

                    </section>

                  </div>

                </div>

              </div>

            </form>

          </div>

        </div>



        <div class="loading-block-content" style="display: none; position:absolute;">

          <div class="loading-content">

            <svg class="machine" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 645 526">

              <g>

                <path class="large-shadow" d="M645 194v-21l-29-4c-1-10-3-19-6-28l25-14 -8-19 -28 7c-5-8-10-16-16-24L602 68l-15-15 -23 17c-7-6-15-11-24-16l7-28 -19-8 -14 25c-9-3-18-5-28-6L482 10h-21l-4 29c-10 1-19 3-28 6l-14-25 -19 8 7 28c-8 5-16 10-24 16l-23-17L341 68l17 23c-6 7-11 15-16 24l-28-7 -8 19 25 14c-3 9-5 18-6 28l-29 4v21l29 4c1 10 3 19 6 28l-25 14 8 19 28-7c5 8 10 16 16 24l-17 23 15 15 23-17c7 6 15 11 24 16l-7 28 19 8 14-25c9 3 18 5 28 6l4 29h21l4-29c10-1 19-3 28-6l14 25 19-8 -7-28c8-5 16-10 24-16l23 17 15-15 -17-23c6-7 11-15 16-24l28 7 8-19 -25-14c3-9 5-18 6-28L645 194zM471 294c-61 0-110-49-110-110S411 74 471 74s110 49 110 110S532 294 471 294z"></path>

              </g>

            </svg>

          </div>

        </div>



        <div id="content-wrapper" class="col-md-12 col-lg-9">

          <section id="main">

            <section id="products" class="">

              @if($rims->count())
                <div id="atv-view-grid">
                  @include('atv-rims._grid', ['rims' => $rims])
                </div>
              @endif

              <div id="js-product-list">

                <div class="products row hide-price title-flip">

                  @if($rims->count())

                    @include('atv-rims.list-table', ['rims' => $rims])

                  @else

                    <p class="col-12">Nav atrastu disku pēc izvēlētajiem parametriem.</p>

                  @endif

                </div>

                @if($rims->hasPages())

                  <div class="pagination-col mt-3">{{ $rims->appends(request()->query())->links() }}</div>

                @endif

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

          <section class="facet clearfix">

            <h1 class="h6 facet-title">Attālums starp skrūvēm</h1>

            <select name="currentPcd" form="atv-rims-filter-form" class="r1-select select-title select-rim-spread">

              <option value="Visi" @if(($currentPcdForView === 'Visi') || $currentPcdForView === null) selected @endif>Visi</option>

              @foreach ($pcdValues as $pcdOption)

                <option value="{{ $pcdOption }}" @if((string) $currentPcdForView === (string) $pcdOption) selected @endif>{{ $pcdOption }}</option>

              @endforeach

            </select>

          </section>

          <button type="submit" form="atv-rims-filter-form" class="btn btn-primary btn-sm mt-2">Meklēt</button>

        </div>

      </div>

    </div>

  </div>



  <style>
    .atv-rims-page.atv-hide-unselected .js-atv-rim-row.atv-suppress { display: none !important; }
    #atv-rims-catalog.atv-mode-list #atv-view-grid { display: none !important; }
    #atv-rims-catalog.atv-mode-grid #js-product-list { display: none !important; }
  </style>

  <script src="{{ asset('js/rimAjax.js?rev=' . time()) }}"></script>

  <script src="{{ asset('js/atv-rims.js?v='.(int) microtime(true)) }}"></script>

@endsection

