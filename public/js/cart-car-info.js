// Car info lookup for cart step "Dati" (vehicle details).
// Fills car brand/model by license plate using /api/car-info.
// Non-blocking: if API fails, user can continue manually.

(function () {
  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function normalizePlate(raw) {
    return String(raw || '')
      .trim()
      .toUpperCase()
      // remove any non-alphanumeric characters (spaces, dashes, etc.)
      .replace(/[^A-Z0-9]+/g, '');
  }

  function isValidPlate(vnr) {
    if (!vnr) return false;
    if (!/^[A-Z0-9]{2,8}$/.test(vnr)) return false;
    if (!/[A-Z]/.test(vnr)) return false;

    const mStd = vnr.match(/^([A-Z]{2})(\d{1,4})$/);
    if (mStd) {
      const n = parseInt(mStd[2], 10);
      return n >= 1 && n <= 9999;
    }

    const mTrailer = vnr.match(/^([A-Z])(\d{1,4})$/);
    if (mTrailer) {
      const n = parseInt(mTrailer[2], 10);
      return n >= 1 && n <= 9999;
    }

    return /^[A-Z]{2,8}$/.test(vnr) || /^[A-Z]+\d+$/.test(vnr) || /^\d+[A-Z]+$/.test(vnr);
  }

  function setValidity(el, valid) {
    if (!el) return;
    if (valid) {
      el.style.borderColor = '';
      return;
    }
    el.style.borderColor = '#e11d48';
    window.setTimeout(function () {
      el.style.borderColor = '';
    }, 1200);
  }

  function getCsrfToken() {
    return (
      qs('meta[name="csrf-token"]')?.getAttribute('content') ||
      qs('input[name="_token"]')?.value ||
      ''
    );
  }

  async function postCarInfo(apiUrl, token, vnr) {
    const csrf = getCsrfToken();
    const res = await fetch(apiUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'X-Requested-With': 'XMLHttpRequest',
        'X-Car-Info-Token': token,
        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
      },
      body: new URLSearchParams({ vnr }).toString(),
    });

    const nextToken = res.headers.get('X-Car-Info-Token') || '';
    const text = await res.text();
    let json = null;
    try {
      json = text ? JSON.parse(text) : null;
    } catch (_e) {
      json = null;
    }
    return { ok: res.ok, status: res.status, json, nextToken, rawText: text };
  }

  function valToText(v) {
    if (v === null || v === undefined) return '';
    if (Array.isArray(v)) {
      return v
        .map(valToText)
        .map(function (s) { return String(s || '').trim(); })
        .filter(Boolean)
        .join(', ');
    }
    if (typeof v === 'string') return v;
    if (typeof v === 'number') return Number.isFinite(v) ? String(v) : '';
    if (typeof v === 'boolean') return v ? '1' : '0';
    return '';
  }

  function formatReleaseYear(v) {
    if (v === null || v === undefined) return '';
    if (typeof v === 'number' && Number.isFinite(v)) return String(Math.trunc(v));
    const s = String(v).trim();
    const m = s.match(/\b(\d{4})\b/);
    return m ? m[1] : s;
  }

  function formatEngineSize(v) {
    if (v === null || v === undefined) return '';

    // If API returns a number (e.g. 3.0), JSON parsing will make it 3.
    // We intentionally format "liter-like" values with one decimal to keep "3.0".
    if (typeof v === 'number' && Number.isFinite(v)) {
      // Heuristic: values like 1968 are likely cm3, convert to liters.
      if (v >= 400 && v <= 30000) return (v / 1000).toFixed(1);
      if (v >= 0.4 && v <= 20) return v.toFixed(1);
      return String(v);
    }

    // Strings: keep as-is (do not strip digits). Normalize comma to dot for convenience.
    const s = String(v).trim();
    if (/^\d+,\d+$/.test(s)) return s.replace(',', '.');
    // Heuristic: plain integer like "1968" is likely cm3, convert to liters.
    if (/^\d{3,5}$/.test(s)) {
      const n = parseInt(s, 10);
      if (Number.isFinite(n) && n >= 400 && n <= 30000) return (n / 1000).toFixed(1);
    }
    return s;
  }

  function init() {
    const plateInput = qs('#car_reg_nr');
    const brandInput = qs('#brand');
    const modelInput = qs('#model');
    const releaseYearInput = qs('#car_release-year');
    const engineSizeInput = qs('#car_engine_size');
    const tokenInput = qs('input[name="car_info_token"]');
    const container = qs('[data-car-info-url]');

    if (!plateInput || !brandInput || !modelInput || !tokenInput || !container) {
      return;
    }

    const apiUrl = container.getAttribute('data-car-info-url') || '/api/car-info';
    let lastVnr = '';
    let inflight = null;

    async function lookup() {
      const vnr = normalizePlate(plateInput.value);
      if (!vnr) return;
      if (!isValidPlate(vnr)) {
        setValidity(plateInput, false);
        return;
      }
      setValidity(plateInput, true);

      if (vnr === lastVnr) return;
      lastVnr = vnr;

      if (inflight) {
        try { inflight.abort(); } catch (_e) {}
        inflight = null;
      }

      const ac = new AbortController();
      inflight = ac;

      const token = String(tokenInput.value || '').trim();
      if (!token) return;

      try {
        const { ok, json, nextToken } = await postCarInfo(apiUrl, token, vnr);
        if (nextToken) tokenInput.value = nextToken;
        if (!ok || !json || typeof json !== 'object') return;

        const marka = valToText(json.MARKA ?? json.marka ?? '');
        const modelis = valToText(json.MODELIS ?? json.modelis ?? '');
        const izlaidumaGads = formatReleaseYear(
          json.GADS ?? json.gads ?? json.IZLAIDUMA_GADS ?? json.izlaiduma_gads ?? ''
        );
        const dzinetaTilpums = formatEngineSize(
          json.TILPUMS ?? json.tilpums ?? json.DZINETA_TILPUMS ?? json.dzineta_tilpums ?? ''
        );
        if (marka) brandInput.value = marka;
        if (modelis) modelInput.value = modelis;
        if (izlaidumaGads && releaseYearInput) releaseYearInput.value = izlaidumaGads;
        if (dzinetaTilpums && engineSizeInput) engineSizeInput.value = dzinetaTilpums;
      } catch (_e) {
        // Non-blocking: ignore errors.
      } finally {
        inflight = null;
      }
    }

    plateInput.addEventListener('blur', lookup);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

