@extends('layouts.app')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $isArabic = app()->getLocale() === 'ar';

    /*
    |--------------------------------------------------------------------------
    | بيانات SEO
    |--------------------------------------------------------------------------
    */

    $pageTitle = $article->seo_title ?: $article->title;

    $pageDescription = $article->seo_description
        ?: $article->excerpt
        ?: (
            $isArabic
                ? 'اقرأ أحدث الأخبار التقنية على منصة ALYASI.'
                : 'Read the latest technology news on ALYASI.'
        );

    /*
    |--------------------------------------------------------------------------
    | صورة الخبر
    |--------------------------------------------------------------------------
    */

    $fallbackImageUrl = asset('images/logo/logo-dark.png');

    $resolveImageUrl = function (?string $image) use (
        $fallbackImageUrl
    ): string {
        if (blank($image)) {
            return $fallbackImageUrl;
        }

        $image = trim($image);

        if (Str::startsWith(
            $image,
            ['http://', 'https://', 'data:']
        )) {
            return $image;
        }

        $normalizedImage = ltrim($image, '/');

        /*
         * الملفات الموجودة مباشرة داخل public.
         *
         * أمثلة:
         * images/logo/logo-dark.png
         * assets/news/images/article.jpg
         * uploads/news/article.jpg
         */
        if (Str::startsWith(
            $normalizedImage,
            ['images/', 'assets/', 'uploads/']
        )) {
            return asset($normalizedImage);
        }

        /*
         * المسارات المحفوظة بصيغة storage/...
         */
        if (Str::startsWith(
            $normalizedImage,
            'storage/'
        )) {
            return asset($normalizedImage);
        }

        /*
         * بقية المسارات تعتبر محفوظة في:
         * storage/app/public
         */
        return Storage::url($normalizedImage);
    };

    $articleImageUrl = $resolveImageUrl($article->image);

    /*
    |--------------------------------------------------------------------------
    | التصنيف
    |--------------------------------------------------------------------------
    */

    $categoryName = $article->category?->name
        ?? $article->category?->title
        ?? null;

    /*
    |--------------------------------------------------------------------------
    | تاريخ النشر
    |--------------------------------------------------------------------------
    */

    $publishedDate = $article->published_at
        ?->locale($isArabic ? 'ar' : 'en')
        ->translatedFormat('d F Y');

    $publishedTime = $article->published_at
        ?->format('h:i A');

    /*
    |--------------------------------------------------------------------------
    | مدة القراءة التقريبية
    |--------------------------------------------------------------------------
    */

    $plainContent = trim(
        preg_replace(
            '/\s+/u',
            ' ',
            strip_tags($article->content ?? '')
        )
    );

    $wordCount = str_word_count($plainContent);

    /*
     * احتساب أفضل للنص العربي عند عدم تعرف str_word_count عليه.
     */
    if ($wordCount === 0 && filled($plainContent)) {
        $wordCount = count(
            preg_split(
                '/\s+/u',
                $plainContent,
                -1,
                PREG_SPLIT_NO_EMPTY
            )
        );
    }

    $readingTime = max(
        1,
        (int) ceil($wordCount / 200)
    );

    /*
    |--------------------------------------------------------------------------
    | رابط الخبر
    |--------------------------------------------------------------------------
    */

    $articleUrl = request()->fullUrl();

    /*
    |--------------------------------------------------------------------------
    | رابط الخبر ذي الصلة
    |--------------------------------------------------------------------------
    |
    | يتطلب تحميل علاقة permalinks داخل NewsController.
    |
    */

    $relatedArticleUrl = function ($relatedArticle): string {
        if (! $relatedArticle->relationLoaded('permalinks')) {
            return '#';
        }

        $permalink = $relatedArticle->permalinks
            ->firstWhere('locale', app()->getLocale())
            ?? $relatedArticle->permalinks->first();

        return $permalink
            ? route('news.show', $permalink->slug)
            : '#';
    };
@endphp

@section('title', $pageTitle)

@section('description', $pageDescription)

@push('styles')
    {{-- التصميم العام المشترك للأخبار --}}
    <link
        rel="stylesheet"
        href="{{ versioned_asset('assets/news/css/news.css') }}"
    >

    {{-- التصميم الخاص بصفحة عرض الخبر --}}
    <link
        rel="stylesheet"
        href="{{ versioned_asset('assets/news/css/news-show.css') }}"
    >
@endpush

@section('content')

<main class="news-article-page">

    {{-- =====================================================
         زخارف الخلفية
    ====================================================== --}}

    <div
        class="news-article-background"
        aria-hidden="true"
    >
        <span class="news-article-orb news-article-orb-one"></span>
        <span class="news-article-orb news-article-orb-two"></span>
        <span class="news-article-grid"></span>
    </div>

    {{-- =====================================================
         رأس الخبر
    ====================================================== --}}

    <section class="news-article-hero">

        <div class="news-article-container">

            {{-- مسار التنقل --}}

            <nav
                class="news-article-breadcrumb"
                aria-label="{{ $isArabic ? 'مسار التنقل' : 'Breadcrumb' }}"
            >
                <a href="{{ route('home') }}">
                    <i class="fa-solid fa-house"></i>

                    <span>
                        {{ $isArabic ? 'الرئيسية' : 'Home' }}
                    </span>
                </a>

                <i
                    class="fa-solid fa-chevron-left news-article-breadcrumb-arrow"
                    aria-hidden="true"
                ></i>

                <a href="{{ route('news.index') }}">
                    {{ $isArabic ? 'الأخبار' : 'News' }}
                </a>

                @if (filled($categoryName))
                    <i
                        class="fa-solid fa-chevron-left news-article-breadcrumb-arrow"
                        aria-hidden="true"
                    ></i>

                    <span>
                        {{ $categoryName }}
                    </span>
                @endif
            </nav>

            <div class="news-article-hero-content">

                {{-- التصنيف والخبر العاجل --}}

                <div class="news-article-labels">

                    @if ($article->is_breaking)
                        <span class="news-article-label news-article-breaking-label">
                            <i class="fa-solid fa-bolt"></i>

                            {{ $isArabic ? 'خبر عاجل' : 'Breaking News' }}
                        </span>
                    @endif

                    @if (filled($categoryName))
                        <span class="news-article-label news-article-category-label">
                            {{ $categoryName }}
                        </span>
                    @endif

                    @if ($article->is_featured)
                        <span class="news-article-label news-article-featured-label">
                            <i class="fa-solid fa-star"></i>

                            {{ $isArabic ? 'خبر مميز' : 'Featured' }}
                        </span>
                    @endif

                </div>

                {{-- عنوان الخبر --}}

                <h1 class="news-article-title">
                    {{ $article->title }}
                </h1>

                {{-- الملخص --}}

                @if (filled($article->excerpt))
                    <p class="news-article-excerpt">
                        {{ $article->excerpt }}
                    </p>
                @endif

                {{-- بيانات الخبر --}}

                <div class="news-article-meta-wrapper">

                    <div class="news-article-meta">

                        @if ($article->published_at)
                            <span class="news-article-meta-item">
                                <i class="fa-regular fa-calendar"></i>

                                {{ $publishedDate }}
                            </span>

                            <span class="news-article-meta-item">
                                <i class="fa-regular fa-clock"></i>

                                {{ $publishedTime }}
                            </span>
                        @endif

                        @if (filled($article->formatted_author_name))
                            <span class="news-article-meta-item">
                                <i class="fa-regular fa-user"></i>

                                {{ $article->formatted_author_name }}
                            </span>
                        @endif

                        <span class="news-article-meta-item">
                            <i class="fa-regular fa-eye"></i>

                            {{ number_format($article->views_count) }}

                            {{ $isArabic ? 'مشاهدة' : 'views' }}
                        </span>

                        <span class="news-article-meta-item">
                            <i class="fa-regular fa-hourglass-half"></i>

                            {{ $readingTime }}

                            {{ $isArabic ? 'دقائق قراءة' : 'min read' }}
                        </span>

                    </div>

                    {{-- أزرار المشاركة --}}

                    <div class="news-article-share">

                        <span class="news-article-share-title">
                            {{ $isArabic ? 'مشاركة' : 'Share' }}
                        </span>

                        <a
                            href="https://wa.me/?text={{ urlencode(
                                $article->title . ' ' . $articleUrl
                            ) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="WhatsApp"
                            class="news-article-share-button"
                        >
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>

                        <a
                            href="https://twitter.com/intent/tweet?text={{ urlencode(
                                $article->title
                            ) }}&url={{ urlencode($articleUrl) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="X"
                            class="news-article-share-button"
                        >
                            <i class="fa-brands fa-x-twitter"></i>
                        </a>

                        <a
                            href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(
                                $articleUrl
                            ) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Facebook"
                            class="news-article-share-button"
                        >
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </section>

    {{-- =====================================================
         صورة الخبر
    ====================================================== --}}

    <section class="news-article-media-section">

        <div class="news-article-container">

            <figure class="news-article-main-image">

                <img
                    src="{{ $articleImageUrl }}"
                    alt="{{ $article->image_alt ?: $article->title }}"
                    width="1400"
                    height="800"
                    fetchpriority="high"
                    decoding="async"
                    onerror="this.onerror=null; this.src='{{ $fallbackImageUrl }}';"
                >

                <span
                    class="news-article-image-overlay"
                    aria-hidden="true"
                ></span>

                <div class="news-article-image-decoration">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

            </figure>

        </div>

    </section>

    {{-- =====================================================
         محتوى الخبر
    ====================================================== --}}

    <section class="news-article-content-section">

        <div class="news-article-container">

            <div class="news-article-layout">

                {{-- المحتوى الرئيسي --}}

                <article class="news-article-content-card">

                    @if (filled($article->content))

                        <div class="news-article-content">
                            {!! $article->content !!}
                        </div>

                    @else

                        <div class="news-article-empty-content">

                            <i class="fa-regular fa-file-lines"></i>

                            <p>
                                {{ $isArabic
                                    ? 'لا يوجد محتوى متاح لهذا الخبر حاليًا.'
                                    : 'No content is currently available for this article.'
                                }}
                            </p>

                        </div>

                    @endif

                    {{-- مصدر الخبر --}}

                    @if (
                        filled($article->source_name)
                        || filled($article->source_url)
                    )
                        <div class="news-article-source">

                            <div class="news-article-source-icon">
                                <i class="fa-solid fa-link"></i>
                            </div>

                            <div class="news-article-source-content">

                                <span>
                                    {{ $isArabic
                                        ? 'مصدر الخبر'
                                        : 'Article source'
                                    }}
                                </span>

                                @if (filled($article->source_url))
                                    <a
                                        href="{{ $article->source_url }}"
                                        target="_blank"
                                        rel="nofollow noopener noreferrer"
                                    >
                                        {{ $article->source_name
                                            ?: $article->source_url
                                        }}

                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                @else
                                    <strong>
                                        {{ $article->source_name }}
                                    </strong>
                                @endif

                            </div>

                        </div>
                    @endif

                    {{-- مشاركة أسفل الخبر --}}

                    <footer class="news-article-footer">

                        <div class="news-article-footer-text">

                            <span>
                                {{ $isArabic
                                    ? 'هل أعجبك الخبر؟'
                                    : 'Did you like this article?'
                                }}
                            </span>

                            <strong>
                                {{ $isArabic
                                    ? 'شاركه مع الآخرين'
                                    : 'Share it with others'
                                }}
                            </strong>

                        </div>

                        <div class="news-article-footer-actions">

                            <a
                                href="https://wa.me/?text={{ urlencode(
                                    $article->title . ' ' . $articleUrl
                                ) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="news-article-whatsapp-button"
                            >
                                <i class="fa-brands fa-whatsapp"></i>

                                <span>WhatsApp</span>
                            </a>

                            <button
                                type="button"
                                class="news-article-copy-button"
                                data-copy-url="{{ $articleUrl }}"
                                data-copy-label="{{ $isArabic
                                    ? 'نسخ الرابط'
                                    : 'Copy link'
                                }}"
                                data-copied-label="{{ $isArabic
                                    ? 'تم نسخ الرابط'
                                    : 'Link copied'
                                }}"
                            >
                                <i class="fa-regular fa-copy"></i>

                                <span>
                                    {{ $isArabic
                                        ? 'نسخ الرابط'
                                        : 'Copy link'
                                    }}
                                </span>
                            </button>

                        </div>

                    </footer>

                </article>

                {{-- الشريط الجانبي --}}

                <aside class="news-article-sidebar">

                    {{-- معلومات الكاتب --}}

                    @if (filled($article->formatted_author_name))
                        <div class="news-article-sidebar-card">

                            <span class="news-article-sidebar-eyebrow">
                                {{ $isArabic ? 'كاتب الخبر' : 'Author' }}
                            </span>

                            <div class="news-article-author">

                                <div class="news-article-author-icon">
                                    <i class="fa-regular fa-user"></i>
                                </div>

                                <div>
                                    <strong>
                                        {{ $article->formatted_author_name }}
                                    </strong>

                                    @if (filled($article->source_name))
                                        <span>
                                            {{ $isArabic
                                                ? 'كاتب أصلي بموقع ' . $article->source_name
                                                : 'Original author at ' . $article->source_name
                                            }}
                                        </span>
                                    @endif
                                </div>

                            </div>

                        </div>
                    @endif

                    {{-- بطاقة المنصة --}}

                    <div class="news-article-sidebar-card news-article-about-card">

                        <div class="news-article-about-logo">
                            <img
                                src="{{ asset('images/logo/logo-light.png') }}"
                                alt="ALYASI"
                                width="180"
                                height="70"
                                loading="lazy"
                            >
                        </div>

                        <span class="news-article-sidebar-eyebrow">
                            {{ $isArabic
                                ? 'منصة رقمية متقدمة'
                                : 'Advanced Digital Platform'
                            }}
                        </span>

                        <h2>
                            {{ $isArabic
                                ? 'منصة ALYASI'
                                : 'ALYASI Platform'
                            }}
                        </h2>

                        <p>
                            {{ $isArabic
                                ? 'منصة رقمية تقدم الأخبار والمحتوى التقني المتخصص في البرمجة والذكاء الاصطناعي والأمن السيبراني.'
                                : 'A digital platform providing specialized news and content about programming, artificial intelligence and cybersecurity.'
                            }}
                        </p>

                        <a
                            href="{{ route('news.index') }}"
                            class="news-article-sidebar-link"
                        >
                            <span>
                                {{ $isArabic
                                    ? 'تصفح جميع الأخبار'
                                    : 'Browse all news'
                                }}
                            </span>

                            <i class="fa-solid fa-arrow-left"></i>
                        </a>

                    </div>

                    {{-- متابعة المنصة --}}

                    <div class="news-article-sidebar-card">

                        <span class="news-article-sidebar-eyebrow">
                            {{ $isArabic ? 'تابعنا' : 'Follow us' }}
                        </span>

                        <h2>
                            {{ $isArabic
                                ? 'ابقَ على اطلاع'
                                : 'Stay updated'
                            }}
                        </h2>

                        <p>
                            {{ $isArabic
                                ? 'تابع آخر الأخبار والتطورات التقنية من خلال منصاتنا.'
                                : 'Follow the latest technology news and developments through our platforms.'
                            }}
                        </p>

                        <div class="news-article-social-links">

                            <a
                                href="#"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Instagram"
                            >
                                <i class="fa-brands fa-instagram"></i>
                            </a>

                            <a
                                href="#"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="X"
                            >
                                <i class="fa-brands fa-x-twitter"></i>
                            </a>

                            <a
                                href="#"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="YouTube"
                            >
                                <i class="fa-brands fa-youtube"></i>
                            </a>

                        </div>

                    </div>

                </aside>

            </div>

        </div>

    </section>

    {{-- =====================================================
         الأخبار ذات الصلة
    ====================================================== --}}

    @if (
        isset($relatedArticles)
        && $relatedArticles->isNotEmpty()
    )
        <section class="news-related-section">

            <div class="news-article-container">

                <div class="news-related-heading">

                    <div>
                        <span class="news-related-eyebrow">
                            {{ $isArabic
                                ? 'قد يهمك أيضًا'
                                : 'You may also like'
                            }}
                        </span>

                        <h2>
                            {{ $isArabic
                                ? 'أخبار ذات صلة'
                                : 'Related news'
                            }}
                        </h2>
                    </div>

                    <a
                        href="{{ route('news.index') }}"
                        class="news-related-all-link"
                    >
                        <span>
                            {{ $isArabic
                                ? 'عرض جميع الأخبار'
                                : 'View all news'
                            }}
                        </span>

                        <i class="fa-solid fa-arrow-left"></i>
                    </a>

                </div>

                <div class="news-related-grid">

                    @foreach ($relatedArticles as $relatedArticle)

                        @php
                            $relatedCategoryName =
                                $relatedArticle->category?->name
                                ?? $relatedArticle->category?->title
                                ?? null;

                            $relatedImageUrl = $resolveImageUrl(
                                $relatedArticle->image
                            );

                            $relatedUrl = $relatedArticleUrl(
                                $relatedArticle
                            );
                        @endphp

                        <article class="news-related-card">

                            <a
                                href="{{ $relatedUrl }}"
                                class="news-related-card-link"
                                @if ($relatedUrl === '#')
                                    aria-disabled="true"
                                @endif
                            >

                                <div class="news-related-image">

                                    <img
                                        src="{{ $relatedImageUrl }}"
                                        alt="{{ $relatedArticle->image_alt ?: $relatedArticle->title }}"
                                        width="600"
                                        height="380"
                                        loading="lazy"
                                        decoding="async"
                                        onerror="this.onerror=null; this.src='{{ $fallbackImageUrl }}';"
                                    >

                                    <span
                                        class="news-related-image-overlay"
                                        aria-hidden="true"
                                    ></span>

                                    @if ($relatedArticle->is_breaking)
                                        <span class="news-related-breaking">
                                            <i class="fa-solid fa-bolt"></i>

                                            {{ $isArabic
                                                ? 'عاجل'
                                                : 'Breaking'
                                            }}
                                        </span>
                                    @endif

                                </div>

                                <div class="news-related-content">

                                    @if (filled($relatedCategoryName))
                                        <span class="news-related-category">
                                            {{ $relatedCategoryName }}
                                        </span>
                                    @endif

                                    <h3>
                                        {{ $relatedArticle->title }}
                                    </h3>

                                    @if (filled($relatedArticle->excerpt))
                                        <p>
                                            {{ Str::limit(
                                                $relatedArticle->excerpt,
                                                120
                                            ) }}
                                        </p>
                                    @endif

                                    <div class="news-related-meta">

                                        <span>
                                            <i class="fa-regular fa-calendar"></i>

                                            {{ $relatedArticle->published_at
                                                ?->locale(
                                                    $isArabic ? 'ar' : 'en'
                                                )
                                                ->translatedFormat('d F Y')
                                            }}
                                        </span>

                                        <span class="news-related-arrow">
                                            <i class="fa-solid fa-arrow-left"></i>
                                        </span>

                                    </div>

                                </div>

                            </a>

                        </article>

                    @endforeach

                </div>

            </div>

        </section>
    @endif

</main>

@endsection

@push('scripts')
    <script src="{{ versioned_asset('assets/news/js/news-show.js') }}"></script>
@endpush