<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منزل محمد بن سالم الحجري (راعي صويط)</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#180B0F">
    <link rel="icon" type="image/png" href="{{ asset('images/ra3i-swait/home.webp') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            position: relative;
            min-height: 100svh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 28px;
            padding: 48px 20px;
            background: #180B0F;
            font-family: 'IBM Plex Sans Arabic', sans-serif;
            isolation: isolate;
        }

        /* ── صورة الصحراء كخلفية كاملة للصفحة ── */
        .desert-bg {
            position: fixed;
            inset: 0;
            z-index: -5;
            background-image: url('{{ asset('images/ra3i-swait/desert-bg.jpg') }}');
            background-size: cover;
            background-position: center 60%;
            filter: saturate(0.85) brightness(0.7);
            transform: scale(1.06);
            animation: drift-bg 24s ease-in-out infinite alternate;
        }

        @keyframes drift-bg {
            from { transform: scale(1.06) translate(0, 0); }
            to { transform: scale(1.1) translate(-1%, -1%); }
        }

        /* ── ضباب خفيف فوق صورة الصحراء لتهدئتها ودمجها بالهوية الداكنة ── */
        .desert-fog {
            position: fixed;
            inset: 0;
            z-index: -4;
            pointer-events: none;
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            background: linear-gradient(
                180deg,
                rgba(24, 11, 15, 0.72) 0%,
                rgba(24, 11, 15, 0.32) 30%,
                rgba(24, 11, 15, 0.4) 65%,
                rgba(24, 11, 15, 0.85) 100%
            );
        }

        /* ── الغلاف الجوي: توهج ذهبي هادئ يتنفس خلف البطاقة ── */
        .atmosphere {
            position: fixed;
            inset: 0;
            z-index: -3;
            pointer-events: none;
            background:
                radial-gradient(closest-side, rgba(208, 170, 106, 0.16), transparent 70%) 50% 38% / 60% 50% no-repeat,
                radial-gradient(closest-side, rgba(208, 170, 106, 0.08), transparent 70%) 50% 60% / 80% 60% no-repeat;
            animation: breathe 7s ease-in-out infinite;
        }

        @keyframes breathe {
            0%, 100% { opacity: 0.7; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.06); }
        }

        /* ── حبيبات دقيقة تكسر تسطّح الخلفية، بنفس روح الموقع الرئيسي ── */
        .grain {
            position: fixed;
            inset: 0;
            z-index: -2;
            pointer-events: none;
            opacity: 0.05;
        }

        /* ── جزيئات ذهبية عائمة، كأنها شرر يتصاعد ببطء في الخلفية ── */
        .embers {
            position: fixed;
            inset: 0;
            z-index: -1;
            overflow: hidden;
            pointer-events: none;
        }

        .ember {
            position: absolute;
            bottom: -5vh;
            left: var(--x, 50%);
            width: var(--size, 4px);
            height: var(--size, 4px);
            border-radius: 50%;
            background: radial-gradient(circle, #F1D59A 0%, #D0AA6A 55%, transparent 75%);
            box-shadow: 0 0 6px 1px rgba(208, 170, 106, 0.55);
            opacity: 0;
            filter: blur(var(--blur, 0px));
            animation: rise-ember var(--duration, 14s) linear var(--delay, 0s) infinite;
        }

        @keyframes rise-ember {
            0% {
                transform: translate(0, 0);
                opacity: 0;
            }

            8% {
                opacity: var(--opacity, 0.8);
            }

            85% {
                opacity: var(--opacity, 0.8);
            }

            100% {
                transform: translate(var(--drift, 40px), -115vh);
                opacity: 0;
            }
        }

        .stage {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 28px;
            opacity: 0;
            transform: translateY(18px);
            animation: rise 1s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards;
        }

        @keyframes rise {
            to { opacity: 1; transform: translateY(0); }
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border: 1px solid rgba(208, 170, 106, 0.28);
            border-radius: 100px;
            background: rgba(250, 246, 240, 0.03);
            color: #D0AA6A;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .eyebrow::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #D0AA6A;
            box-shadow: 0 0 8px 2px rgba(208, 170, 106, 0.6);
            animation: pulse-dot 2.2s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 0.5; transform: scale(0.85); }
            50% { opacity: 1; transform: scale(1.15); }
        }

        .frame {
            position: relative;
            padding: 10px;
            border-radius: 26px;
            background: linear-gradient(155deg, rgba(208, 170, 106, 0.5), rgba(208, 170, 106, 0.08) 40%, transparent 70%);
        }

        .photo-link {
            position: relative;
            display: block;
            max-width: 420px;
            width: 100%;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.55);
            transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.6s ease;
        }

        .photo-link:hover,
        .photo-link:focus-visible {
            transform: scale(1.018);
            box-shadow: 0 28px 90px rgba(208, 170, 106, 0.22), 0 24px 70px rgba(0, 0, 0, 0.55);
        }

        .photo-link img {
            display: block;
            width: 100%;
            height: auto;
        }

        /* ── تلميح "اضغط للموقع" يظهر بلطف عند التمرير ── */
        .hint {
            position: absolute;
            inset-inline: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 16px 10px 14px;
            background: linear-gradient(to top, rgba(24, 11, 15, 0.92), transparent);
            color: #FAF6F0;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.04em;
            opacity: 0;
            transform: translateY(6px);
            transition: opacity 0.4s ease, transform 0.4s ease;
        }

        .photo-link:hover .hint,
        .photo-link:focus-visible .hint {
            opacity: 1;
            transform: translateY(0);
        }

        .hint svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        .caption {
            text-align: center;
            color: #FAF6F0;
            font-size: 1.15rem;
            font-weight: 700;
            line-height: 1.6;
        }

        .caption .sub {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 8px;
            color: #A98B74;
            font-size: 0.82rem;
            font-weight: 500;
        }

        .caption .sub .line {
            width: 26px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(208, 170, 106, 0.5));
        }

        .caption .sub .line.reverse {
            background: linear-gradient(90deg, rgba(208, 170, 106, 0.5), transparent);
        }

        @media (prefers-reduced-motion: reduce) {
            .atmosphere, .eyebrow::before, .stage, .photo-link, .ember, .desert-bg {
                animation: none !important;
                transition: none !important;
            }

            .stage {
                opacity: 1;
                transform: none;
            }

            .embers {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="desert-bg" aria-hidden="true"></div>
    <div class="desert-fog" aria-hidden="true"></div>

    <div class="atmosphere" aria-hidden="true"></div>

    <svg class="grain" aria-hidden="true">
        <filter id="grainNoise">
            <feTurbulence type="fractalNoise" baseFrequency="0.7" numOctaves="4" stitchTiles="stitch" />
            <feColorMatrix type="saturate" values="0" />
        </filter>
        <rect width="100%" height="100%" filter="url(#grainNoise)" />
    </svg>

    <div class="embers" id="embers" aria-hidden="true"></div>

    <main class="stage">

        <span class="eyebrow">منزل خاص · بدية</span>

        <div class="frame">
            <a
                class="photo-link"
                href="https://maps.app.goo.gl/CRQJSd897y7bhXoj9"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="فتح موقع منزل محمد بن سالم الحجري على خرائط جوجل"
            >
                <img
                    src="{{ asset('images/ra3i-swait/home.webp') }}"
                    alt="منزل محمد بن سالم الحجري (راعي صويط)"
                >

                <span class="hint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 21s-7-6.5-7-11.5a7 7 0 0 1 14 0C19 14.5 12 21 12 21Z" />
                        <circle cx="12" cy="9.5" r="2.5" />
                    </svg>
                    اضغط لعرض الموقع على الخريطة
                </span>
            </a>
        </div>

        <p class="caption">
            منزل محمد بن سالم الحجري
            <span class="sub">
                <span class="line"></span>
                (راعي صويط)
                <span class="line reverse"></span>
            </span>
        </p>

    </main>

    <script>
        (function () {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            var container = document.getElementById('embers');
            var count = window.innerWidth < 640 ? 16 : 28;

            for (var i = 0; i < count; i++) {
                var ember = document.createElement('span');
                ember.className = 'ember';

                var size = (Math.random() * 3 + 2).toFixed(1);
                var duration = (Math.random() * 10 + 10).toFixed(1);
                var delay = (Math.random() * -duration).toFixed(1);
                var drift = (Math.random() * 120 - 60).toFixed(0);
                var opacity = (Math.random() * 0.5 + 0.35).toFixed(2);
                var blur = Math.random() < 0.4 ? '1px' : '0px';

                ember.style.setProperty('--x', Math.random() * 100 + '%');
                ember.style.setProperty('--size', size + 'px');
                ember.style.setProperty('--duration', duration + 's');
                ember.style.setProperty('--delay', delay + 's');
                ember.style.setProperty('--drift', drift + 'px');
                ember.style.setProperty('--opacity', opacity);
                ember.style.setProperty('--blur', blur);

                container.appendChild(ember);
            }
        })();
    </script>

</body>

</html>
