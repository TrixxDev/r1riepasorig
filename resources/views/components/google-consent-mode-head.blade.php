@php
  $gtmId = trim((string) env('GTM_ID', ''));
  $ga4Id = trim((string) env('GA4_MEASUREMENT_ID', ''));
  $googleAdsId = trim((string) config('marketing.google_ads.conversion_id', ''), " \t\n\r\0\x0B\"'");
  $googleAdsPurchaseLabel = trim((string) config('marketing.google_ads.conversion_label', ''), " \t\n\r\0\x0B\"'");
  $googleAdsBookingLabel = trim((string) config('marketing.google_ads.booking_conversion_label', ''), " \t\n\r\0\x0B\"'");
  $hasGoogle = $gtmId !== '' || $ga4Id !== '' || $googleAdsId !== '';
@endphp
@if ($hasGoogle)
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  window.gtag = window.gtag || gtag;
  gtag('consent', 'default', {
    ad_storage: 'denied',
    ad_user_data: 'denied',
    ad_personalization: 'denied',
    analytics_storage: 'denied',
    functionality_storage: 'denied',
    personalization_storage: 'denied',
    security_storage: 'granted',
    wait_for_update: 500
  });
  window.__r1ConsentModeInitialized = true;
</script>
@endif
@if ($gtmId !== '')
<script>
  window.__r1GtmLoaded = true;
  (function (w, d, s, l, i) {
    w[l] = w[l] || [];
    w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
    var f = d.getElementsByTagName(s)[0];
    var j = d.createElement(s);
    var dl = l !== 'dataLayer' ? '&l=' + l : '';
    j.async = true;
    j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
    f.parentNode.insertBefore(j, f);
  })(window, document, 'script', 'dataLayer', @json($gtmId));
</script>
@endif
@if ($googleAdsId !== '')
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleAdsId }}"></script>
@elseif ($ga4Id !== '' && $gtmId === '')
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
<script>
  gtag('js', new Date());
  gtag('config', @json($ga4Id));
</script>
@endif
@if ($googleAdsId !== '')
<script>
  window.R1_GOOGLE_ADS_CONFIG = {
    id: @json($googleAdsId),
    purchaseLabel: @json($googleAdsPurchaseLabel),
    bookingLabel: @json($googleAdsBookingLabel)
  };
  window.r1ApplyGoogleAdsLabels = function (override) {
    var cfg = window.R1_GOOGLE_ADS_CONFIG || {};
    var id = (override && override.googleAdsId) || cfg.id || '';
    var purchaseLabel = (override && override.googleAdsPurchaseLabel) || cfg.purchaseLabel || '';
    var bookingLabel = (override && override.googleAdsBookingLabel) || cfg.bookingLabel || '';
    if (!id) return;
    if (purchaseLabel) {
      window.R1_GOOGLE_ADS_PURCHASE = { send_to: id + '/' + purchaseLabel };
    }
    if (bookingLabel) {
      window.R1_GOOGLE_ADS_BOOKING = { send_to: id + '/' + bookingLabel };
    }
  };
  window.r1ApplyGoogleAdsLabels();
</script>
@endif
