<tr class="tire-table-row">
  <th scope="row" class="tire-table-checkbox">
    <input type="checkbox" value="{{ $tire->tire_id }}" name="product_ids[]"
           class="tire-table-checkbox">
  </th>

  <td class="table-tire-name-cell">
    <a data-toggle="tooltip" data-html="true" class="tire-table-link"
       title='{!! App\Helper\Image::show('moto', $tire->make_id) !!}'
       href="{{ route('motociklu-riepa', [strtolower(\Tires::getMotoTireBrand($tire->tread->brand_id)->title), strtolower(str_replace('/', '_', $tire->tread->title)), $tire->tire_id]) }}"
       data-content="{{ $tire->fullName }}" data-article="{{ $tire->article }}" data-quantity="4">
      {{ $tire->title }}
    </a>
  </td>

  <td class="hidden-sm-down text-center">
                          <span data-toggle="tooltip"
                                title="<span style='color: black'>@if (isset($tire->typeDesc[1])) {{ $tire->typeDesc[1] }} @endif</span>">{{ $tire->motoType }}
                              </span>
  </td>

  <td class="hidden-sm-down text-center">
                          <span data-toggle="tooltip"
                                title="<span style='color: black'>{{ $tire->lisiDesc($tire->li, $tire->si) }}</span>">{{ $tire->li . $tire->si }}
                          </span>
  </td>

  <td class="hidden-sm-down text-center">
                            <span data-toggle="tooltip" title="<span style='color: black'>
                                                @php $codes = explode(' ', $tire->code); @endphp
                            @foreach ($codes as $code)
                            @if (isset($code_array[$code]))
                            {!! $code_array[$code] . '<br>' !!}
                            @endif
                            @endforeach
                            @if (strpos($tire->code, 'DOT') !== false)
                            {!! $code_array['DOT'] !!}
                            @endif
                              </span>" class="hidden-sm-down table-cell prod-code">{{ $tire->code }}
                                    </span>

  </td>

  <td id="store-price" class="text-center store-price">€ {{ $tire->price1 }}</td>
  <td id="sale-price" class="text-center tire-price-red">€ {{ $tire->price2 }}</td>
  <td class="hidden-sm-down">{{ $tire->comment }}</td>

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
