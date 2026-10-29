{{-- استوديو المقابلات (InterviewStudioController): هوية ALYASI × المؤتمر. رابط الضيف = سؤال
     وحد بالمرة؛ رابط المضيف = أزرار سريعة + تبويب لكل ضيف. التسجيل: سجّل ← اسمع ← أرسل. --}}
@php
    $ev = $data['event'];
    $first = $guest ? explode(' ', trim(str_replace('د. ', '', $guest['name_ar'])))[0] : null;
@endphp
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#050b17">
    <title>{{ $guest ? 'مقابلة '.$guest['name_ar'] : 'استوديو المقابلات' }} — {{ $ev['name'] }} × ALYASI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quantico:wght@700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap">
    <style>
        :root {
            color-scheme: dark;
            --bg: #050b17; --surface: #0b1830; --surface-2: #11223f; --line: rgba(160, 190, 230, .14);
            --text: #eef3fa; --muted: #9bb0c9; --teal: #0bb9cb; --red: #f33829; --silver: #c9d4e3; --ok: #3ccf8e; --warn: #f6b54d;
            --grad: linear-gradient(90deg, var(--teal), var(--red));
            --display: 'Quantico', 'Arial Narrow', sans-serif;
            --ui: 'IBM Plex Sans Arabic', 'SF Arabic', 'Segoe UI', system-ui, sans-serif;
        }
        * { box-sizing: border-box; }
        html { padding-top: env(safe-area-inset-top, 0px); padding-bottom: env(safe-area-inset-bottom, 0px); }
        body {
            margin: 0; color: var(--text); font-family: var(--ui);
            background: radial-gradient(120% 60% at 100% 0%, rgba(11, 185, 203, .13), transparent 60%),
                        radial-gradient(110% 60% at 0% 100%, rgba(243, 56, 41, .10), transparent 60%), var(--bg);
            background-attachment: fixed; min-height: 100vh;
            padding: 14px max(16px, env(safe-area-inset-right, 0px)) 40px max(16px, env(safe-area-inset-left, 0px));
        }
        [hidden] { display: none !important; }
        .app { max-width: 540px; margin: 0 auto; display: grid; gap: 14px; }
        .cobrand { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--line); }
        .cobrand .alyasi { font: 700 15px/1 var(--ui); letter-spacing: .32em; color: var(--silver); }
        .cobrand .x { color: var(--muted); font-size: 13px; }
        .cobrand .ev { text-align: end; }
        .cobrand .ev b { display: block; font: 700 17px/1 var(--display); letter-spacing: .06em; background: var(--grad); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .cobrand .ev span { font-size: 11px; color: var(--muted); }
        .meta-line { display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 12.5px; color: var(--muted); }
        .meta-line i { width: 6px; height: 6px; border-radius: 50%; background: var(--teal); display: inline-block; margin-inline-end: 6px; vertical-align: middle; }

        .card { background: var(--surface); border: 1px solid var(--line); border-radius: 18px; padding: 16px; }
        .guest-card { display: grid; gap: 4px; position: relative; overflow: hidden; }
        .guest-card::before { content: ""; position: absolute; inset: 0 0 auto; height: 3px; background: var(--grad); }
        .guest-card .hello { font-size: 13px; color: var(--teal); font-weight: 600; }
        .guest-card h1 { margin: 0; font-size: 22px; line-height: 1.4; }
        .guest-card .role { font-size: 13.5px; color: var(--muted); line-height: 1.6; }
        .guest-card .note { margin: 8px 0 0; font-size: 13.5px; line-height: 1.85; color: var(--silver); }
        .langs { display: flex; gap: 6px; margin-top: 10px; }
        .langs button { font: 600 12.5px/1 var(--ui); color: var(--muted); background: var(--surface-2); border: 1px solid var(--line); border-radius: 99px; padding: 8px 12px; cursor: pointer; }
        .langs button[aria-pressed="true"] { color: var(--text); border-color: var(--teal); }

        .progress { display: grid; gap: 8px; }
        .progress .row { display: flex; justify-content: space-between; font-size: 12.5px; color: var(--muted); }
        .dots { display: grid; grid-auto-flow: column; gap: 6px; }
        .dots span { height: 6px; border-radius: 99px; background: var(--surface-2); }
        .dots span.done { background: var(--ok); }
        .dots span.now { background: var(--grad); }

        .question { display: grid; gap: 14px; }
        .question .tag { font: 600 12px/1 var(--ui); color: var(--teal); }
        .question .tag.opt { color: var(--warn); }
        .question p { margin: 0; font-size: 19px; line-height: 1.85; }
        .question p.en { direction: ltr; text-align: left; font-size: 14px; color: var(--muted); line-height: 1.6; }

        .btn { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; font: 700 16px/1 var(--ui); color: var(--text); border-radius: 14px; padding: 16px; cursor: pointer; border: 1px solid var(--line); background: var(--surface-2); touch-action: manipulation; }
        .btn:focus-visible, button:focus-visible { outline: 3px solid var(--teal); outline-offset: 2px; }
        .btn:disabled { opacity: .55; cursor: default; }
        .btn.play { border-color: rgba(11, 185, 203, .55); background: rgba(11, 185, 203, .12); }
        .btn.play.is-playing { background: rgba(11, 185, 203, .26); }
        .btn.rec { border: 0; background: var(--grad); color: #fff; }
        .btn.rec.is-recording { background: var(--red); animation: pulse 1.2s ease-in-out infinite; }
        .btn.send { border: 0; background: var(--ok); color: #04130c; }
        .btn.ghost { background: transparent; }
        .ico { width: 22px; height: 22px; flex: none; }
        .timer { font: 700 15px/1 var(--display); letter-spacing: .05em; font-variant-numeric: tabular-nums; }
        .wave { display: grid; grid-auto-flow: column; gap: 3px; align-items: center; justify-content: center; height: 26px; }
        .wave i { width: 4px; border-radius: 4px; background: var(--red); animation: wave 1s ease-in-out infinite; }
        .wave i:nth-child(2n) { animation-delay: .15s } .wave i:nth-child(3n) { animation-delay: .3s }
        @keyframes pulse { 50% { opacity: .75 } }
        @keyframes wave { 0%, 100% { height: 6px } 50% { height: 24px } }

        .recorder { display: grid; gap: 10px; }
        .review { display: grid; gap: 10px; background: rgba(11, 185, 203, .07); border: 1px solid rgba(11, 185, 203, .3); border-radius: 14px; padding: 12px; }
        .review .hint { font-size: 13px; color: var(--silver); line-height: 1.7; }
        .review audio { width: 100%; }
        .review .two { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .status { font-size: 13.5px; font-weight: 600; line-height: 1.7; }
        .status.ok { color: var(--ok); } .status.err { color: var(--warn); } .status.wait { color: #ffc861; }
        .review .retry { font: 600 13px/1 var(--ui); color: var(--teal); background: none; border: 0; padding: 4px 0; cursor: pointer; text-decoration: underline; justify-self: start; }
        .sent-box { display: grid; gap: 8px; background: rgba(60, 207, 142, .08); border: 1px solid rgba(60, 207, 142, .35); border-radius: 14px; padding: 12px; }
        .sent-box button { font: 600 13px/1 var(--ui); color: var(--muted); background: none; border: 0; padding: 4px 0; cursor: pointer; text-decoration: underline; justify-self: start; }

        .nav { display: grid; grid-template-columns: 1fr 2fr; gap: 8px; }
        .nav .next { border: 0; background: var(--silver); color: var(--bg); }
        .finish { text-align: center; display: grid; gap: 12px; justify-items: center; }
        .finish h2 { margin: 0; font-size: 22px; }
        .finish p { margin: 0; color: var(--muted); line-height: 1.8; font-size: 14.5px; }

        .label { font: 600 12px/1 var(--ui); color: var(--muted); }
        .quick { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
        .quick button { font: 600 13px/1.3 var(--ui); color: var(--text); background: var(--surface); border: 1px solid rgba(11, 185, 203, .4); border-radius: 12px; padding: 12px 6px; cursor: pointer; }
        .quick button.is-playing { background: rgba(11, 185, 203, .26); }
        .tabs { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none; }
        .tabs button { flex: none; font: 600 13px/1 var(--ui); color: var(--muted); background: var(--surface); border: 1px solid var(--line); border-radius: 99px; padding: 10px 14px; cursor: pointer; }
        .tabs button[aria-selected="true"] { color: var(--text); border-color: var(--teal); background: rgba(11, 185, 203, .14); }
        .hq { display: grid; gap: 10px; }
        .hq .top { display: grid; grid-template-columns: 2.2em minmax(0, 1fr); gap: 10px; align-items: start; }
        .hq .n { font: 700 14px/1.9 var(--display); color: var(--teal); text-align: center; }
        .hq p { margin: 0; font-size: 15px; line-height: 1.75; }
        .hq.done { border-color: rgba(60, 207, 142, .45); }
        .hq.done .n { color: var(--ok); }
        .hq .row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .hq .btn { padding: 12px; font-size: 14px; }
        footer { text-align: center; font-size: 11.5px; color: var(--muted); line-height: 1.8; padding-top: 6px; }
        footer b { background: var(--grad); -webkit-background-clip: text; background-clip: text; color: transparent; }
        @media (prefers-reduced-motion: reduce) { .wave i, .btn.rec.is-recording { animation: none; } }
    </style>
</head>
<body>
<div class="app">
    <header class="cobrand">
        <span class="alyasi">ALYASI</span>
        <span class="x">×</span>
        <div class="ev"><b>{{ $ev['name'] }}</b><span id="t-partner">الشريك الإعلامي</span></div>
    </header>
    <div class="meta-line"><span><i></i><span id="t-date">{{ $ev['date_ar'] }}</span></span><span id="t-venue">{{ $ev['venue_ar'] }}</span></div>

    @if ($mode === 'guest')
        <section class="card guest-card">
            <span class="hello" id="t-hello">أهلًا وسهلًا {{ $first }}</span>
            <h1 id="t-name">{{ $guest['name_ar'] }}</h1>
            <span class="role" id="t-role">{{ $guest['role_ar'] }}</span>
            <p class="note" id="t-note">استمع لكل سؤال بصوت سالم الحجري، وبعدها سجّل إجابتك واسمعها. إذا عجبتك اضغط «أرسل الإجابة»، وإذا لا أعد التسجيل.</p>
            <div class="langs" role="group" aria-label="لغة الصفحة">
                <button type="button" data-lang="ar" aria-pressed="true">العربية</button>
                <button type="button" data-lang="en" aria-pressed="false">English</button>
            </div>
        </section>

        <div class="progress" id="progress">
            <div class="row"><span id="step-label"></span><span id="sent-label"></span></div>
            <div class="dots" id="dots" aria-hidden="true"></div>
        </div>

        <article class="card question" id="stage"></article>
    @else
        <section class="card guest-card">
            <span class="hello">وضع المضيف</span>
            <h1>استوديو المقابلات</h1>
            <p class="note">شغّل السؤال بصوتك، وسجّل إجابة الضيف، واسمعها قبل الإرسال.</p>
        </section>
        <span class="label">أزرار سريعة بصوتك</span>
        <div class="quick" id="quick"></div>
        <span class="label">الضيوف</span>
        <div class="tabs" id="tabs" role="tablist" aria-label="الضيوف"></div>
        <div class="hq-list" id="host-list" style="display:grid; gap:10px"></div>
    @endif

    <footer><span id="t-footer">تغطية <b>ALYASI</b> الحصرية لـ {{ $ev['name'] }}</span><br>alyasi.dev</footer>
</div>

<script>
(function () {
    const data = @json($data);
    const mode = @json($mode);
    const guest = @json($guest);
    const audioBase = @json($audioBase);
    const answersUrl = {{ Js::from(route('tools.interview-studio.answers', ['event' => $event, 'token' => $token], false)) }};
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const storeKey = 'studio:' + @json($event) + ':' + @json($token) + ':';
    let lang = 'ar';
    const T = (ar, en) => (lang === 'en' ? en : ar);
    const store = {
        get: (k) => { try { return localStorage.getItem(storeKey + k); } catch (e) { return null; } },
        set: (k, v) => { try { localStorage.setItem(storeKey + k, v); } catch (e) {} },
    };

    // ---- مشغّل واحد بكل الصفحة
    let active = null;
    function playClip(name, btn, labels) {
        if (active && active.btn === btn) { active.audio.pause(); stopActive(); return; }
        stopActive();
        const audio = new Audio(audioBase + name + '-' + lang + '.m4a');
        active = { audio, btn, labels };
        btn.classList.add('is-playing');
        if (labels) btn.querySelector('span').textContent = labels[1];
        audio.addEventListener('ended', stopActive);
        audio.play().catch(stopActive);
    }
    function stopActive() {
        if (!active) return;
        active.audio.pause();
        active.btn.classList.remove('is-playing');
        if (active.labels) active.btn.querySelector('span').textContent = active.labels[0];
        active = null;
    }

    // ---- المايك: يقفل لو انسكرت الصفحة وهو يسجّل
    const streams = new Set();
    window.addEventListener('pagehide', () => streams.forEach((s) => s.getTracks().forEach((t) => t.stop())));
    const mimeType = ['audio/mp4', 'audio/webm;codecs=opus', 'audio/webm'].find((t) => window.MediaRecorder && MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported(t)) || '';
    const newTake = () => (crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).slice(2, 12));

    const ICON_MIC = '<svg class="ico" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 15a3 3 0 0 0 3-3V6a3 3 0 0 0-6 0v6a3 3 0 0 0 3 3Zm5-3a5 5 0 0 1-10 0H5a7 7 0 0 0 6 6.92V21h2v-2.08A7 7 0 0 0 19 12Z"/></svg>';
    const ICON_PLAY = '<svg class="ico" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>';
    const el = (html) => { const t = document.createElement('template'); t.innerHTML = html.trim(); return t.content.firstElementChild; };

    // ---- صندوق محلي (IndexedDB): الإجابة تنحفظ بالجهاز أول ما يخلص التسجيل،
    // وما تنمسح إلا بعد ما توصل للسيرفر. انقطاع النت أو قفل الصفحة ما يضيّعها:
    // نفس الرابط يرجّعها ويكمل إرسالها تلقائيًا (نفس take_id فما تتكرر).
    const scope = @json($event) + ':' + @json($token);
    const takes = (() => {
        const mem = new Map();
        let db = null;
        const open = () => db || (db = new Promise((resolve, reject) => {
            if (!window.indexedDB) return reject(new Error('no indexedDB'));
            const req = indexedDB.open('alyasi-studio', 1);
            req.onupgradeneeded = () => req.result.createObjectStore('takes', { keyPath: 'take' });
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        }));
        const run = (mode, fn) => open().then((d) => new Promise((resolve, reject) => {
            const tx = d.transaction('takes', mode);
            const out = fn(tx.objectStore('takes'));
            tx.oncomplete = () => resolve(out ? out.result : undefined);
            tx.onerror = tx.onabort = () => reject(tx.error);
        }));
        return {
            put: (t) => { mem.set(t.take, t); return run('readwrite', (s) => s.put(t)).catch(() => {}); },
            del: (k) => { mem.delete(k); return run('readwrite', (s) => s.delete(k)).catch(() => {}); },
            all: async () => {
                const saved = await run('readonly', (s) => s.getAll()).catch(() => []);
                const out = new Map((saved || []).filter((t) => t.scope === scope).map((t) => [t.take, t]));
                mem.forEach((t, k) => out.set(k, t));
                return [...out.values()].sort((a, b) => a.at - b.at);
            },
        };
    })();
    if (navigator.storage && navigator.storage.persist) navigator.storage.persist().catch(() => {});

    const watchers = new Map(); // question_id → تحديث شكل المسجّل
    const sentTakes = new Set();
    const notify = (t, state, msg) => { const w = watchers.get(t.questionId); if (w) w(t, state, msg); };

    async function uploadTake(t) {
        const body = new FormData();
        const ext = ((t.blob.type || 'audio/webm').split('/')[1] || 'webm').split(';')[0];
        body.append('audio', t.blob, t.questionId + '.' + ext);
        body.append('question_id', t.questionId);
        body.append('take_id', t.take);
        const res = await fetch(answersUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' }, body });
        const out = await res.json().catch(() => ({}));
        if (res.ok && out.success) return out;
        const err = new Error(out.message || ('HTTP ' + res.status));
        err.status = res.status;
        throw err;
    }

    // يرسل كل الإجابات اللي بالطابور. يشتغل عند فتح الصفحة، ولما يرجع النت، وكل 20 ثانية.
    let flushing = false;
    async function flush() {
        if (flushing) return;
        flushing = true;
        try {
            for (const t of (await takes.all()).filter((x) => x.state === 'queued')) {
                notify(t, 'sending');
                try {
                    await uploadTake(t);
                    sentTakes.add(t.take);
                    await takes.del(t.take);
                    store.set(t.questionId, '1');
                    notify(t, 'sent');
                } catch (e) {
                    if (e.status === 419) {
                        notify(t, 'waiting', T('انتهت صلاحية الصفحة. حدّث الصفحة وبتنرسل إجابتك تلقائيًا، هي محفوظة بجهازك.', 'This page expired. Refresh it and your saved answer will be sent automatically.'));
                    } else if (e.status && e.status >= 400 && e.status < 500 && ![408, 409, 425, 429].includes(e.status)) {
                        await takes.del(t.take);
                        notify(t, 'rejected', e.message);
                    } else {
                        notify(t, 'waiting');
                    }
                }
            }
        } finally {
            flushing = false;
        }
    }
    window.addEventListener('online', flush);
    setInterval(flush, 20000);
    setTimeout(flush, 1200); // اللي بالطابور من فتحة سابقة

    // ---- مسجّل: سجّل ← اسمع ← أرسل (مرة وحدة لكل تسجيل) ← نسخة جديدة اختيارية
    function recorder(questionId, onSent) {
        const box = el('<div class="recorder"></div>');
        let rec = null, stream = null, chunks = [], timer = null, secs = 0, starting = false, cur = null, view = null;

        function idle() {
            view = null;
            box.innerHTML = '';
            const b = el('<button class="btn rec" type="button">' + ICON_MIC + '<span>' + T('سجّل إجابتك', 'Record your answer') + '</span></button>');
            b.addEventListener('click', start);
            box.appendChild(b);
            if (store.get(questionId)) box.prepend(sentBox(true));
        }

        async function start() {
            if (starting) return;
            starting = true;
            stopActive();
            try {
                stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            } catch (e) {
                starting = false;
                box.prepend(el('<div class="status err">' + T('ما قدرنا نوصل للمايك. اسمح للمتصفح باستخدام المايك وحاول مرة ثانية.', 'We could not access the microphone. Please allow it and try again.') + '</div>'));
                return;
            }
            streams.add(stream);
            chunks = [];
            rec = mimeType ? new MediaRecorder(stream, { mimeType }) : new MediaRecorder(stream);
            rec.addEventListener('dataavailable', (e) => { if (e.data.size) chunks.push(e.data); });
            rec.addEventListener('stop', stopped);
            rec.start();
            starting = false;
            secs = 0;
            view = null;
            box.innerHTML = '';
            const b = el('<button class="btn rec is-recording" type="button">' + ICON_MIC + '<span>' + T('يسجّل… اضغط للإيقاف', 'Recording… tap to stop') + '</span><span class="timer">00:00</span></button>');
            b.addEventListener('click', () => rec && rec.state === 'recording' && rec.stop());
            box.appendChild(b);
            box.appendChild(el('<div class="wave" aria-hidden="true">' + '<i></i>'.repeat(12) + '</div>'));
            timer = setInterval(() => { secs++; b.querySelector('.timer').textContent = String(Math.floor(secs / 60)).padStart(2, '0') + ':' + String(secs % 60).padStart(2, '0'); }, 1000);
        }

        function stopped() {
            clearInterval(timer);
            stream.getTracks().forEach((t) => t.stop());
            streams.delete(stream);
            const type = rec.mimeType || 'audio/webm';
            rec = null;
            // ينحفظ بالجهاز فورًا، قبل الإرسال.
            cur = { take: newTake(), scope, questionId, blob: new Blob(chunks, { type }), state: 'draft', at: Date.now() };
            takes.put(cur);
            review();
        }

        function review(note) {
            box.innerHTML = '';
            view = el('<div class="review"><span class="hint"></span><audio controls preload="metadata"></audio><div class="two"><button class="btn ghost again" type="button">' + T('إعادة التسجيل', 'Record again') + '</button><button class="btn send" type="button">' + T('أرسل الإجابة', 'Send answer') + '</button></div><div class="status" role="status"></div></div>');
            view.querySelector('.hint').textContent = note || T('اسمع إجابتك. إذا تمام اضغط «أرسل الإجابة»، وإذا تبي تعيدها اضغط «إعادة التسجيل».', 'Listen to your answer. If you are happy, tap “Send answer”; otherwise record again.');
            view.querySelector('audio').src = URL.createObjectURL(cur.blob);
            view.querySelector('.again').addEventListener('click', () => { takes.del(cur.take); cur = null; idle(); });
            view.querySelector('.send').addEventListener('click', queue);
            box.appendChild(view);
            if (cur.state === 'queued') waiting();
        }

        function setStatus(cls, text) {
            if (!view) return;
            const s = view.querySelector('.status');
            s.className = 'status' + (cls ? ' ' + cls : '');
            s.textContent = text;
        }
        function lock(on) { if (view) view.querySelectorAll('.two button').forEach((b) => { b.disabled = on; }); }

        function waiting(msg) {
            lock(true);
            setStatus('wait', msg || T('⏳ ما وصل النت. إجابتك محفوظة بجهازك وبتنرسل تلقائيًا أول ما يرجع الاتصال. لا تسجّلها مرة ثانية، وحتى لو قفلت الصفحة افتح نفس الرابط وبتكمل.', '⏳ No connection. Your answer is saved on this device and will be sent automatically when you are back online. No need to record it again; even if you close the page, open the same link and it will continue.'));
            if (!view.querySelector('.retry')) {
                const r = el('<button class="retry" type="button">' + T('حاول الحين', 'Try now') + '</button>');
                r.addEventListener('click', flush);
                view.querySelector('.status').after(r);
            }
        }

        async function queue() {
            if (!cur || cur.state === 'queued') return;
            cur.state = 'queued';
            lock(true);
            setStatus('', T('جاري الإرسال…', 'Sending…'));
            await takes.put(cur);
            flush();
        }

        watchers.set(questionId, (t, state, msg) => {
            if (!cur || t.take !== cur.take) {
                // إجابة قديمة من فتحة سابقة للصفحة وصلت بالخلفية.
                if (state === 'sent' && !cur && !rec) { idle(); if (onSent) onSent(); }
                return;
            }
            if (state === 'sending') { lock(true); setStatus('', T('جاري الإرسال…', 'Sending…')); }
            else if (state === 'waiting') waiting(msg);
            else if (state === 'sent') { cur = null; view = null; box.innerHTML = ''; box.appendChild(sentBox(false)); if (onSent) onSent(); }
            else if (state === 'rejected') {
                cur.state = 'rejected';
                lock(false);
                view.querySelector('.send').disabled = true;
                setStatus('err', T('ما انقبلت الإجابة (' + msg + '). سجّلها مرة ثانية.', 'The answer was not accepted (' + msg + '). Please record it again.'));
            }
        });

        function sentBox(fromBefore) {
            const s = el('<div class="sent-box"><span class="status ok">' + (fromBefore ? T('✓ أرسلت إجابة لهذا السؤال قبل.', '✓ You already sent an answer to this question.') : T('✓ وصلت إجابتك، شكرًا لك.', '✓ Your answer was received, thank you.')) + '</span><button type="button">' + T('سجّل نسخة جديدة (تنرسل كإجابة إضافية)', 'Record a new version (sent as an extra answer)') + '</button></div>');
            s.querySelector('button').addEventListener('click', () => { s.remove(); start(); });
            return s;
        }

        idle();
        // تسجيل محفوظ بالجهاز لهذا السؤال (ما انرسل أو بالطابور)؟ نرجّعه.
        takes.all().then((list) => {
            const saved = list.filter((t) => t.questionId === questionId).pop();
            if (!saved || sentTakes.has(saved.take) || cur || rec || starting) return;
            cur = saved;
            review(saved.state === 'draft' ? T('هذا تسجيلك السابق، ما انرسل بعد. اسمعه وأرسله، أو أعد التسجيل.', 'This is your earlier recording; it has not been sent yet. Listen and send it, or record again.') : null);
        });
        return box;
    }

    // ================= رابط الضيف =================
    if (mode === 'guest') {
        const steps = guest.questions.map((q, i) => ({ id: guest.id + '-q' + (i + 1), audio: q.audio, ar: q.ar, en: q.en, n: i + 1 }));
        if (guest.extra && data.extra) steps.push({ id: guest.id + '-extra', audio: data.extra.audio, ar: data.extra.ar, en: data.extra.en, extra: true });
        let idx = Math.min(steps.length, Number(store.get('step')) || 0);
        const stage = document.getElementById('stage');

        function texts() {
            document.documentElement.lang = lang;
            document.documentElement.dir = lang === 'en' ? 'ltr' : 'rtl';
            const first = (lang === 'en' ? guest.name_en : guest.name_ar).replace(/^(Dr\.|د\.)\s*/, '').split(' ')[0];
            document.getElementById('t-hello').textContent = T('أهلًا وسهلًا ' + first, 'Welcome, ' + first);
            document.getElementById('t-name').textContent = T(guest.name_ar, guest.name_en);
            document.getElementById('t-role').textContent = T(guest.role_ar, guest.role_en);
            document.getElementById('t-note').textContent = T('استمع لكل سؤال بصوت سالم الحجري، وبعدها سجّل إجابتك واسمعها. إذا عجبتك اضغط «أرسل الإجابة»، وإذا لا أعد التسجيل.', 'Listen to each question in Salem Al Hajri’s voice, then record your answer and play it back. If you are happy, tap “Send answer”; if not, record again.');
            document.getElementById('t-partner').textContent = T('الشريك الإعلامي', 'Media partner');
            document.getElementById('t-date').textContent = T(data.event.date_ar, data.event.date_en);
            document.getElementById('t-venue').textContent = T(data.event.venue_ar, data.event.venue_en);
            document.getElementById('t-footer').innerHTML = T('تغطية <b>ALYASI</b> الحصرية لـ ', 'ALYASI exclusive coverage of ') + data.event.name;
            document.querySelectorAll('.langs button').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.lang === lang)));
        }

        function progress() {
            const main = steps.filter((s) => !s.extra);
            const sent = main.filter((s) => store.get(s.id)).length;
            document.getElementById('step-label').textContent = idx < steps.length
                ? (steps[idx].extra ? T('سؤال إضافي (اختياري)', 'Bonus question (optional)') : T('السؤال ' + steps[idx].n + ' من ' + main.length, 'Question ' + steps[idx].n + ' of ' + main.length))
                : T('خلصت الأسئلة', 'All done');
            document.getElementById('sent-label').textContent = T('أرسلت ' + sent + ' من ' + main.length, sent + ' of ' + main.length + ' sent');
            const dots = document.getElementById('dots');
            dots.innerHTML = '';
            steps.forEach((s, i) => { const d = document.createElement('span'); if (store.get(s.id)) d.className = 'done'; if (i === idx) d.className = 'now'; dots.appendChild(d); });
        }

        function render() {
            stopActive();
            store.set('step', String(idx));
            progress();
            stage.innerHTML = '';
            if (idx >= steps.length) {
                const f = el('<div class="finish"><h2>' + T('شكرًا لك ' + guest.name_ar, 'Thank you, ' + guest.name_en) + '</h2><p>' + T('وصلتنا إجاباتك، وبتنشر ضمن تغطية ALYASI الحصرية للمؤتمر بعد المراجعة.', 'We have your answers. They will be published in ALYASI’s exclusive conference coverage after review.') + '</p><button class="btn play" type="button">' + ICON_PLAY + '<span>' + T('رسالة شكر من سالم', 'A thank-you from Salem') + '</span></button><button class="btn ghost back" type="button">' + T('رجوع للأسئلة', 'Back to the questions') + '</button></div>');
                const pb = f.querySelector('.play');
                pb.addEventListener('click', () => playClip('thanks', pb));
                f.querySelector('.back').addEventListener('click', () => { idx = 0; render(); });
                stage.appendChild(f);
                return;
            }
            const s = steps[idx];
            stage.appendChild(el('<span class="tag' + (s.extra ? ' opt' : '') + '">' + (s.extra ? T('سؤال إضافي، تقدر تتخطاه', 'Bonus question, you may skip it') : T('السؤال ' + s.n, 'Question ' + s.n)) + '</span>'));
            const p1 = el('<p></p>'); p1.textContent = T(s.ar, s.en); stage.appendChild(p1);
            if (lang === 'ar') { const p2 = el('<p class="en"></p>'); p2.textContent = s.en; stage.appendChild(p2); }
            const labels = [T('استمع للسؤال', 'Listen to the question'), T('يشتغل السؤال… اضغط للإيقاف', 'Playing… tap to stop')];
            const pb = el('<button class="btn play" type="button">' + ICON_PLAY + '<span>' + labels[0] + '</span></button>');
            pb.addEventListener('click', () => playClip(s.audio, pb, labels));
            stage.appendChild(pb);
            stage.appendChild(recorder(s.id, progress));
            const nav = el('<div class="nav"><button class="btn ghost prev" type="button">' + T('السابق', 'Back') + '</button><button class="btn next" type="button">' + (idx === steps.length - 1 ? T('إنهاء', 'Finish') : T('السؤال التالي', 'Next question')) + '</button></div>');
            nav.querySelector('.prev').disabled = idx === 0;
            nav.querySelector('.prev').addEventListener('click', () => { idx--; render(); });
            nav.querySelector('.next').addEventListener('click', () => { idx++; render(); });
            stage.appendChild(nav);
        }

        document.querySelectorAll('.langs button').forEach((b) => b.addEventListener('click', () => { lang = b.dataset.lang; store.set('lang', lang); texts(); render(); }));
        lang = store.get('lang') || guest.language || 'ar';
        texts();
        render();
        return;
    }

    // ================= وضع المضيف =================
    const quick = document.getElementById('quick');
    (data.quick || []).forEach((q) => {
        const b = el('<button type="button"><span></span></button>');
        b.querySelector('span').textContent = q.label_ar;
        b.addEventListener('click', () => playClip(q.audio, b));
        quick.appendChild(b);
    });

    const tabs = document.getElementById('tabs'), list = document.getElementById('host-list');
    function showGuest(g) {
        stopActive();
        tabs.querySelectorAll('button').forEach((b) => b.setAttribute('aria-selected', String(b.dataset.id === g.id)));
        list.innerHTML = '';
        const qs = g.questions.map((q, i) => ({ id: g.id + '-q' + (i + 1), audio: q.audio, ar: q.ar, n: String(i + 1).padStart(2, '0') }));
        if (g.extra && data.extra) qs.push({ id: g.id + '-extra', audio: data.extra.audio, ar: data.extra.ar, n: '+' });
        qs.forEach((q) => {
            const card = el('<div class="card hq"><div class="top"><span class="n"></span><p></p></div><div class="row"><button class="btn play" type="button">' + ICON_PLAY + '<span>تشغيل</span></button><button class="btn ghost en" type="button"><span>English</span></button></div></div>');
            card.querySelector('.n').textContent = q.n;
            card.querySelector('p').textContent = q.ar;
            const mark = () => card.classList.toggle('done', !!store.get(q.id));
            const pb = card.querySelector('.play'), eb = card.querySelector('.en');
            pb.addEventListener('click', () => { lang = 'ar'; playClip(q.audio, pb, ['تشغيل', 'إيقاف']); });
            eb.addEventListener('click', () => { lang = 'en'; playClip(q.audio, eb, ['English', 'Stop']); lang = 'ar'; });
            card.appendChild(recorder(q.id, mark));
            mark();
            list.appendChild(card);
        });
    }
    (data.guests || []).forEach((g, i) => {
        const b = el('<button role="tab" type="button"></button>');
        b.textContent = g.name_ar;
        b.dataset.id = g.id;
        b.addEventListener('click', () => showGuest(g));
        tabs.appendChild(b);
        if (i === 0) setTimeout(() => showGuest(g));
    });
})();
</script>
</body>
</html>
