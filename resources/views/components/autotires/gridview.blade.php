<a
  href="{{ route($current_url, [\Str::slug(\Tires::getAutoTireBrand($tire->brand_id)->title), strtolower(str_replace('/', '_', $tire->t_title)), $tire->tire_id]) }}"
  class="grid-view-link"
  data-article="{{ $tire->article }}">
  <div class="tire-image-card sort-order">
    <div class="text-center image-grid-overflow">
      {!! App\Helper\Image::showGrid('auto', $tire->make_id) !!}
    </div>

    <div class="tire-list-caption">

      <div class="card-title-text" data-toggle="tooltip" title="<div>{{$tire->title}}</div>">
        {{$tire->title}}
      </div>

      <div class="tire-tread">
        <b>{{$tire->d1}} / {{$tire->d2}} / {{$tire->d3}} </b>
        <span data-toggle="tooltip"
              title="<span style='color: black'>{{ $tire->lisiDesc($tire->li, $tire->si) }}</span>">{{ $tire->li . $tire->si }}</span>
        <span class="tire-image-code">{{$tire->code}}</span>
      </div>
      <div class="grid-card-footer">
        <input type="checkbox" name="product_ids[]" value="{{$tire->tire_id}}" class="grid-card-checkbox">
        <div class="grid-card-prices">
          <div class="rim-price-old">€{{$tire->price1}}</div>
          <div class="rim-price-red">€{{$tire->price2}}</div>
        </div>
        <div class="grid-card-actions">
          <span class="grid-card-cart-wrap" data-toggle="tooltip"
                title="<span style='color: black'>Pievienot grozam</span>">
            <button class="grid-buy-btn cart-shopping-button"
                    data-toggle="modal"
                    data-info="{{ $tire->tire_id }}"
                    onclick="event.preventDefault()"
                    @hasrole('administrators')
                      data-target="#"
                    @else
                      data-target="#blockcart-modal"
                    @endhasrole>
              <i class="material-icons">add_shopping_cart</i>
            </button>
          </span>
          <span class="grid-dot {{ $tire->dotAvailable }} {{ $tire->stockCount }}"
                data-toggle="tooltip"
                data-html="true"
                onclick="event.preventDefault()"
                title="{{ $tire->stockAvailability }}">
            <span class="sort-order" style="display: none;">{{ $tire->dotAvailable }}</span>
          </span>
        </div>
      </div>
    </div>
    {{--                        <button class="grid-shopping-button grid-cart-btn" data-toggle="modal" data-target="#blockcart-modal" data-info="148204">Pirkt--}}
    {{--                        </button>--}}


  </div>
</a>
