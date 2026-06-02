// $(document).ready(function() {
//
//   const pusher = new Pusher('04c358afec27f4ba222f', {
//     cluster: 'eu',
//     encrypted: true,
//     authEndpoint: '/broadcasting/auth', // just a helper method to create a link
//     auth: {
//       headers: {
//         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//       }
//     }
//   });
//
//   // if (loggedIn) {
//   pusher.subscribe('private-stocks').bind('update-stock', (data) => {
//     console.log(data);
//   });
//   // }
//
//   pusher.subscribe('queue').bind('changeQueueHalf', (data) => {
//     console.log(data);
//     if (data.workingDayVisible == 1) {
//       if (data.show == 1) {
//         $('.slot[data-date=' + data.date + '][data-queue=' + data.queue_id + ']').each(function() {
//           if ($(this).children().data('iorder') % 2 != 0) {
//             if ($(this).hasClass('available-slot') && $(this).children().data('col') == data.queue_id) {
//               $(this).children().fadeOut(1000, function() {
//                 $(this).remove();
//               });
//             }
//           }
//         });
//       } else {
//         console.log(321);
//         $('.slot[data-date=' + data-date + '][data-queue=' + data.queue_id + ']').each(function() {
//           console.log($(this));
//         })
//       }
//     }
//   }).bind('toggleQueue', (data) => {
//     if (data.show == 0) {
//       $('.slot[data-date=' + data-date + '][data-queue_id=' + data.queue_id + ']').each(function() {
//         console.log($(this));
//       })
//     } else {
//       console.log(data);
//     }
//   });
//
//   pusher.subscribe('slots').bind('new-slot', (data) => {
//     let plate;
//     if (data.slot.ownerPhone) {
//       plate = data.slot.ownerPhone.substr(-3);
//       plate = parseInt(plate);
//       plate = $.trim(plate);
//     } else {
//       plate = '';
//     }
//
//     let slot = 'slot' + data.slot_iorder + '-' + data.slot_queue_id;
//
//     let date = data.date;
//
//     let successText = truncateCharacters($.trim(data.slot.vehicleMake),8,'&mldr;',1) + ' xxxxx' + plate;
//
//     $('<td class="taken-slot slot' + data.slot_iorder + '-' + data.slot_queue_id + ' slot" data-date="' + date + '">' + successText + '</td>').hide().fadeIn().insertAfter($('#' + slot + '[data-date="' + date + '"]').parent());
//     $('#' + slot + '[data-date="' + date + '"]').parent().fadeOut().remove();
//   }).bind('delete-slot', (data) => {
//     $('td.slot' + data.slot_iorder + '-' + data.slot_queue_id + '[data-date="' + data.date + '"]').fadeOut().addClass('available-slot').html('<button class="free-slot-link" id="slot' + data.slot_iorder + '-' + data.slot_queue_id + '" data-col="' + data.slot_queue_id + '" data-iorder="' + data.slot_iorder + '" data-date="' + data.date + '" data-toggle="modal" data-target="#reservation">Brīvs</button>').removeClass('taken-slot').removeClass('slot-offer').fadeIn();
//   }).bind('edit-slot', (data) => {
//     if (data.status == 0) {
//       $('td.slot' + data.slot_iorder + '-' + data.slot_queue_id + '[data-date="' + data.date + '"]').fadeOut().addClass('available-slot').html('<button class="free-slot-link" id="slot' + data.slot_iorder + '-' + data.slot_queue_id + '" data-col="' + data.slot_queue_id + '" data-iorder="' + data.slot_iorder + '" data-date="' + data.date + '" data-toggle="modal" data-target="#reservation">Brīvs</button>').removeClass('taken-slot').removeClass('slot-offer').fadeIn();
//     } else if (data.status == 1) {
//       if (data.slot.vehicleMake && data.slot.vehicleModel) {
//         let plate;
//         if (data.slot.ownerPhone) {
//           plate = data.slot.ownerPhone.substr(-3);
//           plate = parseInt(plate);
//           plate = $.trim(plate);
//         } else {
//           plate = '';
//         }
//
//         let slot = 'slot' + data.slot_iorder + '-' + data.slot_queue_id;
//
//         let date = data.date;
//
//         let successText = truncateCharacters($.trim(data.slot.vehicleMake),8,'&mldr;',1) + ' xxxxx' + plate;
//
//         if ($('.available-slot.slot' + data.slot_iorder + '-' + data.slot_queue_id + '[data-date=' + data.date + ']').is(':visible')) {
//           $('<td class="taken-slot slot' + data.slot_iorder + '-' + data.slot_queue_id + ' slot" data-date="' + date + '">' + successText + '</td>').hide().fadeIn().insertAfter($('#' + slot + '[data-date="' + date + '"]').parent());
//           $('#' + slot + '[data-date="' + date + '"]').parent().fadeOut().remove();
//         } else {
//           $('.slot' + data.slot_iorder + '-' + data.slot_queue_id + '[data-date="' + date + '"]').fadeOut().html(successText).fadeIn();
//         }
//
//       } else {
//         let slot = 'slot' + data.slot_iorder + '-' + data.slot_queue_id;
//
//         let date = data.date;
//
//         let successText = 'xxxxx';
//
//         if ($('.available-slot.slot' + data.slot_iorder + '-' + data.slot_queue_id + '[data-date=' + data.date + ']').is(':visible')) {
//           $('<td class="taken-slot slot' + data.slot_iorder + '-' + data.slot_queue_id + ' slot" data-date="' + date + '">' + successText + '</td>').hide().fadeIn().insertAfter($('#' + slot + '[data-date="' + date + '"]').parent());
//           $('#' + slot + '[data-date="' + date + '"]').parent().fadeOut().remove();
//         } else {
//           $('.slot' + data.slot_iorder + '-' + data.slot_queue_id + '[data-date="' + date + '"]').fadeOut().html(successText).fadeIn();
//         }
//       }
//     }
//   }).bind('move-slot', (data) => {
//     var today = new Date();
//     var dd = String(today.getDate()).padStart(2, '0');
//     var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
//     var yyyy = today.getFullYear();
//
//     today = yyyy + '-' + mm + '-' + dd;
//
//     let slot_html = $('.slot' + data.targetSlot.iorder + '-' + data.targetSlot.queue_id + '[data-date="' + data.targetSlot.date + '"]');
//     let html = $(slot_html[0].outerHTML).addClass('slot' + data.slot.iorder + '-' + data.slot.queue_id).removeClass('slot' + data.targetSlot.iorder + '-' + data.targetSlot.queue_id).data('date', data.slot.date);
//
//     $(html).insertBefore($('.slot' + data.slot.iorder + '-' + data.slot.queue_id + '[data-date="' + data.slot.date + '"]')).fadeOut().fadeIn();
//     $(html).next().fadeOut().remove();
//     if (data.targetSlot.date == today) {
//       $('<td class="slot-gray-free slot' + data.targetSlot.iorder + '-' + data.targetSlot.queue_id + ' slot" data-date="' + data.targetSlot.date + '">Brīvs</td>').insertBefore(slot_html).fadeOut().fadeIn();
//     } else {
//       $('<td class="available-slot slot' + data.targetSlot.iorder + '-' + data.targetSlot.queue_id + ' slot" data-date="' + data.targetSlot.date + '"><button class="free-slot-link" id="slot' + data.targetSlot.iorder + '-' + data.targetSlot.queue_id + '" data-col="' + data.targetSlot.queue_id + '" data-iorder="' + data.targetSlot.iorder + '" data-date="' + data.targetSlot.date + '" data-toggle="modal" data-target="#reservation">Brīvs</button></td>').insertBefore(slot_html).fadeOut().fadeIn();
//     }
//     $(slot_html).fadeOut().remove();
//
//
//   });
//
//   $.ajaxSetup({
//     headers: {
//       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//     }
//   });
//
//
// })
