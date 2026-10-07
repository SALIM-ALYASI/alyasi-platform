<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>بطاقة تجريبية — ALYASI</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap">
<style>
  :root {
    --bg: #0B1F3A; --band: rgba(19, 35, 63, .55); --panel: rgba(16, 32, 58, .62);
    --glass-line: rgba(255, 255, 255, .18); --tx-on-band: #FFFFFF; --mu-on-band: #DCE4F1; --gold: #D8B56A;
  }
  * { box-sizing: border-box; }
  html { background: var(--bg); }
  html, body { margin: 0; font-family: Tajawal, system-ui, sans-serif; }
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

  .photo { position: absolute; left: calc(var(--w) * -.03); bottom: 0; width: var(--photo-w); pointer-events: none; }
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
  .lead { width: 80vw; margin: 44px 0 -12px; text-align: center; color: #F2F5FA; font-size: clamp(14px, 3.6vw, 21px);
          font-weight: 700; line-height: 1.9; text-shadow: 0 2px 10px rgba(0, 0, 0, .55); }
  .lead::before { content: ""; display: block; width: 46px; height: 2px; margin: 0 auto 12px; background: var(--gold); border-radius: 2px; }
  .lead--outro { margin: 48px 0 24px; color: var(--gold); }
  .lead a { color: #FFFFFF; }

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
  .card--panel .photo--left { left: calc(var(--w) * -.02); right: auto; width: var(--left-w); }
  .card--panel .photo--right { right: calc(var(--w) * -.02); left: auto; width: var(--right-w); }
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
            'lead' => 'من سايبر إكس عُمان 2026 في مسقط، أنقل لكم خمس محطات توقفت عندها. ونبدأ بالسؤال الأصعب: ماذا لو وقع الاختراق فعلًا؟',
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
            'subtitle' => 'مدير تقنية المعلومات · SOFPITAL',
            'image' => 'images/demo/card-rineel-v4.png',
            'ratio' => 1,
            'headline' => 'الأمن السيبراني في القطاع الصحي حماية للمرضى واستمرار للرعاية',
            'paragraphs' => [
                'أكد رينيل وحيد، خلال مؤتمر سايبر إكس عُمان 2026، أن الأمن السيبراني في القطاع الصحي يرتبط مباشرة بسلامة المرضى. واستهل كلمته بمشهد لمريض يصل إلى الطوارئ بينما تتعطل الأنظمة، فلا يستطيع الفريق الطبي الوصول إلى سجله أو معرفة أدويته، موضحًا أن الهجمات قد تؤثر في قرارات علاجية عاجلة.',
                'وأشار إلى أن حساسية البيانات الصحية، وكثرة الأجهزة الطبية المتصلة، والأنظمة القديمة تجعل حماية المؤسسات الصحية أكثر تعقيدًا. وطرح خارطة طريق من خمس مراحل: حصر الأجهزة والأنظمة والموردين، وإتقان أساسيات الحماية، واكتشاف الهجمات واحتواؤها، والاستعداد للتعافي، وبناء ثقافة أمنية لدى الموظفين.',
                'وشدد على أهمية تقسيم الشبكات للحد من انتشار الهجوم، ووضع إجراءات واضحة للاستجابة تمنع فصل الأجهزة عشوائيًا أثناء الأزمة. كما دعا إلى تدريبات توعوية بالعربية والإنجليزية وتشجيع الإبلاغ عن الرسائل المشبوهة.',
                'وأوضح أن الذكاء الاصطناعي يمكن استخدامه للدفاع والهجوم، وأن توظيفه في الرعاية الصحية يتطلب حماية بيانات المرضى وضبط مشاركتها. واختتم بأن الهدف من الأمن السيبراني هو حماية الأشخاص الذين وثقوا بالمؤسسة، وضمان استمرار تقديم الرعاية لهم.',
            ],
        ],
        [
            'name' => 'شابيل بشير',
            'lead' => 'حماية المريض تبدأ باكتشاف الهجوم قبل أن يصل إليه. وهنا يأتي دور الذكاء الاصطناعي في مساندة فرق الأمن، مع شابيل بشير من ESET.',
            'subtitle' => 'ESET',
            'image' => 'images/demo/card-guest4-v2.png',
            'ratio' => 1,
            'headline' => 'الذكاء الاصطناعي لا يستبدل المحلل البشري… بل يساعده على القرار أسرع',
            'paragraphs' => [
                'أكد شابيل بشير، مهندس ما قبل البيع الرئيسي في شركة ESET، خلال مؤتمر سايبر إكس عُمان 2026، أن المهاجمين باتوا يعملون «بسرعة الآلة» بينما يعمل المدافعون بسرعة البشر، مشيرًا إلى أن أسرع اختراق رُصد استغرق 27 ثانية فقط، وأن كثرة التنبيهات باتت ترهق فرق الأمن.',
                'وأوضح أن القيمة الحقيقية للذكاء الاصطناعي ليست في اكتشاف تهديدات أكثر، بل في فهمها: الكشف بالسلوك بدل التواقيع المعروفة، وتحليل البرمجيات الخبيثة في السحابة خلال دقائق، وربط عشرات التنبيهات في «قصة هجوم» واحدة، وترتيب الأولويات حسب المخاطر.',
                'وأشار إلى أن نحو 80% من الشركات تستخدم الذكاء الاصطناعي، ما يوسّع سطح الهجوم، داعيًا إلى حماية محادثات الموظفين مع أدوات الذكاء الاصطناعي ومنع تسرب البيانات الحساسة، ومراقبة سلوك وكلاء الذكاء الاصطناعي.',
                'واختتم بأن الذكاء الاصطناعي لا يحل محل المحلل البشري، بل يمنحه السياق والمعلومة ليتخذ القرار الصحيح بسرعة أكبر.',
            ],
        ],
    ];
@endphp

  @foreach ($cards as $card)
    @if (! empty($card['lead']))
      <p class="lead">{{ $card['lead'] }}</p>
    @endif
    <article class="card" style="--ratio: {{ $card['ratio'] }}"
             data-headline="{{ $card['headline'] }}"
             data-paragraphs='@json($card['paragraphs'], JSON_UNESCAPED_UNICODE)'>
      <div class="band" role="button" tabindex="0" aria-expanded="false">
        <div class="photo">
          <img src="{{ asset($card['image']) }}" alt="{{ $card['name'] }}">
        </div>
        <div class="title">
          <span class="arrow" aria-hidden="true">⌄</span>
          <div class="title-text">
            <h2>{{ $card['name'] }}</h2>
            <small>{{ $card['subtitle'] ?? 'سايبر إكس عُمان 2026' }}</small>
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
      $panelBlocks = json_decode(file_get_contents(resource_path('views/demo/panel-ai-governance.json')), true);
  @endphp
  <p class="lead">لكن الذكاء الاصطناعي نفسه يحتاج إلى من يضبطه: ما البيانات التي يصل إليها؟ ومن يتحمّل القرار؟ هذا ما ناقشته الحلقة الختامية.</p>
  <article class="card card--panel" style="--ratio-left: 1; --ratio-right: {{ 506 / 900 }}"
           data-blocks='@json($panelBlocks, JSON_UNESCAPED_UNICODE)'>
    <div class="band" role="button" tabindex="0" aria-expanded="false">
      <div class="photo photo--left">
        <img src="{{ asset('images/demo/panel-ramya.png') }}" alt="راميا سانكاري كارثيك — مديرة الجلسة">
      </div>
      <div class="panel-center">
        <span class="panel-btn">جلسة نقاش <span class="arrow" aria-hidden="true">⌄</span></span>
      </div>
      <div class="photo photo--right">
        <img src="{{ asset('images/demo/panel-group.png') }}" alt="المشاركون في حلقة النقاش">
      </div>
    </div>
    <div class="body">
      <div>
        <div class="text"></div>
      </div>
    </div>
  </article>

  <p class="lead lead--outro">خمس محطات، ورسالة واحدة: الاستعداد واختبار الجاهزية وحماية البيانات هي ما يُبقي المؤسسات قائمة عندما تتعطل الأنظمة. التفاصيل كاملة على <a href="https://alyasi.dev">alyasi.dev</a>.</p>

  <script>
    function setupCard(card) {
      const band = card.querySelector('.band');
      const textEl = card.querySelector('.text');
      const blocks = card.dataset.blocks
        ? JSON.parse(card.dataset.blocks)
        : [{ t: 'h3', x: card.dataset.headline }, ...JSON.parse(card.dataset.paragraphs).map((x) => ({ t: 'p', x }))];
      // نص طويل (حلقة النقاش) يُكتب بخطوات أكبر عشان يخلص بحوالي 10 ثواني.
      const totalChars = blocks.reduce((n, b) => n + [...b.x].length, 0);
      const step = Math.max(2, Math.ceil(totalChars / 650));
      // أي فتح/إغلاق جديد يلغي الكتابة الجارية لهذي البطاقة بس.
      let typingRun = 0;

      async function typeInto(el, text, run) {
        el.classList.add('typing');
        const chars = [...text];
        for (let i = 0; i < chars.length; i += step) {
          if (run !== typingRun) return false;
          el.textContent += chars.slice(i, i + step).join('');
          await new Promise((r) => setTimeout(r, 16));
        }
        el.classList.remove('typing');
        return true;
      }

      async function typeText() {
        const run = ++typingRun;
        textEl.innerHTML = '';
        let list = null;

        for (const block of blocks) {
          let el;
          if (block.t === 'li' || block.t === 'oli') {
            const tag = block.t === 'li' ? 'UL' : 'OL';
            if (!list || list.tagName !== tag) {
              list = document.createElement(tag);
              textEl.appendChild(list);
            }
            el = document.createElement('li');
            list.appendChild(el);
          } else {
            list = null;
            el = document.createElement(block.t === 'note' ? 'p' : block.t);
            if (block.t === 'note') el.className = 'note';
            textEl.appendChild(el);
          }
          if (!await typeInto(el, block.x, run)) return;
        }
      }

      function toggle() {
        const open = card.classList.toggle('is-open');
        band.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
          typeText();
        } else {
          typingRun++;
        }
      }

      band.addEventListener('click', toggle);
      band.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); } });
    }

    document.querySelectorAll('.card').forEach(setupCard);
  </script>
</body>
</html>
