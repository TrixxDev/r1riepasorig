$(document).ready(function() {

  let retryCount = 3;
  let timeout = 30000;

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

  function hasDualSizeCatalog() {
    return $('#dual-size-toggle').length > 0;
  }

  function isDualSizeEnabled() {
    if (!hasDualSizeCatalog()) {
      return false;
    }

    const $toggle = $('#dual-size-toggle');
    return $toggle.is(':checked');
  }

  function isDualSizePairMode() {
    if (!isDualSizeEnabled()) {
      return false;
    }

    return $('.dual-size-tables-stack, .dual-size-grid-stack').length > 0;
  }

  function resolveCartAddQuantity() {
    return isDualSizePairMode() ? 2 : 4;
  }

  function resolveCatalogSegment() {
    const searchForm = document.querySelector('#search_filters_wrapper form');
    if (searchForm) {
      const actionPath = (searchForm.getAttribute('action') || '')
        .replace(/^\//, '')
        .replace(/\/search\/?$/, '');
      if (actionPath) {
        return actionPath;
      }
    }

    const pathParts = window.location.pathname.split('/').filter(Boolean);
    if (pathParts[0] === 'vasaras-riepas' || pathParts[0] === 'ziemas-riepas') {
      return pathParts[0];
    }

    return 'vasaras-riepas';
  }

  let catalogSegment = resolveCatalogSegment();
  const initialDualSizeEnabled = hasDualSizeCatalog() && (
    $('#dual-size-toggle').is(':checked')
    || $.urlParam('dual') === '1'
    || ($.urlParam('d1b') !== null && $.urlParam('d1b') !== 'Visi')
  );
  let season;
  window['fastsearch'] = '';
  window['fastsearchInput'] = '';
  window['newUrl'] = '';
  window['availability'] = ($.urlParam('availability') !== null) ? '&availability=' + $.urlParam('availability') : '';
  window['code'] = ($.urlParam('code') !== null) ? '&code=' + $.urlParam('code') : '';
  window['type'] = ($.urlParam('type') !== null) ? '&type=' + $.urlParam('type') : '';
  window['fuelEco'] = ($.urlParam('fuel') !== null) ? '&fuel=' + $.urlParam('fuel') : '';
  window['wetRoad'] = ($.urlParam('wet') !== null) ? '&wet=' + $.urlParam('wet') : '';
  window['noise'] = ($.urlParam('noise') !== null) ? '&noise=' + $.urlParam('noise') : '';
  window['selected_tires'] = ($.urlParam('selected') !== null) ? '&selected=' + $.urlParam('selected') : '';
  window['table_type'] = '&table_type=list';
  window['top_enabled'] = ($.urlParam('top') !== null) ? '&top=' + $.urlParam('top') : '';
  let pageNr = 1;
  window['page'] = ($.urlParam('page') !== null) ? '&page=' + $.urlParam('page') : '';
  let pageLoaded;
  let tires_array = [];
  window['show_selected'] = ($.urlParam('show_selected') !== null) ? '&show_selected=' + $.urlParam('show_selected') : '';
  let clickedTop = ($.urlParam('top') !== null);

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

  // $(document).on('keydown', function(e) {
  //   if (e.ctrlKey && e.key === " ") {
  //     $('.fastsearch-modal').fadeIn();
  //   }
  //   if (e.key === "Escape") {
  //     $('.fastsearch-modal').fadeOut().find('.r1-select-input').val('');
  //   }
  // })

  function selectOptionById($select, id) {
    const value = id == null || id === '' ? '' : String(id);
    $select.find('option').prop('selected', false);

    if (value === '' || value === 'Visi') {
      return value;
    }

    let $option = $select.find('option[id="' + value.replace(/"/g, '\\"') + '"]');

    if (!$option.length && /C$/i.test(value)) {
      $option = $select.find('option[id="' + value.replace(/C$/i, '') + '"]');
    }

    if (!$option.length && /^\d+$/i.test(value)) {
      $option = $select.find('option[id="' + value + 'C"]');
    }

    if ($option.length) {
      $option.prop('selected', true);
      return $option.val();
    }

    return value;
  }

  let brand;
  let selectedSize = true;
  window['d1'] = $.urlParam('d1') || 205;
  window['d2'] = $.urlParam('d2') || 55;
  window['d3'] = $.urlParam('d3') || 16;
  window['d1b'] = initialDualSizeEnabled ? ($.urlParam('d1b') || 'Visi') : '';
  window['d2b'] = initialDualSizeEnabled ? ($.urlParam('d2b') || 'Visi') : '';
  window['d3b'] = initialDualSizeEnabled ? ($.urlParam('d3b') || 'Visi') : '';

  if (initialDualSizeEnabled && /C$/i.test(String(window['d3b']))) {
    const rearRadiusBase = String(window['d3b']).replace(/C$/i, '');
    if ($('select.tire-radius-b option[id="' + rearRadiusBase + '"]').length) {
      window['d3b'] = rearRadiusBase;
    }
  }

  function readPrimarySizeFromForm() {
    window['d1'] = $('select.tire-width option:selected').val();
    window['d2'] = $('select.tire-height option:selected').val();
    window['d3'] = $('select.tire-radius option:selected').val();
  }

  function readSecondarySizeFromForm() {
    if ($('select.tire-width-b').length) {
      window['d1b'] = $('select.tire-width-b option:selected').attr('id') || $('select.tire-width-b option:selected').val();
      window['d2b'] = $('select.tire-height-b option:selected').attr('id') || $('select.tire-height-b option:selected').val();
      window['d3b'] = $('select.tire-radius-b option:selected').attr('id') || $('select.tire-radius-b option:selected').val();
      return;
    }

    window['d1b'] = 'Visi';
    window['d2b'] = 'Visi';
    window['d3b'] = 'Visi';
  }

  function fixFastsearchSelect2Width() {
    $('.dual-size-fastsearch-wrap .select2-container').css('width', '100%');
  }

  function syncFastsearchVisibility() {
    const $fastsearchWrap = $('.dual-size-fastsearch-wrap');

    if (!$fastsearchWrap.length) {
      return;
    }

    if (isDualSizeEnabled()) {
      window['fastsearch'] = '';
      window['fastsearchInput'] = '';
      $('select.r1-select-input').val([]).trigger('change');
      $fastsearchWrap.hide();
      return;
    }

    $fastsearchWrap.show();
    fixFastsearchSelect2Width();
  }

  function syncDualSizePanel(animate) {
    const $block = $('.dual-size-second-block');
    const $toggle = $('#dual-size-toggle');

    if (!$toggle.length) {
      return;
    }

    syncFastsearchVisibility();

    if (isDualSizeEnabled()) {
      if (animate) {
        $block.stop(true, true).slideDown(180);
      } else {
        $block.show();
      }
      readSecondarySizeFromForm();
    } else {
      if (animate) {
        $block.stop(true, true).slideUp(180);
      } else {
        $block.hide();
      }
      window['d1b'] = '';
      window['d2b'] = '';
      window['d3b'] = '';
    }
  }

  selectOptionById($('select.tire-width'), window['d1']);
  selectOptionById($('select.tire-height'), window['d2']);
  window['d3'] = selectOptionById($('select.tire-radius'), window['d3']);
  if (initialDualSizeEnabled) {
    if ($.urlParam('d1b') !== null) {
      selectOptionById($('select.tire-width-b'), window['d1b']);
    }
    if ($.urlParam('d2b') !== null) {
      selectOptionById($('select.tire-height-b'), window['d2b']);
    }
    if ($.urlParam('d3b') !== null) {
      window['d3b'] = selectOptionById($('select.tire-radius-b'), window['d3b']);
    }
  }

  $('.filter-button').on('click', function(e) {
    e.preventDefault();
    readPrimarySizeFromForm();
    if (isDualSizeEnabled()) {
      readSecondarySizeFromForm();
    }
    catalogSegment = resolveCatalogSegment();
    window['availability'] = '';
    window['code'] = '';
    window['type'] = '';
    window['fuelEco'] = '';
    window['wetRoad'] = '';
    window['noise'] = '';
    window['selected_tires'] = '';
    window['show_selected'] = '';
    window['fastsearch'] = '';
    window['fastsearchInput'] = '';
    $('.custom-checkbox input').not(':first').removeAttr('checked').prop('checked', false);
    syncShowSelectedCheckboxState();
    window['page'] = '';
    selectedSize = true;
    pageNr = 1;
    requestWithRetry();
    if (window['top_enabled'].length <= 0) {
      if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        $('.mobile-filter-modal #search_filters #show-top-checkbox').trigger('click').attr('checked', true).prop('checked', true);
      } else {
        $('#search_filters_wrapper #search_filters #show-top-checkbox').trigger('click').attr('checked', true).prop('checked', true);
      }
    }
  });

  $('select.r1-select-input').select2({
    language: 'lv',
    maximumSelectionLength: 1,
    placeholder: "Ātrā meklēšana",
    data: sizes,
    width: '100%',
  }).on("select2:select", async function () {
    $('.loading-block-content').fadeIn();
    $('.select2-container').removeClass('select2-container--focus').removeClass('select2-container--open');
    $('textarea.select2-search__field').blur();
    window['availability'] = '';
    window['code'] = '';
    window['type'] = '';
    window['fuelEco'] = '';
    window['wetRoad'] = '';
    window['noise'] = '';
    window['selected_tires'] = '';
    window['show_selected'] = '';
    window['fastsearchInput'] = $(this).val();
    syncShowSelectedCheckboxState();

    try {
      const response = await $.get('/api/tires/autoSplitInput/' + window['fastsearchInput']);
      window['d1'] = response.d1;
      window['d2'] = response.d2;
      window['d3'] = response.d3;

    $('select.tire-width option, select.tire-height option, select.tire-radius option').removeAttr('selected').prop('selected', false);

    selectOptionById($('select.tire-width'), window['d1']);
    selectOptionById($('select.tire-height'), window['d2']);
    window['d3'] = selectOptionById($('select.tire-radius'), window['d3']);

      $('select.r1-select-input').val([]).trigger('change');
    } catch (error) {
      console.error('Kļūda saņemot datus par izmēru:', error);
    }

    $('.custom-checkbox input').not(':first').removeAttr('checked').prop('checked', false);
    window['fastsearch'] = '&fastsearch=' + window['fastsearchInput'];
    window['page'] = '';
    selectedSize = true;
    pageNr = 1;
    if (window['fastsearchInput'].length > 0) {
      requestWithRetry();
      if (window['top_enabled'].length <= 0) {
        if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
          $('.mobile-filter-modal #search_filters #show-top-checkbox').trigger('click').attr('checked', true).prop('checked', true);
        } else {
          $('#search_filters_wrapper #search_filters #show-top-checkbox').trigger('click').attr('checked', true).prop('checked', true);
        }
      }
    }
  });

  fixFastsearchSelect2Width();

  if ($('#dual-size-toggle').length) {
    if (initialDualSizeEnabled) {
      $('#dual-size-toggle').prop('checked', true);
    }
    syncDualSizePanel(false);
  }

  $('select.tire-width, select.tire-height, select.tire-radius').on('change', function() {
    window['d1'] = $('select.tire-width option:selected').val();
    window['d2'] = $('select.tire-height option:selected').val();
    window['d3'] = $('select.tire-radius option:selected').val();
  });

  $('select.tire-width-b, select.tire-height-b, select.tire-radius-b').on('change', function() {
    if (!isDualSizeEnabled()) {
      return;
    }
    readSecondarySizeFromForm();
  });

  $('#dual-size-toggle').on('change', function() {
    syncDualSizePanel(true);
    catalogSegment = resolveCatalogSegment();
    pageNr = 1;
    window['page'] = '';
    requestWithRetry();
  });

  function dualSizeQuery() {
    if (!isDualSizeEnabled()) {
      return '';
    }
    readSecondarySizeFromForm();
    return '&d1b=' + window['d1b'] + '&d2b=' + window['d2b'] + '&d3b=' + window['d3b'] + '&dual=1';
  }

  function buildCatalogSearchUrl() {
    catalogSegment = resolveCatalogSegment();
    return '/' + catalogSegment + '/search?' + brand + 'd1=' + window['d1'] + '&d2=' + window['d2'] + '&d3=' + window['d3'] + dualSizeQuery() + window['availability'] + window['code'] + window['type'] + window['fuelEco'] + window['wetRoad'] + window['noise'] + window['selected_tires'] + window['show_selected'] + window['top_enabled'] + window['page'];
  }

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

  $('ul#facet_code li label .custom-checkbox input').on('change', function() {
    let selectedCodes = [];

    // Find all checked checkboxes with the class "custom-checkbox" and collect their values
    $('ul#facet_code input[data-for="prod-code"]:checked').each(function() {
      selectedCodes.push($(this).val());
    });

    // Build a parameter string based on selected values
    window['code'] = selectedCodes.length > 0 ? '&code=' + selectedCodes.join('+') : '';

    window['page'] = '&page=1';
    pageNr = 1;
    requestWithRetry();
  });

  $('ul#facet_type li label .custom-checkbox input').on('change', function() {
    let selectedTypes = [];

    // Find all checked checkboxes with the class "custom-checkbox" and collect their values
    $('ul#facet_type input[data-for="prod-type"]:checked').each(function() {
      selectedTypes.push($(this).val());
    });

    // Build a parameter string based on selected values
    window['type'] = selectedTypes.length > 0 ? '&type=' + selectedTypes.join('+') : '';

    window['page'] = '&page=1';
    pageNr = 1;
    requestWithRetry();
  });

  $('ul#facet_fuel_eco li label .custom-checkbox input').on('change', function() {
    let selectedFuels = [];

    // Find all checked checkboxes with the class "custom-checkbox" and collect their values
    $('ul#facet_fuel_eco input[data-for="fuel_efficiency"]:checked').each(function() {
      selectedFuels.push($(this).val());
    });

    // Build a parameter string based on selected values
    window['fuelEco'] = selectedFuels.length > 0 ? '&fuel=' + selectedFuels.join('+') : '';

    window['page'] = '&page=1';
    pageNr = 1;
    requestWithRetry();
  });

  $('ul#facet_wet li label .custom-checkbox input').on('change', function() {
    let selectedWet = [];

    // Find all checked checkboxes with the class "custom-checkbox" and collect their values
    $('ul#facet_wet input[data-for="wet_grip"]:checked').each(function() {
      selectedWet.push($(this).val());
    });

    // Build a parameter string based on selected values
    window['wetRoad'] = selectedWet.length > 0 ? '&wet=' + selectedWet.join('+') : '';

    window['page'] = '&page=1';
    pageNr = 1;
    requestWithRetry();
  });

  $('ul#facet_noise li label .custom-checkbox input').on('change', function() {
    let selectedNoises = [];

    // Find all checked checkboxes with the class "custom-checkbox" and collect their values
    $('ul#facet_noise input[data-for="noise"]:checked').each(function() {
      selectedNoises.push($(this).val());
    });

    // Build a parameter string based on selected values
    window['noise'] = selectedNoises.length > 0 ? '&noise=' + selectedNoises.join('+') : '';

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

  function getBrandQuery() {
    let selectedBrand = $('select.tire-brand option:selected').val();
    return (selectedBrand === 'Ražotājs') ? '' : 'brand=' + selectedBrand + '&';
  }

  function buildFilterQuerySuffix() {
    return window['availability'] + window['code'] + window['type'] + window['fuelEco'] + window['wetRoad'] + window['noise'] + window['selected_tires'] + window['show_selected'] + window['top_enabled'] + window['page'];
  }

  $('.season-select .winter-tires-link').on('click', function (e) {
    e.preventDefault();
    readPrimarySizeFromForm();
    window.location.href = '/ziemas-riepas/search?' + getBrandQuery() + 'd1=' + window['d1'] + '&d2=' + window['d2'] + '&d3=' + window['d3'] + buildFilterQuerySuffix();
  });

  $('.season-select .summer-tires-link').on('click', function (e) {
    e.preventDefault();
    readPrimarySizeFromForm();
    window.location.href = '/vasaras-riepas/search?' + getBrandQuery() + 'd1=' + window['d1'] + '&d2=' + window['d2'] + '&d3=' + window['d3'] + buildFilterQuerySuffix();
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
    window['newUrl'] = buildCatalogSearchUrl();

    if (pageLoaded === 1) {
      history.pushState({prevUrl: document.referrer}, '', window['newUrl']);
      if (hadShowSelected && window['show_selected'].length === 0) {
        requestWithRetry();
      }
    }
  }

  function drawHTML(data) {

    let tire_availability = [];
    let codes = [];
    let tire_types = [];
    let tire_fuels = [];
    let tire_wets = [];
    let tire_noises = [];

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

    if ($('.category-vasaras-riepas').length >= 1) {
      $('.summer-sorter').not('.dual-size-tires-table').each(function () {
        $(this).tablesorter({
            headers: {
              0: {sorter: false},
              1: {sorter: true},
              2: {sorter: false},
              3: {sorter: false},
              4: {sorter: false},
              5: {sorter: false},
              6: {sorter: false},
              9: {sorter: false},
              10: {sorter: false},
              11: {sorter: false},
              12: {sorter: true}
            },
          }
        );
      });
    } else if ($('.category-ziemas-riepas').length >= 1) {
      $('.summer-sorter').each(function () {
        $(this).tablesorter({
            headers: {
              0: {sorter: false},
              1: {sorter: true},
              2: {sorter: false},
              3: {sorter: false},
              4: {sorter: false},
              5: {sorter: false},
              6: {sorter: false},
              7: {sorter: false},
              8: {sorter: true},
              9: {sorter: true},
              10: {sorter: false},
              11: {sorter: false},
              12: {sorter: true}
            },
          }
        );
      });
    }

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

    $(document).find('input.tire-table-checkbox[name="product_ids[]"]').each(function (key, value) {
      // Если есть data-атрибуты (grid-режим)
      if ($(this).data('code') !== undefined) {
        codes.push($(this).data('code'));
        tire_types.push($(this).data('type'));
        tire_fuels.push($(this).data('fuel'));
        tire_wets.push($(this).data('wet'));
        tire_noises.push(String($(this).data('noise')).charAt(0));
        tire_availability.push($(this).data('availability'));
      } else {
        // Старый способ (list-режим)
        let tire_codes = $(this).parent().parent().find('.code-explain').text();
        if (tire_codes.includes('ACOUSTIC') || tire_codes.includes('NCS') || tire_codes.includes('SCT')) {
          codes.push('SOUND');
        }
        if (tire_codes.includes('HL')) {
          codes.push('XL');
        }
        tire_availability.push($(this).parent().parent().find('.dot.green, .dot.half-green, .dot.yellow, .dot.half-yellow, .dot.red').data('color'));
        codes.push(tire_codes);
        tire_types.push($(this).parent().parent().find('.type-explain').data('type'));
        tire_fuels.push($(this).parent().parent().find('.fuel-explain').text());
        tire_wets.push($(this).parent().parent().find('.wet-explain').text());
        tire_noises.push($(this).parent().parent().find('.noise-explain').text().charAt(0));
      }
      // ON SHOPPING CART BUTTON CLICK
      // Весь функционал теперь только в делегированном обработчике ниже
    });

    let uniqueAvailabilityArray = unique(tire_availability);
    let uniqueCodeArray = unique(codes);
    let uniqueTypeArray = unique(tire_types);
    let uniqueFuelArray = unique(tire_fuels);
    let uniqueWetArray = unique(tire_wets);
    let uniqueNoiseArray = unique(tire_noises);

    if (selectedSize === true) {
      $('ul#facet_availability .custom-checkbox input').attr('disabled', true).prop('disabled', true);
      $('ul#facet_code .custom-checkbox input').attr('disabled', true).prop('disabled', true);
      $('ul#facet_type .custom-checkbox input').attr('disabled', true).prop('disabled', true);
      $('ul#facet_fuel_eco .custom-checkbox input').attr('disabled', true).prop('disabled', true);
      $('ul#facet_wet .custom-checkbox input').attr('disabled', true).prop('disabled', true);
      $('ul#facet_noise .custom-checkbox input').attr('disabled', true).prop('disabled', true);

      $.each(uniqueAvailabilityArray, function (index, value) {
        let facetValue = value;
        if (facetValue === 'half-green') {
          facetValue = 'green';
        } else if (facetValue === 'half-yellow') {
          facetValue = 'yellow';
        }
        $('ul#facet_availability li').each(function () {
          $(this).find('input[data-value="' + facetValue + '"]').removeAttr('disabled').prop('disabled', false);
        })
      });

      $.each(uniqueCodeArray, function (index, value) {
        let text = value.split(' ');
        $.each(text, function (index, value) {
          $('ul#facet_code li').each(function () {
            $(this).find('input[value="' + value + '"]').removeAttr('disabled').prop('disabled', false);
          })
        });
      });

      $.each(uniqueTypeArray, function (index, value) {
        $('ul#facet_type li').each(function () {
          $(this).find('input[value="' + value + '"]').removeAttr('disabled').prop('disabled', false);
        })
      });

      $.each(uniqueFuelArray, function (index, value) {
        $('ul#facet_fuel_eco li').each(function () {
          $(this).find('input[value="' + value + '"]').removeAttr('disabled').prop('disabled', false);
        })
      });

      $.each(uniqueWetArray, function (index, value) {
        $('ul#facet_wet li').each(function () {
          $(this).find('input[value="' + value + '"]').removeAttr('disabled').prop('disabled', false);
        })
      });

      $.each(uniqueNoiseArray, function (index, value) {
        $('ul#facet_noise li').each(function () {
          $(this).find('input[value="' + value + '"]').removeAttr('disabled').prop('disabled', false);
        })
      });

      $('ul#facet_availability .custom-checkbox input:checked').removeAttr('disabled').prop('disabled', false);
      $('ul#facet_code .custom-checkbox input:checked').removeAttr('disabled').prop('disabled', false);
      $('ul#facet_type .custom-checkbox input:checked').removeAttr('disabled').prop('disabled', false);
      $('ul#facet_fuel_eco .custom-checkbox input:checked').removeAttr('disabled').prop('disabled', false);
      $('ul#facet_wet .custom-checkbox input:checked').removeAttr('disabled').prop('disabled', false);
      $('ul#facet_noise .custom-checkbox input:checked').removeAttr('disabled').prop('disabled', false);
      selectedSize = false;
    }

    syncShowSelectedCheckboxState();

    $('.loading-block-content').fadeOut();
  }

  async function fetchWithTimeout() {
    catalogSegment = resolveCatalogSegment();

    $('#show-top-checkbox').removeAttr('disabled').prop('disabled', false);
    $('.loading-block-content').fadeIn();
    season = (catalogSegment.indexOf('vasaras-riepas') === 0) ? 1 : 2;

    brand = $('select.tire-brand option:selected').val();
    brand = (brand === 'Ražotājs') ? '' : 'brand=' + brand + '&';

    let url = '/api/tires/auto/' + season + '?' + brand + 'd1=' + window['d1'] + '&d2=' + window['d2'] + '&d3=' + window['d3'] + dualSizeQuery() + window['fastsearch'] + window['availability'] + window['code'] + window['type'] + window['fuelEco'] + window['wetRoad'] + window['noise'] + window['selected_tires'] + window['show_selected'] + window['top_enabled'] + window['table_type'] + window['page'];

    if (pageLoaded === 0) {
      url = '/api/tires/auto/' + season + '?' + brand + window['fastsearch'] + location.search.slice(1) + window['table_type'] + window['top_enabled'];
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

      // let perfEntries = performance.getEntriesByType('navigation');
      // if (perfEntries.length > 0) {
      //   const latestNavigation = perfEntries[0];
      //   if (latestNavigation.type === 'reload') {
      //     return false;
      //   }
      // }

      if (clickedTop === false) {
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
          $('.mobile-filter-modal #search_filters #show-top-checkbox').trigger('click');
        } else {
          $('#search_filters_wrapper #search_filters #show-top-checkbox').trigger('click');
        }
        clickedTop = true;
      }

      window['newUrl'] = buildCatalogSearchUrl();

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
        localStorage.setItem('autoError', error);
        console.warn(`⏳ Pieprasījuma kļūda, ${attempt} mēģinājums nav izdevies.`);
      }

      if (attempt < retryCount) {
        await new Promise(resolve => setTimeout(resolve, 3000)); // Ждем 3 секунды перед новым запросом
      }
    }

    console.error(`❌ Nav izdevies izpildīt pieprasījumu no ${retryCount} reizēm.`);
    $('.loading-block-content').fadeOut();
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

    if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
      if (clickedTop) {
        $('.mobile-filter-modal #search_filters #show-top-checkbox').attr('checked', true).prop('checked', true);
      } else {
        $('.mobile-filter-modal #search_filters #show-top-checkbox').removeAttr('checked').removeProp('checked');
      }
    } else {
      if (clickedTop) {
        $('#search_filters_wrapper #search_filters #show-top-checkbox').attr('checked', true).prop('checked', true);
      } else {
        $('#search_filters_wrapper #search_filters #show-top-checkbox').removeAttr('checked').removeProp('checked');
      }
    }

    pageLoaded = 0;
    // Call your function to load items (assuming this is what loadItems() does)
    requestWithRetry();
  };

  requestWithRetry();

  const $blockcartModal = $('#blockcart-modal');
  let blockcartSingleMarkup = null;

  function cacheBlockcartSingleMarkup() {
    if (blockcartSingleMarkup || !$blockcartModal.length) {
      return;
    }

    blockcartSingleMarkup = {
      title: $blockcartModal.find('.modal-title').html(),
      leftRow: $blockcartModal.find('.col-md-5 > .row').html(),
    };
  }

  function resetBlockcartSingleMarkup() {
    if (!blockcartSingleMarkup || !$blockcartModal.length) {
      return;
    }

    $blockcartModal.removeClass('blockcart-modal--dual-kit');
    $blockcartModal.find('.modal-title').html(blockcartSingleMarkup.title);
    $blockcartModal.find('.col-md-5 > .row').html(blockcartSingleMarkup.leftRow);
  }

  function normalizeCartImageUrl(image) {
    let imageUrl = image || '/img/p/en-default-home_default.jpg';
    if (String(imageUrl).charAt(0) !== '/' && String(imageUrl).indexOf('http') !== 0) {
      imageUrl = '/' + String(imageUrl).replace(/^\\+/, '');
    }

    return imageUrl;
  }

  function updateCartPreview(data) {
    const cart_quantity = parseInt(data.quantity, 10);
    const total_sum = parseInt(data.total_sum, 10);

    $('.cart-content .cart-products-total').html(total_sum);
    $('.cart-content p.cart-products-count').first().html('Jūsu grozā ir <span class="cart-products-count">' + cart_quantity + '</span> produkti');
    $('span.cart-products-count').html('(' + cart_quantity + ')');
    $('.blockcart.cart-preview').removeClass('inactive').addClass('active');
    $('.blockcart.cart-preview .header').empty();
    $('<a rel="nofollow" href="' + grozs_url + '"><i class="material-icons shopping-cart">shopping_cart</i><span class="hidden-sm-down">Grozs: </span><span class="cart-products-count">(' + cart_quantity + ')</span></a>').appendTo('.blockcart.cart-preview .header');
  }

  function populateBlockcartModalForSingle(tire_id, data) {
    resetBlockcartSingleMarkup();

    const tire = data.cart.products[tire_id];
    if (!tire) {
      return;
    }

    $('.modal-image-preview img').attr('src', normalizeCartImageUrl(tire.image));
    $('.modal-product-info .product-name').html(tire.name);
    $('.modal-product-info .product-price').html(parseInt(tire.price, 10)).attr('data-price', parseInt(tire.price, 10));
    $('.modal-product-info .product-width').html(tire.d1);
    $('.modal-product-info .product-height').html(tire.d2);
    $('.modal-product-info .product-radius').html(tire.d3);
    $('.modal-product-info .product-type').html(tire.type);
    $('.modal-product-info .product-li').html(tire.li);
    $('.modal-product-info .product-si').html(tire.si);
    $('.modal-product-info .product-qty').html(tire.quantity).attr('data-qty', tire.quantity);
  }

  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function buildDualKitSpecRow(label, value) {
    if (!value) {
      return '';
    }

    return ''
      + '<div class="dual-kit-modal__spec">'
      + '  <span class="dual-kit-modal__spec-label">' + escapeHtml(label) + '</span>'
      + '  <span class="dual-kit-modal__spec-value">' + escapeHtml(value) + '</span>'
      + '</div>';
  }

  function buildDualKitItemSpecsHtml(item) {
    const liSi = String(item.li || '') + String(item.si || '');
    const specsHtml = buildDualKitSpecRow('Platums', item.d1)
      + buildDualKitSpecRow('Augstums', item.d2)
      + buildDualKitSpecRow('Diametrs', item.d3)
      + buildDualKitSpecRow('LI/SI', liSi)
      + buildDualKitSpecRow('Tips', item.type)
      + buildDualKitSpecRow('Kods', item.code)
      + buildDualKitSpecRow('Degviela', item.eco)
      + buildDualKitSpecRow('Slapjš segums', item.wet)
      + buildDualKitSpecRow('Trokšnis', item.noise)
      + buildDualKitSpecRow('Piezīmes', item.comment);

    if (!specsHtml) {
      return '';
    }

    return '<div class="dual-kit-modal__specs">' + specsHtml + '</div>';
  }

  function buildDualKitAxisBlockHtml(item, modifier) {
    return ''
      + '<section class="dual-kit-modal__axis-block dual-kit-modal__axis-block--' + modifier + '">'
      + '  <div class="dual-kit-modal__axis-head">'
      + '    <span class="dual-kit-modal__axis">' + escapeHtml(item.axis_label) + '</span>'
      + '    <span class="dual-kit-modal__size">' + escapeHtml(item.size) + '</span>'
      + '  </div>'
      + buildDualKitItemSpecsHtml(item)
      + '  <div class="dual-kit-modal__price-row">'
      + '    <span class="dual-kit-modal__meta">' + item.quantity + ' gab. × € ' + item.price + '</span>'
      + '    <span class="dual-kit-modal__line-total">€ ' + item.line_total + '</span>'
      + '  </div>'
      + '</section>';
  }

  function populateBlockcartModalForDualKit(data) {
    cacheBlockcartSingleMarkup();

    const kit = data.kit;
    if (!kit || !kit.items || !kit.items.length) {
      return;
    }

    const frontItem = kit.items[0];
    const rearItem = kit.items[1] || kit.items[0];
    const heroImage = normalizeCartImageUrl(frontItem.image || rearItem.image);

    $blockcartModal.addClass('blockcart-modal--dual-kit');
    $blockcartModal.find('.modal-title').html(
      '<i class="material-icons dual-kit-modal__title-icon">check_circle</i>'
      + '<span>Komplekts pievienots grozam</span>'
    );

    $blockcartModal.find('.col-md-5 > .row').html(
      '<div class="col-12 dual-kit-modal">'
      + '  <div class="dual-kit-modal__panel">'
      + '    <div class="dual-kit-modal__head">'
      + '      <span class="dual-kit-modal__badge">2+2 komplekts</span>'
      + '      <p class="dual-kit-modal__brand">' + escapeHtml(kit.title) + '</p>'
      + '    </div>'
      + '    <div class="dual-kit-modal__body">'
      + '      <div class="dual-kit-modal__hero">'
      + '        <img loading="lazy" style="width: 100%;" src="' + heroImage + '" alt="">'
      + '      </div>'
      + '      <div class="dual-kit-modal__axes">'
      + buildDualKitAxisBlockHtml(frontItem, 'front')
      + '        <div class="dual-kit-modal__axis-separator" aria-hidden="true"></div>'
      + buildDualKitAxisBlockHtml(rearItem, 'rear')
      + '      </div>'
      + '    </div>'
      + '  </div>'
      + '</div>'
    );
  }

  $blockcartModal.on('hidden.bs.modal', resetBlockcartSingleMarkup);

  $(document).on('click', '.dual-size-kit-cart-button', function(e) {
    e.preventDefault();

    if (admin) {
      return;
    }

    const $button = $(this);
    if ($button.prop('disabled')) {
      return;
    }

    $button.prop('disabled', true);

    $.ajax({
      url: '/' + resolveCatalogSegment() + '/ajax/dual-kit',
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      method: 'POST',
      data: {
        tire_id_a: $button.data('info-a'),
        tire_id_b: $button.data('info-b'),
        tire_url_a: $button.data('url-a'),
        tire_url_b: $button.data('url-b'),
      },
      success: function(data) {
        populateBlockcartModalForDualKit(data);
        updateCartPreview(data);
        $blockcartModal.modal('show');
      },
      complete: function() {
        $button.prop('disabled', false);
      }
    });
  });

  $(document).on('click', '.cart-shopping-button', function() {
    if (!admin) {
      const tire_id = $(this).data('info');
      let ajaxUrl = (season == 1) ? '/vasaras-riepas' : '/ziemas-riepas';
      let tire_url = $(this).data('url');
      const cartQuantity = resolveCartAddQuantity();
      if ($(this).data('link')) {
        ajaxUrl = $(this).data('link');
      }
      $.ajax({
        url: ajaxUrl + '/ajax',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        method: 'POST',
        data: {tire_id: tire_id, tire_url: tire_url, quantity: cartQuantity},
        success: function (data) {
          populateBlockcartModalForSingle(tire_id, data);
          updateCartPreview(data);
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
        qty = $('.table-tire-name-cell a', tire_data).data('quantity') || resolveCartAddQuantity();
        user_val = user;
      } else {
        // Grid
        tire_data = $checkbox.closest('.tire-image-card');
        article = $checkbox.closest('.grid-view-link').data('article');
        prod = tire_data.find('.card-title-text .tippy').data('content');
        price = tire_data.find('.rim-price-red').text().replace('€', '').trim();
        qty = tire_data.closest('.grid-view-link').data('quantity') || resolveCartAddQuantity();
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

  // Обработчик кнопки "Pirkt" на странице товара
  $(document).on('click', '.add-to-cart', function() {
    if (!admin) {
      const tire_id = $(this).data('info');
      let quantity = parseInt($('#quantity_wanted').val()) || 1;
      let tire_url = window.location.href; // Получаем текущий URL страницы
      
      // Определяем правильный URL для AJAX запроса в зависимости от типа страницы
      let ajaxUrl = '/ziemas-riepas/ajax'; // По умолчанию для зимних шин
      if (window.location.pathname.includes('/vasaras-riepas/')) {
        ajaxUrl = '/vasaras-riepas/ajax';
      }

      $.ajax({
        url: ajaxUrl,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        method: 'POST',
        data: {tire_id: tire_id, tire_url: tire_url, quantity: quantity},
        success: function (data) {
          populateBlockcartModalForSingle(tire_id, data);
          updateCartPreview(data);
        }
      });
    }
  });
});
