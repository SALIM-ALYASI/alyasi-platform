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
    --bg: #F4F7FC; --card: #FFFFFF; --tx: #0B1F3A; --mu: #4A5D7A;
    --gold: #B8913F; --line: rgba(11,31,58,.10); --photo-bg: linear-gradient(160deg, #E8EEF8, #D5DFEE);
    --shadow: 0 16px 40px rgba(11,31,58,.14);
  }
  @media (prefers-color-scheme: dark) {
    :root {
      --bg: #0B1F3A; --card: #10284A; --tx: #FFFFFF; --mu: #A9B8CF;
      --gold: #D8B56A; --line: rgba(255,255,255,.10); --photo-bg: linear-gradient(160deg, #1B3A66, #0F2647);
      --shadow: 0 16px 40px rgba(0,0,0,.45);
    }
  }
  * { box-sizing: border-box; }
  html, body { margin: 0; background: var(--bg); color: var(--tx); font-family: Tajawal, system-ui, sans-serif; }
  body { min-height: 100vh; display: flex; align-items: flex-start; justify-content: center; padding: 40px 0; }

  /* البطاقة: عرض 80% من الشاشة، نصها صورة ونصها عنوان + سهم */
  .card { width: 80vw; background: var(--card); border: 1px solid var(--line); border-radius: 22px; box-shadow: var(--shadow); overflow: hidden; }
  .head { display: grid; grid-template-columns: 1fr 1fr; align-items: stretch; cursor: pointer; user-select: none; }
  .photo { background: var(--photo-bg); display: flex; align-items: flex-end; justify-content: center; min-height: 100%; }
  .photo img { display: block; width: 100%; height: auto; aspect-ratio: 465 / 445; object-fit: contain; }
  .title { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px; padding: 16px; text-align: center; }
  .title h2 { margin: 0; font-size: clamp(16px, 4.2vw, 28px); font-weight: 800; line-height: 1.4; }
  .title small { color: var(--gold); font-weight: 700; font-size: clamp(12px, 2.8vw, 16px); }
  .arrow { width: 44px; height: 44px; border-radius: 50%; border: 1px solid var(--gold); color: var(--gold); background: transparent;
           display: grid; place-items: center; font-size: 20px; cursor: pointer; transition: transform .35s ease, background .2s; }
  .card.is-open .arrow { transform: rotate(180deg); background: var(--gold); color: #0B1F3A; }

  /* النص المنسدل: ينزل تحت البطاقة عند الضغط، ويختفي بالضغطة الثانية */
  .body { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .4s ease; }
  .card.is-open .body { grid-template-rows: 1fr; }
  .body > div { overflow: hidden; }
  .body p { margin: 0; padding: 18px 22px 22px; border-top: 1px solid var(--line); color: var(--mu); font-size: clamp(14px, 3.4vw, 18px); line-height: 1.9; }
</style>
</head>
<body>
  <article class="card" id="card">
    <div class="head" role="button" tabindex="0" aria-expanded="false" aria-controls="cardBody">
      <div class="photo">
        <img src="{{ asset('images/demo/card-person.png') }}" alt="صورة الضيف">
      </div>
      <div class="title">
        <h2>عنوان تجريبي</h2>
        <small>CyberX Oman 2026</small>
        <span class="arrow" aria-hidden="true">⌄</span>
      </div>
    </div>
    <div class="body" id="cardBody">
      <div>
        <p>هذا نص تجريبي يظهر عند الضغط على السهم. يمكن هنا كتابة نبذة عن الضيف، منصبه، وأبرز ما قاله في المقابلة. اضغط السهم مرة أخرى ليختفي النص وتبقى الصورة والعنوان فقط.</p>
      </div>
    </div>
  </article>

  <script>
    const card = document.getElementById('card');
    const head = card.querySelector('.head');
    function toggle() {
      const open = card.classList.toggle('is-open');
      head.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    head.addEventListener('click', toggle);
    head.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); } });
  </script>
</body>
</html>
