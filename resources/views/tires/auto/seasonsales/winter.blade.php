<section id="products">
    <div id="">
        <div id="js-product-list">
            <span class="text-uppercase flipped-title tire-brand-name" style="color:black;">Ziemas riepas</span>
            <div class="products row hide-price title-flip">

                @php
                    $cbrand = '';
                @endphp
                @foreach ($tires as $tire)
                    @php
                        $brand = $tire->fullSize;
                        $tire->includeStock = true;
                    @endphp
                    @if ($cbrand != $brand)
                        @if ($cbrand !== '')
                            </tbody>
                        </table>
                        @endif
                        @php $cbrand = $brand; @endphp
                        <h4 class="tire-brand-name">{{ $brand }}</h4>
                        <table id="tires-table"
                               class="table table-striped winter-sorter tires-table table-hover tablesorter">
                            <thead class="tires-thead sticky-table">
                            <tr>
                                <th scope="col"></th>
                                <th scope="col" class="table-tire-name-cell">Brends / modelis</th>
                                <th scope="col" class="hidden-sm-down text-center">LI/SI</th>
                                <th scope="col" class="hidden-sm-down text-center">Tips</th>
                                <th scope="col" class="hidden-sm-down text-center">Kods</th>

                                <th scope="col" class="hidden-sm-down">
                                    <div class="tire-table-icon icon-tire-fuel" title="Degvielas ekonomija"></div>
                                </th>

                                <th scope="col" class="hidden-sm-down">
                                    <div class="tire-table-icon icon-tire-rain" title="Slapjš segums"></div>
                                </th>

                                <th scope="col" class="hidden-sm-down">
                                    <div class="tire-table-icon icon-tire-sound" title="Troksnis"></div>
                                </th>

                                <th id="store-price-button" scope="col" class="text-center">
                                    Veikala cena
                                </th>

                                <th id="store-sale-button" scope="col" class="text-center">Akcijas cena</th>
                                <th scope="col" class="hidden-sm-down text-center">Piezīmes</th>
                                <th scope="col"></th>
                                <th scope="col">
                                    <div class="tire-table-icon icon-question" title="Pieejamība" data-toggle="tooltip"></div>
                                </th>

                            </tr>
                            </thead>
                            <tbody id="tires-table-body">
                    @endif
                    <tr class="tire-table-row">
                        <th scope="row" class="tire-table-checkbox">
                            <input type="checkbox" value="{{ $tire->tire_id }}" name="product_ids[]"
                                   class="tire-table-checkbox">
                        </th>

                        <td class="table-tire-name-cell" data-link="{{ route('ziemas-riepas') }}">
                            <a class="tire-table-link tippy image"
                               data-tippy-content="<div><img data-src='{{ App\Helper\Image::showAd('auto', $tire->make_id) }}'></div>"
                               href="{{ route($winterURL, [$tire->brand_slug, $tire->tread_slug, $tire->tire_id]) }}"
                               data-content="{{ $tire->sale_full_name }}"
                               data-article="{{ $tire->article }}"
                               data-quantity="4">
                              <div class="table-link-title">{{ $tire->sale_title }}</div>
                            </a>
                        </td>

                        <td class="hidden-sm-down text-center">
                          <span data-toggle="tooltip"
                                title="<span style='color: black'>{{ $tire->lisiDesc($tire->li, $tire->si) }}</span>">{{ $tire->li . $tire->si }}
                          </span>
                        </td>

                        <td scope="col" class="hidden-sm-down text-center">

                            @switch($tire->type)
                                @case(1)
                                <span data-toggle="tooltip">
                                  <img src="{{asset('images/ms.png')}}" alt="ms"
                                       title="<span>Centrāleiropas tipa ziemas riepa</span>" style="margin:0;">
                                </span>

                                @break

                                @case(2)
                                <span data-toggle="tooltip">
                                  <img src="{{asset('images/radzeb.png')}}" alt="radzojama"
                                       title="<span>Radžojama</span>" style="margin:0;">
                                </span>

                                @break

                                @case(3)
                                <span data-toggle="tooltip">
                                  <img src="{{asset('images/radzea.png')}}" alt="ar radzem"
                                       title="<span>Ar radzēm</span>" style="margin:0;">
                                </span>

                                @break

                                @case(4)
                                <span data-toggle="tooltip">
                                  <img src="{{asset('images/parsla.png')}}" alt="skandinavijas"
                                       title="<span>Skandināvijas tipa ziemas riepa</span>" style="margin:0;">
                                </span>
                                @break

                            @endswitch

                        </td>

                        <td class="hidden-sm-down text-center">
                          <span data-toggle="tooltip"
                                title="<span style='color: black'>{!! $tire->code_explain !!}</span>"
                                class="hidden-sm-down table-cell prod-code">
                              {{ $tire->code }}
                          </span>
                        </td>

                        <td class="hidden-sm-down text-center">
                        <span data-toggle="tooltip"
                              title="<span style='color: black'>{{ $tire->eco }}</span>">{{ $tire->eco }}</span>
                        </td>

                        <td class="hidden-sm-down text-center">
                        <span data-toggle="tooltip"
                              title="<span style='color: black'>{{ $tire->wet }}</span>">{{ $tire->wet }}</span>
                        </td>

                        <td class="hidden-sm-down text-center">
                        <span data-toggle="tooltip"
                              title="<span style='color: black'>{{ $tire->noise }}</span>">{{ $tire->noise }}</span>
                        </td>

                        <td id="store-price" class="text-center store-price">€ {{ $tire->price1 }}</td>
                        <td id="sale-price" class="text-center tire-price-red sellout">€ {{ $tire->price2 }}</td>
                        <td class="hidden-sm-down text-center sellout">{{$tire->comment}}</td>

                        <td class="shopping-cart-col">
                            <div class="clearfix atc_div text-right">
                                <button class="cart-shopping-button" data-toggle="modal"
                                        @hasrole('administrators') data-target="#" @else data-target="#blockcart-modal" @endhasrole data-info="{{ $tire->tire_id }}"><i
                                        class="material-icons">add_shopping_cart</i>
                                </button>
                            </div>
                        </td>

                        <td class="dot-availability text-center">
                            <span class="tippy lisi-tooltip dot {{ $tire->sale_dot_available }}" data-tippy-content='<div style="padding: 5px; text-align: left;"><span style="color: black; font-size: 15px; line-height: 28px;">{{ $tire->sale_stock_availability }}</span></div>'></span>
                        </td>

                    </tr>
                @endforeach
                @if ($cbrand !== '')
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</section>
