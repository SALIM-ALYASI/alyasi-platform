<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#0B1F3A">
<title>منصة الياسي</title>
<meta name="description" content="منصة الياسي للبرمجيات — بدية، عُمان. Web · Apps · AI · Media.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700;800&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box}
  body{margin:0;background:#0B1F3A;color:#FFFFFF;font-family:'Tajawal',sans-serif;min-height:100vh;display:flex;justify-content:center;padding:48px 24px}
  main{width:100%;max-width:520px;display:flex;flex-direction:column;gap:24px}
  .meta{display:flex;gap:8px;align-items:center}
  .tag{background:#08172B;color:#E5E7EB;border:1px solid #6E6E73;border-radius:999px;padding:4px 14px;font-size:14px;font-weight:700}
  .city{color:#E5E7EB;font-size:15px}
  h1{font-size:clamp(34px,8vw,48px);line-height:1.2;margin:0;font-weight:800}
  .photo{display:block;width:100%;aspect-ratio:1/1;border-radius:24px;overflow:hidden;background:#08172B;border:1px solid #6E6E73;box-shadow:0 12px 32px rgba(0,0,0,.35)}
  .photo img{width:100%;height:100%;object-fit:cover;display:block}
  .btn{display:flex;align-items:center;justify-content:center;gap:8px;padding:16px 24px;border-radius:999px;font-family:inherit;font-size:17px;font-weight:700;text-decoration:none;transition:background .2s,color .2s}
  .btn-primary{background:#FFFFFF;color:#0B1F3A}
  .btn-primary:hover{background:#E5E7EB}
  .btn-secondary{background:transparent;color:#FFFFFF;border:1.5px solid #6E6E73}
  .btn-secondary:hover{background:#08172B}
  .btn:focus-visible{outline:2px solid #FFFFFF;outline-offset:2px}
</style>
</head>
<body>
<main>
  <div class="meta">
    <span class="tag">متجر</span>
    <span class="city">بدية</span>
  </div>
  <h1>منصة الياسي</h1>
  <a class="photo" href="https://www.google.com/maps/place/%D8%A7%D9%84%D9%8A%D8%A7%D8%B3%D9%8A+%D9%84%D9%84%D8%A8%D8%B1%D9%85%D8%AC%D9%8A%D8%A7%D8%AA%E2%80%AD/@22.4464444,58.8101003,17.56z/data=!4m6!3m5!1s0x3e90730025dcdc0f:0xba948d129186c281!8m2!3d22.4493693!4d58.810725!16s%2Fg%2F11wbm1k4tp" target="_blank" rel="noopener">
    <img src="{{ asset('images/alyasi-bidiyah-cover.png') }}" alt="منصة الياسي">
  </a>
  <a class="btn btn-primary" href="https://www.google.com/maps/place/%D8%A7%D9%84%D9%8A%D8%A7%D8%B3%D9%8A+%D9%84%D9%84%D8%A8%D8%B1%D9%85%D8%AC%D9%8A%D8%A7%D8%AA%E2%80%AD/@22.4464444,58.8101003,17.56z/data=!4m6!3m5!1s0x3e90730025dcdc0f:0xba948d129186c281!8m2!3d22.4493693!4d58.810725!16s%2Fg%2F11wbm1k4tp" target="_blank" rel="noopener">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
    موقعي
  </a>
  <a class="btn btn-secondary" href="tel:+96898881054">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/></svg>
    اتصل بنا
  </a>
</main>
</body>
</html>
