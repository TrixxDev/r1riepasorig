<div class="container">
    <nav class="header-nav">
        <div class="row">
            <div class="hidden-sm-down">
                <div class="col-md-12 right-nav">

                    <div id="_desktop_user_info">
                        <div class="user-info">
                            @guest
                                <a href="{{ route('login') }}"
                                   title="Pierakstīties savā klienta kontā" rel="nofollow">
                                    <i class="material-icons"></i>
                                    <span class="hidden-sm-down">Ienākt</span>
                                </a>
                            @endguest
                            @auth
                                <a class="logout hidden-sm-down" href="{{ route('logout') }}" rel="nofollow">
                                    <i class="material-icons"></i>
                                    Iziet
                                </a>
                                <a class="account" href="{{ route('my-account') }}" title="Skatīt manu klienta kontu" data-role="{{ Auth::user()->getRoleNames() }}" data-user="{{ Auth::user()->fullName }}" rel="nofollow">
                                    <i class="material-icons hidden-md-up logged"></i>
                                    <span class="hidden-sm-down">{{ Auth::user()->fullName }}</span>
                                </a>
                            @endauth
                        </div>
                    </div>
                    <div id="_desktop_cart">
                        <div class="blockcart cart-preview @if ($cartCount > 0) active @else inactive @endif">
                            @if ($cartCount > 0)
                                <div class="header">
                                    <a rel="nofollow" href="{{ route('cart') }}">
                                        <i data-url="{{ route('cart') }}" class="desktop material-icons shopping-cart">shopping_cart</i>
                                        <span class="hidden-sm-down">Grozs:</span>
                                        <span class="cart-products-count">({{ $cartCount }})</span>
                                    </a>
                                </div>
                            @else
                                <div class="header">
                                    <i data-url="{{ route('cart') }}" class="desktop material-icons shopping-cart">shopping_cart</i>
                                    <span class="desktop hidden-sm-down">Grozs:</span>
                                    <span class="desktop cart-products-count">(0)</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="hidden-md-up text-sm-center mobile">
                <div class="float-xs-left" id="menu-icon">
                    <i class="material-icons d-inline"></i>
                </div>
                <div class="float-xs-right" id="_mobile_cart">
                    <div class="blockcart cart-preview @if (\Cart::count()) active @else inactive @endif"
                         data-refresh-url="//r1riepas.lv/index.php?fc=module&amp;module=ps_shoppingcart&amp;controller=ajax&amp;id_lang=2">
                        @if (\Cart::count() > 0)
                            <div class="header">
                                <a rel="nofollow" href="{{ route('cart') }}">
                                    <i class="material-icons shopping-cart">shopping_cart</i>
                                    <span class="hidden-sm-down">Grozs:</span>
                                    <span class="cart-products-count">({{ \Cart::count() }})</span>
                                </a>
                            </div>
                        @else
                            <div class="header">
                                <i class="mobile material-icons shopping-cart">shopping_cart</i>
                                <span class="mobile hidden-sm-down">Grozs:</span>
                                <span class="mobile cart-products-count">(0)</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="float-xs-right" id="_mobile_user_info">
                    <div class="user-info">
                        @auth
                            <a class="account" href="{{ route('my-account') }}" title="Skatīt manu klienta kontu" rel="nofollow">
                                <i class="material-icons hidden-md-up logged"></i>
                                <span class="hidden-sm-down">{{ Auth::user()->fullName }}</span>
                            </a>
                        @endauth
                        @guest
                            <a href="{{ route('login') }}" title="Pierakstīties savā klienta kontā" rel="nofollow">
                                <i class="material-icons"></i>
                                <span class="hidden-sm-down">Ienākt</span>
                            </a>
                        @endguest
                    </div>
                </div>
                <div class="top-logo" id="_mobile_logo">
                    <a href="/">
                        <img class="logo img-responsive" src="{{ asset('/img/r1-riepas-logo-1515661637.jpg') }}" alt="R1">
                    </a>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </nav>
</div>


{{--<div id="mobile_top_menu_wrapper" class="row hidden-md-up" style="display: none;">--}}
{{--    <div class="js-top-menu mobile" id="_mobile_top_menu"><ul class="top-menu" id="top-menu" data-depth="0">--}}
{{--            <li class="category">--}}
{{--                <a class="dropdown-item" href="#" data-depth="0">--}}
{{--                    <span class="float-xs-right hidden-md-up">--}}
{{--                        <span data-target="#top_sub_menu_1" data-toggle="collapse" class="navbar-toggler collapse-icons collapsed" aria-expanded="false">--}}
{{--                          <i class="material-icons add"></i>--}}
{{--                          <i class="material-icons remove"></i>--}}
{{--                        </span>--}}
{{--                    </span>--}}
{{--                    Riepas--}}
{{--                </a>--}}
{{--                <div class="popover sub-menu js-sub-menu collapse" id="top_sub_menu_1" aria-expanded="false" style="">--}}
{{--                    <ul class="top-menu" data-depth="1">--}}
{{--                        <li class="category">--}}
{{--                            <a class="dropdown-item dropdown-submenu" href="{{ route('ziemas-riepas') }}" data-depth="1">--}}
{{--                                Ziemas riepas--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="category">--}}
{{--                            <a class="dropdown-item dropdown-submenu" href="{{ route('vasaras-riepas') }}" data-depth="1">--}}
{{--                                Vasaras riepas--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="category">--}}
{{--                            <a class="dropdown-item dropdown-submenu" href="{{ route('kvadraciklu-riepas') }}" data-depth="1">--}}
{{--                                Kvadraciklu riepas--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="category">--}}
{{--                            <a class="dropdown-item dropdown-submenu" href="{{ route('motociklu-riepas') }}" data-depth="1">--}}
{{--                                Motociklu riepas--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}
{{--            </li>--}}
{{--            <li class="category">--}}
{{--                <a class="dropdown-item" href="#" data-depth="0">--}}

{{--                    <span class="float-xs-right hidden-md-up">--}}
{{--                        <span data-target="#top_sub_menu_2" data-toggle="collapse" class="navbar-toggler collapse-icons">--}}
{{--                          <i class="material-icons add"></i>--}}
{{--                          <i class="material-icons remove"></i>--}}
{{--                        </span>--}}
{{--                    </span>--}}
{{--                    Diski--}}
{{--                </a>--}}
{{--                <div class="popover sub-menu js-sub-menu collapse" id="top_sub_menu_2" style="">--}}
{{--                    <ul class="top-menu" data-depth="1">--}}
{{--                        <li class="category">--}}
{{--                            <a class="dropdown-item dropdown-submenu" href="{{ route('lietie-diski') }}" data-depth="1">--}}
{{--                                Jauni lietie diski--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="category">--}}
{{--                            <a class="dropdown-item dropdown-submenu" href="{{ route('kvadraciklu-diski') }}" data-depth="1">--}}
{{--                                Kvadru diski--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}
{{--            </li>--}}
{{--            <li class="category">--}}
{{--                <a class="dropdown-item" href="#" data-depth="0">--}}

{{--                    <span class="float-xs-right hidden-md-up">--}}
{{--                        <span data-target="#top_sub_menu_3" data-toggle="collapse" class="navbar-toggler collapse-icons">--}}
{{--                          <i class="material-icons add"></i>--}}
{{--                          <i class="material-icons remove"></i>--}}
{{--                        </span>--}}
{{--                    </span>--}}
{{--                    Serviss--}}
{{--                </a>--}}
{{--                <div class="popover sub-menu js-sub-menu collapse" id="top_sub_menu_3">--}}
{{--                    <ul class="top-menu" data-depth="1">--}}
{{--                        <li class="cms-page">--}}
{{--                            <a class="dropdown-item dropdown-submenu" href="{{ route('pieraksts') }}" data-depth="1">--}}
{{--                                E-pieraksts--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="cms-page">--}}
{{--                            <a class="dropdown-item dropdown-submenu" href="/pakalpojumi" data-depth="1">--}}
{{--                                Pakalpojumi--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="cms-page">--}}
{{--                            <a class="dropdown-item dropdown-submenu" href="{{ route('kondicionieris') }}" data-depth="1">--}}
{{--                                Kondicionieru uzpilde--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}
{{--            </li>--}}
{{--            <li class="category">--}}
{{--                <a class="dropdown-item" href="#" data-depth="0">--}}

{{--                    <span class="float-xs-right hidden-md-up">--}}
{{--                        <span data-target="#top_sub_menu_4" data-toggle="collapse" class="navbar-toggler collapse-icons">--}}
{{--                          <i class="material-icons add"></i>--}}
{{--                          <i class="material-icons remove"></i>--}}
{{--                        </span>--}}
{{--                    </span>--}}
{{--                    Info--}}
{{--                </a>--}}
{{--                <div class="popover sub-menu js-sub-menu collapse" id="top_sub_menu_4">--}}
{{--                    <ul class="top-menu" data-depth="1">--}}
{{--                        <li class="cms-page">--}}
{{--                            <a class="dropdown-item dropdown-submenu" href="{{ route('contacts') }}" data-depth="1">--}}
{{--                                Kontakti--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="cms-page">--}}
{{--                            <a class="dropdown-item dropdown-submenu" href="{{ route('terms') }}" data-depth="1">--}}
{{--                                Paskaidrojumi--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="cms-page">--}}
{{--                            <a class="dropdown-item dropdown-submenu" href="{{ route('about') }}" data-depth="1">--}}
{{--                                Par i-veikalu--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="cms-page">--}}
{{--                            <a class="dropdown-item dropdown-submenu" href="#" data-depth="1">--}}
{{--                                Moto pārvadājumi--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}
{{--            </li>--}}
{{--        </ul><div class="clearfix"></div></div>--}}
{{--    <div class="js-top-menu-bottom">--}}
{{--        <div id="_mobile_currency_selector"></div>--}}
{{--        <div id="_mobile_language_selector"><div class="language-selector-wrapper">--}}
{{--                <span id="language-selector-label" class="hidden-md-up">Valoda:</span>--}}
{{--                <div class="hidden-sm-down" aria-labelledby="language-selector-label">--}}
{{--                    <li style="display: inline-block;" @if (app()->getLocale() == 'en') class="current" @endif>--}}
{{--                        <a href="{{ route('lang', 'en') }}" class="list-item">EN</a>--}}
{{--                    </li>--}}
{{--                    <li style="display: inline-block;" @if (app()->getLocale() == 'lv') class="current" @endif>--}}
{{--                        <a href="{{ route('lang', 'lv') }}" class="list-item">LV</a>--}}
{{--                    </li>--}}
{{--                    <li style="display: inline-block;" @if (app()->getLocale() == 'ru') class="current" @endif>--}}
{{--                        <a href="{{ route('lang', 'ru') }}" class="list-item">RU</a>--}}
{{--                    </li>--}}
{{--                </div>--}}
{{--                <div class="language-selector dropdown js-dropdown">--}}
{{--                    <select class="link hidden-md-up" aria-labelledby="language-selector-label">--}}
{{--                        <option value="{{ route('lang', 'en') }}" @if (app()->getLocale() == 'en') selected="selected" @endif>EN</option>--}}
{{--                        <option value="{{ route('lang', 'lv') }}" @if (app()->getLocale() == 'lv') selected="selected" @endif>LV</option>--}}
{{--                        <option value="{{ route('lang', 'ru') }}" @if (app()->getLocale() == 'ru') selected="selected" @endif>RU</option>--}}
{{--                    </select>--}}
{{--                </div>--}}
{{--            </div></div>--}}
{{--        <div id="_mobile_contact_link"></div>--}}
{{--    </div>--}}
{{--</div>--}}

<div id="mobile_top_menu_wrapper" class="row hidden-md-up" style="display: none;">

    <a class="nav-dropdown-button navbar-item navbar-link-btn" href="{{ route('sale-tires') }}">Akcijas</a>

  <button class="nav-dropdown-button navbar-item" onclick="showRiepasDropdown()">Riepas <span class="material-icons riepas">keyboard_arrow_down</span></button>

    <div class="dropdown-options riepas">
        @if (config('site.season') === 2)
        <div class="nav-dropdown-link">
            <a class="dropdown-item" href="{{ route('ziemas-riepas') }}">Ziemas Riepas</a>
        </div>
        @else
        <div class="nav-dropdown-link">
            <a class="dropdown-item" href="{{ route('vasaras-riepas') }}">Vasaras Riepas</a>
        </div>
        @endif

      <div class="nav-dropdown-link">
        <a class="dropdown-item" href="{{ route('motociklu-riepas') }}">Motociklu Riepas</a>
      </div>

      <div class="nav-dropdown-link">
        <a class="dropdown-item" href="{{ route('kvadraciklu-riepas') }}">Kvadraciklu Riepas</a>
      </div>

        @if (config('site.season') === 2)
            <div class="nav-dropdown-link">
                <a class="dropdown-item" href="{{ route('vasaras-riepas') }}">Vasaras Riepas</a>
            </div>
        @else
            <div class="nav-dropdown-link">
                <a class="dropdown-item" href="{{ route('ziemas-riepas') }}">Ziemas Riepas</a>
            </div>
        @endif

      <div class="nav-dropdown-link">
        <a class="dropdown-item" href="{{ route('lielas-riepas') }}">Lielās riepas</a>
      </div>

      <div class="nav-dropdown-link">
        <a class="dropdown-item" href="{{ route('radzes') }}">Skrūvējamas radzes</a>
      </div>

    </div>

    <button class="nav-dropdown-button navbar-item" onclick="showDiskiDropdown()">Diski <span class="material-icons diski">keyboard_arrow_down</span></button>


    <div class="dropdown-options diski">
      <div class="nav-dropdown-link">
        <a class="dropdown-item" href="{{ route('lietie-diski') }}">Lietie Diski</a>
      </div>

{{--      <div class="nav-dropdown-link">--}}
{{--        <a class="dropdown-item" href="{{ route('kvadru-diski') }}">Kvadraciklu Diski</a>--}}
{{--      </div>--}}
    </div>

  <button class="nav-dropdown-button navbar-item" onclick="showServissDropdown()">Serviss <span class="material-icons serviss">keyboard_arrow_down</span></button>

  <a class="nav-dropdown-button navbar-item navbar-link-btn" href="/pakalpojumi">Pakalpojumi</a>

    <button class="nav-dropdown-button navbar-item" onclick="showInfoDropdown()">Info <span class="material-icons info">keyboard_arrow_down</span></button>

    <div class="dropdown-options info">
      <div class="dropdown-options">
        <div class="nav-dropdown-link">
          <a class="dropdown-item" href="{{ url('/kontakti') }}">Kontakti un darba laiks</a>
        </div>

        <div class="nav-dropdown-link">
          <a class="dropdown-item" href="{{ url('kondicionieris') }}">Kondicionieru Uzpilde</a>
        </div>

        <div class="nav-dropdown-link">
          <a class="dropdown-item" href="{{ route('terms') }}">Paskaidrojumi</a>
        </div>

        <div class="nav-dropdown-link">
          <a class="dropdown-item" href="{{ url('/internet-veikals')  }}">Par I-Veikalu</a>
        </div>
      </div>

      <div class="nav-dropdown-link">
        <a class="dropdown-item sizeCalc" href="{{ url('/kalkulators')  }}">Riepu izmēru kalkulators</a>
      </div>

      <div class="nav-dropdown-link">
        <a class="dropdown-item" href="{{ url('/riepu-atruma-indeksu-tabula')  }}">LI un SI indeksu tabula</a>
      </div>
    </div>

    <a class="nav-dropdown-button navbar-item navbar-link-btn" href="{{ route('pieraksts') }}">E-Pieraksts</a>

{{--    <div class="nav-dropdown-button">--}}
{{--      <a href="" class="nav-dropdown-btn-link">Serviss</a>--}}
{{--    </div>--}}
{{--    <div class="nav-dropdown-button">--}}
{{--      <a href="" class="nav-dropdown-btn-link">Info</a>--}}
{{--    </div>--}}

</div>



