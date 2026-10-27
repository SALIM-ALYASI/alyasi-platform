{{-- صفحة مشاركة فيديو «فريق سراج عمان 2026» (مدرسة زينب بنت الرسول للتعليم الأساسي).
     صفحة مستقلة برابط مباشر: ما تظهر في محركات البحث ولا في خريطة الموقع.
     الملفات نفسها في public/storage/videos/siraj-oman-2026 (خارج git). --}}
@php
    $dir = 'storage/videos/siraj-oman-2026';
    $files = [
        'web' => "{$dir}/siraj-oman-2026-1080.mp4",
        'hd' => "{$dir}/siraj-oman-2026-4k.mp4",
        'poster' => "{$dir}/poster.jpg",
    ];
    $size = function (string $path): ?string {
        $full = public_path($path);
        if (! is_file($full)) {
            return null;
        }
        $mb = filesize($full) / 1048576;

        return $mb >= 1024 ? number_format($mb / 1024, 1).' جيجا' : number_format($mb).' ميجا';
    };
    $title = 'فريق سراج عمان 2026';
    $school = 'مدرسة زينب بنت الرسول للتعليم الأساسي';
    $pageUrl = url('/siraj-oman-2026');
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | {{ $school }}</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="فيديو {{ $title }} — {{ $school }}، المديرية العامة للتعليم بمحافظة الداخلية.">
    <meta name="theme-color" content="#0f3b3f">
    <meta property="og:type" content="video.other">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $school }} — المديرية العامة للتعليم بمحافظة الداخلية">
    <meta property="og:url" content="{{ $pageUrl }}">
    <meta property="og:image" content="{{ asset($files['poster']) }}">
    <meta property="og:locale" content="ar_OM">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100svh;
            display: flex; flex-direction: column; align-items: center;
            gap: 22px; padding: 36px 16px 40px;
            font-family: 'IBM Plex Sans Arabic', system-ui, sans-serif;
            color: #f4efe6;
            background: radial-gradient(120% 80% at 50% 0%, #17575c 0%, #0f3b3f 45%, #0a2629 100%);
        }
        header { text-align: center; max-width: 520px; }
        .badge { display: inline-block; margin-bottom: 10px; padding: 4px 14px; border-radius: 999px; background: rgba(255, 255, 255, .1); color: #f6d9a8; font-size: 13.5px; font-weight: 600; }
        h1 { font-size: clamp(26px, 7vw, 36px); line-height: 1.4; }
        .school { margin-top: 6px; font-size: 16.5px; font-weight: 600; color: #ffd7cf; }
        .dept { margin-top: 2px; font-size: 14px; color: rgba(244, 239, 230, .75); }
        .player {
            width: min(100%, 420px); aspect-ratio: 1080 / 1896;
            border-radius: 22px; overflow: hidden; background: #061a1c;
            box-shadow: 0 22px 50px rgba(0, 0, 0, .45), 0 0 0 1px rgba(255, 255, 255, .08);
        }
        .player video { display: block; width: 100%; height: 100%; object-fit: contain; background: #061a1c; }
        .downloads { display: flex; flex-direction: column; gap: 10px; width: min(100%, 420px); }
        .btn {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            padding: 15px 18px; border-radius: 14px;
            font: inherit; font-size: 17px; font-weight: 700; text-decoration: none;
        }
        .btn--main { background: #a3123a; color: #fff; box-shadow: 0 10px 24px rgba(163, 18, 58, .4); }
        .btn--main:hover { background: #8b0f31; }
        .btn--light { background: rgba(255, 255, 255, .08); color: #f4efe6; border: 1px solid rgba(255, 255, 255, .18); font-size: 15.5px; font-weight: 600; }
        .btn--light:hover { background: rgba(255, 255, 255, .14); }
        .btn small { font-size: 13px; font-weight: 600; opacity: .85; }
        .btn:focus-visible { outline: 3px solid #f6d9a8; outline-offset: 3px; }
        .hint { text-align: center; font-size: 13.5px; line-height: 1.8; color: rgba(244, 239, 230, .7); }
        footer { margin-top: auto; padding-top: 8px; font-size: 12.5px; color: rgba(244, 239, 230, .55); }
        footer a { color: inherit; }
        svg { flex: 0 0 auto; }
    </style>
</head>
<body>
    <header>
        <span class="badge">الموسوعة العُمانية · المجلد 9</span>
        <h1>{{ $title }}</h1>
        <p class="school">{{ $school }}</p>
        <p class="dept">المديرية العامة للتعليم بمحافظة الداخلية</p>
    </header>

    <div class="player">
        <video controls playsinline preload="metadata" poster="{{ asset($files['poster']) }}">
            <source src="{{ asset($files['web']) }}" type="video/mp4">
        </video>
    </div>

    <div class="downloads">
        <a class="btn btn--main" href="{{ asset($files['hd']) }}" download="siraj-oman-2026-4k.mp4">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
            تحميل بجودة عالية (4K)
            @if ($s = $size($files['hd'])) <small>{{ $s }}</small> @endif
        </a>
        <a class="btn btn--light" href="{{ asset($files['web']) }}" download="siraj-oman-2026-1080p.mp4">
            نسخة أخف للجوال (1080p)
            @if ($s = $size($files['web'])) <small>{{ $s }}</small> @endif
        </a>
    </div>
    <p class="hint">نسخة 4K هي الفيديو الأصلي بدون أي ضغط، ويُفضّل تحميلها عبر الواي فاي.</p>

    <footer>مستضاف على <a href="{{ url('/') }}">ALYASI</a></footer>
</body>
</html>
