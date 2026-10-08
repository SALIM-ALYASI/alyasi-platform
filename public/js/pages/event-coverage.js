/**
 * تغطية «خمس محطات»: مودال التفاصيل بشاشة كاملة، مع تنقّل سابق/تالي،
 * وقفل الصفحة الخلفية، والرجوع لنفس موضع القارئ عند الإغلاق.
 */
(function () {
    var dataEl = document.getElementById('coverageData');
    var modal = document.getElementById('coverageModal');
    if (!dataEl || !modal) return;

    // <main> بالمنصة عليه transform (انتقال الصفحات)، وهذا يخلي position: fixed
    // نسبة له بدل الشاشة -- فننقل المودال لـ body عشان يغطي الشاشة فعلًا.
    document.body.appendChild(modal);

    var data = JSON.parse(dataEl.textContent);
    var stops = data.stops;
    var labels = data.labels;
    var $ = function (id) { return document.getElementById(id); };

    var current = -1;
    var savedY = 0;
    var isOpen = false;
    var opener = null;

    function renderArticle(blocks) {
        var article = $('coverageModalArticle');
        article.innerHTML = '';
        var list = null;

        blocks.forEach(function (block) {
            if (block.t === 'li' || block.t === 'oli') {
                var tag = block.t === 'li' ? 'UL' : 'OL';
                if (!list || list.tagName !== tag) {
                    list = document.createElement(tag);
                    article.appendChild(list);
                }
                var li = document.createElement('li');
                li.textContent = block.x;
                list.appendChild(li);
                return;
            }

            list = null;
            var node = document.createElement(block.t === 'note' ? 'p' : block.t);
            if (block.t === 'note') node.className = 'note';
            node.textContent = block.x;
            article.appendChild(node);
        });
    }

    function render(index) {
        current = index;
        var stop = stops[index];

        $('coverageModalCount').textContent = (index + 1) + ' ' + labels.of + ' ' + stops.length;
        $('coverageModalKicker').textContent = labels.ordinals[index] || '';
        $('coverageModalPhotoBox').classList.toggle('stop-card__photo--group', !!stop.group);
        $('coverageModalPhoto').src = stop.image;
        $('coverageModalPhoto').alt = stop.name;
        $('coverageModalName').textContent = stop.name;
        $('coverageModalRole').textContent = stop.role;
        $('coverageModalTitle').textContent = stop.headline;
        $('coverageModalSub').hidden = !stop.sub;
        $('coverageModalSub').textContent = stop.sub || '';
        renderArticle(stop.blocks);

        Array.prototype.forEach.call($('coverageModalProgress').children, function (bar, i) {
            bar.classList.toggle('is-done', i <= index);
        });

        var prev = stops[index - 1];
        var next = stops[index + 1];
        $('coverageModalPrev').disabled = !prev;
        $('coverageModalNext').disabled = !next;
        $('coverageModalPrev').querySelector('span').textContent = prev ? prev.name : '';
        $('coverageModalNext').querySelector('span').textContent = next ? next.name : '';

        modal.scrollTop = 0;
    }

    function lockPage() {
        savedY = window.scrollY;
        Object.assign(document.body.style, { position: 'fixed', top: '-' + savedY + 'px', left: '0', right: '0', width: '100%' });
    }

    function unlockPage() {
        Object.assign(document.body.style, { position: '', top: '', left: '', right: '', width: '' });
        // instant لأن الموقع مفعّل scroll-behavior: smooth، والرجوع المتحرك
        // يوقف بمكان غلط بعد فك تثبيت الصفحة.
        var restore = function () { window.scrollTo({ top: savedY, left: 0, behavior: 'instant' }); };
        restore();
        requestAnimationFrame(restore);
    }

    function open(index, trigger) {
        if (isOpen) { render(index); return; }
        isOpen = true;
        opener = trigger || null;
        lockPage();
        render(index);
        modal.hidden = false;
        requestAnimationFrame(function () { modal.classList.add('is-visible'); });
        // زر الرجوع / سحبة الرجوع بالجوال تقفل المودال بدل ما تطلع من الصفحة.
        history.pushState({ coverageModal: true }, '');
        $('coverageModalClose').focus({ preventScroll: true });
    }

    function close(fromHistory) {
        if (!isOpen) return;
        // الإغلاق الفعلي يصير مرة وحدة من popstate عشان المتصفح ما يحرّك الصفحة بعدنا.
        if (!fromHistory && history.state && history.state.coverageModal) {
            history.back();
            return;
        }
        isOpen = false;
        modal.classList.remove('is-visible');
        modal.hidden = true;
        unlockPage();
        if (opener) opener.focus({ preventScroll: true });
    }

    if ('scrollRestoration' in history) history.scrollRestoration = 'manual';

    document.querySelectorAll('[data-stop]').forEach(function (btn) {
        btn.addEventListener('click', function () { open(Number(btn.dataset.stop), btn); });
    });

    $('coverageModalClose').addEventListener('click', function () { close(false); });
    $('coverageModalPrev').addEventListener('click', function () { if (current > 0) render(current - 1); });
    $('coverageModalNext').addEventListener('click', function () { if (current < stops.length - 1) render(current + 1); });
    window.addEventListener('popstate', function () { close(true); });

    document.addEventListener('keydown', function (e) {
        if (!isOpen) return;
        if (e.key === 'Escape') close(false);
        // الأسهم تتبع اتجاه الصفحة: بالعربي اليسار = التالي.
        var rtl = document.documentElement.dir === 'rtl' || document.body.dir === 'rtl';
        if (e.key === (rtl ? 'ArrowLeft' : 'ArrowRight') && current < stops.length - 1) render(current + 1);
        if (e.key === (rtl ? 'ArrowRight' : 'ArrowLeft') && current > 0) render(current - 1);
    });

    // معرض صور الشكر: عرض الصورة بشاشة كاملة مع تنقّل بالأسهم والسحب.
    var lightbox = $('coverageLightbox');
    var photos = data.photos || [];
    var photoIndex = 0;
    var photoOpener = null;

    function showPhoto(index) {
        photoIndex = (index + photos.length) % photos.length;
        $('coverageLightboxImg').src = photos[photoIndex];
        $('coverageLightboxCount').textContent = photos.length > 1 ? (photoIndex + 1) + ' ' + labels.of + ' ' + photos.length : '';
        $('coverageLightboxPrev').hidden = photos.length < 2;
        $('coverageLightboxNext').hidden = photos.length < 2;
    }

    function closePhoto() {
        if (lightbox.hidden) return;
        lightbox.hidden = true;
        unlockPage();
        if (photoOpener) photoOpener.focus({ preventScroll: true });
    }

    if (lightbox && photos.length) {
        document.body.appendChild(lightbox);

        document.querySelectorAll('[data-photo]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                photoOpener = btn;
                lockPage();
                showPhoto(Number(btn.dataset.photo));
                lightbox.hidden = false;
                $('coverageLightboxClose').focus({ preventScroll: true });
            });
        });

        $('coverageLightboxClose').addEventListener('click', closePhoto);
        $('coverageLightboxPrev').addEventListener('click', function () { showPhoto(photoIndex - 1); });
        $('coverageLightboxNext').addEventListener('click', function () { showPhoto(photoIndex + 1); });
        // الضغط على الخلفية (خارج الصورة والأزرار) يقفل العارض.
        lightbox.addEventListener('click', function (e) { if (e.target === lightbox) closePhoto(); });

        document.addEventListener('keydown', function (e) {
            if (lightbox.hidden) return;
            var rtl = document.documentElement.dir === 'rtl' || document.body.dir === 'rtl';
            if (e.key === 'Escape') closePhoto();
            if (e.key === (rtl ? 'ArrowLeft' : 'ArrowRight')) showPhoto(photoIndex + 1);
            if (e.key === (rtl ? 'ArrowRight' : 'ArrowLeft')) showPhoto(photoIndex - 1);
        });

        var touchX = null;
        lightbox.addEventListener('touchstart', function (e) { touchX = e.touches[0].clientX; }, { passive: true });
        lightbox.addEventListener('touchend', function (e) {
            if (touchX === null || photos.length < 2) return;
            var dx = e.changedTouches[0].clientX - touchX;
            touchX = null;
            if (Math.abs(dx) < 50) return;
            var rtl = document.documentElement.dir === 'rtl' || document.body.dir === 'rtl';
            // بالعربي السحب لليمين = التالية، وبالإنجليزي السحب لليسار.
            showPhoto(photoIndex + ((dx > 0) === rtl ? 1 : -1));
        });
    }

    // شريط صور الشكر: يتحرك تلقائيًا يمين/يسار، ويوقف مع اللمس/المرور
    // أو لما يكون خارج الشاشة، ويرجع للبداية بعد آخر صورة.
    var gallery = document.querySelector('.coverage-gallery');
    var track = $('coverageGalleryTrack');
    if (gallery && track && photos.length > 1) {
        var slide = 0;
        var timer = null;
        var inView = false;
        var hovering = false;
        var dotsBox = $('coverageGalleryDots');
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var isRtl = function () { return document.documentElement.dir === 'rtl' || document.body.dir === 'rtl'; };
        var perView = function () { return parseInt(getComputedStyle(gallery).getPropertyValue('--per-view'), 10) || 1; };
        var lastSlide = function () { return Math.max(0, photos.length - perView()); };

        function buildDots() {
            dotsBox.innerHTML = '';
            for (var i = 0; i <= lastSlide(); i++) dotsBox.appendChild(document.createElement('span'));
        }

        function goTo(index) {
            var last = lastSlide();
            slide = index > last ? 0 : (index < 0 ? last : index);
            // بالعربي الصور مرتبة من اليمين، فالتالية تدخل من اليسار.
            var shift = slide * (100 / perView());
            track.style.transform = 'translateX(' + (isRtl() ? shift : -shift) + '%)';
            Array.prototype.forEach.call(dotsBox.children, function (dot, i) {
                dot.classList.toggle('is-active', i === slide);
            });
        }

        function schedule() {
            clearInterval(timer);
            timer = null;
            if (reduceMotion || !inView || hovering || !lightbox.hidden) return;
            timer = setInterval(function () { goTo(slide + 1); }, 3500);
        }

        buildDots();
        goTo(0);

        $('coverageGalleryPrev').addEventListener('click', function () { goTo(slide - 1); schedule(); });
        $('coverageGalleryNext').addEventListener('click', function () { goTo(slide + 1); schedule(); });
        gallery.addEventListener('mouseenter', function () { hovering = true; schedule(); });
        gallery.addEventListener('mouseleave', function () { hovering = false; schedule(); });

        // السحب بالجوال: نلغي الضغطة لو كانت سحبة عشان ما يفتح العارض.
        var startX = null;
        var swiped = false;
        track.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; swiped = false; }, { passive: true });
        track.addEventListener('touchend', function (e) {
            if (startX === null) return;
            var dx = e.changedTouches[0].clientX - startX;
            startX = null;
            if (Math.abs(dx) < 40) return;
            swiped = true;
            goTo(slide + ((dx > 0) === isRtl() ? 1 : -1));
            schedule();
        });
        track.addEventListener('click', function (e) {
            if (swiped) { e.stopPropagation(); e.preventDefault(); swiped = false; }
        }, true);

        window.addEventListener('resize', function () { buildDots(); goTo(Math.min(slide, lastSlide())); });

        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function (entries) {
                inView = entries[0].isIntersecting;
                schedule();
            }, { threshold: 0.4 }).observe(gallery);
        } else {
            inView = true;
            schedule();
        }

        // يوقف الشريط وقت فتح العارض، ويكمل بعد إغلاقه.
        new MutationObserver(schedule).observe(lightbox, { attributes: true, attributeFilter: ['hidden'] });
    }

    // فيديو الملخص: نحمّل مشغّل يوتيوب فقط لما يضغط القارئ تشغيل.
    var video = $('coverageVideo');
    if (video) {
        video.querySelector('.coverage-video__poster').addEventListener('click', function () {
            var iframe = document.createElement('iframe');
            iframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(video.dataset.videoId) + '?autoplay=1&rel=0&playsinline=1';
            iframe.title = video.dataset.videoTitle;
            iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
            iframe.allowFullscreen = true;
            iframe.referrerPolicy = 'strict-origin-when-cross-origin';
            video.innerHTML = '';
            video.appendChild(iframe);
        });
    }

    // مشاركة التغطية: مشاركة الجوال الأصلية، وإلا نسخ الرابط.
    var share = $('coverageShare');
    if (share) {
        share.addEventListener('click', function () {
            var url = window.location.href.split('#')[0];
            if (navigator.share) {
                navigator.share({ title: share.dataset.title, url: url }).catch(function () {});
                return;
            }
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function () {
                    var original = share.textContent;
                    share.textContent = share.dataset.copied;
                    setTimeout(function () { share.textContent = original; }, 1800);
                });
            }
        });
    }
})();
