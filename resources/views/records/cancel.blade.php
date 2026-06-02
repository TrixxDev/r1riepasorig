<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="utf-8">
  <title>R1 Riepu Serviss - Pieraksta atcelšana</title>
  <script src="https://code.jquery.com/jquery-3.6.1.js" integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI=" crossorigin="anonymous"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <style>
    .confirm-delete-body {
      text-align: center;
      margin-bottom: 45px;
      font-size: calc(1.5rem + 1vw);
    }
  </style>
</head>
<body>

<div class="container-fluid mt-5">
  <div class="row">
    <form method="post">
    <div class="col confirm-delete-col">
      <div class="confirm-delete-body">
        <div>
          Jūsu pieraksts:
          {{ $office->title }}, {{ $_weekDays[date('N', strtotime($slot->date))] }}, {{ date('d.m.Y', strtotime($slot->date)) }}, pl. {{ $time }} <br>
          Automašīnai: {!! $takenBy->car_brand !!} {!! $takenBy->car_model !!} <br>
          <span class="cancelQ" style="color: #e30000; font-size: 4.5rem; position: relative; top: 15px;">Vai vēlaties atcelt pierakstu?</span>
        </div>

      </div>
      <div class="row">
        <div class="col" style="text-align: center; width: 100%;">
          @if (isset($errorMessage))
          <span style="font-size: 2.8rem; color: red;">{!! $errorMessage !!}</span><br><br>
          @endif
          <span class="input-message" style="font-size: 2.8rem;"></span>
        </div>
      </div>
      <div class="row" style="text-align: center;">
        <div class="col">
            <input class="form-control" type="text" name="deleteNr" style="height: 62px; width: 100%;">
        </div>
        <div class="col">
            <button class="btn btn-primary" type="submit" name="delete" style="width: 75%; font-size: 2rem;">Apstiprināt</button>
        </div>
      </div>
    </div>
    </form>
  </div>
</div>

<script>

  let input_message = 'Lai apstiprinātu atcelšanu, ievadiet auto numura zīmi';

  if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    // IF MOBILE
    $('.confirm-delete-body').css({"font-size": "2.8rem"});
    $('button[name="delete"]').css({"font-size": "3rem"});
    $('input[name="deleteNr"]').css({"height": "86px", "font-size": "1.8rem"}).removeAttr('placeholder');
    $('.confirm-delete-col').css({"margin-top": "200px"});
    $('.row .col:nth-child(1)').addClass('col-md-9').removeClass('col').css({"margin": "0 auto", "margin-bottom": "50px"});
    $('.input-message').html(input_message).show();

  } else {
    // IF DESKTOP
    $('.container-fluid').addClass('container').removeClass('container-fluid');
    $('button[name="delete"]').css({"font-size": "3rem"});
    $('input[name="deleteNr"]').css({"height": "86px"}).attr('placeholder', input_message);
    $('.row .col-md-9').addClass('col').removeClass('col-md-9').removeAttr('style');
    $('.input-message').hide();
    $('.cancelQ').css({"font-size": "3.5rem"});
  }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>
