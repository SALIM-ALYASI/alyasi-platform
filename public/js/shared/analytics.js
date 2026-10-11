/**
 * قياس التفاعل بدون أي مكتبة خارجية:
 *  - reached_end: الزائر وصل لآخر نص المقال/الخبر (تمرير -- مب دليل قراءة)،
 *    مع الثواني اللي كانت فيها الصفحة ظاهرة قبل الوصول.
 *  - audio_play / audio_complete: أول تشغيل وإكمال لكل ملف صوت بالصفحة،
 *    بما فيها مشغّلات new Audio() اللي ما تنضاف للصفحة.
 * كل حدث ينرسل مرة وحدة لكل صفحة.
 */
(function () {
    var endpoint = '/analytics/event';
    var sent = {};
    // الوقت والصفحة ظاهرة (نطرح وقت التبويب بالخلفية).
    var hiddenMs = 0;
    var hiddenSince = document.hidden ? performance.now() : null;

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            hiddenSince = performance.now();
        } else if (hiddenSince !== null) {
            hiddenMs += performance.now() - hiddenSince;
            hiddenSince = null;
        }
    });

    function activeSeconds() {
        var now = performance.now();
        // لو الصفحة ظاهرة الحين بس فات حدث الظهور (بعض المتصفحات المضمّنة)، نحسبها ظاهرة.
        var openHidden = hiddenSince !== null && document.hidden ? now - hiddenSince : 0;
        return Math.max(0, Math.round((now - hiddenMs - openHidden) / 1000));
    }

    function send(type, label) {
        var key = type + '|' + (label || '');
        if (sent[key]) return;
        sent[key] = true;

        var body = new FormData();
        body.append('type', type);
        body.append('path', location.pathname);
        if (label) body.append('label', label);
        body.append('seconds', String(activeSeconds()));

        if (navigator.sendBeacon && navigator.sendBeacon(endpoint, body)) return;
        fetch(endpoint, { method: 'POST', body: body, keepalive: true, credentials: 'same-origin' }).catch(function () {});
    }

    // نهاية النص: علامة صغيرة بعد آخر فقرة بالمقال أو الخبر أو حلقة تاريخ التقنية.
    var content = document.querySelector('.articles-detail__content, .news-detail__content');
    // فحص بالتمرير بدل IntersectionObserver: النزول السريع لآخر الصفحة يتخطى
    // العلامة بدون ما «تتقاطع» مع الشاشة، فالـ observer ما ينطلق أبدًا.
    if (content) {
        var marker = document.createElement('div');
        marker.setAttribute('aria-hidden', 'true');
        marker.style.height = '1px';
        content.appendChild(marker);

        var checkEnd = function () {
            if (marker.getBoundingClientRect().top <= window.innerHeight) {
                send('reached_end');
                window.removeEventListener('scroll', checkEnd);
            }
        };
        window.addEventListener('scroll', checkEnd, { passive: true });
    }

    // الصوت: نلف play() عشان نلقط حتى مشغّلات new Audio() غير المضافة للصفحة.
    var watched = typeof WeakSet === 'function' ? new WeakSet() : null;
    var originalPlay = HTMLMediaElement.prototype.play;

    function watch(media) {
        if (!watched || watched.has(media)) return;
        watched.add(media);

        media.addEventListener('playing', function () {
            send('audio_play', media.currentSrc || media.src);
        });
        media.addEventListener('ended', function () {
            send('audio_complete', media.currentSrc || media.src);
        });
    }

    HTMLMediaElement.prototype.play = function () {
        watch(this);
        return originalPlay.apply(this, arguments);
    };

    document.querySelectorAll('audio, video').forEach(watch);
})();
