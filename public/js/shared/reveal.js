/**
 * Reveal-on-scroll عام:
 * 1) أي عنصر يحمل [data-reveal] (المعرّف بـ shared/base.css) ياخذ is-visible عند دخول الشاشة.
 * 2) تلقائي بكل الصفحات: البطاقات وفقرات المقالات اللي تحت الشاشة تصعد من تحت
 *    لفوق وأنت تنزل، بتتابع خفيف بين البطاقات المتجاورة. اللي ظاهر أول ما تفتح
 *    الصفحة ما يتخبّى (بدون وميض)، وبعد الحركة تنشال الكلاسات عشان ترجع حركات
 *    البطاقة الأصلية (hover) زي ما هي.
 */
(function () {
    'use strict';

    // بطاقة = كلاس اسمه card أو ينتهي بـ -card (news-card، home-updates-card...)، مو أجزاءها (__).
    var CARD_CLASS = /^([a-z0-9]+-)*card(--[a-z0-9-]+)?$/;
    // حاوية نص طويل: __content / -content / __body / __description / __text
    var PROSE_PARENT = /(__content|-content|__body|__description|__text)(--[a-z0-9-]+)?$/;
    var PROSE_TAGS = /^(P|H2|H3|H4|FIGURE|BLOCKQUOTE|UL|OL|TABLE|IMG|PRE|VIDEO)$/;
    var STAGGER_MS = 80;
    var STAGGER_MAX = 5;

    function hasClass(el, pattern) {
        for (var i = 0; i < el.classList.length; i++) {
            if (pattern.test(el.classList[i])) {
                return true;
            }
        }
        return false;
    }

    function initDataReveal() {
        var items = document.querySelectorAll('[data-reveal]');

        if (!items.length) {
            return;
        }

        if (!('IntersectionObserver' in window)) {
            items.forEach(function (el) { el.classList.add('is-visible'); });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            var shown = 0;

            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                var el = entry.target;
                var delay = Math.min(shown, STAGGER_MAX) * STAGGER_MS;
                shown++;

                observer.unobserve(el);
                if (delay) {
                    // تتابع بين البطاقات اللي تدخل الشاشة مع بعض، وبعدها يرجع التأخير صفر (hover).
                    el.style.transitionDelay = delay + 'ms';
                    setTimeout(function () { el.style.removeProperty('transition-delay'); }, 800 + delay);
                }
                el.classList.add('is-visible');
            });
        }, { threshold: 0.12 });

        items.forEach(function (el) { observer.observe(el); });
    }

    function autoTargets(main) {
        var found = [];

        main.querySelectorAll('[class*="card"]').forEach(function (el) {
            if (hasClass(el, CARD_CLASS)) {
                found.push(el);
            }
        });

        main.querySelectorAll('[class*="content"] > *, [class*="__body"] > *, [class*="__description"] > *, [class*="__text"] > *')
            .forEach(function (el) {
                if (PROSE_TAGS.test(el.tagName) && hasClass(el.parentElement, PROSE_PARENT)) {
                    found.push(el);
                }
            });

        var set = new Set(found);

        return found.filter(function (el) {
            // اللي عليه [data-reveal] أو داخل واحد يتحرك أصلًا؛ وما نحرّك عنصر داخل عنصر يتحرك.
            if (el.closest('[data-reveal]')) {
                return false;
            }
            for (var p = el.parentElement; p && p !== main; p = p.parentElement) {
                if (set.has(p)) {
                    return false;
                }
            }
            return el.offsetParent !== null;
        });
    }

    function initAuto() {
        var main = document.querySelector('main');

        if (!main
            || !('IntersectionObserver' in window)
            || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        var fold = window.innerHeight;
        var targets = autoTargets(main).filter(function (el) {
            // الظاهر من البداية يبقى ثابت؛ الحركة للي تحت الشاشة بس.
            return el.getBoundingClientRect().top > fold * 0.92;
        });

        if (!targets.length) {
            return;
        }

        targets.forEach(function (el) {
            el.classList.add('reveal-auto', 'reveal-auto--hidden');
        });

        function finish(el) {
            el.classList.remove('reveal-auto');
            el.style.removeProperty('transition-delay');
        }

        var observer = new IntersectionObserver(function (entries) {
            var shown = 0;

            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                var el = entry.target;
                var delay = Math.min(shown, STAGGER_MAX) * STAGGER_MS;
                shown++;

                observer.unobserve(el);
                el.style.transitionDelay = delay + 'ms';
                el.classList.remove('reveal-auto--hidden');

                // بعد ما تخلص الحركة نرجّع البطاقة لأسلوبها الأصلي.
                setTimeout(function () { finish(el); }, 750 + delay);
            });
        }, { threshold: 0.06, rootMargin: '0px 0px -6% 0px' });

        targets.forEach(function (el) { observer.observe(el); });
    }

    function init() {
        initDataReveal();
        initAuto();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
