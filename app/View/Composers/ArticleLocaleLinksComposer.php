<?php

namespace App\View\Composers;

use App\Models\Article;
use App\Models\Permalink;
use Illuminate\View\View;

/**
 * يحسب روابط تبديل اللغة (AR/EN) المعروضة في رأس الموقع.
 *
 * افتراضيًا (كل الصفحات) يبقى التبديل عبر /locale/{locale} كما كان.
 * على صفحات المقالات تحديدًا، الروابط لغتها ثابتة بالرابط (prefix)،
 * لذا يجب حساب رابط النسخة المقابلة الفعلي من نفس المقال، أو إخفاء
 * الزر إن لم توجد ترجمة له.
 */
class ArticleLocaleLinksComposer
{
    public function compose(View $view): void
    {
        $route = request()->route();
        $name = $route?->getName();

        $default = [
            'ar' => route('locale.switch', 'ar'),
            'en' => route('locale.switch', 'en'),
        ];

        if (! in_array($name, ['articles.index', 'articles.index.en', 'articles.show', 'articles.show.en'], true)) {
            $view->with('langLinks', $default);

            return;
        }

        if (in_array($name, ['articles.index', 'articles.index.en'], true)) {
            $view->with('langLinks', [
                'ar' => article_route('index', [], 'ar'),
                'en' => article_route('index', [], 'en'),
            ]);

            return;
        }

        $locale = str_ends_with((string) $name, '.en') ? 'en' : 'ar';
        $slug = $route->parameter('slug');

        $permalink = Permalink::query()
            ->with('linkable')
            ->where('linkable_type', 'article')
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->first();

        $article = $permalink?->linkable;

        if (! $article instanceof Article) {
            $view->with('langLinks', $default);

            return;
        }

        $arSlug = $article->translatedSlug('ar');
        $enSlug = $article->translatedSlug('en');

        $view->with('langLinks', [
            'ar' => $arSlug ? article_route('show', [$arSlug], 'ar') : null,
            'en' => $enSlug ? article_route('show', [$enSlug], 'en') : null,
        ]);
    }
}
