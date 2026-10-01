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
  <a class="photo" href="https://maps.app.goo.gl/sars1AV3BdRk4ioM6" target="_blank" rel="noopener">
    <img src="{{ asset('images/alyasi-bidiyah-cover.png') }}" alt="منصة الياسي">
  </a>
  <a class="btn btn-primary" href="https://maps.app.goo.gl/sars1AV3BdRk4ioM6" target="_blank" rel="noopener">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
    موقعي
  </a>
  <a class="btn btn-secondary" href="https://wa.me/96898881054" target="_blank" rel="noopener">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.5 2 2 6.5 2 12c0 1.8.5 3.5 1.3 5L2 22l5.2-1.4c1.4.8 3.1 1.2 4.8 1.2 5.5 0 10-4.5 10-10S17.5 2 12 2zm0 18c-1.5 0-2.9-.4-4.1-1.1l-.3-.2-3.1.8.8-3-.2-.3C4.4 14.9 4 13.5 4 12c0-4.4 3.6-8 8-8s8 3.6 8 8-3.6 8-8 8zm4.4-6c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.5.1-.2.2-.6.8-.7.9-.1.2-.3.2-.5.1-1.4-.7-2.3-1.2-3.2-2.8-.2-.4.2-.4.6-1.2.1-.1 0-.3 0-.4-.1-.1-.5-1.3-.7-1.7-.2-.5-.4-.4-.5-.4h-.4c-.2 0-.4.1-.6.3-.2.2-.8.8-.8 2s.8 2.3 1 2.5c.1.1 1.7 2.6 4.1 3.6.6.2 1 .4 1.4.5.6.2 1.1.1 1.5-.1.5-.1 1.4-.6 1.6-1.1.2-.5.2-.9.1-1.1 0-.1-.2-.2-.4-.3z"/></svg>
    واتساب
  </a>
</main>
</body>
</html>
