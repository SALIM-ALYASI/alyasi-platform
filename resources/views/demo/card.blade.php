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
    --bg: #0B1F3A; --panel: rgba(16, 32, 58, .62); --card: rgba(11, 27, 52, .78);
    --glass-line: rgba(255, 255, 255, .16); --tx: #FFFFFF; --mu: #C9D4E6; --gold: #D8B56A; --gold-2: #C99E4C;
    --page-w: min(92vw, 720px); --radius: 16px; --gap: 14px;
  }
  * { box-sizing: border-box; }
  html { background: var(--bg); }
  html, body { margin: 0; font-family: Tajawal, system-ui, sans-serif; }
  html[lang="en"] body { font-family: Figtree, system-ui, sans-serif; }
  body { min-height: 100vh; display: flex; flex-direction: column; align-items: center; padding: 0 0 40px; position: relative; color: var(--tx); }
  body::before {
    content: ""; position: fixed; inset: 0; z-index: -1;
    background: linear-gradient(rgba(7, 18, 36, .6), rgba(7, 18, 36, .8)), url("{{ asset('images/demo/hall-bg.jpg') }}") center / cover no-repeat;
  }
  .glass { background: var(--card); border: 1px solid var(--glass-line); -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px); }

  .lang-switch { position: absolute; top: calc(env(safe-area-inset-top) + 14px); inset-inline-end: 16px; z-index: 5;
                 padding: 6px 14px; border-radius: 999px; border: 1px solid var(--glass-line); background: rgba(19, 35, 63, .55);
                 color: #FFFFFF; font-weight: 700; font-size: 13px; text-decoration: none;
                 -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px); }

  .hero { width: var(--page-w); margin-top: 34px; text-align: center; }
  .hero img { height: 46px; width: auto; }
  .hero small { display: block; margin-top: 10px; color: var(--gold); font-weight: 800; letter-spacing: .08em; font-size: clamp(11px, 2.6vw, 15px); }
  .hero h1 { margin: 6px 0 0; font-size: clamp(24px, 6.4vw, 44px); font-weight: 800; line-height: 1.3; text-shadow: 0 2px 14px rgba(0, 0, 0, .6); }

  /* ---- بطاقة سالم (الراوي): شريط وصورته طالعة فوقه، وجملة الافتتاح تحته ---- */
  .host { --w: var(--page-w); --photo-w: calc(var(--w) * .34); width: var(--w); margin-top: calc(var(--photo-w) / 2 + 34px); }
  .host-band { position: relative; min-height: max(calc(var(--photo-w) / 2), 64px); display: flex; align-items: center;
               padding: 10px 18px; padding-inline-end: calc(var(--photo-w) + 10px); border-radius: var(--radius) var(--radius) 0 0; }
  .host-photo { position: absolute; inset-inline-end: calc(var(--w) * -.02); bottom: 0; width: var(--photo-w); pointer-events: none; }
  .host-photo img { display: block; width: 100%; height: auto; }
  .host-band h2 { margin: 0; font-size: clamp(17px, 4.4vw, 28px); font-weight: 800; }
  .host-band small { display: block; margin-top: 2px; color: var(--gold); font-weight: 700; font-size: clamp(11px, 2.6vw, 15px); }
  .host-intro { margin: 0; padding: 14px 20px 16px; text-align: center; color: #F2F5FA; border-top: 0; border-radius: 0 0 var(--radius) var(--radius);
                font-size: clamp(15px, 3.8vw, 20px); font-weight: 700; line-height: 1.9; }

  /* ---- حاوية المحطات الخمس ---- */
  .stops { width: var(--page-w); margin-top: 22px; padding: 14px; border-radius: 22px; display: flex; flex-direction: column; gap: var(--gap);
           background: rgba(7, 18, 36, .42); border: 1px solid var(--glass-line); -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px); }

  /* كلام سالم بين المحطات: مميّز عن كلام المتحدثين -- بدون بطاقة، خط ذهبي
     جانبي وصورة صغيرة، بلون كريمي. */
  .bridge { margin: 0; display: flex; align-items: flex-start; gap: 10px; padding: 4px 4px 4px 0; padding-inline-start: 12px;
            border-inline-start: 2px solid var(--gold); color: #EADFC2; font-size: clamp(14px, 3.4vw, 17px); font-weight: 600; line-height: 1.85; }
  .bridge img { flex: none; width: 30px; height: 30px; border-radius: 50%; object-fit: cover; object-position: 50% 30%;
                border: 1.5px solid var(--gold); background: #E8EEF8; margin-top: 2px; }

  /* بطاقة المحطة: صف فوق (صورة + اسم + صفة) وصف تحت (عنوان المحور + زر) */
  .stop { border-radius: var(--radius); overflow: hidden; }
  .stop-top { display: flex; align-items: center; gap: 12px; padding: 12px 14px; }
  .stop-photo { flex: none; width: 64px; height: 64px; border-radius: 14px; overflow: hidden;
                background: linear-gradient(160deg, #1D3D6B, #0F2647); border: 1px solid var(--glass-line); }
  .stop-photo img { width: 100%; height: 100%; object-fit: cover; object-position: 50% 8%; display: block; }
  .stop-photo--group { width: 118px; }
  .stop-photo--group img { object-position: 50% 20%; }
  .stop-who { min-width: 0; }
  .stop-num { display: block; color: var(--gold); font-weight: 800; font-size: 11px; letter-spacing: .06em; }
  .stop-who h3 { margin: 1px 0 0; font-size: clamp(16px, 4vw, 19px); font-weight: 800; line-height: 1.3; }
  .stop-who p { margin: 2px 0 0; color: var(--mu); font-size: clamp(12px, 3vw, 14px); line-height: 1.5; }
  .stop-bottom { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-top: 1px solid var(--glass-line);
                 background: rgba(255, 255, 255, .03); }
  .stop-title { flex: 1; margin: 0; font-size: clamp(14px, 3.5vw, 16px); font-weight: 700; line-height: 1.6; color: #F2F5FA; }
  .btn-gold { flex: none; display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 999px; cursor: pointer;
              font: inherit; font-weight: 800; font-size: 13px; color: #13233F; white-space: nowrap;
              background: linear-gradient(135deg, #F3DB9C, var(--gold-2)); border: 1px solid rgba(255, 255, 255, .45);
              box-shadow: 0 6px 16px rgba(0, 0, 0, .35), inset 0 1px 0 rgba(255, 255, 255, .5); transition: transform .15s ease; }
  .btn-gold:active { transform: translateY(1px); }

  /* ---- الخاتمة ---- */
  .outro { width: var(--page-w); margin: 22px 0 10px; padding: 18px 20px; border-radius: var(--radius); text-align: center; }
  .outro img { width: 52px; height: 52px; border-radius: 50%; object-fit: cover; object-position: 50% 30%; border: 2px solid var(--gold); background: #E8EEF8; }
  .outro p { margin: 8px 0 0; color: var(--gold); font-size: clamp(14px, 3.4vw, 18px); font-weight: 700; line-height: 1.85; }
  .outro a { display: inline-block; margin-top: 10px; padding: 4px 14px; border-radius: 999px; border: 1px solid var(--gold);
             color: #FFFFFF; font-weight: 800; font-size: 14px; text-decoration: none; direction: ltr; }
  html[lang="en"] .host-intro, html[lang="en"] .bridge, html[lang="en"] .outro p { font-weight: 600; }

  /* ---- مودال التفاصيل: شاشة كاملة فوق الصفحة ---- */
  .stop-modal { position: fixed; inset: 0; width: 100%; height: 100dvh; z-index: 9999; display: flex; flex-direction: column;
                overflow-y: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch;
                background: linear-gradient(rgba(7, 18, 36, .93), rgba(7, 18, 36, .97)), url("{{ asset('images/demo/hall-bg.jpg') }}") center / cover no-repeat;
                opacity: 0; transition: opacity .2s ease; }
  .stop-modal[hidden] { display: none; }
  .stop-modal.is-visible { opacity: 1; }
  .stop-modal__header { position: sticky; top: 0; z-index: 10; display: flex; align-items: center; justify-content: space-between; gap: 12px;
                        padding: calc(env(safe-area-inset-top) + 10px) 16px 10px; background: rgba(11, 31, 58, .9);
                        border-bottom: 1px solid var(--glass-line); -webkit-backdrop-filter: blur(12px); backdrop-filter: blur(12px); }
  .stop-modal__count { color: var(--gold); font-weight: 800; font-size: 13px; letter-spacing: .04em; }
  .stop-modal__close { flex: none; width: 42px; height: 42px; border-radius: 50%; border: 1px solid var(--gold); background: transparent;
                       color: var(--gold); font-size: 18px; font-weight: 800; cursor: pointer; }
  .stop-modal__content { flex: 1; width: 100%; max-width: 720px; margin: 0 auto; padding: 20px 18px 28px; }
  .speaker { display: flex; align-items: center; gap: 14px; padding: 14px; border-radius: var(--radius); }
  .speaker .stop-photo { width: 84px; height: 84px; border-radius: 18px; }
  .speaker .stop-photo--group { width: 150px; }
  .speaker h2 { margin: 0; font-size: clamp(18px, 4.6vw, 24px); font-weight: 800; }
  .speaker p { margin: 3px 0 0; color: var(--mu); font-size: 14px; line-height: 1.6; }
  .article-title { margin: 22px 0 6px; font-size: clamp(20px, 5.2vw, 28px); font-weight: 800; line-height: 1.45; }
  .article-sub { margin: 0 0 14px; color: var(--gold); font-weight: 700; font-size: 14px; line-height: 1.6; }
  .article { color: #E3EAF5; font-size: clamp(15px, 3.8vw, 18px); line-height: 2; }
  html[lang="en"] .article { line-height: 1.8; }
  .article p { margin: 0 0 16px; }
  .article h3 { margin: 6px 0 12px; font-size: clamp(17px, 4.2vw, 21px); font-weight: 800; color: #FFFFFF; line-height: 1.6; }
  .article h4 { margin: 22px 0 8px; color: var(--gold); font-size: clamp(15px, 3.8vw, 18px); font-weight: 800; }
  .article ul, .article ol { margin: 0 0 16px; padding-inline-start: 22px; }
  .article li { margin-bottom: 6px; }
  .article .note { margin-top: 20px; padding-top: 14px; border-top: 1px solid var(--glass-line); font-size: 13px; color: var(--mu); line-height: 1.8; }
  .stop-modal__nav { position: sticky; bottom: 0; z-index: 10; display: flex; gap: 10px; justify-content: space-between;
                     padding: 10px 16px calc(env(safe-area-inset-bottom) + 10px); background: rgba(11, 31, 58, .92);
                     border-top: 1px solid var(--glass-line); -webkit-backdrop-filter: blur(12px); backdrop-filter: blur(12px); }
  .nav-btn { flex: 1; max-width: 340px; display: flex; flex-direction: column; align-items: flex-start; gap: 1px; padding: 8px 14px; border-radius: 14px;
             border: 1px solid var(--glass-line); background: rgba(255, 255, 255, .05); color: #FFFFFF; font: inherit; cursor: pointer; text-align: start; }
  .nav-btn--next { align-items: flex-end; text-align: end; }
  .nav-btn small { color: var(--gold); font-weight: 800; font-size: 12px; }
  .nav-btn span { font-weight: 700; font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; }
  .nav-btn:disabled { opacity: .35; cursor: default; }
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
@php
    $panelBlocks = json_decode(file_get_contents(resource_path($isEn ? 'views/demo/panel-ai-governance.en.json' : 'views/demo/panel-ai-governance.json')), true);

    // الصفة تحت الاسم في البطاقة والمودال.
    $roles = $isEn
        ? ['Oman CERT', 'WiCSME', 'IT Director · SOFPITAL Health Systems', 'Lead Presales Engineer · ESET Middle East']
        : ['Oman CERT', 'WiCSME', 'مدير تقنية المعلومات · SOFPITAL', 'مهندس ما قبل البيع الرئيسي · ESET الشرق الأوسط'];

    $stops = [];
    foreach ($cards as $i => $card) {
        $stops[] = [
            'lead' => $card['lead'] ?? null,
            'name' => $card['name'],
            'role' => $roles[$i] ?? '',
            'sub' => null,
            'image' => asset($card['image']),
            'group' => false,
            'headline' => $card['headline'],
            'blocks' => array_map(fn ($x) => ['t' => 'p', 'x' => $x], $card['paragraphs']),
        ];
    }

    // المحطة الخامسة: جلسة النقاش. أول كتلتين بالملف (عنوان الجلسة الكامل
    // والعنوان الرئيسي) يطلعون فوق المقال، فما نكررهم داخله.
    $stops[] = [
        'lead' => $t('لكن الذكاء الاصطناعي نفسه يحتاج إلى من يضبطه: ما البيانات التي يصل إليها؟ ومن يتحمّل القرار؟ هذا ما ناقشته الحلقة الختامية.', 'But AI itself needs someone to keep it in check: what data can it reach? And who remains accountable for the decision? That\'s what the closing panel discussed.'),
        'name' => $t('جلسة نقاش', 'Panel Discussion'),
        'role' => $t('تأمين مؤسسات الذكاء الاصطناعي', 'Securing the AI Enterprise'),
        'sub' => $panelBlocks[0]['x'],
        'image' => asset('images/demo/panel-group.png'),
        'group' => true,
        'headline' => $panelBlocks[1]['x'],
        'blocks' => array_slice($panelBlocks, 2),
    ];

    $hostAvatar = asset('images/demo/host-salem.png');
    // @json يقسّم الوسائط عند الفواصل، فنجهّز المصفوفة هنا.
    $jsLabels = ['stop' => $t('المحطة', 'Stop'), 'of' => $t('من', 'of')];
@endphp

  <a class="lang-switch" href="{{ $isEn ? url('/demo/card') : url('/en/demo/card') }}" hreflang="{{ $isEn ? 'ar' : 'en' }}">{{ $isEn ? 'العربية' : 'English' }}</a>

  <header class="hero">
    <img src="{{ asset('images/logo/logo-white-trimmed.png') }}" alt="ALYASI">
    <small>{{ $t('CYBERX OMAN 2026 · مسقط', 'CYBERX OMAN 2026 · MUSCAT') }}</small>
    <h1>{{ $t('خمس محطات', 'Five Stops') }}</h1>
  </header>

  <article class="host">
    <div class="host-band glass">
      <div class="host-photo"><img src="{{ $hostAvatar }}" alt="{{ $t('سالم الحجري', 'Salem Al Hajri') }}"></div>
      <div>
        <h2>{{ $t('سالم الحجري', 'Salem Al Hajri') }}</h2>
        <small>{{ $t('منصة الياسي · من قلب المؤتمر', 'ALYASI · From CyberX Oman 2026') }}</small>
      </div>
    </div>
    <p class="host-intro glass">{{ $t('من سايبر إكس عُمان 2026 في مسقط، أنقل لكم خمس محطات توقفت عندها. ونبدأ بالسؤال الأصعب: ماذا لو وقع الاختراق فعلًا؟', 'From CyberX Oman 2026 in Muscat, here are five moments that stood out to me. Let\'s start with the hardest question: what if a breach actually happens?') }}</p>
  </article>

  <section class="stops" aria-label="{{ $t('المحطات الخمس', 'The five stops') }}">
    @foreach ($stops as $i => $stop)
      @if ($stop['lead'])
        <p class="bridge"><img src="{{ $hostAvatar }}" alt=""><span>{{ $stop['lead'] }}</span></p>
      @endif
      <article class="stop glass">
        <div class="stop-top">
          <div class="stop-photo {{ $stop['group'] ? 'stop-photo--group' : '' }}"><img src="{{ $stop['image'] }}" alt="{{ $stop['name'] }}" loading="lazy"></div>
          <div class="stop-who">
            <span class="stop-num">{{ $t('المحطة', 'STOP') }} {{ $i + 1 }}</span>
            <h3>{{ $stop['name'] }}</h3>
            <p>{{ $stop['role'] }}</p>
          </div>
        </div>
        <div class="stop-bottom">
          <p class="stop-title">{{ $stop['headline'] }}</p>
          <button type="button" class="btn-gold" data-stop="{{ $i }}">{{ $t('التفاصيل', 'Details') }} <span aria-hidden="true">{{ $isEn ? '→' : '←' }}</span></button>
        </div>
      </article>
    @endforeach
  </section>

  <aside class="outro glass">
    <img src="{{ $hostAvatar }}" alt="">
    <p>{{ $t('خمس محطات، ورسالة واحدة: الاستعداد واختبار الجاهزية وحماية البيانات هي ما يُبقي المؤسسات قائمة عندما تتعطل الأنظمة. هذه كانت خمس محطات من سايبر إكس عُمان 2026. اقرأ التغطية الكاملة والتفاصيل على', 'Five stops, one message: cyber resilience starts long before an incident happens. Preparation, readiness testing, data protection and clear accountability are what keep organizations moving when systems fail. These were the five moments that stood out to me at CyberX Oman 2026.') }}</p>
    <a href="https://alyasi.dev">alyasi.dev</a>
  </aside>

  {{-- مودال التفاصيل: شاشة كاملة بنفس هوية الصفحة، رأس ثابت فيه الإغلاق،
       وتنقّل سابق/تالي بين المحطات بدون الرجوع للصفحة. --}}
  <div class="stop-modal" id="stopModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" hidden>
    <header class="stop-modal__header">
      <span class="stop-modal__count" id="modalCount"></span>
      <button type="button" class="stop-modal__close" id="modalClose" aria-label="{{ $t('إغلاق', 'Close') }}">✕</button>
    </header>
    <div class="stop-modal__content">
      <div class="speaker glass">
        <div class="stop-photo" id="modalPhotoBox"><img id="modalPhoto" src="" alt=""></div>
        <div>
          <h2 id="modalName"></h2>
          <p id="modalRole"></p>
        </div>
      </div>
      <h1 class="article-title" id="modalTitle"></h1>
      <p class="article-sub" id="modalSub" hidden></p>
      <div class="article" id="modalArticle"></div>
    </div>
    <nav class="stop-modal__nav" aria-label="{{ $t('التنقل بين المحطات', 'Stop navigation') }}">
      <button type="button" class="nav-btn" id="modalPrev"><small>{{ $isEn ? '←' : '→' }} {{ $t('السابق', 'Previous') }}</small><span></span></button>
      <button type="button" class="nav-btn nav-btn--next" id="modalNext"><small>{{ $t('التالي', 'Next') }} {{ $isEn ? '→' : '←' }}</small><span></span></button>
    </nav>
  </div>

  <script type="application/json" id="stopsData">@json($stops, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)</script>
  <script>
    const stops = JSON.parse(document.getElementById('stopsData').textContent);
    const LABEL = @json($jsLabels);

    const modal = document.getElementById('stopModal');
    const el = (id) => document.getElementById(id);
    let current = -1;
    let savedY = 0;
    let isOpen = false;
    let opener = null;

    function renderArticle(blocks) {
      const article = el('modalArticle');
      article.innerHTML = '';
      let list = null;
      for (const block of blocks) {
        if (block.t === 'li' || block.t === 'oli') {
          const tag = block.t === 'li' ? 'UL' : 'OL';
          if (!list || list.tagName !== tag) {
            list = document.createElement(tag);
            article.appendChild(list);
          }
          const li = document.createElement('li');
          li.textContent = block.x;
          list.appendChild(li);
          continue;
        }
        list = null;
        const node = document.createElement(block.t === 'note' ? 'p' : block.t);
        if (block.t === 'note') node.className = 'note';
        node.textContent = block.x;
        article.appendChild(node);
      }
    }

    function render(index) {
      current = index;
      const stop = stops[index];
      el('modalCount').textContent = `${LABEL.stop} ${index + 1} ${LABEL.of} ${stops.length}`;
      el('modalPhotoBox').classList.toggle('stop-photo--group', stop.group);
      el('modalPhoto').src = stop.image;
      el('modalPhoto').alt = stop.name;
      el('modalName').textContent = stop.name;
      el('modalRole').textContent = stop.role;
      el('modalTitle').textContent = stop.headline;
      el('modalSub').hidden = !stop.sub;
      el('modalSub').textContent = stop.sub || '';
      renderArticle(stop.blocks);

      const prev = stops[index - 1];
      const next = stops[index + 1];
      el('modalPrev').disabled = !prev;
      el('modalNext').disabled = !next;
      el('modalPrev').querySelector('span').textContent = prev ? prev.name : '';
      el('modalNext').querySelector('span').textContent = next ? next.name : '';
      modal.scrollTop = 0;
    }

    function lockPage() {
      savedY = window.scrollY;
      Object.assign(document.body.style, { position: 'fixed', top: `-${savedY}px`, left: '0', right: '0', width: '100%' });
    }

    function unlockPage() {
      Object.assign(document.body.style, { position: '', top: '', left: '', right: '', width: '' });
      window.scrollTo(0, savedY);
      requestAnimationFrame(() => window.scrollTo(0, savedY));
    }

    function open(index, trigger) {
      if (isOpen) { render(index); return; }
      isOpen = true;
      opener = trigger || null;
      lockPage();
      render(index);
      modal.hidden = false;
      requestAnimationFrame(() => modal.classList.add('is-visible'));
      // زر الرجوع / سحبة الرجوع بالجوال تقفل المودال بدل ما تطلع من الصفحة.
      history.pushState({ stopModal: true }, '');
      el('modalClose').focus({ preventScroll: true });
    }

    function close(fromHistory = false) {
      if (!isOpen) return;
      // الإغلاق الفعلي يصير مرة وحدة من popstate عشان المتصفح ما يحرّك الصفحة بعدنا.
      if (!fromHistory && history.state && history.state.stopModal) {
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

    document.querySelectorAll('[data-stop]').forEach((btn) => {
      btn.addEventListener('click', () => open(Number(btn.dataset.stop), btn));
    });
    el('modalClose').addEventListener('click', () => close());
    el('modalPrev').addEventListener('click', () => current > 0 && render(current - 1));
    el('modalNext').addEventListener('click', () => current < stops.length - 1 && render(current + 1));
    window.addEventListener('popstate', () => close(true));
    document.addEventListener('keydown', (e) => {
      if (!isOpen) return;
      if (e.key === 'Escape') close();
      // الأسهم تتبع اتجاه الصفحة: بالعربي اليسار = التالي.
      const rtl = document.documentElement.dir === 'rtl';
      if (e.key === (rtl ? 'ArrowLeft' : 'ArrowRight') && current < stops.length - 1) render(current + 1);
      if (e.key === (rtl ? 'ArrowRight' : 'ArrowLeft') && current > 0) render(current - 1);
    });
  </script>
</body>
</html>
