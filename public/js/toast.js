;(function(window, $){
  "use strict";

  let defaultConfig = {
    type: '',
    autoDismiss: false,
    container: '#toasts',
    autoDismissDelay: 4000,
    transitionDuration: 500
  };

  $.toast = function(config){

    let size = arguments.length;
    let isString = typeof(config) === 'string';

    if(isString && size === 1){
      config = {
        message: config
      };
    }

    if(isString && size === 2){
      config = {
        message: arguments[1],
        type: arguments[0]
      };
    }

    return new toast(config);
  };

  let toast = function(config){
    config = $.extend({}, defaultConfig, config);
    // show "x" or not
    let close = config.autoDismiss ? '' : '&times;';
    let title = config.title ? config.title : 'R1Riepas info';
    let type = config.type ? 'bg-' + config.type : '';

    // toast template
    const toast = $([
      '<div class="toast fade ' + type + ' show" style="cursor:pointer;" onclick="$(this).fadeOut(function() { $(this).remove(); })">',
      '<div class="toast-header">',
      '<strong class="mr-auto">' + title + '</strong>',
      '<button type="button" class="ml-2 mb-1 close">',
      '<span>' + close + '</span>',
      '</button>',
      '</div>',
      '<div class="toast-body">',
      config.message,
      '</div>',
      '</div>'
    ].join(''));

    // handle dismiss
    toast.find('.close').on('click', function(){
      let toast = $(this).parent().parent();

      toast.fadeOut(function() {
        $(this).remove();
      })
    });

    // append toast to toasts container
    $(config.container).append(toast.hide().fadeIn());

    // // transition in
    // setTimeout(function(){
    //     toast.addClass('show');
    // }, config.transitionDuration);

    // if auto-dismiss, start counting
    if(config.autoDismiss){
      setTimeout(function(){
        toast.find('.close').click();
      }, config.autoDismissDelay);
    }

    return this;
  };

})(window, jQuery);