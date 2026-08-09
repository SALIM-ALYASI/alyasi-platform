/**
 * سلوك الهيدر المشترك: ظل/خلفية عند التمرير + قائمة الجوال.
 */
(function () {
    'use strict';

    function init() {
        var header = document.querySelector('[data-site-header]');

        if (header) {
            var onScroll = function () {
                if (window.scrollY > 24) {
                    header.classList.add('is-scrolled');
                } else {
                    header.classList.remove('is-scrolled');
                }
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        }

        var toggle = document.querySelector('[data-menu-toggle]');
        var mobileMenu = document.querySelector('[data-mobile-menu]');

        if (!toggle || !mobileMenu) {
            return;
        }

        var isOpen = false;

        var closeMenu = function () {
            if (!isOpen) return;
            isOpen = false;
            toggle.classList.remove('is-active');
            toggle.setAttribute('aria-expanded', 'false');
            mobileMenu.classList.remove('is-open');
            mobileMenu.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('has-mobile-menu-open');
        };

        var openMenu = function () {
            isOpen = true;
            toggle.classList.add('is-active');
            toggle.setAttribute('aria-expanded', 'true');
            mobileMenu.classList.add('is-open');
            mobileMenu.setAttribute('aria-hidden', 'false');
            document.body.classList.add('has-mobile-menu-open');
        };

        toggle.addEventListener('click', function () {
            isOpen ? closeMenu() : openMenu();
        });

        mobileMenu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeMenu);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeMenu();
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 900) closeMenu();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
