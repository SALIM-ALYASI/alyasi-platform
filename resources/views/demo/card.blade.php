@php
    // نفس الصفحة بلغتين: /demo/card (عربي) و /en/demo/card (إنجليزي).
    $isEn = ($lang ?? 'ar') === 'en';
    $t = fn (string $ar, string $en) => $isEn ? $en : $ar;
@endphp
<!doctype html>
<html lang="{{ $isEn ? 'en' : 'ar' }}" dir="{{ $isEn ? 'ltr' : 'rtl' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>{{ $t('سايبر إكس عُمان 2026 · خمس محطات — ALYASI', 'CyberX Oman 2026 · Five Stops — ALYASI') }}</title>
<link rel="alternate" hreflang="ar" href="{{ url('/demo/card') }}">
<link rel="alternate" hreflang="en" href="{{ url('/en/demo/card') }}">
<meta name="description" content="{{ $t('من سايبر إكس عُمان 2026 في مسقط: خمس محطات عن الاستعداد للاختراق، والاستمرارية، وأمن القطاع الصحي، والذكاء الاصطناعي وحوكمته.', 'From CyberX Oman 2026 in Muscat: five stops on breach readiness, continuity, healthcare security, and AI and its governance.') }}">
<meta property="og:type" content="article">
<meta property="og:title" content="{{ $t('سايبر إكس عُمان 2026 · خمس محطات', 'CyberX Oman 2026 · Five Stops') }}">
<meta property="og:description" content="{{ $t('ماذا لو وقع الاختراق فعلًا؟ خمس محطات من المؤتمر — منصة الياسي', 'What if a breach actually happens? Five stops from the conference — ALYASI') }}">
<meta property="og:image" content="{{ asset('images/demo/hall-bg.jpg') }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta name="twitter:card" content="summary_large_image">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Figtree:wght@400;600;700;800&display=swap">
<style>
  :root {
    --bg: #0B1F3A; --band: rgba(19, 35, 63, .55); --panel: rgba(16, 32, 58, .62);
    --glass-line: rgba(255, 255, 255, .18); --tx-on-band: #FFFFFF; --mu-on-band: #DCE4F1; --gold: #D8B56A;
  }
  * { box-sizing: border-box; }
  html { background: var(--bg); }
  html, body { margin: 0; font-family: Tajawal, system-ui, sans-serif; }
  html[lang="en"] body { font-family: Figtree, system-ui, sans-serif; }
  .lang-switch { position: absolute; top: calc(env(safe-area-inset-top) + 14px); inset-inline-end: 16px; z-index: 5;
                 padding: 6px 14px; border-radius: 999px; border: 1px solid var(--glass-line); background: rgba(19, 35, 63, .55);
                 color: #FFFFFF; font-weight: 700; font-size: 13px; text-decoration: none;
                 -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px); }
  body { min-height: 100vh; display: flex; justify-content: center; padding: 0 0 40px; position: relative; }
  body::before {
    content: ""; position: fixed; inset: 0; z-index: -1;
    background: linear-gradient(rgba(7, 18, 36, .55), rgba(7, 18, 36, .75)), url("{{ asset('images/demo/hall-bg.jpg') }}") center / cover no-repeat;
  }

  /* أبعاد مترابطة: البطاقة 80% من الشاشة، الصورة 40% من البطاقة،
     والشريط ارتفاعه نص ارتفاع الصورة -- فنصها السفلي داخله ونصها
     العلوي طالع فوقه. */
  .card {
    --w: 80vw;
    --photo-w: calc(var(--w) * .40);
    --photo-h: calc(var(--photo-w) * var(--ratio, 1));
    width: var(--w);
    margin-top: calc(var(--photo-h) / 2 + 40px);
  }
  body { flex-direction: column; align-items: center; justify-content: flex-start; }

  .band {
    position: relative;
    min-height: max(calc(var(--photo-h) / 2), 64px);
    padding-block: 10px;
    background: var(--band);
    border: 1px solid var(--glass-line);
    -webkit-backdrop-filter: blur(10px);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    transition: border-radius .2s;
    cursor: pointer;
    user-select: none;
    display: flex;
    align-items: center;
    padding-inline-start: 18px;
    padding-inline-end: calc(var(--photo-w) + 10px); /* مكان الصورة يسار الشريط */
  }

  /* الصورة عند نهاية السطر: يسار بالعربي، يمين بالإنجليزي. */
  .photo { position: absolute; inset-inline-end: calc(var(--w) * -.03); bottom: 0; width: var(--photo-w); pointer-events: none; }
  .photo img { display: block; width: 100%; height: auto; }

  .title { flex: 1; display: flex; align-items: center; justify-content: flex-start; gap: clamp(8px, 2.4vw, 16px); min-width: 0; }
  .title-text { min-width: 0; }
  .title h2 { margin: 0; color: var(--tx-on-band); font-size: clamp(16px, 4.2vw, 30px); font-weight: 800; line-height: 1.3; }
  .title small { display: block; color: var(--gold); font-weight: 700; font-size: clamp(10px, 2.2vw, 16px); margin-top: 2px; }

  .arrow { flex: none; color: var(--gold); font-size: clamp(20px, 5vw, 34px); line-height: 1; transition: transform .35s ease; }
  .card.is-open .arrow { transform: rotate(180deg); }

  /* مؤشر الكتابة أثناء ظهور النص حرف حرف */
  .typing::after { content: "▍"; color: var(--gold); margin-inline-start: 2px; animation: blink .8s steps(1) infinite; }
  @keyframes blink { 50% { opacity: 0; } }

  /* النص المنسدل تحت الشريط */
  .card.is-open .band { border-radius: 12px 12px 0 0; }
  .body { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .4s ease; background: var(--panel); border-radius: 0 0 12px 12px;
          -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px); }
  .card.is-open .body { grid-template-rows: 1fr; }
  .body > div { overflow: hidden; }
  .text { padding: 14px 20px 20px; }
  .text p { margin: 0 0 12px; color: var(--mu-on-band); font-size: clamp(13px, 3.2vw, 18px); line-height: 1.9; }
  .text p:last-child { margin-bottom: 0; }
  /* جمل الربط بين المحطات (التعليق) -- فوق كل بطاقة، وصورتها الطالعة
     فوق الشريط تحتها مباشرة بمسافة البطاقة نفسها. */
  /* تعليق سالم بين المحطات: فقاعة كلام بصورته الصغيرة، عشان يبان إنه هو
     اللي يقول الكلام. */
  .lead { width: 80vw; margin: 44px 0 -12px; display: flex; flex-direction: column; align-items: center; text-align: center;
          gap: 8px; padding: 18px 20px 20px;
          background: rgba(19, 35, 63, .5); border: 1px solid var(--glass-line); border-radius: 18px;
          -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px); }
  .lead-avatar { flex: none; width: clamp(46px, 11vw, 60px); height: clamp(46px, 11vw, 60px); border-radius: 50%; overflow: hidden;
                 border: 2px solid var(--gold); background: #E8EEF8; box-shadow: 0 4px 14px rgba(0, 0, 0, .35); }
  .lead-avatar img { width: 100%; height: 100%; object-fit: cover; object-position: 50% 38%; transform: scale(1.35); }
  .lead-body { min-width: 0; display: flex; flex-direction: column; align-items: center; }
  .lead-name { display: block; color: var(--gold); font-weight: 800; font-size: clamp(11px, 2.6vw, 14px); letter-spacing: .02em; }
  .lead-name::after { content: ""; display: block; width: 34px; height: 2px; margin: 8px auto 10px; background: var(--gold); border-radius: 2px; opacity: .7; }
  .lead-text { margin: 0; max-width: 34em; color: #F2F5FA; font-size: clamp(14px, 3.4vw, 19px); font-weight: 700; line-height: 1.9; }
  html[lang="en"] .lead-text, html[lang="en"] .host-intro { font-size: clamp(13px, 3.2vw, 18px); font-weight: 600; line-height: 1.75; }
  /* الاسم موجود ببطاقة سالم فوق، فالفقاعات فيها صورته بس. */
  .lead .lead-name { display: none; }
  .lead .lead-avatar { margin-bottom: 4px; }
  .lead--outro { margin: 48px 0 24px; }
  .lead--outro .lead-text { color: var(--gold); }
  .lead a { color: #FFFFFF; }

  /* بطاقة المقدّم (سالم) أعلى الصفحة -- نفس شريط البطاقات بدون فتح. */
  .card--host .band { cursor: default; border-radius: 12px 12px 0 0; }
  /* جملة الافتتاح ملتصقة ببطاقة سالم كجزء منها. */
  .host-intro { margin: 0; padding: 16px 20px 18px; text-align: center; color: #F2F5FA;
                font-size: clamp(15px, 3.8vw, 21px); font-weight: 700; line-height: 1.95;
                background: var(--panel); border: 1px solid var(--glass-line); border-top: 0; border-radius: 0 0 12px 12px;
                -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px); }
  /* رابط الخاتمة بسطر مستقل بدل ما ينكسر بآخر السطر. */
  .lead-link { display: inline-block; margin-top: 10px; padding: 4px 14px; font-size: 14px; border-radius: 999px; border: 1px solid var(--gold);
               color: #FFFFFF; font-weight: 800; letter-spacing: .03em; text-decoration: none; direction: ltr; }

  .hero { width: 80vw; margin-top: 34px; text-align: center; }
  .hero img { height: 46px; width: auto; }
  .hero small { display: block; margin-top: 10px; color: var(--gold); font-weight: 800; letter-spacing: .08em; font-size: clamp(11px, 2.6vw, 15px); }
  .hero h1 { margin: 6px 0 0; color: #FFFFFF; font-size: clamp(24px, 6.4vw, 44px); font-weight: 800; line-height: 1.3; text-shadow: 0 2px 14px rgba(0, 0, 0, .6); }

  .discussion-view {
    position: fixed; inset: 0; width: 100%; height: 100dvh; z-index: 9999;
    overflow-y: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch;
    background: linear-gradient(rgba(7, 18, 36, .9), rgba(7, 18, 36, .96)), url("{{ asset('images/demo/hall-bg.jpg') }}") center / cover no-repeat;
    opacity: 0; transition: opacity .25s ease;
  }
  .discussion-view[hidden] { display: none; }
  .discussion-view.is-visible { opacity: 1; }
  .discussion-view__header {
    position: sticky; top: 0; z-index: 10;
    display: flex; align-items: center; gap: 12px;
    padding: calc(env(safe-area-inset-top) + 12px) 16px 12px;
    background: rgba(11, 31, 58, .88); border-bottom: 1px solid var(--glass-line);
    -webkit-backdrop-filter: blur(12px); backdrop-filter: blur(12px);
  }
  .discussion-view__thumb { flex: none; height: 44px; width: auto; border-radius: 8px; }
  .discussion-view__titles { flex: 1; min-width: 0; }
  .discussion-view__titles small { display: block; color: var(--gold); font-weight: 800; font-size: 12px; }
  .discussion-view__titles h2 { margin: 2px 0 0; color: #FFFFFF; font-size: clamp(15px, 4vw, 22px); font-weight: 800; line-height: 1.35; }
  .discussion-view__close {
    flex: none; width: 42px; height: 42px; border-radius: 50%; border: 1px solid var(--gold);
    background: transparent; color: var(--gold); font-size: 18px; font-weight: 800; cursor: pointer;
  }
  .discussion-view__content { max-width: 760px; margin: 0 auto; padding: 18px 18px calc(36px + env(safe-area-inset-bottom)); }

  .text h4 { margin: 18px 0 8px; color: var(--gold); font-size: clamp(14px, 3.4vw, 19px); font-weight: 800; }
  .text ul, .text ol { margin: 0 0 12px; padding-inline-start: 20px; color: var(--mu-on-band); font-size: clamp(13px, 3.2vw, 18px); line-height: 1.9; }
  .text li { margin-bottom: 4px; }
  .text .note { margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--glass-line); font-size: clamp(11px, 2.6vw, 14px); opacity: .8; }

  /* بطاقة حلقة النقاش: مديرة الجلسة يسار، المشاركين يمين، والعنوان والزر بالنص. */
  .card--panel {
    --left-w: calc(var(--w) * .22);
    --left-h: calc(var(--left-w) * var(--ratio-left, 1));
    --right-w: calc(var(--w) * .34);
    --right-h: calc(var(--right-w) * var(--ratio-right, 1));
    margin-top: calc(max(var(--left-h), var(--right-h)) / 2 + 40px);
  }
  .card--panel .band {
    min-height: max(calc(max(var(--left-h), var(--right-h)) / 2), 96px);
    padding-left: calc(var(--left-w) + 6px);
    padding-right: calc(var(--right-w) + 6px);
    justify-content: center;
  }
  .card--panel .photo--left { inset-inline-end: auto; left: calc(var(--w) * -.02); right: auto; width: var(--left-w); }
  .card--panel .photo--right { inset-inline-end: auto; right: calc(var(--w) * -.02); left: auto; width: var(--right-w); }
  .panel-center { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 4px; padding-block: 6px; min-width: 0; }
  .panel-center small { color: var(--gold); font-weight: 700; font-size: clamp(9px, 2vw, 14px); }
  .panel-center h2 { margin: 0; color: var(--tx-on-band); font-size: clamp(15px, 4vw, 30px); font-weight: 800; line-height: 1.2; }
  .panel-center .topic { color: var(--mu-on-band); font-size: clamp(10px, 2.3vw, 16px); font-weight: 700; line-height: 1.4; }
  .panel-btn { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 999px;
               background: var(--gold); color: #13233F; font-weight: 800; font-size: clamp(11px, 2.6vw, 15px); white-space: nowrap; }
  .panel-btn .arrow { color: inherit; font-size: 1em; }

  /* الجوال: الوسط ضيق -- "حلقة نقاش" والزر بس، والصور أكبر عشان تطلع فوق
     الشريط مثل باقي البطاقات (عنوان الجلسة الكامل موجود أول الملخص). */
  @media (max-width: 600px) {
    .card--panel { --left-w: calc(var(--w) * .28); --right-w: calc(var(--w) * .42); }
    .card--panel .band { min-height: 60px; padding-block: 8px; }
    .panel-btn { padding: 7px 12px; font-size: 13px; }
  }

  .text h3 { margin: 0 0 12px; color: var(--tx-on-band); font-size: clamp(15px, 3.8vw, 22px); font-weight: 800; line-height: 1.6; }
</style>
</head>
<body>
@php
    // كل بطاقة: الاسم بالشريط، والعنوان + الفقرات تنكتب حرف حرف عند الفتح.
    // ratio = ارتفاع الصورة ÷ عرضها (يحدد كم تطلع الصورة فوق الشريط).
    $cards = [
        [
            'name' => 'يحيى العزري',
            'image' => 'images/demo/card-person.png',
            'ratio' => 445 / 465,
            'headline' => 'الثقة الرقمية تبدأ بالاستعداد للاختراق والقدرة على التعافي',
            'paragraphs' => [
                'أكد يحيى العزري، خلال جلسة «الثقة الرقمية والمرونة السيبرانية» في مؤتمر سايبر إكس عُمان 2026، أن حماية المؤسسات تتطلب الاستعداد للاختراق والقدرة على مواصلة العمل والتعافي منه. واستعرض أمثلة لهجمات طالت قطاعات الصحة والطيران والطاقة والمياه، موضحًا أن تعطل مورد أو شريك تقني قد يؤثر في منظومة كاملة، ولذلك يجب أن تشمل خطط التعافي الموردين والأنظمة المرتبطة بالمؤسسة.',
                'وأوضح أن الذكاء الاصطناعي يزيد تعقيد التهديدات، من خلال تسريع تطوير البرمجيات الخبيثة، وتغيير خصائصها لتفادي الكشف، واستنساخ الأصوات وتزييف الفيديو، بما يصعّب التحقق من الهوية والمحتوى.',
                'وطرح إطارًا دفاعيًا يقوم على افتراض وقوع الاختراق، وتطبيق دفاع متعدد الطبقات والثقة الصفرية، مع إبقاء الإنسان ضمن حلقة اتخاذ القرار. ويشمل ذلك كشف التزييف العميق، وتطوير أنظمة ذكاء اصطناعي آمنة، وأتمتة الاستجابة للحوادث.',
                'وشدد على دور الإدارة العليا في بناء المرونة السيبرانية، وتأهيل الكوادر، وتعزيز الشراكات، وحوكمة استخدام الذكاء الاصطناعي، ونشر الوعي الأمني. كما أشار إلى أهمية التكامل مع الهوية الرقمية الوطنية في عُمان لتعزيز الثقة بالخدمات الرقمية.',
            ],
        ],
        [
            'name' => 'فاطمة اللواتي',
            'lead' => 'افتراض وقوع الاختراق هو البداية، لكن ماذا بعده؟ كيف تواصل المؤسسة عملها وأنظمتها متوقفة؟ هنا تنقلنا فاطمة اللواتي من الالتزام بالمعايير إلى الاستمرارية الحقيقية.',
            'image' => 'images/demo/card-fatma.png',
            'ratio' => 1,
            'headline' => 'الامتثال وحده لا يكفي لحماية استمرارية المؤسسات',
            'paragraphs' => [
                'أكدت فاطمة اللواتي، خلال كلمتها في مؤتمر سايبر إكس عُمان 2026، أن الالتزام بالمعايير والسياسات لا يكفي لضمان قدرة المؤسسات على مواجهة الاضطرابات السيبرانية. فالامتثال يوضح الجاهزية على الورق، بينما تظهر المرونة في قدرة المؤسسة على مواصلة خدماتها الأساسية والتعافي أثناء الأزمات.',
                'وحددت خمس أولويات لبناء هذه المرونة، تبدأ بتحديد الخدمات الحرجة وما تعتمد عليه من تقنية وبيانات وأشخاص وموردين، وترتيب أولويات استعادتها. وتشمل أيضًا حوكمة واضحة تحدد المسؤوليات وصلاحيات القرار قبل وقوع الحوادث، مع مشاركة الإدارة العليا في فهم المخاطر وإدارة الاستجابة.',
                'وشددت على اختبار الجاهزية عبر محاكاة واقعية تجمع الفرق التقنية والقيادات، وتشمل سيناريوهات مثل تعطل مزود رئيسي. كما أوضحت أن الاعتماد على السحابة والاتصالات والأطراف الثالثة يجعل التعاون مع الموردين والجهات المعنية جزءًا أساسيًا من استمرارية العمل.',
                'ودعت إلى التعلم من كل حادث وتحديث خطط الاستجابة والتعافي باستمرار، وإدماج الأمن في تصميم الخدمات منذ البداية. واختتمت بأن حماية الاستمرارية تدعم الثقة في الاقتصاد الرقمي، وأن السؤال الأهم للمؤسسات هو: هل نستطيع الاستمرار عندما تقع الأزمة؟',
            ],
        ],
        [
            'name' => 'رينيل وحيد',
            'lead' => 'وإذا كانت الاستمرارية مهمة في أي مؤسسة، ففي المستشفى قد تصبح مسألة حياة. رينيل وحيد يأخذنا إلى غرفة الطوارئ.',
            'subtitle' => 'SOFPITAL · Health Systems',
            'image' => 'images/demo/card-rineel-v4.png',
            'ratio' => 1,
            'headline' => 'الأمن السيبراني في القطاع الصحي حماية للمرضى واستمرار للرعاية',
            'paragraphs' => [
                'أكد رينيل وحيد، مدير تقنية المعلومات في SOFPITAL Health Systems SPC، خلال مؤتمر سايبر إكس عُمان 2026، أن الأمن السيبراني في القطاع الصحي يرتبط مباشرة بسلامة المرضى. واستهل كلمته بمشهد لمريض يصل إلى الطوارئ بينما تتعطل الأنظمة، فلا يستطيع الفريق الطبي الوصول إلى سجله أو معرفة أدويته، موضحًا أن الهجمات قد تؤثر في قرارات علاجية عاجلة.',
                'وأشار إلى أن حساسية البيانات الصحية، وكثرة الأجهزة الطبية المتصلة، والأنظمة القديمة تجعل حماية المؤسسات الصحية أكثر تعقيدًا. وطرح خارطة طريق من خمس مراحل: حصر الأجهزة والأنظمة والموردين، وإتقان أساسيات الحماية، واكتشاف الهجمات واحتواؤها، والاستعداد للتعافي، وبناء ثقافة أمنية لدى الموظفين.',
                'وشدد على أهمية تقسيم الشبكات للحد من انتشار الهجوم، ووضع إجراءات واضحة للاستجابة تمنع فصل الأجهزة عشوائيًا أثناء الأزمة. كما دعا إلى تدريبات توعوية بالعربية والإنجليزية وتشجيع الإبلاغ عن الرسائل المشبوهة.',
                'وأوضح أن الذكاء الاصطناعي يمكن استخدامه للدفاع والهجوم، وأن توظيفه في الرعاية الصحية يتطلب حماية بيانات المرضى وضبط مشاركتها. واختتم بأن الهدف من الأمن السيبراني هو حماية الأشخاص الذين وثقوا بالمؤسسة، وضمان استمرار تقديم الرعاية لهم.',
            ],
        ],
        [
            'name' => 'شابيل بشير',
            'lead' => 'حماية المريض تبدأ باكتشاف الهجوم قبل أن يصل إليه. وهنا يأتي دور الذكاء الاصطناعي في مساندة فرق الأمن، مع شابيل بشير من ESET الشرق الأوسط.',
            'subtitle' => 'ESET Middle East',
            'image' => 'images/demo/card-guest4-v2.png',
            'ratio' => 1,
            'headline' => 'الذكاء الاصطناعي لا يستبدل المحلل البشري… بل يساعده على القرار أسرع',
            'paragraphs' => [
                'أكد شابيل بشير، مهندس ما قبل البيع الرئيسي في ESET الشرق الأوسط، خلال مؤتمر سايبر إكس عُمان 2026، أن المهاجمين باتوا يعملون «بسرعة الآلة» بينما يعمل المدافعون بسرعة البشر، مشيرًا إلى أن أسرع اختراق رُصد استغرق 27 ثانية فقط، وأن كثرة التنبيهات باتت ترهق فرق الأمن.',
                'وأوضح أن القيمة الحقيقية للذكاء الاصطناعي ليست في اكتشاف تهديدات أكثر، بل في فهمها: الكشف بالسلوك بدل التواقيع المعروفة، وتحليل البرمجيات الخبيثة في السحابة خلال دقائق، وربط عشرات التنبيهات في «قصة هجوم» واحدة، وترتيب الأولويات حسب المخاطر.',
                'وأشار إلى أن نحو 80% من الشركات تستخدم الذكاء الاصطناعي، ما يوسّع سطح الهجوم، داعيًا إلى حماية محادثات الموظفين مع أدوات الذكاء الاصطناعي ومنع تسرب البيانات الحساسة، ومراقبة سلوك وكلاء الذكاء الاصطناعي.',
                'واختتم بأن الذكاء الاصطناعي لا يحل محل المحلل البشري، بل يمنحه السياق والمعلومة ليتخذ القرار الصحيح بسرعة أكبر.',
            ],
        ],
    ];

    if ($isEn) {
        $cards = [
            [
                'name' => 'Yahya Al-Azri',
                'image' => 'images/demo/card-person.png',
                'ratio' => 445 / 465,
                'headline' => 'Digital trust starts with being ready for a breach and able to recover',
                'paragraphs' => [
                    'Speaking at the «Digital Trust and Cyber Resilience» session at CyberX Oman 2026, Yahya Al-Azri stressed that protecting organizations requires being prepared for a breach and able to keep operating and recover from it. He reviewed attacks that hit the healthcare, aviation, energy and water sectors, explaining that the failure of a single supplier or technology partner can affect an entire ecosystem — which is why recovery plans must cover suppliers and every system connected to the organization.',
                    'He explained that AI is making threats more complex: speeding up malware development, changing its characteristics to evade detection, and cloning voices and faking video, which makes verifying identity and content harder.',
                    'He proposed a defensive framework built on assuming breach, layered defense and zero trust, while keeping humans in the decision-making loop. This includes detecting deepfakes, building secure AI systems, and automating incident response.',
                    'He emphasized the role of senior leadership in building cyber resilience, developing talent, strengthening partnerships, governing the use of AI and spreading security awareness. He also pointed to the importance of integrating with Oman\'s national digital identity to strengthen trust in digital services.',
                ],
            ],
            [
                'name' => 'Fatma Al Lawati',
                'lead' => 'Assuming a breach is the starting point — but what comes next? How does an organization keep operating when its systems go down? Here, Fatma Al Lawati takes us from meeting standards to real continuity.',
                'image' => 'images/demo/card-fatma.png',
                'ratio' => 1,
                'headline' => 'Compliance alone is not enough to protect business continuity',
                'paragraphs' => [
                    'In her talk at CyberX Oman 2026, Fatma Al Lawati stressed that adhering to standards and policies is not enough to ensure organizations can withstand cyber disruption. Compliance shows readiness on paper, while resilience shows in an organization\'s ability to keep its essential services running and recover during a crisis.',
                    'She set out five priorities for building that resilience, starting with identifying critical services and the technology, data, people and suppliers they depend on, and prioritizing their recovery. They also include clear governance that defines responsibilities and decision rights before incidents happen, with senior leadership involved in understanding risks and managing the response.',
                    'She stressed testing readiness through realistic simulations that bring technical teams and leaders together, including scenarios such as the outage of a key provider. She also explained that reliance on cloud, telecom and third parties makes cooperation with suppliers and stakeholders a core part of business continuity.',
                    'She called for learning from every incident, continuously updating response and recovery plans, and building security into the design of services from the start. She concluded that protecting continuity supports trust in the digital economy, and that the most important question for any organization is: can we keep going when the crisis hits?',
                ],
            ],
            [
                'name' => 'Rineel Wahid',
                'lead' => 'And if continuity matters in any organization, in a hospital it can be a matter of life and death. Rineel Wahid takes us into the emergency room.',
                'subtitle' => 'SOFPITAL · Health Systems',
                'image' => 'images/demo/card-rineel-v4.png',
                'ratio' => 1,
                'headline' => 'Cybersecurity in healthcare protects patients and keeps care going',
                'paragraphs' => [
                    'Speaking at CyberX Oman 2026, Rineel Wahid, IT Director at SOFPITAL Health Systems SPC, stressed that cybersecurity in the healthcare sector is directly tied to patient safety. He opened with a scene of a patient arriving at the emergency room while the systems are down, leaving the medical team unable to access the patient\'s record or know their medications — explaining that attacks can disrupt urgent treatment decisions.',
                    'He noted that the sensitivity of health data, the large number of connected medical devices and legacy systems make protecting healthcare organizations more complex. He proposed a five-stage roadmap: inventorying devices, systems and suppliers; mastering the security basics; detecting and containing attacks; preparing for recovery; and building a security culture among staff.',
                    'He stressed the importance of network segmentation to limit the spread of an attack, and clear response procedures that prevent devices from being disconnected at random during a crisis. He also called for awareness training in both Arabic and English, and for encouraging staff to report suspicious messages.',
                    'He explained that AI can be used for both defense and attack, and that using it in healthcare requires protecting patient data and controlling how it is shared. He concluded that the purpose of cybersecurity is to protect the people who placed their trust in the organization, and to make sure they keep receiving care.',
                ],
            ],
            [
                'name' => 'Shabil Basheer',
                'lead' => 'Protecting the patient also means detecting threats before they disrupt care. This is where AI comes in to support security teams, with Shabil Basheer of ESET Middle East.',
                'subtitle' => 'ESET Middle East',
                'image' => 'images/demo/card-guest4-v2.png',
                'ratio' => 1,
                'headline' => 'AI doesn\'t replace the human analyst… it helps them decide faster',
                'paragraphs' => [
                    'Speaking at CyberX Oman 2026, Shabil Basheer, Lead Presales Engineer at ESET Middle East, said attackers now operate «at machine speed» while defenders work at human speed, noting that the fastest breach on record took just 27 seconds and that alert overload is exhausting security teams.',
                    'He explained that AI\'s real value lies not in detecting more threats but in understanding them: behavior-based detection instead of known signatures, cloud malware analysis within minutes, linking dozens of alerts into a single «attack story», and prioritizing by risk.',
                    'He noted that around 80% of companies use AI, which widens the attack surface, and called for protecting employees\' conversations with AI tools, preventing sensitive data leaks, and monitoring the behavior of AI agents.',
                    'He concluded that AI does not replace the human analyst; it gives them the context and information to make the right decision faster.',
                ],
            ],
        ];
    }
@endphp

  <a class="lang-switch" href="{{ $isEn ? url('/demo/card') : url('/en/demo/card') }}" hreflang="{{ $isEn ? 'ar' : 'en' }}">{{ $isEn ? 'العربية' : 'English' }}</a>

  <header class="hero">
    <img src="{{ asset('images/logo/logo-white-trimmed.png') }}" alt="ALYASI">
    <small>{{ $t('CYBERX OMAN 2026 · مسقط', 'CYBERX OMAN 2026 · MUSCAT') }}</small>
    <h1>{{ $t('خمس محطات', 'Five Stops') }}</h1>
  </header>

  <article class="card card--host" style="--ratio: 1">
    <div class="band">
      <div class="photo">
        <img src="{{ asset('images/demo/host-salem.png') }}" alt="{{ $t('سالم الحجري', 'Salem Al Hajri') }}">
      </div>
      <div class="title">
        <div class="title-text">
          <h2>{{ $t('سالم الحجري', 'Salem Al Hajri') }}</h2>
          <small>{{ $t('منصة الياسي · من قلب المؤتمر', 'ALYASI · From CyberX Oman 2026') }}</small>
        </div>
      </div>
    </div>
    <p class="host-intro">{{ $t('من سايبر إكس عُمان 2026 في مسقط، أنقل لكم خمس محطات توقفت عندها. ونبدأ بالسؤال الأصعب: ماذا لو وقع الاختراق فعلًا؟', 'From CyberX Oman 2026 in Muscat, here are five moments that stood out to me. Let\'s start with the hardest question: what if a breach actually happens?') }}</p>
  </article>

  @foreach ($cards as $card)
    @if (! empty($card['lead']))
      <div class="lead">
      <div class="lead-avatar"><img src="{{ asset('images/demo/host-salem.png') }}" alt=""></div>
      <div class="lead-body"><span class="lead-name">{{ $t('سالم الحجري', 'Salem Al Hajri') }}</span><p class="lead-text">{{ $card['lead'] }}</p></div>
    </div>
    @endif
    <article class="card" style="--ratio: {{ $card['ratio'] }}"
             data-headline="{{ $card['headline'] }}"
             data-paragraphs="{{ json_encode($card['paragraphs'], JSON_UNESCAPED_UNICODE) }}">
      <div class="band" role="button" tabindex="0" aria-expanded="false">
        <div class="photo">
          <img src="{{ asset($card['image']) }}" alt="{{ $card['name'] }}">
        </div>
        <div class="title">
          <span class="arrow" aria-hidden="true">⌄</span>
          <div class="title-text">
            <h2>{{ $card['name'] }}</h2>
            <small>{{ $card['subtitle'] ?? $t('سايبر إكس عُمان 2026', 'CyberX Oman 2026') }}</small>
          </div>
        </div>
      </div>
      <div class="body">
        <div>
          <div class="text"></div>
        </div>
      </div>
    </article>
  @endforeach

  @php
      $panelBlocks = json_decode(file_get_contents(resource_path($isEn ? 'views/demo/panel-ai-governance.en.json' : 'views/demo/panel-ai-governance.json')), true);
  @endphp
  <div class="lead">
      <div class="lead-avatar"><img src="{{ asset('images/demo/host-salem.png') }}" alt=""></div>
      <div class="lead-body"><span class="lead-name">{{ $t('سالم الحجري', 'Salem Al Hajri') }}</span><p class="lead-text">{{ $t('لكن الذكاء الاصطناعي نفسه يحتاج إلى من يضبطه: ما البيانات التي يصل إليها؟ ومن يتحمّل القرار؟ هذا ما ناقشته الحلقة الختامية.', 'But AI itself needs someone to keep it in check: what data can it reach? And who remains accountable for the decision? That\'s what the closing panel discussed.') }}</p></div>
    </div>
  <article class="card card--panel" style="--ratio-left: 1; --ratio-right: {{ 506 / 900 }}"
           data-blocks="{{ json_encode($panelBlocks, JSON_UNESCAPED_UNICODE) }}">
    <div class="band" role="button" tabindex="0" aria-expanded="false">
      <div class="photo photo--left">
        <img src="{{ asset('images/demo/panel-ramya.png') }}" alt="{{ $t('راميا سانكاري كارثيك — مديرة الجلسة', 'Ramya Sankari Karthick — moderator') }}">
      </div>
      <div class="panel-center">
        <span class="panel-btn">{{ $t('جلسة نقاش', 'Panel Discussion') }} <span class="arrow" aria-hidden="true">⌄</span></span>
      </div>
      <div class="photo photo--right">
        <img src="{{ asset('images/demo/panel-group.png') }}" alt="{{ $t('المشاركون في حلقة النقاش', 'Panelists') }}">
      </div>
    </div>
  </article>

  {{-- جلسة النقاش تنفتح شاشة كاملة فوق "خمس محطات" بدل ما تتمدد داخل
       الصفحة: النص طويل، وزر الإغلاق لازم يبقى ظاهر مهما نزل القارئ. --}}
  <div class="discussion-view" id="discussionView" role="dialog" aria-modal="true" aria-labelledby="discussionTitle" hidden>
    <header class="discussion-view__header">
      <img class="discussion-view__thumb" src="{{ asset('images/demo/panel-group.png') }}" alt="">
      <div class="discussion-view__titles">
        <small>{{ $t('جلسة نقاش · CyberX Oman 2026', 'Panel discussion · CyberX Oman 2026') }}</small>
        <h2 id="discussionTitle">{{ $t('تأمين مؤسسات الذكاء الاصطناعي', 'Securing the AI Enterprise') }}</h2>
      </div>
      <button type="button" class="discussion-view__close" aria-label="{{ $t('إغلاق الجلسة', 'Close session') }}">✕</button>
    </header>
    <div class="discussion-view__content">
      <div class="text"></div>
    </div>
  </div>

  <div class="lead lead--outro">
      <div class="lead-avatar"><img src="{{ asset('images/demo/host-salem.png') }}" alt=""></div>
      <div class="lead-body"><span class="lead-name">{{ $t('سالم الحجري', 'Salem Al Hajri') }}</span><p class="lead-text">{{ $t('خمس محطات، ورسالة واحدة: الاستعداد واختبار الجاهزية وحماية البيانات هي ما يُبقي المؤسسات قائمة عندما تتعطل الأنظمة. هذه كانت خمس محطات من سايبر إكس عُمان 2026. اقرأ التغطية الكاملة والتفاصيل على', 'Five stops, one message: cyber resilience starts long before an incident happens. Preparation, readiness testing, data protection and clear accountability are what keep organizations moving when systems fail. These were the five moments that stood out to me at CyberX Oman 2026.') }}<br><a class="lead-link" href="https://alyasi.dev">alyasi.dev</a></p></div>
    </div>

  <script>
    // كتابة الكتل (عناوين، فقرات، قوائم) حرف حرف داخل عنصر معيّن. أي
    // بدء/إيقاف جديد يلغي الكتابة الجارية لنفس الكاتب.
    function createTyper(blocks) {
      // النص الطويل (جلسة النقاش) يُكتب بخطوات أكبر عشان يخلص بحوالي 10 ثواني.
      const totalChars = blocks.reduce((n, b) => n + [...b.x].length, 0);
      const step = Math.max(2, Math.ceil(totalChars / 650));
      let run = 0;

      async function typeInto(el, text, myRun) {
        el.classList.add('typing');
        const chars = [...text];
        for (let i = 0; i < chars.length; i += step) {
          if (myRun !== run) return false;
          el.textContent += chars.slice(i, i + step).join('');
          await new Promise((r) => setTimeout(r, 16));
        }
        el.classList.remove('typing');
        return true;
      }

      return {
        async start(target) {
          const myRun = ++run;
          target.innerHTML = '';
          let list = null;

          for (const block of blocks) {
            let el;
            if (block.t === 'li' || block.t === 'oli') {
              const tag = block.t === 'li' ? 'UL' : 'OL';
              if (!list || list.tagName !== tag) {
                list = document.createElement(tag);
                target.appendChild(list);
              }
              el = document.createElement('li');
              list.appendChild(el);
            } else {
              list = null;
              el = document.createElement(block.t === 'note' ? 'p' : block.t);
              if (block.t === 'note') el.className = 'note';
              target.appendChild(el);
            }
            if (!await typeInto(el, block.x, myRun)) return;
          }
        },
        stop() { run++; },
      };
    }

    function blocksOf(card) {
      return card.dataset.blocks
        ? JSON.parse(card.dataset.blocks)
        : [{ t: 'h3', x: card.dataset.headline }, ...JSON.parse(card.dataset.paragraphs).map((x) => ({ t: 'p', x }))];
    }

    function onActivate(el, handler) {
      el.addEventListener('click', handler);
      el.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); handler(); } });
    }

    // بطاقات الضيوف: تتمدد داخل الصفحة.
    function setupCard(card) {
      const band = card.querySelector('.band');
      const textEl = card.querySelector('.text');
      const typer = createTyper(blocksOf(card));

      onActivate(band, () => {
        const open = card.classList.toggle('is-open');
        band.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) typer.start(textEl); else typer.stop();
      });
    }

    // جلسة النقاش: شاشة كاملة فوق الصفحة. الصفحة اللي ورا تنقفل (ما
    // تتحرك)، والتمرير داخل الشاشة بس، والإغلاق يرجّع القارئ لنفس مكانه.
    function setupDiscussion(card) {
      const view = document.getElementById('discussionView');
      const viewText = view.querySelector('.text');
      const closeBtn = view.querySelector('.discussion-view__close');
      const band = card.querySelector('.band');
      const typer = createTyper(blocksOf(card));
      let savedY = 0;
      let isOpen = false;

      function lockPage() {
        savedY = window.scrollY;
        Object.assign(document.body.style, { position: 'fixed', top: `-${savedY}px`, left: '0', right: '0', width: '100%' });
      }

      function unlockPage() {
        Object.assign(document.body.style, { position: '', top: '', left: '', right: '', width: '' });
        window.scrollTo(0, savedY);
        // احتياط: لو المتصفح حرّك الصفحة بعد الرجوع بالتاريخ.
        requestAnimationFrame(() => window.scrollTo(0, savedY));
      }

      function open() {
        if (isOpen) return;
        isOpen = true;
        lockPage();
        view.hidden = false;
        view.scrollTop = 0;
        requestAnimationFrame(() => view.classList.add('is-visible'));
        band.setAttribute('aria-expanded', 'true');
        typer.start(viewText);
        // زر الرجوع / سحبة الرجوع بالجوال تقفل الجلسة بدل ما تطلع من الصفحة.
        history.pushState({ discussion: true }, '');
        closeBtn.focus({ preventScroll: true });
      }

      function close(fromHistory = false) {
        if (!isOpen) return;
        // زر الإغلاق/Esc يرجع خطوة بالتاريخ، والإغلاق الفعلي يصير مرة وحدة
        // من popstate -- عشان المتصفح ما يرجّع الصفحة لأعلاها بعدنا.
        if (!fromHistory && history.state && history.state.discussion) {
          history.back();
          return;
        }
        isOpen = false;
        typer.stop();
        view.classList.remove('is-visible');
        view.hidden = true;
        band.setAttribute('aria-expanded', 'false');
        unlockPage();
        band.focus({ preventScroll: true });
      }

      onActivate(band, open);
      closeBtn.addEventListener('click', () => close());
      document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
      window.addEventListener('popstate', () => close(true));
    }

    // نتحكم نحن بمكان التمرير عند فتح/إغلاق الجلسة، مو المتصفح.
    if ('scrollRestoration' in history) history.scrollRestoration = 'manual';

    document.querySelectorAll('.card:not(.card--host):not(.card--panel)').forEach(setupCard);
    document.querySelectorAll('.card--panel').forEach(setupDiscussion);
  </script>
</body>
</html>
