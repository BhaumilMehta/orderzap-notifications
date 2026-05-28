/**
 * OrderZap Notifications – Admin JS Entry
 *
 * Development loader: pulls React, ReactDOM, Babel from CDN and
 * transpiles admin.jsx on the fly.
 *
 * PRODUCTION: Replace with a compiled Vite/Webpack bundle.
 */

(function () {
    'use strict';

    // Tailwind CSS (dev only)
    if (!document.querySelector('script[src*="tailwindcss"]')) {
        var tw = document.createElement('script');
        tw.src = 'https://cdn.tailwindcss.com';
        tw.onload = function () {
            if (window.tailwind) {
                tailwind.config = { darkMode: 'class' };
            }
        };
        document.head.appendChild(tw);
    }

    function loadScript(src, cb) {
        var s = document.createElement('script');
        s.src = src;
        s.crossOrigin = 'anonymous';
        s.onload = cb;
        document.head.appendChild(s);
    }

    if (typeof React === 'undefined') {
        loadScript('https://unpkg.com/react@18/umd/react.development.js', function () {
            loadScript('https://unpkg.com/react-dom@18/umd/react-dom.development.js', loadBabelAndApp);
        });
    } else {
        loadBabelAndApp();
    }

    function loadBabelAndApp() {
        if (typeof Babel === 'undefined') {
            loadScript('https://unpkg.com/@babel/standalone/babel.min.js', loadApp);
        } else {
            loadApp();
        }
    }

    function loadApp() {
        var base = window.wcWan
            ? window.wcWan.adminUrl.replace('admin.php', '').replace(/\/+$/, '')
            : '';
        var jsxUrl = base + '/../wp-content/plugins/orderzap-notifications/assets/js/admin.jsx';

        fetch(jsxUrl)
            .then(function (r) { return r.text(); })
            .then(function (jsx) {
                var code   = Babel.transform(jsx, { presets: ['react'] }).code;
                var script = document.createElement('script');
                script.textContent = code;
                document.body.appendChild(script);
            })
            .catch(function (err) {
                console.error('[WC_WAN] Failed to load admin app:', err);
                var el = document.getElementById('wc-wan-app');
                if (el) {
                    el.innerHTML = '<div style="padding:20px;color:#c00;">Failed to load WC WhatsApp Order Notification admin UI. Check the browser console.</div>';
                }
            });
    }
})();
