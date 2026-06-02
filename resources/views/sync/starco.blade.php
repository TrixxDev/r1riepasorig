<!DOCTYPE html>
<html>
<head>
  <title>Ajax Progress</title>

  <!-- Meta -->
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <meta name="description" content="">
  <meta name="viewport" content="width=device-width">

  <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />

  <!-- Google CDN -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
  <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/black-tie/jquery-ui.css" />

  <script>
    (function($, window, undefined) {
      //is onprogress supported by browser?
      var hasOnProgress = ("onprogress" in $.ajaxSettings.xhr());

      //If not supported, do nothing
      if (!hasOnProgress) {
        console.log(123);
        return;
      }

      //patch ajax settings to call a progress callback
      var oldXHR = $.ajaxSettings.xhr;
      $.ajaxSettings.xhr = function() {
        var xhr = oldXHR.apply(this, arguments);
        if(xhr instanceof window.XMLHttpRequest) {
          xhr.addEventListener('progress', this.progress, false);
        }

        if(xhr.upload) {
          xhr.upload.addEventListener('progress', this.progress, false);
        }

        return xhr;
      };
    })(jQuery, window);
  </script>
  <script>
    $(function() {
      $('#prog').progressbar({ value: 0 });

      $.ajax({
        method: 'GET',
        url: '/sync/starcoSync',
        success: function() {
          console.log('YAYE!', arguments[0]);
        },
        error: function() {
          console.log('AWWW!');
        },
        progress: function(e) {
          if(e.lengthComputable) {
            var pct = (e.loaded / e.total) * 100;

            $('#prog')
              .progressbar('option', 'value', pct)
              .children('.ui-progressbar-value')
              .html(pct.toPrecision(3) + '%')
              .css('display', 'block');
          } else {
            console.warn('Content Length not reported!');
          }
        }
      });
    });
  </script>
</head>
<body>
<div id="prog"></div>
</body>
</html>
