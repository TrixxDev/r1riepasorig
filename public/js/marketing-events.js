/**
 * Marketing helpers (Meta Pixel, GTM dataLayer, optional Google Ads gtag).
 * Respects r1_cookie_consent_v1 (same keys as cookie-consent.js).
 */
(function () {
  function preferences() {
    try {
      var raw = localStorage.getItem("r1_cookie_consent_v1");
      if (raw === "accepted") return { analytics: true, marketing: true };
      if (raw === "rejected") return { analytics: false, marketing: false };
      if (raw) return JSON.parse(raw);
    } catch (e) {}
    return { analytics: false, marketing: false };
  }

  window.r1MarketingPreferences = preferences;

  function ensureGoogleAdsSendToObjects() {
    if (typeof window.r1ApplyGoogleAdsLabels === "function") {
      var banner = document.getElementById("cookie-consent-banner");
      if (banner) {
        window.r1ApplyGoogleAdsLabels({
          googleAdsId: banner.getAttribute("data-google-ads-id") || "",
          googleAdsPurchaseLabel: banner.getAttribute("data-google-ads-purchase-label") || "",
          googleAdsBookingLabel: banner.getAttribute("data-google-ads-booking-label") || ""
        });
        return;
      }
      window.r1ApplyGoogleAdsLabels();
    }
  }

  function googleAdsBookingSendTo() {
    ensureGoogleAdsSendToObjects();
    var booking = window.R1_GOOGLE_ADS_BOOKING;
    return booking && booking.send_to ? booking.send_to : null;
  }

  function googleAdsPurchaseSendTo() {
    ensureGoogleAdsSendToObjects();
    var purchase = window.R1_GOOGLE_ADS_PURCHASE;
    return purchase && purchase.send_to ? purchase.send_to : null;
  }

  /**
   * E-pieraksts: successful slot booking (AJAX success, no thank-you URL).
   * @param {string} [serverEventId] — event_id from server for Meta CAPI deduplication
   */
  window.r1TrackBookingConversion = function (serverEventId) {
    var prefs = preferences();
    var eventId = serverEventId ||
      "booking_" + Date.now() + "_" + Math.random().toString(36).slice(2, 10);
    if (prefs.marketing && typeof fbq === "function") {
      fbq("track", "Schedule", { content_name: "e_pieraksts" }, { eventID: eventId });
    }
    if (prefs.analytics) {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({ event: "booking_complete", booking_event_id: eventId });
    }
    var bookingSendTo = googleAdsBookingSendTo();
    if (prefs.marketing && window.gtag && bookingSendTo) {
      window.gtag("event", "conversion", {
        send_to: bookingSendTo,
        transaction_id: eventId,
      });
    }
  };

  /**
   * shop/done: order purchase (browser duplicate for CAPI deduplication via event_id).
   * @param {object} p — { event_id, value, currency, contents, transaction_id, enhanced_conversion_email_sha256 }
   */
  window.r1TrackPurchasePage = function (p) {
    if (!p || !p.event_id) return;
    var prefs = preferences();
    if (prefs.marketing && typeof fbq === "function") {
      fbq(
        "track",
        "Purchase",
        {
          value: p.value,
          currency: p.currency,
          contents: p.contents,
          content_type: "product",
        },
        { eventID: p.event_id }
      );
    }
    if (prefs.analytics) {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({ ecommerce: null });
      var items = (p.contents || []).map(function (c) {
        return {
          item_id: String(c.id),
          price: c.item_price,
          quantity: c.quantity,
        };
      });
      window.dataLayer.push({
        event: "purchase",
        ecommerce: {
          transaction_id: p.transaction_id,
          value: p.value,
          currency: p.currency,
          items: items,
        },
      });
      if (p.enhanced_conversion_email_sha256) {
        window.dataLayer.push({
          event: "purchase_user_data",
          user_data: {
            sha256_email_address: p.enhanced_conversion_email_sha256,
          },
        });
      }
    }
    var purchaseSendTo = googleAdsPurchaseSendTo() || p.google_ads_send_to || null;
    if (prefs.marketing && window.gtag && purchaseSendTo) {
      var conv = {
        send_to: purchaseSendTo,
        value: p.value,
        currency: p.currency,
        transaction_id: p.transaction_id,
      };
      if (p.enhanced_conversion_email_sha256) {
        conv.user_data = {
          sha256_email_address: p.enhanced_conversion_email_sha256,
        };
      }
      window.gtag("event", "conversion", conv);
    }
  };
})();
