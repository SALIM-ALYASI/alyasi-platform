/* ALYASI shared color-theme controller */
(function () {
    'use strict';

    var STORAGE_KEY = 'alyasi-theme';
    var root = document.documentElement;
    var systemDark = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

    function storedTheme() {
        try {
            var value = localStorage.getItem(STORAGE_KEY);
            return value === 'dark' || value === 'light' ? value : null;
        } catch (error) {
            return null;
        }
    }

    function preferredTheme() {
        return storedTheme() || (systemDark && systemDark.matches ? 'dark' : 'light');
    }

    function setTheme(theme, persist) {
        var next = theme === 'dark' ? 'dark' : 'light';
        root.dataset.theme = next;
        root.style.colorScheme = next;

        if (persist) {
            try {
                localStorage.setItem(STORAGE_KEY, next);
            } catch (error) {
                // localStorage can be unavailable in strict/private contexts.
            }
        }

        var metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            metaThemeColor.setAttribute('content', next === 'dark' ? '#07111F' : '#0B1F3A');
        }

        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            var dark = next === 'dark';
            var icon = button.querySelector('[data-theme-icon]');
            var label = dark
                ? (button.dataset.themeLightLabel || 'Light mode')
                : (button.dataset.themeDarkLabel || 'Dark mode');

            button.setAttribute('aria-pressed', dark ? 'true' : 'false');
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);

            if (icon) {
                icon.classList.toggle('fa-moon', !dark);
                icon.classList.toggle('fa-sun', dark);
            }
        });

        window.dispatchEvent(new CustomEvent('alyasi:themechange', {
            detail: { theme: next }
        }));
    }

    function toggleTheme() {
        setTheme(root.dataset.theme === 'dark' ? 'light' : 'dark', true);
    }

    function init() {
        setTheme(root.dataset.theme || preferredTheme(), false);

        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            button.addEventListener('click', toggleTheme);
        });

        if (systemDark) {
            var handleSystemChange = function (event) {
                if (!storedTheme()) {
                    setTheme(event.matches ? 'dark' : 'light', false);
                }
            };

            if (typeof systemDark.addEventListener === 'function') {
                systemDark.addEventListener('change', handleSystemChange);
            } else if (typeof systemDark.addListener === 'function') {
                systemDark.addListener(handleSystemChange);
            }
        }

        window.addEventListener('storage', function (event) {
            if (event.key === STORAGE_KEY) {
                setTheme(preferredTheme(), false);
            }
        });

        window.requestAnimationFrame(function () {
            root.classList.add('theme-ready');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
