$(document).ready(function() {
  function getSelectedTreadIds() {
    const ids = [];
    $('#tires-table-body input[name="product_ids[]"]:checked').each(function() {
      ids.push($(this).val());
    });
    return ids;
  }

  function buildTreadPageUrl(selectedIds, showSelectedFilter) {
    const params = new URLSearchParams(window.location.search);

    if (selectedIds.length > 0) {
      params.set('selected', selectedIds.join(','));
    } else {
      params.delete('selected');
      params.delete('show_selected');
    }

    if (showSelectedFilter) {
      params.set('show_selected', 'show');
    } else {
      params.delete('show_selected');
    }

    const qs = params.toString();
    return qs ? `${window.location.pathname}?${qs}` : window.location.pathname;
  }

  function updateTreadUrl(options) {
    const replace = !!(options && options.replace);
    const selectedIds = getSelectedTreadIds();
    const showSelectedFilter = $('#show-selected-tread-checkbox').is(':checked');
    const newUrl = buildTreadPageUrl(selectedIds, showSelectedFilter);
    const currentUrl = window.location.pathname + window.location.search;

    if (newUrl === currentUrl) {
      return;
    }

    const state = { treadSelected: selectedIds };
    if (replace) {
      history.replaceState(state, '', newUrl);
    } else {
      history.pushState(state, '', newUrl);
    }
  }

  function syncTreadShowSelectedFilter() {
    const selectedCount = $('#tires-table-body input[name="product_ids[]"]:checked').length;
    const $filter = $('#show-selected-tread-checkbox');

    if (!$filter.length) {
      return;
    }

    if (selectedCount > 0) {
      $filter.removeAttr('disabled').prop('disabled', false);
    } else {
      $filter.attr('disabled', true).prop('disabled', true);
      $filter.prop('checked', false);
      $('#tires-table-body .tire-table-row').show();
      updateTreadUrl({ replace: true });
    }
  }

  function applyTreadShowSelectedFilter(showOnlySelected) {
    $('#tires-table-body .tire-table-row').each(function() {
      const isSelected = $(this).find('input[name="product_ids[]"]').is(':checked');
      $(this).toggle(!showOnlySelected || isSelected);
    });
  }

  if ($('#show-selected-tread-checkbox').length) {
    syncTreadShowSelectedFilter();

    const treadUrlParams = new URLSearchParams(window.location.search);
    if (treadUrlParams.get('show_selected')) {
      const $filter = $('#show-selected-tread-checkbox');
      $filter.removeAttr('disabled').prop('disabled', false);
      $filter.prop('checked', true);
      applyTreadShowSelectedFilter(true);
    }

    $(document).on('click', 'th.tread-tire-table-checkbox input[name="product_ids[]"]', function() {
      $(this).closest('.tire-table-row').toggleClass('selected', this.checked);
      syncTreadShowSelectedFilter();
      if ($('#show-selected-tread-checkbox').is(':checked')) {
        applyTreadShowSelectedFilter(true);
      }
      updateTreadUrl();
    });

    $(document).on('change', '#show-selected-tread-checkbox', function() {
      if (this.disabled) {
        return;
      }
      applyTreadShowSelectedFilter(this.checked);
      updateTreadUrl();
    });
  }

  // Обработчик кнопки "Pirkt" на странице товара для мотоциклетных шин
  $(document).on('click', '.add-to-cart', function() {
    const isAdmin = (typeof admin !== 'undefined' && admin);
    if (!isAdmin) {
      const tire_id = $(this).data('info');
      let quantity = parseInt($('#quantity_wanted').val()) || 1;
      let tire_url = window.location.href; // Получаем текущий URL страницы
      
      // Определяем правильный URL для AJAX запроса для мотоциклетных шин
      let ajaxUrl = '/motociklu-riepas/ajax';

      $.ajax({
        url: ajaxUrl,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        method: 'POST',
        data: {tire_id: tire_id, tire_url: tire_url, quantity: quantity},
        success: function (data) {
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
      });
      return;
    }

    // ADMIN: добавляем в админ-корзину (localStorage allEntries) и открываем quick order
    const qty = parseInt($('#quantity_wanted').val(), 10) || 1;
    const article =
      ($('input.tire_article').val() || $('input[name="article"].tire_article').val() || '').toString().trim() ||
      'no_article';
    const prod =
      ($('input.tire_title').val() || $('input[name="title"].tire_title').val() || $('h1[itemprop="name"]').text() || '').toString().trim() ||
      article;

    const priceFromContent = $('[itemprop="price"]').attr('content');
    const priceFromText = $('[itemprop="price"]').text();
    const price = (priceFromContent || priceFromText || '').toString().replace(/[^\d.,]/g, '').replace(',', '.');

    const currentUser = (typeof user !== 'undefined' ? user : '');
    const calcData = {
      article: article,
      qty: qty,
      user: currentUser,
      prod: prod,
      price: price
    };

    if (typeof addEntry === 'function') {
      addEntry(calcData);
    }
    if (typeof popCalc === 'function') {
      popCalc('/testing3', 1200, 750);
    }
  });
});

