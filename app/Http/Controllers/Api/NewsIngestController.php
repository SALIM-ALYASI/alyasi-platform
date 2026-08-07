<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\Models\Permalink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        if ($isPublished) {
            $this->notifyN8nOfNewArticle();
        }

        return response()->json([
            'success' => true,
            'id' => $article->id,
            'created' => $article->wasRecentlyCreated,
        ]);
    }

    /**
     * تنبيه n8n فورًا بوجود خبر منشور جديد لنشره على برامج التواصل،
     * بدل الانتظار لدورة الجدولة القادمة. فشل التنبيه لا يوقف حفظ الخبر.
     */
    private function notifyN8nOfNewArticle(): void
    {
        $webhookUrl = config('services.n8n.news_webhook_url');

        if (blank($webhookUrl)) {
            return;
        }

        try {
            Http::timeout(5)->post($webhookUrl);
        } catch (\Throwable $e) {
            Log::warning('تعذّر تنبيه n8n بخبر جديد.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * الأخبار المنشورة اللي ما انرسلت لبرامج التواصل بعد (لاستهلاك n8n).
     */
    public function pendingSocial(Request $request): JsonResponse
    {
        $limit = min($request->integer('limit', 5), 20);

        $articles = NewsArticle::query()
            ->with('permalinks')
            ->pendingSocial()
            ->limit($limit)
            ->get()
            ->map(function (NewsArticle $article) {
                $slug = $article->permalinks
                    ->firstWhere('locale', 'ar')
                    ?->slug;

                return [
                    'id' => $article->id,
                    'title' => $article->title_ar,
                    'excerpt' => Str::limit(
                        strip_tags($article->content_ar ?: $article->excerpt_ar ?: ''),
                        500
                    ),
                    'source_name' => $article->source_name,
                    'published_at' => $article->published_at?->toIso8601String(),
                    'url' => $slug ? url("/news/{$slug}") : null,
                ];
            })
            ->filter(fn (array $article) => $article['url'] !== null)
            ->values();

        return response()->json([
            'success' => true,
            'data' => $articles,
        ]);
    }

    /**
     * تحديد الخبر كمُرسَل لبرامج التواصل (بعد نشره فعليًا عبر n8n).
     */
    public function markSocialSent(NewsArticle $newsArticle): JsonResponse
    {
        $newsArticle->update(['social_sent_at' => now()]);

        return response()->json(['success' => true]);
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
