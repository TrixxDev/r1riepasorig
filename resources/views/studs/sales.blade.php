<section id="products">
    <div id="">
        <div id="js-product-list">
            <h4 class="tire-type-title tire-brand-name flipped-title" style="color: black;">Skrūvējamas radzes</h4>
            <div class="products row hide-price title-flip">
                <table id="tires-table"
                   class="table table-striped studs-sorter tires-table table-hover tablesorter">
                <thead class="tires-thead sticky-table">
                <tr>
                    <th scope="col"></th>
                    <th scope="col" class="table-tire-name-cell">Brends / modelis</th>
                    <th scope="col" class="hidden-sm-down text-center">Radzes garums</th>
                    <th scope="col" class="hidden-sm-down text-center">Daudzums</th>
                    <th id="store-price-button" scope="col" class="text-center">
                        Veikala cena
                    </th>
                    <th id="store-sale-button" scope="col" class="text-center">Akcijas cena</th>
                    <th scope="col" class="hidden-sm-down text-center">Piezīmes</th>
                    <th scope="col"></th>
                    <th scope="col">
                        <div class="tire-table-icon icon-question" title="Pieejamība" data-toggle="tooltip"></div>
                    </th>

                </tr>
                </thead>
                <tbody id="tires-table-body">

                @foreach ($studs as $stud)
                    <tr class="tire-table-row">
                        <th scope="row" class="tire-table-checkbox">
                            <input type="checkbox" value="{{ $stud->stud_id }}" name="product_ids[]"
                                   class="tire-table-checkbox">
                        </th>

                        <td class="table-tire-name-cell" data-link="{{ route('radzes') }}">
                            <a data-toggle="tooltip" data-html="true" class="tire-table-link" title='{!! App\Helper\Image::show('studs', $stud->make_id) !!}'
                               href="{{ route('radze', [$stud->brand_slug, $stud->tread_slug, $stud->stud_id]) }}"
                               data-content="{{ $stud->sale_full_name }}"
                               data-article="{{ $stud->article }}"
                               data-quantity="{{ $cartQty }}">
                                <div class="table-link-title">{{ $stud->sale_full_name }}</div>
                            </a>
                        </td>

                        <td class="hidden-sm-down text-center">{{ $stud->stud_length }} mm</td>
                        <td class="hidden-sm-down text-center">{{ $stud->stud_count }}</td>

                        <td id="store-price" class="text-center store-price">€ {{ $stud->price1 }}</td>
                        <td id="sale-price" class="text-center tire-price-red sellout">€ {{ $stud->price2 }}</td>
                        <td class="hidden-sm-down text-center sellout">{{$stud->comment}}</td>

                        <td class="shopping-cart-col">
                            <div class="clearfix atc_div text-right">
                                <button class="cart-shopping-button" data-toggle="modal"
                                        @hasrole('administrators') data-target="#" @else data-target="#blockcart-modal" @endhasrole data-info="{{ $stud->stud_id }}"><i
                                        class="material-icons">add_shopping_cart</i>
                                </button>
                            </div>
                        </td>

                        <td class="dot-availability text-center">
                            <span class="dot {{ $stud->sale_dot_available }}" data-toggle="tooltip"
                                  data-html="true"
                                  title="{{ $stud->sale_stock_availability }}">
                              <span class="sort-order">{{ $stud->sale_dot_available }}</span>
                            </span>
                        </td>

                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
</section>