<?php

namespace App\Jobs;

use App\Models\NewsSuggestion;
use App\Models\OmanPulseItem;
use App\Support\OmanPulse\OfficialPageExtractor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * «نبض عُمان»: يحوّل رابط بيان رسمي (من Grok) إلى بطاقة بنصّه الأصلي -- بدون
 * أي ذكاء اصطناعي في المحتوى. يشتغل بعد الرد على Grok مباشرة (afterResponse)،
 * فما يحتاج جدولة على السيرفر.
 */
class ProcessOmanPulseSuggestion implements ShouldQueue
{
    use Queueable;

    public const DAILY_LIMIT = 3;

    public function __construct(public int $suggestionId)
    {
    }

    public function handle(): void
    {
        $suggestion = NewsSuggestion::query()->find($this->suggestionId);

        if (! $suggestion || $suggestion->status !== 'pending' || $suggestion->section !== 'oman-pulse') {
            return;
        }

        $today = now('Asia/Muscat')->startOfDay()->utc();
        if (OmanPulseItem::query()->where('created_at', '>=', $today)->count() >= self::DAILY_LIMIT) {
            $this->finish($suggestion, 'failed', 'تجاوز حصة نبض عُمان اليومية ('.self::DAILY_LIMIT.')');

            return;
        }

        try {
            $response = $this->client($suggestion->url)->get($suggestion->url);
        } catch (\Throwable $e) {
            $this->finish($suggestion, 'failed', 'تعذّر فتح الصفحة: '.Str::limit($e->getMessage(), 120));

            return;
        }

        if (! $response->successful() || ! str_contains((string) $response->header('Content-Type'), 'html')) {
            $this->finish($suggestion, 'failed', 'الصفحة ردّت '.$response->status());

            return;
        }

        $page = OfficialPageExtractor::extract($response->body());

        if (! $page['title'] || ! $page['paragraphs']) {
            $this->finish($suggestion, 'failed', 'ما لقينا عنوان أو نص خبر في الصفحة');

            return;
        }

        if ($duplicate = $this->similarRecentItem($page['title'])) {
            $this->finish($suggestion, 'duplicate', 'نفس خبر نبض عُمان #'.$duplicate->id);

            return;
        }

        $publishedAt = $page['published_at'] ?? $suggestion->source_published_at;
        if ($publishedAt && $publishedAt->lt(now()->subHours(72))) {
            $this->finish($suggestion, 'failed', 'خبر قديم ('.$publishedAt->toDateString().')');

            return;
        }

        $item = OmanPulseItem::query()->create([
            'suggestion_id' => $suggestion->id,
            'title' => $page['title'],
            'lead' => $page['paragraphs'][0],
            'paragraphs' => array_slice($page['paragraphs'], 1),
            'source_name' => $suggestion->source_name ?: (parse_url($suggestion->url, PHP_URL_HOST) ?: 'المصدر الرسمي'),
            'source_url' => $suggestion->url,
            'source_url_hash' => $suggestion->url_hash,
            'source_published_at' => $publishedAt,
            'image' => $page['image'] ? $this->storeImage($page['image']) : null,
            'tag' => $this->tag($suggestion->category, $page['title'].' '.$page['paragraphs'][0]),
            'status' => 'published',
        ]);

        $this->finish($suggestion, 'fetched', 'نبض عُمان #'.$item->id);
    }

    /**
     * وكالة الأنباء العُمانية ما ترسل الشهادة الوسيطة؛ نتحقق منها بسلسلة
     * DigiCert الرسمية بدل تعطيل التحقق.
     */
    private function client(string $url)
    {
        $host = (string) parse_url($url, PHP_URL_HOST);
        $verify = str_ends_with($host, 'omannews.gov.om') ? resource_path('certs/omannews-chain.pem') : true;

        return Http::timeout(20)
            ->withUserAgent('ALYASI-OmanPulse/1.0 (+https://alyasi.dev/news/oman)')
            ->withOptions(['verify' => $verify]);
    }

    private function storeImage(string $url): ?string
    {
        try {
            $response = $this->client($url)->get($url);
        } catch (\Throwable) {
            return null;
        }

        $type = (string) $response->header('Content-Type');
        $ext = match (true) {
            str_contains($type, 'png') => 'png',
            str_contains($type, 'webp') => 'webp',
            str_contains($type, 'jpeg'), str_contains($type, 'jpg') => 'jpg',
            default => null,
        };

        if (! $response->successful() || ! $ext || strlen($response->body()) > 4 * 1024 * 1024) {
            return null;
        }

        $dir = public_path('uploads/oman-pulse');
        File::ensureDirectoryExists($dir, 0755, true);
        $name = now()->format('YmdHis').'-'.Str::lower(Str::random(10)).'.'.$ext;
        File::put($dir.'/'.$name, $response->body());

        return 'uploads/oman-pulse/'.$name;
    }

    /**
     * نفس البيان من رابطين (مثلًا الوكالة والجهة نفسها): تشابه كلمات العنوان.
     */
    private function similarRecentItem(string $title): ?OmanPulseItem
    {
        $words = fn (string $t) => array_unique(array_filter(
            preg_split('/\s+/u', preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $t)),
            fn ($w) => mb_strlen($w) > 2
        ));
        $mine = $words($title);

        return OmanPulseItem::query()
            ->where('created_at', '>=', now()->subDays(3))
            ->get()
            ->first(function (OmanPulseItem $item) use ($mine, $words) {
                $theirs = $words($item->title);
                $union = count(array_unique([...$mine, ...$theirs]));

                return $union > 0 && count(array_intersect($mine, $theirs)) / $union >= 0.6;
            });
    }

    private function tag(?string $category, string $text): string
    {
        if ($category && array_key_exists($category, OmanPulseItem::TAGS)) {
            return $category;
        }

        return match (true) {
            (bool) preg_match('/سيبران|اختراق|احتيال/u', $text) => 'cyber',
            (bool) preg_match('/رقمي|تقني|ذكاء اصطناعي|اتصالات|إلكتروني|منصة/u', $text) => 'tech',
            (bool) preg_match('/اقتصاد|استثمار|تمويل|ميزانية|صادرات/u', $text) => 'economy',
            (bool) preg_match('/خدمة|خدمات|تراخيص|إجراءات/u', $text) => 'services',
            default => 'society',
        };
    }

    private function finish(NewsSuggestion $suggestion, string $status, string $note): void
    {
        $suggestion->update(['status' => $status, 'status_note' => $note, 'processed_at' => now()]);
        Log::info('oman_pulse_processed', ['suggestion' => $suggestion->id, 'status' => $status, 'note' => $note]);
    }
}
