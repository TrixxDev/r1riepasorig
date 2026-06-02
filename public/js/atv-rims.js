/**
 * Kvadraciklu diski catalog UX — aligned with lietie-diski sidebar/list/grid patterns.
 */
(function ($) {
  'use strict';

  var STORAGE_PREFIX = 'atvRims.';
  var root;

  function readJson(key, fallback) {
    try {
      var raw = localStorage.getItem(STORAGE_PREFIX + key);
      if (!raw) return fallback;
      return JSON.parse(raw);
    } catch (e) {
      return fallback;
    }
  }

  function writeJson(key, val) {
    try {
      localStorage.setItem(STORAGE_PREFIX + key, JSON.stringify(val));
    } catch (e) { /* noop */ }
  }

  function getSelectedIds() {
    var all = readJson('selected', []);
    if (!Array.isArray(all)) return [];
    return all.map(Number).filter(function (n) { return !isNaN(n); });
  }

  function setSelectedIds(ids) {
    var uniq = {};
    ids.forEach(function (id) { uniq[String(id)] = Number(id); });
    writeJson('selected', Object.keys(uniq).map(function (k) { return uniq[k]; }));
  }

  function syncCheckboxes(ids) {
    root.find('.js-atv-select').each(function () {
      var rid = parseInt(this.value || this.getAttribute('data-atv-rim-row'), 10);
      this.checked = ids.indexOf(rid) !== -1;
      var $row = $(this).closest('.js-atv-rim-row, tr.tire-table-row');
      $row.toggleClass('selected', this.checked);
    });
  }

  function updateShowSelectedControl(ids) {
    var $filter = $('#show-selected-checkbox');
    if (!$filter.length) return;

    if (ids.length > 0) {
      $filter.prop('disabled', false);
    } else {
      $filter.prop('disabled', true).prop('checked', false);
      $filter.next().children().css('display', 'none');
      root.removeClass('atv-hide-unselected');
      root.find('.js-atv-rim-row.atv-suppress').removeClass('atv-suppress');
    }
  }

  function applySelectedFilter() {
    var showOnly = $('#show-selected-checkbox').is(':checked');
    var ids = getSelectedIds();

    if (showOnly && ids.length > 0) {
      root.addClass('atv-hide-unselected');
      root.find('.js-atv-rim-row').each(function () {
        var rid = parseInt(this.getAttribute('data-atv-rim-row'), 10);
        $(this).toggleClass('atv-suppress', ids.indexOf(rid) === -1);
      });
    } else {
      root.removeClass('atv-hide-unselected');
      root.find('.js-atv-rim-row.atv-suppress').removeClass('atv-suppress');
    }
  }

  function applyViewMode() {
    var isGrid = localStorage.getItem('show_type') === 'grid';

    root.toggleClass('atv-mode-grid', isGrid);
    root.toggleClass('atv-mode-list', !isGrid);

    if (isGrid) {
      $('span.show_grid').addClass('active');
      $('span.show_list').removeClass('active');
    } else {
      $('span.show_list').addClass('active');
      $('span.show_grid').removeClass('active');
    }
  }

  function initTablesorter() {
    root.find('.rims-sorter').each(function () {
      if ($(this).data('tablesorter')) {
        return;
      }
      $(this).tablesorter({
        headers: {
          0: { sorter: false },
          1: { sorter: false },
          2: { sorter: false },
          3: { sorter: false },
          4: { sorter: false },
          5: { sorter: false },
          6: { sorter: true },
          7: { sorter: true },
          8: { sorter: false },
          9: { sorter: false },
          10: { sorter: true }
        }
      });
    });

    var $pagination = root.find('.pagination-col');
    if ($pagination.length) {
      $pagination.insertAfter(root.find('#tires-table:last-child'));
    }
  }

  function onSelectionChange() {
    var ids = getSelectedIds();
    syncCheckboxes(ids);
    updateShowSelectedControl(ids);
    applySelectedFilter();
  }

  function handleAtvRimsCartClick(e) {
    var btn = e.target.closest('.cart-shopping-button, .grid-buy-btn');
    if (!btn || !root[0].contains(btn)) {
      return;
    }

    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    var rimId = btn.getAttribute('data-info') || $(btn).data('info');
    if (!rimId) {
      return;
    }

    if (typeof admin !== 'undefined' && admin) {
      var $button = $(btn);
      var $row = $button.closest('tr');
      var $link = $row.length ? $row.find('.table-tire-name-cell a') : $button.closest('.js-atv-rim-row');
      var article = ($link.data('article') || $button.closest('.js-atv-rim-row').data('article') || '').toString().trim();
      if (!article) {
        article = 'no_article';
      }

      var content = ($link.data('content') || $link.find('.tippy').attr('data-tippy-content') || $link.text() || '').toString().trim();
      if (!content) {
        content = $button.closest('.tire-image-card').find('.card-title-text').text().trim();
      }

      var quantityValue = parseInt($link.data('quantity'), 10) || 1;
      var priceHtml = ($row.find('.tire-price-red, .sale-price').first().text() || $button.closest('.tire-image-card').find('.rim-price-red').text() || '').trim();
      var priceNumeric = priceHtml.replace(/€\s*/g, '').trim();
      var currentUser = (typeof user !== 'undefined' ? user : '');

      if (typeof addEntry === 'function') {
        addEntry({
          article: article,
          qty: quantityValue,
          user: currentUser,
          prod: content,
          price: priceNumeric
        });
      }
      if (typeof popCalc === 'function') {
        popCalc('/testing3', 1200, 750);
      }
      return;
    }

    if (typeof addRimToCart === 'function') {
      addRimToCart(rimId, 1);
    } else {
      console.error('ATV rims: addRimToCart nav atrasts — pārbaudiet rimAjax.js');
    }
  }

  $(document).ready(function () {
    root = $('#atv-rims-catalog');
    if (!root.length) return;

    if (!localStorage.getItem('show_type')) {
      localStorage.setItem('show_type', 'list');
    }

    applyViewMode();
    syncCheckboxes(getSelectedIds());
    updateShowSelectedControl(getSelectedIds());
    applySelectedFilter();
    initTablesorter();

    $('div.can-collapse span.show_list').on('click.atvRims', function () {
      localStorage.setItem('show_type', 'list');
      applyViewMode();
    });

    $('div.can-collapse span.show_grid').on('click.atvRims', function () {
      localStorage.setItem('show_type', 'grid');
      applyViewMode();
    });

    root.on('change', '.js-atv-select', function () {
      var ids = getSelectedIds();
      var rid = parseInt(this.value, 10);
      if (isNaN(rid)) return;

      if (this.checked) {
        if (ids.indexOf(rid) === -1) ids.push(rid);
      } else {
        ids = ids.filter(function (id) { return id !== rid; });
      }

      setSelectedIds(ids);
      onSelectionChange();
    });

    $('#show-selected-checkbox').on('click.atvRims', function (e) {
      e.preventDefault();
      if ($(this).prop('disabled')) return;

      var checked = !$(this).is(':checked');
      $(this).prop('checked', checked);
      $(this).next().children().css('display', checked ? 'block' : 'none');
      applySelectedFilter();
    });

    $('#autofind_sub.filter-button').on('click.atvRims', function () {
      $('#show-selected-checkbox').prop('checked', false).next().children().css('display', 'none');
      root.removeClass('atv-hide-unselected');
    });

    root[0].addEventListener('click', handleAtvRimsCartClick, true);
  });
})(jQuery);
