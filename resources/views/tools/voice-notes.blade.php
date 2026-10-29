{{-- «صوتي» (VoiceNotesController): سالم يسجّل ← الماك يحوّل لنص ← يعدّل والقاموس يتعلّم.
     التسجيل ينحفظ بالجهاز (IndexedDB) لين يوصل للسيرفر، فانقطاع النت ما يضيّعه. --}}
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#050b17">
    <title>صوتي — ALYASI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap">
    <style>
        :root {
            color-scheme: dark;
            --bg: #050b17; --surface: #0b1830; --surface-2: #11223f; --line: rgba(160, 190, 230, .14);
            --text: #eef3fa; --muted: #9bb0c9; --teal: #0bb9cb; --red: #f33829; --silver: #c9d4e3; --ok: #3ccf8e; --warn: #f6b54d;
            --ui: 'IBM Plex Sans Arabic', 'SF Arabic', 'Segoe UI', system-ui, sans-serif;
        }
        * { box-sizing: border-box; }
        html { padding-top: env(safe-area-inset-top, 0px); padding-bottom: env(safe-area-inset-bottom, 0px); }
        body {
            margin: 0; color: var(--text); font-family: var(--ui); min-height: 100vh;
            background: radial-gradient(120% 60% at 100% 0%, rgba(11, 185, 203, .13), transparent 60%),
                        radial-gradient(110% 60% at 0% 100%, rgba(243, 56, 41, .08), transparent 60%), var(--bg);
            background-attachment: fixed;
            padding: 14px max(16px, env(safe-area-inset-right, 0px)) 40px max(16px, env(safe-area-inset-left, 0px));
        }
        [hidden] { display: none !important; }
        .app { max-width: 620px; margin: 0 auto; display: grid; gap: 14px; }
        header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--line); }
        .brand { font: 700 15px/1 var(--ui); letter-spacing: .32em; color: var(--silver); }
        h1 { margin: 0; font-size: 26px; }
        .sub { margin: 4px 0 0; color: var(--muted); font-size: 14px; line-height: 1.7; }
        .card { background: var(--surface); border: 1px solid var(--line); border-radius: 18px; padding: 16px; display: grid; gap: 12px; }
        .langs { display: inline-flex; background: var(--surface-2); border-radius: 999px; padding: 3px; }
        .langs button { font: 600 13px/1 var(--ui); color: var(--muted); background: none; border: 0; border-radius: 999px; padding: 8px 14px; cursor: pointer; }
        .langs button[aria-pressed="true"] { background: var(--teal); color: #03141a; }
        .btn { font: 700 17px/1.2 var(--ui); color: #fff; border: 0; border-radius: 14px; padding: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; }
        .btn:disabled { opacity: .5; cursor: default; }
        .rec { background: linear-gradient(90deg, var(--teal), var(--red)); font-size: 19px; padding: 20px; }
        .rec.is-recording { background: var(--red); animation: pulse 1.4s ease-in-out infinite; }
        @keyframes pulse { 50% { box-shadow: 0 0 0 10px rgba(243, 56, 41, .18); } }
        @media (prefers-reduced-motion: reduce) { .rec.is-recording { animation: none; } }
        .ghost { background: var(--surface-2); color: var(--silver); font-size: 15px; padding: 12px; }
        .row { display: flex; gap: 8px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
        .timer { font: 700 15px/1 var(--ui); font-variant-numeric: tabular-nums; }
        .status { font-size: 14px; color: var(--muted); line-height: 1.7; min-height: 1.7em; }
        .status.err { color: var(--warn); } .status.ok { color: var(--ok); }
        .note { background: var(--surface); border: 1px solid var(--line); border-radius: 16px; padding: 14px; display: grid; gap: 10px; }
        .note .meta { display: flex; gap: 8px; align-items: center; justify-content: space-between; font-size: 12.5px; color: var(--muted); }
        .badge { font-size: 12px; font-weight: 600; border-radius: 999px; padding: 4px 10px; background: var(--surface-2); color: var(--silver); }
        .badge.wait { color: var(--warn); } .badge.done { color: var(--ok); } .badge.fail { color: var(--red); }
        textarea { width: 100%; min-height: 120px; resize: vertical; font: 400 16px/1.9 var(--ui); color: var(--text); background: #07112a; border: 1px solid var(--line); border-radius: 12px; padding: 12px; }
        textarea[dir="ltr"] { font-family: system-ui, sans-serif; }
        audio { width: 100%; height: 40px; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .actions button, .chip-btn { font: 600 13.5px/1 var(--ui); color: var(--silver); background: var(--surface-2); border: 1px solid var(--line); border-radius: 10px; padding: 10px 14px; cursor: pointer; }
        .actions .primary { background: var(--teal); color: #03141a; border-color: transparent; }
        .del { font: 600 12.5px/1 var(--ui); color: var(--muted); background: none; border: 0; padding: 6px 2px; cursor: pointer; }
        .del:hover { color: var(--red); }
        .learn { background: rgba(11, 185, 203, .07); border: 1px solid rgba(11, 185, 203, .3); border-radius: 12px; padding: 12px; display: grid; gap: 8px; font-size: 14px; }
        .learn label { display: flex; gap: 8px; align-items: center; }
        .learn s { color: var(--warn); } .learn b { color: var(--ok); font-weight: 600; }
        details summary { cursor: pointer; font-weight: 700; }
        .dict { display: grid; gap: 10px; margin-top: 10px; }
        .dict form { display: flex; gap: 6px; flex-wrap: wrap; }
        .dict input { flex: 1 1 120px; min-width: 0; font: 400 15px/1.4 var(--ui); color: var(--text); background: #07112a; border: 1px solid var(--line); border-radius: 10px; padding: 10px; }
        .chips { display: flex; flex-wrap: wrap; gap: 6px; }
        .chip { display: inline-flex; align-items: center; gap: 6px; font-size: 13.5px; background: var(--surface-2); border-radius: 999px; padding: 6px 6px 6px 12px; }
        .chip button { background: none; border: 0; color: var(--muted); font-size: 16px; cursor: pointer; padding: 0 6px; }
        .empty { color: var(--muted); font-size: 14px; text-align: center; padding: 10px; }
        footer { color: var(--muted); font-size: 12px; text-align: center; }
    </style>
</head>
<body>
<div class="app">
    <header>
        <div><h1>صوتي</h1><p class="sub">تكلّم، والماك يحوّل كلامك لنص. عدّل النص، وأتذكّر تصحيحاتك المرة الجاية.</p></div>
        <span class="brand">ALYASI</span>
    </header>

    <section class="card">
        <div class="row">
            <div class="langs" role="group" aria-label="لغة الكلام">
                <button type="button" data-lang="ar" aria-pressed="true">عربي</button>
                <button type="button" data-lang="en" aria-pressed="false">English</button>
            </div>
            <span class="timer" id="timer">00:00</span>
        </div>
        <button class="btn rec" id="rec" type="button">🎙 ابدأ الكلام</button>
        <button class="btn ghost" id="pick" type="button">📁 ملف صوتي من الجهاز</button>
        <input type="file" id="file" accept="audio/*,video/*,.m4a,.mp3,.wav,.aac,.ogg,.opus,.webm,.caf" hidden>
        <div class="status" id="status" role="status"></div>
    </section>

    <section class="card" id="train" style="gap:8px">
        <div class="row"><b>🎯 يتعلّم من صوتك</b><span class="badge" id="train-label">0 من 30 دقيقة</span></div>
        <div style="height:8px; background:var(--surface-2); border-radius:99px; overflow:hidden"><div id="train-bar" style="height:100%; width:0; background:linear-gradient(90deg, var(--teal), var(--ok))"></div></div>
        <p class="sub" style="margin:0" id="train-note">كل ملاحظة تصحّح نصها وتضغط «حفظ» تصير درس: صوتك مع النص الصحيح. لما توصل 30 دقيقة، ندرّب نسخة من النموذج على نطقك أنت.</p>
    </section>

    <section id="notes" style="display:grid; gap:12px"></section>

    <section class="card">
        <details id="dict-box">
            <summary>قاموسك <span id="dict-count" style="color:var(--muted); font-weight:500"></span></summary>
            <div class="dict">
                <p class="sub" style="margin:0">الكلمات هنا يعطيها الماك للنموذج قبل التحويل، والتصحيحات تنطبق على النص بعده. اللغة حسب الاختيار فوق.</p>
                <form id="add-term"><input name="right" placeholder="كلمة أو اسم تستخدمه كثير" required><button class="chip-btn" type="submit">أضف</button></form>
                <form id="add-fix"><input name="wrong" placeholder="يطلع غلط كذا" required><input name="right" placeholder="والصحيح" required><button class="chip-btn" type="submit">أضف تصحيح</button></form>
                <div class="chips" id="chips"></div>
            </div>
        </details>
    </section>

    <footer>خاص · alyasi.dev</footer>
</div>

<script>
(function () {
    const base = {{ Js::from('/tools/voice/'.$token) }};
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const $ = (id) => document.getElementById(id);
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const pref = { get: (k) => { try { return localStorage.getItem('voice:' + k); } catch (e) { return null; } }, set: (k, v) => { try { localStorage.setItem('voice:' + k, v); } catch (e) {} } };
    const newId = () => (crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).slice(2, 12));
    let lang = pref.get('lang') || 'ar';
    let notes = [], dictionary = [];
    const drafts = {}; // نص يكتبه سالم وما حفظه، ما يضيع مع التحديث الدوري

    async function api(path, opts = {}) {
        const res = await fetch(base + path, Object.assign({ headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } }, opts));
        const out = await res.json().catch(() => ({}));
        if (!res.ok) { const e = new Error(out.message || ('HTTP ' + res.status)); e.status = res.status; throw e; }
        return out;
    }
    const setStatus = (text, cls) => { const s = $('status'); s.textContent = text || ''; s.className = 'status' + (cls ? ' ' + cls : ''); };

    // ---- صندوق محلي: التسجيل ينحفظ بالجهاز لين يأكد السيرفر استلامه.
    const outbox = (() => {
        const mem = new Map();
        let db = null;
        const open = () => db || (db = new Promise((resolve, reject) => {
            if (!window.indexedDB) return reject(new Error('no indexedDB'));
            const req = indexedDB.open('alyasi-voice', 1);
            req.onupgradeneeded = () => req.result.createObjectStore('takes', { keyPath: 'id' });
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        }));
        const run = (mode, fn) => open().then((d) => new Promise((resolve, reject) => {
            const tx = d.transaction('takes', mode); const out = fn(tx.objectStore('takes'));
            tx.oncomplete = () => resolve(out ? out.result : undefined); tx.onerror = tx.onabort = () => reject(tx.error);
        }));
        return {
            put: (t) => { mem.set(t.id, t); return run('readwrite', (s) => s.put(t)).catch(() => {}); },
            del: (id) => { mem.delete(id); return run('readwrite', (s) => s.delete(id)).catch(() => {}); },
            all: async () => { const saved = await run('readonly', (s) => s.getAll()).catch(() => []); const m = new Map((saved || []).map((t) => [t.id, t])); mem.forEach((t, k) => m.set(k, t)); return [...m.values()].sort((a, b) => a.at - b.at); },
        };
    })();
    if (navigator.storage && navigator.storage.persist) navigator.storage.persist().catch(() => {});

    let flushing = false, waiting = [];
    async function flush() {
        if (flushing) return;
        flushing = true;
        try {
            waiting = await outbox.all();
            for (const t of waiting) {
                const body = new FormData();
                const ext = ((t.blob.type || 'audio/webm').split('/')[1] || 'webm').split(';')[0];
                body.append('audio', t.blob, t.name || ('note.' + ext));
                body.append('take_id', t.id);
                body.append('language', t.language);
                try {
                    await api('/notes', { method: 'POST', body });
                    await outbox.del(t.id);
                } catch (e) {
                    if (e.status === 422) { await outbox.del(t.id); setStatus('ما انقبل الملف: ' + e.message, 'err'); continue; }
                    setStatus(e.status === 419 ? 'انتهت صلاحية الصفحة. حدّثها والتسجيل يترفع، هو محفوظ بجهازك.' : '⏳ ما في نت. التسجيل محفوظ بجهازك وبيترفع أول ما يرجع الاتصال.', 'err');
                    break;
                }
            }
            waiting = await outbox.all();
            if (!waiting.length && /ما في نت|انتهت صلاحية/.test($('status').textContent)) setStatus('✓ وصل التسجيل.', 'ok');
        } finally {
            flushing = false;
            render();
            refresh();
        }
    }
    window.addEventListener('online', flush);

    // ---- التسجيل
    const mimeType = ['audio/mp4', 'audio/webm;codecs=opus', 'audio/webm'].find((t) => window.MediaRecorder && MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported(t)) || '';
    let rec = null, stream = null, chunks = [], timer = null, started = 0;
    async function startRec() {
        try { stream = await navigator.mediaDevices.getUserMedia({ audio: true }); }
        catch (e) { setStatus('ما قدرنا نوصل للمايك. اسمح للمتصفح باستخدامه وحاول مرة ثانية.', 'err'); return; }
        chunks = [];
        rec = mimeType ? new MediaRecorder(stream, { mimeType }) : new MediaRecorder(stream);
        rec.ondataavailable = (e) => { if (e.data.size) chunks.push(e.data); };
        rec.onstop = async () => {
            clearInterval(timer);
            stream.getTracks().forEach((t) => t.stop());
            const blob = new Blob(chunks, { type: rec.mimeType || 'audio/webm' });
            rec = null;
            $('rec').textContent = '🎙 ابدأ الكلام'; $('rec').classList.remove('is-recording');
            if (blob.size < 1000) { setStatus('التسجيل قصير جدًا.', 'err'); return; }
            await outbox.put({ id: newId(), blob, language: lang, at: Date.now() });
            setStatus('⬆️ يترفع…');
            flush();
        };
        rec.start(1000);
        started = Date.now();
        $('rec').textContent = '⏹ وقّف وحوّل لنص'; $('rec').classList.add('is-recording');
        setStatus('🔴 أسمعك…');
        timer = setInterval(() => { const s = Math.floor((Date.now() - started) / 1000); $('timer').textContent = String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0'); }, 500);
    }
    $('rec').addEventListener('click', () => (rec && rec.state === 'recording' ? rec.stop() : startRec()));
    window.addEventListener('beforeunload', (e) => { if (rec && rec.state === 'recording') { e.preventDefault(); e.returnValue = ''; } });

    $('pick').addEventListener('click', () => $('file').click());
    $('file').addEventListener('change', async () => {
        const f = $('file').files[0]; $('file').value = '';
        if (!f) return;
        if (f.size > 50 * 1024 * 1024) { setStatus('الملف أكبر من 50MB.', 'err'); return; }
        await outbox.put({ id: newId(), blob: f, name: f.name, language: lang, at: Date.now() });
        setStatus('⬆️ يترفع «' + f.name + '»…');
        flush();
    });

    document.querySelectorAll('.langs button').forEach((b) => {
        b.setAttribute('aria-pressed', String(b.dataset.lang === lang));
        b.addEventListener('click', () => { lang = b.dataset.lang; pref.set('lang', lang); document.querySelectorAll('.langs button').forEach((x) => x.setAttribute('aria-pressed', String(x.dataset.lang === lang))); renderDict(); });
    });

    // ---- مقارنة نص الماك بتعديل سالم: الكلمات اللي تبدّلت = اقتراحات للقاموس
    const norm = (t) => t.replace(/[ً-ْـ]/g, '').replace(/[^\p{L}\p{N}]/gu, '').toLowerCase();
    const trimP = (s) => s.replace(/^[^\p{L}\p{N}]+|[^\p{L}\p{N}]+$/gu, '');
    function suggestions(before, after) {
        const a = before.split(/\s+/).filter(Boolean), b = after.split(/\s+/).filter(Boolean);
        if (!a.length || !b.length || a.length > 1500 || b.length > 1500) return [];
        const na = a.map(norm), nb = b.map(norm), n = a.length, m = b.length;
        const L = Array.from({ length: n + 1 }, () => new Uint16Array(m + 1));
        for (let i = n - 1; i >= 0; i--) for (let j = m - 1; j >= 0; j--) L[i][j] = na[i] === nb[j] ? L[i + 1][j + 1] + 1 : Math.max(L[i + 1][j], L[i][j + 1]);
        const out = []; let i = 0, j = 0, del = [], ins = [];
        const close = () => {
            if (del.length && ins.length && del.length <= 4 && ins.length <= 4) {
                const wrong = trimP(del.join(' ')), right = trimP(ins.join(' '));
                if (wrong && right && norm(wrong) !== norm(right) && !out.some((p) => p.wrong === wrong && p.right === right)) out.push({ wrong, right });
            }
            del = []; ins = [];
        };
        while (i < n || j < m) {
            if (i < n && j < m && na[i] === nb[j]) { close(); i++; j++; }
            else if (j < m && (i >= n || L[i][j + 1] >= L[i + 1][j])) ins.push(b[j++]);
            else del.push(a[i++]);
        }
        close();
        return out.slice(0, 30);
    }

    // ---- العرض
    const BADGE = { queued: ['wait', '⏳ ينتظر الماك'], transcribing: ['wait', '✍️ يحوّل لنص…'], done: ['done', '✓ جاهز'], failed: ['fail', 'ما نجح'] };
    function render() {
        const box = $('notes');
        const active = document.activeElement && document.activeElement.closest && document.activeElement.closest('.note');
        if (active) return; // ما نعيد الرسم وسالم يكتب
        box.innerHTML = '';
        waiting.forEach((t) => box.insertAdjacentHTML('beforeend', '<div class="note"><div class="meta"><span>' + new Date(t.at).toLocaleTimeString('ar-OM', { hour: '2-digit', minute: '2-digit' }) + '</span><span class="badge wait">📱 محفوظ بجهازك، ينتظر الرفع</span></div></div>'));
        if (!notes.length && !waiting.length) { box.innerHTML = '<div class="empty">ما في ملاحظات بعد. اضغط «ابدأ الكلام».</div>'; return; }
        notes.forEach((n) => {
            const [cls, label] = BADGE[n.status] || ['', n.status];
            const el = document.createElement('div');
            el.className = 'note';
            el.dataset.id = n.id;
            el.innerHTML = '<div class="meta"><span><button type="button" class="del" aria-label="احذف الملاحظة">🗑 حذف</button> ' + esc(n.created_at) + (n.duration ? ' · ' + Math.round(n.duration) + ' ث' : '') + (n.language === 'en' ? ' · English' : '') + '</span><span class="badge ' + cls + '">' + label + (n.approved ? ' · 🎯 درس' : '') + '</span></div>';
            el.querySelector('.del').addEventListener('click', async (e) => {
                if (!confirm('تحذف هذي الملاحظة وتسجيلها؟ ما ترجع بعد الحذف.')) return;
                e.target.disabled = true;
                try { await api('/notes/' + n.id, { method: 'DELETE' }); notes = notes.filter((x) => x.id !== n.id); delete drafts[n.id]; el.remove(); refresh(); }
                catch (err) { e.target.disabled = false; setStatus('ما انحذفت: ' + err.message, 'err'); }
            });
            if (n.status === 'done') {
                const ta = document.createElement('textarea');
                ta.value = drafts[n.id] ?? n.text ?? '';
                ta.dir = n.language === 'en' ? 'ltr' : 'rtl';
                ta.addEventListener('input', () => { drafts[n.id] = ta.value; });
                el.appendChild(ta);
                const actions = document.createElement('div');
                actions.className = 'actions';
                actions.innerHTML = '<button type="button" class="copy">نسخ</button><button type="button" class="primary save">' + (n.approved ? 'حفظ التعديل' : '✓ النص صحيح / احفظ') + '</button><button type="button" class="listen">🔊 تسجيلي</button>';
                actions.querySelector('.copy').addEventListener('click', async (e) => { try { await navigator.clipboard.writeText(ta.value); e.target.textContent = '✓ انسخ'; } catch (_) { ta.select(); } });
                actions.querySelector('.save').addEventListener('click', () => review(n, ta.value, el));
                actions.querySelector('.listen').addEventListener('click', (e) => { e.target.replaceWith(Object.assign(document.createElement('audio'), { controls: true, autoplay: true, src: base + '/notes/' + n.id + '/audio' })); });
                el.appendChild(actions);
            } else if (n.status === 'failed') {
                el.insertAdjacentHTML('beforeend', '<div class="status err">' + esc(n.error || 'خطأ في التحويل') + '</div>');
                const b = Object.assign(document.createElement('button'), { type: 'button', className: 'chip-btn', textContent: 'جرّب مرة ثانية' });
                b.addEventListener('click', async () => { await api('/notes/' + n.id + '/retry', { method: 'POST' }); refresh(); });
                el.appendChild(b);
            }
            box.appendChild(el);
        });
    }

    function review(n, text, el) {
        const old = el.querySelector('.learn'); if (old) old.remove();
        const pairs = n.machine_text ? suggestions(n.machine_text, text) : [];
        const box = document.createElement('div');
        box.className = 'learn';
        if (pairs.length) {
            box.innerHTML = '<span>لقيت ' + pairs.length + ' تصحيح. تبي أتذكرها المرة الجاية؟</span>' + pairs.map((p, i) => '<label><input type="checkbox" checked data-i="' + i + '"><s>' + esc(p.wrong) + '</s> ← <b>' + esc(p.right) + '</b></label>').join('');
        } else {
            box.innerHTML = '<span>ما في كلمات متبدّلة أتعلّمها، بحفظ النص بس.</span>';
        }
        const go = Object.assign(document.createElement('button'), { type: 'button', className: 'chip-btn', textContent: pairs.length ? 'احفظ وتعلّم' : 'احفظ' });
        go.addEventListener('click', async () => {
            const learn = [...box.querySelectorAll('input:checked')].map((c) => pairs[Number(c.dataset.i)]);
            go.disabled = true;
            try {
                const out = await api('/notes/' + n.id + '/text', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify({ text, learn }) });
                delete drafts[n.id];
                box.innerHTML = '<span class="status ok">✓ انحفظ' + (out.learned ? '، وتعلّمت ' + out.learned + ' تصحيح.' : '.') + '</span>';
                setTimeout(() => { document.activeElement && document.activeElement.blur(); refresh(); }, 1500);
            } catch (e) { go.disabled = false; box.insertAdjacentHTML('beforeend', '<span class="status err">ما انحفظ: ' + esc(e.message) + '</span>'); }
        });
        box.appendChild(go);
        el.appendChild(box);
    }

    function renderDict() {
        const mine = dictionary.filter((d) => d.language === lang);
        $('dict-count').textContent = '(' + mine.length + ')';
        $('chips').innerHTML = mine.length ? '' : '<span class="empty">فاضي. يتعبى من تصحيحاتك أو تضيف بيدك.</span>';
        mine.forEach((d) => {
            const c = document.createElement('span');
            c.className = 'chip';
            c.innerHTML = (d.kind === 'correction' ? '<s style="color:var(--warn)">' + esc(d.wrong) + '</s> ← ' : '') + esc(d.right) + '<button type="button" aria-label="احذف">×</button>';
            c.querySelector('button').addEventListener('click', async () => { await api('/dictionary/' + d.id, { method: 'DELETE' }); refresh(); });
            $('chips').appendChild(c);
        });
    }
    const addEntry = (form, kind) => form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        try {
            await api('/dictionary', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify({ kind, language: lang, wrong: fd.get('wrong') || '', right: fd.get('right') }) });
            form.reset(); refresh();
        } catch (err) { setStatus('ما انضاف: ' + err.message, 'err'); }
    });
    addEntry($('add-term'), 'term');
    addEntry($('add-fix'), 'correction');

    // ---- تحديث: كل 4 ثواني لو في شي ينتظر، وإلا كل 30
    let timerRefresh = null;
    async function refresh() {
        clearTimeout(timerRefresh);
        try {
            const out = await api('/notes');
            notes = out.notes; dictionary = out.dictionary;
            if (out.training) { const t = out.training; $('train-label').textContent = t.minutes + ' من ' + t.goal + ' دقيقة · ' + t.notes + ' درس'; $('train-bar').style.width = Math.min(100, t.minutes / t.goal * 100) + '%'; if (t.minutes >= t.goal) $('train-note').textContent = '✓ تجمّع كفاية من صوتك. الخطوة الجاية: تدريب النموذج على نطقك.'; }
            render(); renderDict();
        } catch (e) { /* النت مقطوع: نحاول بعدين */ }
        const pending = waiting.length || notes.some((n) => n.status === 'queued' || n.status === 'transcribing');
        timerRefresh = setTimeout(refresh, pending ? 4000 : 30000);
    }
    document.addEventListener('focusout', () => setTimeout(render, 200));
    flush();
})();
</script>
</body>
</html>
