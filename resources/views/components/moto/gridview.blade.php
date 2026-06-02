<a href="{{ route('motociklu-riepa', [strtolower(\Tires::getMotoTireBrand($tire->tread->brand_id)->title), strtolower(str_replace('/', '_', $tire->tread->title)), $tire->tire_id]) }}"
   class="grid-view-link"
   data-content="{{ $tire->fullName }}"
   data-article="{{ $tire->article }}"
   data-quantity="4">
  <div class="tire-image-card sort-order">
    <div class="text-center image-grid-overflow">
      {!! App\Helper\Image::showGrid('moto', $tire->make_id) !!}
    </div>

    <div class="tire-list-caption">

      <div class="card-title-text" data-toggle="tooltip" title="<div>{{$tire->title}}</div>">
        {{$tire->title}}
      </div>

      <div class="tire-tread">
        <b>{{$tire->d1}} / {{$tire->d2}} / {{$tire->d3}} </b>
        <span data-toggle="tooltip" title="<span style='color: black'>{{ $tire->lisiDesc($tire->li, $tire->si) }}</span>">{{ $tire->li . $tire->si }}</span>
        <span class="tire-image-code">{{$tire->code}}</span>
      </div>
      <div style="display: flex;">
        <input type="checkbox" name="product_ids[]" value="{{$tire->tire_id}}" style="margin-right: 5px;">
        <div class="rim-price-old" style="align-self: center;">€{{$tire->price1}}</div>
        <div class="rim-price-red" style="align-self: center;">€{{$tire->price2}}</div>

        <button style="margin-left: auto;" class="grid-buy-btn cart-shopping-button"
                data-toggle="modal"
                data-info="{{ $tire->tire_id }}"
                data-content="{{ $tire->fullName }}"
                data-article="{{ $tire->article }}"
                data-quantity="4"
                {{--                                      data-info="{{ $currTire->tire_id }}--}}
                onclick="event.preventDefault()"
                @hasrole('administrators')
        data-target="#"
        @else
          data-target="#blockcart-modal"
          @endhasrole>
          <i class="material-icons">add_shopping_cart</i>
          </button>

          <span class="grid-dot {{ $tire->dotAvailable }} {{ $tire->stockCount }}" data-toggle="tooltip"
                data-html="true"
                onclick="event.preventDefault()"
                title="{{ $tire->stockAvailability }}">
                              <span class="sort-order" style="display: none;">{{ $tire->dotAvailable }}</span>
                            </span>
      </div>
    </div>

  </div>
</a>
