$(document).ready(function() {

    let mobile_nav_enabled = 0;
    let atributs;
    let menu_opened = 0;

    let __filterWidth = $('.select-title.tire-width').attr('autocomplete', true).prop('autocomplete', true).val();
    let __filterHeight = $('.select-title.tire-height').attr('autocomplete', false).prop('autocomplete', false).val();
    let __filterDiameter = $('.select-title.tire-radius').attr('autocomplete', false).prop('autocomplete', false).val();

    $('.login-form .toggle, .register-form .toggle').on('click', function() {
       if ($(this).data('text-show') == 'Rādīt') {
           $(this).data('text-show', 'Hide');
           $(this).parent().parent().children('input').attr('type', 'text');
           $(this).html('Slēpt');
       } else {
           $(this).data('text-show', 'Rādīt');
           $(this).parent().parent().children('input').attr('type', 'password');
           $(this).html('Rādīt');
       }
    });

    // Desktop nav bar

    // $('.category').on('mouseover', function() {
    //     if (!$(this).children().last().css('display') == 'block') {
    //         $('.category .popover').css('display', 'none');
    //         $(this).children().last().css('display', 'block');
    //     } else {
    //         $(this).children().last().css('display', 'none');
    //     }
    // })

    // Mobile nav bar

    $('#menu-icon').on('click', function () {
        if ($('#mobile_top_menu_wrapper').css('display') == 'none') {
            $('#mobile_top_menu_wrapper').css('display', 'block');
            $('section#wrapper').css('display', 'none');
            mobile_nav_enabled = 1;
        } else {
            $('#mobile_top_menu_wrapper').css('display', 'none');
            $('section#wrapper').css('display', 'block');
            $('#mobile_top_menu_wrapper .category .popover').removeClass('in');
            mobile_nav_enabled = 0;
        }
    });

    $('#mobile_top_menu_wrapper .category .dropdown-item').on('click', function () {
        if ($(this).siblings('.popover').css('display') === 'block') {
            $(this).siblings('.popover').css('display', 'none');
        } else {
            $('#mobile_top_menu_wrapper .category .popover').css('display', 'none');
            $(this).siblings('.popover').css('display', 'block');
        }
    });

    $('#_desktop_top_menu .category').on('mouseover', function() {
        $('#_desktop_top_menu .category .popover').css('display', 'none');
        $(this).children().last().css('display', 'block');
        $(this).children().last().on('mouseover', function() {
            $(this).css('display', 'block');
        }).on('mouseout', function() {
            $('#_desktop_top_menu .category .popover').css('display', 'none');
        });
    }).on('mouseout', function() {
        $('#_desktop_top_menu .category .popover').css('display', 'none');
    });

    $('.facet-dropdown').on('click', function() {
        const $size = $(this).children().first().val();
        $(this).children().first().focus().val('').val($size);
        if (!$(this).hasClass('open')) {
            $('.facet-dropdown').removeClass('open');
            $(this).addClass('open');
            //$(this).children().first().removeAttr('readonly');
            $('.dropdown-menu.width').scrollTo($('.dropdown-menu.width .select-list#' + __filterWidth));
            $('.dropdown-menu.height').scrollTo($('.dropdown-menu.height .select-list#' + __filterHeight));
            $('.dropdown-menu.radius').scrollTo($('.dropdown-menu.radius .select-list#' + __filterDiameter));
            menu_opened = 1;
        } else {
            $(this).removeClass('open');
            $(this).children().first().attr('readonly', true).prop('readonly', true);
            menu_opened = 0;
        }
    });



    $('.facet-dropdown .dropdown-menu a').bind('click', function() {
      atributs = $(this).attr('id');
      if (atributs == '') { atributs = 'Visi' };
      $(this).parent().parent().children('.select-title').val(atributs);
      $(this).parent().parent().children('.select-title').prop('value', atributs);
      $(this).parent().parent().children('.select-title').attr('value', atributs);
      $(this).parent().parent().children('.select-title').trigger('change');
    });



    //$('.select-title.tire-width, .select-title.tire-height, .select-title.tire-radius').on('keyup', function(e) {
    //    if (e.which == 32) {
    //        let $number = $(this).val();
    //        $number = $number.replaceAll(" ", "");
    //        $(this).val($number);
    //    } else if (e.ctrlKey && e.shiftKey && e.which == 82) {
    //        location.reload();
    //    }
    //    if ($(this).val().length == $(this).attr('maxlength')) {
    //        $('')
    //    }
    //});


    if (menu_opened === 1) {
        $(document).on('click', function(event){
        var container = $("#_desktop_top_menu .facet-dropdown .dropdown-menu");
            if (!container.is(event.target) &&            // If the target of the click isn't the container...
                container.has(event.target).length === 0) // ... nor a descendant of the container
                {
                    container.parent().removeClass('open');
                }
        });
    }
});
