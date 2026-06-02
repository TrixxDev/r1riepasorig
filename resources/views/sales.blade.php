@extends('layouts.app')

@section('body-title', 'category')
@section('title', 'lang-' . app()->getLocale() . ' country-' . app()->getLocale() . ' layout-both-columns page-category tax-display-enabled category-id-14 category-id-parent-12 category-depth-level-3')

@section('content')
    <div class="container">
        <div class="row">
            <div class="main-content clearfix col-md-12 col-xl-12">
                <div id="left-column" class="col-md-12 col-lg-3">
                    <!-- begin D:\OpenServer\domains\r1old/themes/classic/modules/ps_facetedsearch/ps_facetedsearch.tpl -->
                    <div id="search_filters_wrapper">
                        <form method="get" class="category-search">
                            <div id="search_filters" class="params">
                                <input type="hidden" id="facet_all_val" value="Visi">
                                <div class="wrap">

                                    <h4 class="text-uppercase h6 hidden-sm-down">
                                        <span id="search_filters_auto" class="params auto">Auto</span>
                                        <span id="search_filters_params" class="params active">Kategorija</span>
                                    </h4>

                                    <div class="can-collapse">

                                        <div class="sidebar-top">
                                            <div style="width: 100%">
                                                <div class="form-group mb-0">
                                                    <select name="category" class="r1-select select-title tire-category">
                                                        <option class="select-list" value="Visi">Izvēlēties</option>
                                                        @foreach ($categories as $category_id => $category)
                                                            <option class="select-list" value="{{ $category_id }}">{{ ucwords($category['name']) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <section class="facet clearfix">
                                                <button id="autofind_sub" type="submit">
                                                    Meklēt <i class="material-icons search"></i>
                                                </button>
                                            </section>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="content-wrapper" class="col-md-12 col-lg-9">
                    <section id="main" class="sale-positions">
                        @foreach ($sections as $section)
                            @if ($section)
                                {!! $section !!}
                            @endif
                        @endforeach
                    </section>
                </div>
            </div>

<script src="{{ asset('js/sales-cart.js?v=' . time()) }}"></script>
@endsection
