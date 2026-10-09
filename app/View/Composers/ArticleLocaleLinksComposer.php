<?php

namespace App\View\Composers;

use App\Models\Permalink;
use Illuminate\Routing\Route;
use Illuminate\View\View;

/**
 * يحسب روابط تبديل اللغة (AR/EN) المعروضة في رأس الموقع.
 *
 * افتراضيًا (الصفحات التي ما زالت تعتمد على الجلسة فقط) يبقى التبديل
 * عبر /locale/{locale} كما كان.
 *
 * الصفحات ذات عنصر واحد له رابط دائم مختلف فعليًا لكل لغة (مقال/خبر/
 * خدمة — كلها عبر جدول permalinks) تحتاج بحثًا عن رابط اللغة الأخرى
 * الفعلي، أو إخفاء الزر إن لم توجد ترجمة.
 *
 * صفحات القوائم (index) والأعمال (Work — رابط واحد بلا فرق لغوي أصلاً)
 * أبسط: نفس الـ params، فرق فقط اسم الراوت المستهدف.
 */
class ArticleLocaleLinksComposer
{
    /**
     * أسماء الراوتات (بدون لاحقة .en) التي رابطها موحّد بكل لغة —
     * نفس الـ slug/params، فرق فقط اسم الراوت المستهدف.
     */
    private const SIMPLE_LOCALE_ROUTES = [
        'home',
        'services.index',
        'works.index',
        'works.show',
        'news.index',
        'contact',
        'careers',
        'about',
        'privacy',
        'terms',
        'social-links.index',
        'community.index',
        'event_coverage.cyberx_oman_2026',
        'tech-history.index',
    ];

    /**
     * أسماء الراوتات (بدون لاحقة .en) اللي تحتاج بحثًا عن رابط اللغة
     * الأخرى الفعلي عبر جدول permalinks، مع نوع linkable المطابق.
     */
    private const PERMALINK_BASED_ROUTES = [
        'articles.show' => 'article',
        'services.show' => 'service',
        'news.show' => 'news_article',
        // الرابط الذكي للخبر (/news/YYYY/MM/DD/NNN/slug) -- شكل كل الأخبار الحالية.
        'news.show.smart' => 'news_article',
        'event_editions.show' => 'event_edition',
    ];

    public function compose(View $view): void
    {
        $view->with('langLinks', $this->links());
    }

    /**
     * روابط الصفحة الحالية باللغتين. رابط اللغة الأخرى يكون رابطًا مباشرًا
     * لما تكون للصفحة نسخة فعلية بها، وإلا /locale/{locale}، أو null لو
     * المحتوى غير مترجم. يستخدمه أيضًا PreferDeviceLocale لتحويل الزائر.
     *
     * @return array{ar: string|null, en: string|null}
     */
    public function links(): array
    {
        $route = request()->route();
        $name = $route?->getName();

        $default = [
            'ar' => route('locale.switch', 'ar'),
            'en' => route('locale.switch', 'en'),
        ];

        $baseName = $name !== null && str_ends_with($name, '.en') ? substr($name, 0, -3) : $name;

        if ($baseName === 'articles.index') {
            return [
                'ar' => article_route('index', [], 'ar'),
                'en' => article_route('index', [], 'en'),
            ];
        }

        /*
         * منشورات المجتمع بلغة واحدة فقط بلا راوت مقابل — أفضل من تمرير
         * المستخدم عبر /locale/{locale} (يرجع للرئيسية) إخطه مباشرة إلى
         * صفحة قسم المجتمع باللغة المطلوبة.
         */
        if ($baseName === 'community.show') {
            return [
                'ar' => localized_route('community.index', [], 'ar'),
                'en' => localized_route('community.index', [], 'en'),
            ];
        }

        // حلقات تاريخ التقنية مقالات، لكن رابطها بقسمها الخاص مو /articles.
        if ($baseName === 'tech-history.show') {
            $permalink = Permalink::query()
                ->with('linkable.permalinks')
                ->where('linkable_type', 'article')
                ->where('slug', $route->parameter('slug'))
                ->first();
            $linkable = $permalink?->linkable;
            $arSlug = $linkable?->permalinks->firstWhere('locale', 'ar')?->slug;
            $enSlug = $linkable?->permalinks->firstWhere('locale', 'en')?->slug;

            return [
                'ar' => $arSlug ? route('tech-history.show', ['slug' => $arSlug]) : null,
                'en' => $enSlug ? route('tech-history.show.en', ['slug' => $enSlug]) : null,
            ];
        }

        if ($baseName !== null && array_key_exists($baseName, self::PERMALINK_BASED_ROUTES)) {
            return $this->permalinkLinks(
                $route,
                $baseName,
                self::PERMALINK_BASED_ROUTES[$baseName],
                $default
            );
        }

        if ($baseName !== null && in_array($baseName, self::SIMPLE_LOCALE_ROUTES, true)) {
            $params = $route->parameters();

            return [
                'ar' => localized_route($baseName, $params, 'ar'),
                'en' => localized_route($baseName, $params, 'en'),
            ];
        }

        return $default;
    }

    /**
     * @param  array{ar: string, en: string}  $default
     * @return array{ar: string|null, en: string|null}
     */
    private function permalinkLinks(?Route $route, string $baseName, string $linkableType, array $default): array
    {
        $slug = $route?->parameter('slug');

        $permalink = Permalink::query()
            ->with('linkable.permalinks')
            ->where('linkable_type', $linkableType)
            ->where('slug', $slug)
            ->first();

        $linkable = $permalink?->linkable;

        if (! $linkable) {
            return $default;
        }

        // Permalink::url() يبني الرابط الرسمي لكل نوع (ومنه رابط الخبر الذكي
        // اللي يحتاج التاريخ والتسلسل، مو الـ slug بس).
        return [
            'ar' => $linkable->permalinks->firstWhere('locale', 'ar')?->url(),
            'en' => $linkable->permalinks->firstWhere('locale', 'en')?->url(),
        ];
    }
}
