@if (config('site.season') === 1)
  <!-- SUMMER TYRES -->
  @include('tires.auto.seasonsales.summer')
  <!-- END SUMMER TYRES -->
  <!-- WINTER TYRES -->
  @include('tires.auto.seasonsales.winter')
  <!-- END WINTER TYRES -->
@else
  <!-- WINTER TYRES -->
  @include('tires.auto.seasonsales.winter')
  <!-- END WINTER TYRES -->
  <!-- SUMMER TYRES -->
  @include('tires.auto.seasonsales.summer')
  <!-- END SUMMER TYRES -->
@endif







{{--<section id="products">--}}
{{--  <div id="">--}}
{{--    <div id="js-product-list">--}}
{{--      <div class="products row hide-price title-flip">--}}

{{--        @php--}}
{{--          $cbrand = '';--}}
{{--          $index = 0;--}}
{{--        @endphp--}}
{{--        @foreach ($tires as $tire)--}}
{{--          @php--}}
{{--            if ($tire->season == 1) {--}}
{{--              $current_url = 'vasaras-riepa';--}}
{{--            } else {--}}
{{--              $current_url = 'ziemas-riepa';--}}
{{--            }--}}
{{--            $brand = $tire->fullSize;--}}
{{--            $tire->includeStock = true;--}}
{{--            if ($cbrand!=$brand){--}}

{{--            if ($cbrand) {--}}
{{--               echo '<h4 class="tire-brand-name">' . $cbrand;--}}
{{--            }--}}
{{--            if ($index == 0){--}}
{{--              switch ($tire->season){--}}
{{--              case 1:--}}
{{--                echo '<span class="text-uppercase flipped-title tire-brand-name" style="color:black;">Vasaras riepas</span>';--}}
{{--                break;--}}
{{--              case 2:--}}
{{--                echo '<span class="text-uppercase flipped-title tire-brand-name" style="color:black;">Ziemas riepas</span>';--}}
{{--                break;--}}
{{--              }--}}
{{--              echo '</h4>';--}}
{{--            }--}}
{{--            echo '';--}}

{{--            $cbrand = $brand;--}}
{{--            $stripe = 1;--}}
{{--          @endphp--}}
{{--          <table id="tires-table"--}}
{{--                 class="table table-striped summer-sorter tires-table table-hover tablesorter">--}}
{{--            <thead class="tires-thead sticky-table">--}}
{{--            <tr>--}}
{{--              <th scope="col"></th>--}}
{{--              <th scope="col" class="table-tire-name-cell">Brends / modelis</th>--}}
{{--              <th scope="col" class="hidden-sm-down text-center">LI/SI</th>--}}
{{--              @if ($tire->season == 2)--}}
{{--                <th scope="col" class="hidden-sm-down text-center">Tips</th>--}}
{{--              @endif--}}
{{--              <th scope="col" class="hidden-sm-down text-center">Kods</th>--}}

{{--              <th scope="col" class="hidden-sm-down">--}}
{{--                <div class="tire-table-icon icon-tire-fuel" title="Degvielas ekonomija"></div>--}}
{{--              </th>--}}

{{--              <th scope="col" class="hidden-sm-down">--}}
{{--                <div class="tire-table-icon icon-tire-rain" title="Slapjš segums"></div>--}}
{{--              </th>--}}

{{--              <th scope="col" class="hidden-sm-down">--}}
{{--                <div class="tire-table-icon icon-tire-sound" title="Troksnis"></div>--}}
{{--              </th>--}}

{{--              <th id="store-price-button" scope="col" class="text-center">--}}
{{--                Veikala cena--}}
{{--              </th>--}}

{{--              <th id="store-sale-button" scope="col" class="text-center">Akcijas cena</th>--}}
{{--              <th scope="col" class="hidden-sm-down text-center">Piezīmes</th>--}}
{{--              <th scope="col"></th>--}}
{{--              <th scope="col">--}}
{{--                <div class="tire-table-icon icon-question" title="Pieejamība" data-toggle="tooltip"></div>--}}
{{--              </th>--}}

{{--            </tr>--}}
{{--            </thead>--}}
{{--            <tbody id="tires-table-body">--}}
{{--            @php--}}
{{--              $cbrand = $brand;--}}
{{--              $stripe = 1;--}}
{{--          }--}}
{{--            @endphp--}}
{{--            @if ($loop->last) <h4 class="tire-brand-name">{{ $brand }}</h4> @endif--}}
{{--            <tr class="tire-table-row">--}}
{{--              <th scope="row" class="tire-table-checkbox">--}}
{{--                <input type="checkbox" value="{{ $tire->tire_id }}" name="product_ids[]"--}}
{{--                       class="tire-table-checkbox">--}}
{{--              </th>--}}

{{--              <td class="table-tire-name-cell">--}}
{{--                <a data-toggle="tooltip" data-html="true" class="tire-table-link"--}}
{{--                   title='{!! App\Helper\Image::show('auto', $tire->make_id) !!}'--}}
{{--                   href="{{ route($current_url, [\Str::slug(\Tires::getAutoTireBrand($tire->brand_id)->title), strtolower(str_replace('/', '_', $tire->t_title)), $tire->tire_id]) }}"--}}
{{--                   data-content="{{ $tire->fullName }}"--}}
{{--                   data-article="{{ $tire->article }}"--}}
{{--                   data-quantity="{{ (new \App\Http\Controllers\AutoTireController(new \Illuminate\Http\Request()))->cartQty }}">--}}
{{--                  <div class="table-link-title">{{ $tire->title }}</div>--}}
{{--                </a>--}}
{{--              </td>--}}

{{--              <td class="hidden-sm-down text-center">--}}
{{--                              <span data-toggle="tooltip"--}}
{{--                                    title="<span style='color: black'>{{ $tire->lisiDesc($tire->li, $tire->si) }}</span>">{{ $tire->li . $tire->si }}--}}
{{--                              </span>--}}
{{--              </td>--}}

{{--              @if ($tire->season == 2)--}}
{{--                <td scope="col" class="hidden-sm-down text-center">--}}

{{--                  @switch($tire->type)--}}
{{--                    @case(1)--}}
{{--                    <span data-toggle="tooltip">--}}
{{--                                  <img src="{{asset('images/ms.png')}}" alt="ms"--}}
{{--                                       title="<span>Centrāleiropas tipa ziemas riepa</span>" style="margin:0;">--}}
{{--                                </span>--}}

{{--                    @break--}}

{{--                    @case(2)--}}
{{--                    <span data-toggle="tooltip">--}}
{{--                                  <img src="{{asset('images/radzeb.png')}}" alt="radzojama"--}}
{{--                                       title="<span>Radžojama</span>" style="margin:0;">--}}
{{--                                </span>--}}

{{--                    @break--}}

{{--                    @case(3)--}}
{{--                    <span data-toggle="tooltip">--}}
{{--                                  <img src="{{asset('images/radzea.png')}}" alt="ar radzem"--}}
{{--                                       title="<span>Ar radzēm</span>" style="margin:0;">--}}
{{--                                </span>--}}

{{--                    @break--}}

{{--                    @case(4)--}}
{{--                    <span data-toggle="tooltip">--}}
{{--                                  <img src="{{asset('images/parsla.png')}}" alt="skandinavijas"--}}
{{--                                       title="<span>Skandināvijas tipa ziemas riepa</span>" style="margin:0;">--}}
{{--                                </span>--}}
{{--                    @break--}}

{{--                  @endswitch--}}

{{--                </td>--}}
{{--              @endif--}}

{{--              <td class="hidden-sm-down text-center">--}}
{{--                            <span data-toggle="tooltip" title="<span style='color: black'>--}}
{{--				                    @php $codes = explode(' ', $tire->code); @endphp--}}
{{--                            @foreach ($codes as $code1)--}}
{{--                            @if (isset($code_array[$code1]))--}}
{{--                            {!! $code_array[$code1] . '<br>' !!}--}}
{{--                            @endif--}}
{{--                            @endforeach--}}
{{--                            @if (strpos($tire->code, 'DOT') !== false)--}}
{{--                            {!! $code_array['DOT'] !!}--}}
{{--                            @endif--}}
{{--                                </span>" class="hidden-sm-down table-cell prod-code">{{ $tire->code }}</span>--}}
{{--              </td>--}}

{{--              <td class="hidden-sm-down text-center">--}}
{{--                            <span data-toggle="tooltip"--}}
{{--                                  title="<span style='color: black'>{{ $tire->eco }}</span>">{{ $tire->eco }}</span>--}}
{{--              </td>--}}

{{--              <td class="hidden-sm-down text-center">--}}
{{--                            <span data-toggle="tooltip"--}}
{{--                                  title="<span style='color: black'>{{ $tire->wet }}</span>">{{ $tire->wet }}</span>--}}
{{--              </td>--}}

{{--              <td class="hidden-sm-down text-center">--}}
{{--                            <span data-toggle="tooltip"--}}
{{--                                  title="<span style='color: black'>{{ $tire->noise }}</span>">{{ $tire->noise }}</span>--}}
{{--              </td>--}}

{{--              <td id="store-price" class="text-center store-price">€ {{ $tire->price1 }}</td>--}}
{{--              <td id="sale-price" class="text-center tire-price-red sale-price">€ {{ $tire->price2 }}</td>--}}
{{--              <td class="hidden-sm-down text-center @if($tire->comment == 'Izpārdošana!' || $tire->priceoffer == 1){{ 'sellout' }}@endif">{{$tire->comment}}</td>--}}

{{--              <td class="shopping-cart-col">--}}
{{--                <div class="clearfix atc_div text-right">--}}
{{--                  <button class="cart-shopping-button" data-toggle="modal"--}}
{{--                          @hasrole('administrators') data-target="#" @else data-target="#blockcart-modal" @endhasrole data-info="{{ $tire->tire_id }}"><i--}}
{{--                      class="material-icons">add_shopping_cart</i>--}}
{{--                  </button>--}}
{{--                </div>--}}
{{--              </td>--}}

{{--              <td class="dot-availability text-center">--}}
{{--                            <span class="dot {{ $tire->dotAvailable }} {{ $tire->stockCount }}" data-toggle="tooltip"--}}
{{--                                  data-html="true"--}}
{{--                                  title="{{ $tire->stockAvailability }}">--}}
{{--                              <span class="sort-order">{{ $tire->dotAvailable }}</span>--}}
{{--                            </span>--}}
{{--              </td>--}}

{{--            </tr>--}}
{{--            @php--}}
{{--              $index++;--}}
{{--            @endphp--}}
{{--            @endforeach--}}
{{--            </tbody>--}}
{{--          </table>--}}
{{--      </div>--}}
{{--    </div>--}}
{{--  </div>--}}
{{--</section>--}}