<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\Models\Permalink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class NewsIngestController extends Controller
{
    /**
     * استقبال خبر من بوت الأخبار وحفظه.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title_en' => ['required', 'string', 'max:500'],
            'title_ar' => ['required', 'string', 'max:500'],
            'content_en' => ['nullable', 'string'],
            'content_ar' => ['nullable', 'string'],
            'link' => ['required', 'url', 'max:2000'],
            'image' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $isPublished = $request->boolean('is_published', true);

        $article = DB::transaction(function () use ($validated, $isPublished) {
            $article = NewsArticle::query()
                ->firstOrNew([
                    'source_url' => $validated['link'],
                ]);

            $article->fill([
                'title_ar' => $validated['title_ar'],
                'title_en' => $validated['title_en'],
                'excerpt_ar' => $this->makeExcerpt($validated['content_ar'] ?? null),
                'excerpt_en' => $this->makeExcerpt($validated['content_en'] ?? null),
                'content_ar' => $this->sanitizeContent($validated['content_ar'] ?? null),
                'content_en' => $this->sanitizeContent($validated['content_en'] ?? null),
                'image' => $validated['image'] ?? null,
                'source_name' => $validated['source'] ?? 'TechCrunch',
                'source_url' => $validated['link'],
                'author_name' => $validated['author'] ?? null,
                'status' => $isPublished
                    ? NewsArticle::STATUS_PUBLISHED
                    : NewsArticle::STATUS_DRAFT,
                'published_at' => $isPublished
                    ? ($validated['published_at'] ?? now())
                    : null,
            ]);

            $article->save();

            $this->syncPermalinks($article);

            return $article;
        });

        return response()->json([
            'success' => true,
            'id' => $article->id,
            'created' => $article->wasRecentlyCreated,
        ]);
    }

    /**
     * إنشاء مقتطف مختصر من المحتوى الكامل.
     */
    private function makeExcerpt(?string $content): ?string
    {
        if (blank($content)) {
            return null;
        }

        return Str::limit(strip_tags($content), 200);
    }

    /**
     * تعقيم محتوى الخبر القادم من مصدر خارجي (بوت الأخبار) لمنع
     * حقن سكربتات أو HTML خطر قبل تخزينه وعرضه لاحقًا بدون escaping.
     */
    private function sanitizeContent(?string $content): ?string
    {
        if (blank($content)) {
            return null;
        }

        return Purifier::clean($content);
    }

    /**
     * إنشاء أو تحديث الروابط الدائمة للخبر باللغتين.
     */
    private function syncPermalinks(NewsArticle $article): void
    {
        foreach (['ar' => $article->title_ar, 'en' => $article->title_en] as $locale => $title) {
            if (blank($title)) {
                continue;
            }

            $permalink = Permalink::query()
                ->where('linkable_type', 'news_article')
                ->where('linkable_id', $article->id)
                ->where('locale', $locale)
                ->first();

            if ($permalink) {
                continue;
            }

            Permalink::query()->create([
                'linkable_type' => 'news_article',
                'linkable_id' => $article->id,
                'locale' => $locale,
                'slug' => $this->generateUniqueSlug($title),
            ]);
        }
    }

    /**
     * إنشاء Slug فريد للرابط الدائم.
     */
    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);

        if ($baseSlug === '') {
            $baseSlug = 'news';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Permalink::query()
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
