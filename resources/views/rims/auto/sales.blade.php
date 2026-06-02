<section id="products">
    <div id="">
        <div id="js-product-list">
        <span class="text-uppercase flipped-title tire-brand-name" style="color:black;">Lietie diski</span>
        <div class="products row hide-price title-flip">
        @php
            $cbrand = '';
        @endphp
        @foreach ($rims as $rim)
        @php
          $brand = $rim->d3;
          $rim->includeStock = true;
        @endphp
        @if ($cbrand != $brand)
          @if ($cbrand !== '')
          </tbody>
        </table>
          @endif
          @php $cbrand = $brand; @endphp
          <h4 class="tire-brand-name">R{{ $brand }}</h4>
        <table id="tires-table" class="table table-striped rims-sorter tires-table table-hover tablesorter">
          <thead class="tires-thead sticky-top">
          <tr>
            <th scope="col"></th>
            <th scope="col" class="table-tire-name-cell">Nosaukums</th>
            <th scope="col" class="text-center">Izmērs</th>
            <th scope="col" class="hidden-sm-down text-center">Skrūvju attālums</th>
            <th scope="col" class="hidden-sm-down text-center">ET</th>
            <th scope="col" class="hidden-sm-down text-center">Centrs</th>
            <th scope="col" class="hidden-sm-down text-center">Krāsa</th>

            <th id="store-price-button" scope="col" class="text-center">Veikala cena</th>
            <th id="store-sale-button" scope="col" class="text-center">Akcijas cena</th>

            <th scope="col" class="hidden-sm-down text-center">Piezīmes</th>
            <th scope="col"></th>
            <th scope="col"
                data-toggle="tooltip"
                data-html="true"
                title="<span style='color: black'>Pieejamība</span>">
              <span class="tire-table-icon icon-question"></span>
            </th>

          </tr>
          </thead>
          <tbody id="tires-table-body">
        @endif
          <tr class="tire-table-row">
            <th scope="row" class="tire-table-checkbox">
              <input type="checkbox" value="{{$rim->rim_id}}" name="product_ids[]"
                     class="tire-table-checkbox">
            </th>

            <td class="table-tire-name-cell" data-link="{{ route('lietie-diski') }}">
              <a class="tire-table-link tippy image"
                 data-tippy-content="<div><img data-src='{!! App\Helper\Image::showAd('auto-rim', $rim->make_id) !!}'></div>"
                 href="{{ route('lietais-disks', [\Str::slug($rim->brandTitle), strtolower(str_replace('/', '_', $rim->treadTitle)), $rim->rim_id]) }}"
                 data-content="{{ $rim->fullName }}"
                 data-article="{{ $rim->article }}"
                 data-quantity="{{ $cartQty }}">
                <div class="table-link-title">{{ $rim->fullTitle }}</div>
              </a>
            </td>
            <td class="text-center">
              {{$rim->d1}}*{{$rim->d3}}
            </td>

            <td class="text-center hidden-sm-down">
              {{$rim->skr}}x{{$rim->pcd}}
            </td>

            <td class="text-center hidden-sm-down">
              {{ $rim->et }}
            </td>

            <td class="text-center hidden-sm-down">
              {{$rim->dc}}
            </td>

            <td class="hidden-sm-down text-center">
              {{ strtoupper($rim->color) }}
            </td>

            <td id="store-price" class="text-center store-price">€ {{$rim->price1}}</td>
            <td id="sale-price" class="text-center tire-price-red sale-price">€ {{$rim->price2}}</td>
            <td class="hidden-sm-down text-center @if($rim->comment == 'Izpārdošana!' || $rim->priceoffer == 1) sellout @endif">{{ $rim->comment }}</td>

            <td class="shopping-cart-col">
              <div class="clearfix atc_div text-right">
                <button class="cart-shopping-button" data-toggle="modal"
                        @if (Auth::check() && Auth::user()->hasRole('administrators')) data-target="#" @else data-target="#blockcart-modal"
                        @endif data-info="{{ $rim->rim_id }}"><i
                      class="material-icons">add_shopping_cart</i>
                </button>
              </div>
            </td>

            <td class="dot-availability text-center">
                                  <span class="dot {{ $rim->dotAvailable }} {{ $rim->stockCount }}" data-toggle="tooltip"
                                        data-html="true"
                                        title="{{ $rim->stockAvailability }}">
                                    <span class="sort-order">{{ $rim->dotAvailable }}</span>
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
    </div>
</section>
