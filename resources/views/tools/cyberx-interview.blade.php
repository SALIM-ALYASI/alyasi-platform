<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>أسئلة مقابلات CyberX — ALYASI</title>
    <style>
        :root {
            --navy: #0B1F3A;
            --card: #15294a;
            --border: rgba(255,255,255,.10);
            --text: #eef2f8;
            --muted: rgba(238,242,248,.62);
            --gold: #d8b56a;
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

        .followups-bar[hidden] { display: none; }
        .followups-bar {
            background: rgba(216,181,106,.08);
            border: 1px solid rgba(216,181,106,.3);
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 20px;
        }

        .followups-bar .heading {
            font-size: 13px;
            font-weight: 700;
            color: var(--gold);
            margin-bottom: 8px;
        }

        .followups-bar .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        /* -- تبويب الضيوف -- اضغط اسم فتفتح مقاطعه هو بس، وتختفي البقية. */
        .tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 18px;
        }

        .tab-btn {
            border: 1px solid var(--border);
            background: rgba(255,255,255,.05);
            color: var(--text);
            font-size: 13px;
            font-weight: 700;
            border-radius: 999px;
            padding: 9px 16px;
            cursor: pointer;
            touch-action: manipulation;
        }

        .tab-btn.is-active {
            background: var(--gold);
            border-color: var(--gold);
            color: var(--navy);
        }

        .tab-panel { display: none; }
        .tab-panel.is-active { display: block; }

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
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .card .label .meta {
            font-weight: 400;
            color: var(--muted);
            direction: ltr;
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

        button.play.small {
            font-size: 13px;
            padding: 8px 12px;
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

        .send-status {
            width: 100%;
            font-size: 12px;
            color: var(--muted);
        }

        .send-status.is-ok { color: #48bb78; }
        .send-status.is-error { color: #f6ad55; }

        .divider {
            text-align: center;
            color: var(--muted);
            font-size: 12px;
            margin: 24px 0 10px;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .guest-heading {
            margin: 28px 0 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
        }

        .guest-heading .name {
            font-size: 17px;
            font-weight: 700;
        }

        .guest-heading .sub {
            font-size: 12px;
            color: var(--muted);
            margin-top: 2px;
        }

        .guest-heading .lang-note {
            display: inline-block;
            margin-top: 6px;
            font-size: 12px;
            font-weight: 700;
            color: var(--navy);
            background: var(--gold);
            border-radius: 999px;
            padding: 3px 10px;
        }

        .agenda-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

        .agenda-table td {
            padding: 8px 6px;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }

        .agenda-table td.time {
            white-space: nowrap;
            color: var(--muted);
            font-size: 12px;
            padding-inline-end: 10px;
        }

        .tabs-divider {
            width: 100%;
            font-size: 11px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin: 4px 0 -2px;
        }

        details.extra-questions {
            margin: 10px 0 14px;
        }

        details.extra-questions summary {
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            color: var(--gold);
            padding: 8px 0;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <header class="top">
            <h1>أسئلة مقابلات CyberX عُمان 2026</h1>
            <p>اضغط زر سريع فوق قبل ما تبدأ، وبعدها اختر الضيف — كل تبويب فيه أهم سؤالين بس.</p>
        </header>

        {{-- مخفي مؤقتًا: الضيوف يسجّلون من الرابط بأنفسهم -- شيل hidden لإرجاعه --}}
        <div class="followups-bar" hidden>
            <div class="heading">أزرار سريعة (تعريف · سؤال موحد · ختام)</div>
            <div class="actions" id="quick-buttons"></div>
        </div>

        <div class="followups-bar" hidden>
            <div class="heading">متابعات سريعة (أي وقت، أي ضيف)</div>
            <div class="actions" id="followups"></div>
        </div>

        <div class="tabs" id="tabs"></div>
        <div id="panels"></div>
    </div>

    <script>
        const data = @json($data);
        const baseUrl = @json(asset('audio/cyberx-interview-2026/'));
        const tabsBar = document.getElementById('tabs');
        const panelsWrap = document.getElementById('panels');

        // مشغّل واحد نشط بكل الصفحة -- قبل ما نشغّل أي مقطع جديد نوقف أي
        // مقطع ثاني شغّال (بدون ما نصفّر مكانه، زي ضغط زر الإيقاف نفسه)،
        // عشان ما تتداخل الأصوات لو ضغط المستخدم زرين بالغلط.
        let activePlayer = null;

        // كل التيارات (streams) المفتوحة حالياً للتسجيل -- تُستخدم لقفل
        // المايك قسراً لو المستخدم سكّر الصفحة أو بدّل تبويب المتصفح وهو
        // لسه مسجّل، بدل ما يضل المايك شغّال للأبد.
        const activeStreams = new Set();
        window.addEventListener('pagehide', () => {
            activeStreams.forEach((s) => s.getTracks().forEach((t) => t.stop()));
        });

        // -- شريط الأزرار السريعة (تعريف / سؤال موحد / ختام) -- تشغيل فوري
        //    بدون تسجيل، متاح دايمًا فوق بغض النظر عن التبويب المفتوح.
        //    يُعاد بناؤه بكل تبديل تبويب عشان يجيب النسخة المؤنثة الصح.
        const quickButtonsBar = document.getElementById('quick-buttons');
        function renderQuickButtons(feminine) {
            quickButtonsBar.innerHTML = '';
            (data.quick_buttons || []).forEach((qb) => {
                const group = document.createElement('div');
                group.style.display = 'flex';
                group.style.gap = '6px';
                group.style.alignItems = 'center';

                const tag = document.createElement('span');
                tag.textContent = qb.label || qb.audio;
                tag.style.fontSize = '12px';
                tag.style.color = 'var(--muted)';
                group.appendChild(tag);

                const isFeminineEligible = qb.audio !== 'shared-unified';
                makePlayGroup(group, 'عربي', audioUrl(qb.audio, 'ar', feminine && isFeminineEligible), 'small');
                makePlayGroup(group, 'EN', audioUrl(qb.audio, 'en'), 'small');

                quickButtonsBar.appendChild(group);
            });
        }
        renderQuickButtons(false);

        // -- شريط المتابعات السريعة (أعلى الصفحة) -- يُعاد بناؤه بكل تبديل
        //    تبويب عشان يجيب النسخة المؤنثة الصح لو الضيف الحالي بنت
        //    (نفس المقاطع مخاطبة بصيغة "أنت" فتحتاج صوت مختلف حسب الجنس).
        const followupsBar = document.getElementById('followups');
        function renderFollowups(feminine) {
            followupsBar.innerHTML = '';
            (data.followups || []).forEach((f) => {
                const group = document.createElement('div');
                group.style.display = 'flex';
                group.style.gap = '6px';
                group.style.alignItems = 'center';

                const tag = document.createElement('span');
                tag.textContent = f.label;
                tag.style.fontSize = '12px';
                tag.style.color = 'var(--muted)';
                group.appendChild(tag);

                makePlayGroup(group, 'عربي', audioUrl(f.audio, 'ar', feminine), 'small');
                makePlayGroup(group, 'EN', audioUrl(f.audio, 'en'), 'small');

                followupsBar.appendChild(group);
            });
        }
        renderFollowups(false);

        // كل ضيف (أولوية أو احتياط) بمكان واحد -- تُستخدم لبحث الاسم بتبويب
        // "الجدول" بدون ما نهتم من أي قائمة جاء الضيف.
        // ضيف عليه "hidden": true ما يطلع له تبويب ولا يظهر بالجدول -- يبقى
        // بالملف (ما ينحذف) عشان نرجّعه بسطر واحد لو تغيّرت الخطة.
        const isVisible = (guest) => !guest.hidden;
        data.priority_guests = (data.priority_guests || []).filter(isVisible);
        data.additional_guests = (data.additional_guests || []).filter(isVisible);

        const allGuests = [...data.priority_guests, ...data.additional_guests];

        // -- تبويب "عام": سؤال الجلسة القيادية فقط -- مقطع التعريف صار
        //    بالشريط السريع فوق، فما نكرره هنا. --
        makeTab('general', 'عام', false, (panel) => {
            addDivider(panel, 'سؤال الجلسة القيادية · ' + formatTimesInText(data.panel_question.session));
            addCard(panel, { id: 'panel_question', label: 'سؤال الجلسة', ar: data.panel_question.ar, en: data.panel_question.en },
                audioUrl(data.panel_question.audio, 'ar'), audioUrl(data.panel_question.audio, 'en'), true, 'panel');
        });

        // -- تبويب "الجدول": مرجع سريع لسالم -- متى يمسك كل ضيف (نوافذ
        //    المقابلات) وجدول اليوم الكامل، بدون أي تشغيل/تسجيل صوت. --
        if ((data.interview_windows && data.interview_windows.length) || (data.agenda && data.agenda.length)) {
            makeTab('schedule', 'الجدول', false, (panel) => {
                if (data.interview_windows && data.interview_windows.length) {
                    addDivider(panel, 'نوافذ المقابلات');
                    data.interview_windows.forEach((w) => {
                        const visibleGuests = w.guests
                            .map((gid) => allGuests.find((g) => g.id === gid))
                            .filter(Boolean);
                        if (!visibleGuests.length) return;

                        const names = visibleGuests.map((g) => g.name_ar).join('، ');

                        const card = document.createElement('div');
                        card.className = 'card';
                        card.innerHTML = '<div class="label"><span>' + w.label + '</span><span class="meta">' + formatTimesInText(w.time) + '</span></div>' +
                            '<p class="q" style="margin:0">' + names + '</p>';
                        panel.appendChild(card);
                    });
                }

                if (data.agenda && data.agenda.length) {
                    addDivider(panel, 'جدول اليوم الكامل');

                    const table = document.createElement('table');
                    table.className = 'agenda-table';
                    data.agenda.forEach((item) => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = '<td class="time">' + formatTimesInText(item.time) + '</td><td>' + item.ar + '</td>';
                        table.appendChild(tr);
                    });
                    panel.appendChild(table);
                }
            });
        }

        // -- تبويب لكل ضيف أولوية: سؤالين أساسيين + سؤال متابعة بس ظاهرين،
        //    وأي أسئلة احتياطية إضافية تنطوي تحت قائمة قابلة للفتح (ما
        //    تُحذف، بس ما تزاحم الشاشة وقت الزحمة). --
        (data.priority_guests || []).forEach((guest) => {
            const isFeminine = guest.gender === 'f';
            const showArabic = guest.language !== 'en';

            makeTab(guest.id, guest.name_ar, isFeminine, (panel) => {
                const heading = document.createElement('div');
                heading.className = 'guest-heading';
                heading.innerHTML = '<div class="name">' + guest.name_ar + '</div>' +
                    '<div class="sub">' + guest.name_en + ' · ' + guest.hint + ' · ' + formatTimeAr(guest.window) + '</div>';
                panel.appendChild(heading);

                // عنصر بـ label خاص (مثل "تعريفي") ياخذ عنوانه، والترقيم
                // "سؤال N" للأسئلة الفعلية بس.
                let coreNumber = 0;
                guest.core.forEach((q) => {
                    addCard(panel, { id: q.audio, label: q.label || 'سؤال ' + (++coreNumber), ar: q.ar, en: q.en },
                        audioUrl(q.audio, 'ar'), audioUrl(q.audio, 'en'), true, guest.id, showArabic);
                });

                if (guest.followup) {
                    addCard(panel, { id: guest.followup.audio, label: 'سؤال متابعة', ar: guest.followup.ar, en: guest.followup.en },
                        audioUrl(guest.followup.audio, 'ar'), audioUrl(guest.followup.audio, 'en'), true, guest.id, showArabic);
                }

                addFinalQuestion(panel, guest, isFeminine, showArabic);

                if (guest.extra && guest.extra.length) {
                    const details = document.createElement('details');
                    details.className = 'extra-questions';

                    const summary = document.createElement('summary');
                    summary.textContent = 'أسئلة احتياطية إضافية (' + guest.extra.length + ')';
                    details.appendChild(summary);

                    guest.extra.forEach((q, idx) => {
                        addCard(details, { id: q.audio, label: 'احتياطي ' + (idx + 1), ar: q.ar, en: q.en },
                            audioUrl(q.audio, 'ar'), audioUrl(q.audio, 'en'), true, guest.id, showArabic);
                    });

                    panel.appendChild(details);
                }
            });
        });

        // -- فاصل بصري بس بشريط التبويبات، يفصل ضيوف الأولوية عن ضيوف
        //    الاحتياط بدون ما يكون تبويب قابل للضغط. --
        if ((data.additional_guests || []).length) {
            const divider = document.createElement('div');
            divider.className = 'tabs-divider';
            divider.textContent = 'ضيوف احتياط';
            tabsBar.appendChild(divider);
        }

        // -- تبويب لكل ضيف احتياط: كل أسئلته زي ما هي، بدون تقليص --
        (data.additional_guests || []).forEach((guest) => {
            const isEnglishOnly = guest.language === 'en';
            const isFeminine = guest.gender === 'f';
            const showArabic = !isEnglishOnly;
            const tabLabel = isEnglishOnly ? guest.name_en : guest.name_ar;

            makeTab(guest.id, tabLabel, isFeminine, (panel) => {
                const heading = document.createElement('div');
                heading.className = 'guest-heading';
                heading.innerHTML = isEnglishOnly
                    ? '<div class="name">' + guest.name_en + '</div>' +
                      '<div class="sub">' + guest.hint + ' · ' + formatTimeAr(guest.window) + '</div>' +
                      '<div class="lang-note">🌐 أجنبي — استخدم زر English بس معاه</div>'
                    : '<div class="name">' + guest.name_ar + '</div>' +
                      '<div class="sub">' + guest.name_en + ' · ' + guest.hint + ' · ' + formatTimeAr(guest.window) + '</div>';
                panel.appendChild(heading);

                let questionNumber = 0;
                guest.questions.forEach((q) => {
                    addCard(panel, { id: q.audio, label: q.label || 'سؤال ' + (++questionNumber), ar: q.ar, en: q.en },
                        audioUrl(q.audio, 'ar'), audioUrl(q.audio, 'en'), true, guest.id, showArabic);
                });

                addFinalQuestion(panel, guest, isFeminine, showArabic);

                // نفس قائمة الأسئلة الاحتياطية المطوية عند ضيوف الأولوية.
                if (guest.extra && guest.extra.length) {
                    const details = document.createElement('details');
                    details.className = 'extra-questions';

                    const summary = document.createElement('summary');
                    summary.textContent = 'أسئلة احتياطية إضافية (' + guest.extra.length + ')';
                    details.appendChild(summary);

                    guest.extra.forEach((q, idx) => {
                        addCard(details, { id: q.audio, label: 'احتياطي ' + (idx + 1), ar: q.ar, en: q.en },
                            audioUrl(q.audio, 'ar'), audioUrl(q.audio, 'en'), true, guest.id, showArabic);
                    });

                    panel.appendChild(details);
                }
            });
        });

        // أول تبويب مفعّل تلقائياً
        if (tabsBar.firstElementChild) tabsBar.firstElementChild.click();

        function makeTab(id, label, isFeminine, build) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'tab-btn';
            btn.textContent = label;

            const panel = document.createElement('div');
            panel.className = 'tab-panel';
            panel.dataset.tab = id;

            btn.addEventListener('click', () => {
                // نوقف أي مقطع شغّال بتبويب ثاني قبل ما نبدّل -- بدون هذا
                // يضل يشتغل بالخلفية خلف تبويب مختلف تمامًا عن الضيف الحالي.
                if (activePlayer) activePlayer();

                [...tabsBar.children].forEach((b) => b.classList.remove('is-active'));
                [...panelsWrap.children].forEach((p) => p.classList.remove('is-active'));
                btn.classList.add('is-active');
                panel.classList.add('is-active');
                renderQuickButtons(isFeminine);
                renderFollowups(isFeminine);
                window.scrollTo({ top: 0, behavior: 'auto' });
            });

            tabsBar.appendChild(btn);
            panelsWrap.appendChild(panel);
            build(panel);
        }

        // المقاطع المخاطبة لشخص واحد بصيغة "أنت" (مقطع التعريف، تعريف
        // الضيف، الختام، والمتابعات) لها نسخة مؤنثة منفصلة (لاحقة -f) عشان
        // صيغة الخطاب تكون صحيحة نحويًا لو الضيفة بنت -- ملفات الأسئلة
        // الخاصة بكل ضيف مكتوبة أصلًا بصيغته الصحيحة فما تحتاج هذا.
        // يرفع الإجابة للسيرفر فور الإيقاف: تنحفظ نسخة (ما تضيع لو انسكرت
        // الصفحة) وتوصل لسالم بالواتساب المجاني مع السؤال. زر "حفظ" المحلي
        // يبقى كاحتياط لو فشل الرفع (مثلاً انقطاع النت بالقاعة).
        async function sendAnswer(blob, questionId, meta, answerRow) {
            const status = document.createElement('div');
            status.className = 'send-status';
            status.textContent = '⏳ جاري الإرسال للواتساب…';
            answerRow.appendChild(status);

            const body = new FormData();
            const ext = (blob.type.split('/')[1] || 'webm').split(';')[0];
            body.append('audio', blob, 'answer-' + questionId + '.' + ext);
            body.append('question_id', questionId);
            body.append('guest', (meta && meta.guest) || '');
            body.append('question', (meta && meta.question) || '');

            try {
                const response = await fetch(@json(route('tools.cyberx-interview.answers')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body,
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok || !result.success) throw new Error(result.message || ('HTTP ' + response.status));

                status.textContent = '✅ انحفظت وانرسلت للواتساب';
                status.classList.add('is-ok');
            } catch (err) {
                status.textContent = '⚠️ ما انرسلت (' + err.message + ') — اضغط ⬇ حفظ عشان ما تضيع';
                status.classList.add('is-error');

                const retry = document.createElement('button');
                retry.type = 'button';
                retry.className = 'btn';
                retry.textContent = '🔁 إعادة الإرسال';
                retry.addEventListener('click', () => {
                    status.remove();
                    retry.remove();
                    sendAnswer(blob, questionId, meta, answerRow);
                });
                answerRow.appendChild(retry);
            }
        }

        // السؤال الأخير المشترك (زر "السؤال الأخير" بالشريط السريع) كبطاقة
        // أخيرة داخل تبويب الضيف -- للضيوف اللي عندهم final_question بس.
        // نفس التسجيل، والنسخة المؤنثة تلقائيًا للضيفات.
        function addFinalQuestion(panel, guest, isFeminine, showArabic) {
            if (!guest.final_question) return;

            const finalQuestion = (data.quick_buttons || []).find((qb) => qb.audio === 'shared-last');
            if (!finalQuestion) return;

            addCard(panel, { id: guest.id + '-last', label: 'السؤال الأخير', ar: finalQuestion.ar, en: finalQuestion.en },
                audioUrl(finalQuestion.audio, 'ar', isFeminine), audioUrl(finalQuestion.audio, 'en'), true, guest.id, showArabic);
        }

        function audioUrl(id, lang, feminine) {
            const suffix = (feminine && lang === 'ar') ? '-f' : '';
            return baseUrl + '/' + id + '-' + lang + suffix + '.wav';
        }

        // يحوّل "HH:MM" من صيغة الـ24 ساعة لصيغة صباحًا/ظهرًا/مساءً، عشان
        // سالم ما يحتاج يحسب بعقله وقت الأوقات بعد الظهر (13:00 وما فوق).
        function formatTimeAr(hhmm) {
            const [h, m] = hhmm.split(':').map(Number);
            const period = h < 12 ? 'صباحًا' : (h < 13 ? 'ظهرًا' : 'مساءً');
            let h12 = h % 12;
            if (h12 === 0) h12 = 12;
            return String(h12).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ' ' + period;
        }

        // يستبدل كل وقت HH:MM جوّا أي نص (وقت مفرد أو مدى "HH:MM – HH:MM"
        // أو جملة كاملة فيها وقت) بصيغته المحوّلة، بدون ما يلمس باقي النص.
        function formatTimesInText(str) {
            return str.replace(/\b\d{1,2}:\d{2}\b/g, (match) => formatTimeAr(match));
        }

        function addDivider(container, text) {
            const divider = document.createElement('div');
            divider.className = 'divider';
            divider.textContent = text;
            container.appendChild(divider);
        }

        function addCard(container, q, arSrc, enSrc, withRecord, recordPrefix, showArabic, contextLabel) {
            showArabic = showArabic !== false;

            const card = document.createElement('div');
            card.className = 'card';

            const label = document.createElement('div');
            label.className = 'label';
            // نضيف اسم الضيف كـ"meta" للمقاطع المشتركة (تعريف الضيف/الموحد)
            // اللي تتكرر نسخة مستقلة منها بكل تبويب -- بدون هذا يصعب تمييز
            // تسجيل مين هو وأنت تراجع التبويبات بسرعة أثناء المقابلة.
            label.innerHTML = '<span>' + (q.label || q.id) + '</span>' +
                (contextLabel ? '<span class="meta">' + contextLabel + '</span>' : '');
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

            if (showArabic) makePlayGroup(actions, 'عربي', arSrc);
            makePlayGroup(actions, 'English', enSrc);

            if (withRecord) {
                const recordBtn = document.createElement('button');
                recordBtn.className = 'record';
                recordBtn.textContent = '🎙 تسجيل الإجابة';
                actions.appendChild(recordBtn);

                card.appendChild(actions);

                const answerRow = document.createElement('div');
                answerRow.className = 'answer';
                card.appendChild(answerRow);

                const guest = allGuests.find((g) => g.id === recordPrefix);
                wireRecorder(recordBtn, answerRow, (recordPrefix || q.id) + '-' + q.id, {
                    guest: guest ? guest.name_ar : (recordPrefix === 'panel' ? 'سؤال الجلسة القيادية' : ''),
                    question: q.ar || q.en || '',
                });
            } else {
                card.appendChild(actions);
            }

            container.appendChild(card);
        }

        // زر تشغيل/إيقاف مؤقت -- الإيقاف يحفظ مكان التوقف (يكمل من نفس
        // النقطة عند الضغط ثانية)، وزر الإعادة المنفصل (⟲) هو الوحيد
        // اللي يرجّع الصوت لبدايته من الصفر.
        function makePlayGroup(actions, label, src, size) {
            const playBtn = document.createElement('button');
            playBtn.className = 'play' + (size === 'small' ? ' small' : '');
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
                // نحجز activePlayer فوراً (مو بعد ما ينجح play()) -- لو
                // ضغط المستخدم زر تشغيل ثاني بالفترة القصيرة قبل ما يرجع
                // الـ promise، لازم الزر الثاني يشوف إنه فيه شي شغّال
                // بالفعل ويوقفه، بدل ما يشتغلون الاثنين بنفس الوقت.
                activePlayer = pauseSelf;
                playBtn.textContent = '⏸ ' + label;
                audio.play().catch(() => {
                    setPaused();
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

        function wireRecorder(button, answerRow, questionId, meta) {
            let mediaRecorder = null;
            let chunks = [];
            let stream = null;
            let starting = false;

            button.addEventListener('click', async () => {
                if (button.classList.contains('is-recording')) {
                    mediaRecorder.stop();
                    return;
                }

                // قفل فوري قبل أي await -- بدونه، ضغطة ثانية سريعة (لمس
                // بالغلط، أو انتظار إذن المايك) تعدي هذا الفحص وتبدأ تسجيل
                // ثاني فوق الأول قبل ما يوصل رد getUserMedia.
                if (starting) return;
                starting = true;

                try {
                    stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                } catch (err) {
                    starting = false;
                    alert('ما قدرت أوصل للمايك. تأكد من صلاحية الوصول.');
                    return;
                }

                activeStreams.add(stream);
                chunks = [];
                mediaRecorder = new MediaRecorder(stream);

                mediaRecorder.addEventListener('dataavailable', (e) => {
                    if (e.data.size > 0) chunks.push(e.data);
                });

                mediaRecorder.addEventListener('stop', () => {
                    stream.getTracks().forEach((t) => t.stop());
                    activeStreams.delete(stream);

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
                    sendAnswer(blob, questionId, meta, answerRow);

                    button.textContent = '🎙 إعادة التسجيل';
                    button.classList.remove('is-recording');
                });

                mediaRecorder.start();
                starting = false;
                button.textContent = '⏹ إيقاف';
                button.classList.add('is-recording');
            });
        }
    </script>
</body>
</html>
