<section id="products">
  <div id="">
    <div id="js-product-list">
      <span class="text-uppercase flipped-title tire-brand-name" style="color:black;">Motociklu riepas</span>
      <div class="products row hide-price title-flip">

        @php
          $cbrand = '';
        @endphp
        @foreach ($tires as $tire)
          @if (empty($tire->sale_title))
            @continue
          @endif
          @php
            $brand = $tire->fullSize;
          @endphp
          @if ($cbrand != $brand)
            @if ($cbrand !== '')
              </tbody>
            </table>
            @endif
            @php $cbrand = $brand; @endphp
            <h4 class="tire-brand-name">{{ $brand }}</h4>
          <table id="tires-table" class="table table-striped moto-sorter tires-table table-hover tablesorter">
            <thead class="tires-thead sticky-table">
            <tr>
              <th scope="col"></th>
              <th scope="col" class="table-tire-name-cell">Brends / modelis</th>
              <th scope="col" class="hidden-sm-down text-center">Tips</th>
              <th scope="col" class="hidden-sm-down text-center">LI/SI</th>
              <th scope="col" class="hidden-sm-down text-center">Kods</th>

              <th id="store-price-button" scope="col" class="text-center">Veikala cena</th>
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

              <td class="table-tire-name-cell" data-link="{{ route('motociklu-riepas') }}">
                <a class="tire-table-link tippy image"
                   data-tippy-content="<div><img data-src='{{ App\Helper\Image::showAd('moto', $tire->make_id) }}'></div>"
                   href="{{ route('motociklu-riepa', [$tire->brand_slug, $tire->tread_slug, $tire->tire_id]) }}"
                   data-content="{{ $tire->sale_full_name }}" data-article="{{ $tire->article }}" data-quantity="{{ $cartQty }}">
                  <div class="table-link-title">{{ $tire->sale_title }}</div>
                </a>
              </td>

              <td class="hidden-sm-down text-center">
                          <span data-toggle="tooltip"
                                title="<span style='color: black'>{{ $tire->sale_type_desc }}</span>">{{ $tire->sale_moto_type }}
                              </span>
              </td>

              <td class="hidden-sm-down text-center">
                          <span data-toggle="tooltip"
                                title="<span style='color: black'>{{ $tire->sale_lisi_desc }}</span>">{{ $tire->li . $tire->si }}
                          </span>
              </td>

              <td class="hidden-sm-down text-center">
                            <span data-toggle="tooltip" title="{!! $tire->code_explain !!}" class="hidden-sm-down table-cell prod-code">{{ $tire->code }}
                                    </span>

              </td>

              <td id="store-price" class="text-center store-price">€ {{ $tire->price1 }}</td>
              <td id="sale-price" class="text-center tire-price-red sellout">€ {{ $tire->price2 }}</td>
              <td class="hidden-sm-down text-center sellout">{{$tire->comment}}</td>

              <td class="shopping-cart-col">
                <div class="clearfix atc_div text-right">
                  <button class="cart-shopping-button" data-toggle="modal"
                          @if (Auth::user()) data-target="#" @else data-target="#blockcart-modal"
                          @endif data-info="{{ $tire->tire_id }}"><i
                        class="material-icons">add_shopping_cart</i>
                  </button>
                </div>
              </td>

              <td class="dot-availability text-center">

                            <span class="dot {{ $tire->sale_dot_available }}" data-toggle="tooltip"
                                  data-html="true"
                                  title="{{ $tire->sale_stock_availability }}">
                              <span class="sort-order">{{ $tire->sale_dot_available }}</span>
                            </span>
              </td>

            </tr>
            @endforeach
            @if ($cbrand !== '')
            </tbody>
          </table>
            @endif
      </div>
    </div>
</section>
