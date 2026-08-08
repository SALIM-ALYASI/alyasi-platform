@php
    $isArabic = app()->getLocale() === 'ar';

    $footerLabels = [
        'about' => $isArabic ? 'من نحن' : 'About',
        'brand_desc' => $isArabic
            ? 'منصة رقمية متكاملة تجمع الخدمات، والأخبار التقنية، والمجتمع في تجربة واحدة.'
            : 'An integrated digital platform bringing services, tech news, and community together.',
        'explore' => $isArabic ? 'استكشف' : 'Explore',
        'company' => $isArabic ? 'الشركة' : 'Company',
        'legal' => $isArabic ? 'قانوني' : 'Legal',
        'home' => $isArabic ? 'الرئيسية' : 'Home',
        'services' => $isArabic ? 'الخدمات' : 'Services',
        'works' => $isArabic ? 'أعمالي' : 'Works',
        'news' => $isArabic ? 'الأخبار' : 'News',
        'community' => $isArabic ? 'المجتمع' : 'Community',
        'about_us' => $isArabic ? 'من نحن' : 'About Us',
        'contact' => $isArabic ? 'تواصل معنا' : 'Contact Us',
        'social_links' => $isArabic ? 'روابط التواصل' : 'Social Links',
        'privacy' => $isArabic ? 'سياسة الخصوصية' : 'Privacy Policy',
        'terms' => $isArabic ? 'الشروط والأحكام' : 'Terms & Conditions',
        'rights' => $isArabic
            ? '© ' . now()->year . ' ALYASI. جميع الحقوق محفوظة.'
            : '© ' . now()->year . ' ALYASI. All rights reserved.',
    ];
@endphp

<footer class="site-footer">
    <div class="site-footer__grid">

        <div>
            <a href="{{ route('home') }}" class="brand">
                <span class="brand__mark">A</span>
                <span class="brand__name">ALYASI</span>
            </a>
            <p class="site-footer__about">{{ $footerLabels['brand_desc'] }}</p>
        </div>

        <div>
            <div class="site-footer__heading">{{ $footerLabels['explore'] }}</div>
            <ul class="site-footer__links">
                <li><a href="{{ route('home') }}">{{ $footerLabels['home'] }}</a></li>
                <li><a href="{{ route('services.index') }}">{{ $footerLabels['services'] }}</a></li>
                <li><a href="{{ route('works.index') }}">{{ $footerLabels['works'] }}</a></li>
                <li><a href="{{ route('news.index') }}">{{ $footerLabels['news'] }}</a></li>
                <li><a href="{{ route('community.index') }}">{{ $footerLabels['community'] }}</a></li>
            </ul>
        </div>

        <div>
            <div class="site-footer__heading">{{ $footerLabels['company'] }}</div>
            <ul class="site-footer__links">
                <li><a href="{{ route('about') }}">{{ $footerLabels['about_us'] }}</a></li>
                <li><a href="{{ route('contact') }}">{{ $footerLabels['contact'] }}</a></li>
                <li><a href="{{ route('social-links.index') }}">{{ $footerLabels['social_links'] }}</a></li>
            </ul>
        </div>

        <div>
            <div class="site-footer__heading">{{ $footerLabels['legal'] }}</div>
            <ul class="site-footer__links">
                <li><a href="{{ route('privacy') }}">{{ $footerLabels['privacy'] }}</a></li>
                <li><a href="{{ route('terms') }}">{{ $footerLabels['terms'] }}</a></li>
            </ul>
        </div>

    </div>

    <div class="site-footer__bottom">
        <span>{{ $footerLabels['rights'] }}</span>
    </div>
</footer>
