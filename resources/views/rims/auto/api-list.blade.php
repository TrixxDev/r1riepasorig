@php
  $cbrand = '';
@endphp
@if($rims->isNotEmpty())
  <button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal">
    Filtrs
  </button>
@endif
@foreach ($rims as $rim)
@php
  $brand = $rim->d3;
  if ($cbrand !== $brand) {
    if ($cbrand !== '') {
@endphp
          </tbody>
        </table>
@php
    }
    $cbrand = $brand;
@endphp
        <table id="tires-table" class="table table-striped rims-sorter tires-table table-hover tablesorter">
          <thead class="tires-thead sticky-top">
          <tr>
            <th scope="col"></th>
            <th scope="col">Nosaukums</th>
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
          <h4 class="tire-brand-name"><span class="text-uppercase tire-brand-name" style="color:black;">Lietie diski</span> R{{ $brand }} </h4>
@php
  }
@endphp
          <tr class="tire-table-row" role="row">
            <th scope="row" class="tire-table-checkbox">
              <input type="checkbox" value="{{ $rim->rim_id }}" name="product_ids[]" class="tire-table-checkbox" title="">
            </th>
            <td class="table-tire-name-cell">
              <a class="tire-table-link tippy image"
                 data-tippy-content="<div><img data-src='https://r1riepas.lv/storage/rims/tread/{{ $rim->make_id }}-o.jpg'></div>"
                 href="{{ $rim->getUrl }}"
                 data-content="{{ $rim->fullName }}"
                 data-article="{{ $rim->article }}"
                 data-quantity="{{ $cartQty }}">
                <div class="table-link-title">{{ $rim->brandTitle }} {{ $rim->treadTitle }}</div>
              </a>
            </td>
            <td class="text-center">{{ $rim->d1 }}*{{ $rim->d3 }}</td>
            <td class="text-center hidden-sm-down">{{ $rim->skr }}x{{ $rim->pcd }}</td>
            <td class="text-center hidden-sm-down">{{ $rim->et }}</td>
            <td class="text-center hidden-sm-down">{{ $rim->dc }}</td>
            <td class="text-center hidden-sm-down">{{ strtoupper($rim->color) }}</td>
            <td id="store-price" class="text-center store-price">€ {{ $rim->price1 }}</td>
            <td id="sale-price" class="text-center tire-price-red sale-price">€ {{ $rim->price2 }}</td>
            <td class="hidden-sm-down text-center @if($rim->comment == 'Izpārdošana!' || $rim->priceoffer == 1) sellout @endif">{{ $rim->comment }}</td>
            <td class="shopping-cart-col">
              <div class="clearfix atc_div text-right">
                <button class="cart-shopping-button"
                        data-toggle="modal"
                        @if (Auth::check() && Auth::user()->hasRole('administrators')) data-target="#" @else data-target="#blockcart-modal" @endif
                        data-info="{{ $rim->rim_id }}"
                        data-url="{{ $rim->getUrl }}">
                  <i class="material-icons">add_shopping_cart</i>
                </button>
              </div>
            </td>
            <td class="dot-availability text-center">
              <span class="tippy lisi-tooltip dot {{ $rim->dotAvailable }}"
                    data-color="{{ $rim->dotAvailable }}"
                    data-tippy-content='{!! '<div style="padding: 5px; text-align: left;"><span style="color: black; font-size: 15px; line-height: 28px;">' . str_replace("'", '&#39;', $rim->stockAvailability) . '</span></div>' !!}'>
                <span class="sort-order">{{ $rim->dotAvailable }}</span>
              </span>
            </td>
          </tr>
@endforeach
@if($cbrand !== '')
          </tbody>
        </table>
@endif
