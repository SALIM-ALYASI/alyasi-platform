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
    cursor: pointer;
    user-select: none;
    display: flex;
    align-items: center;
    padding-inline-start: 18px;
    padding-inline-end: calc(var(--photo-w) + 10px); /* مكان الصورة يسار الشريط */
  }

  .photo { position: absolute; left: calc(var(--w) * -.03); bottom: 0; width: var(--photo-w); pointer-events: none; }
  .photo img { display: block; width: 100%; height: auto; }

  .title { flex: 1; display: flex; align-items: center; justify-content: space-between; gap: 10px; min-width: 0; }
  .title-text { min-width: 0; }
  .title h2 { margin: 0; color: var(--tx-on-band); font-size: clamp(14px, 3.4vw, 30px); font-weight: 800; line-height: 1.3; }
  .title small { display: block; color: var(--gold); font-weight: 700; font-size: clamp(10px, 2.2vw, 16px); margin-top: 2px; }

  .arrow { flex: none; width: clamp(30px, 6vw, 46px); height: clamp(30px, 6vw, 46px); border-radius: 50%;
           border: 1px solid var(--gold); color: var(--gold); display: grid; place-items: center;
           font-size: clamp(14px, 3vw, 22px); transition: transform .35s ease, background .2s, color .2s; }
  .card.is-open .arrow { transform: rotate(180deg); background: var(--gold); color: var(--band); }

  /* النص المنسدل تحت الشريط */
  .body { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .4s ease; background: var(--panel); }
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
        <div class="title-text">
          <h2>عنوان تجريبي</h2>
          <small>CyberX Oman 2026</small>
        </div>
        <span class="arrow" aria-hidden="true">⌄</span>
      </div>
    </div>
    <div class="body" id="cardBody">
      <div>
        <p>هذا نص تجريبي يظهر عند الضغط على السهم. يمكن هنا كتابة نبذة عن الضيف، منصبه، وأبرز ما قاله في المقابلة. اضغط مرة أخرى ليختفي النص ويبقى الشريط والصورة فقط.</p>
      </div>
    </div>
  </article>

  <script>
    const card = document.getElementById('card');
    const band = card.querySelector('.band');
    function toggle() {
      const open = card.classList.toggle('is-open');
      band.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    band.addEventListener('click', toggle);
    band.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); } });
  </script>
</body>
</html>
