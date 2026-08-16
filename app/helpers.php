<?php

if (! function_exists('media_url')) {
    /**
     * Resolve a content-image path to a public URL, regardless of whether
     * it was seeded as a plain public asset path (e.g. `images/home/x.webp`)
     * or saved by an upload flow as a path relative to the `public` storage
     * disk (e.g. `works/covers/x.jpg`). Mixing both formats in the same
     * database column (as WorkDemoSeeder vs. the admin upload flow do for
     * `works.cover_image`) is what causes 404s if only one format is assumed.
     */
    function media_url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'images/') || str_starts_with($normalized, 'assets/') || str_starts_with($normalized, 'uploads/') || str_starts_with($normalized, 'storage/')) {
            return asset($normalized);
        }

        return \Illuminate\Support\Facades\Storage::url($normalized);
    }
}

if (! function_exists('article_route')) {
    /**
     * بناء رابط لصفحة مقالات حسب اللغة (عربي بدون prefix، إنجليزي بـ /en).
     *
     * $name هو الاسم الأساسي للراوت: 'index' أو 'show'.
     */
    function article_route(string $name, array $parameters = [], ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        $routeName = $locale === 'en' ? "articles.{$name}.en" : "articles.{$name}";

        return route($routeName, $parameters);
    }
}

if (! function_exists('localized_route')) {
    /**
     * بناء رابط لصفحة عامة حسب اللغة (عربي بدون prefix، إنجليزي بـ /en)،
     * لأي راوت غير راوتات المقالات (article_route() تخصهم لمنطقها المختلف:
     * slug مختلف فعليًا لكل لغة). هنا نفس الـ slug/params لكل اللغتين،
     * فرق فقط اسم الراوت المستهدف.
     */
    function localized_route(string $name, array $parameters = [], ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if ($locale !== 'en') {
            return route($name, $parameters);
        }

        return route($name === 'home' ? 'home.en' : "{$name}.en", $parameters);
    }
}

if (! function_exists('render_rich_content')) {
    /**
     * تجهيز محتوى المقال للعرض: تصفية للوسوم المسموحة (دفاع إضافي رغم
     * تعقيمه مسبقًا وقت الحفظ)، ثم تحويل روابط الفيديو/الصوت المعروفة
     * تلقائيًا إلى زر CTA عبر App\Support\RichContentRenderer.
     */
    function render_rich_content(?string $html, string $allowedTags): string
    {
        $filtered = strip_tags((string) $html, $allowedTags);

        return \App\Support\RichContentRenderer::render($filtered);
    }
}

if (! function_exists('versioned_asset')) {
    /**
     * Build an asset URL with a cache-busting query string based on the
     * file's last modification time, so browsers fetch the latest version
     * of static CSS/JS files instead of serving a stale cached copy.
     */
    function versioned_asset(string $path): string
    {
        $fullPath = public_path($path);

        if (! file_exists($fullPath)) {
            return asset($path);
        }

        return asset($path).'?v='.filemtime($fullPath);
    }
}
