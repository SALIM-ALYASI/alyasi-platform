(function () {
    'use strict';

    function animateCounters() {
        var counters = document.querySelectorAll('[data-count-target]');

        counters.forEach(function (el) {
            var target = parseInt(el.getAttribute('data-count-target'), 10) || 0;
            var start = performance.now();
            var duration = 1100;

            function tick(now) {
                var progress = Math.min(1, (now - start) / duration);
                var eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.round(target * eased);
                if (progress < 1) {
                    requestAnimationFrame(tick);
                }
            }

            requestAnimationFrame(tick);
        });
    }

    function initCounters() {
        var hero = document.querySelector('.home-hero');
        if (!hero || !('IntersectionObserver' in window)) {
            animateCounters();
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.disconnect();
                }
            });
        }, { threshold: 0.2 });

        observer.observe(hero);
    }

    function initFaq() {
        var items = document.querySelectorAll('.faq-item');

        items.forEach(function (item) {
            var button = item.querySelector('.faq-item__question');
            if (!button) return;

            button.addEventListener('click', function () {
                var isOpen = item.classList.contains('is-open');

                items.forEach(function (other) {
                    other.classList.remove('is-open');
                    var otherButton = other.querySelector('.faq-item__question');
                    if (otherButton) otherButton.setAttribute('aria-expanded', 'false');
                });

                if (!isOpen) {
                    item.classList.add('is-open');
                    button.setAttribute('aria-expanded', 'true');
                }
            });
        });
    }

    function init() {
        initCounters();
        initFaq();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
