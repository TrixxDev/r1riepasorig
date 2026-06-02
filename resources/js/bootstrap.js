window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */



import Echo from 'laravel-echo';
import $ from 'jquery';

// window.Pusher = require('pusher-js');

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: process.env.MIX_PUSHER_APP_KEY,
  cluster: process.env.MIX_PUSHER_APP_CLUSTER,
  encrypted: true,
});

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});
//
// $('table .btn').on('click', function() {
//   let col = $(this).data('col');
//   let row = $(this).data('row');
//
//   $('.modal-body #col').val(col);
//   $('.modal-body #row').val(row);
// });

window.Echo.channel('slots')
  .listen('.new-slot', (data) => {
    console.log(data);
  });

// $('.modal-footer button.btn-primary').on('click', function(e) {
//   e.preventDefault();
//   let car = $('.modal-body input#car').val();
//   let model = $('.modal-body input#model').val();
//   let col = $('.modal-body input#col').val();
//   let row = $('.modal-body input#row').val();
//   $.ajax({
//     url: '/store',
//     method: 'post',
//     data: {
//       car: car,
//       model: model,
//       col: col,
//       row: row,
//     },
//     dataType: 'JSON',
//     success: function(data) {
//       $('.btn-close').click();
//     }
//   });
// });
