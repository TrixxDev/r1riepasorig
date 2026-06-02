function updateRimCartModal(response, rimId, quantityOverride) {
  let data;
  try {
    data = (typeof response === 'string') ? JSON.parse(response) : response;
  } catch (error) {
    console.warn('RimProductPageCart: nevar parsēt atbildi', error);
    return false;
  }

  if (!data || typeof data !== 'object') {
    console.warn('RimProductPageCart: saņemti nekorekti dati', data);
    return false;
  }

  const products = (data.cart && data.cart.products) ? data.cart.products : {};
  const rimProduct = products[rimId] || products[String(rimId)] || null;

  if (!rimProduct) {
    console.warn('RimProductPageCart: disks nav atrasts grozā', rimId, products);
    return false;
  }

  const cartQuantity = Number(data.quantity) || 0;
  const totalSum = Number(data.total_sum) || 0;
  const productImage = rimProduct.image ? '/' + rimProduct.image : '/img/p/en-default-home_default.jpg';
  const priceValue = Number(rimProduct.price);
  const priceText = Number.isFinite(priceValue) ? priceValue.toFixed(2) : (rimProduct.price || '0.00');
  const quantityValue = Number.isFinite(Number(rimProduct.quantity))
    ? Number(rimProduct.quantity)
    : (Number.isFinite(Number(quantityOverride))
      ? Number(quantityOverride)
      : (Number.isFinite(Number(data.bought)) ? Number(data.bought) : 0));
  const totalSumText = Number.isFinite(totalSum)
    ? totalSum.toFixed(2)
    : (data.total_sum || '0');
  const boltPattern = (rimProduct.skr && rimProduct.pcd) ? rimProduct.skr + 'x' + rimProduct.pcd : '';
  const etValue = rimProduct.et !== undefined && rimProduct.et !== null ? rimProduct.et : '';
  const centerValue = rimProduct.dc !== undefined && rimProduct.dc !== null ? rimProduct.dc : (rimProduct.center || '');
  const colorValue = (rimProduct.color || '').toString().trim();

  const modalInfo = $('.modal-product-info');
  if (!modalInfo.length) {
    console.warn('RimProductPageCart: nav atrasta modāles struktūra');
    return false;
  }

  $('.modal-image-preview img').attr('src', productImage);
  modalInfo.find('.product-name').text(rimProduct.name || '');
  modalInfo.find('.product-price')
    .text(priceText)
    .attr('data-price', Number.isFinite(priceValue) ? priceValue : rimProduct.price);
  modalInfo.find('.product-width').text(rimProduct.d1 || '');
  modalInfo.find('.product-height').text(rimProduct.d2 || '');
  modalInfo.find('.product-radius').text(rimProduct.d3 || '');
  modalInfo.find('.product-type').text(boltPattern);
  modalInfo.find('.product-li').text(etValue);
  modalInfo.find('.product-si').text(centerValue);
  modalInfo.find('.product-qty')
    .text(quantityValue)
    .attr('data-qty', quantityValue);

  const typeContainer = modalInfo.find('.product-type').parent();
  const liContainer = modalInfo.find('.product-li').parent();
  const siContainer = modalInfo.find('.product-si').parent();

  const typeLabel = typeContainer.children('strong');
  if (typeLabel.length) {
    typeLabel.text('Skrūvju attālums');
  }
  const liLabel = liContainer.children('strong');
  if (liLabel.length) {
    liLabel.text('ET');
  }
  const siLabel = siContainer.children('strong');
  if (siLabel.length) {
    siLabel.text('Centrs');
  }

  const heightContainer = modalInfo.find('.product-height').parent();
  const heightBreak = heightContainer.length ? heightContainer.next('br') : $();
  heightContainer.hide();
  heightBreak.hide();

  let colorRowWrapper = modalInfo.find('.product-color-row');
  if (colorValue.length) {
    if (!colorRowWrapper.length) {
      $('<span class="product-color-row"><strong>Krāsa</strong>: <span class="product-color"></span></span><br class="product-color-break">')
        .insertBefore(modalInfo.find('p:has(.product-qty)').first());
      colorRowWrapper = modalInfo.find('.product-color-row');
    }
    colorRowWrapper.find('.product-color').text(colorValue.toUpperCase());
    colorRowWrapper.show();
    modalInfo.find('.product-color-break').show();
  } else if (colorRowWrapper.length) {
    colorRowWrapper.hide();
    modalInfo.find('.product-color-break').hide();
  }

  $('.cart-content .cart-products-total').text(totalSumText);
  $('span.cart-products-count').text('(' + cartQuantity + ')');
  $('.blockcart.cart-preview').removeClass('inactive').addClass('active');
  $('.blockcart.cart-preview .header').empty();
  $('<a rel="nofollow" href="' + grozs_url + '"><i class="material-icons shopping-cart">shopping_cart</i><span class="hidden-sm-down">Grozs: </span><span class="cart-products-count">(' + cartQuantity + ')</span></a>')
    .appendTo('.blockcart.cart-preview .header');

  $('#blockcart-modal').modal('show');

  $('#blockcart-modal')
    .off('hidden.bs.modal.rimReset')
    .on('hidden.bs.modal.rimReset', function () {
      if (typeLabel.length) {
        typeLabel.text('Tips');
      }
      if (liLabel.length) {
        liLabel.text('LI');
      }
      if (siLabel.length) {
        siLabel.text('SI');
      }
      if (heightContainer.length) {
        heightContainer.show();
      }
      if (heightBreak.length) {
        heightBreak.show();
      }
      if (colorRowWrapper.length) {
        colorRowWrapper.hide();
        modalInfo.find('.product-color-break').hide();
      }
      $(this).off('hidden.bs.modal.rimReset');
    });

  return true;
}

function isRimProductTreadPage() {
  const path = window.location.pathname;
  return /\/lietie-diski\/[^/]+\/[^/]+\/\d+\/?$/.test(path)
    || /\/kvadraciklu-diski\/[^/]+\/[^/]+\/\d+\/?$/.test(path)
    || /\/kvadru-diski\/[^/]+\/[^/]+\/\d+\/?$/.test(path);
}

function getRimProductAjaxUrl() {
  if (window.location.pathname.includes('/kvadraciklu-diski')) {
    return '/kvadraciklu-diski/ajax';
  }
  if (window.location.pathname.includes('/kvadru-diski')) {
    return '/kvadru-diski/ajax';
  }
  return '/lietie-diski/ajax';
}

function addRimToCart(rimId, quantity) {
  const qty = Number.isFinite(Number(quantity)) && Number(quantity) > 0
    ? Number(quantity)
    : 1;

  $.ajax({
    url: getRimProductAjaxUrl(),
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    method: 'POST',
    data: {
      rim_id: rimId,
      rim_url: window.location.href,
      quantity: qty
    },
    success(response) {
      updateRimCartModal(response, rimId, qty);
    },
    error(xhr, status, error) {
      console.error('RimProductPageCart: AJAX kļūda:', error, xhr.responseText);
    }
  });
}

$(document).ready(function() {
  function openRimQuickOrder(calcData) {
    if (!calcData) {
      return;
    }

    const currentUser = (typeof user !== 'undefined' ? user : '');
    const qty = parseInt(calcData.qty, 10) || 1;
    const price = (calcData.price || '').toString().replace(/€\s*/g, '').trim();
    const total = parseFloat(price.replace(',', '.')) * qty;

    $('.popup input[name=prod]').val(calcData.prod || '');
    $('.popup input[name=price]').val(price);
    $('.popup input[name=qty]').val(qty);
    $('.popup input[name=total]').val(Number.isFinite(total) ? total : 0);
    $('.popup input[name=user]').val(currentUser).attr('readonly', true).prop('readonly', true);
    $('.popup input[name=article]').val(calcData.article || '');

    if (typeof addEntry === 'function') {
      addEntry(calcData);
    }
    if (typeof popCalc === 'function') {
      popCalc('/testing3', 1200, 750);
    }
  }

  function extractRimQuickOrderData($button) {
    const currentUser = (typeof user !== 'undefined' ? user : '');
    const $row = $button.closest('tr');

    if ($row.length) {
      const $tireInfo = $row.find('.tire-info');
      if ($tireInfo.length) {
        let article = ($tireInfo.data('article') || '').toString().trim();
        if (!article) {
          article = 'no_article';
        }
        const prod = ($tireInfo.data('content') || '').toString().trim() || article;
        const qty = parseInt($tireInfo.data('quantity'), 10) || parseInt($('#quantity_wanted').val(), 10) || 1;
        const priceHtml = ($row.find('.tire-price-red, .sale-price, #sale-price').first().text() || '').trim();
        const price = priceHtml.replace(/€\s*/g, '').trim();
        return { article, qty, user: currentUser, prod, price };
      }

      const $link = $row.find('.table-tire-name-cell a');
      if ($link.length) {
        let article = ($link.data('article') || '').toString().trim();
        if (!article) {
          article = 'no_article';
        }
        const prod = ($link.data('content') || '').toString().trim() || article;
        const qty = parseInt($link.data('quantity'), 10) || 1;
        const priceHtml = ($row.find('.tire-price-red').html() || $row.find('.sale-price').text() || '').trim();
        const price = priceHtml.replace(/€\s*/g, '').replace('€', '').trim();
        return { article, qty, user: currentUser, prod, price };
      }
    }

    if ($button.hasClass('add-to-cart')) {
      const qty = parseInt($('#quantity_wanted').val(), 10) || 1;
      let article = ($('input.tire_article').val() || $('input[name="article"].tire_article').val() || '').toString().trim();
      if (!article) {
        article = 'no_article';
      }
      const prod = ($('input.tire_title').val() || $('input[name="title"].tire_title').val() || $('h1[itemprop="name"]').text() || '').toString().trim() || article;
      const priceFromContent = $('[itemprop="price"]').attr('content');
      const priceFromText = $('[itemprop="price"]').text();
      const price = (priceFromContent || priceFromText || '').toString().replace(/[^\d.,]/g, '').replace(',', '.');
      return { article, qty, user: currentUser, prod, price };
    }

    return null;
  }

  function handleRimTreadCartClick(e) {
    if (!isRimProductTreadPage()) {
      return;
    }

    const btn = e.target.closest('.add-to-cart, .grid-cart-btn');
    if (!btn) {
      return;
    }

    const main = document.getElementById('main');
    if (!main || !main.contains(btn)) {
      return;
    }

    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    const $button = $(btn);
    const isAdmin = (typeof admin !== 'undefined' && admin);

    if (isAdmin) {
      openRimQuickOrder(extractRimQuickOrderData($button));
      return;
    }

    const rimId = btn.getAttribute('data-info') || $button.data('info');
    if (!rimId) {
      return;
    }

    const quantity = parseInt($('#quantity_wanted').val(), 10) || 1;
    addRimToCart(rimId, quantity);
  }

  const mainEl = document.getElementById('main');
  if (mainEl && isRimProductTreadPage()) {
    mainEl.addEventListener('click', handleRimTreadCartClick, true);
  }
});
