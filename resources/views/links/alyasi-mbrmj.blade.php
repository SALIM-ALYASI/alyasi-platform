<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ALYASI — روابطي</title>
<meta name="description" content="تابع سالم الحجري (ALYASI) على كل المنصات.">
<meta name="theme-color" content="#0B1F3A">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700&family=Tajawal:wght@400;500;700&display=swap">
<link rel="stylesheet" href="{{ asset('links/style.css') }}">
<script>
  (function () {
    var t = null;
    try { t = localStorage.getItem('alyasi-theme'); } catch (e) {}
    if (!t) t = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', t);
  })();
</script>
</head>
<body>
  <div class="page">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <main class="wrap">
      <button class="theme-btn anim-up" id="themeBtn" aria-label="تبديل الوضع الفاتح والغامق">
        <i class="fa-solid fa-moon"></i>
      </button>

      <header class="head">
        <div class="logo-box">
          <div class="ring ring-1"></div>
          <div class="ring ring-2"></div>
          <div class="logo">
            <img id="logoImg" src="{{ asset('links/logo-light.png') }}" alt="ALYASI — Create · Connect · Innovate">
          </div>
        </div>
        <h1 class="title anim-up">سالم الحجري <span>· تابعني على منصاتي</span></h1>
      </header>

      <nav class="links" id="links"></nav>

      <footer class="foot anim-up">
        <span dir="ltr">© 2026 ALYASI</span>
        <button class="icon-btn" id="replayBtn" aria-label="إعادة الحركة"><i class="fa-solid fa-rotate-right"></i></button>
      </footer>
    </main>
  </div>
  <script src="{{ asset('links/script.js') }}"></script>
</body>
</html>
