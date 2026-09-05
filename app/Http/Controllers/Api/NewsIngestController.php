<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\NotifiesWhatsApp;
use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\Permalink;
use App\Models\RegionalAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Mews\Purifier\Facades\Purifier;

class NewsIngestController extends Controller
{
    use NotifiesWhatsApp;

    /**
     * استقبال خبر كامل من بوت الأخبار وحفظه.
     *
     * عقد v2 صارم: لا يُنشأ ولا يُحدّث أي خبر إذا كانت إحدى بيانات النشر
     * الأساسية ناقصة. الحقول الاختيارية الوحيدة هنا هي حقول ALYASI Analysis.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title_en' => ['required', 'string', 'min:3', 'max:500'],
            'title_ar' => ['required', 'string', 'min:3', 'max:500'],
            'content_en' => ['required', 'string', 'min:20'],
            'content_ar' => ['required', 'string', 'min:20'],

            'category_slug' => [
                'required',
                'string',
                'max:120',
                Rule::exists('news_categories', 'slug')->where(
                    fn ($query) => $query
                        ->where('is_active', true)
                        ->whereNull('deleted_at')
                ),
            ],
            'slug' => [
                'required',
                'string',
                'max:200',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],

            // ALYASI Analysis — optional / non-blocking
            'analysis_status' => ['nullable', 'string', 'in:none,ready,skipped,failed'],
            'analysis_title_ar' => ['nullable', 'string', 'max:500'],
            'analysis_ar' => ['nullable', 'string'],
            'analysis_regional_angle_ar' => ['nullable', 'string'],
            'angle' => [
                'nullable', 'string',
                'in:price_reality,what_changed,who_cares,what_broke',
            ],

            'link' => ['required', 'url', 'max:2000'],
            'image' => $request->hasFile('image')
                ? ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096']
                : ['required', 'url', 'max:2000'],
            'source' => ['required', 'string', 'min:2', 'max:255'],
            'author' => ['required', 'string', 'min:2', 'max:255'],
            'published_at' => ['required', 'date'],
            'is_published' => ['required', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $extension = strtolower(
                $file->getClientOriginalExtension()
                ?: $file->extension()
                ?: 'png'
            );

            $fileName = sprintf(
                '%s-%s.%s',
                now()->format('YmdHis'),
                Str::lower(Str::random(12)),
                $extension
            );

            $validated['image'] = $file->storeAs(
                'news',
                $fileName,
                'public'
            );
        }

        $isPublished = $request->boolean('is_published');

        $category = NewsCategory::query()
            ->active()
            ->where('slug', $validated['category_slug'])
            ->firstOrFail();

        $article = DB::transaction(function () use ($validated, $isPublished, $category) {
            $article = NewsArticle::query()
                ->firstOrNew([
                    'source_url' => $validated['link'],
                ]);

            $excerptAr = $this->makeExcerpt($validated['content_ar']);
            $excerptEn = $this->makeExcerpt($validated['content_en']);

            $article->fill([
                'news_category_id' => $category->id,
                'title_ar' => $validated['title_ar'],
                'title_en' => $validated['title_en'],
                'excerpt_ar' => $excerptAr,
                'excerpt_en' => $excerptEn,
                'content_ar' => $this->sanitizeContent($validated['content_ar']),
                'content_en' => $this->sanitizeContent($validated['content_en']),

                // ALYASI Analysis
                'analysis_status' => $validated['analysis_status'] ?? 'none',
                'analysis_title_ar' => $validated['analysis_title_ar'] ?? null,
                'analysis_ar' => $this->sanitizeContent($validated['analysis_ar'] ?? null),
                'analysis_regional_angle_ar' => $this->sanitizeContent($validated['analysis_regional_angle_ar'] ?? null),
                'angle' => $validated['angle'] ?? null,

                'image' => $validated['image'],
                'image_alt_ar' => $validated['title_ar'],
                'image_alt_en' => $validated['title_en'],
                'source_name' => $validated['source'],
                'source_url' => $validated['link'],
                'author_name' => $validated['author'],
                'status' => $isPublished
                    ? NewsArticle::STATUS_PUBLISHED
                    : NewsArticle::STATUS_DRAFT,
                'published_at' => $isPublished
                    ? $validated['published_at']
                    : null,
                'seo_title_ar' => Str::limit($validated['title_ar'], 70, ''),
                'seo_title_en' => Str::limit($validated['title_en'], 70, ''),
                'seo_description_ar' => Str::limit((string) $excerptAr, 170, ''),
                'seo_description_en' => Str::limit((string) $excerptEn, 170, ''),
            ]);

            $article->save();

            // Smart News URL identity
            // التاريخ والرقم يُحددان مرة واحدة فقط ولا يتغيران لاحقاً.
            if (! $article->publication_date || ! $article->daily_sequence) {
                $publishedAt = $article->published_at ?? now();

                $publicationDate = $publishedAt
                    ->copy()
                    ->timezone('Asia/Muscat')
                    ->toDateString();

                // قفل سجلات اليوم أثناء تخصيص الرقم التالي لتجنب التكرار.
                // withTrashed() إلزامي هنا: الرقم دائم ولا يُعاد استخدامه حتى
                // لو حُذف الخبر لاحقاً (soft delete) -- القيد الفريد بقاعدة
                // البيانات ما يستثني السجلات المحذوفة، فتجاهلها بالحساب هنا
                // ينتج رقماً محجوزاً فعلياً ويطيح بـ Integrity constraint.
                $lastSequence = NewsArticle::withTrashed()
                    ->whereDate('publication_date', $publicationDate)
                    ->lockForUpdate()
                    ->max('daily_sequence');

                $article->publication_date = $publicationDate;
                $article->daily_sequence = ((int) $lastSequence) + 1;
                $article->save();

                Log::info('Smart news URL identity assigned.', [
                    'news_id' => $article->id,
                    'publication_date' => $publicationDate,
                    'daily_sequence' => $article->daily_sequence,
                ]);
            }

            $this->syncPermalinks($article, $validated['slug']);

            return $article;
        });

        if ($isPublished) {
            $this->notifyN8nOfNewArticle();
        }

        $permalink = $article->permalinks()->where('locale', 'ar')->first()
            ?? $article->permalinks()->first();

        if ($isPublished) {
            // الهاشتاقات نفسها المستخدمة ببقية قنوات النشر (بوت الأخبار)، عشان لو المستخدم
            // نسخ النص من واتساب ولصقه يدويًا بتويتر/X (ما فيه ربط API آلي هناك حاليًا)
            // يطلع جاهز بنفس هوية الهاشتاقات بلا ما يكتبها من جديد.
            $this->notifyWhatsApp(
                "📰 خبر جديد على ALYASI:\n{$article->title_ar}"
                .($permalink ? "\n{$permalink->url()}" : '')
                ."\n\n#AlyasiMagazine #الياسي #تقنية #أخبار_تقنية #عمان"
            );
        }

        return response()->json([
            'success' => true,
            'id' => $article->id,
            'created' => $article->wasRecentlyCreated,
            'url' => $permalink?->url(),
            'slug' => $permalink?->slug,
            'category_slug' => $category->slug,
            'image' => $article->image,
            'image_url' => filled($article->image)
                ? (
                    Str::startsWith($article->image, ['http://', 'https://'])
                        ? $article->image
                        : Storage::disk('public')->url($article->image)
                )
                : null,
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
                $permalink = $article->permalinks
                    ->firstWhere('locale', 'ar');

                return [
                    'id' => $article->id,
                    'title' => $article->title_ar,
                    'excerpt' => Str::limit(
                        strip_tags($article->content_ar ?: $article->excerpt_ar ?: ''),
                        500
                    ),
                    'source_name' => $article->source_name,
                    'published_at' => $article->published_at?->toIso8601String(),
                    'url' => $permalink?->url(),
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
     * قراءة فقط -- بيانات التوفر الإقليمي اللي يعبّيها المحرر يدويًا أثناء
     * المراجعة (لوحة الإدارة)، يستهلكها بوت الأخبار كسياق محلي (LOCAL_CONTEXT)
     * عند كتابة خبر يذكر منتجًا/شركة موجودة بالجدول.
     */
    public function regionalAvailability(): JsonResponse
    {
        $rows = RegionalAvailability::query()
            ->orderBy('entity')
            ->get([
                'entity', 'entity_type', 'status',
                'has_local_warranty', 'local_reseller', 'note',
            ]);

        return response()->json([
            'success' => true,
            'data' => $rows,
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
     * كل الأخبار المنشورة بتاريخ معيّن (اليوم افتراضيًا)، بمحتوى كافٍ
     * لتوليد فكرة مقال أصلي لاحقًا — يستهلكها n8n بجدولة يومية مستقلة.
     */
    public function dailyDigest(Request $request): JsonResponse
    {
        $date = $request->date('date') ?? now();

        $articles = NewsArticle::query()
            ->published()
            ->whereBetween('published_at', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ])
            ->orderBy('published_at')
            ->get()
            ->map(fn (NewsArticle $article) => [
                'id' => $article->id,
                'title_ar' => $article->title_ar,
                'title_en' => $article->title_en,
                'excerpt_ar' => $article->excerpt_ar,
                'content_ar' => strip_tags((string) $article->content_ar),
                'source_name' => $article->source_name,
                'source_url' => $article->source_url,
                'published_at' => $article->published_at?->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'date' => $date->toDateString(),
            'count' => $articles->count(),
            'data' => $articles,
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
     * حفظ الـ slug الإنجليزي المرسل من البوت للغتين، مع منع أي تعارض
     * مع خبر آخر. بهذا يصبح رابط العربية والإنجليزية ثابتًا ومقروءًا.
     */
    private function syncPermalinks(NewsArticle $article, string $slug): void
    {
        foreach (['ar', 'en'] as $locale) {
            $conflict = Permalink::query()
                ->where('locale', $locale)
                ->where('slug', $slug)
                ->where(function ($query) use ($article) {
                    $query
                        ->where('linkable_type', '!=', 'news_article')
                        ->orWhere('linkable_id', '!=', $article->id);
                })
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'slug' => ["Slug already exists for locale {$locale}."],
                ]);
            }

            Permalink::query()->updateOrCreate(
                [
                    'linkable_type' => 'news_article',
                    'linkable_id' => $article->id,
                    'locale' => $locale,
                ],
                [
                    'slug' => $slug,
                ]
            );
        }
    }
}
