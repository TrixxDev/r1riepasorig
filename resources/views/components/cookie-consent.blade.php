<style>
  #cookie-consent-banner.cookie-banner-so {
    display: none;
    position: fixed;
    left: 16px;
    right: 16px;
    bottom: 16px;
    z-index: 99999;
    width: 32.41rem;
    max-width: calc(100% - 32px);
    background: #252627;
    color: #e3e5e8;
    border: 1px solid #3b4045;
    border-radius: 8px;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.45);
    padding: 32px;
    font-size: 14px;
    line-height: 1.30769231;
    font-family: Arial, Helvetica, sans-serif;
  }

  #cookie-consent-banner .so-title {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 4px;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  #cookie-consent-banner .so-text {
    color: #e3e5e8;
    margin-bottom: 12px;
  }

  #cookie-consent-banner .so-link {
    color: #90c4f9;
    text-decoration: underline;
  }

  #cookie-consent-banner .so-categories {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin: 8px 0 12px;
  }

  #cookie-consent-banner .so-categories.is-hidden {
    display: none;
  }

  #cookie-consent-banner .so-cat {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #f8f9f9;
    cursor: pointer;
    padding: 4px 8px;
    border: 1px solid #4d4d4d;
    border-radius: 999px;
    background: #252627;
  }

  #cookie-consent-banner .so-actions {
    display: flex;
    gap: 8px;
    flex-wrap: nowrap;
    margin-bottom: 8px;
  }

  #cookie-consent-banner .so-customize {
    background: transparent;
    border: none;
    color: #90c4f9;
    text-decoration: underline;
    padding: 0;
    font-size: 13px;
    cursor: pointer;
  }

  #cookie-consent-banner .so-btn {
    border: 1px solid transparent;
    border-radius: 6px;
    padding: .8em;
    font-size: 13px;
    cursor: pointer;
    line-height: 1.15;
    width: 100%;
    font-weight: 400;
  }

  #cookie-consent-banner .so-btn-outline {
    border-color: #90c4f9;
    background: #252627;
    color: #90c4f9;
  }

  #cookie-consent-banner .so-btn-primary {
    border-color: #90c4f9;
    background: #90c4f9;
    color: #252627;
  }

  #cookie-consent-banner .so-btn-secondary {
    border-color: #90c4f9;
    background: #252627;
    color: #90c4f9;
  }

  #cookie-consent-banner .so-btn:hover {
    filter: brightness(1.03);
  }

  #cookie-consent-banner .so-btn:active {
    transform: translateY(1px);
    filter: brightness(0.95);
  }

  #cookie-consent-banner .so-cookie-icon {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #90c4f9;
    color: #252627;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    line-height: 1;
  }

  @media (max-width: 640px) {
    #cookie-consent-banner.cookie-banner-so {
      left: 12px;
      right: 12px;
      bottom: 16px;
      width: auto;
      padding: 16px;
    }

    #cookie-consent-banner .so-actions {
      flex-direction: column;
    }

    #cookie-consent-banner .so-btn {
      width: 100%;
      text-align: center;
    }
  }
</style>

<div
  id="cookie-consent-banner"
  class="cookie-banner-so"
  data-clarity-project-id="{{ $clarityProjectId ?? '' }}"
  data-gtm-id="{{ $gtmId ?? '' }}"
  data-ga4-id="{{ $ga4Id ?? '' }}"
  data-facebook-pixel-id="{{ $facebookPixelId ?? '' }}"
  data-google-ads-id="{{ $googleAdsConversionId ?? '' }}"
  data-google-ads-purchase-label="{{ $googleAdsPurchaseLabel ?? '' }}"
  data-google-ads-booking-label="{{ $googleAdsBookingLabel ?? '' }}"
>
  <div class="so-title"><span class="so-cookie-icon">🍪</span> Mēs izmantojam sīkdatnes</div>
  <div class="so-text">
    Izmantojam sīkdatnes vietnes darbībai, analītikai un mārketingam. Neobligātās sīkdatnes aktivizējam tikai pēc Jūsu izvēles.
    <a href="/privatuma-politika" class="so-link">Privātuma politika</a>.
  </div>

  <div id="cookie-categories" class="so-categories is-hidden">
    <label class="so-cat">
      <input id="cookie-analytics" type="checkbox" checked>
      <span>Analītika</span>
    </label>
    <label class="so-cat">
      <input id="cookie-marketing" type="checkbox" checked>
      <span>Mārketings</span>
    </label>
  </div>

  <div class="so-actions">
    <button id="cookie-accept" type="button" class="so-btn so-btn-primary">Pieņemt visas sīkdatnes</button>
    <button id="cookie-reject" type="button" class="so-btn so-btn-outline">Tikai nepieciešamās sīkdatnes</button>
    <button id="cookie-save" type="button" class="so-btn so-btn-secondary">Saglabāt izvēli</button>
  </div>
  <button id="cookie-customize" type="button" class="so-customize">Pielāgot iestatījumus</button>
</div>
