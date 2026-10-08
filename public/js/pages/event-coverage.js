/**
 * تغطية «خمس محطات»: مودال التفاصيل بشاشة كاملة، مع تنقّل سابق/تالي،
 * وقفل الصفحة الخلفية، والرجوع لنفس موضع القارئ عند الإغلاق.
 */
(function () {
    var dataEl = document.getElementById('coverageData');
    var modal = document.getElementById('coverageModal');
    if (!dataEl || !modal) return;

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
        window.scrollTo(0, savedY);
        requestAnimationFrame(function () { window.scrollTo(0, savedY); });
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
