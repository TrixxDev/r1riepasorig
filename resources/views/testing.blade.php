<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="utf-8">
  <title>R1 Riepu Serviss</title>
  <script src="https://code.jquery.com/jquery-3.6.1.js" integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI=" crossorigin="anonymous"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <style>
    /*.confirm-delete-body {*/
    /*  text-align: center;*/
    /*  margin-bottom: 45px;*/
    /*  font-size: calc(1.5rem + 1vw);*/

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
    body {
      background-color: #f2f8ff;
      color: #263238;
      font-family: 'Noto Sans', sans-serif;
      margin: 0;
      padding: 2em 6vw;
    }
    .grid {
      display: grid;
      grid-gap: var(--card-padding);
      margin: 0 auto;
      max-width: 60em;
      padding: 0;
    }
    @media (min-width: 42em) {
      .grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    .card {
      background-color: #fff;
      border-radius: var(--card-radius);
      position: relative;
    }
    .card:hover {
      box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.15);
    }
    .radio {
      font-size: inherit;
      margin: 0;
      position: absolute;
      right: calc(var(--card-padding) + var(--radio-border-width));
      top: calc(var(--card-padding) + var(--radio-border-width));
    }
    @supports (-webkit-appearance: none) or (-moz-appearance: none) {
      .radio {
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
      .radio::after {
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
      .radio:checked {
        background: var(--color-green);
        border-color: var(--color-green);
      }
      .card:hover .radio {
        border-color: var(--color-dark-gray);
      }
      .card:hover .radio:checked {
        border-color: var(--color-green);
      }
    }
    .plan-details {
      border: var(--radio-border-width) solid var(--color-gray);
      border-radius: var(--card-radius);
      cursor: pointer;
      display: flex;
      flex-direction: column;
      padding: var(--card-padding);
      transition: border-color 0.2s ease-out;
    }
    .card:hover .plan-details {
      border-color: var(--color-dark-gray);
    }
    .radio:checked ~ .plan-details {
      border-color: var(--color-green);
    }
    .radio:focus ~ .plan-details {
      box-shadow: 0 0 0 2px var(--color-dark-gray);
    }
    .radio:disabled ~ .plan-details {
      color: var(--color-dark-gray);
      cursor: default;
    }
    .radio:disabled ~ .plan-details .plan-type {
      color: var(--color-dark-gray);
    }
    .card:hover .radio:disabled ~ .plan-details {
      border-color: var(--color-gray);
      box-shadow: none;
    }
    .card:hover .radio:disabled {
      border-color: var(--color-gray);
    }
    .plan-type {
      color: var(--color-green);
      font-size: 1.5rem;
      font-weight: bold;
      line-height: 1em;
    }
    .plan-cost {
      font-size: 2.5rem;
      font-weight: bold;
      padding: 0.5rem 0;
    }
    .slash {
      font-weight: normal;
    }
    .plan-cycle {
      font-size: 2rem;
      font-variant: none;
      border-bottom: none;
      cursor: inherit;
      text-decoration: none;
    }
    .hidden-visually {
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

  </style>
</head>
<body>

{{--<div class="container-fluid mt-5">--}}
{{--  <div class="row">--}}
{{--    <div class="col confirm-delete-col">--}}
{{--      <div class="confirm-delete-body">--}}
{{--        <div>--}}
{{--          Jūsu pieraksts:--}}
{{--          Ulbrokā, piektdien, 03.03.2023, pl. 17:40 <br>--}}
{{--          Automašīnai: Vw Golf <br>--}}
{{--          Vai vēlaties atcelt pierakstu?--}}
{{--        </div>--}}

{{--      </div>--}}
{{--      <div class="row" style="text-align: center;">--}}
{{--        <div class="col">--}}
{{--          <button class="btn btn-primary" style="width: 75%; font-size: 3rem;">Jā</button>--}}
{{--        </div>--}}
{{--        <div class="col">--}}
{{--          <button class="btn btn-secondary" style="width: 75%; font-size: 3rem;">Nē</button>--}}
{{--        </div>--}}
{{--      </div>--}}
{{--    </div>--}}
{{--  </div>--}}
{{--</div>--}}

<div class="grid">
  <label class="card">
    <input name="plan" class="radio" type="radio" checked>

    <span class="plan-details">
{{--      <span class="plan-type">Basic</span>--}}
      <span class="plan-cost">Ulbroka</span>
      <span>Acones iela 2a</span>
      <span>67910555</span>
      <br>
      <span>Pirm. - Piekt. 9:00 - 18:00</span>
      <span>Sestdiena - 10:00 - 15:00</span>
      <span>Svētdiena - Slēgts</span>
    </span>
  </label>
  <label class="card">
    <input name="plan" class="radio" type="radio">
    <span class="hidden-visually">Pro - $50 per month, 5 team members, 500 GB per month, 5 concurrent builds</span>
    <span class="plan-details" aria-hidden="true">
{{--      <span class="plan-type">Pro</span>--}}
      <span class="plan-cost">Rīga</span>
      <span>Kalnciema iela 39</span>
      <span>67615615</span>
      <br>
      <span>Pirm. - Piekt. 9:00 - 18:00</span>
      <span>Sestdiena - Slēgts</span>
      <span>Svētdiena - Slēgts</span>
    </span>
  </label>
</div>




{{--<div class="section over-hide z-bigger">--}}
{{--  <div class="section over-hide z-bigger">--}}
{{--    <div class="container pb-5">--}}
{{--      <div class="row justify-content-center pb-5 mt-5">--}}
{{--        <div class="col-12 pb-5">--}}
{{--          <input class="checkbox-tools" type="radio" name="tools" id="address-1" checked>--}}
{{--          <label class="for-checkbox-tools" for="address-1">--}}
{{--            <i class='uil uil-line-alt'></i>--}}
{{--            <h3>Ulbroka</h3>--}}
{{--            <div>Acones iela 2a</div>--}}
{{--            <span>Darba laiks 9:00 - 18:00</span>--}}
{{--          </label>--}}
{{--          <input class="checkbox-tools" type="radio" name="tools" id="address-2" checked>--}}
{{--          <label class="for-checkbox-tools" for="address-2">--}}
{{--            <i class='uil uil-line-alt'></i>--}}
{{--            <h3>Rīga</h3>--}}
{{--            <div>Kalnciema iela</div>--}}
{{--            <span>Darba laiks 10:00 - 17:00</span>--}}
{{--          </label>--}}
{{--        </div>--}}

{{--      </div>--}}
{{--    </div>--}}
{{--  </div>--}}
{{--</div>--}}

<script>
  if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    // IF MOBILE
    $('.confirm-delete-body').css({"font-size": "4.5rem"});
    $('.confirm-delete-col').css({"margin-top": "200px"});

  } else {
    // IF DESKTOP
  $('.container-fluid').addClass('container').removeClass('container-fluid');
  }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>
