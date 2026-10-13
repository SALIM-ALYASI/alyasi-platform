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

    // «سؤال 2» أو اسم البطاقة لو لها عنوان (مثل «تعريف الضيف»).
    function qTag(i) {
        return /^\d+$/.test(items[i].tag) ? data.labels.question + ' ' + items[i].tag : items[i].tag;
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
            dockLabel.textContent = current.kind === 'i' || current.kind === 'x'
                ? data.labels[current.kind === 'i' ? 'intro' : 'outro'] + ' · ' + data.labels.host
                : (current.kind === 'q' ? qTag(current.item) + ' · ' + data.labels.host : data.labels.answer + ' · ' + items[current.item].tag);
        }
    }

    function load(track, seek) {
        current = track;
        var src = track.kind === 'i' ? data.intro
            : track.kind === 'x' ? data.outro
            : track.kind === 'q' ? items[track.item].q
            : track.kind === 'o' ? items[track.item].o
            : items[track.item].a;
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
        // الترتيب: المقدمة بصوت سالم (لو موجودة)، ثم سؤال/إجابة لكل محور، ثم الخاتمة.
        if (!current) return data.intro ? { item: -1, kind: 'i' } : { item: 0, kind: 'q' };
        if (current.kind === 'i') return { item: 0, kind: 'q' };
        if (current.kind === 'q') return { item: current.item, kind: 'a' };
        if (current.kind === 'x') return null;
        if (current.item + 1 < items.length) return { item: current.item + 1, kind: 'q' };
        return data.outro ? { item: -2, kind: 'x' } : null;
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
            var li = next.kind === 'x' ? document.getElementById('interviewOutro') : document.getElementById('q' + (next.item + 1));
            if (li && (next.kind === 'q' || next.kind === 'x')) li.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
        current = null;
        var first = nextInQueue();
        load(first);
        if (first.kind === 'q') document.getElementById('q1').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    document.getElementById('interviewDockToggle').addEventListener('click', function () {
        if (!current) return;
        audio.paused ? audio.play() : audio.pause();
    });

    // ترجمة النص بنفس الصفحة (والصوت يبقى التسجيل الأصلي): زر عام يبدّل كل
    // شي، وزر صغير تحت كل إجابة يبدّل نصها بس. ما ينحفظ -- التحديث يرجّع العربي.
    var trSource = document.getElementById('interviewTranslation');
    if (trSource) {
        var translation = JSON.parse(trSource.textContent);
        var trButton = document.getElementById('interviewTranslate');
        var itemTwins = {};
        var introTwins = null;

        var englishTwin = function (original, tag, className, texts) {
            var twin = document.createElement(tag);
            twin.className = className;
            twin.lang = 'en';
            twin.dir = 'ltr';
            twin.hidden = true;
            texts.forEach(function (text) {
                if (tag === 'div') {
                    var p = document.createElement('p');
                    p.textContent = text;
                    twin.appendChild(p);
                } else {
                    twin.textContent = text;
                }
            });
            original.parentNode.insertBefore(twin, original.nextSibling);
            return [original, twin];
        };

        var show = function (pairs, english) {
            pairs.forEach(function (pair) {
                if (pair[0]) pair[0].hidden = english;
                if (pair[1]) pair[1].hidden = !english;
            });
        };

        // full: السؤال والعناوين كمان (الزر العام)؛ بدونها نص الإجابة بس (زر الإجابة).
        var twinsFor = function (i) {
            if (itemTwins[i]) return itemTwins[i];
            var li = document.querySelectorAll('.qa')[i];
            var item = translation.items[i];
            var labels = li.querySelectorAll('.qa__label');
            itemTwins[i] = {
                answer: [englishTwin(li.querySelector('.qa__text'), 'div', 'qa__text qa__text--written', item.paragraphs)],
                rest: [
                    englishTwin(labels[0], 'span', 'qa__label', [item.question_label]),
                    englishTwin(li.querySelector('.qa__bubble > p'), 'p', '', [item.question]),
                    englishTwin(labels[1], 'span', 'qa__label', [item.answer_label]),
                ],
            };
            return itemTwins[i];
        };

        var setItem = function (i, english, full) {
            var twins = twinsFor(i);
            show(twins.answer, english);
            if (full) show(twins.rest, english);
            var button = document.querySelector('[data-translate-item="' + i + '"]');
            if (button) button.textContent = english ? button.dataset.labelAr : button.dataset.labelEn;
        };

        document.querySelectorAll('[data-translate-item]').forEach(function (button) {
            button.addEventListener('click', function () {
                var i = Number(button.dataset.translateItem);
                var english = twinsFor(i).answer[0][0].hidden === false;
                setItem(i, english, false);
            });
        });

        var showingEnglish = false;
        if (trButton) {
            trButton.addEventListener('click', function () {
                showingEnglish = !showingEnglish;
                if (!introTwins) {
                    // المقدمة والخاتمة: فقرات سالم تتبدّل بترجمتها كلها مرة وحدة.
                    introTwins = [];
                    [['.interview__intro:not(.interview__outro) > div', translation.intro], ['#interviewOutro > div', translation.outro]].forEach(function (part) {
                        var box = document.querySelector(part[0]);
                        if (!box || !part[1]) return;
                        var paras = box.querySelectorAll(':scope > p');
                        var block = englishTwin(paras[paras.length - 1], 'div', 'interview__intro-en', part[1].split(/\n\s*\n/));
                        paras.forEach(function (p) { introTwins.push([p, null]); });
                        introTwins.push([null, block[1]]);
                    });
                }
                show(introTwins, showingEnglish);
                translation.items.forEach(function (_, i) { setItem(i, showingEnglish, true); });
                trButton.textContent = showingEnglish ? trButton.dataset.labelAr : trButton.dataset.labelEn;
            });
        }
    }
})();
