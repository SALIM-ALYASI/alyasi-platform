@extends('layouts.app')

@php
    $isEn = app()->getLocale() === 'en';
    $t = fn (string $ar, string $en) => $isEn ? $en : $ar;
    $arrow = $isEn ? '→' : '←';
@endphp

@section('title', $t('سالم محمد الحجري — مؤسس ALYASI', 'Salem Mohammed Al Hajri — Founder of ALYASI'))
@section('meta_description', $t(
    'سالم محمد الحجري، مؤسس منصة ALYASI ومطوّرها: خبرة تقارب 12 عامًا في البرمجة والتصميم، وتطوير المواقع والأنظمة وتطبيقات iOS والذكاء الاصطناعي.',
    'Salem Mohammed Al Hajri, founder and developer of ALYASI: close to 12 years in programming and design, building websites, systems, iOS apps and AI tools.'
))
@section('canonical', localized_route('about.founder'))
@section('hreflang_ar', route('about.founder'))
@section('hreflang_en', route('about.founder.en'))
@section('og_image', asset('images/about/salem-al-hajri.jpg'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/about.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/works-index.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/founder.css') }}">
@endpush

@section('content')

    @if ($preview)
        <div class="founder-preview" role="note">{{ $t('مسودة للمراجعة — هذه الصفحة غير منشورة بعد.', 'Draft for review — this page is not published yet.') }}</div>
    @endif

    {{-- المقدمة: الاسم والدور وسطر واحد يلخّص --}}
    <section class="container founder-hero">
        <img class="founder-hero__photo" src="{{ versioned_asset('images/about/salem-al-hajri.jpg') }}" alt="{{ $t('سالم محمد الحجري', 'Salem Mohammed Al Hajri') }}" width="220" height="220">
        <div class="founder-hero__text">
            <span class="founder-hero__badge">{{ $t('عن المؤسس', 'About the founder') }}</span>
            <h1>{{ $t('سالم محمد الحجري', 'Salem Mohammed Al Hajri') }}</h1>
            <p class="founder-hero__role">{{ $t('مؤسس منصة ALYASI ومطوّرها', 'Founder and developer of ALYASI') }}</p>
            <p class="founder-hero__lead">{{ $t(
                'مطوّر ومصمم عُماني، أعمل منذ قرابة 12 عامًا في البرمجة والتصميم والتقنية، تعلّمت أغلبها ذاتيًا. أطوّر المواقع والأنظمة وتطبيقات iOS، وأوظّف الذكاء الاصطناعي في بناء أدوات عملية.',
                'An Omani developer and designer with close to 12 years in programming, design and technology, most of it self-taught. I build websites, systems and iOS apps, and use AI to create practical tools.'
            ) }}</p>
        </div>
    </section>

    {{-- أرقام موثّقة: سنوات الخبرة وسنة أول مشروع فعلي (من سالم)، والمشاريع من صفحة الأعمال --}}
    <section class="container about-stats">
        <div class="grid-3">
            <div class="about-stats__item" data-reveal>
                <div class="about-stats__value">12</div>
                <div class="about-stats__label">{{ $t('عامًا تقريبًا في البرمجة والتصميم', 'years, roughly, in programming and design') }}</div>
            </div>
            <div class="about-stats__item" data-reveal>
                <div class="about-stats__value">2019</div>
                <div class="about-stats__label">{{ $t('أول تطبيق وبداية الإنتاج الفعلي', 'first app, and the start of real projects') }}</div>
            </div>
            <div class="about-stats__item" data-reveal>
                <div class="about-stats__value">{{ $projects->count() }}</div>
                <div class="about-stats__label">{{ $t('مشاريع معروضة في صفحة الأعمال', 'projects featured in the portfolio') }}</div>
            </div>
        </div>
    </section>

    {{-- مجالات الخبرة --}}
    <section class="container about-section">
        <div class="section-head" data-reveal>
            <div class="section-head__eyebrow">{{ $t('الخبرة', 'Experience') }}</div>
            <h2 class="section-head__title">{{ $t('ما أعمل عليه', 'What I work on') }}</h2>
        </div>
        <div class="founder-skills">
            @foreach ([
                ['fa-solid fa-code', $t('المواقع والأنظمة', 'Websites and systems'), $t('مواقع تعريفية ومنصات محتوى وأنظمة إدارة ولوحات تحكم، مثل النظام المحاسبي «الحرفيون» ومنصة ALYASI نفسها.', 'Websites, content platforms, management systems and dashboards, such as the «Al Harfiyoon» accounting system and the ALYASI platform itself.')],
                ['fa-brands fa-apple', $t('تطبيقات iOS', 'iOS apps'), $t('خبرة عملية في تطوير تطبيقات أجهزة آبل بنظام iOS، بدأت بتطبيق «البادية» عام 2019.', 'Hands-on experience building apps for Apple devices on iOS, starting with the «Al Badyah» app in 2019.')],
                ['fa-solid fa-wand-magic-sparkles', $t('الذكاء الاصطناعي', 'Artificial intelligence'), $t('أدوات تجمع الأخبار وتحللها وتتحقق من معلوماتها قبل النشر، وتجارب في توليد الصوت، وتوظيف الذكاء الاصطناعي في تطوير المشاريع.', 'Tools that gather, analyse and fact-check news before publishing, voice generation experiments, and AI applied to building projects.')],
                ['fa-solid fa-pen-ruler', $t('التصميم الرقمي', 'Digital design'), $t('هويات بصرية وتصاميم واجهات ومحتوى مرئي، جزء من أغلب المشاريع من بدايتها.', 'Visual identities, interface design and visual content, part of most projects from the start.')],
            ] as [$icon, $title, $body])
                <div class="founder-skill" data-reveal>
                    <span class="founder-skill__icon" aria-hidden="true"><i class="{{ $icon }}"></i></span>
                    <h3>{{ $title }}</h3>
                    <p>{{ $body }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- المسيرة: المحطات كما رواها سالم، بما فيها ما لم يستمر --}}
    <section class="container about-section">
        <div class="section-head" data-reveal>
            <div class="section-head__eyebrow">{{ $t('المسيرة', 'Journey') }}</div>
            <h2 class="section-head__title">{{ $t('محطات في الطريق إلى ALYASI', 'Milestones on the way to ALYASI') }}</h2>
        </div>
        <ol class="founder-timeline">
            @foreach ([
                [$t('2014 تقريبًا', 'Around 2014'), $t('بداية التعلّم', 'Learning begins'), $t(
                    'بدأت تعلّم البرمجة والتصميم ذاتيًا، وكانت السنوات التالية مرحلة تعلّم وتجريب.',
                    'I started teaching myself programming and design; the years that followed were for learning and experimenting.'
                ), null, []],
                ['2019', $t('أول تطبيق: «البادية»', 'First app: «Al Badyah»'), $t(
                    'طوّرت أول تطبيق لي لأجهزة آبل باسم «البادية»، وكان أول مشروع أطبّق فيه ما تعلّمته، وبداية إنتاجي الفعلي. أزلته لاحقًا من متجر App Store لأنني لم أجد له الجدوى أو الدعم الكافي في ذلك الوقت، لكنه بقي محطة مهمة في رحلتي التقنية.',
                    'I built my first app for Apple devices, «Al Badyah» — the first project where I applied what I had learned, and the start of my real output. I later removed it from the App Store because it lacked enough traction and support at the time, but it remained an important step in my journey.'
                ), $t('تجربة لم تستمر · أُزيل من App Store', 'Discontinued · removed from the App Store'), []],
                ['2025', $t('تأسيس ALYASI', 'Founding ALYASI'), $t(
                    'أسست منصة ALYASI منصةً متخصصة في نشر الأخبار.',
                    'I founded ALYASI as a platform dedicated to publishing news.'
                ), null, []],
                [$t('مارس 2026', 'March 2026'), $t('صوت رقمي بصوتي', 'A digital voice of my own'), $t(
                    'بعد شهور من التجارب طوّرت صوتًا رقميًا يشبه أسلوبي في الكلام، وشاركت التجربة في مقطع «الخطوة الأولى في رحلة صناعة صوت رقمي». لم يكن الهدف التجربة التقنية وحدها، بل تحويل التقنية إلى محتوى ومعرفة نافعة بأسلوب بسيط. أستخدم هذا الصوت اليوم في محتوى ALYASI، ومنه مقدمات مقابلات سايبر إكس عُمان 2026 وترجمة أسئلتها.',
                    'After months of experiments I built a digital voice that sounds like the way I speak, and shared the journey in a short video. The goal was never the technology alone, but turning it into useful, simple content and knowledge. I use this voice in ALYASI content today, including the introductions to the CyberX Oman 2026 interviews and the translated questions.'
                ), null, [
                    ['https://youtu.be/LJDnzI0uDsw', $t('شاهد المقطع على يوتيوب', 'Watch the video on YouTube'), true],
                    [localized_route('works.show', ['work' => 'alyasi-ai-voice']), $t('مشروع ALYASI AI Voice', 'The ALYASI AI Voice project'), false],
                ]],
                [$t('يوليو 2026', 'July 2026'), $t('ALYASI بشكلها الحالي', 'ALYASI as it is today'), $t(
                    'طوّرت ALYASI إلى شكلها الحالي، لتشمل الأخبار والمقالات والتغطيات التقنية والخدمات الرقمية.',
                    'I developed ALYASI into its current form, covering news, articles, technology coverage and digital services.'
                ), null, []],
                [$t('6 أكتوبر 2026', '6 October 2026'), $t('عمل ميداني: سايبر إكس عُمان 2026', 'On the ground: CyberX Oman 2026'), $t(
                    'غطّيت مؤتمر سايبر إكس عُمان 2026 ميدانيًا من موقع الحدث، ومنه انطلقت سلسلة مقابلات مع متحدثين في المؤتمر نُشرت على ALYASI بالعربية والإنجليزية.',
                    'I covered CyberX Oman 2026 on site, which led to a series of interviews with conference speakers published on ALYASI in Arabic and English.'
                ), null, [
                    [\App\Models\EventEdition::localizedPath('/events/cyberx-oman-2026/coverage'), $t('التغطية', 'The coverage'), false],
                    [\App\Models\EventEdition::localizedPath('/events/cyberx-oman-2026/interviews'), $t('المقابلات', 'The interviews'), false],
                ]],
            ] as [$when, $title, $body, $status, $links])
                <li class="founder-timeline__item" data-reveal>
                    <span class="founder-timeline__when">{{ $when }}</span>
                    <h3>{{ $title }}</h3>
                    <p>{{ $body }}</p>
                    @if ($status)
                        <span class="founder-timeline__status">{{ $status }}</span>
                    @endif
                    @if ($links)
                        <div class="founder-timeline__links">
                            @foreach ($links as [$href, $label, $external])
                                <a href="{{ $href }}" @if ($external) target="_blank" rel="noopener" @endif>{{ $label }} {{ $arrow }}</a>
                            @endforeach
                        </div>
                    @endif
                </li>
            @endforeach
        </ol>
        <p class="founder-timeline__note" data-reveal>{{ $t(
            'كل مشروع، استمر أو توقف، كان تجربة أضافت إلى خبرتي وأوصلتني إلى ما أصبحت عليه ALYASI اليوم.',
            'Every project, whether it lasted or not, added to my experience and led to what ALYASI is today.'
        ) }}</p>
    </section>

    {{-- منصة ALYASI: ما بُني من الصفر --}}
    <section class="about-mission">
        <div class="container">
            <div class="section-head" data-reveal>
                <div class="section-head__eyebrow">ALYASI</div>
                <h2 class="section-head__title">{{ $t('منصة بنيتها من الصفر', 'A platform built from scratch') }}</h2>
                <p class="section-head__desc">{{ $t(
                    'أسست ALYASI عام 2025 منصةً لنشر الأخبار، ثم طوّرتها في يوليو 2026 إلى شكلها الحالي، وبنيتها بنفسي من الصفر: قسم للأخبار التقنية يعمل بأدوات ذكاء اصطناعي لجمع الأخبار وتحليلها والتحقق منها، ومقالات، و«تاريخ التقنية»، وتغطيات للمؤتمرات التقنية في عُمان، منها تغطية ميدانية لمؤتمر سايبر إكس عُمان 2026 ومقابلات مسجّلة مع متحدثيه، إلى جانب خدمات تطوير المواقع والأنظمة والتصميم.',
                    'I founded ALYASI in 2025 as a news platform, developed it into its current form in July 2026, and built it myself from scratch: a tech news section powered by AI tools that gather, analyse and fact-check stories, articles, «Tech History», and coverage of technology conferences in Oman — including on-site coverage of CyberX Oman 2026 and recorded interviews with its speakers — alongside web, systems and design services.'
                ) }}</p>
            </div>
        </div>
    </section>

    {{-- أبرز المشاريع (من صفحة الأعمال) --}}
    @if ($projects->isNotEmpty())
        <section class="container about-section">
            <div class="section-head" data-reveal>
                <div class="section-head__eyebrow">{{ $t('المشاريع', 'Projects') }}</div>
                <h2 class="section-head__title">{{ $t('من أعمالي', 'Selected work') }}</h2>
            </div>
            <div class="founder-projects">
                @foreach ($projects as $work)
                    <a class="founder-project" href="{{ localized_route('works.show', ['work' => $work]) }}" data-reveal>
                        <strong>{{ $work->localized('title') }}</strong>
                        <span>{{ $work->type_label }}@if ($work->status_label) · {{ $work->status_label }}@endif</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- الرؤية --}}
    <section class="container about-section">
        <div class="section-head" data-reveal>
            <div class="section-head__eyebrow">{{ $t('الرؤية', 'Vision') }}</div>
            <h2 class="section-head__title">{{ $t('تقنية عملية بلغة الناس', 'Practical technology, in people’s language') }}</h2>
            <p class="section-head__desc">{{ $t(
                'أؤمن بأن التقنية تكون مفيدة حين تحل مشكلة حقيقية لصاحبها. لذلك أركّز على حلول عملية للأفراد والمشاريع في عُمان والمنطقة، وعلى محتوى تقني عربي واضح ودقيق.',
                'I believe technology is useful when it solves a real problem for the person using it. That is why I focus on practical solutions for people and businesses in Oman and the region, and on clear, accurate Arabic tech content.'
            ) }}</p>
        </div>
    </section>

    <section class="container">
        <div class="cta-band" data-reveal>
            <h2 class="cta-band__title">{{ $t('عندك مشروع؟', 'Have a project in mind?') }}</h2>
            <p class="cta-band__desc">{{ $t('تواصل معي لنناقش فكرتك.', 'Get in touch to talk it through.') }}</p>
            <div class="cta-band__actions">
                <a href="{{ localized_route('contact') }}" class="btn btn--light">{{ $t('تواصل معنا', 'Contact us') }}</a>
                <a href="https://wa.me/96898881054" class="btn btn--ghost-light" target="_blank" rel="noopener">{{ $t('واتساب', 'WhatsApp') }}</a>
            </div>
        </div>
    </section>

@endsection
