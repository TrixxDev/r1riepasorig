<a href="{{ route('kvadraciklu-riepa', [strtolower(\Tires::getQuadrTireBrand($tire->tread->brand_id)->title), strtolower(str_replace('/', '_', $tire->tread->title)), $tire->tire_id]) }}" class="grid-view-link">
  <div class="tire-image-card sort-order">
    <div class="text-center image-grid-overflow">
      {!! App\Helper\Image::showGrid('quadr', $tire->make_id) !!}
    </div>

    <div class="tire-list-caption">

      <div class="card-title-text" data-toggle="tooltip" title="<div>{{$tire->title}}</div>">
        {{$tire->title}}
      </div>

      <div class="tire-tread">
        <b>{{$tire->d1}} / {{$tire->d2}} / {{$tire->d3}} </b>
        <span class="tire-image-code">{{$tire->code}}</span>
      </div>
      <div style="display: flex;">
        <input type="checkbox" name="product_ids[]" value="{{$tire->tire_id}}" style="margin-right: 5px;">
        <div class="rim-price-old" style="align-self: center;">€{{$tire->price1}}</div>
        <div class="rim-price-red" style="align-self: center;">€{{$tire->price2}}</div>
        {{--                              <i class="material-icons" style="margin-left: auto;">add_shopping_cart</i>--}}
        {{--                              <span class="grid-dot {{ $tire->dotAvailable }} {{ $tire->stockCount }}" data-toggle="tooltip"--}}
        {{--                                    data-html="true"--}}
        {{--                                    title="{{ $tire->stockAvailability }}">--}}
        <span style="margin-left: auto;" data-toggle="tooltip" data-html="true"
              title="<span style='color: black'>Pievienot grozam</span>">

                                  <button class="grid-buy-btn cart-shopping-button"
                                          data-toggle="modal"
                                          data-info="{{ $tire->tire_id }}"
                                          {{--                                      data-info="{{ $currTire->tire_id }}--}}
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
      </div>
    </div>
  </div>
</a>
