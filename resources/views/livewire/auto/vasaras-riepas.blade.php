<div class="container">
    <div class="row">
        <div class="main-content clearfix col-md-12 col-xl-10">

            <div id="left-column" class="col-md-12 col-lg-3">
                <!-- begin D:\OpenServer\domains\r1old/themes/classic/modules/ps_facetedsearch/ps_facetedsearch.tpl -->
                <div id="search_filters_wrapper" class="hidden-sm-down">
                    <div id="search_filter_controls" class="hidden-md-up">

                        <button class="btn btn-secondary ok">
                            <i class="material-icons"></i>
                            Labi
                        </button>
                    </div>
                    <div id="search_filters" class="params">
                        <input type="hidden" id="facet_all_val" value="Visi">
                        <div class="wrap">

                            <h4 class="text-uppercase h6 hidden-sm-down">
                                <span id="search_filters_auto" class="params auto">Auto</span><span
                                    id="search_filters_params" class="params active">Parametri</span>
                            </h4>

                            <div class="can-collapse">

                                <span class="show_list active"><i class="material-icons "></i>Saraksts</span>
                                <span class="show_grid"><i class="material-icons "></i>Bilde</span>

                                <template id="facet-template">
                                    <section class="facet clearfix">
                                        <h1 class="h6 facet-title hidden-sm-down">Kods</h1>
                                        <input type="text" value="" id="autofind_atr">
                                        <button id="autofind_sub">Meklēt <i class="material-icons search"></i>
                                        </button>
                                    </section>
                                </template>

                                <form method="get" wire:submit.prevent="search">
                                <div class="sidebar-top">


                                    <section class="facet clearfix facet--0 facet-ind-0">
                                        <h1 class="h6 facet-title hidden-sm-down">Ražotājs</h1>
                                        <div class="title hidden-md-up" data-target="#facet_20294"
                                             data-toggle="collapse">
                                            <h1 class="h6 facet-title">Ražotājs</h1>
                                            <span class="float-xs-right">
                                                <span class="navbar-toggler collapse-icons">
                                                    <i class="material-icons add"></i>
                                                    <i class="material-icons remove"></i>
                                                </span>
                                            </span>
                                        </div>
                                        <ul id="facet_20294" class="collapse">
                                            <li>
                                                <div class="col-sm-12 col-xs-12 col-md-12 facet-dropdown dropdown">
                                                    <input type="text" readonly class="select-title tire-brand" wire:model="tire_brand" value="{{ $currBrand }}">
                                                    <i class="material-icons float-xs-right"></i>
                                                    <div class="dropdown-menu">
                                                        <a rel="nofollow" wire:click="changeBrand('')" class="select-list">
                                                            Visi
                                                        </a>
                                                        @foreach ($brands as $brand)
                                                            <a rel="nofollow" class="select-list" wire:click="changeBrand('{{ $brand->title }}')" id="{{ $brand->title }}">
                                                                {{ $brand->title }}
                                                            </a>
                                                        @endforeach

                                                    </div>
                                                </div>
                                            </li>
                                        </ul>


                                    </section>


                                    <section class="facet clearfix facet--1 facet-ind-1">
                                        <h1 class="h6 facet-title hidden-sm-down">Platums</h1>
                                        <div class="title hidden-md-up" data-target="#facet_78843"
                                             data-toggle="collapse" aria-expanded="true">
                                            <h1 class="h6 facet-title">Platums</h1>
                                            <span class="float-xs-right">
                                                <span class="navbar-toggler collapse-icons">
                                                    <i class="material-icons add"></i>
                                                    <i class="material-icons remove"></i>
                                                </span>
                                            </span>
                                        </div>


                                        <ul id="facet_78843" class="collapse in">
                                            <li>
                                                <div class="col-sm-12 col-xs-12 col-md-12 facet-dropdown dropdown">
                                                    <input type="text" readonly class="select-title tire-width" value="{{ $d1 }}">
                                                    <i class="material-icons float-xs-right"></i>
                                                    <div class="dropdown-menu width">

                                                        <a rel="nofollow" data-d1="all" wire:click="changeD1('')" class="select-list">
                                                            Visi
                                                        </a>
                                                        @foreach ($autoTiresD1 as $tire)
                                                        <a rel="nofollow" class="select-list" wire:click="changeD1({{ $tire->d1 }})" id="{{ $tire->d1 }}">
                                                            {{ $tire->d1 }}
                                                        </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>


                                    </section>


                                    <section class="facet clearfix facet--2 facet-ind-2">
                                        <h1 class="h6 facet-title hidden-sm-down">Augstums</h1>
                                        <div class="title hidden-md-up" data-target="#facet_15402"
                                             data-toggle="collapse" aria-expanded="true">
                                            <h1 class="h6 facet-title">Augstums</h1>
                                            <span class="float-xs-right">
                                                <span class="navbar-toggler collapse-icons">
                                                    <i class="material-icons add"></i>
                                                    <i class="material-icons remove"></i>
                                                </span>
                                            </span>
                                        </div>


                                        <ul id="facet_15402" class="collapse in">
                                            <li>
                                                <div class="col-sm-12 col-xs-12 col-md-12 facet-dropdown dropdown">
                                                    <input type="text" class="select-title tire-height" readonly value="{{ $d2 }}">
                                                    <i class="material-icons float-xs-right"></i>
                                                    <div class="dropdown-menu height">

                                                        <a rel="nofollow" data-d2="all" wire:click="changeD2('')" class="select-list">
                                                            Visi
                                                        </a>
                                                        @foreach ($autoTiresD2 as $tire)
                                                            <a rel="nofollow" class="select-list" wire:click="changeD2({{ $tire->d2 }})" id="{{ $tire->d2 }}">
                                                                {{ $tire->d2 }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>


                                    </section>


                                    <section class="facet clearfix facet--3 facet-ind-3">
                                        <h1 class="h6 facet-title hidden-sm-down">Diametrs</h1>
                                        <div class="title hidden-md-up" data-target="#facet_24954"
                                             data-toggle="collapse" aria-expanded="true">
                                            <h1 class="h6 facet-title">Diametrs</h1>
                                            <span class="float-xs-right">
                                                <span class="navbar-toggler collapse-icons">
                                                    <i class="material-icons add"></i>
                                                    <i class="material-icons remove"></i>
                                                </span>
                                            </span>
                                        </div>


                                        <ul id="facet_24954" class="collapse in">
                                            <li>
                                                <div class="col-sm-12 col-xs-12 col-md-12 facet-dropdown dropdown">
                                                    <input type="text" class="select-title tire-radius" readonly value="{{ $d3 }}">
                                                    <i class="material-icons float-xs-right"></i>
                                                    <div class="dropdown-menu radius">

                                                        @foreach ($autoTiresD3 as $tire)
                                                            <a rel="nofollow" class="select-list" wire:click="changeD3({{ $tire->d3 }})" id="{{ $tire->d3 }}">
                                                                {{ $tire->d3 }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>

                                    </section>
                                    <section class="facet clearfix">
                                        <h1 class="h6 facet-title hidden-sm-down">Kods</h1>
                                        <input type="text" value="" id="autofind_atr">
                                        <button id="autofind_sub" type="submit">Meklēt <i class="material-icons search"></i>
                                        </button>
                                    </section>

                                </div>
                                </form>
                            </div>
                        </div>
                        <div class="wrap">
                            <div class="sidebar-bottom">


                                <section class="facet clearfix facet--27">
                                    <h1 class="h6 facet-title hidden-sm-down">TOP40</h1>
                                    <div class="title hidden-md-up" data-target="#facet_39112"
                                         data-toggle="collapse">
                                        <h1 class="h6 facet-title">TOP40</h1>
                                        <span class="float-xs-right">
                                            <span class="navbar-toggler collapse-icons">
                                                <i class="material-icons add"></i>
                                                <i class="material-icons remove"></i>
                                            </span>
                                        </span>
                                    </div>


                                    <ul id="facet_39112" class="collapse">
                                        <li data-label="Top+40">
                                            <label class="facet-label" for="facet_input_39112_0">
                                                                                                                    <span
                                                                                                                        class="custom-checkbox">
                            <input id="facet_input_39112_0"
                                   data-search-url="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Augstums-55/Diametrs-16/TOP40-Top+40"
                                   type="checkbox">
                                                                                                <span
                                                                                                    class="ps-shown-by-js"><i
                                                                                                        class="material-icons checkbox-checked"></i></span>
                                                                                            </span>

                                                <a href="javascript:;"
                                                   class="_gray-darker search-link js-search-link" rel="nofollow">
                                                    Top 40
                                                </a>
                                            </label>
                                        </li>
                                    </ul>


                                </section>


                                <section class="facet clearfix facet--4">
                                    <h1 class="h6 facet-title hidden-sm-down">Kods</h1>
                                    <div class="title hidden-md-up" data-target="#facet_11641"
                                         data-toggle="collapse">
                                        <h1 class="h6 facet-title">Kods</h1>
                                        <span class="float-xs-right">
              <span class="navbar-toggler collapse-icons">
                <i class="material-icons add"></i>
                <i class="material-icons remove"></i>
              </span>
            </span>
                                    </div>


                                    <ul id="facet_11641" class="collapse">
                                        <li data-label="XL">
                                            <label class="facet-label" for="facet_input_11641_0">
                                                                                                                    <span
                                                                                                                        class="custom-checkbox">
                            <input id="facet_input_11641_0"
                                   data-search-url="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Augstums-55/Diametrs-16/Kods-XL"
                                   type="checkbox">
                                                                                                <span
                                                                                                    class="ps-shown-by-js"><i
                                                                                                        class="material-icons checkbox-checked"></i></span>
                                                                                            </span>

                                                <a href="javascript:;"
                                                   class="_gray-darker search-link js-search-link" rel="nofollow">
                                                    XL
                                                </a>
                                            </label>
                                        </li>
                                        <li data-label="RSC">
                                            <label class="facet-label" for="facet_input_11641_1">
                                                                                                                    <span
                                                                                                                        class="custom-checkbox">
                            <input id="facet_input_11641_1"
                                   data-search-url="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Augstums-55/Diametrs-16/Kods-RSC"
                                   type="checkbox">
                                                                                                <span
                                                                                                    class="ps-shown-by-js"><i
                                                                                                        class="material-icons checkbox-checked"></i></span>
                                                                                            </span>

                                                <a href="javascript:;"
                                                   class="_gray-darker search-link js-search-link" rel="nofollow">
                                                    RSC
                                                </a>
                                            </label>
                                        </li>
                                    </ul>


                                </section>


                                <section class="facet clearfix facet--5">
                                    <h1 class="h6 facet-title hidden-sm-down">Tips</h1>
                                    <div class="title hidden-md-up" data-target="#facet_37451"
                                         data-toggle="collapse">
                                        <h1 class="h6 facet-title">Tips</h1>
                                        <span class="float-xs-right">
              <span class="navbar-toggler collapse-icons">
                <i class="material-icons add"></i>
                <i class="material-icons remove"></i>
              </span>
            </span>
                                    </div>


                                    <ul id="facet_37451" class="collapse">
                                        <li data-label="M%2BS">
                                            <label class="facet-label" for="facet_input_37451_0">
                                                                                                                    <span
                                                                                                                        class="custom-checkbox">
                            <input id="facet_input_37451_0"
                                   data-search-url="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Augstums-55/Diametrs-16/Tips-M%2BS"
                                   type="checkbox">
                                                                                                <span
                                                                                                    class="ps-shown-by-js"><i
                                                                                                        class="material-icons checkbox-checked"></i></span>
                                                                                            </span>

                                                <a href="javascript:;"
                                                   class="_gray-darker search-link js-search-link" rel="nofollow">
                                                    M+S
                                                </a>
                                            </label>
                                        </li>
                                    </ul>


                                </section>


                                <section class="facet clearfix facet--8">
                                    <h1 class="h6 facet-title hidden-sm-down">Degvielas ekonomija</h1>
                                    <div class="title hidden-md-up" data-target="#facet_70638"
                                         data-toggle="collapse">
                                        <h1 class="h6 facet-title">Degvielas ekonomija</h1>
                                        <span class="float-xs-right">
              <span class="navbar-toggler collapse-icons">
                <i class="material-icons add"></i>
                <i class="material-icons remove"></i>
              </span>
            </span>
                                    </div>


                                    <ul id="facet_70638" class="collapse">
                                        <li data-label="F">
                                            <label class="facet-label" for="facet_input_70638_0">
                                                                                                                    <span
                                                                                                                        class="custom-checkbox">
                            <input id="facet_input_70638_0"
                                   data-search-url="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Augstums-55/Diametrs-16/Degvielas+ekonomija-F"
                                   type="checkbox">
                                                                                                <span
                                                                                                    class="ps-shown-by-js"><i
                                                                                                        class="material-icons checkbox-checked"></i></span>
                                                                                            </span>

                                                <a href="javascript:;"
                                                   class="_gray-darker search-link js-search-link" rel="nofollow">
                                                    F
                                                </a>
                                            </label>
                                        </li>
                                        <li data-label="E">
                                            <label class="facet-label" for="facet_input_70638_1">
                                                                                                                    <span
                                                                                                                        class="custom-checkbox">
                            <input id="facet_input_70638_1"
                                   data-search-url="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Augstums-55/Diametrs-16/Degvielas+ekonomija-E"
                                   type="checkbox">
                                                                                                <span
                                                                                                    class="ps-shown-by-js"><i
                                                                                                        class="material-icons checkbox-checked"></i></span>
                                                                                            </span>

                                                <a href="javascript:;"
                                                   class="_gray-darker search-link js-search-link" rel="nofollow">
                                                    E
                                                </a>
                                            </label>
                                        </li>
                                        <li data-label="B">
                                            <label class="facet-label" for="facet_input_70638_2">
                                                                                                                    <span
                                                                                                                        class="custom-checkbox">
                            <input id="facet_input_70638_2"
                                   data-search-url="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Augstums-55/Diametrs-16/Degvielas+ekonomija-B"
                                   type="checkbox">
                                                                                                <span
                                                                                                    class="ps-shown-by-js"><i
                                                                                                        class="material-icons checkbox-checked"></i></span>
                                                                                            </span>

                                                <a href="javascript:;"
                                                   class="_gray-darker search-link js-search-link" rel="nofollow">
                                                    B
                                                </a>
                                            </label>
                                        </li>
                                        <li data-label="C">
                                            <label class="facet-label" for="facet_input_70638_3">
                                                                                                                    <span
                                                                                                                        class="custom-checkbox">
                            <input id="facet_input_70638_3"
                                   data-search-url="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Augstums-55/Diametrs-16/Degvielas+ekonomija-C"
                                   type="checkbox">
                                                                                                <span
                                                                                                    class="ps-shown-by-js"><i
                                                                                                        class="material-icons checkbox-checked"></i></span>
                                                                                            </span>

                                                <a href="javascript:;"
                                                   class="_gray-darker search-link js-search-link" rel="nofollow">
                                                    C
                                                </a>
                                            </label>
                                        </li>
                                        <li data-label="A">
                                            <label class="facet-label" for="facet_input_70638_4">
                                                                                                                    <span
                                                                                                                        class="custom-checkbox">
                            <input id="facet_input_70638_4"
                                   data-search-url="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Augstums-55/Diametrs-16/Degvielas+ekonomija-A"
                                   type="checkbox">
                                                                                                <span
                                                                                                    class="ps-shown-by-js"><i
                                                                                                        class="material-icons checkbox-checked"></i></span>
                                                                                            </span>

                                                <a href="javascript:;"
                                                   class="_gray-darker search-link js-search-link" rel="nofollow">
                                                    A
                                                </a>
                                            </label>
                                        </li>
                                    </ul>


                                </section>


                                <section class="facet clearfix facet--9">
                                    <h1 class="h6 facet-title hidden-sm-down">Slapjš segums</h1>
                                    <div class="title hidden-md-up" data-target="#facet_8079"
                                         data-toggle="collapse">
                                        <h1 class="h6 facet-title">Slapjš segums</h1>
                                        <span class="float-xs-right">
              <span class="navbar-toggler collapse-icons">
                <i class="material-icons add"></i>
                <i class="material-icons remove"></i>
              </span>
            </span>
                                    </div>


                                    <ul id="facet_8079" class="collapse">
                                        <li data-label="F">
                                            <label class="facet-label" for="facet_input_8079_0">
                                                                                                                    <span
                                                                                                                        class="custom-checkbox">
                            <input id="facet_input_8079_0"
                                   data-search-url="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Augstums-55/Diametrs-16/Slapj%C5%A1+segums-F"
                                   type="checkbox">
                                                                                                <span
                                                                                                    class="ps-shown-by-js"><i
                                                                                                        class="material-icons checkbox-checked"></i></span>
                                                                                            </span>

                                                <a href="javascript:;"
                                                   class="_gray-darker search-link js-search-link" rel="nofollow">
                                                    F
                                                </a>
                                            </label>
                                        </li>
                                        <li data-label="E">
                                            <label class="facet-label" for="facet_input_8079_1">
                                                                                                                    <span
                                                                                                                        class="custom-checkbox">
                            <input id="facet_input_8079_1"
                                   data-search-url="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Augstums-55/Diametrs-16/Slapj%C5%A1+segums-E"
                                   type="checkbox">
                                                                                                <span
                                                                                                    class="ps-shown-by-js"><i
                                                                                                        class="material-icons checkbox-checked"></i></span>
                                                                                            </span>

                                                <a href="javascript:;"
                                                   class="_gray-darker search-link js-search-link" rel="nofollow">
                                                    E
                                                </a>
                                            </label>
                                        </li>
                                        <li data-label="B">
                                            <label class="facet-label" for="facet_input_8079_2">
                                                                                                                    <span
                                                                                                                        class="custom-checkbox">
                            <input id="facet_input_8079_2"
                                   data-search-url="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Augstums-55/Diametrs-16/Slapj%C5%A1+segums-B"
                                   type="checkbox">
                                                                                                <span
                                                                                                    class="ps-shown-by-js"><i
                                                                                                        class="material-icons checkbox-checked"></i></span>
                                                                                            </span>

                                                <a href="javascript:;"
                                                   class="_gray-darker search-link js-search-link" rel="nofollow">
                                                    B
                                                </a>
                                            </label>
                                        </li>
                                        <li data-label="C">
                                            <label class="facet-label" for="facet_input_8079_3">
                                                                                                                    <span
                                                                                                                        class="custom-checkbox">
                            <input id="facet_input_8079_3"
                                   data-search-url="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Augstums-55/Diametrs-16/Slapj%C5%A1+segums-C"
                                   type="checkbox">
                                                                                                <span
                                                                                                    class="ps-shown-by-js"><i
                                                                                                        class="material-icons checkbox-checked"></i></span>
                                                                                            </span>

                                                <a href="javascript:;"
                                                   class="_gray-darker search-link js-search-link" rel="nofollow">
                                                    C
                                                </a>
                                            </label>
                                        </li>
                                        <li data-label="A">
                                            <label class="facet-label" for="facet_input_8079_4">
                                                                                                                    <span
                                                                                                                        class="custom-checkbox">
                            <input id="facet_input_8079_4"
                                   data-search-url="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Augstums-55/Diametrs-16/Slapj%C5%A1+segums-A"
                                   type="checkbox">
                                                                                                <span
                                                                                                    class="ps-shown-by-js"><i
                                                                                                        class="material-icons checkbox-checked"></i></span>
                                                                                            </span>

                                                <a href="javascript:;"
                                                   class="_gray-darker search-link js-search-link" rel="nofollow">
                                                    A
                                                </a>
                                            </label>
                                        </li>
                                    </ul>


                                </section>
                                <section class="facet clearfix facet--availability">
                                    <h1 class="h6 facet-title hidden-sm-down">Pieejamība</h1>
                                    <ul id="facet_availability" class="collapse">
                                        <li>
                                            <label class="facet-label" for="facet_availability_0"
                                                   style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                  <span class="custom-checkbox">
                    <input id="facet_availability_0" type="checkbox" data-search-url="#" data-color="green">
                    <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                  </span>
                                                Pieejams
                                                <span class="dot green" style="float:right;margin-top: 3px;"></span>
                                            </label>
                                        </li>
                                        <li>
                                            <label class="facet-label" for="facet_availability_1"
                                                   style="width: 100%;text-align: left;cursor: pointer;margin-bottom: 5px">
                  <span class="custom-checkbox">
                    <input id="facet_availability_1" type="checkbox" data-search-url="#" data-color="yellow">
                    <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                  </span>
                                                Pasutams
                                                <span class="dot yellow"
                                                      style="float:right;margin-top: 3px;"></span>
                                            </label>
                                        </li>
                                        <li>
                                            <label class="facet-label" for="facet_availability_2"
                                                   style="width: 100%;text-align: left;cursor: pointer">
                  <span class="custom-checkbox">
                    <input id="facet_availability_2" type="checkbox" data-search-url="#" data-color="red">
                    <span class="ps-shown-by-js"><i class="material-icons checkbox-checked"></i></span>
                  </span>
                                                Zvaniet!
                                                <span class="dot red" style="float:right;margin-top: 3px;"></span>
                                            </label>
                                        </li>
                                    </ul>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="content-wrapper" class="col-md-12 col-lg-9">
                <section id="main">
                    <section id="products" class="">
                        <div id="">
                            <div class="row products-selection">
                                <div class="col-md-8 hidden-md-down">
                                    <nav data-depth="3" class="breadcrumb hidden-sm-down">
                                        <ol itemscope="" itemtype="http://schema.org/BreadcrumbList">
                                            <li itemprop="itemListElement" itemscope=""
                                                itemtype="http://schema.org/ListItem">
                                                <a itemprop="item" href="http://r1riepas.lv/index.php">
                                                    <span itemprop="name">Sākumlapa</span>
                                                </a>
                                                <meta itemprop="position" content="1">
                                            </li>
                                            <li itemprop="itemListElement" itemscope=""
                                                itemtype="http://schema.org/ListItem">
                                                <a itemprop="item"
                                                   href="http://r1riepas.lv/index.php?id_category=12&amp;controller=category&amp;id_lang=2">
                                                    <span itemprop="name">Riepas</span>
                                                </a>
                                                <meta itemprop="position" content="2">
                                            </li>
                                            <li itemprop="itemListElement" itemscope=""
                                                itemtype="http://schema.org/ListItem">
                                                <a itemprop="item"
                                                   href="http://r1riepas.lv/index.php?id_category=14&amp;controller=category&amp;id_lang=2">
                                                    <span itemprop="name">Vasaras riepas</span>
                                                </a>
                                                <meta itemprop="position" content="3">
                                            </li>
                                        </ol>
                                    </nav>
                                </div>
                                <div class="col-md-6">
                                    <div class="row sort-by-row">
                                        <div class="col-sm-3 col-xs-4 hidden-md-up filter-button">
                                            <button id="search_filter_toggler" class="btn btn-secondary">
                                                Filtrs
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="" class="hidden-sm-down">
                            <section id="js-active-search-filters" class="active_filters">
                                <h1 class="h6 active-filter-title">Active filters</h1>
                                <ul>
                                    <li class="filter-block">
                                        205
                                        <a class="js-search-link"
                                           href="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Augstums-55/Diametrs-16"><i
                                                class="material-icons close"></i></a>
                                    </li>
                                    <li class="filter-block">
                                        55
                                        <a class="js-search-link"
                                           href="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Diametrs-16"><i
                                                class="material-icons close"></i></a>
                                    </li>
                                    <li class="filter-block">
                                        16
                                        <a class="js-search-link"
                                           href="http://r1riepas.lv/index.php?controller=category&amp;id_category=14&amp;id_lang=2&amp;q=Platums-205/Augstums-55"><i
                                                class="material-icons close"></i></a>
                                    </li>

                                </ul>
                            </section>
                        </div>
                        <div id="">
                            <div id="js-product-list">
                                <div class="products row hide-price">

                                    <div class="table-top product_show_list">
                                        <span class="table-cell sortable"
                                              data-filter=".product-description .product-title a" data-order="DESC">Brends / modelis</span>
                                        <span class="table-cell hidden-sm-down">LI/SI</span>
                                        <span class="table-cell hidden-sm-down">Kods</span>
                                        <span class="table-cell hidden-sm-down fuel_efficiency-head">Degvielas ekonomija</span>
                                        <span class="table-cell hidden-sm-down wet_grip-head">Slapjš segums</span>
                                        <span class="table-cell hidden-sm-down external_noise-head">Skaļums</span>
                                        <span class="table-cell sortable"
                                              data-filter=".product-price-and-shipping .regular-price"
                                              data-order="DESC">Veikala cena</span>
                                        <span class="table-cell sortable"
                                              data-filter=".product-price-and-shipping .price" data-order="DESC">Akcijas cena</span>
                                        <span class="table-cell">Piezīmes
                                            <!--{hook h='displayProductAttributesHeader' listing=$listing}--></span>
                                        <span class="table-cell availability sortable"
                                              data-filter=".product-price-and-shipping .dot"
                                              data-order="DESC"> </span>
                                    </div>
                                    <h4 class="custom_brand_name product_list_view" data-brand="59"
                                        style="display: none;">ANTARES</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="104"
                                        style="display: none;">AUSTONE</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="47"
                                        style="display: none;">BF GOODRICH</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="21"
                                        style="display: none;">BRIDGESTONE</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="9"
                                        style="display: none;">DUNLOP</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="3"
                                        style="display: none;">FALKEN</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="66"
                                        style="display: none;">FIREMAX</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="67"
                                        style="display: none;">FORTUNA</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="63"
                                        style="display: none;">GOODRIDE</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="2"
                                        style="display: none;">GOODYEAR</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="55"
                                        style="display: none;">GT RADIAL</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="40"
                                        style="display: none;">HANKOOK</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="113"
                                        style="display: none;">HILO</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="36"
                                        style="display: none;">KORMORAN</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="12"
                                        style="display: none;">KUMHO</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="114"
                                        style="display: none;">LEAO</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="7"
                                        style="display: none;">MICHELIN</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="45"
                                        style="display: none;">NEXEN</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="57"
                                        style="display: none;">NOKIAN</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="117"
                                        style="display: none;">OVATION</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="31"
                                        style="display: none;">ROTALLA</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="71"
                                        style="display: none;">ROUTEWAY</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="35"
                                        style="display: none;">ROVELO</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="92"
                                        style="display: none;">SAETTA</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="34"
                                        style="display: none;">SAILUN</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="87"
                                        style="display: none;">TRIANGLE</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="33"
                                        style="display: none;">WESTLAKE</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="72"
                                        style="display: none;">WINDFORCE</h4><h4
                                        class="custom_brand_name product_list_view" data-brand="73"
                                        style="display: none;">WINRUN</h4><h4
                                        class="custom_atv_name product_show_list" data-atv="205/55R16"
                                        style="display: none;">205/55 R16</h4>
                                    @foreach ($tires as $tire)
                                    <article class="product_show_list cat-14 product-miniature js-product-miniature"
                                             data-id-product="{{ $tire->tire_id }}" data-id-product-attribute="12324" itemscope=""
                                             itemtype="http://schema.org/Product" data-brand="{{ $tire->brand }}"
                                             data-atv="{{ $tire->d1 }}/{{ $tire->d2 }}R{{ $tire->d3 }}">
                                        <div class="thumbnail-container">
                                            <a href="http://r1riepas.lv/index.php?id_product=225&amp;id_product_attribute=12324&amp;rewrite=michelin-primacy-3&amp;controller=product&amp;id_lang=2#/2-autowidth-205/7-autoratio-55/11-autodiameter-16/19-externalnoise-712/23-autotype-ms/25-fuelefficiency-e/48-top40-top_40/80-autoloadindex-91/87-autospeedindex-h/366-autocode-rsc/1036-wetgrip-a"
                                               class="thumbnail product-thumbnail">
                                                <img src="{{ asset('img\p\en-default-home_default.jpg') }}">
                                            </a>
                                            <div class="product-description">
                                                <input type="checkbox" value="12324" name="product_ids[]">
                                                <h1 class="h3 product-title" itemprop="name">
                                                    <a data-toggle="tooltip" data-html="true"
                                                       title="<img src='{{ asset('img\p\en-default-home_default.jpg') }}'>"
                                                       href="http://r1riepas.lv/index.php?id_product=225&amp;id_product_attribute=12324&amp;rewrite=michelin-primacy-3&amp;controller=product&amp;id_lang=2#/2-autowidth-205/7-autoratio-55/11-autodiameter-16/19-externalnoise-712/23-autotype-ms/25-fuelefficiency-e/48-top40-top_40/80-autoloadindex-91/87-autospeedindex-h/366-autocode-rsc/1036-wetgrip-a"
                                                       data-content="{{ $tire->title }}">
                                                        <div class="product-title-hidden">{{ $tire->title }}</div>
                                                    </a>
                                                </h1>
                                                <div class="product-price-and-shipping" data-content="{{ $tire->title }}">
                                                    <span class="hidden-sm-down table-cell">
                                                        <span data-toggle="tooltip" title="<span style='color: black'>Kravnesības indekss: 91 – 615 kg</span>">{{ $tire->li }}</span>
                                                        <span data-toggle="tooltip" title="<span style='color: black'>H</span>">{{ $tire->si }}</span>
                                                    </span>
                                                    <span data-toggle="tooltip" title="<span style='color: black'>RSC – Runflat System Component (nulles spiediena riepa)</span>" class="hidden-sm-down table-cell prod-code">
                                                        {{ $tire->code }}
                                                    </span>
                                                    <span data-toggle="tooltip" title="<span style='color: black'>E</span>" class="hidden-sm-down table-cell fuel_efficiency">
                                                        E
                                                    </span>
                                                    <span class="hidden-sm-down table-cell wet_grip">
                                                        A
                                                    </span>
                                                    <span data-toggle="tooltip" title="<span style='color: black'>71(2)</span>" class="hidden-sm-down table-cell">
                                                        71(2)
                                                    </span>
                                                    <span class="sr-only">Veikala cena</span>
                                                    <span class="regular-price">€ {{ $tire->price1 }}</span>
                                                    <span class="sr-only">Akcijas cena</span>
                                                    <span itemprop="price" class="price">€ {{ $tire->price2 }}</span>
                                                    <span class="table-cell notes">
&nbsp;                                                      <span class="table-cell top40">Top 40</span>
                                                    </span>
                                                    <div class="clearfix atc_div">
                                                        <button class="btn grid-cart-btn btn-primary"
                                                                data-callback="showQuickBuyForm"
                                                                data-callback-param="12324"
                                                                data-popup-open="popup-2" data-backdrop="2"
                                                                data-show="1" data-toggle="modal"><i
                                                                class="material-icons">add_shopping_cart</i>
                                                        </button>
                                                        <span class="dot red" data-toggle="tooltip"
                                                              data-html="true"
                                                              title="<p>R1 Kopā:0</p><br/><p>Noliktava: 0</p><br/><p>Veikals: 0</p><br/><p>Lattako: 0</p><br/><p>Goodyear: 0</p><br/><p>Nokian: 0</p><br/><p>Kumho: 0</p><br/><p>Nevetas: 0</p><br/>">
                                                            <span class="sort-order">6</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                    @endforeach
                                </div>
                                <nav class="pagination">
                                    <div class="col-md-12">
                                    </div>
                                </nav>
                                <div class="hidden-md-up text-xs-right up">
                                    <a href="#header" class="btn btn-secondary">
                                        Back to top
                                        <i class="material-icons"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div id="js-product-list-bottom">
                            <div id="js-product-list-bottom"></div>
                        </div>
                    </section>
                </section>
            </div>
        </div>
        @include('components.right-sidebar')
    </div>
</div>

