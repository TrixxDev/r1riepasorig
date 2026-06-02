function updateRimCartModal(response, rimId, quantityOverride) {
  let data;
  try {
    data = (typeof response === 'string') ? JSON.parse(response) : response;
  } catch (error) {
    console.warn('RimAjax: nevar parsēt atbildi', error);
    return false;
  }

  if (!data || typeof data !== 'object') {
    console.warn('RimAjax: saņemti nekorekti dati', data);
    return false;
  }

  const products = (data.cart && data.cart.products) ? data.cart.products : {};
  const rimProduct = products[rimId] || products[String(rimId)] || null;

  if (!rimProduct) {
    console.warn('RimAjax: disks nav atrasts grozā', rimId, products);
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
    console.warn('RimAjax: nav atrasta modāles struktūra');
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
    const colorRow = colorRowWrapper.find('.product-color');
    colorRow.text(colorValue.toUpperCase());
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

function addRimToCart(rimId, quantity, ajaxUrlOverride) {
  const qty = Number.isFinite(Number(quantity)) && Number(quantity) > 0
    ? Number(quantity)
    : 1;

  let ajaxUrl = ajaxUrlOverride || '/kvadraciklu-diski/ajax';
  if (!ajaxUrlOverride) {
    if (window.location.pathname.includes('/lietie-diski')) {
      ajaxUrl = '/lietie-diski/ajax';
    } else if (window.location.pathname.includes('/kvadru-diski')) {
      ajaxUrl = '/kvadru-diski/ajax';
    }
  }

  $.ajax({
    url: ajaxUrl,
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
      console.error('RimAjax: addRimToCart error:', error, xhr.responseText);
    }
  });
}

$(document).ready(function() {
  const pathname = window.location.pathname;
  const isRimPage = pathname.includes('/lietie-diski') || pathname.includes('/kvadraciklu-diski')
    || pathname.includes('/kvadru-diski');
  const isRimTreadPage =
    /\/lietie-diski\/[^/]+\/[^/]+\/\d+\/?$/.test(pathname) ||
    /\/kvadraciklu-diski\/[^/]+\/[^/]+\/\d+\/?$/.test(pathname) ||
    /\/kvadru-diski\/[^/]+\/[^/]+\/\d+\/?$/.test(pathname);

  function handleRimAddToCart($button, ajaxUrlOverride) {
    const isAdmin = (typeof admin !== 'undefined' && admin);

    if (isAdmin) {
      const tireData = $button.closest('tr');
      const linkElement = $('.table-tire-name-cell a', tireData);
      let article = (linkElement.data('article') || '').toString().trim();
      if (!article) {
        article = 'no_article';
      }

      const content = (linkElement.data('content') || '').toString().trim();
      const quantityValue = parseInt(linkElement.data('quantity'), 10) || 1;
      const priceHtml = ($('.tire-price-red', tireData).html() || '').trim();
      const priceNumeric = priceHtml.replace(/€\s*/g, '').trim();
      const currentUser = (typeof user !== 'undefined' ? user : '');

      $('.popup input[name=prod]').val(content);
      $('.popup input[name=price]').val(priceNumeric);
      $('.popup input[name=qty]').val(quantityValue);
      const total = parseFloat(priceNumeric.replace(',', '.')) * quantityValue;
      $('.popup input[name=total]').val(Number.isFinite(total) ? total : 0);
      $('.popup input[name=user]').val(currentUser).attr('readonly', true).prop('readonly', true);
      $('.popup input[name=article]').val(article);

      calcData = {
        article: article,
        qty: quantityValue,
        user: currentUser,
        prod: content,
        price: priceNumeric,
      };

      if (typeof addEntry === 'function') {
        addEntry(calcData);
      }
      if (typeof popCalc === 'function') {
        popCalc('/testing3', 1200, 750);
      }
      return;
    }

    const rimId = $button.data('info');
    
    if (!rimId) {
      console.error('RimAjax: nav rim_id pogai', $button);
      return;
    }

    let ajaxUrl = ajaxUrlOverride;
    if (!ajaxUrl) {
      ajaxUrl = '/lietie-diski/ajax';
      if (window.location.pathname.includes('/kvadraciklu-diski')) {
        ajaxUrl = '/kvadraciklu-diski/ajax';
      } else if (window.location.pathname.includes('/kvadru-diski')) {
        ajaxUrl = '/kvadru-diski/ajax';
      }
    }

    const linkedCell = $button.closest('tr').find('.table-tire-name-cell');

    if (linkedCell.length && linkedCell.attr('data-link')) {
      const dl = linkedCell.attr('data-link');
      if (dl.includes('kvadraciklu-diski')) {
        ajaxUrl = '/kvadraciklu-diski/ajax';
      } else if (dl.includes('kvadru-diski')) {
        ajaxUrl = '/kvadru-diski/ajax';
      } else if (dl.includes('lietie-diski')) {
        ajaxUrl = '/lietie-diski/ajax';
      } else {
        ajaxUrl = dl + '/ajax';
      }
    }

    addRimToCart(rimId, 1, ajaxUrl);
  }

  const rimShoppingCartSelectors = '.cart-shopping-button, .grid-cart-btn, .grid-buy-btn, button.add-to-cart';
  const isAtvRimsCatalog = $('#atv-rims-catalog').length > 0;

  if (isRimPage && !isRimTreadPage && !isAtvRimsCatalog) {
    $(document)
      .off('click.rimShoppingCartPage', rimShoppingCartSelectors)
      .on('click.rimShoppingCartPage', rimShoppingCartSelectors, function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        const $button = $(this);
        handleRimAddToCart($button);
        return false;
      });
  }

  // Обработчик для .cart-shopping-button (таблицы на всех страницах, включая /akcijas)
  // Проверяем, что это действительно диск по data-link, URL страницы или data-url кнопки
  // Используем делегирование с высоким приоритетом (ранняя регистрация)
  if (!isAtvRimsCatalog) {
  $(document)
    .off('click.rimShoppingCart', rimShoppingCartSelectors)
    .on('click.rimShoppingCart', rimShoppingCartSelectors, function(e) {
      const $button = $(this);

      if (window.location.pathname.includes('/lietie-diski')) {
        return;
      }

      const path = window.location.pathname;
      const isQuadTreadPath =
        (path.includes('/kvadraciklu-diski') || path.includes('/kvadru-diski')) &&
        path.split('/').filter(Boolean).length >= 4;

      const buttonUrl = $button.attr('data-url') || '';
      const isRimUrl =
        buttonUrl.includes('lietais-disks') ||
        buttonUrl.includes('lietie-diski') ||
        buttonUrl.includes('kvadraciklu-diski') ||
        buttonUrl.includes('kvadru-diski');

      const tire_name = $button.closest('tr').find('.table-tire-name-cell');
      const dataLink = tire_name.attr('data-link');
      const isRimLink =
        dataLink &&
        (
          dataLink.includes('lietie-diski') ||
          dataLink.includes('kvadraciklu-diski') ||
          dataLink.includes('kvadru-diski')
        );

      const isRimContext = isRimUrl || isRimLink || isQuadTreadPath;

      if (!isRimContext) {
        return;
      }

      e.stopImmediatePropagation();
      e.preventDefault();

      let ajaxUrl = '/lietie-diski/ajax';
      if (window.location.pathname.includes('/kvadraciklu-diski')) {
        ajaxUrl = '/kvadraciklu-diski/ajax';
      } else if (window.location.pathname.includes('/kvadru-diski')) {
        ajaxUrl = '/kvadru-diski/ajax';
      }
      if (isRimLink) {
        if (dataLink.includes('kvadraciklu-diski')) ajaxUrl = '/kvadraciklu-diski/ajax';
        else if (dataLink.includes('kvadru-diski')) ajaxUrl = '/kvadru-diski/ajax';
        else ajaxUrl = dataLink.endsWith('/ajax') ? dataLink : (dataLink.replace(/\/ajax$/, '') + '/ajax');
      }

      handleRimAddToCart($button, ajaxUrl);
      return false;
    });
  }

  if (isRimTreadPage) {
    return;
  }

  // Server-rendered ATV catalog: atv-rims.js handles filters/view and cart.
  if (pathname.includes('/kvadraciklu-diski')) {
    return;
  }

  $.urlParam = function(name){
    let results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null) {
      return null;
    }
    return decodeURI(results[1]) || 0;
  }

  const pathParts = window.location.pathname.split('/');
  let newUrl;
  let rims_array = [];
  window['table_type'] = '&table_type=list';
  window['selected_rims'] = ($.urlParam('selected') !== null) ? '&selected=' + $.urlParam('selected') : '';
  window['show_selected'] = ($.urlParam('show_selected') !== null) ? '&show_selected=' + $.urlParam('show_selected') : '';
  window['page'] = ($.urlParam('page') !== null) ? '&page=' + $.urlParam('page') : '';
  let pageNr = 1;
  let pageLoaded;

  function syncFilterStateFromSelects() {
    let skr = $('select.select-rim-lugs option:selected').val();
    let pcd = $('select.select-rim-spread option:selected').val();
    let d3 = $('select.select-rim-diameter option:selected').val();
    let wid = $('select.select-rim-width option:selected').val();
    let wid2 = $('select.select-rim-width2 option:selected').val();
    let et = $('select.select-rim-offset option:selected').val();
    let et2 = $('select.select-rim-offset2 option:selected').val();
    let rim_center = $('select.select-rim-center option:selected').val();

    if (skr != window['skr']) window['skr'] = skr;
    if (pcd != window['pcd']) window['pcd'] = pcd;
    if (d3 != window['d3']) window['d3'] = d3;
    if (wid != window['wid']) window['wid'] = wid;
    if (wid2 != window['wid2']) window['wid2'] = wid2;
    if (et != window['et']) window['et'] = et;
    if (et2 != window['et2']) window['et2'] = et2;
    if (rim_center != window['rim_center']) window['rim_center'] = rim_center;
  }

  function canUseServerRenderedList() {
    if (typeof pageLoaded !== 'undefined') {
      return false;
    }
    if ($('#js-product-list .products .tire-table-row').length === 0) {
      return false;
    }
    if (window['selected_rims'] !== '' || window['show_selected'] !== '' || window['page'] !== '') {
      return false;
    }
    if (localStorage.getItem('show_type') === 'grid') {
      return false;
    }
    return true;
  }

  function finalizeRimList() {
    if (window['selected_rims'].length > 0) {
      rims_array = window['selected_rims'].replace('&selected=', '').split(',');
      $(document).find('th.tire-table-checkbox').children().each(function() {
        $(this).removeAttr('checked').removeProp('checked');
        $(this).parent().parent().removeClass('selected');
      });
      $(document).find('#js-product-list .mobile-tire-container .tire-list-caption input[type=checkbox]').each(function() {
        $(this).removeAttr('checked').removeProp('checked');
        $(this).parent().parent().parent().removeClass('selected');
      });
      $.map(rims_array, function(value, index) {
        $('.tire-table-row .tire-table-checkbox input[value="' + value + '"]').attr('checked', true).prop('checked', true).parent().parent().toggleClass('selected');
        $(document).find('#js-product-list .mobile-tire-container .tire-list-caption input[type=checkbox][value="' + value + '"]').attr('checked', true).prop('checked', true).parent().parent().parent().toggleClass('selected');
      });
    }

    $('.rims-sorter').each(function() {
      if ($(this).data('tablesorter')) {
        return;
      }
      $(this).tablesorter({
          headers: {
            0: {sorter: false},
            1: {sorter: false},
            2: {sorter: false},
            3: {sorter: false},
            4: {sorter: false},
            5: {sorter: false},
            6: {sorter: false},
            7: {sorter: true},
            8: {sorter: true},
            9: {sorter: false},
            10: {sorter: false},
            11: {sorter: true},
          },
        }
      );
    });

    $($('.pagination-col')).insertAfter($('#tires-table:last-child'));

    $(document).find('th.tire-table-checkbox').children().off('click.rimSelected').on('click.rimSelected', function() {
      rims_array = [];
      $(this).parent().parent().toggleClass('selected');
      $(document).find('th.tire-table-checkbox').children(':checked').each(function() {
        rims_array.push($(this).val());
      });

      if (rims_array.length > 0) {
        $('#show-selected-checkbox').removeAttr('disabled').prop('disabled', false);
        window['selected_rims'] = '&selected=' + rims_array.join(',');
      } else {
        $('#show-selected-checkbox').attr('disabled', true).prop('disabled', true);
        window['selected_rims'] = '';
      }
      newUrl = '/lietie-diski/search?' + 'currentSkr=' + window['skr'] + '&currentPcd=' + window['pcd'] + '&currentDia=' + window['d3'] + '&currentWid=' + window['wid'] + '&currentWid2=' + window['wid2'] + '&currentEt=' + window['et'] + '&currentEt2=' + window['et2'] + '&currentCenter=' + window['rim_center'] + window['selected_rims'] + window['show_selected'] + window['table_type'] + window['page'];

      if (pageLoaded === 1) {
        history.pushState({ prevUrl: document.referrer }, '', newUrl);
      }
    });

    $('.tire-table-checkbox').each(function(){
      if($(this).is(':checked')){
        $('input#show-selected-checkbox').prop( "disabled", false );
      }
    });

    $('.loading-block-content').fadeOut();
  }

  // SHOW LIST VIEW
  $('div.can-collapse span.show_list').on('click', function(){
    $(this).addClass('active');
    $('span.show_grid').removeClass('active');
    localStorage.setItem("show_type", "list");
    window['table_type'] = '&table_type=list';
    loadItems();
  });

// SHOW GRID VIEW
  $('div.can-collapse span.show_grid').on('click', function(){
    $(this).addClass('active');
    $('span.show_list').removeClass('active');
    localStorage.setItem("show_type", "grid");
    window['table_type'] = '&table_type=grid';
    loadItems();
  });

// SHOW VIEW DEPENDING ON LOCAL STORAGE VALUE
  if (localStorage.getItem('show_type') === 'list') {
    $('span.show_list').addClass('active');
    $('span.show_grid').removeClass('active');
    window['table_type'] = '&table_type=list';
  }
  if (localStorage.getItem('show_type') === 'grid') {
    $('span.show_grid').addClass('active');
    $('span.show_list').removeClass('active');
    window['table_type'] = '&table_type=grid';
  }

  window['skr'] = 5;
  window['pcd'] = 112;
  window['d3'] = 16;
  window['wid'] = 6;
  window['wid2'] = 8;
  window['et'] = 0;
  window['et2'] = 0;
  window['rim_center'] = 'Visi';

  $('.filter-button').on('click', function(e) {
    e.preventDefault();
    $('#search_filters #show-selected-checkbox').attr('checked', false).prop('checked', false).next().children().css('display', 'none');
    window['selected_rims'] = '';
    window['show_selected'] = '';
    window['page'] = '';
    selectedSize = true;
    pageNr = 1;
    loadItems();
  });

  function handlePaginationClick(pageId) {
    // Update the current page
    pageNr = pageId;
    window['selected_tires'] = '';
    window['page'] = '&page=' + pageNr;
    // Call your loadItems function with the new page
    loadItems();
  }
  $(document).on('click', '.pagination a[data-page]', function (e) {
    e.preventDefault();
    pageNr = $(this).data('page');
    handlePaginationClick(pageNr);
  });
  // Previous button click event
  $(document).on('click', '.pagination .previous', function (e) {
    e.preventDefault();
    if (pageNr > 1) {
      pageNr--;
      handlePaginationClick(pageNr);
    }
  });
  // Next button click event
  $(document).on('click', '.pagination .next', function (e) {
    e.preventDefault();
    pageNr++;
    handlePaginationClick(pageNr);
  });

  $('#search_filters #show-selected-checkbox').on('click', function(e) {
    e.preventDefault();

    $(this).attr('checked', function(i, value) {
      if (value === undefined) {
        window['show_selected'] = '&show_selected=show';
        $(this).next().children().css('display', 'block');
        return 'checked';
      } else {
        window['show_selected'] = '';
        $(this).next().children().css('display', 'none');
        return null;
      }
    });

    loadItems();
  });

  function loadItems()
  {
    $('.loading-block-content').fadeIn();

    syncFilterStateFromSelects();

    let url = '/api/rims/auto?' + 'currentSkr=' + window['skr'] + '&currentPcd=' + window['pcd'] + '&currentDia=' + window['d3'] + '&currentWid=' + window['wid'] + '&currentWid2=' + window['wid2'] + '&currentEt=' + window['et'] + '&currentEt2=' + window['et2'] + '&currentCenter=' + window['rim_center'] + window['selected_rims'] + window['show_selected'] + window['table_type'] + window['page'];

    if (pageLoaded === 0) {
      url = '/api/rims/auto?' + window['table_type'];
    }

    $.ajax({
      url: url,
      method: 'GET',
      success: function(data) {
        $('#js-product-list .products').html(data);
        finalizeRimList();
      }
    });


    newUrl = '/lietie-diski/search?' + 'currentSkr=' + window['skr'] + '&currentPcd=' + window['pcd'] + '&currentDia=' + window['d3'] + '&currentWid=' + window['wid'] + '&currentWid2=' + window['wid2'] + '&currentEt=' + window['et'] + '&currentEt2=' + window['et2'] + '&currentCenter=' + window['rim_center'] + window['selected_rims'] + window['show_selected'] + window['table_type'] + window['page'];

    if (pageLoaded === 1) {
      history.pushState({ prevUrl: document.referrer }, '', newUrl);
    }

    pageLoaded = 1;
  }

  function getURLParameters(url) {
    let params = {};
    let urlParts = url.split('?');
    if (urlParts.length > 1) {
      let paramString = urlParts[1];
      let paramPairs = paramString.split('&');
      paramPairs.forEach(pair => {
        let [key, value] = pair.split('=');
        params[key] = value;
      });
    }
    return params;
  }

  window.onpopstate = function (event) {
    pageLoaded = 0;
    // Call your function to load items (assuming this is what loadItems() does)
    loadItems();
  };

  if (canUseServerRenderedList()) {
    syncFilterStateFromSelects();
    pageLoaded = 1;
    finalizeRimList();
  } else {
    loadItems();
  }
});
