@php
    $t = fn (string $ar, string $enText) => $en ? $enText : $ar;
    $name = $en ? ($guest['name_en'] ?? $guest['name_ar']) : ($guest['name_ar'] ?? $guest['name_en']);
@endphp
<!doctype html>
<html lang="{{ $en ? 'en' : 'ar' }}" dir="{{ $en ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $t('رفع الإجابات', 'Upload your answers') }} — ALYASI</title>
    <style>
        :root {
            --navy: #0B1F3A;
            --card: #15294a;
            --border: rgba(255,255,255,.10);
            --text: #eef2f8;
            --muted: rgba(238,242,248,.62);
            --gold: #d8b56a;
            --ok: #6fd39a;
            --err: #ff9a8a;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            background: var(--navy);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Tahoma, Arial, sans-serif;
        }

        .wrap { max-width: 720px; margin: 0 auto; padding: 24px 16px 80px; }

        h1 { font-size: 20px; margin: 0 0 6px; color: var(--gold); }

        .intro { margin: 0 0 20px; font-size: 14px; line-height: 1.7; color: var(--muted); }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .card__num { font-size: 12px; color: var(--gold); margin-bottom: 6px; }

        .card__text { margin: 0 0 12px; font-size: 15px; line-height: 1.7; }

        .pick {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 10px;
            border: 1px solid rgba(216,181,106,.5);
            background: rgba(216,181,106,.12);
            color: var(--gold);
            font: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .pick input { display: none; }

        .files { list-style: none; margin: 10px 0 0; padding: 0; }

        .files li { font-size: 13px; padding: 6px 0; border-top: 1px solid var(--border); word-break: break-word; }

        .files .is-ok { color: var(--ok); }

        .files .is-error { color: var(--err); }

        progress { width: 100%; height: 6px; margin-top: 4px; accent-color: var(--gold); }

        .note { font-size: 12px; color: var(--muted); line-height: 1.7; margin-top: 18px; }
    </style>
</head>
<body>
<main class="wrap">
    <h1>{{ $t('رفع إجاباتك المسجّلة', 'Upload your recorded answers') }}</h1>
    <p class="intro">
        {{ $t('أهلًا '.$name.'، اختر ملف التسجيل تحت كل سؤال، ويوصل مباشرة. شكرًا لوقتك.',
              'Hi '.$name.', for each question tap “Choose file” and pick the recording from your phone. It uploads straight away. Thank you for your time!') }}
    </p>

    @foreach ($questions as $i => $q)
        <section class="card" data-question="{{ $q['id'] }}">
            <div class="card__num">{{ $t('السؤال '.($i + 1), 'Question '.($i + 1)) }}</div>
            <p class="card__text">{{ $q['text'] }}</p>
            <label class="pick">
                <input type="file" accept="audio/*,video/*,.m4a,.mp3,.wav,.aac,.ogg,.opus,.amr,.3gp,.mov,.mp4,.flac" multiple>
                {{ $t('اختر الملف', 'Choose file') }}
            </label>
            <ul class="files"></ul>
        </section>
    @endforeach

    <section class="card" data-question="{{ $otherId }}">
        <div class="card__num">{{ $t('ملفات أخرى', 'Other files') }}</div>
        <p class="card__text">{{ $t('عندك ملف لكل الإجابات مع بعض، أو مب متأكد لأي سؤال؟ ارفعه هنا.',
                                     'Have one file with all your answers, or not sure which question it belongs to? Upload it here.') }}</p>
        <label class="pick">
            <input type="file" accept="audio/*,video/*,.m4a,.mp3,.wav,.aac,.ogg,.opus,.amr,.3gp,.mov,.mp4,.flac" multiple>
            {{ $t('اختر الملفات', 'Choose files') }}
        </label>
        <ul class="files"></ul>
    </section>

    <p class="note">
        {{ $t('الحد الأقصى 95MB للملف. ملفاتك توصل لسالم الحجري فقط، وما تُنشر إلا بعد موافقتك.',
              'Maximum 95 MB per file. Your files go to Salem Al Hajri only and will not be published without your approval.') }}
    </p>
</main>

<script>
    (() => {
        const en = @json($en);
        const T = (ar, enText) => (en ? enText : ar);
        const endpoint = @json(route('tools.cyberx-interview.answers'));
        const maxBytes = @json($maxBytes);
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        // فتح الصفحة يُحسب فتح لرابط الضيف (مهلة الأيام العشرة).
        try {
            const body = new FormData();
            body.append('type', 'guest_open');
            body.append('path', location.pathname);
            body.append('label', @json($guest['id']));
            if (!(navigator.sendBeacon && navigator.sendBeacon('/analytics/event', body))) {
                fetch('/analytics/event', { method: 'POST', body, keepalive: true, credentials: 'same-origin' }).catch(() => {});
            }
        } catch (error) {
            // القياس ما يوقف الصفحة.
        }

        const errorText = (status, message) => {
            if (status === 413 || status === 422 && /audio/.test(message)) return T('الملف كبير جدًا', 'File is too large');
            if (status === 422) return T('نوع الملف غير مدعوم', 'This file type is not supported');
            if (status === 410) return T('انتهت صلاحية الرابط', 'This link has expired');
            if (status === 429) return T('محاولات كثيرة، انتظر دقيقة', 'Too many uploads, please wait a minute');
            return T('تعذّر الرفع', 'Upload failed') + (status ? ' (' + status + ')' : '');
        };

        // الرفع بـ XHR عشان شريط التقدّم (ملفات الجوال ممكن تكون كبيرة).
        const send = (file, questionId, row) => new Promise((resolve) => {
            const bar = document.createElement('progress');
            bar.max = 100;
            bar.value = 0;
            row.appendChild(bar);

            const body = new FormData();
            body.append('audio', file, file.name);
            body.append('question_id', questionId);
            body.append('source', 'upload');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', endpoint);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.upload.onprogress = (e) => { if (e.lengthComputable) bar.value = Math.round(e.loaded / e.total * 100); };

            const done = (ok, text) => {
                bar.remove();
                row.querySelector('.state').textContent = text;
                row.classList.add(ok ? 'is-ok' : 'is-error');
                if (!ok) {
                    const retry = document.createElement('button');
                    retry.type = 'button';
                    retry.className = 'pick';
                    retry.style.marginTop = '6px';
                    retry.textContent = T('🔁 إعادة المحاولة', '🔁 Try again');
                    retry.addEventListener('click', () => {
                        retry.remove();
                        row.classList.remove('is-error');
                        row.querySelector('.state').textContent = T('⏳ جاري الرفع…', '⏳ Uploading…');
                        send(file, questionId, row).then(resolve);
                    });
                    row.appendChild(retry);
                }
                resolve(ok);
            };

            xhr.onload = () => {
                let result = {};
                try { result = JSON.parse(xhr.responseText); } catch (e) {}
                const ok = xhr.status >= 200 && xhr.status < 300 && result.success;
                done(ok, ok ? T('✅ وصل، شكرًا', '✅ Received — thank you!')
                    : '⚠️ ' + errorText(xhr.status, JSON.stringify(result.errors || result.message || '')));
            };
            xhr.onerror = () => done(false, '⚠️ ' + T('انقطع الاتصال', 'Connection lost'));
            xhr.send(body);
        });

        document.querySelectorAll('[data-question]').forEach((card) => {
            const input = card.querySelector('input[type=file]');
            const list = card.querySelector('.files');

            input.addEventListener('change', async () => {
                const files = [...input.files];
                input.value = '';

                // واحد ورا الثاني عشان ما نضغط على النت ولا نتجاوز حد الطلبات.
                for (const file of files) {
                    const row = document.createElement('li');
                    row.innerHTML = '<span class="name"></span> — <span class="state"></span>';
                    row.querySelector('.name').textContent = file.name;
                    list.appendChild(row);

                    if (file.size > maxBytes) {
                        row.querySelector('.state').textContent = '⚠️ ' + T('الملف أكبر من 95MB', 'File is larger than 95 MB');
                        row.classList.add('is-error');
                        continue;
                    }

                    row.querySelector('.state').textContent = T('⏳ جاري الرفع…', '⏳ Uploading…');
                    await send(file, card.dataset.question, row);
                }
            });
        });
    })();
</script>
</body>
</html>
