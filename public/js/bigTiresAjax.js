$(document).ready(function () {
  $(document).on('click', '.cart-shopping-button', function () {
    const isAdmin = typeof admin !== 'undefined' && admin;

    if (isAdmin) {
      handleAdminAddToCart($(this));
      return;
    }

    const tireId = $(this).data('info');
    if (!tireId) {
      return;
    }

    const ajaxUrl = $(this).data('link') || '/lielas-riepas';
    const tireUrl = $(this).data('url') || window.location.href;

    addBigTireToCart({
      tireId: tireId,
      quantity: 1,
      ajaxUrl: ajaxUrl,
      tireUrl: tireUrl
    });
  });

  $(document).on('click', '.add-to-cart', function (event) {
    event.preventDefault();

    const isAdmin = typeof admin !== 'undefined' && admin;
    if (isAdmin) {
      return;
    }

    const $button = $(this);
    const tireId = $button.data('info');
    if (!tireId) {
      return;
    }

    const quantityInput = $button.closest('.product-add-to-cart').find('input[name="qty"], #quantity_wanted');
    let quantity = parseInt(quantityInput.val(), 10);
    if (!Number.isFinite(quantity) || quantity < 1) {
      quantity = 1;
    }

    const ajaxUrl = $button.data('link') || '/lielas-riepas';
    const tireUrl = $button.data('url') || window.location.href;

    addBigTireToCart({
      tireId: tireId,
      quantity: quantity,
      ajaxUrl: ajaxUrl,
      tireUrl: tireUrl
    });
  });
});

function addBigTireToCart(params) {
  const ajaxUrl = (params.ajaxUrl || '/lielas-riepas').replace(/\/+$/, '');
  const tireId = params.tireId;
  const quantity = params.quantity || 1;
  const tireUrl = params.tireUrl || window.location.href;

  $.ajax({
    url: ajaxUrl + '/ajax',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    method: 'POST',
    data: { tire_id: tireId, tire_url: tireUrl, quantity: quantity },
    success: function (data) {
      updateCartModal(data, tireId, quantity);
    }
  });
}

function updateCartModal(data, tireId, requestedQty) {
  const cartQuantity = parseInt(data.quantity, 10) || 0;
  const totalSum = parseFloat(data.total_sum || 0) || 0;
  const tire = data.cart && data.cart.products ? data.cart.products[tireId] : null;
  if (!tire) {
    return;
  }

  const resolvedQty = parseInt(tire.quantity, 10) || parseInt(requestedQty, 10) || 1;
  const imageUrl = resolveImageUrl(tire.image);

  $('.modal-image-preview img')
    .attr('src', imageUrl)
    .attr('alt', tire.name || 'riepas_attēls');

  $('.modal-product-info .product-name').text(tire.name || '');
  $('.modal-product-info .product-price')
    .text(formatPrice(tire.price))
    .attr('data-price', formatNumericValue(tire.price));
  $('.modal-product-info .product-width').text(formatValue(tire.d1));
  $('.modal-product-info .product-height').text(formatValue(tire.d2));
  $('.modal-product-info .product-radius').text(formatValue(tire.d3));
  $('.modal-product-info .product-type').text(formatValue(tire.type));
  $('.modal-product-info .product-li').text(formatValue(tire.li));
  $('.modal-product-info .product-si').text(formatValue(tire.si));
  $('.modal-product-info .product-qty').text(resolvedQty).attr('data-qty', resolvedQty);

  $('.cart-content .cart-products-total').text(formatPrice(totalSum));
  $('span.cart-products-count').text('(' + cartQuantity + ')');
  $('.blockcart.cart-preview').removeClass('inactive').addClass('active');
  $('.blockcart.cart-preview .header').empty();
  $('<a rel="nofollow" href="' + grozs_url + '"><i class="material-icons shopping-cart">shopping_cart</i><span class="hidden-sm-down">Grozs: </span><span class="cart-products-count">(' + cartQuantity + ')</span></a>').appendTo('.blockcart.cart-preview .header');

  $('#blockcart-modal').modal('show');
}

function handleAdminAddToCart($button) {
  let tireData;
  let article = '';
  let productName = '';
  let price = '';
  let quantity = 1;

  if ($button.closest('tr').length) {
    tireData = $button.closest('tr');
    const link = tireData.find('.table-tire-name-cell a');
    article = link.data('article') || '';
    productName = link.data('content') || '';
    price = (tireData.find('#sale-price').text() || tireData.find('#store-price').text()).replace(/€\s*/g, '').trim();
    quantity = link.data('quantity') || 1;
  } else {
    tireData = $button.closest('.tire-image-card');
    const link = $button.closest('.grid-view-link');
    article = link.data('article') || '';
    productName = tireData.find('.card-title-text').text().trim();
    price = tireData.find('.rim-price-red').text().replace(/€\s*/g, '').trim() || tireData.find('.rim-price-old').text().replace(/€\s*/g, '').trim();
    quantity = link.data('quantity') || 1;
  }

  if (!article || article.length === 0) {
    article = 'no_article';
  }

  $('.popup input[name=prod]').val(productName);
  $('.popup input[name=price]').val(price);
  $('.popup input[name=qty]').val(quantity);
  $('.popup input[name=total]').val(parseFloat(price || 0) * quantity);
  $('.popup input[name=user]').val(user).attr('readonly', true).prop('readonly', true);
  $('.popup input[name=article]').val(article);

  const calcData = {
    article: article,
    qty: quantity,
    user: user,
    prod: productName,
    price: price,
  };

  addEntry(calcData);
  popCalc('/quick-order', 1200, 750);
}

function formatValue(value) {
  if (value === null || value === undefined || value === '') {
    return '-';
  }
  return value;
}

function formatNumericValue(value) {
  const number = parseFloat(value);
  return Number.isFinite(number) ? number : 0;
}

function formatPrice(value) {
  const number = formatNumericValue(value);
  return number.toFixed(2);
}

function resolveImageUrl(url) {
  if (!url) {
    return '/img/p/en-default-home_default.jpg';
  }

  if (/^https?:\/\//i.test(url)) {
    return url;
  }

  return '/' + url.replace(/^\/+/, '');
}


