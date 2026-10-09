/**
 * صفحة المقابلة: مشغّل صوت واحد للأسئلة والإجابات، يظلّل الجملة اللي تنقال
 * الحين، والضغط على أي جملة يقفز لها. «استمع للمقابلة كاملة» يشغّل السؤال
 * ثم الإجابة بالترتيب حتى النهاية.
 */
(function () {
    var dataEl = document.getElementById('interviewData');
    if (!dataEl) return;

    var data = JSON.parse(dataEl.textContent);
    var items = data.items;
    var audio = new Audio();
    audio.preload = 'none';

    var dock = document.getElementById('interviewDock');
    var dockLabel = document.getElementById('interviewDockLabel');
    var dockFill = document.getElementById('interviewDockFill');
    var playAll = document.getElementById('interviewPlayAll');
    // شريط التشغيل لازم يكون على body مباشرة عشان position: fixed يكون نسبة للشاشة.
    document.body.appendChild(dock);

    var current = null;   // { item, kind: 'q' | 'a' }
    var queueMode = false;
    var activeSeg = null;

    function fmt(sec) {
        sec = Math.max(0, Math.round(sec || 0));
        return Math.floor(sec / 60) + ':' + String(sec % 60).padStart(2, '0');
    }

    function same(a, b) { return a && b && a.item === b.item && a.kind === b.kind; }

    function setSegment(itemIndex, segIndex) {
        if (activeSeg) activeSeg.classList.remove('is-active');
        activeSeg = segIndex === null ? null
            : document.querySelector('.qa__seg[data-item="' + itemIndex + '"][data-seg="' + segIndex + '"]');
        if (activeSeg) activeSeg.classList.add('is-active');
    }

    function render() {
        var playing = !audio.paused;

        document.querySelectorAll('[data-play]').forEach(function (btn) {
            var mine = same(current, { item: Number(btn.dataset.item), kind: btn.dataset.play });
            btn.classList.toggle('is-playing', mine && playing);
            var icon = btn.querySelector('.qa__play-icon, .qa__mini-icon');
            if (icon) icon.textContent = mine && playing ? '❚❚' : '▶';
        });

        document.querySelectorAll('.qa').forEach(function (li, i) {
            li.classList.toggle('is-current', !!current && current.item === i);
        });

        playAll.classList.toggle('is-playing', queueMode && playing);
        playAll.querySelector('[data-label-play]').hidden = queueMode && playing;
        playAll.querySelector('[data-label-pause]').hidden = !(queueMode && playing);
        playAll.querySelector('.interview__play-all-icon').textContent = queueMode && playing ? '❚❚' : '▶';

        dock.hidden = !current;
        if (current) {
            document.getElementById('interviewDockToggle').textContent = playing ? '❚❚' : '▶';
            dockLabel.textContent = (current.kind === 'q' ? data.labels.question + ' ' + (current.item + 1) + ' · ' + data.labels.host : data.labels.answer + ' · ' + (current.item + 1));
        }
    }

    function load(track, seek) {
        current = track;
        var src = track.kind === 'q' ? items[track.item].q : items[track.item].a;
        if (audio.getAttribute('src') !== src) {
            audio.src = src;
        }
        if (typeof seek === 'number') {
            var apply = function () { audio.currentTime = seek; };
            audio.readyState >= 1 ? apply() : audio.addEventListener('loadedmetadata', apply, { once: true });
        }
        if (track.kind === 'q') setSegment(null, null);
        audio.play().catch(function () {});
        render();
    }

    function toggle(track, seek) {
        if (same(current, track) && typeof seek !== 'number') {
            audio.paused ? audio.play() : audio.pause();
            render();
            return;
        }
        load(track, seek);
    }

    function nextInQueue() {
        if (!current) return { item: 0, kind: 'q' };
        if (current.kind === 'q') return { item: current.item, kind: 'a' };
        return current.item + 1 < items.length ? { item: current.item + 1, kind: 'q' } : null;
    }

    audio.addEventListener('timeupdate', function () {
        if (!current) return;
        var ratio = audio.duration ? audio.currentTime / audio.duration : 0;
        dockFill.style.width = (ratio * 100) + '%';

        if (current.kind !== 'a') return;
        var fill = document.querySelector('[data-fill="' + current.item + '"]');
        if (fill) fill.style.width = (ratio * 100) + '%';
        var time = document.querySelector('[data-time="' + current.item + '"]');
        if (time) time.textContent = fmt(audio.currentTime) + ' / ' + fmt(audio.duration);

        var marks = items[current.item].segments;
        var seg = 0;
        for (var i = 0; i < marks.length; i++) if (audio.currentTime >= marks[i]) seg = i;
        if (!activeSeg || Number(activeSeg.dataset.seg) !== seg || Number(activeSeg.dataset.item) !== current.item) {
            setSegment(current.item, seg);
        }
    });

    audio.addEventListener('play', render);
    audio.addEventListener('pause', render);
    audio.addEventListener('ended', function () {
        if (current && current.kind === 'a') setSegment(null, null);
        var next = queueMode ? nextInQueue() : null;
        if (next) {
            load(next);
            var li = document.getElementById('q' + (next.item + 1));
            if (li && next.kind === 'q') li.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            queueMode = false;
            render();
        }
    });

    document.querySelectorAll('[data-play]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            queueMode = false;
            toggle({ item: Number(btn.dataset.item), kind: btn.dataset.play });
        });
    });

    document.querySelectorAll('.qa__seg').forEach(function (seg) {
        seg.addEventListener('click', function () {
            queueMode = false;
            toggle({ item: Number(seg.dataset.item), kind: 'a' }, Number(seg.dataset.t));
        });
    });

    playAll.addEventListener('click', function () {
        if (queueMode && !audio.paused) { audio.pause(); return; }
        if (queueMode && current) { audio.play(); return; }
        queueMode = true;
        load({ item: 0, kind: 'q' });
        document.getElementById('q1').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    document.getElementById('interviewDockToggle').addEventListener('click', function () {
        if (!current) return;
        audio.paused ? audio.play() : audio.pause();
    });
})();
