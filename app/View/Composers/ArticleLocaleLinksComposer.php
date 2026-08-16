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
    ];

    /**
     * أسماء الراوتات (بدون لاحقة .en) اللي تحتاج بحثًا عن رابط اللغة
     * الأخرى الفعلي عبر جدول permalinks، مع نوع linkable المطابق.
     */
    private const PERMALINK_BASED_ROUTES = [
        'articles.show' => 'article',
        'services.show' => 'service',
        'news.show' => 'news_article',
    ];

    public function compose(View $view): void
    {
        $route = request()->route();
        $name = $route?->getName();

        $default = [
            'ar' => route('locale.switch', 'ar'),
            'en' => route('locale.switch', 'en'),
        ];

        $baseName = $name !== null && str_ends_with($name, '.en') ? substr($name, 0, -3) : $name;

        if ($baseName === 'articles.index') {
            $view->with('langLinks', [
                'ar' => article_route('index', [], 'ar'),
                'en' => article_route('index', [], 'en'),
            ]);

            return;
        }

        if ($baseName !== null && array_key_exists($baseName, self::PERMALINK_BASED_ROUTES)) {
            $view->with('langLinks', $this->permalinkLinks(
                $route,
                $baseName,
                self::PERMALINK_BASED_ROUTES[$baseName],
                $default
            ));

            return;
        }

        if ($baseName !== null && in_array($baseName, self::SIMPLE_LOCALE_ROUTES, true)) {
            $params = $route->parameters();

            $view->with('langLinks', [
                'ar' => localized_route($baseName, $params, 'ar'),
                'en' => localized_route($baseName, $params, 'en'),
            ]);

            return;
        }

        $view->with('langLinks', $default);
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

        $arSlug = $linkable->permalinks->firstWhere('locale', 'ar')?->slug;
        $enSlug = $linkable->permalinks->firstWhere('locale', 'en')?->slug;

        return [
            'ar' => $arSlug ? localized_route($baseName, ['slug' => $arSlug], 'ar') : null,
            'en' => $enSlug ? localized_route($baseName, ['slug' => $enSlug], 'en') : null,
        ];
    }
}
