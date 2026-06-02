<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="csrf-token" content="{!! csrf_token() !!}">
  <title>Ātrais pasūtījums</title>
  <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
  <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="{{ asset('css/jquery-ui.min.css?rev=' . time()) }}" type="text/css" media="all">
  <link rel="stylesheet" href="{{ asset('css/jquery.ui.theme.min.css?rev=' . time()) }}" type="text/css" media="all">
  <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('css/sweetalert2.min.css') }}">

</head>
<body class="quick-order-page">

<div class="popup quick-order-shell" id="quick-popup" data-popup="popup-3" style="display: block;">
  <div class="popup-inner quick-order-panel">
    <div class="busy_bgr"><div class="busy_img"></div></div>
    <form id="quick-buy-form">
      @csrf
      <input type="hidden" name="article" value="">
      <section class="quick-order-section quick-order-toolbar" aria-label="Noliktava">
        <div class="section-title">
          <span>Noliktava</span>
        </div>
      <div class="location-wraper">
        <div class="radio-field">
          <input id="loc_URS" type="radio" name="location" value="URS" checked="">
          <label for="loc_URS">
            URS
{{--            <span id="urs_quantity">--}}
{{--              <div class="spinner-border text-dark" role="status">--}}
{{--                <span class="sr-only">Loading...</span>--}}
{{--              </div>--}}
{{--            </span>--}}
          </label>
        </div>
        <div class="radio-field">
          <input id="loc_KRS" type="radio" name="location" value="KRS">
          <label for="loc_KRS">
            KRS
{{--            <span id="krs_quantity">--}}
{{--              <div class="spinner-border text-dark" role="status">--}}
{{--                <span class="sr-only">Loading...</span>--}}
{{--              </div>--}}
{{--            </span>--}}
          </label>
        </div>
        @if (isset($links) && count($links) > 0)
          @foreach ($links as $name => $opts)
          <div class="radio-field">
            <a href="{{ $opts['link'] }}" target="_blank">{{ $name }} ({{ $opts['remaining'] }})</a>
          </div>
          @endforeach
        @endif
      </div>
      </section>

      <section class="quick-order-section items-section">
        <div class="section-title">
          <span>Preces</span>
          <small>Daudzums, cena un atlikumi</small>
        </div>
        <div class="items-head" aria-hidden="true">
          <span class="head-stock">Atlikumi</span>
          <span class="head-product">Prece</span>
          <span class="head-qty">Sk.</span>
          <span class="head-price">Cena</span>
        </div>
      <div class="inserthere order-items-list">

      </div>
      </section>
      <section class="quick-order-section services-section">
        <div class="section-title">
          <span>Pakalpojumi</span>
        </div>
        <div class="services-grid">
          <div class="bottom-long-fields service-row service-row-montage">
            <div class="switch-field">
              <input type="checkbox" id="montage" onchange="toggleMontage()" name="montage" value="1"><label for="montage"></label>
              <span>Montāža</span>
            </div>
            <input type="text" placeholder="Cena" name="price_montage" onkeyup="addMontagePrice()" disabled="">
          </div>
          <div class="bottom-long-fields service-row service-row-safe">
            <div class="switch-field">
              <input type="checkbox" id="safe" onchange="toggleSafe()" name="safe" value="1"><label for="safe"></label>
              <span>Glabāšana</span>
            </div>
            <input type="text" placeholder="Cena" name="price_safe" onkeyup="addSafePrice()" disabled="">
          </div>
          <div class="bottom-long-fields service-row service-row-phone">
            <div class="switch-field">
              <input type="checkbox" id="mobile" onchange="toggleMobile()" name="mobile" checked="" value="1"><label for="mobile"></label>
              <span>Tel. Numurs</span>
            </div>
            <input type="text" placeholder="Telefona numurs" name="mobile_number">
          </div>
          <div class="bottom-long-fields service-row total-row">
            <label for="order-total">Summa</label>
            <input id="order-total" type="text" name="total" placeholder="Summa" value="" readonly="">
          </div>
        </div>
      </section>

      <section class="quick-order-section details-section">
        <div class="section-title">
          <span>Detaļas</span>
        </div>
        <div class="user-fields">
          <input type="text" name="user" placeholder="Lietotājs" value="">
          <textarea type="textarea" name="comments" placeholder="Komentāri"></textarea>
        </div>
        <div class="partner-fields" id="partner-fields" style="display:none;">
          <div class="partner-fields-title">Klients</div>
          <div class="partner-search-wrap">
            <input type="text" id="partner-search" placeholder="Meklēt klientu (nosaukums, reģ. nr., adrese)..." autocomplete="off">
            <div id="partner-search-results" class="partner-search-results"></div>
          </div>
          <div class="partner-fields-grid">
            <button type="button" id="partner-create-btn" class="partner-create-btn">+ Jauns klients</button>
            <input type="hidden" name="partner_id" id="partner-id" value="">
            <input type="text" name="partner_name" id="partner-name" placeholder="Klienta nosaukums / vārds">
            <input type="text" name="partner_regnr" id="partner-regnr" placeholder="Reģ. Nr. (neobligāti)">
            <input type="text" name="partner_address" id="partner-address" placeholder="Adrese (neobligāti)">
          </div>
        </div>
      </section>
      <div class="submit-actions">
        <a class="button button-order" onclick="return sendData(getFormData($('#quick-buy-form')), 'order');">Pieņemtais pasūtījums</a>
        <a class="button button-prepayment" onclick="return sendData(getFormData($('#quick-buy-form')), 'prepayment');">Rēķins priekšapmaksai</a>
      </div>
    </form>

    <style>

      .spinner-border {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        vertical-align: -4px;
        border: 0.25em solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        -webkit-animation: .75s linear infinite spinner-border;
        animation: .75s linear infinite spinner-border;
      }

      /* POPUPS */

      .popup {
	      z-index: 0!important;
      }

      .popup .location-wraper {
        float: left;
        margin: 0 0 15px -13px;
        width: 120px;
        height: 120px;
      }

      .popup .location-wraper input {
        height: 15px;
        display: inline-block;
        margin-right: 7px;
      }

      .popup .top-long-fields {
        height: 51px !important;
        width: 100% !important;
        display: table-header-group;
      }

      .popup .top-long-fields input {
        float: left;
        margin-left: 10px;
      }

      .popup .top-long-fields input[name="prod"] {
        width: 305px;
        margin-left: 10px;
      }

      span.delete_item {
        float: left;
      }

      span.delete_item img {
        width: 10px;
      }

      .popup .top-long-fields input[name="qty"] {
        width: 37px !important;
        padding: 0;
        padding-left: 5px;
      }

      .popup .top-long-fields > label[for='price'] {
        right: 60px;
      }

      .popup .top-long-fields input[name="price"] {
        width: 85px !important;
      }

      .popup .bottom-long-fields {
        height: 50px;
        width: 547px;
        margin-left: 26px;
      }

      input#montage {
        margin-left: 24px;
      }

      .popup .bottom-long-fields span {
        float: left;
        /* margin: 0 0px; */
        font-size: 20px;
        font-weight: bold;
      }

      .popup .bottom-long-fields input {
        float: left;
        margin-left: 10px;
      }

      .popup .bottom-long-fields input[name="total"] {
        float: right;
        margin-left: 10px;
      }

      .popup .bottom-long-fields input[name="price_montage"] {
        width: 100px;
      }

      .popup .bottom-long-fields input[name="total"] {
        width: 85px;
      }

      .popup .top-long-fields > label {
        font-size: 9pt;
        font-weight: bold;
        position: absolute;
        top: 5px;
      }

      .popup .top-long-fields > label[for='qty'] {
        right: 148px;
      }

      .popup .top-long-fields > label[for='price'] {
        right: 60px;
      }

      .popup label[for='total'] {
        position: relative;
        left: 75px;
        top: 5px;
      }

      #quick-buy-msg {
        display: none;
        font-size: 30px;
        margin-top: 30px;
      }

      .popup .radio-field {
        font-size: .875rem;
      }

      .popup .user-fields {
        margin-top: 15px;
      }

      .popup .partner-fields {
        margin: 10px 26px 0;
      }

      .popup .partner-fields-title {
        font-weight: 600;
        margin-bottom: 6px;
      }

      .popup .partner-search-wrap {
        position: relative;
        margin-bottom: 6px;
      }

      .popup .partner-search-wrap input {
        width: 100%;
      }

      .popup .partner-search-results {
        display: none;
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 2px);
        max-height: 220px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #ccc;
        z-index: 50;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
      }

      .popup .partner-search-results.is-open {
        display: block;
      }

      .popup .partner-search-item,
      .popup .partner-search-empty {
        display: block;
        width: 100%;
        text-align: left;
        padding: 8px 10px;
        border: 0;
        background: #fff;
        cursor: pointer;
      }

      .popup .partner-search-item:hover,
      .popup .partner-search-empty:hover {
        background: #f3f6ff;
      }

      .popup .partner-search-item small {
        display: block;
        color: #666;
        margin-top: 2px;
      }

      .popup .partner-create-btn {
        display: inline-block;
        margin-bottom: 8px;
        padding: 4px 10px;
        border: 1px solid #1f6f3f;
        background: #fff;
        color: #1f6f3f;
        cursor: pointer;
      }

      .popup .partner-fields input {
        display: block;
        width: 100%;
        margin-bottom: 6px;
      }

      .popup .submit-actions {
        margin: 15px 0 0 26px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
      }

      .popup .submit-actions .button {
        margin-left: 0;
      }

      .popup .button-prepayment {
        background: #1f6f3f;
      }

      :root {
        --quick-bg: #f4f6f8;
        --quick-panel: #ffffff;
        --quick-section: #ffffff;
        --quick-border: #d9e0e7;
        --quick-border-strong: #b8c4cf;
        --quick-text: #17212b;
        --quick-muted: #617080;
        --quick-green: #1f6f3f;
        --quick-green-dark: #15552f;
        --quick-blue: #2f5f9f;
        --quick-red: #b42318;
        --quick-soft: #eef4f0;
      }

      * {
        box-sizing: border-box;
      }

      body.quick-order-page {
        min-height: 100vh;
        min-width: 1000px;
        margin: 0;
        background: var(--quick-bg);
        color: var(--quick-text);
        font-family: Arial, Helvetica, sans-serif;
        font-size: 14px;
      }

      .quick-order-shell {
        position: static !important;
        width: 100%;
        min-width: 1000px;
        min-height: 100vh;
        padding: 10px 16px;
        background: var(--quick-bg);
        z-index: auto !important;
      }

      .quick-order-panel {
        position: relative;
        width: 1100px !important;
        min-width: 1100px !important;
        max-width: none !important;
        top: 0 !important;
        left: 0 !important;
        transform: none !important;
        -webkit-transform: none !important;
        margin: 0 auto;
        padding: 0;
        background: transparent;
        border: 0;
        box-shadow: none;
      }

      #quick-buy-form {
        display: grid;
        gap: 8px;
      }

      .quick-order-section,
      .submit-actions {
        background: var(--quick-panel);
        border: 1px solid var(--quick-border);
        border-radius: 8px;
      }

      .quick-order-section {
        padding: 10px 12px;
      }

      .section-title {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
      }

      .section-title span {
        color: var(--quick-text);
        font-size: 14px;
        font-weight: 700;
      }

      .section-title small {
        color: var(--quick-muted);
        font-size: 12px;
      }

      .popup .location-wraper {
        float: none;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        width: auto;
        height: auto;
        margin: 0;
      }

      .popup .radio-field {
        display: flex;
        align-items: center;
        gap: 6px;
        min-height: 30px;
        padding: 5px 9px;
        border: 1px solid var(--quick-border);
        border-radius: 8px;
        background: #fff;
      }

      .popup .radio-field input {
        width: 16px;
        height: 16px;
        margin: 0;
      }

      .popup .radio-field label,
      .popup .radio-field a {
        margin: 0;
        color: var(--quick-text);
        font-weight: 700;
        line-height: 1;
      }

      .popup .radio-field a {
        color: var(--quick-blue);
        text-decoration: none;
      }

      .items-head,
      .popup .top-long-fields {
        display: grid;
        grid-template-columns: 32px minmax(190px, 230px) minmax(260px, 1fr) 78px 92px;
        gap: 6px;
        align-items: center;
      }

      .items-head {
        padding: 0 6px 5px;
        color: var(--quick-muted);
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
      }

      .head-stock {
        grid-column: 2;
      }

      .head-product {
        grid-column: 3;
      }

      .head-qty {
        grid-column: 4;
      }

      .head-price {
        grid-column: 5;
      }

      .order-items-list {
        display: grid;
        gap: 6px;
      }

      .popup .top-long-fields {
        width: 100% !important;
        min-height: 40px !important;
        height: auto !important;
        padding: 6px;
        border: 1px solid var(--quick-border);
        border-radius: 8px;
        background: #fbfcfd;
      }

      .popup .top-long-fields input {
        float: none;
        width: 100% !important;
        height: 30px;
        margin: 0;
        padding: 4px 7px !important;
        border: 1px solid var(--quick-border-strong);
        border-radius: 6px;
        background: #fff;
        color: var(--quick-text);
        font-size: 12px;
      }

      .popup .top-long-fields input[readonly] {
        background: #f8fafb;
      }

      .popup .top-long-fields input[name="prod"] {
        min-width: 0;
      }

      .popup .top-long-fields input[name="qty"],
      .popup .top-long-fields input[name="price"] {
        width: 100% !important;
        text-align: right;
      }

      .popup .top-long-fields > label {
        position: static;
        display: none;
      }

      span.delete_item,
      .order-item-remove {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        float: none;
        width: 28px;
        height: 28px;
        border: 1px solid #f0c7c2;
        border-radius: 6px;
        background: #fff;
        color: var(--quick-red);
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
      }

      span.delete_item img {
        display: none;
      }

      .stock-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(24px, 1fr));
        gap: 3px;
      }

      .popup .stock-grid input {
        min-width: 0;
        height: 28px;
        padding: 0 !important;
        text-align: center;
        font-size: 11px;
        font-weight: 700;
      }

      .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 8px;
      }

      .popup .bottom-long-fields {
        display: grid;
        align-items: center;
        width: auto;
        height: auto;
        min-height: 48px;
        margin: 0;
        padding: 8px;
        border: 1px solid var(--quick-border);
        border-radius: 8px;
        background: #fbfcfd;
      }

      .service-row {
        grid-template-columns: 1fr;
        gap: 6px;
      }

      .total-row {
        grid-template-columns: minmax(0, 1fr) 110px;
        background: var(--quick-soft) !important;
      }

      .switch-field {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
      }

      .switch-field span {
        overflow-wrap: anywhere;
      }

      .popup .bottom-long-fields span,
      .total-row label {
        float: none;
        margin: 0 !important;
        color: var(--quick-text);
        font-size: 13px;
        font-weight: 700;
      }

      .popup .bottom-long-fields input {
        float: none;
        width: 100% !important;
        height: 30px;
        margin: 0 !important;
        padding: 4px 7px;
        border: 1px solid var(--quick-border-strong);
        border-radius: 6px;
        background: #fff;
      }

      .popup .service-row > input:not([type="checkbox"]) {
        min-width: 0;
      }

      .popup .total-row input[name="total"] {
        float: none !important;
        width: 110px !important;
        max-width: 100%;
        text-align: right;
        font-weight: 700;
      }

      .popup .bottom-long-fields input[type="checkbox"] {
        width: 16px !important;
        height: 16px;
        padding: 0;
      }

      .popup .bottom-long-fields input:disabled {
        background: #edf1f4;
        color: var(--quick-muted);
      }

      .popup label[for='total'] {
        position: static;
      }

      .popup .user-fields {
        display: grid;
        grid-template-columns: minmax(160px, 240px) minmax(260px, 1fr);
        gap: 8px;
        margin: 0;
      }

      .popup .user-fields input,
      .popup .user-fields textarea,
      .popup .partner-fields input,
      .popup .partner-search-wrap input {
        width: 100%;
        min-height: 32px;
        margin: 0;
        padding: 6px 8px;
        border: 1px solid var(--quick-border-strong);
        border-radius: 6px;
        background: #fff;
        color: var(--quick-text);
      }

      .popup .user-fields textarea {
        min-height: 58px;
        resize: vertical;
      }

      .popup .partner-fields {
        margin: 10px 0 0;
        padding-top: 10px;
        border-top: 1px solid var(--quick-border);
      }

      .popup .partner-fields-title {
        margin: 0 0 6px;
        color: var(--quick-text);
        font-size: 13px;
        font-weight: 700;
      }

      .popup .partner-search-wrap {
        position: relative;
        margin-bottom: 8px;
      }

      .popup .partner-search-results {
        top: calc(100% + 4px);
        border-color: var(--quick-border-strong);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(16, 24, 40, 0.16);
      }

      .popup .partner-search-item,
      .popup .partner-search-empty {
        padding: 10px 12px;
      }

      .popup .partner-search-item:hover,
      .popup .partner-search-empty:hover {
        background: var(--quick-soft);
      }

      .partner-fields-grid {
        display: grid;
        grid-template-columns: 140px minmax(220px, 1fr) minmax(140px, 190px);
        gap: 8px;
      }

      .popup .partner-fields-grid input[name="partner_address"] {
        grid-column: 2 / 4;
      }

      .popup .partner-create-btn {
        min-height: 32px;
        margin: 0;
        padding: 6px 10px;
        border: 1px solid var(--quick-green);
        border-radius: 6px;
        background: #fff;
        color: var(--quick-green);
        font-weight: 700;
      }

      .popup .submit-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin: 0;
        padding: 10px 12px;
        box-shadow: none;
        z-index: 10;
      }

      .popup .submit-actions .button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        min-width: 170px;
        margin: 0;
        padding: 8px 14px;
        border: 0;
        border-radius: 6px;
        color: #fff;
        font-size: 13px;
        font-family: Arial, Helvetica, sans-serif;
        font-style: normal !important;
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: 0 !important;
        text-decoration: none;
        text-transform: none !important;
        transform: none !important;
        clip-path: none !important;
        box-shadow: none !important;
      }

      .popup .submit-actions .button::before,
      .popup .submit-actions .button::after {
        display: none !important;
        content: none !important;
      }

      .popup .button-order {
        background: var(--quick-green);
      }

      .popup .button-prepayment {
        background: var(--quick-blue);
      }

      .busy_bgr {
        border-radius: 8px;
      }

      @media (max-width: 960px) {
        .quick-order-shell {
          padding: 12px;
        }

        .items-head {
          display: none;
        }

        .items-head,
        .popup .top-long-fields {
          grid-template-columns: 32px 1fr;
        }

        .stock-grid,
        .popup .top-long-fields input[name="prod"],
        .popup .top-long-fields input[name="qty"],
        .popup .top-long-fields input[name="price"] {
          grid-column: 2;
        }

        .services-grid,
        .popup .user-fields,
        .partner-fields-grid {
          grid-template-columns: 1fr;
        }

        .popup .partner-fields-grid input[name="partner_address"] {
          grid-column: auto;
        }

        .popup .submit-actions {
          position: static;
          flex-direction: column;
        }

        .popup .submit-actions .button {
          width: 100%;
        }
      }

      @media (max-width: 560px) {
        .stock-grid {
          grid-template-columns: repeat(4, minmax(28px, 1fr));
        }
      }

      .popup.msg #quick-buy-form{
        display: none;
      }

      .popup.msg a.button{
        display: none;
      }

      .popup.msg #quick-buy-msg{
        display: block;
      }
    </style>
  </div>
</div>

<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script> -->
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script>
<script>

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

let $items = JSON.parse(localStorage.getItem('allEntries'));

let $price = [];
let $count = [];
let $article = [];
let $articles = [];
let $user = '';

$.each($items, function(index, item) {
  const safeProd = (item.prod ? item.prod : (item.article || ''));
  const $row = $('<div class="top-long-fields order-item-row"></div>');
  const $remove = $('<button type="button" class="delete_item order-item-remove" title="Noņemt">×</button>');
  const $stock = $('<span class="stock-grid"></span>').attr('data-article', item.article);

  [
    ['URS', 'urs_quantity', 'default'],
    ['KRS', 'krs_quantity', 'default'],
    ['Latakko', 'latakko', 'pointer'],
    ['Goodyear', 'goodyear', 'pointer'],
    ['RiepuZona', 'riepuzona', 'pointer'],
    ['Duell', 'duell', 'pointer'],
    ['StarCo', 'starco', 'pointer'],
  ].forEach(function(stockField) {
    $stock.append(
      $('<input type="text" readonly>')
        .attr('title', stockField[0])
        .attr('name', stockField[1])
        .css('cursor', stockField[2])
    );
  });

  $row
    .append($remove)
    .append($stock)
    .append($('<input type="text" placeholder="Prece" name="prod" readonly>').val(safeProd))
    .append('<label for="qty">Sk.</label>')
    .append($('<input type="number" min="1" placeholder="Daudzums" name="qty">')
      .val(item.qty)
      .on('change keyup', function() { calcQuickBuyPrice(); }))
    .append('<label for="price">Cena</label>')
    .append($('<input type="text" placeholder="Cena" name="price">')
      .val(item.price)
      .on('change keyup', function() { calcQuickBuyPrice(); }))
    .appendTo('.inserthere');

  $article.push(item.article);
  $price.push(parseInt(item.price) * item.qty);
  $count.push(item.qty);
  $user = item.user;
});

$articles = $article;

$price = $price.reduce((a, b) => a + b, 0);
$count = $count.reduce((a, b) => a + b, 0);

let $globalPrice;

$article = $article.join('$');
$('#quick-buy-form input[name=article]').val($article);
$('.user-fields input[name=user]').val($user);
$('#quick-buy-form input[name=total]').val(isNaN($price) ? '' : $price);
$globalPrice = $price;
updateTotalPreview();

$price = [];
$count = [];

function updateTotalPreview(){
  const total = $('#quick-buy-form input[name=total]').val();
  $('#total-preview').text(total && !isNaN(parseFloat(total)) ? total : '0');
}

function calcQuickBuyPrice(addServices = false){
  $('.inserthere .top-long-fields').each(function(index, value) {
    $price.push(parseInt($('input[name=qty]', value).val()) * $('input[name=price]', value).val());
  });
  $price = $price.reduce((a, b) => a + b, 0);
  if (addServices === true) {
    if ($('#quick-buy-form input[name=price_montage]').val() > 0) {
      $price = $price + parseInt($('#quick-buy-form input[name=price_montage]').val());
    } else if ($('#quick-buy-form input[name=price_safe]').val() > 0) {
      $price = $price + parseInt($('#quick-buy-form input[name=price_safe]').val());
    } else if ($('#quick-buy-form input[name=price_montage]').val() > 0 && $('#quick-buy-form input[name=price_safe]').val() > 0) {
      $price = $price + parseInt($('#quick-buy-form input[name=price_montage]').val()) + parseInt($('#quick-buy-form input[name=price_safe]').val());
    }
  }
  $globalPrice = $price;
  $('#quick-buy-form input[name=total]').val(isNaN($price) ? '' : $price);
  updateTotalPreview();
  $price = [];
}

$('#quick-buy-form').on('click', '.delete_item', function() {
  let posCount = $('.top-long-fields').length;
  let $title = $(this).parent().children('input[name=prod]').val();
  $(this).parent().remove();
  $.each($items, function(index, value) {
    if ((value.prod || value.article) === $title) {
      delete $items[index];
    }
  });
  let existingEntries = [];
  $items = Object.entries($items)
    .filter(([key, value]) => value !== undefined)
    .reduce((obj, [key, value]) => {
      existingEntries.push(value);
      obj[key] = value;
      return obj;
    }, {});
  localStorage.setItem('allEntries', JSON.stringify(existingEntries));
  $article = [];
  $.each($items, function(index, item) {
    $article.push(item.article);
  });
  $article = $article.join('$');
  $('#quick-buy-form input[name=article]').val($article);
  calcQuickBuyPrice(true);
  if (posCount === 1) {
    localStorage.removeItem('allEntries');
    window.close();
  }
});

$.ajax({
  url: '/sync/accrual',
  method: 'GET',
  dataType: 'JSON',
  data: {'articles': $articles},
  success: function(data) {
    if (data && data.error) {
      alert(data.error);
      return;
    }
    $.each(data, function(index, value) {
      let item = JSON.parse(value);
      $('#quick-buy-form span[data-article="' + item.article + '"] input[name=urs_quantity]').val(item.urs_quantity);
      $('#quick-buy-form span[data-article="' + item.article + '"] input[name=krs_quantity]').val(item.krs_quantity);
    });
  },
  error: function(xhr) {
    let errorMsg = 'Accrual sync failed';
    if (xhr.responseJSON && xhr.responseJSON.error) {
      errorMsg = xhr.responseJSON.error;
    } else if (xhr.responseText) {
      try {
        const response = JSON.parse(xhr.responseText);
        if (response && response.error) {
          errorMsg = response.error;
        }
      } catch (e) {
        if (xhr.responseText.trim() !== '') {
          errorMsg = xhr.responseText.trim();
        }
      }
    }
    alert(errorMsg);
  }
});

$.ajax({
  url: '/getLinks',
  method: 'POST',
  dataType: 'JSON',
  data: {'articles': $articles},
  success: function(data) {
    $.each(data, function(index, value) {
      let $linkArticle = index;
      let $item = $('#quick-buy-form span[data-article="' + $linkArticle + '"]');
      if (value.Latakko) {
        $item.find('input[name=latakko]').val(value.Latakko.remaining).on('click', function() {
          window.open(value.Latakko.link);
        });
      } else {
        $item.find('input[name=latakko]').css('visibility', 'hidden');
      }

      if (value.Goodyear) {
        $item.find('input[name=goodyear]').val(value.Goodyear.remaining).on('click', function() {
          window.open(value.Goodyear.link);
        });
      } else {
        $item.find('input[name=goodyear]').css('visibility', 'hidden');
      }

      if (value.RiepuZona) {
        $item.find('input[name=riepuzona]').val(value.RiepuZona.remaining).on('click', function() {
          window.open(value.RiepuZona.link);
        });
      } else {
        $item.find('input[name=riepuzona]').css('visibility', 'hidden');
      }

      if (value.Duell) {
        $item.find('input[name=duell]').val(value.Duell.remaining).on('click', function() {
          window.open(value.Duell.link);
        });
      } else {
        $item.find('input[name=duell]').css('visibility', 'hidden');
      }

      if (value.Starco) {
        $item.find('input[name=starco]').val(value.Starco.remaining).on('click', function() {
          window.open(value.Starco.link);
        });
      } else {
        $item.find('input[name=starco]').css('visibility', 'hidden');
      }
    });
  }
});

function getFormData(form){
  var paramObj = {};
  $.each(form.serializeArray(), function(_, kv) {
    //console.log(kv);
    if (paramObj.hasOwnProperty(kv.name)) {
      paramObj[kv.name] = $.makeArray(paramObj[kv.name]);
      paramObj[kv.name].push(kv.value);
    }
    else {
      paramObj[kv.name] = kv.value;
    }
  });
  return paramObj;
}

let partnerSearchTimer = null;

function fillPartnerFields(partner) {
  $('#partner-id').val(partner.id || '');
  $('#partner-name').val(partner.name || '');
  $('#partner-regnr').val(partner.regnr || '');
  $('#partner-address').val(partner.address || '');
  $('#partner-search').val(partner.label || partner.name || '');
  hidePartnerSearchResults();
}

function hidePartnerSearchResults() {
  $('#partner-search-results').removeClass('is-open').empty();
}

function renderPartnerSearchResults(items, query) {
  const $results = $('#partner-search-results');
  $results.empty();

  if (!items.length) {
    $results.append(
      $('<button type="button" class="partner-search-empty"></button>')
        .text('Nav atrasts — izveidot jaunu klientu')
        .on('click', function() {
          openCreatePartnerDialog(query);
        })
    );
  } else {
    items.forEach(function(partner) {
      const $btn = $('<button type="button" class="partner-search-item"></button>');
      $btn.append(document.createTextNode(partner.label || partner.name));
      if (partner.address) {
        $btn.append($('<small></small>').text(partner.address));
      }
      $btn.on('click', function() {
        fillPartnerFields(partner);
      });
      $results.append($btn);
    });

    $results.append(
      $('<button type="button" class="partner-search-empty"></button>')
        .text('+ Jauns klients: "' + query + '"')
        .on('click', function() {
          openCreatePartnerDialog(query);
        })
    );
  }

  $results.addClass('is-open');
}

function searchPartners(query) {
  $.ajax({
    url: '/accrual-partners/search',
    method: 'GET',
    dataType: 'json',
    data: { q: query, limit: 25 },
    success: function(response) {
      if (response.error) {
        alert(response.error);
        return;
      }
      renderPartnerSearchResults(response.items || [], query);
    },
    error: function(xhr) {
      let message = 'Neizdevās ielādēt klientu sarakstu';
      if (xhr.responseJSON && xhr.responseJSON.error) {
        message = xhr.responseJSON.error;
      }
      alert(message);
    }
  });
}

function openCreatePartnerDialog(prefillName) {
  Swal.fire({
    title: 'Jauns klients',
    html:
      '<input id="swal-partner-name" class="swal2-input" placeholder="Nosaukums / vārds" value="' + $('<div>').text(prefillName || '').html() + '">' +
      '<input id="swal-partner-regnr" class="swal2-input" placeholder="Reģ. Nr.">' +
      '<input id="swal-partner-address" class="swal2-input" placeholder="Adrese">',
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Saglabāt',
    cancelButtonText: 'Atcelt',
    preConfirm: function() {
      const name = $('#swal-partner-name').val().trim();
      if (!name) {
        Swal.showValidationMessage('Norādiet klienta nosaukumu');
        return false;
      }
      return {
        name: name,
        regnr: $('#swal-partner-regnr').val().trim(),
        address: $('#swal-partner-address').val().trim(),
      };
    }
  }).then(function(result) {
    if (!result.isConfirmed || !result.value) {
      return;
    }

    $.ajax({
      url: '/accrual-partners',
      method: 'POST',
      dataType: 'json',
      data: result.value,
      success: function(response) {
        if (response.error) {
          Swal.fire('Kļūda!', response.error, 'error');
          return;
        }
        fillPartnerFields(response.partner);
        Swal.fire(
          response.created ? 'Saglabāts' : 'Atrasts esošs klients',
          response.created ? 'Jauns klients ir pievienots Accrual.' : 'Šāds klients jau eksistē — izmantots esošais ieraksts.',
          'success'
        );
      },
      error: function(xhr) {
        let message = 'Neizdevās izveidot klientu';
        if (xhr.responseJSON && xhr.responseJSON.error) {
          message = xhr.responseJSON.error;
        }
        Swal.fire('Kļūda!', message, 'error');
      }
    });
  });
}

function initPartnerPicker() {
  $('#partner-search').on('input', function() {
    const query = $(this).val().trim();
    clearTimeout(partnerSearchTimer);

    if (query.length < 2) {
      hidePartnerSearchResults();
      return;
    }

    partnerSearchTimer = setTimeout(function() {
      searchPartners(query);
    }, 300);
  });

  $('#partner-name, #partner-regnr, #partner-address').on('input', function() {
    $('#partner-id').val('');
  });

  $('#partner-create-btn').on('click', function() {
    openCreatePartnerDialog($('#partner-search').val().trim() || $('#partner-name').val().trim());
  });

  $(document).on('click', function(event) {
    if (!$(event.target).closest('.partner-search-wrap, #partner-create-btn').length) {
      hidePartnerSearchResults();
    }
  });
}

function sendData(data, documentType){
  documentType = documentType || 'order';
  data.document_type = documentType;

  if (documentType === 'prepayment') {
    $('#partner-fields').show();
    if (!data.partner_name || String(data.partner_name).trim() === '') {
      Swal.fire({
        title: 'Kļūda!',
        text: 'Rēķinam priekšapmaksai jānorāda klienta nosaukums.',
        icon: 'error',
        confirmButtonText: 'OK'
      });
      return false;
    }
  }

  const isPrepayment = documentType === 'prepayment';
  const errorTextDefault = isPrepayment ? 'Rēķins priekšapmaksai nav izveidots!' : 'Pasūtījums nav pieņemts!';

  $('#quick-buy-form').parent().addClass('busy');
  $.ajax({
    type: 'POST',
    url: '/accrualOrder',
    data: {info: data, '_token': data._token},
    timeout: 20000,
    success: function(resp){
      resp = JSON.parse(resp);
      if (resp.success) {
        if (isPrepayment) {
          Swal.fire({
            title: 'Paziņojums',
            html: resp.success,
            icon: 'success',
            confirmButtonText: 'Skatīt HTML',
            showCancelButton: true,
            cancelButtonText: 'Aizvērt',
          }).then((result) => {
            if (result.isConfirmed && resp.previewUrl) {
              window.open(resp.previewUrl, '_blank');
            }
            localStorage.removeItem('allEntries');
            if (result.isConfirmed || result.isDismissed) {
              window.close();
            }
          });
          return;
        }

        Swal.fire({
          title: 'Paziņojums',
          html: resp.success,
          icon: 'success',
          confirmButtonText: 'OK',
          showDenyButton: true,
          denyButtonText: 'SMS',
        }).then((result) => {
          if (result.isConfirmed) {
            localStorage.removeItem('allEntries');
            window.close();
          } else if (result.isDenied) {
            $.ajax({
              method: 'POST',
              url: '/orderSMS',
              data: {info: data, orderId: resp.orderId, '_token': data._token},
              timeout: 10000,
              success: function(response) {
                Swal.fire({
                title: 'Paziņojums',
                html: response.success,
                icon: 'success',
                confirmButtonText: 'OK',
                }).then((result) => {
                  if (result.isConfirmed) {
                    localStorage.removeItem('allEntries');
                    window.close();
                  }
                })
              }
            });
          }
        });
      } else if (resp.danger) {
        Swal.fire({
          title: 'Kļūda!',
          html: resp.danger,
          icon: 'error',
          confirmButtonText: 'OK'
        });
      }
    },
    error: function(jqXHR, textStatus){
      let errorText = errorTextDefault;
      if (textStatus === 'timeout') {
        errorText = 'Servera atbilde kavējas. Mēģiniet vēlreiz.';
      } else if (jqXHR && jqXHR.responseText) {
        try {
          const response = JSON.parse(jqXHR.responseText);
          if (response && response.danger) {
            errorText = response.danger;
          }
        } catch (e) {
          // Keep default fallback text
        }
      }

      Swal.fire({
        title: 'Kļūda!',
        text: errorText,
        icon: 'error',
        confirmButtonText: 'OK'
      });
    },
    complete: function(){
      $('#quick-buy-form').parent().removeClass('busy');
    }
  })
}

$(document).ready(function() {
  initPartnerPicker();
  $('.button-prepayment').on('mousedown', function() {
    $('#partner-fields').show();
  });
  $('.swal2-confirm').on('click', function() { window.close(); })
});

let montage = '';
let safe = '';

function toggleMontage(){
  if ($('#montage').prop('checked')) {
    $('#quick-buy-form input[name=price_montage]').removeAttr('disabled').focus();
  } else {
    $('#quick-buy-form input[name=price_montage]').attr('disabled','disabled').val('');
  }
  addMontagePrice();
}

function addMontagePrice(){
  if (!isNaN(parseFloat($('#quick-buy-form input[name=price]').val())) && !isNaN(parseFloat($('#quick-buy-form input[name=qty]').val()))) {
    montage = isNaN(parseInt($('#quick-buy-form input[name=price_montage]').val())) ? 0 : parseInt($('#quick-buy-form input[name=price_montage]').val());
    $('#quick-buy-form input[name=total]').val($globalPrice + montage);
    if (montage > 0 && safe > 0) {
      $('#quick-buy-form input[name=total]').val($globalPrice + montage + safe);
    } else if (montage === 0 && safe > 0) {
      $('#quick-buy-form input[name=total]').val($globalPrice + safe);
    } else if (montage === 0) {
      calcQuickBuyPrice(true);
    }
    updateTotalPreview();
  }
}

function toggleSafe(){
  if ($('#safe').prop('checked')) {
    $('#quick-buy-form input[name=price_safe]').removeAttr('disabled').focus();
  } else {
    $('#quick-buy-form input[name=price_safe]').attr('disabled','disabled').val('');
  }
  addSafePrice();
}

function toggleMobile(){
  if ($('#mobile').prop('checked')) {
    $('#quick-buy-form input[name=mobile_number]').removeAttr('disabled').focus();
  } else {
    $('#quick-buy-form input[name=mobile_number]').attr('disabled','disabled').val('');
  }
}

function addSafePrice(){
  if (!isNaN(parseFloat($('#quick-buy-form input[name=price]').val())) && !isNaN(parseFloat($('#quick-buy-form input[name=qty]').val()))) {
    safe = isNaN(parseFloat($('#quick-buy-form input[name=price_safe]').val())) ? 0 : parseFloat($('#quick-buy-form input[name=price_safe]').val());
    $('#quick-buy-form input[name=total]').val(safe + $globalPrice);
    if (safe > 0 && montage > 0) {
      $('#quick-buy-form input[name=total]').val(safe + montage + $globalPrice);
    } else if (safe === 0 && montage > 0) {
      $('#quick-buy-form input[name=total]').val(montage + $globalPrice);
    } else if (safe === 0) {
      calcQuickBuyPrice(true);
    }
    updateTotalPreview();
  }
}

function showQuickBuyForm(id) {
  $('#quick-buy-form input[name=prod_id]').val(id);
  $('#quick-buy-form input[name=price]').val($('#js-product-list article[data-id-product-attribute="'+id+'"]').find('.price').text().substr(2));
  $('#quick-buy-form input[name=prod]').val($('#js-product-list article[data-id-product-attribute="'+id+'"]').find('.product-title-hidden').text());
  calcQuickBuyPrice();
};

const QUICK_ORDER_MIN_WIDTH = 1150;
const QUICK_ORDER_MIN_HEIGHT = 760;
let quickOrderResizeTimer = null;
let quickOrderIsResizing = false;

function resizeQuickOrderWindow(width, height) {
  if (quickOrderIsResizing) {
    return;
  }

  quickOrderIsResizing = true;
  try {
    window.resizeTo(width, height);
  } catch (e) {
    // Browsers can block resizeTo for normal tabs; CSS min-size still keeps layout stable.
  }

  setTimeout(function() {
    quickOrderIsResizing = false;
  }, 150);
}

function enforceQuickOrderWindowSize() {
  const currentWidth = window.outerWidth || window.innerWidth;
  const currentHeight = window.outerHeight || window.innerHeight;
  const targetWidth = Math.max(currentWidth, QUICK_ORDER_MIN_WIDTH);
  const targetHeight = Math.max(currentHeight, QUICK_ORDER_MIN_HEIGHT);

  if (targetWidth !== currentWidth || targetHeight !== currentHeight) {
    resizeQuickOrderWindow(targetWidth, targetHeight);
  }
}

resizeQuickOrderWindow(QUICK_ORDER_MIN_WIDTH, QUICK_ORDER_MIN_HEIGHT);
$(window).on('resize', function() {
  clearTimeout(quickOrderResizeTimer);
  quickOrderResizeTimer = setTimeout(enforceQuickOrderWindowSize, 80);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" integrity="sha384-fbbOQedDUMZZ5KreZpsbe1LCZPVmfTnH7ois6mU1QK+m14rQ1l2bGBq41eYeM/fS" crossorigin="anonymous"></script></body>
</html>
