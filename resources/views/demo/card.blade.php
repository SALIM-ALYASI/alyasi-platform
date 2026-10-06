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
    --bg: #FFFFFF; --band: #13233F; --panel: #1B2F52; --tx-on-band: #FFFFFF; --mu-on-band: #C9D4E6; --gold: #D8B56A;
  }
  @media (prefers-color-scheme: dark) {
    :root { --bg: #0B1F3A; --band: #17345E; --panel: #10284A; }
  }
  * { box-sizing: border-box; }
  html, body { margin: 0; background: var(--bg); font-family: Tajawal, system-ui, sans-serif; }
  body { min-height: 100vh; display: flex; justify-content: center; padding: 0 0 40px; }

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
  .body { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .4s ease; background: var(--panel); border-radius: 0 0 12px 12px; }
  .card.is-open .body { grid-template-rows: 1fr; }
  .body > div { overflow: hidden; }
  .text { padding: 14px 20px 20px; }
  .text p { margin: 0 0 12px; color: var(--mu-on-band); font-size: clamp(13px, 3.2vw, 18px); line-height: 1.9; }
  .text p:last-child { margin-bottom: 0; }
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
            'subtitle' => 'مدير تقنية المعلومات · SOFPITAL',
            'image' => 'images/demo/card-rineel-v2.png',
            'ratio' => 1,
            'headline' => '«من الهشاشة إلى المرونة: بناء خارطة طريق للأمن السيبراني في القطاع الصحي»',
            'paragraphs' => [
                'أكد رينيل وحيد، خلال مؤتمر سايبر إكس عُمان 2026، أن الأمن السيبراني في القطاع الصحي يرتبط مباشرة بسلامة المرضى واستمرار الرعاية. وبدأ بمشهد لمريض يصل إلى الطوارئ بينما تتعطل الأنظمة، فلا يستطيع الفريق الوصول إلى سجله الطبي أو معرفة أدويته، موضحًا أن الهجمات قد تعطل قرارات علاجية عاجلة.',
                'وأوضح أن المؤسسات الصحية تواجه تحديات بسبب حساسية بيانات المرضى، وكثرة الأجهزة الطبية المتصلة، والاعتماد على أنظمة قديمة. وقدم خارطة طريق من خمس مراحل تبدأ بحصر الأجهزة والأنظمة والموردين، ثم إتقان أساسيات الحماية، مثل التحديثات وتأمين كلمات المرور والتوعية بالتصيد.',
                'وتشمل الخارطة اكتشاف الهجمات واحتواءها عبر تقسيم الشبكات، والاستعداد للتعافي بإجراءات واضحة تمنع التصرف العشوائي أثناء الأزمة. كما دعا إلى بناء ثقافة أمنية تشجع الموظفين على الإبلاغ عن الرسائل المشبوهة، وتنفيذ تدريبات توعوية بالعربية والإنجليزية.',
                'وأشار إلى أن الذكاء الاصطناعي أداة للدفاع والهجوم معًا، وأن استخدامه يتطلب حوكمة تحمي بيانات المرضى من التسرب. واختتم بأن حماية الأنظمة الصحية تعني حماية الأشخاص الذين وضعوا ثقتهم في المؤسسة.',
            ],
        ],
    ];
@endphp

  @foreach ($cards as $card)
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

  <script>
    function setupCard(card) {
      const band = card.querySelector('.band');
      const textEl = card.querySelector('.text');
      const headlineText = card.dataset.headline;
      const paragraphs = JSON.parse(card.dataset.paragraphs);
      // أي فتح/إغلاق جديد يلغي الكتابة الجارية لهذي البطاقة بس.
      let typingRun = 0;

      async function typeInto(el, text, run) {
        el.classList.add('typing');
        const chars = [...text];
        for (let i = 0; i < chars.length; i += 2) {
          if (run !== typingRun) return false;
          el.textContent += chars.slice(i, i + 2).join('');
          await new Promise((r) => setTimeout(r, 16));
        }
        el.classList.remove('typing');
        return true;
      }

      // العنوان أولًا ثم الفقرات، كلها حرف حرف.
      async function typeText() {
        const run = ++typingRun;
        textEl.innerHTML = '';

        const headline = document.createElement('h3');
        textEl.appendChild(headline);
        if (!await typeInto(headline, headlineText, run)) return;

        for (const paragraph of paragraphs) {
          const p = document.createElement('p');
          textEl.appendChild(p);
          if (!await typeInto(p, paragraph, run)) return;
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
