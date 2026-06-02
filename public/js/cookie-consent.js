(function () {
  var storageKey = "r1_cookie_consent_v1";
  var loadedCategories = {
    analytics: false,
    marketing: false
  };

  function injectScript(src, isAsync) {
    var script = document.createElement("script");
    script.src = src;
    script.async = typeof isAsync === "boolean" ? isAsync : true;
    document.head.appendChild(script);
  }

  function ensureGtag() {
    window.dataLayer = window.dataLayer || [];
    if (typeof window.gtag !== "function") {
      window.gtag = function () { window.dataLayer.push(arguments); };
    }
  }

  /**
   * Google Consent Mode v2 — sync banner choices with gtag/GTM (set in head as denied).
   */
  function updateGoogleConsent(preferences) {
    if (!window.__r1ConsentModeInitialized) return;
    ensureGtag();
    window.gtag("consent", "update", {
      analytics_storage: preferences.analytics ? "granted" : "denied",
      ad_storage: preferences.marketing ? "granted" : "denied",
      ad_user_data: preferences.marketing ? "granted" : "denied",
      ad_personalization: preferences.marketing ? "granted" : "denied"
    });
  }

  function loadClarity(consentConfig) {
    if (!consentConfig.clarityProjectId) return;
    (function (c, l, a, r, i, t, y) {
      c[a] = c[a] || function () { (c[a].q = c[a].q || []).push(arguments); };
      t = l.createElement(r); t.async = 1; t.src = "https://www.clarity.ms/tag/" + i;
      y = l.getElementsByTagName(r)[0]; y.parentNode.insertBefore(t, y);
    })(window, document, "clarity", "script", consentConfig.clarityProjectId);
  }

  function loadGoogleTracking(consentConfig) {
    if (window.__r1GtmLoaded) {
      return;
    }

    if (consentConfig.gtmId) {
      (function (w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({ "gtm.start": new Date().getTime(), event: "gtm.js" });
        var f = d.getElementsByTagName(s)[0];
        var j = d.createElement(s);
        var dl = l !== "dataLayer" ? "&l=" + l : "";
        j.async = true;
        j.src = "https://www.googletagmanager.com/gtm.js?id=" + i + dl;
        f.parentNode.insertBefore(j, f);
      })(window, document, "script", "dataLayer", consentConfig.gtmId);
      window.__r1GtmLoaded = true;
      return;
    }

    if (consentConfig.ga4Id) {
      injectScript("https://www.googletagmanager.com/gtag/js?id=" + encodeURIComponent(consentConfig.ga4Id), true);
      ensureGtag();
      window.gtag("js", new Date());
      window.gtag("config", consentConfig.ga4Id);
    }
  }

  function configureGoogleAdsLabels(consentConfig) {
    if (typeof window.r1ApplyGoogleAdsLabels === "function") {
      window.r1ApplyGoogleAdsLabels(consentConfig);
      return;
    }
    if (!consentConfig.googleAdsId) return;
    if (consentConfig.googleAdsPurchaseLabel) {
      window.R1_GOOGLE_ADS_PURCHASE = {
        send_to: consentConfig.googleAdsId + "/" + consentConfig.googleAdsPurchaseLabel
      };
    }
    if (consentConfig.googleAdsBookingLabel) {
      window.R1_GOOGLE_ADS_BOOKING = {
        send_to: consentConfig.googleAdsId + "/" + consentConfig.googleAdsBookingLabel
      };
    }
  }

  function loadGoogleAdsGtag(consentConfig) {
    if (!consentConfig.googleAdsId) return;
    ensureGtag();

    if (!window.__r1GoogleAdsGtagLoaded) {
      var existing = document.querySelector(
        'script[src*="googletagmanager.com/gtag/js"][src*="' + consentConfig.googleAdsId + '"]'
      );
      if (!existing) {
        injectScript(
          "https://www.googletagmanager.com/gtag/js?id=" + encodeURIComponent(consentConfig.googleAdsId),
          true
        );
      }
      window.gtag("js", new Date());
      window.gtag("config", consentConfig.googleAdsId);
      window.__r1GoogleAdsGtagLoaded = true;
    }

    configureGoogleAdsLabels(consentConfig);
  }

  function loadMetaPixel(consentConfig) {
    if (!consentConfig.facebookPixelId) return;
    !function (f, b, e, v, n, t, s) {
      if (f.fbq) return;
      n = f.fbq = function () { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments); };
      if (!f._fbq) f._fbq = n;
      n.push = n;
      n.loaded = true;
      n.version = "2.0";
      n.queue = [];
      t = b.createElement(e);
      t.async = true;
      t.src = v;
      s = b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t, s);
    }(window, document, "script", "https://connect.facebook.net/en_US/fbevents.js");
    fbq("init", consentConfig.facebookPixelId);
    fbq("track", "PageView");
  }

  function applyConsent(consentConfig, preferences) {
    updateGoogleConsent(preferences);

    if (preferences.analytics && !loadedCategories.analytics) {
      loadedCategories.analytics = true;
      loadClarity(consentConfig);
      loadGoogleTracking(consentConfig);
    }

    if (preferences.marketing && !loadedCategories.marketing) {
      loadedCategories.marketing = true;
      loadGoogleAdsGtag(consentConfig);
      loadMetaPixel(consentConfig);
    }
  }

  function getConsentConfig(banner) {
    return {
      clarityProjectId: banner.getAttribute("data-clarity-project-id") || "",
      gtmId: banner.getAttribute("data-gtm-id") || "",
      ga4Id: banner.getAttribute("data-ga4-id") || "",
      facebookPixelId: banner.getAttribute("data-facebook-pixel-id") || "",
      googleAdsId: banner.getAttribute("data-google-ads-id") || "",
      googleAdsPurchaseLabel: banner.getAttribute("data-google-ads-purchase-label") || "",
      googleAdsBookingLabel: banner.getAttribute("data-google-ads-booking-label") || ""
    };
  }

  function setConsent(preferences, banner, consentConfig) {
    localStorage.setItem(storageKey, JSON.stringify(preferences));
    if (banner) banner.style.display = "none";
    applyConsent(consentConfig, preferences);
  }

  function parseConsent(rawValue) {
    if (!rawValue) return null;

    if (rawValue === "accepted") {
      return { analytics: true, marketing: true };
    }
    if (rawValue === "rejected") {
      return { analytics: false, marketing: false };
    }

    try {
      var parsed = JSON.parse(rawValue);
      return {
        analytics: !!parsed.analytics,
        marketing: !!parsed.marketing
      };
    } catch (error) {
      return null;
    }
  }

  function openSettings(banner) {
    localStorage.removeItem(storageKey);
    if (banner) banner.style.display = "block";
  }

  document.addEventListener("DOMContentLoaded", function () {
    var banner = document.getElementById("cookie-consent-banner");
    if (!banner) return;

    var consentConfig = getConsentConfig(banner);
    var consent = parseConsent(localStorage.getItem(storageKey));
    var acceptBtn = document.getElementById("cookie-accept");
    var rejectBtn = document.getElementById("cookie-reject");
    var saveBtn = document.getElementById("cookie-save");
    var customizeBtn = document.getElementById("cookie-customize");
    var categoriesBlock = document.getElementById("cookie-categories");
    var analyticsCheckbox = document.getElementById("cookie-analytics");
    var marketingCheckbox = document.getElementById("cookie-marketing");

    if (consent) {
      if (analyticsCheckbox) analyticsCheckbox.checked = !!consent.analytics;
      if (marketingCheckbox) marketingCheckbox.checked = !!consent.marketing;
      applyConsent(consentConfig, consent);
    } else {
      banner.style.display = "block";
    }

    if (acceptBtn) {
      acceptBtn.addEventListener("click", function () {
        if (analyticsCheckbox) analyticsCheckbox.checked = true;
        if (marketingCheckbox) marketingCheckbox.checked = true;
        setConsent({ analytics: true, marketing: true }, banner, consentConfig);
      });
    }
    if (rejectBtn) {
      rejectBtn.addEventListener("click", function () {
        if (analyticsCheckbox) analyticsCheckbox.checked = false;
        if (marketingCheckbox) marketingCheckbox.checked = false;
        setConsent({ analytics: false, marketing: false }, banner, consentConfig);
      });
    }
    if (saveBtn) {
      saveBtn.addEventListener("click", function () {
        setConsent({
          analytics: analyticsCheckbox ? analyticsCheckbox.checked : false,
          marketing: marketingCheckbox ? marketingCheckbox.checked : false
        }, banner, consentConfig);
      });
    }
    if (customizeBtn && categoriesBlock) {
      customizeBtn.addEventListener("click", function () {
        categoriesBlock.classList.toggle("is-hidden");
      });
    }

    document.addEventListener("click", function (event) {
      var trigger = event.target.closest("[data-cookie-settings='open']");
      if (!trigger) return;
      event.preventDefault();
      openSettings(banner);
    });
  });
})();
