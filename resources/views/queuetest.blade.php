<head>
  <meta name="csrf-token" content="{!! csrf_token() !!}">
  <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
  <script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script>
  <script>
    $(document).ready(function() {
      $.ajaxSetup({
        headers:
          { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
      });

      $('input[type=radio]').on('change', function() {
        let office_id = $(this).val();
        $('input[type=hidden][name=office_id]').val(office_id);
        $('input[type=hidden][name=date]').val();
        $('input[type=hidden][name=slot_id]').val();
        $.ajax({
          url: '/queuetest',
          data: {office_id: office_id},
          method: 'POST',
          beforeSend: function() {
            $('.mobile_reservation_table').animate({ height: 'toggle', opacity: 'toggle' }, 'slow');
            $('input[type=radio]').attr('disabled', true).prop('disabled', true);
          },
          success: function(data) {
            $('input[type=radio]').attr('disabled', false).prop('disabled', false);
            $('.mobile_reservation_table').html(data).animate({ height: 'toggle', opacity: 'toggle' }, 'slow');
            $('.time-slot .slot').each(function() {
              $(this).on('click', function() {
                if ($(this).hasClass('available')) {
                  $('.time-slot .slot').removeClass('selected');
                  $(this).addClass('selected');
                  $('input[type=hidden][name=date]').val($(this).parent().parent().attr('data-date'));
                  $('input[type=hidden][name=slot_id]').val($(this).attr('data-slot_id'));
                }
              })
            })
          }
        });
      });
    })
  </script>

  <style>
    html {
      font-family: Sans-Serif;
      font-size: 16px;
    }

    .form-group.rims-with-mobile {
      text-align: center;
    }

    :root {
      --card-line-height: 1.2em;
      --card-padding: 1em;
      --card-radius: 0.5em;
      --color-green: #558309;
      --color-gray: #e2ebf6;
      --color-dark-gray: #c4d1e1;
      --radio-border-width: 2px;
      --radio-size: 1.5em;
    }

    .filiale_grid {
      display: grid;
      grid-gap: var(--card-padding);
      margin: 0 auto;
      max-width: 60em;
      padding: 0;
    }
    @media (min-width: 42em) {
      .filiale_grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    .filiale_card {
      background-color: #fff;
      border-radius: var(--card-radius);
      position: relative;
    }
    .filiale_card:hover {
      box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.15);
    }
    .filiale_radio {
      font-size: inherit;
      margin: 0;
      position: absolute;
      right: calc(var(--card-padding) + var(--radio-border-width));
      top: calc(var(--card-padding) + var(--radio-border-width));
    }
    @supports (-webkit-appearance: none) or (-moz-appearance: none) {
      .filiale_radio {
        -webkit-appearance: none;
        -moz-appearance: none;
        background: #fff;
        border: var(--radio-border-width) solid var(--color-gray);
        border-radius: 50%;
        cursor: pointer;
        height: var(--radio-size);
        outline: none;
        transition: background 0.2s ease-out, border-color 0.2s ease-out;
        width: var(--radio-size);
      }
      .filiale_radio::after {
        border: var(--radio-border-width) solid #fff;
        border-top: 0;
        border-left: 0;
        content: '';
        display: block;
        height: 0.75rem;
        left: 25%;
        position: absolute;
        top: 50%;
        transform: rotate(45deg) translate(-50%, -50%);
        width: 0.375rem;
      }
      .filiale_radio:checked {
        background: var(--color-green);
        border-color: var(--color-green);
      }
      .filiale_card:hover .filiale_radio {
        border-color: var(--color-dark-gray);
      }
      .filiale_card:hover .filiale_radio:checked {
        border-color: var(--color-green);
      }
    }
    .filiale_plan-details {
      text-align: start;
      border: var(--radio-border-width) solid var(--color-gray);
      border-radius: var(--card-radius);
      cursor: pointer;
      display: flex;
      flex-direction: column;
      padding: var(--card-padding);
      transition: border-color 0.2s ease-out;
    }
    .filiale_card:hover .filiale_plan-details {
      border-color: var(--color-dark-gray);
    }
    .filiale_radio:checked ~ .filiale_plan-details {
      border-color: var(--color-green);
    }
    .filiale_radio:focus ~ .filiale_plan-details {
      box-shadow: 0 0 0 2px var(--color-dark-gray);
    }
    .filiale_radio:disabled ~ .filiale_plan-details {
      color: var(--color-dark-gray);
      cursor: default;
    }
    .filiale_radio:disabled ~ .filiale_plan-details .filiale_plan-type {
      color: var(--color-dark-gray);
    }
    .filiale_card:hover .filiale_radio:disabled ~ .filiale_plan-details {
      border-color: var(--color-gray);
      box-shadow: none;
    }
    .filiale_card:hover .filiale_radio:disabled {
      border-color: var(--color-gray);
    }
    .filiale_plan-type {
      color: var(--color-green);
      font-size: 1.5rem;
      font-weight: bold;
      line-height: 1em;
    }
    .filiale_plan-cost {
      font-size: 2.5rem;
      font-weight: bold;
      padding: 0.5rem 0;
    }
    .filiale_slash {
      font-weight: normal;
    }
    .filiale_plan-cycle {
      font-size: 2rem;
      font-variant: none;
      border-bottom: none;
      cursor: inherit;
      text-decoration: none;
    }
    .filiale_hidden-visually {
      border: 0;
      clip: rect(0, 0, 0, 0);
      height: 1px;
      margin: -1px;
      overflow: hidden;
      padding: 0;
      position: absolute;
      white-space: nowrap;
      width: 1px;
    }

    div.w {
      position: relative;
      padding: 14px 20px;
      max-width: 670px;
      margin: 0 auto;
    }

    div.reservation {
      margin-top: 16px;
      margin-bottom: 16px;
    }

    div.reservation div.time-list {
      font-size: 0;
      margin: 8px -6px;
    }

    div.reservation div.time-list .time-slot {
      display: inline-block;
      width: 95px;
      height: 34px;
      font-size: 15px;
      color: #fff;
      font-weight: 600;
      background-color: #d8d8d8;
      border-radius: 4px;
      text-align: center;
      text-decoration: none!important;
      margin: 15px 6px;
      cursor: pointer;
    }

    div.reservation div.time-list .available {
      background-color: #49c200;
    }

    div.reservation div.time-list .time-slot .selected {
      background-color: #c50909;
    }

    .slot {
      border-radius: 4px;
      user-select: none;
    }

    .discount.available.slot.active {
      background: orange;
    }

    .available.slot.active {
      font-size: 17px;
    }

    .available span {
      font-size: 12px;
    }

    .unavailable {
      cursor: default;
    }

    .dot-availability {
      width: 15px;
    }

    .text-center {
      text-align: center !important;
    }

    .dot.red {
      background-color: lightgrey;
      color: lightgrey;
    }

    .dot.green {
      background-color: #4cbb6c;
      color: #4cbb6c;
    }

    .dot.orange {
      background-color: orange;
      border-color: #a16800;
    }

    .dot.transparent {
      background-color: transparent;
      border-color: transparent;
    }

    .dot {
      height: 11px;
      width: 11px;
      /* background-color: #4cbb6c; */
      border-radius: 50%;
      display: inline-block;
      /*margin-left: 7px;*/
      margin-bottom: 8px;
      /*margin-right: 6px;*/
      border: 1px solid #00000045;
    }

    .dots {
      margin-top: 4px;
      cursor: default;
      user-select: none;
    }

    .dot .sort-order {
      display: none;
      -webkit-touch-callout: none;
      -webkit-user-select: none;
      -khtml-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
    }
  </style>
</head>

<input type="hidden" name="office_id">
<input type="hidden" name="date">
<input type="hidden" name="slot_id">

<div class="w">
  <div class="form-group reservation-filiale">
    <span class="validate">*</span><label for="select">Filiāle</label>

    <div id="mobile-filiale">
      <div class="filiale_grid">
        <label class="filiale_card">
          <input name="filiale" class="filiale_radio" type="radio" id="filiale_ulbroka" value="1">

          <span class="filiale_plan-details">
            <span class="filiale_plan-cost">Ulbroka</span>
            <span>Acones iela 2a</span>
            <span>67910555</span>
          </span>
        </label>
        <label class="filiale_card">
          <input name="filiale" class="filiale_radio" type="radio" id="filiale_riga" value="2">
          <span class="filiale_hidden-visually">Pro - $50 per month, 5 team members, 500 GB per month, 5 concurrent builds</span>
          <span class="filiale_plan-details" aria-hidden="true">
            <span class="filiale_plan-cost">Rīga</span>
            <span>Kalnciema iela 39</span>
            <span>67615615</span>
          </span>
        </label>
      </div>

    </div>
  </div>
</div>

<div class="mobile_reservation_table">

</div>
