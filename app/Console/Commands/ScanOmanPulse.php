<?php

namespace App\Console\Commands;

use App\Jobs\ProcessOmanPulseSuggestion;
use App\Models\NewsSuggestion;
use App\Models\OmanPulseItem;
use App\Support\OmanPulse\TechTopic;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * وكيل «نبض عُمان»: يقرأ خلاصات RSS الرسمية لوكالة الأنباء العُمانية (المحليات
 * والاقتصاد) كل نص ساعة، يرشّح الأخبار التقنية، ويمرّرها على نفس مسار النشر
 * (النص الرسمي حرفيًا + فحص التقنية + التكرار + عمر الخبر). بدون حد ثابت للعدد.
 */
class ScanOmanPulse extends Command
{
    protected $signature = 'oman-pulse:scan {--limit=5 : أقصى عدد أخبار جديدة بكل تشغيل}';

    protected $description = 'وكيل نبض عُمان: يرشّح الأخبار التقنية من خلاصات وكالة الأنباء العُمانية';

    public const AGENT = 'ona-rss';

    public const FEEDS = [
        'https://omannews.gov.om/rss/lang/ar/cat/3', // المحليات
        'https://omannews.gov.om/rss/lang/ar/cat/7', // الاقتصاد
    ];

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $candidates = 0;
        $processed = 0;

        foreach (self::FEEDS as $feed) {
            $xml = $this->fetch($feed);

            // الأقدم أول، عشان ترتيب البطاقات يطابق ترتيب صدور الأخبار.
            foreach (array_reverse($this->items($xml)) as $item) {
                if ($processed >= $limit) {
                    break 2;
                }

                if (! $item['published_at'] || $item['published_at']->lt(now()->subHours(72))) {
                    continue;
                }

                $tag = TechTopic::agentTag($item['title'], $item['description']);
                if ($tag === null) {
                    continue;
                }

                $candidates++;
                $hash = NewsSuggestion::hashUrl($item['url']);

                if (NewsSuggestion::query()->where('url_hash', $hash)->exists()
                    || OmanPulseItem::query()->where('source_url_hash', $hash)->exists()) {
                    continue;
                }

                $suggestion = NewsSuggestion::query()->create([
                    'url' => $item['url'],
                    'url_hash' => $hash,
                    'title' => $item['title'],
                    'source_name' => 'وكالة الأنباء العُمانية',
                    'country' => 'OM',
                    'source_published_at' => $item['published_at'],
                    'suggested_by' => self::AGENT,
                    'section' => 'oman-pulse',
                    'category' => $tag,
                    'status' => 'pending',
                ]);

                ProcessOmanPulseSuggestion::dispatchSync($suggestion->id);
                $processed++;
            }
        }

        // آخر تشغيل (لمتابعة إن الجدولة شغّالة فعلًا).
        \App\Models\Setting::set('oman_pulse_last_scan', now()->toDateTimeString()." | {$candidates}/{$processed}");

        $this->info("أخبار تقنية بالخلاصات: {$candidates} | جديدة تمت معالجتها: {$processed}");

        return self::SUCCESS;
    }

    private function fetch(string $feed): string
    {
        try {
            return Http::timeout(20)
                ->withUserAgent('ALYASI-OmanPulse/1.0 (+https://alyasi.dev/news/oman)')
                // الوكالة ما ترسل الشهادة الوسيطة؛ نتحقق بسلسلة DigiCert الرسمية.
                ->withOptions(['verify' => resource_path('certs/omannews-chain.pem')])
                ->get($feed)
                ->throw()
                ->body();
        } catch (\Throwable $e) {
            Log::warning('oman_pulse_scan: تعذّر قراءة الخلاصة', ['feed' => $feed, 'error' => $e->getMessage()]);

            return '';
        }
    }

    /**
     * @return list<array{title: string, url: string, description: string, published_at: ?Carbon}>
     */
    private function items(string $xml): array
    {
        if ($xml === '') {
            return [];
        }

        $previous = libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        libxml_use_internal_errors($previous);

        if (! $doc || ! isset($doc->channel->item)) {
            return [];
        }

        $items = [];
        foreach ($doc->channel->item as $node) {
            $url = trim((string) $node->link);
            if (! preg_match('#^https://omannews\.gov\.om/#', $url)) {
                continue;
            }

            try {
                $publishedAt = Carbon::parse((string) $node->pubDate)->utc();
            } catch (\Throwable) {
                $publishedAt = null;
            }

            $items[] = [
                'title' => trim(preg_replace('/\s+/u', ' ', (string) $node->title)),
                'url' => $url,
                'description' => trim(preg_replace('/\s+/u', ' ', strip_tags((string) $node->description))),
                'published_at' => $publishedAt,
            ];
        }

        return $items;
    }
}
