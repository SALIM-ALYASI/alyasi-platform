<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * يجلب رابط فيديو يوتيوب (شورتس) المنشور فعليًا لكل خبر من smart-content
 * (news-bot-v2 يشغّل خط أنابيب فيديو مستقل لكل خبر على 5 منصات، ويتتبعه
 * smart-content بمعرّف job_id = "news-article-{id}" -- راجع publish.py
 * build_video_webhook_payload، story_id صار "article-{laravel_article_id}"
 * بدل الصيغة القديمة المرتبطة بمعرّف داخلي لبوت الأخبار فقط).
 *
 * الفيديو يجهز عادة خلال دقيقتين من النشر، فتشغيله كل ربع ساعة كافٍ.
 */
class SyncYoutubeLinks extends Command
{
    protected $signature = 'news:sync-youtube-links';

    protected $description = 'يحدّث رابط فيديو يوتيوب لكل خبر من حالة نشره الفعلية بـsmart-content';

    public function handle(): int
    {
        $baseUrl = config('services.smart_content.url');
        $key = config('services.smart_content.jobs_api_key');

        if (blank($baseUrl) || blank($key)) {
            return self::SUCCESS;
        }

        $articles = NewsArticle::query()
            ->published()
            ->whereNull('youtube_video_url')
            ->where('published_at', '>=', now()->subDays(3))
            ->get(['id']);

        foreach ($articles as $article) {
            $jobId = "news-article-{$article->id}";

            try {
                $response = Http::withHeaders(['x-api-key' => $key])
                    ->timeout(10)
                    ->get("{$baseUrl}/jobs/{$jobId}/platforms");
            } catch (\Throwable) {
                continue;
            }

            if (! $response->successful()) {
                continue;
            }

            if (
                $response->json('platforms.youtube.status') === 'published'
                && filled($youtubeUrl = $response->json('platforms.youtube.published_url'))
            ) {
                $article->update(['youtube_video_url' => $youtubeUrl]);
            }
        }

        return self::SUCCESS;
    }
}
