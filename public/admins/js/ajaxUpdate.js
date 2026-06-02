$(document).ready(function() {

  let edit = false;

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  let main_url = window.location.protocol;
  let pathParts = window.location.pathname.split('/');
  let model = $('#brand_select').data('model');
  let brand_id = $('#brand_select').val();
  let tread_id;
  let current_tread = pathParts[4];
  let current_url;
  let tire_count;

  $(document).on('change', '#seasonChange', function() {
    let season = $('#seasonChange option:selected').val();

    $.ajax({
      url: '/admin/changeSeason',
      data: {season: season},
      method: 'POST',
      dataType: 'json',
      success: function(data) {
        if (data && data.success) {
          window.location.reload();
          return;
        }

        alert('Neizdevās nomainīt sezonu.');
      },
      error: function() {
        alert('Neizdevās nomainīt sezonu.');
      }
    });
  });

  $(document).on('change', 'input.toggle-top40', function(e) {
    e.preventDefault();

    let tire_type = $('#brand_select').data('model');
    let tire_id = $(this).parent().parent().find('.tire_id').val();

    $.ajax({
      method: 'POST',
      url: '/admin/' + tire_type + '/toggleTop',
      data: {'tire_id': tire_id},
      dataType: 'json',
      beforeSend: function() {
        $('input.toggle-top40').attr('disabled', true).prop('disabled', true);
      },
      success: function(data) {
        if (data.success !== true) {
          if ($(this).is(':checked')) {
            $(this).attr('checked', true).prop('checked', true);
          } else {
            $(this).attr('checked', false).prop('checked', false);
          }
        }
        $(this).removeAttr('checked').prop('checked', false);
      },
      complete: function() {
        $('input.toggle-top40').attr('disabled', false).prop('disabled', false);
      }
    });
  });

  $(document).on('click', 'button.edit-banner', function(e) {
    e.preventDefault();
    let url = $(this).parent().parent().find('.banner-link').text();
    current_url = url;
    $('<input type="text" name="url" class="banner-link-input form-control">').insertAfter($(this).parent().parent().find('.banner-link'));
    $('.banner-link-input').val(url);
    $(this).parent().parent().find('.banner-link').remove();
    $(this).removeClass('btn-warning').addClass('btn-success').removeClass('edit-banner').addClass('save-banner');
    $(this).parent().parent().children('.delete-form').find('.delete-banner').addClass('cancel-edit').removeClass('delete-banner').text('Atcelt');
    if ($(document).find('.banner-link-input')) {
      $('.banner-link-input').keypress(function(e) {
        if (e.keyCode == 13) {
          $('.save-banner').click();
        }
      });
    }
  });

  $(document).on('click', 'button.save-banner',function(e) {
    e.preventDefault();
    let url = $(this).parent().parent().find('.banner-link-input').val();
    $(this).parent().parent().children('.edit-form').find('input[type="hidden"][name="url"]').val(url);
    $(this).parent().parent().children('.edit-form').submit();
  });

  $(document).on('click', 'button.cancel-edit', function(e) {
    e.preventDefault();
    let url = $(this).parent().parent().find('.banner-link-input').val();
    $('<span class="banner-link form-control">' + url + '</span>').insertAfter($(this).parent().parent().find('.banner-link-input'));
    $(this).parent().parent().find('.banner-link-input').remove();
    $(this).parent().parent().find('.save-banner').removeClass('btn-success').addClass('btn-warning').removeClass('save-banner').addClass('edit-banner');
    $(this).removeClass('cancel-edit').addClass('delete-banner').text('Dzēst');
  });

  $('.admin-banner-images input.enable-banner').on('change', function(e) {
    e.preventDefault();
    let banner_id = $(this).data('banner-id');
    let enabled = ($(this).prop('checked') === true) ? 1 : 0;
    $.ajax({
      method: 'POST',
      url: '/admin/settings/banners/' + banner_id + '/enable',
      data: {enabled: enabled},
      dataType: 'json',
      beforeSend: function() {
        $('.admin-banner-images input.enable-banner').attr('disabled', true);
      },
      success: function(data) {
        if (data.error) {
          alert(data.error);
        }
      },
      complete: function() {
        $('.admin-banner-images input.enable-banner').removeAttr('disabled');
      }
    });
  })

  $('.services-list input.service_enable').on('change', function(e) {
    e.preventDefault();
    let service_id = $(this).data('service-id');
    let enabled = ($(this).prop('checked') === true) ? 1 : 0;
    $.ajax({
      method: 'POST',
      url: '/admin/settings/services/' + service_id + '/enable',
      data: {enabled: enabled},
      dataType: 'json',
      beforeSend: function() {
        $('.services-list input.service_enable').attr('disabled', true);
      },
      success: function(data) {
        if (data.error) {
          alert(data.error);
        }
      },
      complete: function() {
        $('.services-list input.service_enable').removeAttr('disabled');
      }
    });
  });

  $('.services-list input.service_active').on('change', function(e) {
    e.preventDefault();
    let service_id = $(this).data('service-id');
    let enabled = ($(this).prop('checked') === true) ? 1 : 0;
    $.ajax({
      method: 'POST',
      url: '/admin/settings/services/' + service_id + '/active',
      data: {enabled: enabled},
      dataType: 'json',
      beforeSend: function() {
        $('.services-list input.service_active').attr('disabled', true);
      },
      success: function(data) {
        if (data.error) {
          alert(data.error);
        }
      },
      complete: function() {
        $('.services-list input.service_active').removeAttr('disabled');
      }
    });
  });

  function changeBrands() {
    $('#tread_select').attr('disabled', true);
    $('.brand-settings input[name=brand-id], .make-settings input[name=brand-id]').val(brand_id);
    $.ajax({
      url: main_url + '/admin/' + model + '/tread/' + brand_id + '/ajaxUpdateTreads',
      method: 'POST',
      dataType: 'JSON',
      data: { brand_id: brand_id },
      success: function(data) {
        let season = '';
        if (!$('.brand-settings .brand-input').attr('disabled') || !$('.make-settings .make-input').attr('disabled')) {
          $('#tread_select').attr('disabled', true);
        } else {
          $('#tread_select').attr('disabled', false);
        }
        data.sort();
        let html = '<select name="tread" class="form-control col-md-3" id="tread_select"><option></option>';
        data.forEach(function(value, key) {
          if (value.season !== null && value.season == 1) {
            season = 'Vasaras';
          } else if (value.season !== null && value.season == 2) {
            season = 'Ziemas';
          }
          if (value.tire_count) {
            tire_count = (value.tire_count[0]) ? ' [' + value.tire_count[0].tire_count + ']' : ' [0]';
          } else {
            tire_count = '';
          }
          if (value.season) {
            if (current_tread == value.tread_id) {
              html += '<option value="' + value.tread_id + '" selected>' + value.t_title + ' (' + season + ')' + tire_count + '</option>';
            } else {
              html += '<option value="' + value.tread_id + '">' + value.t_title + ' (' + season + ')' + tire_count + '</option>';
            }
          } else {
            if (current_tread == value.tread_id) {
              html += '<option value="' + value.tread_id + '" selected>' + value.t_title + tire_count + '</option>';
            } else {
              html += '<option value="' + value.tread_id + '">' + value.t_title + tire_count + '</option>';
            }
          }

        });
        html += '</select>';
        $('#tread_select').html(html);
        if ($('.make-settings #tread_select option:selected').val() != '') {
          $('.make-settings .edit-make[type=button]').attr('disabled', false);
          $('.make-settings .delete-make').attr('disabled', false);
        }
      }
    });
  }

  function changeTread() {
    $.ajax({
      url: main_url + '/admin/' + model + '/tread/' + brand_id + '/ajaxUpdateTires',
      method: 'POST',
      dataType: 'JSON',
      data: { tread_id: tread_id },
      success: function() {
        window.location.href = main_url + '/admin/' + model + '/tread/' + tread_id;
      }
    });
  }

  $('#brand_select').on('change', function() {
    brand_id = $(this).val();

    changeBrands();
  });

  $('#tread_select').on('change', function() {
    tread_id = $(this).val();

    if (tread_id) {
      changeTread();
    } else {
      window.location.href = main_url + '/admin/' + model;
    }
  });

  if ($(document).find('.brand-settings').length && $(document).find('.make-settings').length) {
    changeBrands();
  }

  if ($(document).find('.role-settings')) {
    $('.role-settings #myTab .nav-item').each(function() {
      $('a', this).on('click', function() {
        $('.role-settings .revert .delete-role a').attr('href', '/admin/settings/roles/' + $(this).data('id') + '/delete');
      })
    });

    $('.role-settings .tab-pane .permissions .permission').each(function() {
      $('input', this).change(function() {
        let data = $(this).data('switch');
        $.ajax({
          method: 'POST',
          url: '/admin/settings/roles/togglePermission',
          data: {data: data},
          dataType: 'json',
          success: function(data) {
            console.log(data);
          }
        })
      })
    });
  }

  // $(document).on('click', '.service-edit', function(e) {
  //   e.preventDefault();
  //   edit = true;
  //   let $id = $(this).parent().parent().attr('id');
  //   $('input[name="service"]').attr('data-service-id', $id);
  // });

  $(document).on('click', '.service_stop_edit', function(e) {
    e.preventDefault();
    edit = false;
    $('input[name="service_id"]').val('');
    $('input[name="service"]').val('');
    $('input[name="pdf_service"]').val('');
    $('input[name="f_save"]').attr('checked', false).prop('checked', false);
    $('.services_form .card-footer button').removeClass('service_edit_button').addClass('service_add_button').first().text('Izveidot');
    $(this).remove();
  });


  $(document).on('click', '.service_add_button', function(e) {
    e.preventDefault();
    let title = $('input[name="service"]').val();
    let pdf_title = $('input[name="pdf_service"]').val();
    let f_save = $('input[name="f_save"]:checked').val();
    if (title === '') {
      return false;
    }
    if (edit === false) {
      $.ajax({
        url: '/admin/settings/services/add',
        method: 'POST',
        data: { 'title': title, 'pdf_title': pdf_title, 'f_save': f_save },
        dataType: 'JSON',
        success: function(data) {
          if (data.success) {
            let $el = '';
            $el = ($('.no-services').length === 1) ? $('.no-services').first() : $('.services').first();
            $($el).before('<li id="service_' + data.service_id + '" class="services list-group-item d-flex justify-content-between align-items-center">' +
              '<span class="service_title">' + data.service_title + '</span>' +
              '<div class="options" style="display: inline-flex; align-items: center;">' +
              '<input type="checkbox" class="service_enable" data-service-id="' + data.service_id + '" name="service_enable">' +
              '<a href="/admin/settings/services/' + data.service_id + '/edit" style="margin-left: 10px;" class="badge bg-primary rounded-pill service-edit">Labot</a> ' +
              '<a href="/admin/settings/services/' + data.service_id + '/delete" style="margin-left: 10px;" class="badge bg-primary rounded-pill service-delete">Dzēst</a>' +
              '</div>' +
              '</li>');
            $('input[name="service"]').val('');
            $('input[name="pdf_service"]').val('');
            $('input[name="f_save"]').removeAttr('checked').prop('checked', false);
            if ($('.no-services').length === 1) {
              $('.no-services').remove();
            }
          }
        }
      });
    }
  });

  $(document).on('click', '.service_edit_button', function(e) {
    e.preventDefault();
    let title = $('input[name="service"]').val();
    let pdf_title = $('input[name="pdf_service"]').val();
    let f_save = ($('input[name="f_save"]:checked').val()) ? 1 : null;
    if (title === '') {
      return false;
    }
    let $id = $('input[name="service"]').attr('data-service-id');
    $.ajax({
      url: '/admin/settings/services/' + $id.replace('service_', '') + '/edit',
      method: 'POST',
      data: { 'title': title, 'pdf_title': pdf_title, 'f_save': f_save },
      dataType: 'JSON',
      success: function(data) {
        if (data.f_save != null) {
          $('li#' + $id + ' .service_title').attr('data-save', 1);
        } else {
          $('li#' + $id + ' .service_title').removeAttr('data-save');
        }
        edit = false;
        let $value = $('input[name="service"]').val();
        $('li#' + $id + ' .service_title').html($value);
        $('li#' + $id + ' .service_title').attr('data-desc', pdf_title);
        $('input[name="service_id"]').val('');
        $('input[name="pdf_service"]').val('');
        $('input[name="service"]').val('').removeAttr('data-service-id');
        $('input[name="f_save"]').attr('checked', false).prop('checked', false);
        $('.services_form .card-footer button').first().text('Izveidot');
        $('.services_form .card-footer button').first().attr('type', 'submit').removeClass('service_edit_button').addClass('service_add_button');
        $('.services_form .card-footer button').last().remove();
      }
    });
  });

  $(document).on('click', '.services .options a.edit', function(e) {
    e.preventDefault();
    edit = true;
    if (!$('.service_stop_edit').is(':visible')) {
      $('.services_form .card-footer button').first().after('<button class="btn btn-sm btn-primary service_stop_edit" style="margin-left: 5px;"> Atcelt</button>');
    }
    let service_id = $(this).parent().parent().attr('id');
    let service_title = $(this).parent().parent().children().first().text();
    let service_desc = $(this).parent().parent().children().first().attr('data-desc');
    $('input[name="service"]').attr('data-service-id', service_id);
    $('input[name="service_id"]').val(service_id);
    $('input[name="service"]').val(service_title);
    $('input[name="pdf_service"]').val(service_desc);
    if ($(this).parent().parent().children().first().attr('data-save') == 1) $('input[name="f_save"]').attr('checked', true).prop('checked', true);
    $('.services_form .card-footer button').first().text('Labot');
    $('.services_form .card-footer button').first().removeAttr('type').removeClass('service_add_button').addClass('service_edit_button');
  });

  function getAccrualErrorMessage(xhr, data) {
    if (data && data.error) {
      return data.error;
    }
    if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
      return xhr.responseJSON.error;
    }
    if (xhr && xhr.responseText) {
      try {
        const response = JSON.parse(xhr.responseText);
        if (response && response.error) {
          return response.error;
        }
      } catch (e) {
        if (xhr.responseText.trim() !== '') {
          return xhr.responseText.trim();
        }
      }
    }
    return 'Sinhronizācijas kļūda!';
  }

  function showAccrualSyncError(errorMsg, $time) {
    alert(errorMsg);
    $('.logs').prepend(
      '<p style="border-bottom: 1px solid #d8dbe0; color: #c0392b;">' +
      '<strong>Accrual — kļūda!</strong><br>' + errorMsg + '<br>' + $time +
      '</p>'
    );
  }

  function showAccrualSyncSuccess(message, $time) {
    $('.logs').prepend(
      '<p style="border-bottom: 1px solid #d8dbe0; color: #27ae60;">' +
      'Accrual - ' + message + '<br>' + $time +
      '</p>'
    );
    $('.accrual_last_time').html($time);
  }

  $('.card-body.btn').on('click', function() {
    let $btn_id = $(this).attr('id');
    $(this).attr('disabled', true).text('Sinhronizējās...').css('cursor', 'default');

    let $date = new Date();
    const $year = $date.getFullYear();
    const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
    const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
    const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
    const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
    const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

    const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';
    $(this).parents().children().first().find('span').html($time);

    let $sync = '';

    switch ($btn_id) {
      case 'accrual':
        $.ajax({
          url: '/sync/accrual',
          method: 'GET',
          dataType: 'json',
          success: function(data, textStatus, xhr) {
            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();
            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            if (data && data.error) {
              showAccrualSyncError(data.error, $time);
              return;
            }

            const message = (data && data.message) ? data.message : 'Done';
            showAccrualSyncSuccess(message, $time);
          },
          error: function(xhr) {
            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();
            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            showAccrualSyncError(getAccrualErrorMessage(xhr), $time);
          },
          complete: function() {
            $('#' + $btn_id).attr('disabled', false).text('Sinhronizēt').css('cursor', 'pointer');
          }
        });
        $sync = 'Accrual';
        break;
      case 'i3-auto': // Lattako Auto riepu sinhronizācija - AJAX
        $.ajax({
          url: '/sync/i3-auto',
          method: 'GET',
          success: function(data) {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('<p style="border-bottom: 1px solid #d8dbe0;">Lattako - ' + data + '<br>' + $time + '</p>').prependTo($('.logs'));
            $('.i3auto_last_time').html($time);
          },
          error: function() {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('.logs').prepend('<p style="border-bottom: 1px solid #d8dbe0">Lattako Auto<br>Sinhronizācijas kļūda!<br>' + $time + '</p>');
            $('.i3auto_last_time').html($time);
          },
          complete: function() {
            $('#' + $btn_id).attr('disabled', false).text('Sinhronizēt').css('cursor', 'pointer');
          }
        });
        $sync = 'Lattako Auto';
        break;
      case 'i3-alloy-rims': // Lattako Auto disku sinhronizācija - AJAX
        $.ajax({
          url: '/sync/i3-alloy-rims',
          method: 'GET',
          success: function(data) {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('<p style="border-bottom: 1px solid #d8dbe0;">Lattako auto disku - ' + data + '<br>' + $time + '</p>').prependTo($('.logs'));
            $('.i3alloyrims_last_time').html($time);
          },
          error: function() {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('.logs').prepend('<p style="border-bottom: 1px solid #d8dbe0">Lattako Auto disku<br>Sinhronizācijas kļūda!<br>' + $time + '</p>');
            $('.i3alloyrims_last_time').html($time);
          },
          complete: function() {
            $('#' + $btn_id).attr('disabled', false).text('Sinhronizēt').css('cursor', 'pointer');
          }
        });
        $sync = 'Lattako Auto diski';
        break;
      case 'i3-moto': // Lattako Moto riepu sinhronizācija - AJAX
        $.ajax({
          url: '/sync/i3-moto',
          method: 'GET',
          success: function(data) {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('<p style="border-bottom: 1px solid #d8dbe0;">Lattako - ' + data + '<br>' + $time + '</p>').prependTo($('.logs'));
            $('.i3moto_last_time').html($time);
          },
          error: function() {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('.logs').prepend('<p style="border-bottom: 1px solid #d8dbe0">Lattako Moto<br>Sinhronizācijas kļūda!<br>' + $time + '</p>');
            $('.i3moto_last_time').html($time);
          },
          complete: function() {
            $('#' + $btn_id).attr('disabled', false).text('Sinhronizēt').css('cursor', 'pointer');
          }
        });
        $sync = 'Lattako Moto';
        break;
      case 'i3-quadr': // Lattako Kvadraciklu riepu sinhronizācija - AJAX
        $.ajax({
          url: '/sync/i3-quadr',
          method: 'GET',
          success: function(data) {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('<p style="border-bottom: 1px solid #d8dbe0;">Lattako - ' + data + '<br>' + $time + '</p>').prependTo($('.logs'));
            $('.i3quadr_last_time').html($time);
          },
          error: function() {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('.logs').prepend('<p style="border-bottom: 1px solid #d8dbe0">Lattako Kvadraciklu<br>Sinhronizācijas kļūda!<br>' + $time + '</p>');
            $('.i3quadr_last_time').html($time);
          },
          complete: function() {
            $('#' + $btn_id).attr('disabled', false).text('Sinhronizēt').css('cursor', 'pointer');
          }
        });
        $sync = 'Lattako Kvadracikli';
        break;
      case 'duell-moto': // Duell Moto riepu sinhronizācija - AJAX
        $.ajax({
          url: '/sync/duell-moto',
          method: 'GET',
          success: function(data) {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('<p style="border-bottom: 1px solid #d8dbe0;">Duell - ' + data + '<br>' + $time + '</p>').prependTo($('.logs'));
            $('.duellmoto_last_time').html($time);
          },
          error: function() {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('.logs').prepend('<p style="border-bottom: 1px solid #d8dbe0">Duell Moto<br>Sinhronizācijas kļūda!<br>' + $time + '</p>');
            $('.duellmoto_last_time').html($time);
          },
          complete: function() {
            $('#' + $btn_id).attr('disabled', false).text('Sinhronizēt').css('cursor', 'pointer');
          }
        });
        $sync = 'Duell Moto';
        break;
      case 'duell-quadr': // Duell Kvadraciklu riepu sinhronizācija - AJAX
        $.ajax({
          url: '/sync/duell-quadr',
          method: 'GET',
          success: function(data) {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('<p style="border-bottom: 1px solid #d8dbe0;">Duell - ' + data + '<br>' + $time + '</p>').prependTo($('.logs'));
            $('.duellquadr_last_time').html($time);
          },
          error: function() {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('.logs').prepend('<p style="border-bottom: 1px solid #d8dbe0">Duell Kvadraciklu<br>Sinhronizācijas kļūda!<br>' + $time + '</p>');
            $('.duellquadr_last_time').html($time);
          },
          complete: function() {
            $('#' + $btn_id).attr('disabled', false).text('Sinhronizēt').css('cursor', 'pointer');
          }
        });
        $sync = 'Duell Kvadracikli';
        break;
      case 'i3-big': // Lattako Lielo riepu (Truck) sinhronizācija - AJAX
        $.ajax({
          url: '/sync/i3-big',
          method: 'GET',
          success: function(data) {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('<p style="border-bottom: 1px solid #d8dbe0;">Lattako - ' + data + '<br>' + $time + '</p>').prependTo($('.logs'));
            $('.i3big_last_time').html($time);
          },
          error: function() {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('.logs').prepend('<p style="border-bottom: 1px solid #d8dbe0">Lattako Lielās riepas (Truck)<br>Sinhronizācijas kļūda!<br>' + $time + '</p>');
            $('.i3big_last_time').html($time);
          },
          complete: function() {
            $('#' + $btn_id).attr('disabled', false).text('Sinhronizēt').css('cursor', 'pointer');
          }
        });
        $sync = 'Lattako Lielās riepas (Truck)';
        break;
      case 'i3-agro': // Lattako Lielo riepu (Agro) sinhronizācija - AJAX
        $.ajax({
          url: '/sync/i3-agro',
          method: 'GET',
          success: function(data) {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('<p style="border-bottom: 1px solid #d8dbe0;">Lattako - ' + data + '<br>' + $time + '</p>').prependTo($('.logs'));
            $('.i3agro_last_time').html($time);
          },
          error: function() {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('.logs').prepend('<p style="border-bottom: 1px solid #d8dbe0">Lattako Lielās riepas (Agro)<br>Sinhronizācijas kļūda!<br>' + $time + '</p>');
            $('.i3agro_last_time').html($time);
          },
          complete: function() {
            $('#' + $btn_id).attr('disabled', false).text('Sinhronizēt').css('cursor', 'pointer');
          }
        });
        $sync = 'Lattako Lielās riepas (Agro)';
        break;
      case 'gy-auto': // GoodYear Auto riepu sinhronizācija - AJAX
        $.ajax({
          url: '/sync/goodyear',
          method: 'GET',
          success: function(data) {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('<p style="border-bottom: 1px solid #d8dbe0;">GoodYear - ' + data + '<br>' + $time + '</p>').prependTo($('.logs'));
            $('.gy_last_time').html($time);
          },
          error: function() {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('.logs').prepend('<p style="border-bottom: 1px solid #d8dbe0">GoodYear Auto<br>Sinhronizācijas kļūda!<br>' + $time + '</p>');
            $('.gy_last_time').html($time);
          },
          complete: function() {
            $('#' + $btn_id).attr('disabled', false).text('Sinhronizēt').css('cursor', 'pointer');
          }
        })
        $sync = 'GoodYear Auto';
        break;

      case 'rz-auto':
        $.ajax({
          url: '/sync/rz-auto',
          method: 'GET',
          success: function(data) {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('<p style="border-bottom: 1px solid #d8dbe0;">Riepu zona - ' + data + '<br>' + $time + '</p>').prependTo($('.logs'));
            $('.rz_last_time').html($time);
          },
          error: function() {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('.logs').prepend('<p style="border-bottom: 1px solid #d8dbe0">Riepu zona auto riepas<br>Sinhronizācijas kļūda!<br>' + $time + '</p>');
            $('.rz_last_time').html($time);
          },
          complete: function() {
            $('#' + $btn_id).attr('disabled', false).text('Sinhronizēt').css('cursor', 'pointer');
          }
        })
        $sync = 'Riepu zona auto riepas';
        break;

      case 'rg-auto':
        $.ajax({
          url: '/sync/rg-auto',
          method: 'GET',
          success: function(data) {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('<p style="border-bottom: 1px solid #d8dbe0;">Riepu Garāža - ' + data + '<br>' + $time + '</p>').prependTo($('.logs'));
            $('.rg_last_time').html($time);
          },
          error: function() {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('.logs').prepend('<p style="border-bottom: 1px solid #d8dbe0">Riepu Garāža (ecom XML)<br>Sinhronizācijas kļūda!<br>' + $time + '</p>');
            $('.rg_last_time').html($time);
          },
          complete: function() {
            $('#' + $btn_id).attr('disabled', false).text('Sinhronizēt').css('cursor', 'pointer');
          }
        })
        $sync = 'Riepu Garāža auto riepas';
        break;

      case 'starco-big': // GoodYear Auto riepu sinhronizācija - AJAX
        $.ajax({
          url: '/sync/starco',
          method: 'GET',
          success: function(data) {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('<p style="border-bottom: 1px solid #d8dbe0;">Bohnenkamp - ' + data + '<br>' + $time + '</p>').prependTo($('.logs'));
            $('.starcobig_last_time').html($time);
          },
          error: function() {

            let $date = new Date();
            const $year = $date.getFullYear();
            const $month = ($date.getMonth() < 10) ? '0' + parseInt($date.getMonth() + 1) : $date.getMonth();
            const $day = ($date.getDate() < 10) ? '0' + $date.getDate() : $date.getDate();
            const $hours = ($date.getHours() < 10) ? '0' + $date.getHours() : $date.getHours();
            const $mins = ($date.getMinutes() < 10) ? '0' + $date.getMinutes() : $date.getMinutes();
            const $secs = ($date.getSeconds() < 10) ? '0' + $date.getSeconds() : $date.getSeconds();

            const $time = '<b>' + $year + '-' + $month + '-' + $day + ' ' + $hours + ':' + $mins + ':' + $secs + '</b>';

            $('.logs').prepend('<p style="border-bottom: 1px solid #d8dbe0">Bohnenkamp lielās riepas<br>Sinhronizācijas kļūda!<br>' + $time + '</p>');
            $('.starcobig_last_time').html($time);
          },
          complete: function() {
            $('#' + $btn_id).attr('disabled', false).text('Sinhronizēt').css('cursor', 'pointer');
          }
        })
        $sync = 'Bohnenkamp lielās riepas';
        break;
    }

    $('.logs').prepend('<p style="border-bottom: 1px solid #d8dbe0;">Uzsākta sinhronizācija - ' + $sync + '<br>' + $time + '</p>');

  });



  $('tr.odd td').on('mouseover', function() {
    $(this).parent().css('background', '#d8dbe0');
  }).on('mouseout', function() {
    $(this).parent().removeAttr('style');
  });


  $('#ordersForm select').on('change', function(){
    $('#ordersForm').submit();
  });


});

$(document).ready(function () {
  $('.alloy_rims .link').each(function() {
    $(this).on('click', function() {
      window.location.href = ($(this).attr('href') !== '') ? $(this).attr('href') : '/admin/rims/';
    });
  });

  $('.queueTable.records .subheader svg').on('click', function() {
    $('.modal#queueModal input[name="queue_id"]').val($(this).data('queue-id'));
    $('.modal#queueModal input[name="date"]').val($(this).data('date'));

    $.ajax({
      method: 'GET',
      url: '/admin/pieraksts/queue_ajax/' + $(this).data('queue-id') + '/' + $(this).data('date'),
      dataType: 'JSON',
      success: function(data) {
        $('.modal#queueModal .title').text(data.f_office);
        $('.modal#queueModal input#title').val(data.f_title);
        (data.f_visible == 1) ? $('.modal#queueModal .time #isActive').attr('checked', true) : $('.modal#queueModal .time #isActive').removeAttr('checked');
        $('.modal#queueModal .time #openTime').val(data.f_opentime);
        $('.modal#queueModal .time #closeTime').val(data.f_closetime);
        $('.modal#queueModal .f_day').html(data.f_day);
        if (data.f_rows === 1) {
          $('#gridRadios6').removeAttr('checked', true);
          $('#gridRadios5').attr('checked', true);
        } else {
          $('#gridRadios5').removeAttr('checked', true);
          $('#gridRadios6').attr('checked', true);
        }
      }
    })
  });

  $('.modal#queueModal .submit').on('click', function(e) {
    e.preventDefault();
    $.ajax({
      method: 'POST',
      url: '/admin/pieraksts/queue_ajax/' + $('.modal#queueModal input[name="queue_id"]').val() + '/' + $('.modal#queueModal input[name="date"]').val(),
      data: {
        'q': $('.modal#queueModal input[name="queue_id"]').val(),
        'd': $('.modal#queueModal input[name="date"]').val(),
        'title': $('.modal#queueModal #title').val(),
        'isActive': $('.modal#queueModal #isActive').is(':checked'),
        'opentime': $('.modal#queueModal #openTime').val(),
        'closetime': $('.modal#queueModal #closeTime').val(),
        'f_purpose': $('.modal#queueModal input[name=gridRadios]:checked').val(),
        'f_rows': $('.modal#queueModal input[name=rows]:checked').val(),
      },
      dataType: 'JSON',
      success: function (data) {
        if (data.status === 0) {
          this.error(data.status_text);
        }
        location.reload();
      },
      error: function(data) {
        console.log(data.responseText);
        $('<div class="alert alert-danger">' + data.status_text + '</div>').insertAfter($('.modal#queueModal input[type=hidden]').last());
      }
    });
  });

  $('.queueTable.records .buttonbar svg').on('click', function() {
    $('.modal#slotModal input[name="queue_id"]').val($(this).data('queue-id'));
    $('.modal#slotModal input[name="date"]').val($(this).data('date'));
    $('.modal#slotModal input[name="slot"]').val($(this).data('slot-id'));

    $.ajax({
      method: 'GET',
      url: '/admin/pieraksts/slot_ajax/' + $(this).data('queue-id') + '/' + $(this).data('date') + '/' + $(this).data('slot-id'),
      dataType: 'JSON',
      success: function(data) {
        $('.modal#slotModal h6.title').text(data.f_office + ', ' + data.f_date + ' ' + data.f_time);
        /*if (parseInt(data.f_status) === 2) {
          $('select#f_status').append($('<option>', {value:2, text:'Akcija', selected: 'selected'}));
        } else {
          $('select#f_status option').each(function() {
            $(this).removeAttr('selected');
            if ($(this).val() === data.f_status) {
              $(this).attr('selected', 'selected');
            }
          });
        }*/
        $('.modal#slotModal #f_slotcomment').text(data.f_slotcomment);
      }
    })
  });

   $('.slotSettings#slotModal .submit').on('click', function(e) {
     e.preventDefault();
     $.ajax({
       method: 'POST',
       url: '/admin/pieraksts/slot_ajax/' + $('.modal#slotModal input[name="queue_id"]').val() + '/' + $('.modal#slotModal input[name="date"]').val() + '/' + $('.modal#slotModal input[name="slot"]').val(),
       data: {
         'queue_id': $('.modal#slotModal input[name="queue_id"]').val(),
         'date': $('.modal#slotModal input[name="date"]').val(),
         'slot_id': $('.modal#slotModal input[name="slot"]').val(),
         //'f_status': $('.modal#slotModal #f_status').val(),
         'f_slotcomment': $('.modal#slotModal #f_slotcomment').val(),
         'f_editTime': $('.modal#slotModal #f_editTime').val()
       },
       dataType: 'JSON',
       success: function (data) {
         if (data.status === 0) {
           this.error(data);
         }
         location.reload();
       },
       error: function(data) {
         console.log(data);
       }
     });
   });

  $('.queueTable .discount').each(function() {
    $(this).on('click', function() {

      let checked;

      if ($(this).is(':checked')) {
        checked = 1;
        $(this).parent().children('.slot-comment').html(' -20% darbam ! ! !');
      } else {
        checked = 0;
        $(this).parent().children('.slot-comment').html('');
      }

      $.ajax({
        method: 'POST',
        url: '/admin/pieraksts/discount',
        data: {'slot_id': $(this).data('slot-id'), 'checked': checked},
      });
    });
  });

  $('.queueTable.reservation .buttonbar svg').on('click', function() {

    if ($('.last-info').length) {
	    $('.last-info').remove();
    }
    if ($('.temp_save_nr').length) {
	    $('.temp_save_nr').remove();
    }

    $('.slotSettings #f_time option').each(function() {
      $(this).removeAttr('selected');
      if ($(this).val() == __date) {
        $(this).parent().val($(this).val());
        $(this).attr('selected', true).prop('selected', true);
      }
    });

    let __date = $(this).data('date');

    $('.modal#slotModal input[name="queue_id"]').val($(this).data('queue-id'));
    $('.modal#slotModal #f_date option').each(function() {
      $(this).removeAttr('selected');
      if ($(this).val() == __date) {
        $(this).parent().val($(this).val());
        $(this).attr('selected', true).prop('selected', true);
      }
    });
    $('.modal#slotModal input[name="date"]').val($(this).data('date'));
    $('.modal#slotModal input[name="slot"]').val($(this).data('slot-id'));
    $('.modal#slotModal input[name="part"]').val($(this).data('slot-part'));

    $.ajax({
      method: 'GET',
      url: '/admin/rezervacijas/slot_ajax/' + $(this).data('queue-id') + '/' + $(this).data('date') + '/' + $(this).data('slot-id') + '/' + $(this).data('slot-part'),
      dataType: 'JSON',
      success: function(data) {
        $('.modal#slotModal #f_time').val(data.f_time);
        $('.modal#slotModal select#f_office option[value="' + data.q + data.f_office + '"]').attr('selected','selected');
        $('.modal#slotModal #f_car').val(data.f_car);
        $('.modal#slotModal #f_model').val(data.f_model);
        $('.modal#slotModal #f_plate').val(data.f_plate);
        $('.modal#slotModal input[name="serviceOption"]').each(function() {
          $(this).removeAttr('checked');
	        if ($(this).val() == data.f_purpose) {
            $(this).attr('checked', true).prop('checked', true);
            if ($(this).data('save') == 1) {
	            $('<div class="form-group row bg-light temp_save_nr"><label for="save_nr" class="col-sm-3 pt-3" style="text-align:right;">Glabāšanas talona numurs:</label><div class="col-sm-9"><input type="text" class="form-control" id="save_nr" value="' + data.f_storagebin + '"></div><div class="col-sm-3"></div><div class="col-sm-9" style="font-size: 11px;line-height: 15px;margin-left: -12px;">Ja Jums pašlaik nav zināms glabāšanas talona numurs, tas nekas, atradīsim Jūsu riepas vai riteņus pēc automašīnas numura</div></div>').insertAfter('.services');
            }
	        }
        });
        $('.modal#slotModal #f_comment').html(data.f_comment);
        $('.modal#slotModal #f_name').val(data.f_name);
        $('.modal#slotModal #f_phone').val(data.f_phone);
        $('.modal#slotModal #f_email').val(data.f_email);
        $('.modal#slotModal #f_status').val(data.f_status);
        $('.modal#slotModal #f_slotcomment').html(data.f_slotcomment);
	if (data.p == 'a') {
	  if (data.f_edittime == '') {
	    $('<div class="last-info">Izveidots: ' + data.f_createtime + ' (' + data.f_createuser + ')<br>Labots:</div>').insertAfter($('.modal#slotModal .form-group').last());
	  } else {
	    $('<div class="last-info">Izveidots: ' + data.f_createtime + ' (' + data.f_createuser + ')<br>Labots: ' + data.f_edittime + ' (' + data.f_edituser + ')</div>').insertAfter($('.modal#slotModal .form-group').last());
	  }
        } else {
	  if (data.f_edittime2 == '') {
	    $('<div class="last-info">Izveidots: ' + data.f_createtime2 + ' (' + data.f_createuser2 + ')<br>Labots:</div>').insertAfter($('.modal#slotModal .form-group').last());
	  } else {
	    $('<div class="last-info">Izveidots: ' + data.f_createtime2 + ' (' + data.f_createuser2 + ')<br>Labots: ' + data.f_edittime2 + ' (' + data.f_edituser2 + ')</div>').insertAfter($('.modal#slotModal .form-group').last());
	  }
	}
      }
    })
  });

  $('.modal#slotModal').on('hide.bs.modal', function() {
    if ($('.temp_save_nr').is(':visible')) {
      $('.modal#slotModal .temp_save_nr').remove();
    }
  });

  if (!$('.modal#slotModal #f_editTime').length) {
    $('.modal#slotModal .submit').on('click', function(e) {
      e.preventDefault();

      $.ajax({
        method: 'POST',
        url: '/admin/rezervacijas/slot_ajax/' + $('.modal#slotModal input[name="queue_id"]').val() + '/' + $('.modal#slotModal input[name="date"]').val() + '/' + $('.modal#slotModal input[name="slot"]').val() + '/' + $('.modal#slotModal input[name="part"]').val(),
        data: {
          'f_office': $('.modal#slotModal #f_office').val(),
          'f_date': $('.modal#slotModal #f_date').val(),
          'f_time': $('.modal#slotModal #f_time').val(),
          'f_status': $('.modal#slotModal #f_status').val(),
          'f_car': $('.modal#slotModal #f_car').val(),
          'f_model': $('.modal#slotModal #f_model').val(),
          'f_plate': $('.modal#slotModal #f_plate').val(),
          'f_purpose': $('.modal#slotModal input[name="serviceOption"]:checked').val(),
          'f_storagebin': $('.modal#slotModal #f_storagebin').val(),
          'f_comment': $('.modal#slotModal #f_comment').val(),
          'f_name': $('.modal#slotModal #f_name').val(),
          'f_phone': $('.modal#slotModal #f_phone').val(),
          'f_email': $('.modal#slotModal #f_email').val(),
          'f_slotcomment': $('.modal#slotModal #f_slotcomment').val(),
        },
        dataType: 'JSON',
        success: function (data) {
          if (data.status === 0) {
            this.error(data);
          }
          location.reload();
        },
        error: function(data) {
          console.log(data);
        }
      });
    });
  }

  $('.modal#queueModal .decline').on('click', function() {
    $('.modal#queueModal input[name="gridRadios"]').first().attr('checked', true).prop('checked', true);
    $('.modal#queueModal input[name="rows"]').first().attr('checked', true).prop('checked', true);
  });

  $('.modal#slotModal .decline').on('click', function() {
    $('.modal#slotModal input[name="gridRadios"]').each(function() {
      $(this).attr('checked', false);
    });
    $('.modal#slotModal textarea').each(function() {
      $(this).html('');
    })
  });

});

let shipping_city = $('select.custom-select[name=shipping_city] option:selected').val();
let shipping_address = $('input[name=shipping_address]').val();

if ($('select.custom-select[name=delivery_address]').val() == 3) {
	$('select.custom-select[name=delivery_address]').parent().parent().next().show();
}

$('select.custom-select[name=shipping_city]').on('change', function() {
	shipping_city = $(this).val();
});

$('select[name=delivery_address]').on('change', function() {
	if ($(this).val() == 3) {
		$(this).parent().parent().next().show();
		shipping_city = $('select.custom-select[name=shipping_city] option:selected').val();
	} else {
		$(this).parent().parent().next().hide();
	}
});

let buttons = '<button type="submit" style="width: 49%;" class="btn btn-success change-price">Saglabāt</button>';
buttons = buttons + '<button type="button" style="width: 49%;" class="btn btn-danger cancel-edit">Atcelt</button>';

let button = $('.edit-price').parent().html();

if (button) button = button.trim();

let inputs = {};
let $id = 0;

let $input = '';

$('.prices_form tbody tr').each(function() {

	$(this).on('click', '.edit-price', function(e) {
		e.preventDefault();
		let field_count = $(this).parent().parent().children(':visible');
		field_count.each(function(e) {
			if(e === field_count.length-1) {
				return;
			}
			$input = $(this).html();
			$(this).html('<input type="text" class="form-control" value="' + $input + '">');
			$(this).parent().children().last().css({'display': 'flex', 'justify-content': 'space-around'}).html(buttons);
		});
	});

	$(this).on('click', '.cancel-edit', function(e) {
		e.preventDefault();
		let field_count = $(this).parent().parent().children(':visible');
		$input = $(this).parent().parent();
		let abbr = $input.data('abbr');
		let name = $input.data('name');
		let price = $input.data('value');

		field_count.each(function(e) {
			if (e === field_count.length-1) {
				return;
			}
			if (e === 0) $(this).html(abbr);
			if (e === 1) $(this).html(name);
			if (e === 2) $(this).html(price);
			$(this).parent().children().last().removeAttr('style').html(button);
		});
		$('.alert.price-alert').each(function() {
			$(this).hide();
		});
	});

	$(this).on('click', '.change-price', function(e) {
		e.preventDefault();
		let field_count = $(this).parent().parent().children(':visible');
		field_count.each(function(e) {
			if (e === field_count.length-1) {
				return;
			}

			$id = $(this).parent().children().first().data('id');

			$input = $(this).children().val();
			if (e === 0) inputs.text = $input;
			if (e === 1) inputs.name = $input;
			if (e === 2) inputs.price = $input;


		});
		$.ajax({
	                url: '/admin/settings/prices/' + $id + '/update',
        	        method: 'POST',
                	data: {'inputs': inputs, '_token': $('meta[name="csrf-token"]').attr('content')},
                        dataType: 'JSON',
			success: function(data) {
				$('.alert.price-alert').each(function() {
                                        $(this).hide();
                                });
				if (data.success) {
					field_count.not(':last').each(function(e) {
						let edited_input = $('input', this).val();
						if (e === 0) $(this).parent().attr('data-abbr', edited_input);
						if (e === 1) $(this).parent().attr('data-name', edited_input);
						if (e === 2) $(this).parent().attr('data-value', edited_input);
						$(this).html(edited_input);
					});
					field_count.last().html(button);
					$('.alert-success.price-alert').show().children().first().html(data.success);
				} else if (data.warning) {
					$('.alert-warning.price-alert').show().children().first().html(data.warning);
				} else {
					$('.alert-danger.price-alert').show().children().first().html(data.danger);
				}
                        }
                });

	});
});

$('.price-alert button.close').on('click', function(e) {
	e.preventDefault();
	$(this).parent().hide();
});

$('.make-settings .edit-make').attr('disabled', true);
$('.make-settings .delete-make').attr('disabled', true);

$(document).ready(function() {
  $('<input type="hidden" name="brand-id" value="' + $('.brand-settings #brand_select option:selected').val() + '">').insertBefore($('.brand-input'));
  $('<input type="hidden" name="brand-id" value="' + $('.brand-settings #brand_select option:selected').val() + '">').insertBefore($('.make-input'));
});

// Brendu iestatījumi

$('.brand-settings').on('click', '.new-brand[type=button]', function(e) {
  if ($(this).attr('type') != 'submit') e.preventDefault();
  $('.brand-input').attr('disabled', false);
  $(this).attr('disabled', true).css('cursor', 'default').attr('type', 'submit').attr('name', 'new-brand').attr('value', 'true');
  $('.edit-brand').addClass('btn-danger').addClass('stop-new-brand').removeClass('btn-warning').removeClass('edit-brand').text('Atcelt');
  $('.delete-brand').hide();
  $('#brand_select, #tread_select, .new-make, .edit-make, .delete-make').attr('disabled', 'true').css('cursor', 'default');
});

$('.brand-settings').on('click', '.stop-new-brand', function(e) {
  if ($(this).attr('type') != 'submit') e.preventDefault();
  $('.brand-input').attr('disabled', true).val('');
  $('.new-brand').attr('disabled', false).css('cursor', 'pointer').attr('type', 'button').removeAttr('name').removeAttr('value');
  $('.stop-new-brand').addClass('btn-warning').addClass('edit-brand').removeClass('btn-danger').removeClass('stop-new-brand').text('Labot');
  $('.delete-brand').show();
  $('.edit-brand').attr('disabled', false).css('cursor', 'pointer');
  $('#brand_select, #tread_select, .new-make, .edit-make, .delete-make').removeAttr('disabled').css('cursor', 'pointer');
});

$('.brand-settings').on('click', '.edit-brand[type=button]', function(e) {
  if ($(this).attr('type') != 'submit') e.preventDefault();
  $('.brand-input').attr('disabled', false).val($('.brand-settings #brand_select option:selected').text());
  $('.new-brand').addClass('btn-warning').addClass('edit-brand').removeClass('btn-success').removeClass('new-brand').attr('type', 'submit').attr('name', 'edit-brand').attr('value', 'true').text('Labot');
  $('.edit-brand').last().addClass('btn-danger').addClass('stop-edit-brand').removeClass('btn-warning').removeClass('edit-brand').text('Atcelt');
  $('.delete-brand').hide();
  $('#brand_select, #tread_select, .new-make, .edit-make, .delete-make').attr('disabled', true).css('cursor', 'default');
});

$('.brand-settings').on('click', '.stop-edit-brand', function(e) {
  if ($(this).attr('type') != 'submit') e.preventDefault();
  $('.brand-input').attr('disabled', true).val('');
  $('.stop-edit-brand').addClass('btn-warning').addClass('edit-brand').removeClass('btn-danger').removeClass('stop-edit-brand').text('Labot');
  $('.edit-brand').first().addClass('btn-success').addClass('new-brand').removeClass('btn-warning').removeClass('edit-brand').attr('type', 'button').removeAttr('name').removeAttr('value').text('Izveidot');
  $('.delete-brand').show();
  $('.new-brand').attr('disabled', false).css('cursor', 'pointer');
  $('#brand_select, #tread_select, .new-make, .edit-make, .delete-make').removeAttr('disabled').css('cursor', 'pointer');
});

// Modeļu iestatījumi

$('.make-settings').on('click', '.new-make[type=button]', function(e) {
  if ($(this).attr('type') != 'submit') e.preventDefault();
  $('.make-input').attr('disabled', false);
  $(this).attr('disabled', true).css('cursor', 'default').attr('type', 'submit').attr('name', 'new-make').attr('value', 'true');
  $('.edit-make').addClass('btn-danger').addClass('stop-new-make').removeClass('btn-warning').removeClass('edit-make').removeAttr('disabled').text('Atcelt');
  $('.delete-make').hide();
  $('#brand_select, #tread_select, .new-brand, .edit-brand, .delete-brand').attr('disabled', 'true').css('cursor', 'default');
  $('.make-season').show();
});

$('.make-settings').on('click', '.stop-new-make', function(e) {
  if ($(this).attr('type') != 'submit') e.preventDefault();
  $('.make-input').attr('disabled', true).val('');
  $('.new-make').attr('disabled', false).css('cursor', 'pointer').attr('type', 'button').removeAttr('name').removeAttr('value');
  $('.stop-new-make').addClass('btn-warning').addClass('edit-make').removeClass('btn-danger').removeClass('stop-new-make').text('Labot');
  $('.delete-make').show();
  $('.edit-make').attr('disabled', false).css('cursor', 'pointer');
  $('#brand_select, #tread_select, .new-brand, .edit-brand, .delete-brand').removeAttr('disabled').css('cursor', 'pointer');
  $('.make-season').hide();
});

$('.make-settings').on('click', '.edit-make[type=button]', function(e) {
  if ($(this).attr('type') != 'submit') e.preventDefault();
  $('.make-input').attr('disabled', false).val($('.make-settings #tread_select option:selected').text());
  $('.new-make').addClass('btn-warning').addClass('edit-make').removeClass('btn-success').removeClass('new-make').attr('type', 'submit').attr('name', 'edit-make').attr('value', 'true').text('Labot');
  $('.edit-make').last().addClass('btn-danger').addClass('stop-edit-make').removeClass('btn-warning').removeClass('edit-make').text('Atcelt');
  $('.delete-make').hide();
  $('#brand_select, #tread_select, .new-brand, .edit-brand, .delete-brand').attr('disabled', true).css('cursor', 'default');
  if ($('.make-input').val().length == 0) $('.edit-make[type=submit]').attr('disabled', true).css('cursor', 'default');
  $('.make-season').show();
});

$('.make-settings').on('click', '.stop-edit-make', function(e) {
  if ($(this).attr('type') != 'submit') e.preventDefault();
  $('.make-input').attr('disabled', true).val('');
  $('.stop-edit-make').addClass('btn-warning').addClass('edit-make').removeClass('btn-danger').removeClass('stop-edit-make').text('Labot');
  $('.edit-make').first().addClass('btn-success').addClass('new-make').removeClass('btn-warning').removeClass('edit-make').attr('type', 'button').removeAttr('name').removeAttr('value').text('Izveidot');
  $('.delete-make').show();
  $('.new-make').attr('disabled', false).css('cursor', 'pointer');
  $('#brand_select, #tread_select, .new-brand, .edit-brand, .delete-brand').removeAttr('disabled').css('cursor', 'pointer');
  $('.make-season').hide();
});

// Inputu iestatījumi

$('.brand-settings').on('keyup', '.brand-input', function() {
  if ($(this).val().length >= 1) {
    $('.edit-brand').attr('disabled', false).css('cursor', 'pointer');
    $('.new-brand').attr('disabled', false).css('cursor', 'pointer');
  } else {
    $('.edit-brand').attr('disabled', true).css('cursor', 'default');
    $('.new-brand').attr('disabled', true).css('cursor', 'default');
  }
});

$('.make-settings').on('keyup', '.make-input', function() {
  if ($(this).val().length >= 1) {
    $('.edit-make').attr('disabled', false).css('cursor', 'pointer');
    $('.new-make').attr('disabled', false).css('cursor', 'pointer');
  } else {
    $('.edit-make').attr('disabled', true).css('cursor', 'default');
    $('.new-make').attr('disabled', true).css('cursor', 'default');
  }
});

$('.brand-settings').on('click', '.delete-brand', function (e) {
  if (!confirm('Vai tiešām dzēst?')) e.preventDefault();
});

$('.make-settings').on('click', '.delete-make', function (e) {
  if (!confirm('Vai tiešām dzēst?')) e.preventDefault();
});

if ($('.tread_comment .nav-link.active').length) {
  let $tab = $('.tread_comment .nav-link.active').attr('href').replace('#', '');
}

// Brenda apraksta iestatījumi

$('.tread_comment .nav-link').each(function() {
  $(this).on('click', function() {
    if ($(this).hasClass('active')) {
      $tab = $(this).attr('href').replace('#', '');
      if ($('.tread_comment .brand-comment-edit').length > 0) {
        $('.tread_comment .brand-comment-edit').addClass($tab + '-comment-edit').removeClass('brand-comment-edit');
        $('.tread_comment .brand-comment-edit-cancel').addClass($tab + '-comment-edit-cancel').removeClass('brand-comment-edit-cancel');
      } else if ($('.tread_comment .tread-comment-edit').length > 0) {
        $('.tread_comment .tread-comment-edit').addClass('' + $tab + '-comment-edit').removeClass('tread-comment-edit');
        $('.tread_comment .tread-comment-edit-cancel').addClass('' + $tab + '-comment-edit-cancel').removeClass('tread-comment-edit-cancel');
      }
    }
  });
});

// Modeļa apraksta iestatījumi

$('.tread_comment').on('click', '.tread-comment-edit[type=button]', function(e) {
  if ($(this).attr('type') != 'submit') e.preventDefault();
  $('.tread_comment .tread-comment-text').attr('disabled', function(index, attr) {
    return attr != 'disabled';
  });
  $('.tread_comment .tread-comment-edit-cancel').show();
  $('.tread_comment .tread-comment-edit').attr('type', 'submit').attr('name', 'tread-comment-edit').attr('value', 'true');
  $('.nav.nav-tabs .nav-item').last().children('.nav-link').addClass('disabled');
});

$('.tread_comment').on('click', '.tread-comment-edit-cancel[type=button]', function() {
  $('.tread_comment .tread-comment-text').attr('disabled', function(index, attr) {
    return attr != 'disabled';
  });
  $('.tread_comment .tread-comment-edit-cancel').hide();
  $('.tread_comment .tread-comment-edit').attr('type', 'button').removeAttr('name').removeAttr('value');
  $('.nav.nav-tabs .nav-item').last().children('.nav-link').removeClass('disabled');
});

// Brenda apraksta iestatījumi

$('.tread_comment').on('click', '.brand-comment-edit[type=button]', function(e) {
  if ($(this).attr('type') != 'submit') e.preventDefault();
  $('.tread_comment .brand-comment-text').attr('disabled', function(index, attr) {
    return attr != 'disabled';
  });
  $('.tread_comment .brand-comment-edit-cancel').show();
  $('.tread_comment .brand-comment-edit').attr('type', 'submit').attr('name', 'brand-comment-edit').attr('value', 'true');
  $('.nav.nav-tabs .nav-item').first().children('.nav-link').addClass('disabled');
});

$('.tread_comment').on('click', '.brand-comment-edit-cancel[type=button]', function() {
  $('.tread_comment .brand-comment-text').attr('disabled', function(index, attr) {
    return attr != 'disabled';
  });
  $('.tread_comment .brand-comment-edit-cancel').hide();
  $('.tread_comment .brand-comment-edit').attr('type', 'button').removeAttr('name').removeAttr('value');
  $('.nav.nav-tabs .nav-item').first().children('.nav-link').removeClass('disabled');
});

$('#orders_from, #orders_to').datepicker({ dateFormat: 'yy-mm-dd' });

$(document).ready(function() {

  $(document).on('mouseenter', '.tippy', function() {
    tippy(this, {
      touchHold: true,
      hideOnClick: false,
      placement: 'top',
      arrow: false,
      animateFill: false,
      animation: 'shift-away',
    });
  });

});