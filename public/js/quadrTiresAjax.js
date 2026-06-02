$(document).ready(function() {

  let retryCount = 3;
  let timeout = 4000;

  $.urlParam = function(name){
    let results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null) {
      return null;
    }
    return decodeURI(results[1]) || 0;
  }

  function unique(array) {
    return $.grep(array, function(el, index) {
      return index === $.inArray(el, array);
    });
  }

  function addEntry(item) {
    // Parse the JSON stored in allEntries
    let existingEntries = JSON.parse(localStorage.getItem("allEntries"));
    if (existingEntries == null) existingEntries = [];

    // Check if the entry already exists
    // (Modify the criteria based on what constitutes a duplicate)
    const isDuplicate = existingEntries.some(entry =>
      entry.article === item.article &&
      entry.user === item.user
    );

    if (isDuplicate) {
      return false; // Entry already exists
    }

    // If not a duplicate, proceed with adding the entry
    let entry = {
      "article": item.article,
      "qty": item.qty,
      "user": item.user,
      "prod": item.prod,
      "price": item.price,
    };

    existingEntries.push(entry);
    localStorage.setItem("allEntries", JSON.stringify(existingEntries));

    return true; // If you want to return true on successful addition
  }

  $('.loading-block-content').fadeIn();

  const pathParts = window.location.pathname.split('/');
  let season;
  window['fastsearch'] = '';
  window['fastsearchInput'] = '';
  window['newUrl'] = '';
  window['availability'] = ($.urlParam('availability') !== null) ? '&availability=' + $.urlParam('availability') : '';
  window['selected_tires'] = ($.urlParam('selected') !== null) ? '&selected=' + $.urlParam('selected') : '';
  window['table_type'] = '&table_type=list';
  let pageNr = 1;
  window['page'] = ($.urlParam('page') !== null) ? '&page=' + $.urlParam('page') : '';
  window['show_selected'] = ($.urlParam('show_selected') !== null) ? '&show_selected=' + $.urlParam('show_selected') : '';
  window['top_enabled'] = ($.urlParam('top') !== null) ? '&top=' + $.urlParam('top') : '';
  let clickedTop = ($.urlParam('top') !== null);
  let pageLoaded;
  let tires_array = [];

  function syncShowSelectedCheckboxState() {
    const $filters = $('input.show-selected-filter');
    if (window['selected_tires'].length > 0) {
      $filters.removeAttr('disabled').prop('disabled', false);
    } else {
      $filters.attr('disabled', true).prop('disabled', true);
    }

    if (window['show_selected'].length > 0) {
      $filters.attr('checked', true).prop('checked', true);
      $filters.next().children().css('display', 'block');
    } else {
      $filters.removeAttr('checked').prop('checked', false);
      $filters.next().children().css('display', 'none');
    }
  }

  syncShowSelectedCheckboxState();

  // SHOW LIST VIEW
  $('div.can-collapse span.show_list').on('click', function(){
    $(this).addClass('active');
    $('span.show_grid').removeClass('active');
    localStorage.setItem("show_type", "list");
    window['table_type'] = '&table_type=list';
    requestWithRetry();
  });

// SHOW GRID VIEW
  $('div.can-collapse span.show_grid').on('click', function(){
    $(this).addClass('active');
    $('span.show_list').removeClass('active');
    localStorage.setItem("show_type", "grid");
    window['table_type'] = '&table_type=grid';
    requestWithRetry();
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

  let brand;
  let selectedSize = true;

  window['d1'] = $.urlParam('d1') || 25;
  window['d2'] = $.urlParam('d2') || 8;
  window['d3'] = $.urlParam('d3') || 12;

  $('select.tire-width option[id="' + window['d1'] + '"]').attr('selected', true).prop('selected', true);
  $('select.tire-height option[id="' + window['d2'] + '"]').attr('selected', true).prop('selected', true);
  $('select.tire-radius option[id="' + window['d3'] + '"]').attr('selected', true).prop('selected', true);

  $('.filter-button').on('click', function(e) {
    e.preventDefault();
    window['availability'] = '';
    window['fastsearch'] = '';
    window['fastsearchInput'] = '';
    window['selected_tires'] = '';
    window['show_selected'] = '';
    window['page'] = '';
    $('.custom-checkbox input').not(':first').removeAttr('checked').prop('checked', false);
    syncShowSelectedCheckboxState();
    selectedSize = true;
    pageNr = 1;
    requestWithRetry();
    if (window['top_enabled'].length <= 0) {
      $('#search_filters #show-top-checkbox').trigger('click').attr('checked', true).prop('checked', true);
    }
  });

  $('select.r1-select-input').select2(({
    language: 'lv',
    maximumSelectionLength: 1,
    placeholder: "Ātrā meklēšana",
    data: sizes,
  })).on("select2:select", async function () {
    $('.loading-block-content').fadeIn();
    $('.select2-container').removeClass('select2-container--focus').removeClass('select2-container--open');
    $('textarea.select2-search__field').blur();
    window['availability'] = '';
    $('.custom-checkbox input').not(':first').removeAttr('checked').prop('checked', false);
    window['fastsearchInput'] = $(this).val();

    try {
      const response = await $.get('/api/tires/quadrSplitInput/' + window['fastsearchInput']);
      window['d1'] = response.d1;
      window['d2'] = response.d2;
      window['d3'] = response.d3;

      $('select.tire-width option, select.tire-height option, select.tire-radius option').removeAttr('selected').prop('selected', false);

      $('select.tire-width option[id="' + window['d1'] + '"]').attr('selected', true).prop('selected', true);
      $('select.tire-height option[id="' + window['d2'] + '"]').attr('selected', true).prop('selected', true);
      $('select.tire-radius option[id="' + window['d3'] + '"]').attr('selected', true).prop('selected', true);

      $('select.r1-select-input').val([]).trigger('change');
    } catch (error) {
      console.error('Kļūda saņemot datus par izmēru:', error);
    }

    window['fastsearch'] = '&fastsearch=' + window['fastsearchInput'];
    window['selected_tires'] = '';
    window['show_selected'] = '';
    window['page'] = '';
    syncShowSelectedCheckboxState();
    selectedSize = true;
    pageNr = 1;
    if (window['fastsearchInput'].length > 0) {
      requestWithRetry();
      if (window['top_enabled'].length <= 0) {
        $('#search_filters #show-top-checkbox').trigger('click').attr('checked', true).prop('checked', true);
      }
    }
  });

  $('select.tire-width, select.tire-height, select.tire-radius').on('change', function() {
    window['d1'] = $('select.tire-width option:selected').val();
    window['d2'] = $('select.tire-height option:selected').val();
    window['d3'] = $('select.tire-radius option:selected').val();
  });

  function handlePaginationClick(pageId) {
    // Update the current page
    pageNr = pageId;
    window['page'] = '&page=' + pageNr;
    // Call your loadItems function with the new page
    requestWithRetry();
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

  $('ul#facet_availability li label .custom-checkbox input').on('change', function() {
    let selectedColors = [];

    // Check which color checkboxes are checked and add them to the selectedColors array
    if ($('ul#facet_availability input.green').is(':checked')) {
      selectedColors.push('green');
    }
    if ($('ul#facet_availability input.yellow').is(':checked')) {
      selectedColors.push('yellow');
    }
    if ($('ul#facet_availability input.red').is(':checked')) {
      selectedColors.push('red');
    }

    // Build the availability parameter based on selected colors
    window['availability'] = selectedColors.length > 0 ? '&availability=' + selectedColors.join('+') : '';

    window['page'] = '&page=1';
    pageNr = 1;
    requestWithRetry();
  });

  $('#search_filters #show-top-checkbox').on('click', function(e) {
    e.preventDefault();

    $(this).attr('checked', function(i, value) {
      if (value === undefined) {
        window['top_enabled'] = '&top=show';
        $(this).next().children().css('display', 'block');
        return 'checked';
      } else {
        window['top_enabled'] = '';
        $(this).next().children().css('display', 'none');
        return null;
      }
    });

    requestWithRetry();
  });

  $(document).on('click', 'input.show-selected-filter', function(e) {
    e.preventDefault();

    window['show_selected'] = window['show_selected'].length > 0 ? '' : '&show_selected=show';
    syncShowSelectedCheckboxState();
    requestWithRetry();
  });

  function updateSelectedTiresState() {
    const hadShowSelected = window['show_selected'].length > 0;

    if (tires_array.length > 0) {
      window['selected_tires'] = '&selected=' + tires_array.join(',');
    } else {
      window['selected_tires'] = '';
      if (hadShowSelected) {
        window['show_selected'] = '';
      }
    }

    syncShowSelectedCheckboxState();
    window['newUrl'] = '/' + pathParts[1] + '/search?' + brand + 'd1=' + window['d1'] + '&d2=' + window['d2'] + '&d3=' + window['d3'] + window['availability'] + window['selected_tires'] + window['show_selected'] + window['top_enabled'] + window['page'];

    if (pageLoaded === 1) {
      history.pushState({prevUrl: document.referrer}, '', window['newUrl']);
      if (hadShowSelected && window['show_selected'].length === 0) {
        requestWithRetry();
      }
    }
  }

  function drawHTML(data) {

    let tire_availability = [];

    data = $.parseJSON(data);
    $('#js-product-list .products').html(data);

    if (window['selected_tires'].length > 0) {
      tires_array = window['selected_tires'].replace('&selected=', '').split(',');
      $(document).find('th.tire-table-checkbox').children().each(function () {
        $(this).removeAttr('checked').removeProp('checked');
        $(this).parent().parent().removeClass('selected');
      });
      $(document).find('#js-product-list .mobile-tire-container .tire-list-caption input[type=checkbox]').each(function () {
        $(this).removeAttr('checked').removeProp('checked');
        $(this).parent().parent().parent().removeClass('selected');
      });
      $.map(tires_array, function (value, index) {
        $('.tire-table-row .tire-table-checkbox input[value="' + value + '"]').attr('checked', true).prop('checked', true).parent().parent().toggleClass('selected');
        $(document).find('#js-product-list .mobile-tire-container .tire-list-caption input[type=checkbox][value="' + value + '"]').attr('checked', true).prop('checked', true).parent().parent().parent().toggleClass('selected');
      });
    }

    $('.quadr-sorter').each(function () {
      $(this).tablesorter({
          headers: {
            0: {sorter: false},
            1: {sorter: true},
            2: {sorter: false},
            3: {sorter: true},
            4: {sorter: true},
            5: {sorter: false},
            6: {sorter: false},
            7: {sorter: false}
          },
        }
      );
    });

    $($('.pagination-col')).insertAfter($('#tires-table:last-child'));

    // Rādīt izvēlētos (saraksts) start
    $(document).find('th.tire-table-checkbox').children().on('click', function () {
      tires_array = [];
      $(this).parent().parent().toggleClass('selected');
      $(document).find('th.tire-table-checkbox').children(':checked').each(function () {
        tires_array.push($(this).val());
      });

      updateSelectedTiresState();
    });
    // Rādīt izvēlētos (saraksts) end

    // Rādīt izvēlētos (saraksts) start
    if ($('#js-product-list .mobile-tire-container').is(':visible')) {
      $(document).find('#js-product-list .mobile-tire-container .tire-list-caption input[type=checkbox]').on('click', function () {
        tires_array = [];
        $(this).parent().parent().parent().toggleClass('selected');
        $(document).find('#js-product-list .mobile-tire-container .tire-list-caption input[type=checkbox]:checked').each(function () {
          tires_array.push($(this).val());
        });

        updateSelectedTiresState();
      });
    }

    $(document).find('.tire-table-checkbox').each(function (key, value) {
      // Если есть data-атрибуты (grid-режим)
      if ($(this).data('availability') !== undefined) {
        tire_availability.push($(this).data('availability'));
      } else {
        // Старый способ (list-режим)
        tire_availability.push($(this).parent().parent().find('.dot.green, .dot.yellow, .dot.red').data('color'));
      }
    });

    let uniqueAvailabilityArray = unique(tire_availability);

    if (selectedSize === true) {
      $('ul#facet_availability .custom-checkbox input').attr('disabled', true).prop('disabled', true);

      $.each(uniqueAvailabilityArray, function (index, value) {
        $('ul#facet_availability li').each(function () {
          $(this).find('input[data-value="' + value + '"]').removeAttr('disabled').prop('disabled', false);
        })
      });

      $('ul#facet_availability .custom-checkbox input:checked').removeAttr('disabled').prop('disabled', false);
      selectedSize = false;
    }

    syncShowSelectedCheckboxState();

    $('.loading-block-content').fadeOut();
  }

  async function fetchWithTimeout() {

    $('#show-top-checkbox').removeAttr('disabled').prop('disabled', false);
    $('.loading-block-content').fadeIn();

    brand = $('select.tire-brand option:selected').val();
    brand = (brand === 'Ražotājs') ? '' : 'brand=' + brand + '&';

    let url = '/api/tires/quadr/?' + brand + 'd1=' + window['d1'] + '&d2=' + window['d2'] + '&d3=' + window['d3'] + window['fastsearch'] + window['availability'] + window['selected_tires'] + window['show_selected'] + window['top_enabled'] + window['table_type'] + window['page'];

    if (pageLoaded === 0) {
      url = '/api/tires/quadr/?' + brand + window['fastsearch'] + location.search.slice(1) + window['table_type'] + window['top_enabled'];
    }

    const controller = new AbortController();
    const signal = controller.signal;

    const timeoutId = setTimeout(() => {
      console.warn("⏳ Pieprasījuma laiks ir pārsniedzis 3 sekundes un tika atcelts.");
      controller.abort();  // Жесткое прерывание запроса
    }, timeout);

    try {
      const response = await fetch(url, { method: 'GET', signal });
      clearTimeout(timeoutId);  // Отменяем таймер, если запрос успел выполниться

      if (!response.ok) {
        throw new Error(`Servera kļūda: ${response.status}`);
      }

      if (clickedTop === false) {
        $('#search_filters #show-top-checkbox').trigger('click');
        clickedTop = true;
      }

      window['newUrl'] = '/' + pathParts[1] + '/search?' + brand + 'd1=' + window['d1'] + '&d2=' + window['d2'] + '&d3=' + window['d3'] + window['availability'] + window['selected_tires'] + window['show_selected'] + window['top_enabled'] + window['page'];

      if (pageLoaded === 1) {
        history.pushState({prevUrl: document.referrer}, '', window['newUrl']);
      }

      pageLoaded = 1;

      return await response.text();
    } catch (error) {
      if (error.name === 'AbortError') {
        console.warn("❌ Pieprasījums piespiedu kārtā apturēts.");
      } else {
        console.error("Pieprasījuma kļūda:", error);
      }
      throw error;
    }

  }

  async function requestWithRetry() {

    const tbody = $("#tires-table-body");
    tbody.innerHTML = Array(30)
      .fill(0)
      .map(() => `
            <tr class="tire-table-row placeholder" role="row">
                <th scope="row" class="tire-table-checkbox"></th>
                <td class="table-tire-name-cell"></td>
                <td class="hidden-sm-down text-center"></td>
                <td scope="col" class="hidden-sm-down text-center"></td>
                <td class="hidden-sm-down text-center"></td>
                <td class="hidden-sm-down text-center"></td>
                <td class="hidden-sm-down text-center"></td>
                <td class="hidden-sm-down text-center"></td>
                <td id="store-price" class="text-center store-price"></td>
                <td id="sale-price" class="text-center tire-price-red sale-price"></td>
                <td class="hidden-sm-down text-center"></td>
                <td class="shopping-cart-col"></td>
                <td class="dot-availability text-center"></td>
            </tr>
        `)
      .join("");
    tbody.html(tbody.innerHTML)

    for (let attempt = 1; attempt <= retryCount; attempt++) {
      console.log(`🔄 ${attempt} mēģinājums no ${retryCount}`);

      try {

        let response = await fetchWithTimeout(timeout); // Жесткий таймаут 3 секунды

        drawHTML(response);
        return;
      } catch (error) {
        localStorage.setItem('quadrError', error);
        console.warn(`⏳ Pieprasījuma kļūda, ${attempt} mēģinājums nav izdevies.`);
      }

      if (attempt < retryCount) {
        await new Promise(resolve => setTimeout(resolve, 3000)); // Ждем 3 секунды перед новым запросом
      }
    }

    console.error(`❌ Nav izdevies izpildīt pieprasījumu no ${retryCount} reizēm.`);
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

    if (clickedTop) {
      $('#search_filters #show-top-checkbox').attr('checked', true).prop('checked', true);
    } else {
      $('#search_filters #show-top-checkbox').removeAttr('checked').removeProp('checked');
    }

    pageLoaded = 0;
    // Call your function to load items (assuming this is what loadItems() does)
    requestWithRetry();
  };

  requestWithRetry();

  $(document).on('click', '.cart-shopping-button', function() {
    if (!admin) {
      const tire_id = $(this).data('info');
      let ajaxUrl = '/kvadru-riepas';
      let tire_url = $(this).data('url');
      if ($(this).data('link')) {
        ajaxUrl = $(this).data('link');
      }
      $.ajax({
        url: ajaxUrl + '/ajax',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        method: 'POST',
        data: {tire_id: tire_id, tire_url: tire_url},
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
    } else {
      const $checkbox = $(this);
      let tire_data, article, prod, price, qty, user_val;
      if ($checkbox.closest('tr').length) {
        // Таблица
        tire_data = $checkbox.parent().parent().parent();
        article = $('.table-tire-name-cell a', tire_data).data('article');
        prod = $('.table-tire-name-cell a', tire_data).data('content');
        price = $('.tire-price-red', tire_data).html().replace('€ ', '');
        qty = $('.table-tire-name-cell a', tire_data).data('quantity');
        user_val = user;
      } else {
        // Grid
        tire_data = $checkbox.closest('.tire-image-card');
        article = $checkbox.closest('.grid-view-link').data('article');
        prod = tire_data.find('.card-title-text .tippy').data('content');
        price = tire_data.find('.rim-price-red').text().replace('€', '').trim();
        qty = tire_data.find('.grid-view-link').data('quantity') || 4;
        user_val = user;
      }
      if (article && article.length == 0) article = 'no_article';
      $('.popup input[name=prod]').val(prod);
      $('.popup input[name=price]').val(price);
      $('.popup input[name=qty]').val(qty);
      $('.popup input[name=total]').val(parseInt(price) * qty);
      $('.popup input[name=user]').val(user_val).attr('readonly', true).prop('readonly', true);
      $('.popup input[name=article]').val(article);
      calcData = {
        'article': article,
        'qty': qty,
        'user': user_val,
        'prod': prod,
        'price': price,
      }
      addEntry(calcData);
      const urlData = new URLSearchParams(calcData).toString();
      popCalc('/testing3',1200,750);
    }
  });
});
