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
    --photo-h: calc(var(--photo-w) * 445 / 465);
    width: var(--w);
    margin-top: calc(var(--photo-h) / 2 + 40px);
  }

  .band {
    position: relative;
    height: calc(var(--photo-h) / 2);
    min-height: 64px;
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
  .title h2 { margin: 0; color: var(--tx-on-band); font-size: clamp(14px, 3.4vw, 30px); font-weight: 800; line-height: 1.3; }
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
  .body p { margin: 0; padding: 16px 20px 20px; color: var(--mu-on-band); font-size: clamp(13px, 3.2vw, 18px); line-height: 1.9; }
</style>
</head>
<body>
  <article class="card" id="card">
    <div class="band" role="button" tabindex="0" aria-expanded="false" aria-controls="cardBody">
      <div class="photo">
        <img src="{{ asset('images/demo/card-person.png') }}" alt="صورة الضيف">
      </div>
      <div class="title">
        <span class="arrow" aria-hidden="true">⌄</span>
        <div class="title-text">
          <h2>عنوان تجريبي</h2>
          <small>CyberX Oman 2026</small>
        </div>
      </div>
    </div>
    <div class="body" id="cardBody">
      <div>
        <p id="cardText" data-text="هذا نص تجريبي يظهر عند الضغط على السهم. يمكن هنا كتابة نبذة عن الضيف، منصبه، وأبرز ما قاله في المقابلة. اضغط مرة أخرى ليختفي النص ويبقى الشريط والصورة فقط."></p>
      </div>
    </div>
  </article>

  <script>
    const card = document.getElementById('card');
    const band = card.querySelector('.band');
    const textEl = document.getElementById('cardText');
    const fullText = textEl.dataset.text;
    // نكتب حرف حرف، وأي فتح/إغلاق جديد يلغي الكتابة الجارية (typingRun).
    let typingRun = 0;

    async function typeText() {
      const run = ++typingRun;
      textEl.textContent = '';
      textEl.classList.add('typing');
      for (const ch of fullText) {
        if (run !== typingRun) return;
        textEl.textContent += ch;
        await new Promise((r) => setTimeout(r, ch === ' ' ? 18 : 35));
      }
      if (run === typingRun) textEl.classList.remove('typing');
    }

    function toggle() {
      const open = card.classList.toggle('is-open');
      band.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) {
        typeText();
      } else {
        typingRun++;
        textEl.classList.remove('typing');
      }
    }

    band.addEventListener('click', toggle);
    band.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); } });
  </script>
</body>
</html>
