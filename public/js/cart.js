$(document).ready(function() {

  $.ajaxSetup({async: false});

  let $el = $('#search_filters .wrap:nth-child(-n+2) h4 span');

  $($el).each(function() {
    $(this).on('click', function() {
      $($el).removeClass('active');
      $(this).addClass('active');
      $('.can-collapse .sidebar-top, .can-collapse .sidebar-auto').hide();
      switch ($(this).attr('id')) {
        case 'search_filters_auto': {
          $('.can-collapse .sidebar-auto').show();
          break;
        }
        case 'search_filters_params': {
          $('.can-collapse .sidebar-top').show();
          break;
        }
      }
    });
  })
});

function encode(str) {
  var encoded = "";
  str = btoa(str);
  str = btoa(str);
  for (i=0; i<str.length;i++) {
    var a = str.charCodeAt(i);
    var b = a ^ 10; // bitwise XOR with any number, e.g. 123
    encoded = encoded+String.fromCharCode(b);
  }
  encoded = btoa(encoded);
  return encoded;
}

let modelId;
let makeId;
let dia;
let modelOpt = '';
let radiusOpt = '';

// if ($('body').hasClass('category-jauni-lietie-diski')) {
//
//   if ($('.sidebar-auto input[name=brand]').val() !== 'Visi') {
//     $('section.facet--auto-model .dropdown-menu').html('<a rel="nofollow" class="select-list" data-id="Visi" id="Visi">Visi</a>');
//     $('section.facet--auto-dia .dropdown-menu').html('<a rel="nofollow" class="select-list" data-id="Visi" id="Visi">Visi</a>');
//     modelId = $('.sidebar-auto input[name=brand]').parent().children('.dropdown-menu').find('.select-list#' + $('.sidebar-auto input[name=brand]').val());
//     $.ajax({
//       url: '/api/wheels/' + modelId,
//       method: 'get',
//       dataType: 'json',
//       async: false,
//       success: function(data) {
//         data.forEach(function(item) {
//           modelOpt += '<a rel="nofollow" class="select-list" data-id="' + item['title'] + '">' + item['title'] + '</a>';
//         });
//       }
//     });
//     $(modelOpt).insertAfter($('section.facet--auto-model .dropdown-menu .select-list').first());
//     $('section.facet--auto-model .dropdown-menu .select-list').first().remove();
//   }
//
//
//   if ($('.sidebar-auto input[name=model]').val() !== 'Visi') {
//     $('section.facet--auto-dia .dropdown-menu').html('<a rel="nofollow" class="select-list" data-id="Visi" id="Visi">Visi</a>');
//     makeId = $('.sidebar-auto input[name=model]').val();
//     $.ajax({
//       url: '/api/wheels/' + modelId + '/' + encode(makeId),
//       method: 'get',
//       dataType: 'json',
//       async: false,
//       success: function(data) {
//         data.forEach(function(item) {
//           radiusOpt += '<a rel="nofollow" class="select-list">' + item + '</a>';
//         });
//       }
//     });
//     $(radiusOpt).insertAfter($('section.facet--auto-dia .dropdown-menu .select-list').first());
//     $('section.facet--auto-dia .dropdown-menu .select-list').first().remove();
//   }
// }


$('.sidebar-auto input[name=brand]').parent().children('.dropdown-menu').children('.select-list').each(function() {
  $(this).on('click', function() {
    modelOpt = '';
    radiusOpt = '';
    $('section.facet--auto-model .dropdown-menu').html('<a rel="nofollow" class="select-list" data-id="Visi" id="Visi">Visi</a>');
    $('section.facet--auto-dia .dropdown-menu').html('<a rel="nofollow" class="select-list" data-id="Visi" id="Visi">Visi</a>');
    $('.sidebar-auto input[name=model]').parent().children('i').remove();
    modelId = $(this).data('id');
    switch (modelId) {
      case 'Visi': {
        $('.sidebar-auto input[name=diameter]').parent().children('i').remove();
        $('section.facet--auto-model ul#facet_auto-model').addClass('select-make');
        $('.sidebar-auto input[name=model]').attr('value', 'Visi').val('Visi');
        break;
      }
      default: {
        $('section.facet--auto-model ul#facet_auto-model').removeClass('select-make');
        $('<i class="material-icons float-xs-right"></i>').insertAfter($('.sidebar-auto input[name=model]'));
        if ($('.sidebar-auto input[name=brand]').val() == $(this).attr('id')) {
          return true;
        }
        $('.sidebar-auto input[name=brand]').attr('value', $(this).text()).val($(this).text()).trigger('change');
        $('.sidebar-auto input[name=model]').attr('value', 'Visi').val('Visi');
        $('section.facet--auto-dia ul#facet_auto-dia').addClass('select-make');
        $('.sidebar-auto input[name=diameter]').parent().children('i').remove();
        $('.sidebar-auto input[name=diameter]').val('Visi');
        $.get('/api/wheels/' + modelId, function() {

        }, 'JSON').complete(function(data) {
          data.forEach(function(item) {
            modelOpt += '<a rel="nofollow" class="select-list" data-id="' + item['title'] + '">' + item['title'] + '</a>';
          });
        });
      }
    }
    $(modelOpt).insertAfter($('section.facet--auto-model .dropdown-menu .select-list').first());
    $('section.facet--auto-model .dropdown-menu .select-list').first().remove();

  })
});

$('.sidebar-auto input[name=model]').parent().children('.dropdown-menu').each(function() {
  $(this).on('click', '.select-list', function() {
    radiusOpt = '';
    $('.sidebar-auto input[name=diameter]').parent().children('i').remove();
    makeId = $(this).data('id');
    switch (makeId) {
      case 'Visi': {
        $('section.facet--auto-dia ul#facet_auto-dia').addClass('select-make');
        $('.sidebar-auto input[name=diameter]').attr('value', 'Visi').val('Visi');
        break;
      }
      default: {
        $('<i class="material-icons float-xs-right"></i>').insertAfter($('.sidebar-auto input[name=diameter]'));
        $('section.facet--auto-dia ul#facet_auto-dia').removeClass('select-make');
        if ($('.sidebar-auto input[name=model]').val() == $(this).text()) {
          return true;
        }
        $('.sidebar-auto input[name=model]').attr('value', $(this).text()).val($(this).text()).trigger('change');
        $('.sidebar-auto input[name=diameter]').attr('value', 'Visi').val('Visi');
        $('section.facet--auto-dia .dropdown-menu').html('<a rel="nofollow" class="select-list" data-id="Visi" id="Visi">Visi</a>');
        makeId = $('.sidebar-auto input[name=model]').val();
        $.get('/api/wheels/' + modelId + '/' + encode(makeId), function(data) {
          data.forEach(function(item) {
            radiusOpt += '<a rel="nofollow" class="select-list">' + item + '</a>';
          });
        }, 'JSON');
      }
    }
    $(radiusOpt).insertAfter($('section.facet--auto-dia .dropdown-menu .select-list').first());
    $('section.facet--auto-dia .dropdown-menu .select-list').first().remove();
  })
});

$('.sidebar-auto input[name=tire-radius]').parent().children('.dropdown-menu').each(function() {
  $(this).on('click', '.select-list', function() {
    $('.sidebar-auto input[name=tire-radius]').attr('value', $(this).text()).val($(this).text()).trigger('change');
  });
});

// $('.sidebar-auto input').each(function() {
//   $(this).on('change', function() {
//     let empty = $('.sidebar-auto input').filter(function() {
//       return $(this).val().trim() === 'Visi';
//     }).length;
//
//     (empty === 0) && $('.sidebar-auto form').submit();
//   });
// });
//
// $('.sidebar-top input').not(':first').each(function() {
//   $(this).on('change', function() {
//     let empty = $('.sidebar-top input').not(':first').filter(function() {
//       return $(this).val().trim() === 'Visi';
//     }).length;
//
//     (empty === 0) && $('.sidebar-top').parent('form').submit();
//   });
// });

$(document).on("click", function(event){
  if(!$(event.target).closest(".size-dropdown").length){
    $('.size-dropdown').removeClass('open');
  }
});
