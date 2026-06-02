@php
$count = 0;
$count++;
@endphp
<tr class="tire-table-row">
  <th scope="row" class="tire-table-checkbox">
    <input type="checkbox" value="{{ $tire->tire_id }}" name="product_ids[]"
           class="tire-table-checkbox">
  </th>

  <td class="table-tire-name-cell">
    <a data-toggle="tooltip" data-html="true" class="tire-table-link"
       title='{!! App\Helper\Image::show('quadr', $tire->make_id) !!}'
       href="{{ route('kvadraciklu-riepa', [strtolower(\Tires::getQuadrTireBrand($tire->tread->brand_id)->title), strtolower(str_replace('/', '_', $tire->tread->title)), $tire->tire_id]) }}"
       data-content="{{ $tire->fullName }}" data-article="{{ $tire->article }}">
      {{ $tire->title }}
    </a>
  </td>

  {{--                          <td class="hidden-sm-down text-center">--}}
  {{--                            <span data-toggle="tooltip"--}}
  {{--                                  title="<span style='color: black'>RSC – Runflat System Component (nulles spiediena riepa)</span>"--}}
  {{--                                  class="hidden-sm-down table-cell prod-code">{{ $tire->code }}</span>--}}
  {{--                          </td>--}}

  {{--                          <td class="hidden-sm-down text-center">--}}
  {{--                            <span data-toggle="tooltip"--}}
  {{--                                  title="<span style='color: black'>{{ $tire->eco }}</span>">{{ $tire->eco }}</span>--}}
  {{--                          </td>--}}

  {{--                          <td class="hidden-sm-down text-center">--}}
  {{--                            <span data-toggle="tooltip"--}}
  {{--                                  title="<span style='color: black'>{{ $tire->wet }}</span>">{{ $tire->wet }}</span>--}}
  {{--                          </td>--}}

  {{--                          <td class="hidden-sm-down text-center">--}}
  {{--                            <span data-toggle="tooltip"--}}
  {{--                                  title="<span style='color: black'>{{ $tire->noise }}</span>">{{ $tire->noise }}</span>--}}
  {{--                          </td>--}}
  <td class="text-center">{{$tire->pr}}</td>
  <td id="store-price" class="text-center store-price">€ {{ $tire->price1 }}</td>
  <td id="sale-price" class="text-center tire-price-red sale-price">€ {{ $tire->price2 }}</td>
  <td class="hidden-sm-down text-center"></td>

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
                            <span class="dot {{ $tire->dotAvailable }}" data-toggle="tooltip"
                                  data-html="true"
                                  title="{{ $tire->stockAvailability }}">
                            <span class="sort-order">{{ $tire->dotAvailable }}</span>
                            </span>
  </td>

</tr>
