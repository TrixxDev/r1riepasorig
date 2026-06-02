<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gaida rēķinu {{ $orderReference }}</title>
  <style>
    body { font-family: Tahoma, Arial, sans-serif; padding: 40px; text-align: center; }
    .spinner { margin: 20px auto; width: 36px; height: 36px; border: 4px solid #ddd; border-top-color: #1f6f3f; border-radius: 50%; animation: spin 1s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>
  <h1>Rēķins vēl tiek importēts no Accrual</h1>
  <p>Pasūtījums: <strong>{{ $orderReference }}</strong></p>
  <div class="spinner"></div>
  <p id="status-text">Meklējam rēķinu MSSQL datubāzē...</p>

  <script>
    const orderReference = @json($orderReference);
    @php
    $queryParams = array_filter([
      'partner' => $partnerName ?? null,
      'total' => $total ?? null,
    ], function($value) { return $value !== null && $value !== ''; });
    @endphp
    const statusUrl = @json(url('/prepayment-invoice/' . rawurlencode($orderReference) . '/status?' . http_build_query($queryParams)));

    function pollStatus() {
      fetch(statusUrl, { headers: { 'Accept': 'application/json' } })
        .then(response => response.json())
        .then(data => {
          if (data.ready && data.previewUrl) {
            window.location.href = data.previewUrl;
            return;
          }
          document.getElementById('status-text').textContent = 'Accrual vēl nav piešķīris PZNr. Mēģinām vēlreiz...';
        })
        .catch(() => {
          document.getElementById('status-text').textContent = 'Neizdevās sazināties ar serveri.';
        });
    }

    pollStatus();
    setInterval(pollStatus, 2000);
  </script>
</body>
</html>
