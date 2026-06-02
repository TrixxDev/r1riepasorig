$(document).ready(function() {
  const ajaxUrl = '/kvadru-riepas/ajax';

  function onCartAjaxSuccess(data, tire_id) {
    const cart_quantity = parseInt(data.quantity);
    const total_sum = parseInt(data.total_sum);
    let tire = data.cart.products[tire_id];
    if (!tire) return;

    let image_url = '\\' + tire.image || '/img/p/en-default-home_default.jpg';
    $('.modal-image-preview img').attr('src', image_url);
    $('.modal-product-info .product-name').html(tire.name);
    $('.modal-product-info .product-price').html(parseInt(tire.price)).attr('data-price', parseInt(tire.price));
    $('.modal-product-info .product-width').html(tire.d1);
    $('.modal-product-info .product-height').html(tire.d2);
    $('.modal-product-info .product-radius').html(tire.d3);
    $('.modal-product-info .product-type').html(tire.type);
    $('.modal-product-info .product-li').html(tire.li);
    $('.modal-product-info .product-si').html(tire.si);
    $('.cart-content .cart-products-total').html(total_sum);
    $('.modal-product-info .product-qty').html(tire.quantity).attr('data-qty', tire.quantity);
    $('span.cart-products-count').html('(' + cart_quantity + ')');
    $('.blockcart.cart-preview').removeClass('inactive').addClass('active');
    $('.blockcart.cart-preview .header').empty();
    $('<a rel="nofollow" href="' + grozs_url + '"><i class="material-icons shopping-cart">shopping_cart</i><span class="hidden-sm-down">Grozs: </span><span class="cart-products-count">(' + cart_quantity + ')</span></a>').appendTo('.blockcart.cart-preview .header');
  }

  // Кнопка «Pirkt» у основной карточки
  $(document).on('click', '.add-to-cart', function() {
    if (!admin) {
      const tire_id = $(this).data('info');
      let quantity = parseInt($('#quantity_wanted').val()) || 1;
      let tire_url = window.location.href;

      $.ajax({
        url: ajaxUrl,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        method: 'POST',
        data: {tire_id: tire_id, tire_url: tire_url, quantity: quantity},
        success: function (data) {
          onCartAjaxSuccess(data, tire_id);
        }
      });
    }
  });

  // Иконка корзины в таблице размеров (coll. Grozs)
  $(document).on('click', '.grid-cart-btn', function() {
    if (!admin) {
      const tire_id = $(this).data('info');
      let tire_url = $(this).data('url') || window.location.href;
      const $row = $(this).closest('tr');
      const payload = {tire_id: tire_id, tire_url: tire_url};
      const qtyRaw = $row.find('.tire-info').data('quantity');
      if (qtyRaw !== undefined && qtyRaw !== null && qtyRaw !== '') {
        const q = parseInt(qtyRaw, 10);
        if (!isNaN(q) && q > 0) {
          payload.quantity = q;
        }
      }

      $.ajax({
        url: ajaxUrl,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        method: 'POST',
        data: payload,
        success: function (data) {
          onCartAjaxSuccess(data, tire_id);
        }
      });
    }
  });
});
