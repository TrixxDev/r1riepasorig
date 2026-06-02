<div class="tire-image-container">
@php
  $cbrand = '';
  $index = 0;
@endphp
@foreach ($rims as $rim)
@php
  $brand = 'R' . $rim->d3;
  if ($cbrand != $brand) {
    if ($index == 0) {
@endphp
      </div><h4 class="tire-brand-name grid-t">
        <span class="tire-type-title" style="margin: 0px;">Lietie diski</span>&nbsp;{{ $brand }}<span style="margin: 0 auto;"></span>
        <button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal">Filtrs</button>
      </h4><div class="row grid-ex pr-1 mobile-tire-container">
@php
    } else {
@endphp
      </div><h4 class="tire-brand-name grid-t">{{ $brand }}</h4><div class="row grid-ex pr-1 mobile-tire-container">
@php
    }
    $cbrand = $brand;
  }
@endphp
  <a href="{{ $rim->getUrl }}"
     class="grid-view-link"
     data-article="{{ $rim->article }}">
    <div class="tire-image-card sort-order">
      <div class="text-center image-grid-overflow">
        {!! \App\Helper\Image::showGrid('auto-rim', $rim->make_id) !!}
      </div>
      <div class="tire-list-caption">
        <div class="card-title-text">
          <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px;'><span style='color: black; font-size: 15px;'>{{ $rim->brandTitle }} {{ $rim->treadTitle }}</span></div>">{{ $rim->brandTitle }} {{ $rim->treadTitle }}</span>
        </div>
        <div class="rim-tread">
          <b>{{ $rim->d1 }}*{{ $rim->d3 }} ({{ $rim->skr }}*{{ $rim->pcd }} ET{{ $rim->et }})</b>
          <span class="tire-image-code">{{ $rim->code }}</span>
        </div>
        <div style="display: flex;">
          <input type="checkbox" name="product_ids[]" value="{{ $rim->rim_id }}" style="margin-right: 5px;">
          <div class="rim-price-old" style="align-self: center;">€{{ $rim->price1 }}</div>
          <div class="rim-price-red" style="align-self: center;">€{{ $rim->price2 }}</div>
          <span style="margin-left: auto;" data-toggle="tooltip" title="<span style='color: black'>Pievienot grozam</span>">
            <button class="grid-buy-btn cart-shopping-button"
                    data-toggle="modal"
                    data-info="{{ $rim->rim_id }}"
                    data-url="{{ $rim->getUrl }}"
                    onclick="event.preventDefault()"
                    @if (Auth::check() && Auth::user()->hasRole('administrators')) data-target="#" @else data-target="#blockcart-modal" @endif>
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
@php
  $index++;
@endphp
@endforeach
</div>
