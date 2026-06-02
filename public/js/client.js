$(document).ready(function() {

  let user = $('.user-info .account').data('user');
  let user_role = $('.user-info .account').data('role');
  let admin = false;
  $.each(user_role, function(key, value) {
    if (value === 'administrators' || value === 'moderators') {
      admin = true;
    }
  });

  $.fn.classChange = function(cb) {
    return $(this).each((_, el) => {
      new MutationObserver(mutations => {
        mutations.forEach(mutation => cb && cb(mutation.target, $(mutation.target).prop(mutation.attributeName)));
      }).observe(el, {
        attributes: true,
        attributeFilter: ['class'] // only listen for class attribute changes
      });
    });
  }

  $(".pace").classChange((el, newClass) => {
      if (newClass) {
        $('body .loading').first().fadeOut(function() { $(this).remove(); });
      }
    }
  );

  // Connect to the WebSocket server
  // const socket = new WebSocket('ws://r1riepas.lv:3500');

  function truncateCharacters(text, limit, ellipsis = '...', strip = 0) {
    if (text.length > limit) {
      text = $.trim(text.substring(0, limit - strip)) + ellipsis;
    }
    return text;
  }

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  function getCarInfoConfig() {
    const $modal = $('#reservation');
    return {
      url: $modal.data('car-info-url') || '',
      token: $modal.find('input[name="car_info_token"]').val() || ''
    };
  }
  let carInfoConfig = getCarInfoConfig();

  let carInfoRequestTimer;
  const carInfoCache = new Map(); // vnr -> { marka, modelis, ts }
  const carInfoInflight = new Map(); // vnr -> jqXHR
  const carInfoSnapshot = new Map(); // vnr -> { json: string, fetchedAt: string }
  const CAR_INFO_CACHE_TTL_MS = 60 * 60 * 1000; // 1 hour
  let lastCarInfoVnr = '';
  let fillSlotRequestSeq = 0;

  function stripReservationModalFinish() {
    $('.modal-body.finish, .modal-footer.finish-footer').remove();
    $('.title-finish').remove();
  }
  // Serialize car-info requests: token is single-use, parallel requests cause 403
  let carInfoRequestQueue = [];
  let carInfoRequestInFlight = false;

  function normalizePlate(raw) {
    return $.trim(String(raw || ''))
      .toUpperCase()
      .replace(/[\s-]+/g, '');
  }

  function isValidPlate(vnr) {
    // CSDD input guidance: enter registration number without spaces (e.g. AA1111).
    // Plate type rules:
    // - General motor vehicles (types A/B/C): 2 Latin letters + 1..4 digits (1..9999); digits may repeat (e.g. 1111).
    // - Trailers: 1 Latin letter + 1..4 digits (1..9999) (some variants may include letters at the end)
    // - Personalised plates: 2..8 symbols, Latin letters and Arabic numerals (or just letters);
    //   letters/numbers in blocks (no A1B2-style interleaving).
    if (!vnr) {
      return false;
    }

    // Hard whitelist and length bounds (personalised max is 8 symbols on type A).
    if (!/^[A-Z0-9]{2,8}$/.test(vnr)) {
      return false;
    }

    // Must contain at least one letter (CSDD personalised allows letters-only; digits-only is not described).
    if (!/[A-Z]/.test(vnr)) {
      return false;
    }

    // Standard passenger vehicle plates (2 letters + 1..4 digits, numeric 1..9999)
    const mStd = vnr.match(/^([A-Z]{2})(\d{1,4})$/);
    if (mStd) {
      const n = parseInt(mStd[2], 10);
      return n >= 1 && n <= 9999;
    }

    // Trailer-like plates (1 letter + 1..4 digits, numeric 1..9999)
    const mTrailer = vnr.match(/^([A-Z])(\d{1,4})$/);
    if (mTrailer) {
      const n = parseInt(mTrailer[2], 10);
      return n >= 1 && n <= 9999;
    }

    // Personalised: either letters only, or letters+digits, or digits+letters (no interleaving).
    return /^[A-Z]{2,8}$/.test(vnr) || /^[A-Z]+\d+$/.test(vnr) || /^\d+[A-Z]+$/.test(vnr);
  }

  function setRegNrValidity(valid) {
    // Only style inputs that exist on the current page.
    const $inputs = $('#reservation #reg_nr, #mobile-reg_nr');
    const $existing = $inputs.filter(function() { return $(this).length > 0; });
    if (valid) {
      $existing.css('border-color', '');
      return;
    }
    $existing.css('border-color', '#e11d48');
    setTimeout(function() {
      $existing.css('border-color', '');
    }, 1200);
  }

  function applyCarInfoToForm(data) {
    if (!data || typeof data !== 'object') {
      return;
    }
    const marka = (data.MARKA ?? data.marka ?? '').toString();
    const modelis = (data.MODELIS ?? data.modelis ?? '').toString();
    if (marka) {
      $('#brand').val(marka);
      $('#mobile-brand').val(marka);
    }
    if (modelis) {
      $('#model').val(modelis);
      $('#mobile-model').val(modelis);
    }
  }

  function processCarInfoQueue() {
  if (carInfoRequestInFlight || carInfoRequestQueue.length === 0) return;
  const item = carInfoRequestQueue.shift();
  carInfoRequestInFlight = true;
  carInfoConfig = getCarInfoConfig();
  const jqXHR = $.ajax({
    url: carInfoConfig.url,
    method: 'POST',
    data: { vnr: item.vnr },
    headers: { 'X-Car-Info-Token': carInfoConfig.token }
  });
  jqXHR.done(function(data, _t, xhr) {
    updateCarInfoTokenFromResponse(xhr);
    item.resolve(data);
  });
  jqXHR.fail(function(xhr) {
    updateCarInfoTokenFromResponse(xhr);
    if (xhr && xhr.status === 403 && !item.retried) {
      item.retried = true;
      carInfoRequestQueue.unshift(item);
    } else {
      item.reject(xhr);
    }
  });
  jqXHR.always(function() {
    carInfoRequestInFlight = false;
    processCarInfoQueue();
  });
}

function performCarInfoRequest(vnr) {
  const d = $.Deferred();
  carInfoRequestQueue.push({ vnr: vnr, resolve: d.resolve, reject: d.reject });
  processCarInfoQueue();
  return d.promise();
}

  function fetchCarInfo(plate) {
    carInfoConfig = getCarInfoConfig();
    if (!carInfoConfig.url || !carInfoConfig.token) {
      console.warn('Car info API is not configured on the page.');
      return;
    }

    const vnr = normalizePlate(plate);
    if (vnr.length < 3) {
      return;
    }
    if (!isValidPlate(vnr)) {
      setRegNrValidity(false);
      return;
    }
    setRegNrValidity(true);

    // Avoid duplicate calls for same value.
    if (vnr === lastCarInfoVnr) {
      const cachedSame = carInfoCache.get(vnr);
      if (cachedSame && (Date.now() - cachedSame.ts) < CAR_INFO_CACHE_TTL_MS) {
        applyCarInfoToForm({ MARKA: cachedSame.marka, MODELIS: cachedSame.modelis });
      }
      return;
    }
    lastCarInfoVnr = vnr;

    // Cache hit.
    const cached = carInfoCache.get(vnr);
    if (cached && (Date.now() - cached.ts) < CAR_INFO_CACHE_TTL_MS) {
      applyCarInfoToForm({ MARKA: cached.marka, MODELIS: cached.modelis });
      return;
    }

    // Inflight dedupe.
    if (carInfoInflight.has(vnr)) {
      return;
    }

    const jqXHR = performCarInfoRequest(vnr);
    carInfoInflight.set(vnr, jqXHR);
    jqXHR.done(function(data) {
      try {
        const marka = (data && (data.MARKA ?? data.marka)) || '';
        const modelis = (data && (data.MODELIS ?? data.modelis)) || '';
        carInfoCache.set(vnr, { marka: String(marka || ''), modelis: String(modelis || ''), ts: Date.now() });
        carInfoSnapshot.set(vnr, { json: JSON.stringify(data || {}), fetchedAt: new Date().toISOString() });
      } catch (_e) {}
      applyCarInfoToForm(data);
    });

    jqXHR.fail(function(xhr) {
      console.warn('car-info error', xhr && xhr.status, xhr && xhr.responseText);
    });
    jqXHR.always(function() {
      carInfoInflight.delete(vnr);
    });
  }

  function ensureCarInfoSnapshotForPlate(plateRaw) {
    const d = $.Deferred();
    const vnr = normalizePlate(plateRaw);

    if (!vnr || !isValidPlate(vnr)) {
      setRegNrValidity(false);
      return d.reject('invalid_plate').promise();
    }
    setRegNrValidity(true);

    const existing = carInfoSnapshot.get(vnr);
    if (existing && existing.json) {
      return d.resolve(existing).promise();
    }

    carInfoConfig = getCarInfoConfig();
    if (!carInfoConfig.url || !carInfoConfig.token) {
      return d.reject('not_configured').promise();
    }

    // If a request is already in flight for this plate, wait for it.
    if (carInfoInflight.has(vnr)) {
      const inflight = carInfoInflight.get(vnr);
      inflight
        .done(function() {
          const snap = carInfoSnapshot.get(vnr);
          if (snap && snap.json) {
            d.resolve(snap);
          } else {
            d.reject('no_snapshot');
          }
        })
        .fail(function() {
          d.reject('api_error');
        });
      return d.promise();
    }

    const jqXHR = performCarInfoRequest(vnr);
    carInfoInflight.set(vnr, jqXHR);
    jqXHR.always(function() {
      carInfoInflight.delete(vnr);
    });
    jqXHR.done(function(data) {
      try {
        carInfoSnapshot.set(vnr, { json: JSON.stringify(data || {}), fetchedAt: new Date().toISOString() });
      } catch (_e) {}
      applyCarInfoToForm(data);
    });

    jqXHR.fail(function() {
      console.warn('car-info error (ensure)');
    });

    jqXHR.done(function() {
      const snap = carInfoSnapshot.get(vnr);
      if (snap && snap.json) {
        d.resolve(snap);
      } else {
        d.reject('no_snapshot');
      }
    });
    jqXHR.fail(function() {
      d.reject('api_error');
    });

    return d.promise();
  }

  function updateCarInfoTokenFromResponse(xhr) {
    if (!xhr) {
      return;
    }
    const nextToken = xhr.getResponseHeader('X-Car-Info-Token');
    if (nextToken) {
      carInfoConfig.token = nextToken;
      $('#reservation input[name="car_info_token"]').val(nextToken);
    }
  }

  function scheduleCarInfoLookup(plate) {
    clearTimeout(carInfoRequestTimer);
    carInfoRequestTimer = setTimeout(function() {
      fetchCarInfo(plate);
    }, 400);
  }

  let iorder;
  let queue_id;
  let date;
  let time;
  let office;
  let allow_all_options;
  let slot;
  let car_brand;
  let phone;
  let rimsWith;
  let temp_nr;
  let selected__office;

  // set the modal menu element
  // const $targetEl = document.getElementById('reservation');

  // // options with default values
  // const options = {
  //     backdrop: 'dynamic',
  //     backdropClasses: 'bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-40',
  //     closable: true,
  //     onHide: () => {
  //         $('body').removeClass('removeScroll');
  //     },
  //     onShow: () => {
  //         $('body').addClass('removeScroll');
  //     },
  //     onToggle: () => {
  //         console.log('modal has been toggled');
  //     }
  // };
  //
  // const modal = new Modal($targetEl, options);

  $(document).on('click', '#reservation #close-modal', function() {
    $('#reservation').modal('hide');
  });

  $(document).on('click', '#reservation .finish-footer #close-modal', function() {
    $('#reservation .rims_with, #reservation .temp_save_nr').hide();
    $('.modal-body.finish, .finish-footer').remove();
    $('.reservation-modal-body').slideDown();
    $('.reservation-modal-footer #submit-reservation').show();
    $('.reservation-modal-footer #close-modal').text('Atcelt');
    $('#reservation #modalTitle.title-finish').remove();
    $('#reservation #modalTitle').slideDown();
  });

  $(document).on('blur', '#reservation #reg_nr', function() {
    scheduleCarInfoLookup($(this).val());
  });

  $(document).on('blur', '#mobile-reservation-form #mobile-reg_nr', function() {
    scheduleCarInfoLookup($(this).val());
  });

  // Also trigger lookup on input (debounced) so user doesn't need to blur
  $(document).on('input', '#reservation #reg_nr, #mobile-reservation-form #mobile-reg_nr', function() {
    scheduleCarInfoLookup($(this).val());
  });

  if (!admin) {
    $(document).on('click', '.time-status.discount', function() {
      let discount_text = $('button.discount-slot', this).text();
      if ($('div.alert.alert-warning.discount-alert').length === 0 ){
        $('.modal-dialog').find('.form-group.services')
          .prepend("<div class='alert alert-warning discount-alert' style='font-size: 14px;'><b>Šajā pieraksta laikā tiek piemērota atlaide (" + discount_text + ")</b></div>");
      }
    });
  } else {
    $('.discount-slot').each(function() {
      if ($(this).parent().data('moto')) {
        $(this).text('Moto montāža').removeClass('discount-slot');
      } else if ($(this).parent().data('ac')) {
        $(this).text('Kondicioniera uzpilde').removeClass('discount-slot');
      } else {
        $(this).text('Brīvs').removeClass('discount-slot');
      }
    })
  }

  $(document).on('click', '.time-status', function() {

    $('#reservation .loader-block').show();

    iorder = $(this).attr('data-iorder');
    queue_id = $(this).parent().attr('data-queue-id');
    date = $(this).parent().parent().attr('data-date');
    time = $(this).children('.time-slot').html();
    office = $(this).parent().attr('class').replace('table office_', '');
    allow_all_options = $(this).parent().data('allow-all');
    slot = $(this);

    if ($(this).hasClass('time-free') || $(this).hasClass('time-offer')) {
      $('#reservation').modal('show');
    }

    setTimeout(function() {
      $('.datedayOfWeek').text(slot.parent().parent().parent().prev().text());
      $('.timeOfDay').text($('.time-slot', slot).text());
      $('.officeTitle').text(slot.parent().children().first().text());

      $.each($('#record-modal .services li'), function(index, value) {
        $(value).find('input').attr('disabled', true).prop('disabled', true).attr('checked', false).prop('checked', false);
      });

      $('#reservation #service .form-check').each(function() {
        $(this).find('input').on('click', function() {
          $('span.service-error').remove();
          switch ($(this).val()) {
            case '1': {
              $('#reservation .rims_with').show();
              $('#reservation .temp_save_nr').hide().val('');
              break;
            }
            case '2': {
              $('#reservation .temp_save_nr').show();
              $('#reservation .rims_with').hide();
              $('#reservation .rims_with input[name="rims_with_input"]').each(function () {
                $(this).attr('selected', false).prop('selected', false);
              });
              break;
            }
            default: {
              $('#reservation .rims_with, #reservation .temp_save_nr').hide();
            }
          }
        })
      });

      $('#reservation .rims_with input[name="rims_with_input"]').each(function() {
        $(this).on('input', function() {
          $('.rimsWith_error').remove();
        })
      })


      if (allow_all_options === false) {
        if ($(slot).attr('data-moto') === 'true') {
          $.each($('#reservation #service .form-check'), function(index, value) {
            $(value).find('input').attr('disabled', true).prop('disabled', true).attr('checked', false).prop('checked', false);
          });
          $('#reservation').find('input[data-moto]').removeAttr('disabled').prop('disabled', false).first().attr('checked', true).prop('checked', true);
        } else if ($(slot).attr('data-ac') === 'true') {
          $.each($('#reservation #service .form-check'), function(index, value) {
            $(value).find('input').attr('disabled', true).prop('disabled', true).attr('checked', false).prop('checked', false);
          });
          $('#reservation').find('input[data-ac]').removeAttr('disabled').prop('disabled', false).attr('checked', true).prop('checked', true);
        } else {
      let __timeSlots = $(slot).parent().parent();
          $.each($('#reservation #service .form-check'), function(index, value) {
            $(value).find('input').attr('disabled', false).prop('disabled', false).attr('checked', false).prop('checked', false);
          });
          
          let motoSlots = $('[data-date="' + date + '"] .table.office_' + office + ' .time-free[data-moto="true"], [data-date="' + date + '"] .table.office_' + office + ' .time-offer[data-moto="true"]');
          if (motoSlots.length > 0) {
            $('#reservation #service').find('input[data-moto]').attr('disabled', true).prop('disabled', true);
          }
          
          let acSlots = $('[data-date="' + date + '"] .table.office_' + office + ' .time-free[data-ac="true"], [data-date="' + date + '"] .table.office_' + office + ' .time-offer[data-ac="true"]');
          if (acSlots.length > 0) {
            $('#reservation #service').find('input[data-ac]').attr('disabled', true).prop('disabled', true);
          }
        }
      } else {
        $.each($('#reservation #service .form-check'), function(index, value) {
          $(value).find('input').attr('disabled', false).prop('disabled', false).attr('checked', false).prop('checked', false);
        });

        let motoSlots = $('[data-date="' + date + '"] .table.office_' + office + ' .time-free[data-moto="true"], [data-date="' + date + '"] .table.office_' + office + ' .time-offer[data-moto="true"]');
        if (motoSlots.length > 0) {
          $('#reservation #service').find('input[data-moto]').attr('disabled', true).prop('disabled', true);
        }
        
        let acSlots = $('[data-date="' + date + '"] .table.office_' + office + ' .time-free[data-ac="true"], [data-date="' + date + '"] .table.office_' + office + ' .time-offer[data-ac="true"]');
        if (acSlots.length > 0) {
          $('#reservation #service').find('input[data-ac]').attr('disabled', true).prop('disabled', true);
        }
      }

      $('#reservation .loader-block').hide();
    }, 0);

  });

  $('#reservation').on('hide.bs.modal', function () {
    $('#reservation form').trigger('reset');
    $('#reservation .alert').remove();
    $('#reservation .rims_with, #reservation .temp_save_nr').hide();
    $('#brand, #model, #phone, #email').removeAttr('placeholder');
    $('body').removeClass('removeScroll');

    $('.modal-body.finish, .modal-footer.finish-footer').remove();
    $('.title-finish').remove();
    $('.reservation-modal-body').show();
    $('#modalTitle').first().show();
    $('.reservation-modal-footer #submit-reservation').show();
    $('.reservation-modal-footer #close-modal').text('Atcelt');
    $('span.service-error, div.rimsWith_error').remove();
  }).on('show.bs.modal', function() {
    $('body').addClass('removeScroll');
  }).on('keypress', function(e) {
    if (e.keyCode === 13) {
      $('#submit-reservation', this).click();
    }
  });;

  $('#reservation button#submit-reservation').on('click', function(e) {
    e.preventDefault();

    car_brand = $('#reservation input#brand').val().replace('&', '');
    car_model = $('#reservation input#model').val().replace('&', '');
    rimsWith = $('#reservation .rims_with input[name="rims_with_input"]:checked').val();
    temp_nr = $('#reservation .temp_save_nr input#save_nr').val();
    phone = $('#reservation input#phone').val().replace(' ', '');
    let lic_plate = $('#reservation input#reg_nr').val();
    let plate = phone.substr(-3);
    plate = parseInt(plate);
    plate = $.trim(plate);
    let service = $('#service').find('input[name="serviceOption"]:checked').val();
    let user_comment = $('#reservation textarea#comment').val();
    let name = $('#reservation input#name').val();
    let email = $('#reservation input#email').val();

    // Client-side validation (needed when Enter triggers submit).
    // If the plate is invalid, the API snapshot call fails early and we never show service errors.
    // Validate all required fields before calling ensureCarInfoSnapshotForPlate().
    $('span.service-error, div.rimsWith_error').remove();
    let hasError = false;

    const vnr = normalizePlate(lic_plate);
    if (!vnr || !isValidPlate(vnr)) {
      setRegNrValidity(false);
      hasError = true;
    } else {
      setRegNrValidity(true);
    }

    if (!service) {
      $('<span class="service-error" style="color: red; opacity: 0.5;">Jāizvēlas viens no pakalpojumiem!</span>')
        .appendTo($('.services #service'));
      hasError = true;
    }

    // Service 1 requires selecting "rims with/without".
    if (String(service) === '1' && (rimsWith === undefined || rimsWith === null || String(rimsWith).trim() === '')) {
      $('<div class="rimsWith_error"><div class="col-sm-3"></div><div class="col-sm-9"><span class="service-error" style="color: red; opacity: 0.5;">Jāizvēlas viena no opcijām!</span></div></div>')
        .appendTo($('.rims_with'));
      hasError = true;
    }

    if (hasError) {
      return;
    }

    $('#reservation .loader-block').show();
    $('#submit-reservation').attr('disabled', true);
    $('#close-modal').attr('disabled', true);

    const normalizedPlate = normalizePlate(lic_plate);

    // Build & submit fillSlot request (optionally with car-info snapshot).
    function submitFillSlot(carInfoSnap) {
      const thisFillSeq = ++fillSlotRequestSeq;
      let formDataPayload = {
        car_brand: car_brand,
        car_model: car_model,
        rimsWith: rimsWith,
        temp_nr: temp_nr,
        lic_plate: lic_plate,
        service: service,
        user_comment: user_comment,
        name: name,
        phone_number: phone,
        email: email,
      };

      if (carInfoSnap && carInfoSnap.json) {
        formDataPayload.car_info_json = carInfoSnap.json;
        formDataPayload.car_info_fetched_at = carInfoSnap.fetchedAt || '';
        formDataPayload.car_info_vnr = normalizedPlate;
        formDataPayload.car_info_source = 'api/car-info';
      }

      let formData = $.param(formDataPayload);

      let dopParams = {
        iorder: iorder,
        queue_id: queue_id,
        date: date,
        time: time,
        office: office,
      };

      $.ajax({
        url: '/pieraksts/fillSlot',
        method: 'POST',
        data: {formData: formData, dopParams: dopParams},
        success: function(data) {
          var payloadImmediate = (typeof data === 'string') ? JSON.parse(data) : data;
          if (thisFillSeq === fillSlotRequestSeq && payloadImmediate.message && payloadImmediate.success) {
            if (window.simpleSlotLock && typeof window.simpleSlotLock.onFillSlotSuccess === 'function') {
              window.simpleSlotLock.onFillSlotSuccess();
            }
          }
          setTimeout(function() {
            if (thisFillSeq !== fillSlotRequestSeq) {
              return;
            }
            var payload = payloadImmediate;

            if (payload.errors) {
              $('span.service-error, div.rimsWith_error').remove();
              $.each(payload.errors, function(index, item) {
                if (index == 'car_brand') {
                  $('#brand').attr('placeholder', 'Jābūt aizpildītam!');
                }
                if (index == 'car_model') {
                  $('#model').attr('placeholder', 'Jābūt aizpildītam!');
                }
                if (index == 'lic_plate') {
                  $('#reg_nr').attr('placeholder', 'Jābūt aizpildītam!');
                }
                if (index == 'service') {
                  $('<span class="service-error" style="color: red; opacity: 0.5;">Jāizvēlas viens no pakalpojumiem!</span>').appendTo($('.services #service'));
                }
                if (index == 'rimsWith') {
                  $('<div class="rimsWith_error"><div class="col-sm-3"></div><div class="col-sm-9"><span class="service-error" style="color: red; opacity: 0.5;">Jāizvēlas viena no opcijām!</span></div></div>').appendTo($('.rims_with'));
                }
                if (index == 'phone_number') {
                  $('#phone').attr('placeholder', 'Jābūt aizpildītam!');
                }
              });
              $('#submit-reservation').removeAttr('disabled');
              $('#close-modal').removeAttr('disabled');
              return;
            }

            stripReservationModalFinish();

            if (payload.alertMessage) {
              $('#submit-reservation').removeAttr('disabled');
              $('#close-modal').removeAttr('disabled');

              $('.reservation-modal-body').slideUp();
              $('#modalTitle').first().slideUp();
              $('<h5 class="modal-title title-finish" id="modalTitle">Pieraksts</h5>').insertAfter('#modalTitle');
              $('.reservation-modal-footer #submit-reservation').hide();
              $('.reservation-modal-footer #close-modal').text('Aizvērt');
              $('<div class="modal-body finish">' + payload.alertMessage + '</div><div class="modal-footer finish-footer"><button type="button" class="btn btn-secondary" id="close-modal" style="margin-right: 10px;">Aizvērt</button></div>').insertAfter($('#modalTitle').parent()).css('display', 'none').slideDown();
              $('#brand, #model, #phone, #email').removeAttr('placeholder');
              $('#reservation form').trigger('reset');
              $('#reservation .rims_with, #reservation .temp_save_nr').hide();
              return;
            }

            if (payload.message && payload.success) {
              $('#submit-reservation').removeAttr('disabled');
              $('#close-modal').removeAttr('disabled');

              let successText = truncateCharacters($.trim(car_brand),8,'&mldr;',1) + ' xxxxx' + plate;

              $('.reservation-modal-body').slideUp();
              $('#modalTitle').first().slideUp();
              $('<h5 class="modal-title title-finish" id="modalTitle">Pieraksts</h5>').insertAfter('#modalTitle');
              $('.reservation-modal-footer #submit-reservation').hide();
              $('.reservation-modal-footer #close-modal').text('Aizvērt');
              $('<div class="modal-body finish">' + payload.message + '</div><div class="modal-footer finish-footer"><button type="button" class="btn btn-secondary" id="close-modal" style="margin-right: 10px;">Aizvērt</button></div>').insertAfter($('#modalTitle').parent()).css('display', 'none').slideDown();
              slot.removeClass('time-free').removeClass('time-offer').addClass('time-taken');
              slot.find('button').fadeOut().remove();
              slot.append('<div class="slot taken-slot">' + successText + '</div>').fadeIn();
              $('#brand, #model, #phone, #email').removeAttr('placeholder');
              $('#reservation form').trigger('reset');
              $('#reservation .rims_with, #reservation .temp_save_nr').hide();

              if (typeof window.r1TrackBookingConversion === 'function') {
                window.r1TrackBookingConversion(payload.booking_event_id);
              }

              document.querySelectorAll('.time-status.taken-slot').forEach(function(element) {
                let nextElement = element.nextElementSibling;

                if (nextElement && nextElement.classList.contains('time-taken-half')) {
                  nextElement.classList.remove('time-taken-half', 'taken-slot');
                  nextElement.classList.add('taken-slot');
                }
              });

              let wsParams = {
                iorder: iorder,
                queue_id: queue_id,
                date: date,
                office: office,
                car_brand: car_brand,
                car_model: car_model,
                plate: plate,
                fullNumber: phone,
              };

              let wsData = {
                wsParams: wsParams,
                new_slot_client: payload.new_slot_client,
              };
            }
          }, 0);
        },
        complete: function() {
          $('#reservation .loader-block').hide();
        }
      });
    }

    // If plate is empty, don't block submission with a car-info alert.
    // Show same validation hint as backend does.
    if (!normalizedPlate) {
      $('#reg_nr').attr('placeholder', 'Jābūt aizpildītam!');
      setRegNrValidity(false);
      $('#submit-reservation').removeAttr('disabled');
      $('#close-modal').removeAttr('disabled');
      $('#reservation .loader-block').hide();
      return;
    }

    // If plate is present but invalid, show inline invalid state and stop.
    if (!isValidPlate(normalizedPlate)) {
      setRegNrValidity(false);
      $('#submit-reservation').removeAttr('disabled');
      $('#close-modal').removeAttr('disabled');
      $('#reservation .loader-block').hide();
      return;
    }

    // Plate is valid => persist EXACT API response to DB.
    ensureCarInfoSnapshotForPlate(lic_plate)
      .done(function(snap) {
        submitFillSlot(snap);
      })
      .fail(function() {
  console.warn('car-info snapshot failed; proceeding without snapshot.');
        submitFillSlot(null);
      });

  });




  $(document).on('change', '#mobile-filiale .filiale_grid .filiale_card', function() {
    selected__office = $('input', this).val();
    let __selected;

    if ($('#mobile-main #mobile-slots-choice').text().trim().length > 0) {
      __selected = 1;
    } else {
      __selected = 0;
    }

    $.ajax({
      url: '/pieraksts/showMobileQueues',
      data: {office_id: selected__office},
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      beforeSend() {
        $('#mobile-filiale .filiale_grid .filiale_card').attr('disabled', true).prop('disabled', true);
        (__selected === 1) ? $('#mobile-slots-choice').slideUp() : '';
      },
      success: function(data) {
        $('#mobile-main #mobile-slots-choice .reservation').html(data);
        $('html, body').animate({
          scrollTop: $('.reservation').offset().top
        }, 'slow');
        $('#mobile-main #mobile-slots-choice .reservation button.status-toggle').click(function(e) {
          e.preventDefault();
          $('#mobile-main #mobile-slots-choice .reservation button.status-toggle').toggleClass("btn-primary btn-secondary").text(function(i, text) {
            return text === "Rādīt tikai brīvos laikus" ? "Rādīt visus laikus" : "Rādīt tikai brīvos laikus";
          });
          if ($(this).hasClass('btn-secondary')) {
            $('.reservation .time-list').each(function() {
              $('.time-slot:not(.closed)', this).each(function() {
                if ($(this).children().hasClass('unavailable')) {
                  $(this).hide();
                }
              });
            });
          } else if ($(this).hasClass('btn-primary')) {
            $('.reservation .time-list').each(function() {
              $('.time-slot:not(.closed)', this).each(function() {
                if ($(this).children().hasClass('unavailable')) {
                  $(this).show();
                }
              });
            });
          }
        });
        $('.reservation .time-list').each(function() {
          let __motoCount = $(this).find('.moto').first().length;
          let __acCount = $(this).find('.conditioner').first().length;

          if (__motoCount > 0) {
            $(this).attr('data-moto', 1);
          }
          if (__acCount > 0) {
            $(this).attr('data-ac', 1);
          }
        });
      },
      complete() {
        $('#mobile-filiale .filiale_grid .filiale_card').removeAttr('disabled').prop('disabled', false);
        $('#mobile-slots-choice').slideDown();
      }
    });
  });

  $(document).on('click', '.reservation .time-list .time-slot .active', function() {
    $('.reservation .time-list .time-slot .slot').removeClass('selected');
    $(this).addClass('selected');

    iorder = $(this).parent().attr('data-iorder');
    queue_id = $(this).parent().attr('data-queue-id');
    date = $(this).parent().parent().attr('data-date');
    time = $(this).children('.time-span').html();
    office = selected__office;
    slot = $(this);

    $('#mobile-reservation-form').show();
    $([document.documentElement, document.body]).animate({
      scrollTop: $('#mobile-reservation-form').offset().top - 30});

    $('#mobile-reservation-form .purpose #mobile-service select[name="serviceOption"]').on('change', function() {
      switch ($('option:selected', this).val()) {
        case '1': {
          $('#mobile-reservation-form .rims-with-mobile').show();
          $('#mobile-reservation-form .rims-storageBin').hide();
          break;
        }
        case '2': {
          $('#mobile-reservation-form .rims-with-mobile').hide();
          $('#mobile-reservation-form .rims-storageBin').show();
          break;
        }
        default: {
          $('#mobile-reservation-form .rims-with-mobile, #mobile-reservation-form .rims-storageBin').hide();
          break;
        }
      }
    });
  });

  $(document).on('click', '.available.slot', function() {
    let __timeSlots = $(this).parent().parent();
    $('#mobile-service select[name=serviceOption] option').each(function() {
      $(this).removeAttr('selected');
      $(this).attr('disabled', false).prop('disabled', false);
      if ($(__timeSlots).attr('data-moto')) {
        if ($(this).attr('data-moto')) {
          $(this).attr('disabled', true).prop('disabled', true);
        }
      }
      if ($(__timeSlots).attr('data-ac')) {
        if ($(this).attr('data-ac')) {
          $(this).attr('disabled', true).prop('disabled', true);
        }
      }
    });
    $('#mobile-service select[name=serviceOption] option.disabled').attr('disabled', true).prop('disabled', true);
    $('#mobile-service select[name=serviceOption]').prop('selectedIndex',0);
  });
  $(document).on('click', '.slot.conditioner', function() {
    $('#mobile-service select[name=serviceOption] option').each(function() {
      $(this).removeAttr('disabled').removeProp('disabled');
      if (!$(this).attr('data-ac')) {
        $(this).attr('disabled', true).prop('disabled', true);
      } else {
        $(this).attr('selected', true).prop('selected', true);
      }
    });
  });
  $(document).on('click', '.slot.moto', function() {
    $('#mobile-service select[name=serviceOption] option').each(function() {
      $(this).removeAttr('disabled').removeProp('disabled');
      if (!$(this).attr('data-moto')) {
        $(this).attr('disabled', true).prop('disabled', true);
      } else {
        $(this).first().attr('selected', true).prop('selected', true);
      }
    });
  });


  $(document).on('click', '#mobile-submit-reservation', function(e) {
    e.preventDefault();

    car_brand = $('#mobile-reservation-form input#mobile-brand').val().replace('&', '');
    car_model = $('#mobile-reservation-form input#mobile-model').val().replace('&', '');
    rimsWith = $('#mobile-reservation-form .rims-with-mobile input[name="rims_with_input"]:checked').val();
    if (rimsWith === undefined) rimsWith = 1;
    temp_nr = $('#mobile-reservation-form .rims-storageBin input#mobile_storage_bin').val();
    phone = $('#mobile-reservation-form .phone-number input#mobile-phone').val().replace(' ', '');
    let lic_plate = $('#mobile-reservation-form input#mobile-reg_nr').val().replace('&', '');
    let plate = phone.substr(-3);
    plate = parseInt(plate);
    plate = $.trim(plate);
    let service = $('#mobile-reservation-form .purpose #mobile-service select[name="serviceOption"] option:selected').val();
    if (service === 'Izvēlēties') service = 1;
    let user_comment = $('#mobile-reservation-form textarea#mobile-comment').val();
    let name = $('#mobile-reservation-form input#mobile-name').val();
    let email = $('#mobile-reservation-form input#mobile-email').val();

    $('#reservation .loader-block').show();
    $('#mobile-submit-reservation').attr('disabled', true);

    const normalizedPlateMobile = normalizePlate(lic_plate);

    // Build & submit fillSlot request (optionally with car-info snapshot).
    function submitFillSlotMobile(carInfoSnap) {
      const thisFillSeq = ++fillSlotRequestSeq;
      let formDataPayload = {
        car_brand: car_brand,
        car_model: car_model,
        rimsWith: rimsWith,
        temp_nr: temp_nr,
        lic_plate: lic_plate,
        service: service,
        user_comment: user_comment,
        name: name,
        phone_number: phone,
        email: email,
      };

      if (carInfoSnap && carInfoSnap.json) {
        formDataPayload.car_info_json = carInfoSnap.json;
        formDataPayload.car_info_fetched_at = carInfoSnap.fetchedAt || '';
        formDataPayload.car_info_vnr = normalizedPlateMobile;
        formDataPayload.car_info_source = 'api/car-info';
      }

      let formData = $.param(formDataPayload);

      let dopParams = {
        iorder: iorder,
        queue_id: queue_id,
        date: date,
        time: time,
        office: office,
      };

      $.ajax({
        url: '/pieraksts/fillSlot',
        method: 'POST',
        data: {formData: formData, dopParams: dopParams, from_mobile: 1},
        success: function (data) {
          var payloadImmediate = (typeof data === 'string') ? JSON.parse(data) : data;
          if (thisFillSeq === fillSlotRequestSeq && payloadImmediate.message && payloadImmediate.success) {
            if (window.simpleSlotLock && typeof window.simpleSlotLock.onFillSlotSuccess === 'function') {
              window.simpleSlotLock.onFillSlotSuccess();
            }
          }
          setTimeout(function() {
            if (thisFillSeq !== fillSlotRequestSeq) {
              return;
            }
            var payload = payloadImmediate;

            if (payload.errors) {
              $.each(payload.errors, function(index, item) {
                if (index == 'car_brand') {
                  $('#mobile-brand').attr('placeholder', 'Jābūt aizpildītam!');
                }
                if (index == 'car_model') {
                  $('#mobile-model').attr('placeholder', 'Jābūt aizpildītam!');
                }
                if (index == 'lic_plate') {
                  $('#mobile-reg_nr').attr('placeholder', 'Jābūt aizpildītam!');
                }
                if (index == 'phone_number') {
                  $('#mobile-phone').attr('placeholder', 'Jābūt aizpildītam!');
                }
              });
              $('#submit-reservation').removeAttr('disabled');
              $('#close-modal').removeAttr('disabled');
              return;
            }

            if (payload.alertMessage) {
              $('html, body').animate({
                scrollTop: $("section#mobile-main").offset().top
              });
              $('.mobile-reservation-modal-body .mobile-body').slideUp();
              $('.mobile-reservation-modal-body .mobile-body-success .alert').html(payload.alertMessage);
              $('.mobile-reservation-modal-body .mobile-body-success').slideDown();
              return;
            }

            if (payload.message && payload.success) {
              $('html, body').animate({
                scrollTop: $("section#mobile-main").offset().top
              });
              $('.mobile-reservation-modal-body .mobile-body').slideUp();
              $('.mobile-reservation-modal-body .mobile-body-success .alert').html(payload.message);
              $('.mobile-reservation-modal-body .mobile-body-success').slideDown();
              $('#mobile-submit-reservation').slideToggle();
              $('#mobile-close-modal').slideToggle().on('click', function () {
                $(this).slideToggle();
                $('#mobile-submit-reservation').slideToggle();
                $('section#mobile-main form').trigger('reset');
                $('.mobile-body-success').slideUp();
                $('.mobile-reservation-modal-body .mobile-body').slideDown();
                $('.mobile-reservation-modal-body .mobile-body-success .alert').text('');
              });

              let wsParams = {
                iorder: iorder,
                queue_id: queue_id,
                date: date,
                office: office,
                car_brand: car_brand,
                car_model: car_model,
                plate: plate,
                fullNumber: phone,
              };

              let wsData = {
                wsParams: wsParams,
                new_slot_client: payload.new_slot_client,
              };

              if (typeof window.r1TrackBookingConversion === 'function') {
                window.r1TrackBookingConversion(payload.booking_event_id);
              }
            }
          }, 0);
        },
        complete: function() {
          $('#reservation .loader-block').hide();
          $('#mobile-submit-reservation').removeAttr('disabled');
        }
      });
    }

    // If plate is empty, don't block submission with a car-info alert.
    // Show same validation hint as backend does.
    if (!normalizedPlateMobile) {
      $('#mobile-reg_nr').attr('placeholder', 'Jābūt aizpildītam!');
      setRegNrValidity(false);
      $('#reservation .loader-block').hide();
      $('#mobile-submit-reservation').removeAttr('disabled');
      return;
    }

    // If plate is present but invalid, show inline invalid state and stop.
    if (!isValidPlate(normalizedPlateMobile)) {
      setRegNrValidity(false);
      $('#reservation .loader-block').hide();
      $('#mobile-submit-reservation').removeAttr('disabled');
      return;
    }

    // Plate is valid => persist EXACT API response to DB.
    ensureCarInfoSnapshotForPlate(lic_plate)
      .done(function(snap) {
        submitFillSlotMobile(snap);
      })
      .fail(function() {
  console.warn('car-info snapshot failed (mobile); proceeding without snapshot.');
        submitFillSlotMobile(null);
      });

  });

  document.querySelectorAll('.time-status.taken-slot').forEach(function(element) {
    let nextElement = element.nextElementSibling;

    if (nextElement && nextElement.classList.contains('time-taken-half')) {
      nextElement.classList.remove('time-taken-half', 'taken-slot');
      nextElement.classList.add('taken-slot');
    }
  });


  let plate;

  function formatTime(date) {
    let hours = ('0' + date.getHours()).slice(-2);
    let minutes = ('0' + date.getMinutes()).slice(-2);
    return hours + ':' + minutes;
  }



  // Listen for WebSocket messages
  // socket.addEventListener('message', function(event) {
  //   const data = JSON.parse(event.data);
  //
  //   if (!data.times && !data.timeChangedState) {
  //     let slot = $('.schedule-table.dashboard .grid[data-date="' + data.wsParams.date + '"] .table[data-queue-id="' + data.wsParams.queue_id + '"] .time-status[data-iorder="' + data.wsParams.iorder + '"]');
  //     let mobile_slot = $('#mobile-slots-choice .time-list[data-date="' + data.wsParams.date + '"] .time-slot[data-queue-id="' + data.wsParams.queue_id + '"][data-iorder="' + data.wsParams.iorder + '"]');
  //
  //     if (!isNaN(data.wsParams.plate) || data.wsParams.plate != null) {
  //       plate = data.wsParams.plate;
  //     } else {
  //       plate = '';
  //     }
  //
  //     if (data.new_slot_client) {
  //       slot.removeClass('time-free').addClass('time-taken').find('button').fadeOut().remove();
  //       let successText = truncateCharacters($.trim(data.wsParams.car_brand), 8, '&mldr;', 1) + ' xxxxx' + plate;
  //       slot.append('<div class="slot taken-slot">' + successText + '</div>').hide().fadeIn();
  //
  //       mobile_slot.find('.slot').removeClass('active').removeClass('available').addClass('unavailable');
  //       mobile_slot.find('.slot-text').html('Aizإ†emts');
  //
  //     } else if (data.slot_admin.edited_slot_admin) {
  //
  //       if (data.dopParams.new_date !== data.dopParams.date || data.dopParams.new_queue !== data.dopParams.queue_id || data.dopParams.new_time !== data.dopParams.time) {
  //         slot = $('.schedule-table.dashboard .grid[data-date="' + data.dopParams.new_date + '"] .table[data-queue-id="' + data.dopParams.new_queue + '"] .time-status[data-iorder="' + data.dopParams.new_iorder + '"]');
  //         mobile_slot = $('#mobile-slots-choice .time-list[data-date="' + data.wsParams.new_date + '"] .time-slot[data-queue-id="' + data.wsParams.new_queue + '"][data-iorder="' + data.wsParams.new_iorder + '"]');
  //       }
  //
  //       if (data.status == 0) {
  //         if (data.wsParams.discount !== null) {
  //           slot.addClass('discount');
  //           slot.find('button.status').addClass('discount-slot').text(data.wsParams.discount);
  //         }
  //       } else if (data.status == 1) {
  //         if (plate === null) plate = '';
  //         if (slot.hasClass('time-taken')) {
  //           slot.find('div.slot').fadeOut().remove();
  //           let successText = truncateCharacters($.trim(data.wsParams.car_brand), 8, '&mldr;', 1) + ' xxxxx' + plate;
  //           slot.append('<div class="slot taken-slot">' + successText + '</div>').hide().fadeIn();
  //         } else if (slot.hasClass('time-free')) {
  //           slot.removeClass('time-free').addClass('time-taken').find('button').fadeOut().remove();
  //           let successText = truncateCharacters($.trim(data.wsParams.car_brand), 8, '&mldr;', 1) + ' xxxxx' + plate;
  //           slot.append('<div class="slot taken-slot">' + successText + '</div>').hide().fadeIn();
  //         } else if (slot.hasClass('time-offer')) {
  //           slot.removeClass('time-offer').addClass('time-taken').find('button').fadeOut().remove();
  //           let successText = truncateCharacters($.trim(data.wsParams.car_brand), 8, '&mldr;', 1) + ' xxxxx' + plate;
  //           slot.append('<div class="slot taken-slot">' + successText + '</div>').hide().fadeIn();
  //         } else if (slot.hasClass('time-closed')) {
  //           slot.removeClass('time-closed').addClass('time-taken').find('span').fadeOut().remove();
  //           let successText = truncateCharacters($.trim(data.wsParams.car_brand), 8, '&mldr;', 1) + ' xxxxx' + plate;
  //           slot.append('<div class="slot taken-slot">' + successText + '</div>').hide().fadeIn();
  //         } else if (slot.hasClass('time-gray')) {
  //           slot.removeClass('time-gray').addClass('time-taken').find('span').fadeOut().remove();
  //           let successText = truncateCharacters($.trim(data.wsParams.car_brand), 8, '&mldr;', 1) + ' xxxxx' + plate;
  //           slot.append('<div class="slot taken-slot">' + successText + '</div>').hide().fadeIn();
  //         }
  //       } else if (data.status == 3) {
  //         // Desktop version
  //         if (slot.hasClass('time-free')) {
  //           slot.removeClass('time-free').addClass('time-closed').find('button').fadeOut().remove();
  //           slot.append('<span class="slot closed-slot">Slؤ“gts</span>').hide().fadeIn();
  //         } else if (slot.hasClass('time-taken')) {
  //           slot.removeClass('time-taken').addClass('time-closed').find('div.slot').fadeOut().remove();
  //           slot.append('<span class="slot closed-slot">Slؤ“gts</span>').hide().fadeIn();
  //         } else if (slot.hasClass('time-offer')) {
  //           slot.removeClass('time-offer').addClass('time-closed').find('div.slot').fadeOut().remove();
  //           slot.append('<span class="slot closed-slot">Slؤ“gts</span>').hide().fadeIn();
  //         } else if (slot.hasClass('time-gray')) {
  //           slot.removeClass('time-gray').addClass('time-closed').find('div.slot').fadeOut().remove();
  //           slot.append('<span class="slot closed-slot">Slؤ“gts</span>').hide().fadeIn();
  //         }
  //
  //         // Mobile version
  //
  //       }
  //     } else if (data.slot_admin.moved_slot_admin) {
  //       if (data.status == 2) {
  //         console.log('status - 2, data - ' + data.dopParams.new_iorder);
  //       } else if (data.status == 1) {
  //         let new_slot = $('.schedule-table.dashboard .grid[data-date="' + data.dopParams.new_date + '"] .table[data-queue-id="' + data.dopParams.new_queue + '"] .time-status[data-iorder="' + data.dopParams.new_iorder + '"]');
  //
  //         let old_slot_classes = slot.prop('classList');
  //         let new_slot_classes = new_slot.prop('classList');
  //
  //         let old_slot_button = slot.find('div.slot').clone();
  //         let new_slot_button = new_slot.find('button.status, div.slot').clone();
  //         console.log(new_slot_button);
  //
  //         new_slot.find('.slot').first().remove();
  //         slot.find('div.slot').first().remove();
  //         new_slot.append(old_slot_button).hide().fadeIn();
  //         slot.append(new_slot_button).hide().fadeIn();
  //
  //         new_slot.attr('data-old-classes', old_slot_classes);
  //         slot.attr('data-new-classes', new_slot_classes);
  //         new_slot.removeAttr('class').attr('class', new_slot.attr('data-old-classes')).removeAttr('data-old-classes');
  //         slot.removeAttr('class').attr('class', slot.attr('data-new-classes')).removeAttr('data-new-classes');
  //
  //         // let successText = truncateCharacters($.trim(data.wsParams.car_brand), 8, '&mldr;', 1) + ' xxxxx' + plate;
  //         // if (data.dopParams.edited === true) {
  //         //     if (slot.find('span').length > 0) {
  //         //         slot.find('span').first().remove();
  //         //         new_slot.attr('class', 'time-status time-taken inline-flex').append('<span class="bg-gray-300 text-sm text-gray py-2 px-4 status" style="cursor: default;">' + successText + '</span>').hide().fadeIn();
  //         //     } else if (slot.find('button').length > 0) {
  //         //         // slot.find('button').first().remove();
  //         //     }
  //         // } else {
  //         //     if (slot.find('span').length > 0) {
  //         //         slot.find('span').first().remove();
  //         //         new_slot.attr('class', 'time-status time-taken inline-flex').append('<span class="bg-gray-300 text-sm text-gray py-2 px-4 status" style="cursor: default;">' + successText + '</span>').hide().fadeIn();
  //         //     } else if (slot.find('button').length > 0) {
  //         //         // slot.find('button').first().remove();
  //         //     }
  //         // }
  //
  //         iorder = data.dopParams.new_iorder;
  //         queue_id = data.dopParams.new_queue;
  //         date = data.dopParams.new_date;
  //         time = data.dopParams.new_time;
  //         office = data.dopParams.new_office;
  //         slot = new_slot;
  //       } else {
  //
  //       }
  //     } else if (data.slot_admin.deleted_slot_admin) {
  //       if (data.status == 2) {
  //         slot.removeClass('time-taken').addClass('time-offer');
  //         slot.find('span').remove();
  //         slot.append('<button class="bg-orange-500 hover:bg-orange-200 text-black py-2 px-4 status">' + data.comment.comment + '</button>').hide().fadeIn();
  //       } else {
  //         if (slot.hasClass('time-taken')) {
  //           slot.removeClass('time-taken').addClass('time-free');
  //         } else if (slot.hasClass('time-closed')) {
  //           slot.removeClass('time-closed').addClass('time-free');
  //         } else if (slot.hasClass('time-gray')) {
  //           slot.removeClass('time-gray').addClass('time-free');
  //         } else if (slot.hasClass('discount')) {
  //           slot.removeClass('discount');
  //         }
  //         if (slot.find('div.slot').length > 0) {
  //           slot.find('div.slot').remove();
  //         } else if (slot.find('span.slot').length > 0) {
  //           slot.find('span.slot').remove();
  //         } else {
  //           slot.find('button.status').remove();
  //         }
  //         slot.append('<button class="status free-slot-link available-slot">Brؤ«vs</button>').hide().fadeIn();
  //       }
  //     }
  //   } else if (data.timeChangedState === 1) {
  //     $('#toasts .toast').each(function() {
  //       $(this).fadeOut(function() {
  //         $(this).remove();
  //       });
  //     });
  //     $.toast({
  //       autoDismiss: false,
  //       title: 'Paziإ†ojums',
  //       message: 'Notika izmaiإ†as darba laikos, atjaunojiet lapu<br><button onclick="location.reload()" class="btn btn-success" style="margin-top: 5px;">Pؤپrlؤپdؤ“t</button>'
  //     });
  //   } else {
  //     if (data.times.changeVal) {
  //
  //       let action;
  //       let closedTime = false;
  //       if (data.times.newCloseTime > data.times.oldCloseTime) {
  //         action = 'add';
  //       } else if (data.times.newCloseTime === data.times.oldCloseTime) {
  //         action = null;
  //       } else {
  //         action = 'remove';
  //       }
  //
  //       let newCloseTime = data.times.newCloseTime;
  //       let oldCloseTime = data.times.oldCloseTime;
  //
  //       // Convert the time strings to Date objects for easier manipulation
  //       let startTime = new Date('2000-01-01T' + newCloseTime + ':00');
  //       let endTime = new Date('2000-01-01T' + oldCloseTime + ':00');
  //
  //       // Define the time step in milliseconds (15 minutes)
  //       let timeStep = data.times.timeStep * 60 * 1000;
  //
  //       let times = [];
  //
  //       // Start the loop from the start time and increment by the time step
  //       for (let currentTime = startTime; currentTime < endTime; currentTime.setTime(currentTime.getTime() + timeStep)) {
  //         // Get the current time in the desired format (e.g., HH:mm)
  //         let formattedTime = currentTime.getHours() + ':' + ('0' + currentTime.getMinutes()).slice(-2);
  //
  //         // Add the formatted time to the array
  //         times.push(formattedTime);
  //       }
  //
  //       // $('.grid[data-date="' + data.times.date + '"] .table[data-queue-id="' + data.times.queue_id + '"] .time-status .time-slot').each(function() {
  //       //     if (times.includes($(this).html())) {
  //       //         $(this).parent().fadeOut(function() {
  //       //             $(this).remove();
  //       //         })
  //       //     }
  //       // });
  //
  //       if (times.includes($('.modal#reservation .timeOfDay').html()) && $('.modal#reservation').is(':visible')) {
  //         closedTime = true;
  //         $('.modal#reservation #submit-reservation').remove();
  //         $('.modal#reservation #close-modal').text('Aizvؤ“rt');
  //         $('.modal#reservation .reservation-modal-body .container-fluid').slideUp();
  //
  //         let alertMessage = '<div class="container-fluid"><div class="row"><div class="col-md-12">' +
  //           '<div class="alert alert-warning">Atvainojamies, darba laiks saؤ«sinؤپjies, lإ«gums izvؤ“lؤ“ties citu pieraksta laiku</div>' +
  //           '</div></div></div>';
  //
  //         $(alertMessage).insertAfter($('.modal#reservation .reservation-modal-body .container-fluid'));
  //
  //         $('.modal#reservation #close-modal').one('click', function() {
  //           location.reload();
  //         });
  //       } else {
  //         // if (!window.Notification) {
  //         //   console.log('Browser does not support notifications.');
  //         // } else {
  //         //   // check if permission is already granted
  //         //   if (Notification.permission === 'granted') {
  //         //     // show notification here
  //         //     var notify = new Notification('Pasإ«tؤ«jumi', {
  //         //       body: 'Ir izveidots jauns pasإ«tؤ«jums',
  //         //       icon: 'https://r1riepas.lv/img/r1-riepas-logo-1515661637.jpg',
  //         //     });
  //         //   } else {
  //         //     // request permission from user
  //         //     Notification.requestPermission().then(function (p) {
  //         //       if (p === 'granted') {
  //         //         // show notification here
  //         //         var notify = new Notification('Pasإ«tؤ«jumi', {
  //         //           body: 'Ir izveidots jauns pasإ«tؤ«jums',
  //         //           icon: 'https://r1riepas.lv/img/r1-riepas-logo-1515661637.jpg',
  //         //         });
  //         //       } else {
  //         //         console.log('User blocked notifications.');
  //         //       }
  //         //     }).catch(function (err) {
  //         //       console.error(err);
  //         //     });
  //         //   }
  //         // }
  //       }
  //
  //     }
  //   }
  // });

});

