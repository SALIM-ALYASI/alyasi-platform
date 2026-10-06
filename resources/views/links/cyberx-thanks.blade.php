<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>شكرًا لكم — CyberX Oman 2026 | ALYASI</title>
<meta name="description" content="شكرًا لكم على التصوير معي في CyberX Oman 2026 — سالم الحجري، منصة الياسي.">
<meta name="theme-color" content="#0B1F3A">
<meta property="og:type" content="website">
<meta property="og:title" content="شكرًا لكم على التصوير معي 🤍">
<meta property="og:description" content="ذكرى من CyberX Oman 2026 — منصة الياسي">
<meta property="og:image" content="{{ asset('images/cyberx-2026/thanks-group.jpg') }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta name="twitter:card" content="summary_large_image">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap">
<style>
  :root {
    --bg: #F4F7FC; --card: #FFFFFF; --tx: #0B1F3A; --mu: #4A5D7A;
    --gold: #B8913F; --gold-soft: rgba(184,145,63,.14); --line: rgba(11,31,58,.10);
    --shadow: 0 18px 50px rgba(11,31,58,.14);
  }
  @media (prefers-color-scheme: dark) {
    :root {
      --bg: #0B1F3A; --card: #10284A; --tx: #FFFFFF; --mu: #A9B8CF;
      --gold: #D8B56A; --gold-soft: rgba(216,181,106,.14); --line: rgba(255,255,255,.10);
      --shadow: 0 18px 50px rgba(0,0,0,.45);
    }
  }
  * { box-sizing: border-box; }
  html, body { margin: 0; background: var(--bg); color: var(--tx); }
  body { font-family: Tajawal, system-ui, sans-serif; min-height: 100vh; }
  .wrap { max-width: 760px; margin: 0 auto; padding: 28px 16px 40px; }
  .brand { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 22px; }
  .brand img { height: 44px; width: auto; }
  .brand .logo-dark { display: none; }
  @media (prefers-color-scheme: dark) {
    .brand .logo-light { display: none; }
    .brand .logo-dark { display: block; }
  }
  .brand span { font-weight: 800; letter-spacing: .18em; font-size: 15px; }
  .event { text-align: center; color: var(--gold); font-weight: 700; font-size: 14px; letter-spacing: .04em; margin-bottom: 18px; }
  .photo { margin: 0; border-radius: 22px; overflow: hidden; box-shadow: var(--shadow); border: 1px solid var(--line); background: var(--card); }
  .photo img { display: block; width: 100%; height: auto; }
  h1 { text-align: center; font-size: clamp(24px, 6vw, 34px); font-weight: 800; margin: 26px 0 10px; }
  .ornament { display: flex; align-items: center; justify-content: center; gap: 10px; color: var(--gold); margin-bottom: 14px; }
  .ornament::before, .ornament::after { content: ""; height: 1px; width: 64px; background: currentColor; opacity: .6; }
  .msg { text-align: center; color: var(--mu); font-size: 17px; line-height: 1.9; margin: 0 auto 26px; max-width: 560px; }
  /* منع حفظ الصورة: بدون سحب، بدون ضغط مطوّل (قائمة "حفظ الصورة" بالآيفون)،
     وطبقة شفافة فوقها تمنع الضغط على الصورة نفسها. */
  .photo { position: relative; -webkit-touch-callout: none; -webkit-user-select: none; user-select: none; }
  .photo img { pointer-events: none; -webkit-user-drag: none; }
  .photo::after { content: ""; position: absolute; inset: 0; }
  .quote { text-align: center; font-size: clamp(19px, 5vw, 23px); font-weight: 700; line-height: 1.8; margin: 0 auto 14px; max-width: 560px; }
  .quote span { color: var(--gold); }
  .foot { text-align: center; color: var(--mu); font-size: 13px; margin-top: 30px; }
  .foot a { color: var(--gold); text-decoration: none; }
</style>
</head>
<body>
  <main class="wrap">
    <div class="brand">
      <img src="{{ asset('images/logo/logo-navy-icon.png') }}" alt="" class="logo-light">
      <img src="{{ asset('images/logo/logo-white-trimmed.png') }}" alt="" class="logo-dark">
      <span>ALYASI</span>
    </div>

    <div class="event">CyberX Oman 2026 · سايبر إكس عُمان</div>

    <figure class="photo">
      <img src="{{ asset('images/cyberx-2026/thanks-group.jpg') }}" alt="صورة جماعية مع سالم الحجري في CyberX Oman 2026" width="1496" height="1051" draggable="false">
    </figure>

    <h1>شكرًا لكم على التصوير معي</h1>
    <div class="ornament">✦</div>

    <p class="quote">بعض اللحظات تمرّ، وبعضها <span>يبقى</span>… وهذه الصورة من اللحظات التي تبقى.</p>

    <p class="msg">
      شكرًا لحضوركم الجميل، ولابتساماتكم التي جعلت يومي في CyberX عُمان 2026 أجمل.
      سعيد بمعرفتكم، وممتن لكل لحظة جمعتنا. 🤍
      <br>— سالم الحجري
    </p>

    <p class="foot">
      <a href="https://alyasi.dev">alyasi.dev</a> · © 2026 ALYASI
    </p>
  </main>
  <script>
    document.addEventListener('contextmenu', (e) => { if (e.target.closest('.photo')) e.preventDefault(); });
    document.addEventListener('dragstart', (e) => { if (e.target.closest('.photo')) e.preventDefault(); });
  </script>
</body>
</html>
