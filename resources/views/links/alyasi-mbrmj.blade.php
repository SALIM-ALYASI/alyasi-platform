<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ALYASI — روابطي</title>
<meta name="description" content="تابع سالم الحجري (ALYASI) على كل المنصات — واتساب، X، إنستغرام، فيسبوك، لينكدإن، يوتيوب، تيك توك، وسناب شات.">
<meta property="og:title" content="ALYASI — روابطي">
<meta property="og:description" content="تابعني على كل منصاتي من صفحة واحدة.">
<meta property="og:image" content="{{ asset('images/alyasi-logo-mark.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap">
<style>
    * { box-sizing: border-box; }

    html, body {
        margin: 0;
        min-height: 100vh;
        font-family: 'Tajawal', sans-serif;
        background: radial-gradient(circle at 50% -10%, #16305a 0%, #0B1F3A 55%, #081527 100%);
    }

    a { text-decoration: none; }

    .wrap {
        max-width: 420px;
        margin: 0 auto;
        padding: 48px 24px 56px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .header {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
        margin-bottom: 40px;
    }

    .header img {
        width: 200px;
        height: auto;
        display: block;
    }

    .header .tagline {
        font-size: 13px;
        font-weight: 500;
        color: #d8b56a;
    }

    .links {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .links a {
        display: block;
        width: 100%;
        transition: transform .15s ease;
        filter: drop-shadow(0 8px 14px rgba(0,0,0,.4));
    }

    .links a:active {
        transform: scale(.97);
    }

    .links img {
        width: 100%;
        height: auto;
        display: block;
    }

    .footer {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        margin-top: 44px;
    }

    .footer .rule {
        width: 40px;
        height: 1px;
        background: rgba(216,181,106,.35);
    }

    .footer .brand {
        font-size: 11px;
        font-weight: 500;
        color: rgba(236,227,207,.55);
        letter-spacing: 1px;
    }
</style>
</head>
<body>

<div class="wrap">

    <div class="header">
        <img src="{{ asset('images/alyasi-logo-mark.png') }}" alt="ALYASI">
        <div class="tagline">تابعني على منصاتي</div>
    </div>

    <div class="links">
        <a href="https://wa.me/qr/2CWYHXAZ25JYO1" target="_blank" rel="noopener" aria-label="واتساب">
            <img src="{{ asset('images/social-badges/whatsapp.png') }}" alt="WhatsApp">
        </a>
        <a href="https://x.com/ALYASI_MBRMJ" target="_blank" rel="noopener" aria-label="X (تويتر)">
            <img src="{{ asset('images/social-badges/x.png') }}" alt="X">
        </a>
        <a href="https://www.instagram.com/alyasi_mbrmj" target="_blank" rel="noopener" aria-label="إنستغرام">
            <img src="{{ asset('images/social-badges/instagram.png') }}" alt="Instagram">
        </a>
        <a href="https://www.facebook.com/share/1MEuuTpQD4/" target="_blank" rel="noopener" aria-label="فيسبوك">
            <img src="{{ asset('images/social-badges/facebook.png') }}" alt="Facebook">
        </a>
        <a href="https://www.linkedin.com/in/سالم-الحجري-b735a721b" target="_blank" rel="noopener" aria-label="لينكدإن">
            <img src="{{ asset('images/social-badges/linkedin.png') }}" alt="LinkedIn">
        </a>
        <a href="https://youtube.com/@alyasiforchargers" target="_blank" rel="noopener" aria-label="يوتيوب">
            <img src="{{ asset('images/social-badges/youtube.png') }}" alt="YouTube">
        </a>
        <a href="https://www.tiktok.com/@alyasi_mbrmj" target="_blank" rel="noopener" aria-label="تيك توك">
            <img src="{{ asset('images/social-badges/tiktok.png') }}" alt="TikTok">
        </a>
        <a href="https://snapchat.com/t/pyHBRyl9" target="_blank" rel="noopener" aria-label="سناب شات">
            <img src="{{ asset('images/social-badges/snapchat.png') }}" alt="Snapchat">
        </a>
    </div>

    <div class="footer">
        <div class="rule"></div>
        <div class="brand">ALYASI · CREATE · CONNECT · INNOVATE</div>
    </div>

</div>

</body>
</html>
