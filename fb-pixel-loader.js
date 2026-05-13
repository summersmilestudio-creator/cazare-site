/**
 * Summer Smile — Meta Pixel Loader
 *
 * Pune ÎNAINTE de acest script un <script> care setează:
 *   window.SS_FB_CONFIG = { pixelId: 'XXXXXXXXXXXXXXXX' };
 *
 * Sau define-ește în .htaccess / template-ul de site.
 *
 * Apoi acest fișier:
 *   1. Inițializează Pixel-ul (PageView automat)
 *   2. Emite evenimente automate în funcție de URL (ViewContent, InitiateCheckout)
 *   3. Expune SS.fbTrack(eventName, params) pentru evenimente custom
 *
 * Pentru Purchase event vezi rezervare-succes.html / rezervare-confirmare.php.
 */
(function () {
    'use strict';

    var cfg = window.SS_FB_CONFIG || {};
    var pixelId = cfg.pixelId;

    if (!pixelId || pixelId === 'XXXXXXXXXXXXXXXX' || pixelId.indexOf('PLACEHOLDER') !== -1) {
        if (window.console) console.warn('[FB Pixel] not configured — set window.SS_FB_CONFIG.pixelId');
        return;
    }

    // Standard Meta Pixel base code
    !function (f, b, e, v, n, t, s) {
        if (f.fbq) return;
        n = f.fbq = function () { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments) };
        if (!f._fbq) f._fbq = n;
        n.push = n;
        n.loaded = !0;
        n.version = '2.0';
        n.queue = [];
        t = b.createElement(e);
        t.async = !0;
        t.src = v;
        s = b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t, s);
    }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

    fbq('init', pixelId);
    fbq('track', 'PageView');

    // Helper public
    window.SS = window.SS || {};
    window.SS.fbTrack = function (eventName, params, opts) {
        try {
            if (window.fbq) {
                if (opts && opts.eventID) {
                    fbq('track', eventName, params || {}, { eventID: opts.eventID });
                } else {
                    fbq('track', eventName, params || {});
                }
            }
        } catch (e) {
            if (window.console) console.warn('[SS.fbTrack]', e);
        }
    };

    // Auto-track ViewContent / InitiateCheckout pe baza URL
    var path = location.pathname.toLowerCase();

    if (/(mamaia-nord|cazare|listings|odessei|calugareni|aer-by-meraky)/.test(path) && !/rezervare/.test(path)) {
        // Pagina de listare/proprietate — ViewContent
        var prop = (location.pathname.match(/[a-z\-]+(?=\.(html|php)$)/i) || ['cazare'])[0];
        window.SS.fbTrack('ViewContent', {
            content_type: 'product',
            content_category: 'cazare',
            content_name: prop,
            content_ids: [prop],
            currency: 'RON'
        });
    }

    if (/rezervare-(mamaia-nord|stripe|odessei|oradea|calugareni)/.test(path) && !/succes|confirmare/.test(path)) {
        // Pagina de checkout — InitiateCheckout
        var u = new URLSearchParams(location.search);
        window.SS.fbTrack('InitiateCheckout', {
            content_type: 'product',
            content_category: 'cazare',
            content_name: u.get('property') || 'mamaia-nord',
            currency: 'RON'
        });
    }
})();
