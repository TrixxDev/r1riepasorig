$(document).ready(function() {

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

  if (localStorage.getItem('toastMessage')) {
    if ($('.reservations_page .apply-changes').is(':visible')) {
      $.toast({
        'type': 'success',
        'title': 'Paziņojums',
        'message': localStorage.getItem('toastMessage'),
      });
    }
    localStorage.removeItem('toastMessage');
  }

  if (localStorage.getItem('deleteMessage')) {
    $.toast({
      'type': 'success',
      'title': 'Paziņojums',
      'message': localStorage.getItem('deleteMessage')
    });
    localStorage.removeItem('deleteMessage');
  }

  let iorder;
  let queue_id;
  let date;
  let time;
  let newOpenTime;
  let oldOpenTime;
  let newCloseTime;
  let oldCloseTime;
  let office;
  let slot;
  let car_brand;
  let phone;
  let new_date;
  let new_time;
  let new_queue;
  let timeStep;
  let empty_slot;
  let discountSelect;
  let numNonEmptyInputs;
  let currentTakenby = null;
  let currentCarInfo = null;
  let adminCarInfoToken = '';
  let adminCarInfoTokenInflight = null;

  function normalizePlate(raw) {
    return $.trim(String(raw || ''))
      .toUpperCase()
      .replace(/[\s-]+/g, '');
  }

  function isValidPlate(vnr) {
    if (!vnr) return false;
    if (!/^[A-Z0-9]{2,8}$/.test(vnr)) return false;
    if (!/[A-Z]/.test(vnr)) return false;
    const mStd = vnr.match(/^([A-Z]{2})(\d{1,4})$/);
    if (mStd) {
      const n = parseInt(mStd[2], 10);
      return n >= 1 && n <= 9999;
    }
    const mTrailer = vnr.match(/^([A-Z])(\d{1,4})$/);
    if (mTrailer) {
      const n = parseInt(mTrailer[2], 10);
      return n >= 1 && n <= 9999;
    }
    return /^[A-Z]{2,8}$/.test(vnr) || /^[A-Z]+\d+$/.test(vnr) || /^\d+[A-Z]+$/.test(vnr);
  }

  function setPlateValidityInModal(valid) {
    const $input = $('#slotModal #f_plate');
    if (!$input.length) return;
    if (valid) {
      $input.css('border-color', '');
      return;
    }
    $input.css('border-color', '#e11d48');
    setTimeout(function() {
      $input.css('border-color', '');
    }, 1200);
  }

  function updateCarInfoTokenFromResponse(jqXHR) {
    try {
      const nextToken = jqXHR && jqXHR.getResponseHeader ? jqXHR.getResponseHeader('X-Car-Info-Token') : '';
      if (nextToken) {
        adminCarInfoToken = nextToken;
        const $hidden = $('#slotModal input[name="car_info_token"]');
        if ($hidden.length) {
          $hidden.val(nextToken);
        }
      }
    } catch (_e) {
      // ignore
    }
  }

  function fetchAdminCarInfoToken(force) {
    const domToken = $('#slotModal input[name="car_info_token"]').val();
    if (domToken) {
      adminCarInfoToken = String(domToken);
    }
    if (!force && adminCarInfoToken) {
      return $.Deferred().resolve(adminCarInfoToken).promise();
    }
    if (adminCarInfoTokenInflight) {
      return adminCarInfoTokenInflight;
    }

    const d = $.Deferred();
    adminCarInfoTokenInflight = d.promise();

    $.ajax({
      url: '/pieraksts',
      method: 'GET',
      cache: false
    }).done(function(html) {
      const m1 = String(html || '').match(/name=["']car_info_token["'][^>]*value=["']([^"']+)["']/i);
      const m2 = String(html || '').match(/value=["']([^"']+)["'][^>]*name=["']car_info_token["']/i);
      const token = (m1 && m1[1]) || (m2 && m2[1]) || '';
      if (!token) {
        d.reject('token_not_found');
        return;
      }
      adminCarInfoToken = token;
      d.resolve(token);
    }).fail(function() {
      d.reject('token_fetch_failed');
    }).always(function() {
      adminCarInfoTokenInflight = null;
    });

    return d.promise();
  }

  function renderCarInfoCardText(parsed, meta) {
    const fmtDateTime = (raw) => {
      if (!raw || typeof raw !== 'string') return '';
      let s = raw.trim();
      if (/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}/.test(s)) {
        s = s.replace(' ', 'T');
      }
      const d = new Date(s);
      if (isNaN(d.getTime())) return raw;
      const dd = String(d.getDate()).padStart(2, '0');
      const mm = String(d.getMonth() + 1).padStart(2, '0');
      const yyyy = String(d.getFullYear());
      const hh = String(d.getHours()).padStart(2, '0');
      const mi = String(d.getMinutes()).padStart(2, '0');
      return `${dd}.${mm}.${yyyy} ${hh}:${mi}`;
    };

    const fmtDdMmYyyy = (raw) => {
      if (!raw) return '';
      const s = String(raw).trim();
      if (!/^\d{8}$/.test(s)) return s;
      const dd = s.slice(0, 2);
      const mm = s.slice(2, 4);
      const yyyy = s.slice(4, 8);
      return `${dd}.${mm}.${yyyy}`;
    };

    const toStr = (v) => {
      if (v === null || v === undefined) return '';
      if (Array.isArray(v)) return v.length ? v.join(', ') : '';
      const s = String(v).trim();
      return s === '[]' ? '' : s;
    };

    const add = (rows, label, value) => {
      const v = toStr(value);
      if (!v) return;
      rows.push([label, v]);
    };

    const marka = toStr(parsed.MARKA || parsed.Marka || parsed.marka);
    const modelis = toStr(parsed.MODELIS || parsed.Modelis || parsed.modelis);

    const rows = [];
    add(rows, 'VNR', meta.vnr || toStr(parsed.RN) || meta.fallbackVnr || '');
    add(rows, 'Marka / modelis', [marka, modelis].filter(Boolean).join(' '));
    add(rows, 'Gads', parsed.GADS);
    add(rows, 'Degviela', parsed.DEGVIELA);
    add(rows, 'VIN', parsed.VIN);
    add(rows, 'Krāsa', parsed.KRASA);
    add(rows, 'COC kategorija', parsed.COC_KATEGORIJA);
    add(rows, 'TL veids', parsed.TL_VEIDS);
    add(rows, 'Tilpums', toStr(parsed.TILPUMS) ? (toStr(parsed.TILPUMS) + ' cm³') : '');
    add(rows, 'Jauda', toStr(parsed.JAUDA) ? (toStr(parsed.JAUDA) + ' kW') : '');
    add(rows, 'Pašmasa', toStr(parsed.PASMASA) ? (toStr(parsed.PASMASA) + ' kg') : '');
    add(rows, 'Pilna masa', toStr(parsed.PILNA_MASA) ? (toStr(parsed.PILNA_MASA) + ' kg') : '');
    add(rows, 'TA līdz', fmtDdMmYyyy(parsed.TA_LIDZ));
    add(rows, 'OCTA līdz', fmtDdMmYyyy(parsed.POL_BEIGAS));
    add(rows, 'Reģistrēts', fmtDdMmYyyy(parsed.REG1));
    add(rows, 'Avots', meta.source || '');
    add(rows, 'Iegūts', fmtDateTime(meta.fetchedAt || '') || meta.fetchedAt || '');

    const labelWidth = rows.reduce((m, [l]) => Math.max(m, l.length), 0);
    const lines = [];
    lines.push('Auto dati (CSDD)');
    lines.push('─'.repeat(40));
    rows.forEach(([label, value]) => {
      lines.push(label.padEnd(labelWidth, ' ') + ': ' + value);
    });
    return lines.join('\n');
  }

  function setCarInfoUIFromSnapshot($btn, $pre, snap) {
    if (!$btn || !$pre) return;
    $btn.off('click.carInfo');
    $btn.prop('disabled', true).text('Apskatīt datus');
    $pre.hide().text('');

    if (!snap || !snap.json) return;
    try {
      const parsed = JSON.parse(snap.json);
      $pre.text(renderCarInfoCardText(parsed, {
        vnr: snap.vnr || '',
        fetchedAt: snap.fetchedAt || '',
        source: snap.source || '',
        fallbackVnr: (currentTakenby && currentTakenby.lic_plate) ? String(currentTakenby.lic_plate) : ''
      }));
    } catch (_e) {
      $pre.text(String(snap.json || ''));
    }

    $btn.prop('disabled', false);
    $btn.on('click.carInfo', function() {
      const visible = $pre.is(':visible');
      if (visible) {
        $pre.hide();
        $btn.text('Apskatīt datus');
      } else {
        $pre.show();
        $btn.text('Paslēpt datus');
      }
    });
  }

  function fetchCarInfoSnapshotForPlate(plateRaw) {
    const d = $.Deferred();
    const vnr = normalizePlate(plateRaw);

    if (!vnr || !isValidPlate(vnr)) {
      return d.reject('invalid_plate').promise();
    }

    fetchAdminCarInfoToken(false).done(function() {
      const doRequest = function(retried) {
        const apiUrl = $('#slotModal').data('car-info-url') || '/api/car-info';
        $.ajax({
          url: apiUrl,
          method: 'POST',
          data: { vnr: vnr },
          headers: {
            'X-Car-Info-Token': adminCarInfoToken
          }
        }).done(function(data, _textStatus, jqXHR) {
          updateCarInfoTokenFromResponse(jqXHR);
          d.resolve({
            vnr: vnr,
            fetchedAt: new Date().toISOString(),
            source: 'api/car-info',
            json: JSON.stringify(data || {}),
            data: data || {}
          });
        }).fail(function(jqXHR) {
          updateCarInfoTokenFromResponse(jqXHR);
          if (!retried && jqXHR && jqXHR.status === 403) {
            // Token might be invalid/expired. Refresh once and retry.
            fetchAdminCarInfoToken(true).done(function() {
              doRequest(true);
            }).fail(function() {
              d.reject('token_refresh_failed');
            });
            return;
          }
          d.reject('car_info_failed');
        });
      };
      doRequest(false);
    }).fail(function() {
      d.reject('token_failed');
    });

    return d.promise();
  }

  let days = {
    'pirmdiena': 'pirmdienām',
    'otrdiena': 'otrdienām',
    'trešdiena': 'trešdienām',
    'ceturtdiena': 'ceturtdienām',
    'piektdiena': 'piektdienām',
    'sestdiena': 'sestdienām',
    'svētdiena': 'svētdienām'
  }

  $(document).on('click', '#slotModal .close-modal, #slotModal .decline', function() {
    $('#slotModal').modal('hide');
    $('#slotModal form').trigger('reset');
  });

  $(document).on('click', '#queueModal .decline', function() {
    $('#queueModal').modal('hide');
  });



  $('#queueModal, #slotModal').on('show.bs.modal hide.bs.modal', function () {
    $('body').toggleClass('removeScroll');
  });

  $('.reservation_edit .select-discount-option').on('change', function() {
    let selectedOption = $('option:selected', this);
    let isLastOption = selectedOption.is(':last-child');

    // $('.reservation_edit #f_status option').removeAttr('selected').prop('selected', false).first().attr('selected', true).prop('selected', true);

    discountSelect = selectedOption;

    if (isLastOption) {
      $(this).next().show();
    } else {
      $(this).next().hide();
    }
  });

  $('.reservation_edit #f_slotcomment').on('input', function() {
    $('.reservation_edit .select-discount-option option').last().attr('selected', true).prop('selected', true);
  });

  $('.schedule-table.reservations_page #save_changes').one('click', function(e) {
    e.preventDefault();

    $('.body.loader-block').show();
    $('body').addClass('removeScroll');
    setTimeout(function() {
      $.ajax({
        url: '/pieraksts/rezervacijas/saveTimeChanges',
        method: 'GET',
        dataType: 'JSON',
        success: function(data) {
          localStorage.setItem('toastMessage', 'Visas izmaiņas veiksmīgi saglabātas!');

          let resData = {};
          resData.timeChangedState = 1;

          // socket.send(JSON.stringify(resData));
          window.location.href = '/pieraksts/rezervacijas';
        }
      });
    }, 1000);
  });

  $('.schedule-table.reservations_page #cancel_changes').one('click', function(e) {
    e.preventDefault();

    $('.body.loader-block').show();
    $('body').addClass('removeScroll');
    setTimeout(function() {
      $.ajax({
        url: '/pieraksts/rezervacijas/cancelTimeChanges',
        method: 'GET',
        dataType: 'JSON',
        success: function(data) {
          localStorage.setItem('deleteMessage', 'Visas izmaiņas veiksmīgi atceltas!');
          window.location.href = '/pieraksts/rezervacijas';
        }
      });
    }, 1000);
  });

  $('#queueModal .submit').on('click', function(e) {
    e.preventDefault();

    let changeVal = $('#queueModal input[name="changeVal"]:checked').val();
    let newOpenTime = $('#queueModal select#f_opentime option:selected').val();
    let newCloseTime = $('#queueModal select#f_closetime option:selected').val();
    let timeStep = $('#queueModal #f_timeinterval option:selected').val();
    let is_half = parseInt($('#queueModal input[name="queue"]:checked').val());
    let ac_toggle = ($('#queueModal input[name="ac_toggle"]:visible').is(':checked') === true) ? 1 : null;
    let moto_toggle = ($('#queueModal input[name="moto_toggle"]:visible').is(':checked') === true) ? 1 : null;

    let sendData = {
      'times': {
        'changeVal': changeVal,
        'oldOpenTime': oldOpenTime,
        'newOpenTime': newOpenTime,
        'oldCloseTime': oldCloseTime,
        'newCloseTime': newCloseTime,
        'timeStep': timeStep,
        'queue_id': queue_id,
        'date': date,
        'is_half': (is_half === 2) ? 1 : null,
        'ac_toggle': ac_toggle,
        'moto_toggle': moto_toggle,
      }
    };

    $('#queueModal .loader-block').show();
    setTimeout(function() {
      $.ajax({
        url: '/pieraksts/rezervacijas/changeTimes',
        method: 'POST',
        data: sendData,
        dataType: 'JSON',
        error: function(jqXHR, textStatus, errorThrown) {
          $('#queueModal .loader-block').hide();
          $.toast({
            'type': 'danger',
            'title': 'Paziņojums',
            'message': 'Notika kļūda laiku izmaiņas laikā',
          })
        },
        success: function(data) {
          if (data.status === 'success') {
            localStorage.setItem('toastMessage', 'Lai <b>saglabātu</b> darba laikus, nospiediet "Saglabāt" pogu, lai atgrieztu <b>VISAS</b> izmaiņas, nospiediet "Atcelt" pogu');
            window.location.href = '/pieraksts/rezervacijas';
          } else {
            $('#queueModal .loader-block').hide();
            $.toast({
              'type': 'danger',
              'title': 'Paziņojums',
              'message': data.message,
            })
          }
          window.location.reload();
        },
        complete: function() {

        }
      });
    }, 1000);

    // socket.send(JSON.stringify(sendData));
  });

  $('.grid .table .title').on('click', function() {
    $('#queueModal .loader-block').show();
    queue_id = $(this).parent().attr('data-queue-id');
    date = $(this).parent().parent().attr('data-date');
    let day = $(this).parent().parent().parent().prev().text().split(',')[0].toLowerCase();
    day = days[day];

    $.ajax({
      url: '/pieraksts/rezervacijas/getTimes',
      method: 'POST',
      data: {queue_id: queue_id, date: date},
      beforeSend: function() {
        $('#queueModal #f_timeinterval option').each(function() {
          $(this).removeAttr('selected').prop('selected', false);
        });
        $('#queueModal #f_opentime').html('');
        $('#queueModal #f_closetime').html('');
        $('#queueModal #title').val('');
        $('#queueModal input[name="changeVal"]').removeAttr('checked').prop('checked', false);
        if ($('#queueModal #all_working_days').is(':hidden')) {
          $('#queueModal #all_working_days').parent().show();
        }
        $('#queueModal #fullQueue, #queueModal #halfQueue').removeAttr('checked').prop('checked', false);
        $('#queueModal .queue_services').hide();
        $('#queueModal #ac_toggle, #queueModal #moto_toggle').removeAttr('checked').prop('checked', false);
      },
      success: function(data) {

        data = JSON.parse(data);

        if (data.weekday == 6) {
          if ($('#queueModal #all_working_days').is(':visible')) {
            $('#queueModal #all_working_days').parent().hide();
          }
        }

        setTimeout(function() {
          $('#queueModal #title').val(data.title);

          $('#queueModal #one_day').attr('checked', true).prop('checked', true);
          if (data.is_half === 1) {
            $('#queueModal').find('#halfQueue').attr('checked', true).prop('checked', true);
            $('#queueModal .queue_services').show();
          } else {
            $('#queueModal').find('#fullQueue').attr('checked', true).prop('checked', true);
            $('#queueModal .queue_services').hide();
          }

          $('#queueModal #halfQueue').on('click', function() {
            $('#queueModal .queue_services').show();
          });

          $('#queueModal #fullQueue').on('click', function() {
            $('#queueModal .queue_services').hide();
          });

          if (data.ac_toggle == 1) {
            $('#queueModal').find('input#ac_toggle').attr('checked', true).prop('checked', true);
          }

          if (data.moto_toggle == 1) {
            $('#queueModal').find('input#moto_toggle').attr('checked', true).prop('checked', true);
          }

          $('#queueModal .f_day').text(day);

          for (var hour = 0; hour <= 23; hour++) {
            // Loop through minutes from 0 to 30
            for (var minute = 0; minute < 60; minute += 30) {
              // Format the time in HH:MM format
              var time = ('0' + hour).slice(-2) + ':' + ('0' + minute).slice(-2);

              // Create an option element for each time and append it to the select element
              $('#queueModal #f_opentime').append($('<option></option>').val(time).html(time));
              $('#queueModal #f_closetime').append($('<option></option>').val(time).html(time));

              $('#queueModal #f_opentime option[value="' + data.timeopen + '"]').attr('selected', true).prop('selected', true);
              $('#queueModal #f_closetime option[value="' + data.timeclose + '"]').attr('selected', true).prop('selected', true);
            }
          }
          $('#queueModal #f_timeinterval option').each(function() {
            if ($(this).val() == data.timeStep) {
              $(this).attr('selected', true).prop('selected', true);
            }
          })

          oldOpenTime = $('#queueModal select#f_opentime option:selected').val();
          oldCloseTime = $('#queueModal select#f_closetime option:selected').val();
        }, 1000);
      },
      complete: function() {

        setTimeout(function() {
          // $('#queueModal .loader-block').hide();
        }, 1000);
      }
    });

    $('#queueModal').modal('show');
  });

  $('#times-modal button[name="editTimes"]').on('click', function(e) {
    e.preventDefault();

    let timeopen = $('#times-modal #working_hours_open option:selected').val();
    let timeclose = $('#times-modal #working_hours_close option:selected').val();
    let lastIorder = $('.grid[data-date="' + date + '"] .office[data-queue-id="' + queue_id + '"] .time-status').last().attr('data-iorder');
    let changeVal = $('#times-modal .change_options input[name="change"]:checked').val();

    $.ajax({
      url: '/pieraksts/rezervacijas/changeTimes',
      method: 'POST',
      data: {timeopen: timeopen, timeclose: timeclose, timeStep: timeStep, changeVal: changeVal, lastIorder: lastIorder, queue_id: queue_id, date: date},
      beforeSend: function() {
        $('#times-modal .times-modal-body').addClass('loading');
        $('#times-modal .loader').fadeIn();
      },
      success: function(data) {
        // data = JSON.parse(data);
        //
        // let resData = {};
        // resData.times = data;
        // resData.times.queue_id = queue_id;
        // resData.times.date = date;
        // resData.times.changeVal = changeVal;
        //
        // socket.send(JSON.stringify(resData));

        window.location.reload();
      }
    });

  });

  /**
   * getSlotInfo may return takenby as string (JSON), boolean false, or already-parsed object — JSON.parse(object) throws.
   */
  function parseTakenbyFromGetSlotInfo(data) {
    var tb = data && data.takenby;
    if (tb === undefined || tb === null) {
      return false;
    }
    if (typeof tb === 'object') {
      return tb;
    }
    if (typeof tb === 'boolean') {
      return tb ? {} : false;
    }
    if (typeof tb === 'string') {
      if (tb === '' || tb === 'false') {
        return false;
      }
      try {
        return JSON.parse(tb);
      } catch (e) {
        return false;
      }
    }
    return false;
  }

  $(document).on('click', 'button.status', function() {

    let today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed, so we add 1
    const day = String(today.getDate()).padStart(2, '0');

    today = `${year}-${month}-${day}`;

    const $btn = $(this);
    const $timeStatus = $btn.closest('.time-status');
    const $table = $btn.closest('.table[data-queue-id]');
    const $dateCol = $btn.closest('[data-date]');

    slot = $timeStatus;
    iorder = $timeStatus.attr('data-iorder');
    queue_id = $table.attr('data-queue-id');
    date = $dateCol.attr('data-date');
    time = ($timeStatus.children('.time-slot').first().html() || '').trim();

    const tableClass = $table.attr('class') || '';
    const officeMatch = tableClass.match(/office_(\d+)/);
    office = officeMatch ? officeMatch[1] : tableClass.replace(/^.*?table\s+office_/i, '').replace(/\s.*$/, '');

    if (!date || !queue_id || iorder === undefined || iorder === null || iorder === '') {
      console.error('Slot modal: trūkst data-date, queue_id vai data-iorder (HTML struktūra?).', { date: date, queue_id: queue_id, iorder: iorder });
      if (typeof $.toast === 'function') {
        $.toast({ type: 'danger', title: 'Kļūda', message: 'Neizdevās noteikt slotu. Atveriet lapu vēlreiz.' });
      }
      return;
    }

    let discount;
    
    // Check whether the slot is reserved by another user
    $.ajax({
      url: '/pieraksts/check-slot-availability',
      method: 'POST',
      data: {
        queue_id: queue_id,
        date: date,
        iorder: iorder
      },
      async: true,
      success: function(response) {
        if (!response.available && response.reserved_by_other) {
          // Reserved by someone else: show warning
          showSlotReservedWarning(response.expires_in);
          return;
        }
        // Slot is available: proceed to open the modal
        openSlotModal();
      },
      error: function() {
        // On error, still open the modal (best-effort)
        openSlotModal();
      }
    });
    
    function showSlotReservedWarning(expiresIn) {
      let modal = document.getElementById('slot-reserved-warning');
      if (!modal) {
        modal = document.createElement('div');
        modal.id = 'slot-reserved-warning';
        modal.style.cssText = `
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0,0,0,0.5);
          display: flex;
          align-items: center;
          justify-content: center;
          z-index: 10001;
        `;
        document.body.appendChild(modal);
      }

      modal.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 15px; max-width: 500px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
          <div style="font-size: 50px; margin-bottom: 20px;"></div>
          <h2 style="font-size: 22px; margin-bottom: 15px; color: #f59e0b;">Slots ir rezervēts</h2>
          <p style="font-size: 16px; margin-bottom: 10px;">Šo laiku <strong>${time}</strong> pašlaik rezervē cits klients.</p>
          <p style="font-size: 14px; color: #6b7280; margin-bottom: 20px;">Lūdzu, mēģiniet vēlāk.</p>
          <button id="close-reserved-warning" style="background: #3b82f6; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-size: 16px; cursor: pointer; font-weight: 500;">
            Sapratu
          </button>
        </div>
      `;

      modal.style.display = 'flex';

      document.getElementById('close-reserved-warning').onclick = function() {
        modal.remove();
      };

      // Close on overlay click
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          modal.remove();
        }
      });
    }
    
    function openSlotModal() {
      $('#slotModal .loader-block').show();

    let data = {
      iorder: iorder,
      queue_id: queue_id,
      date: date,
    }

    $('#slotModal').modal('show');

    $.ajax({
      url: '/pieraksts/getSlotInfo',
      data: data,
      method: 'POST',
      dataType: 'JSON',
      beforeSend: function() {
        $('#slotModal .record-notif-state ul li').each(function() {
          $(this).find('input').removeAttr('checked').prop('checked', false);
        });
        $('#slotModal ul.services li').each(function() {
          $(this).find('input').removeAttr('checked').prop('checked', false);
        });
        $('#slotModal textarea[name="user_message"]').html('');
        $('#slotModal form').trigger('reset');
      },
      success: function(data) {

        $('#slotModal #f_date option').each(function() {
          $(this).removeAttr('selected').prop('selected', false);
          if (date === $(this).val()) {
            $(this).attr('selected', true).prop('selected', true);
          }
        });
        $('#slotModal #f_time option').each(function() {
          $(this).removeAttr('selected').prop('selected', false);
          if (time === $(this).val()) {
            $(this).attr('selected', true).prop('selected', true);
          }
        });
        $('#slotModal #f_office option').each(function() {
          $(this).removeAttr('selected').prop('selected', false);
          if (String(queue_id) === String($(this).val())) {
            $(this).attr('selected', true).prop('selected', true);
          }
        });

        let takenby = parseTakenbyFromGetSlotInfo(data);
        currentTakenby = takenby && takenby !== false ? takenby : null;
        currentCarInfo = null;

        // Reset car-info UI
        const $carInfoBtn = $('#slotModal #car-info-toggle');
        const $carInfoPre = $('#slotModal #car-info-json');
        $carInfoBtn.off('click.carInfo');
        $carInfoBtn.prop('disabled', true).text('Apskatīt datus');
        $carInfoPre.hide().text('');

        $('.last-info').remove();
        if (takenby === false) {
          $('#record-modal #working_days, #record-modal #working_hours, #record-modal #office_queues').attr('disabled', 'true').prop('disabled', true);
          discount = data.discount;
        } else {

          if (data.edittime == '') {
            $('<div class="last-info">Pieraksts izveidots no ' + data.is_mobile + '<br>Izveidots: ' + data.createtime + ' (' + data.createuser + ')<br>Labots:</div>').insertAfter($('.modal#slotModal .form-group').last());
          } else {
            $('<div class="last-info">Pieraksts izveidots no ' + data.is_mobile + '<br>Izveidots: ' + data.createtime + ' (' + data.createuser + ')<br>Labots: ' + data.edittime + ' (' + data.edituser + ')</div>').insertAfter($('.modal#slotModal .form-group').last());
          }



          $('#record-modal #working_days, #record-modal #working_hours, #record-modal #office_queues').removeAttr('disabled').prop('disabled', false);
          $('#slotModal input[name="cancelId"]').val(takenby.cancelId);
          $('#slotModal #f_car').val(takenby.car_brand);
          $('#slotModal #f_model').val(takenby.car_model);
          $('#slotModal #f_plate').val(takenby.lic_plate);
          $('#slotModal #service .select-service-option option[value="' + takenby.service + '"]').attr('selected', true).prop('selected', true).trigger('change');
          $('#slotModal #save_nr').val(takenby.temp_nr);
          $('#slotModal .rims-with-select-row input[name="flexRadioDefault"][value="' + takenby.rimsWith + '"]').attr('checked', true).prop('checked', true);
          $('#slotModal .services select.select-service-option option').on('change', function() {
            $('#slotModal .rims-with-select-row input[name="flexRadioDefault"][value="' + takenby.rimsWith + '"]').attr('checked', true).prop('checked', true);
          });
          $('#slotModal textarea#f_comment').val(takenby.user_comment);
          $('#slotModal #f_name').val(takenby.name);
          $('#slotModal #f_phone').val(takenby.phone_number);
          $('#slotModal #f_email').val(takenby.email);
          discount = data.comment;
        }

        // Populate car-info UI from dedicated slot columns (fallback: legacy takenby snapshot).
        const rawCarInfoJson = (data && data.car_info_json) ? data.car_info_json : (currentTakenby && currentTakenby.car_info_json ? currentTakenby.car_info_json : '');
        const rawCarInfoVnr = (data && data.car_info_vnr) ? data.car_info_vnr : (currentTakenby && currentTakenby.car_info_vnr ? currentTakenby.car_info_vnr : '');
        const rawCarInfoFetchedAt = (data && data.car_info_fetched_at) ? data.car_info_fetched_at : (currentTakenby && currentTakenby.car_info_fetched_at ? currentTakenby.car_info_fetched_at : '');
        const rawCarInfoSource = (data && data.car_info_source) ? data.car_info_source : (currentTakenby && currentTakenby.car_info_source ? currentTakenby.car_info_source : '');

        currentCarInfo = rawCarInfoJson ? {
          car_info_json: rawCarInfoJson,
          car_info_vnr: rawCarInfoVnr,
          car_info_fetched_at: rawCarInfoFetchedAt,
          car_info_source: rawCarInfoSource
        } : null;

        setCarInfoUIFromSnapshot($carInfoBtn, $carInfoPre, rawCarInfoJson ? {
          json: rawCarInfoJson,
          vnr: rawCarInfoVnr,
          fetchedAt: rawCarInfoFetchedAt,
          source: rawCarInfoSource
        } : null);

        empty_slot = !!(data.office_id && data.takenby == 'false');

        if (discount) {
          let found = false;
          $('.reservation_edit .select-discount-option option').each(function() {
            if ($(this).text() === discount) {
              $(this).attr('selected', true).prop('selected', true);
              $(this).parent().next().hide();
              found = true;
              return false;
            }
          });
          if (found === false) {
            $('.reservation_edit .select-discount-option option:last-child').attr('selected', true).prop('selected', true);
            $('.reservation_edit .select-discount-option').next().html(discount).show();
          }
        } else {
          $('.reservation_edit .select-discount-option option').first().attr('selected', true).prop('selected', true);
          $('.reservation_edit .select-discount-option').next().html('').hide();
        }

        $('.reservation_edit .reservationOption').each(function() {
          if (date == today) {
            $(this).parent().show();
            $(this).on('click', function() {
              if ($(this).is(':checked')) {
                $('.reservation_edit .reservationOption').attr('disabled', true).prop('disabled', true);
                $(this).attr('disabled', false).prop('disabled', false);
              } else {
                $('.reservation_edit .reservationOption').attr('disabled', false).prop('disabled', false);
              }
            })
          } else {
            $(this).parent().hide();
          }
        });


      },
      error: function(xhr) {
        console.error('getSlotInfo:', xhr && xhr.status, xhr && xhr.responseText);
        if (typeof $.toast === 'function') {
          $.toast({ type: 'danger', title: 'Kļūda', message: 'Neizdevās ielādēt slotu datus.' });
        }
      },
      complete: function() {
        setTimeout(function() {
          $('#slotModal .loader-block').hide();
        }, 150);
      }
    });
    } // end of openSlotModal()
  });

  $('#slotModal').on('show.bs.modal', function () {
    $('body').addClass('removeScroll');
  }).on('hide.bs.modal', function () {
    $('body').removeClass('removeScroll');
  }).on('keypress', function(e) {
    if (e.keyCode === 13) {
      $('.submit', this).click();
    }
  });

  // Auto-fill car make/model in the operator modal by plate number (same idea as client booking form).
  $(document).on('blur', '#slotModal #f_plate', function() {
    const plateRaw = $(this).val();
    const vnr = normalizePlate(plateRaw);
    if (!vnr) return;
    if (!isValidPlate(vnr)) {
      setPlateValidityInModal(false);
      return;
    }
    setPlateValidityInModal(true);

    fetchCarInfoSnapshotForPlate(plateRaw)
      .done(function(snap) {
        // Apply to form fields
        const marka = (snap.data && (snap.data.MARKA ?? snap.data.marka)) ? String(snap.data.MARKA ?? snap.data.marka) : '';
        const modelis = (snap.data && (snap.data.MODELIS ?? snap.data.modelis)) ? String(snap.data.MODELIS ?? snap.data.modelis) : '';
        if (marka) $('#slotModal #f_car').val(marka);
        if (modelis) $('#slotModal #f_model').val(modelis);

        // Persist snapshot for save
        currentCarInfo = {
          car_info_json: snap.json,
          car_info_vnr: snap.vnr,
          car_info_fetched_at: snap.fetchedAt,
          car_info_source: snap.source
        };

        // Update "Auto dati" UI
        const $carInfoBtn = $('#slotModal #car-info-toggle');
        const $carInfoPre = $('#slotModal #car-info-json');
        setCarInfoUIFromSnapshot($carInfoBtn, $carInfoPre, {
          json: snap.json,
          vnr: snap.vnr,
          fetchedAt: snap.fetchedAt,
          source: snap.source
        });
      })
      .fail(function() {
        // Keep UX quiet; the admin can still edit manually.
        console.warn('car-info lookup failed for plate', vnr);
      });
  });

  $(document).on('click', '#slotModal .submit', function(e) {
    e.preventDefault();

    let discount;

    if (!discountSelect) {
      discountSelect = $('.reservation_edit .select-discount-option option:selected');
    }

    switch ($(discountSelect).val()) {
      case 'empty': {
        discount = null;
        break;
      }
      case 'other': {
        discount = $('.reservation_edit #f_slotcomment').val();
        break;
      }
      default: {
        discount = $(discountSelect).text();
        break;
      }
    }

    let cancelId = $('#slotModal input[name="cancelId"]').val();
    let car_brand = $('#slotModal #f_car').val();
    let car_model = $('#slotModal #f_model').val();
    let name = $('#slotModal #f_name').val();
    let phone = $('#slotModal #f_phone').val();
    let service = $('#slotModal .services select.select-service-option option:selected').val();
    let rimsWith = $('#slotModal .rims-with-select-row input[name="flexRadioDefault"]:checked').val() || '';
    let temp_nr = $('#slotModal .temp_save_nr input#save_nr').val() || '';
    let plate = parseInt(phone.substr(-3)) || '';
    let lic_plate = isNaN(plate) ? '' : plate;
    let license_plate = $('#slotModal #f_plate').val();
    let user_comment = $('#slotModal #f_comment').val();
    let email = $('#slotModal #f_email').val();
    let new_date = $('#slotModal #f_date option:selected').val();
    let new_time = $('#slotModal #f_time option:selected').val();
    let new_queue = $('#slotModal #f_office option:selected').val();
    let status = $('#slotModal #f_status option:selected').val();
    let f_statuscase = $('.reservationOption[type=checkbox]:checked').val();

    // Client-side validation (especially important when user presses Enter).
    // - Plate must be valid
    // - Service-dependent required fields must be filled
    $('#slotModal #f_plate').trigger('blur');
    const vnr = normalizePlate(license_plate);
    if (!vnr || !isValidPlate(vnr)) {
      setPlateValidityInModal(false);
      $.toast({
        autoDismiss: true,
        title: 'Kļūda!',
        message: 'Nepareizs reģistrācijas numurs.',
      });
      $('#slotModal #f_plate').focus();
      return;
    }
    setPlateValidityInModal(true);

    const $serviceSelect = $('#slotModal .services select.select-service-option');
    $serviceSelect.css('border-color', '');
    if (!service) {
      $serviceSelect.css('border-color', 'red').focus();
      $.toast({
        autoDismiss: true,
        title: 'Kļūda!',
        message: 'Lūdzu, izvēlieties pakalpojumu.',
      });
      return;
    }

    // If service requires extra choices, enforce them before saving.
    if (String(service) === '1' && !rimsWith) {
      $.toast({
        autoDismiss: true,
        title: 'Kļūda!',
        message: 'Lūdzu, izvēlieties “Riepas ar/bez Diskiem”.',
      });
      // Bring the required section into view/focus.
      $('#slotModal .rims-with-select-row input[name="flexRadioDefault"]').first().focus();
      return;
    }

    if (String(service) === '2' && !String(temp_nr || '').trim()) {
      $('#slotModal #save_nr').css('border-color', 'red').focus();
      $.toast({
        autoDismiss: true,
        title: 'Kļūda!',
        message: 'Lūdzu, ievadiet glabāšanas talona numuru.',
      });
      return;
    } else {
      $('#slotModal #save_nr').css('border-color', '');
    }

    let formDataPayload = {
      car_brand: car_brand,
      car_model: car_model,
      rimsWith: rimsWith,
      temp_nr: temp_nr,
      lic_plate: license_plate,
      service: service,
      user_comment: user_comment,
      name: name,
      phone_number: phone,
      email: email,
      status: status,
      cancelId: cancelId,
      slotcomment: (discount === null ? 'null' : discount),
    };
    // Preserve car-info snapshot when editing a booking in admin modal.
    // New: stored in dedicated slot columns; Legacy: stored inside takenby.
    if (currentCarInfo && currentCarInfo.car_info_json) {
      formDataPayload.car_info_json = currentCarInfo.car_info_json;
      formDataPayload.car_info_fetched_at = currentCarInfo.car_info_fetched_at || '';
      formDataPayload.car_info_vnr = currentCarInfo.car_info_vnr || '';
      formDataPayload.car_info_source = currentCarInfo.car_info_source || 'api/car-info';
    } else if (currentTakenby && currentTakenby.car_info_json) {
      formDataPayload.car_info_json = currentTakenby.car_info_json;
      formDataPayload.car_info_fetched_at = currentTakenby.car_info_fetched_at || '';
      formDataPayload.car_info_vnr = currentTakenby.car_info_vnr || '';
      formDataPayload.car_info_source = currentTakenby.car_info_source || 'api/car-info';
    }
    let formData = $.param(formDataPayload);

    let dopParams = {
      iorder: iorder,
      queue_id: queue_id,
      date: date,
      time: time,
      office: office,
      new_date: new_date,
      new_time: new_time,
      new_queue: new_queue,
    };

    $.ajax({
      url: '/pieraksts/rezervacijas/editSlot',
      method: 'POST',
      data: {formData: formData, dopParams: dopParams, f_statuscase: f_statuscase},
      beforeSend: function() {
        $('#slotModal .loader-block').show();
      },
      success: function(data) {
        data = JSON.parse(data);

        if (data.failed) {
          $('#toasts .toast').each(function() {
            $(this).fadeOut(function() {
              $(this).remove();
            });
          });
          $.toast({
            autoDismiss: false,
            title: 'Kļūda!',
            message: data.failed_msg,
          });
        }

        let wsParams = {
          iorder: iorder,
          queue_id: queue_id,
          date: date,
          office: office,
          car_brand: car_brand,
          car_model: car_model,
          plate: plate,
          fullNumber: phone,
          discount: discount,
        };

        let slot_admin;

        if (data.edited_slot_admin) {
          dopParams.new_iorder = data.new_iorder;
          dopParams.new_office = data.new_office;
          slot_admin = {edited_slot_admin:data.edited_slot_admin};
        } else if (data.deleted_slot_admin) {
          slot_admin = {deleted_slot_admin:data.deleted_slot_admin};
        } else if (data.moved_slot_admin) {
          if (data.edited) dopParams.edited = true;
          dopParams.new_iorder = data.new_iorder;
          dopParams.new_office = data.new_office;
          slot_admin = {moved_slot_admin:data.moved_slot_admin};
        }

        let wsData = {
          wsParams: wsParams,
          dopParams: dopParams,
          slot_admin: slot_admin,
          status: data.status,
        };

        window.location.reload();
      },
      complete: function() {

        setTimeout(function() {
          // $('#slotModal #service .form-check').each(function() {
          //   $(this).find('input').removeAttr('checked');
          // });
          // $('#slotModal .loader-block').hide();
          // $('#slotModal form').trigger('reset');
          // $('#slotModal').modal('hide');
        }, 1000);
      }
    });

  });

  $(document).on('change', '#slotModal #service .select-service-option', function() {
    switch ($('option:selected', this).val()) {
      case '1': {
        $('.rims-with-select-row').show();
        $('.temp_save_nr').hide();
        break;
      }
      case '2': {
        $('.rims-with-select-row').hide();
        $('.temp_save_nr').show();
        break;
      }
      default: {
        $('.rims-with-select-row').hide();
        $('.temp_save_nr').hide();
      }
    }
  });

  function formatTime(date) {
    let hours = ('0' + date.getHours()).slice(-2);
    let minutes = ('0' + date.getMinutes()).slice(-2);
    return hours + ':' + minutes;
  }

  // Listen for WebSocket messages
  // socket.addEventListener('message', function(event) {
  //   const data = JSON.parse(event.data);
  //
  //   if (!data.times) {
  //     let slot = $('.schedule-table.reservations_page .grid[data-date="' + data.wsParams.date + '"] .table[data-queue-id="' + data.wsParams.queue_id + '"] .time-status[data-iorder="' + data.wsParams.iorder + '"]');
  //     if (data.new_slot_client) {
  //       slot.removeClass('time-free').removeClass('time-discount').addClass('time-taken').find('button').remove();
  //       let successText = truncateCharacters($.trim(data.wsParams.car_brand) + ' ' + $.trim(data.wsParams.car_model),9,'&mldr;',1) + ' ' + data.wsParams.fullNumber;
  //       slot.append('<button class="slot status taken-slot">' + successText + '</button>').hide().fadeIn();
  //     } else if (data.slot_admin.edited_slot_admin) {
  //       if (data.dopParams.new_date !== data.dopParams.date || data.dopParams.new_queue !== data.dopParams.queue_id || data.dopParams.new_time !== data.dopParams.time) {
  //         slot = $('.schedule-table.reservations_page .grid[data-date="' + data.dopParams.new_date + '"] .table[data-queue-id="' + data.dopParams.new_queue + '"] .time-status[data-iorder="' + data.dopParams.new_iorder + '"]');
  //       }
  //       if (data.status == 3) {
  //         slot.removeClass('time-free').removeClass('time-discount').removeClass('time-taken').addClass('time-closed').find('button').remove();
  //         slot.append('<button class="slot status closed-slot">Slēgts</button>').hide().fadeIn();
  //       } else if (data.status == 0) {
  //         if (data.wsParams.discount) {
  //           slot.removeClass('time-free').removeClass('time-taken').removeClass('time-closed').addClass('time-discount');
  //           slot.find('button.slot').addClass('discount').html(data.wsParams.discount);
  //         }
  //       } else {
  //         slot.removeClass('time-free').removeClass('time-discount').addClass('time-taken').find('button').remove();
  //         let successText = truncateCharacters($.trim(data.wsParams.car_brand) + ' ' + $.trim(data.wsParams.car_model),9,'&mldr;',1) + ' ' + data.wsParams.fullNumber;
  //         if ($.trim(successText).length === 0) successText = 'xxxxx';
  //         if (slot.parent().attr('data-half') == 1) {
  //           if (slot.attr('data-iorder') % 2 == 1) {
  //             slot.append('<button class="slot status text-red taken-slot-admin">' + successText + '</button>').hide().fadeIn();
  //           } else {
  //             slot.append('<button class="slot status taken-slot-admin">' + successText + '</button>').hide().fadeIn();
  //           }
  //         } else {
  //           slot.append('<button class="slot status taken-slot-admin">' + successText + '</button>').hide().fadeIn();
  //         }
  //       }
  //     } else if (data.slot_admin.moved_slot_admin) {
  //       let new_slot = $('.schedule-table.reservations_page .grid[data-date="' + data.dopParams.new_date + '"] .table[data-queue-id="' + data.dopParams.new_queue + '"] .time-status[data-iorder="' + data.dopParams.new_iorder + '"]');
  //
  //       let old_slot_classes = slot.prop('classList');
  //       let new_slot_classes = new_slot.prop('classList');
  //
  //       let old_slot_button = slot.find('button').clone();
  //       let new_slot_button = new_slot.find('button').clone();
  //
  //       new_slot.find('button').first().remove();
  //       slot.find('button').first().remove();
  //       new_slot.append(old_slot_button).hide().fadeIn();
  //       slot.append(new_slot_button).hide().fadeIn();
  //
  //       new_slot.attr('data-old-classes', old_slot_classes);
  //       slot.attr('data-new-classes', new_slot_classes);
  //
  //       new_slot.removeAttr('class').attr('class', new_slot.attr('data-old-classes')).removeAttr('data-old-classes');
  //       slot.removeAttr('class').attr('class', slot.attr('data-new-classes')).removeAttr('data-new-classes');
  //
  //       if (data.dopParams.edited === true) {
  //         let successText = truncateCharacters($.trim(data.wsParams.car_brand) + ' ' + $.trim(data.wsParams.car_model),9,'&mldr;',1) + ' ' + data.wsParams.fullNumber;
  //
  //         new_slot.find('button').remove();
  //         new_slot.append('<button class="slot status taken-slot">' + successText + '</button>').fadeIn();
  //       }
  //
  //       //.removeAttr('class').attr('class', new_slot.attr('data-old-classes')).removeAttr('data-old-classes')
  //
  //       iorder = data.dopParams.new_iorder;
  //       queue_id = data.dopParams.new_queue;
  //       date = data.dopParams.new_date;
  //       time = data.dopParams.new_time;
  //       office = data.dopParams.new_office;
  //       slot = new_slot;
  //     } else if (data.slot_admin.deleted_slot_admin) {
  //       if (data.slot_admin.comment) {
  //         slot.removeClass('time-taken').addClass('time-discount').find('button').remove();
  //         slot.append('<button class="bg-orange-500 hover:bg-orange-200 text-black py-2 px-4 status">' + data.comment.comment + '</button>').fadeIn();
  //       } else {
  //         slot.removeClass('time-taken').removeClass('time-discount').addClass('time-free').find('button').remove();
  //         slot.append('<button class="slot status slot-free"></button>').fadeIn();
  //       }
  //     }
  //   } else {
  //     if (data.times.changeVal) {
  //
  //     }
  //   }
  //
  // });

});

