<div class="tire-image-container">

@php

  $cbrand = '';

@endphp

@foreach ($rims as $rim)

@php

  $brand = 'R' . $rim->d3;

  if ($cbrand != $brand) {

    if ($cbrand !== '') {

@endphp

    </div>

@php

    }

    $cbrand = $brand;

@endphp

    @if ($loop->first)

      <h4 class="tire-brand-name grid-t">

        <span class="tire-type-title" style="margin: 0px;">Kvadraciklu diski</span>&nbsp;{{ $brand }}<span style="margin: 0 auto;"></span>

        <button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal">Filtrs</button>

      </h4>

    @else

      <h4 class="tire-brand-name grid-t">{{ $brand }}</h4>

    @endif

    <div class="row grid-ex pr-1 mobile-tire-container">

@php

  }

@endphp

  <a href="{{ route('kvadracikla-disks', [\Illuminate\Support\Str::slug($rim->brandTitle), strtolower(str_replace('/', '_', $rim->treadTitle)), $rim->rim_id]) }}"

     class="grid-view-link js-atv-rim-row"

     data-atv-rim-row="{{ $rim->rim_id }}"

     data-article="{{ $rim->article ?? '' }}">

    <div class="tire-image-card sort-order">

      <div class="text-center image-grid-overflow">

        {!! \App\Helper\Image::showGrid('quadr-rim', $rim->make_id) !!}

      </div>

      <div class="tire-list-caption">

        <div class="card-title-text">

          <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px;'><span style='color: black; font-size: 15px;'>{{ $rim->fullTitle }}</span></div>">{{ $rim->fullTitle }}</span>

        </div>

        <div class="rim-tread">

          <b>{{ $rim->d1 }}*{{ $rim->d3 }} ({{ $rim->skr }}*{{ $rim->pcd }} ET{{ $rim->et }})</b>

        </div>

        <div style="display: flex;">

          <input type="checkbox" name="product_ids[]" value="{{ $rim->rim_id }}" class="js-atv-select tire-table-checkbox" data-atv-rim-row="{{ $rim->rim_id }}" style="margin-right: 5px;">

          <div class="rim-price-old" style="align-self: center;">€{{ $rim->price1 }}</div>

          <div class="rim-price-red" style="align-self: center;">€{{ $rim->price2 }}</div>

          <span style="margin-left: auto;" data-toggle="tooltip" data-html="true"

                title="<span style='color: black'>Pievienot grozam</span>">

            <button class="grid-buy-btn cart-shopping-button"

                    data-toggle="modal"

                    data-info="{{ $rim->rim_id }}"

                    onclick="event.preventDefault()"

                    @if (Auth::check() && Auth::user()->hasRole('administrators')) data-target="#"

                    @else data-target="#blockcart-modal" @endif>

              <i class="material-icons">add_shopping_cart</i>

            </button>

          </span>

          <span class="tippy lisi-tooltip grid-dot {{ $rim->dotAvailable }} {{ $rim->stockCount }}"

                data-color="{{ $rim->dotAvailable }}"

                data-tippy-content='{!! '<div style="padding: 5px;"><span style="color: black; font-size: 15px;">' . str_replace("'", '&#39;', $rim->stockAvailability) . '</span></div>' !!}'></span>

          <span class="sort-order" style="display: none;">{{ $rim->dotAvailable }}</span>

        </div>

      </div>

    </div>

  </a>

@endforeach

@if ($rims->count())

    </div>

@endif

</div>

