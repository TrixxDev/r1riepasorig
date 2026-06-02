<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>
<body>

<label for="fileUpload" class="dragNdrop">
  Pidr
</label>

<input type="file" id="fileUpload" style="display: none;">

<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script>
  $(document).ready(function() {
    $('.dragNdrop').on('dragover', function(e) {
      e.preventDefault();
      console.log(123);
    }).on('drop', function(e) {
      e.preventDefault();
      console.log(321);
    });
  });
</script>
</body>
</html>
