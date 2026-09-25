<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>أسئلة مقابلات CyberX — ALYASI</title>
    <style>
        :root {
            --navy: #0B1F3A;
            --navy-2: #12294d;
            --gold: #d8b56a;
            --card: #15294a;
            --border: rgba(255,255,255,.10);
            --text: #eef2f8;
            --muted: rgba(238,242,248,.62);
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            background: var(--navy);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Tahoma, Arial, sans-serif;
        }

        .wrap {
            max-width: 720px;
            margin: 0 auto;
            padding: 20px 16px 80px;
        }

        header.top {
            position: sticky;
            top: 0;
            z-index: 5;
            background: var(--navy);
            padding: 14px 0 10px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 16px;
        }

        header.top h1 {
            font-size: 18px;
            margin: 0 0 4px;
            color: var(--gold);
        }

        header.top p {
            margin: 0;
            font-size: 13px;
            color: var(--muted);
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 14px;
        }

        .card .label {
            font-size: 13px;
            font-weight: 700;
            color: var(--gold);
            margin-bottom: 8px;
        }

        .card .q {
            font-size: 15px;
            line-height: 1.7;
            margin: 0 0 6px;
        }

        .card .q.en {
            direction: ltr;
            text-align: left;
            color: var(--muted);
            font-size: 14px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        button, .btn {
            appearance: none;
            border: 1px solid var(--border);
            background: rgba(255,255,255,.06);
            color: var(--text);
            font-size: 14px;
            font-weight: 600;
            border-radius: 10px;
            padding: 10px 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            touch-action: manipulation;
        }

        button.play {
            background: rgba(216,181,106,.14);
            border-color: rgba(216,181,106,.4);
        }

        button.play.restart {
            padding-inline: 10px;
            opacity: .8;
            font-size: 16px;
        }

        button.record {
            background: rgba(220,70,70,.14);
            border-color: rgba(220,70,70,.4);
        }

        button.record.is-recording {
            background: #c53030;
            border-color: #c53030;
            animation: pulse 1s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .6; }
        }

        button:disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        .answer {
            margin-top: 10px;
            display: none;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .answer.is-visible { display: flex; }

        .answer audio {
            max-width: 100%;
            height: 34px;
        }

        .divider {
            text-align: center;
            color: var(--muted);
            font-size: 12px;
            margin: 24px 0 10px;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .hint {
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <header class="top">
            <h1>أسئلة مقابلات CyberX عُمان 2026</h1>
            <p>شغّل السؤال المسجّل، وسجّل إجابة الضيف من نفس الصفحة — بدون رفع لأي سيرفر.</p>
        </header>

        <div id="list"></div>
    </div>

    <script>
        const questions = @json($questions);
        const baseUrl = @json(asset('audio/cyberx-interview/'));
        const list = document.getElementById('list');

        let lastLabel = null;

        questions.forEach((q) => {
            if (q.label !== lastLabel) {
                const divider = document.createElement('div');
                divider.className = 'divider';
                divider.textContent = q.label;
                list.appendChild(divider);
                lastLabel = q.label;
            }

            const card = document.createElement('div');
            card.className = 'card';

            const label = document.createElement('div');
            label.className = 'label';
            label.textContent = q.id;
            card.appendChild(label);

            if (q.ar) {
                const p = document.createElement('p');
                p.className = 'q ar';
                p.textContent = q.ar;
                card.appendChild(p);
            }

            if (q.en) {
                const p = document.createElement('p');
                p.className = 'q en';
                p.textContent = q.en;
                card.appendChild(p);
            }

            const actions = document.createElement('div');
            actions.className = 'actions';

            if (q.arFile) {
                makePlayGroup(actions, 'عربي', baseUrl + '/' + q.arFile);
            }
            if (q.enFile) {
                makePlayGroup(actions, 'English', baseUrl + '/' + q.enFile);
            }

            const recordBtn = document.createElement('button');
            recordBtn.className = 'record';
            recordBtn.textContent = '🎙 تسجيل الإجابة';
            actions.appendChild(recordBtn);

            card.appendChild(actions);

            const answerRow = document.createElement('div');
            answerRow.className = 'answer';
            card.appendChild(answerRow);

            wireRecorder(recordBtn, answerRow, q.id);

            list.appendChild(card);
        });

        // مشغّل واحد نشط بكل الصفحة -- قبل ما نشغّل أي مقطع جديد نوقف أي
        // مقطع ثاني شغّال (بدون ما نصفّر مكانه، زي ضغط زر الإيقاف نفسه)،
        // عشان ما تتداخل الأصوات لو ضغط المستخدم زرين بالغلط.
        let activePlayer = null;

        // زر تشغيل/إيقاف مؤقت -- الإيقاف يحفظ مكان التوقف (يكمل من نفس
        // النقطة عند الضغط ثانية)، وزر الإعادة المنفصل (⟲) هو الوحيد
        // اللي يرجّع الصوت لبدايته من الصفر.
        function makePlayGroup(actions, label, src) {
            const playBtn = document.createElement('button');
            playBtn.className = 'play';
            playBtn.textContent = '▶ ' + label;

            const restartBtn = document.createElement('button');
            restartBtn.className = 'play restart';
            restartBtn.textContent = '⟲';
            restartBtn.title = 'ابدأ من الصفر';

            let audio = null;

            const setPaused = () => {
                playBtn.textContent = '▶ ' + label;
                if (activePlayer === pauseSelf) activePlayer = null;
            };
            const setPlaying = () => {
                playBtn.textContent = '⏸ ' + label;
                activePlayer = pauseSelf;
            };
            const pauseSelf = () => {
                if (audio && !audio.paused) audio.pause();
                setPaused();
            };

            const ensureAudio = () => {
                if (!audio) {
                    audio = new Audio(src);
                    audio.addEventListener('ended', setPaused);
                }
                return audio;
            };

            const startPlayback = () => {
                if (activePlayer && activePlayer !== pauseSelf) activePlayer();
                audio.play().then(setPlaying).catch(() => {
                    alert('تعذّر تشغيل المقطع.');
                });
            };

            playBtn.addEventListener('click', () => {
                ensureAudio();
                if (!audio.paused) {
                    pauseSelf();
                    return;
                }
                startPlayback();
            });

            restartBtn.addEventListener('click', () => {
                ensureAudio();
                audio.currentTime = 0;
                startPlayback();
            });

            actions.appendChild(playBtn);
            actions.appendChild(restartBtn);
        }

        function wireRecorder(button, answerRow, questionId) {
            let mediaRecorder = null;
            let chunks = [];
            let stream = null;

            button.addEventListener('click', async () => {
                if (button.classList.contains('is-recording')) {
                    mediaRecorder.stop();
                    return;
                }

                try {
                    stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                } catch (err) {
                    alert('ما قدرت أوصل للمايك. تأكد من صلاحية الوصول.');
                    return;
                }

                chunks = [];
                mediaRecorder = new MediaRecorder(stream);

                mediaRecorder.addEventListener('dataavailable', (e) => {
                    if (e.data.size > 0) chunks.push(e.data);
                });

                mediaRecorder.addEventListener('stop', () => {
                    stream.getTracks().forEach((t) => t.stop());

                    const blob = new Blob(chunks, { type: mediaRecorder.mimeType || 'audio/webm' });
                    const url = URL.createObjectURL(blob);
                    const ext = (blob.type.split('/')[1] || 'webm').split(';')[0];

                    answerRow.innerHTML = '';

                    const audio = document.createElement('audio');
                    audio.controls = true;
                    audio.src = url;
                    answerRow.appendChild(audio);

                    const download = document.createElement('a');
                    download.className = 'btn';
                    download.href = url;
                    download.download = 'answer-' + questionId + '.' + ext;
                    download.textContent = '⬇ حفظ';
                    answerRow.appendChild(download);

                    answerRow.classList.add('is-visible');

                    button.textContent = '🎙 إعادة التسجيل';
                    button.classList.remove('is-recording');
                });

                mediaRecorder.start();
                button.textContent = '⏹ إيقاف';
                button.classList.add('is-recording');
            });
        }
    </script>
</body>
</html>
