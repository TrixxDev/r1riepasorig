@if (!empty($marketingPurchase) && !empty($marketingPurchase['event_id']))
  <script>
    (function () {
      var payload = @json($marketingPurchase);

      function trackPurchase() {
        if (typeof window.r1TrackPurchasePage !== 'function') {
          return;
        }
        window.r1TrackPurchasePage(payload);
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', trackPurchase);
      } else {
        trackPurchase();
      }
    })();
  </script>
@endif
