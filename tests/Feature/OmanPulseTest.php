<?php

namespace Tests\Feature;

use App\Models\NewsSuggestion;
use App\Models\OmanPulseItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OmanPulseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.news_suggester.tokens', ['grok' => 'grok-secret']);
        Config::set('services.news_bot.token', 'bot-secret');
    }

    private function page(string $title, ?string $date = null): string
    {
        $date ??= now('Asia/Muscat')->day.' '.['', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'][now('Asia/Muscat')->month].' '.now('Asia/Muscat')->year;

        return '<html><head><meta property="og:title" content="'.$title.'"><meta property="og:image" content="https://omannews.gov.om/img/a.jpg"></head><body>'
            .'<aside><p>خبر جانبي لا علاقة له بالبيان ويجب ألا يظهر في البطاقة إطلاقًا.</p></aside>'
            .'<div class="post-content">'
            .'<p>مسقط في '.$date.' /العُمانية/ أطلقت وزارة النقل والاتصالات وتقنية المعلومات اليوم خدمة رقمية جديدة للمواطنين.</p>'
            .'<p>وتتيح الخدمة إنجاز المعاملات إلكترونيًا عبر المنصة الوطنية دون الحاجة لمراجعة المكاتب.</p>'
            .'<p>وأكدت الوزارة أن الخدمة متاحة على مدار الساعة عبر التطبيق والموقع الإلكتروني.</p>'
            .'</div></body></html>';
    }

    private function suggest(array $body)
    {
        return $this->withToken('grok-secret')->postJson('/api/news-suggestions', $body + ['section' => 'oman-pulse']);
    }

    public function test_official_page_becomes_a_verbatim_pulse_card(): void
    {
        Http::fake([
            'omannews.gov.om/topics/*' => Http::response($this->page('إطلاق خدمة رقمية جديدة للمعاملات'), 200, ['Content-Type' => 'text/html; charset=utf-8']),
            'omannews.gov.om/img/*' => Http::response('jpgbytes', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $this->suggest(['url' => 'https://omannews.gov.om/topics/ar/1/show/1', 'source_name' => 'وكالة الأنباء العُمانية', 'category' => 'services'])
            ->assertStatus(202);

        $item = OmanPulseItem::query()->firstOrFail();
        $this->assertSame('إطلاق خدمة رقمية جديدة للمعاملات', $item->title);
        $this->assertStringStartsWith('مسقط في', $item->lead);
        $this->assertCount(2, $item->paragraphs);
        $this->assertSame('services', $item->tag);
        $this->assertNotNull($item->source_published_at);
        $this->assertStringNotContainsString('خبر جانبي', $item->lead.implode(' ', $item->paragraphs));
        $this->assertSame('fetched', NewsSuggestion::query()->value('status'));

        // ما يروح لبوت الأخبار
        $this->assertCount(0, $this->withToken('bot-secret')->getJson('/api/news-suggestions/pending')->json('data'));

        $this->get('/news/oman')
            ->assertOk()
            ->assertSee('إطلاق خدمة رقمية جديدة للمعاملات')
            ->assertSee('https://omannews.gov.om/topics/ar/1/show/1', false)
            ->assertSee('وكالة الأنباء العُمانية')
            ->assertSee('دون صياغة أو إضافة');

        $this->get('/news/oman?tag=cyber')->assertOk()->assertDontSee('إطلاق خدمة رقمية جديدة للمعاملات');

        // البطاقة تفتح صفحة قراءة فيها النص الرسمي كامل ورابط البيان.
        $this->get('/news/oman')->assertSee('/news/oman/'.$item->id, false);
        $this->get('/news/oman/'.$item->id)
            ->assertOk()
            ->assertSee('<h1 class="pulse-read__title">إطلاق خدمة رقمية جديدة للمعاملات</h1>', false)
            ->assertSee('وتتيح الخدمة إنجاز المعاملات إلكترونيًا')
            ->assertSee('https://omannews.gov.om/topics/ar/1/show/1', false)
            ->assertSee('"@type":"NewsArticle"', false)
            ->assertSee('property="og:type"', false);

        $item->update(['status' => 'hidden']);
        $this->get('/news/oman/'.$item->id)->assertNotFound();
    }

    public function test_daily_limit_duplicates_and_old_news_are_skipped(): void
    {
        Http::fake(function ($request) {
            $n = (int) basename($request->url());
            $titles = [1 => 'قرار أول عن الاتصالات', 2 => 'إعلان ثانٍ عن التمويل', 3 => 'خبر ثالث عن الأمن السيبراني', 4 => 'خبر رابع جديد تمامًا'];

            return Http::response($this->page($titles[$n] ?? 'قرار أول عن الاتصالات'), 200, ['Content-Type' => 'text/html']);
        });

        $this->suggest(['url' => 'https://gov.om/news/1'])->assertStatus(202);
        // نفس الخبر من رابط ثاني
        $this->suggest(['url' => 'https://gov.om/news/9'])->assertStatus(202);
        $this->assertSame('duplicate', NewsSuggestion::query()->where('url', 'https://gov.om/news/9')->value('status'));

        $this->suggest(['url' => 'https://gov.om/news/2'])->assertStatus(202);
        $this->suggest(['url' => 'https://gov.om/news/3'])->assertStatus(202);
        $this->suggest(['url' => 'https://gov.om/news/4'])->assertStatus(202);

        $this->assertSame(3, OmanPulseItem::query()->count());
        $this->assertStringContainsString('حصة', NewsSuggestion::query()->where('url', 'https://gov.om/news/4')->value('status_note'));
    }

    public function test_old_statement_is_rejected(): void
    {
        Http::fake(['*' => Http::response($this->page('خبر قديم', '3 مارس 2025'), 200, ['Content-Type' => 'text/html'])]);

        $this->suggest(['url' => 'https://gov.om/news/old'])->assertStatus(202);

        $this->assertSame(0, OmanPulseItem::query()->count());
        $this->assertStringContainsString('قديم', NewsSuggestion::query()->value('status_note'));
    }
}
