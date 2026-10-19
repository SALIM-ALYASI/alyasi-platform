<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ServiceLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * طلبات العملاء من Grok: الخليج بس، الطلبات مب العروض، بدون تكرار، ورسالة
 * واتساب وحدة للدفعة (عبر رقم وهمي -- ما يوصل شي حقيقي).
 */
class ServiceLeadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.news_suggester.tokens', ['grok' => 'grok-secret']);
    }

    private function lead(array $overrides = []): array
    {
        return $overrides + [
            'url' => 'https://x.com/someone/status/'.random_int(1000, 999999),
            'platform' => 'x',
            'author' => '@someone',
            'text' => 'مطلوب مصمم جرافيك لتصميم شعار وهوية لمقهى جديد في مسقط، الميزانية 150 ريال',
            'summary' => 'شعار وهوية لمقهى في مسقط',
            'category' => 'graphic_design',
            'country' => 'om',
            'city' => 'مسقط',
            'budget' => '150 ر.ع',
            'posted_at' => now()->subHours(3)->toIso8601String(),
            'post_type' => 'request',
        ];
    }

    private function sendLeads(array $body)
    {
        return $this->withToken('grok-secret')->postJson('/api/service-leads', $body);
    }

    public function test_requires_the_agent_token(): void
    {
        $this->postJson('/api/service-leads', $this->lead())->assertUnauthorized();
        $this->assertSame(0, ServiceLead::count());
    }

    public function test_screens_a_batch_and_sends_one_whatsapp_message(): void
    {
        Config::set('services.whatsapp_notify', ['base_url' => 'https://bridge.test', 'api_key' => 'k', 'number' => '96800000000']);
        Http::fake(['bridge.test/*' => Http::response(['ok' => true])]);

        $response = $this->sendLeads(['items' => [
            $this->lead(['url' => 'https://twitter.com/a/status/1?s=20']),
            $this->lead(['url' => 'https://x.com/b/status/2', 'country' => 'EG', 'text' => 'مطلوب مونتير فيديو لقناة يوتيوب في القاهرة']),
            $this->lead(['url' => 'https://x.com/c/status/3', 'post_type' => null, 'text' => 'نقدم خدمات تصميم الشعارات بأسعار مناسبة، تواصل معنا']),
            $this->lead(['url' => 'https://x.com/d/status/4', 'posted_at' => now()->subDays(10)->toIso8601String(), 'text' => 'نحتاج مصمم موقع لشركة في دبي']),
            $this->lead(['url' => 'https://x.com/e/status/5', 'country' => 'AE', 'category' => 'video', 'budget' => null, 'text' => 'Looking for a video editor in Dubai for reels, urgent']),
            $this->lead(['url' => 'https://x.com/a/status/1']), // نفس الأول (twitter.com = x.com)
            ['url' => 'not-a-url'],
        ]])->assertOk();

        $response->assertJson(['received' => 7, 'accepted' => 2, 'rejected' => 3, 'duplicates' => 1, 'invalid' => 1]);
        $this->assertSame(['outside_region', 'offer', 'stale'], ServiceLead::where('status', 'rejected')->orderBy('id')->pluck('reject_reason')->all());

        // عُمان مع ميزانية أعلى أولوية من الإمارات.
        $this->assertGreaterThan(ServiceLead::where('country', 'AE')->value('score'), ServiceLead::where('country', 'OM')->value('score'));

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_contains($request['message'], '🎯 طلبات عملاء جديدة (2)')
            && str_contains($request['message'], '🇴🇲 عُمان – مسقط · تصميم جرافيك · X')
            && strpos($request['message'], 'عُمان') < strpos($request['message'], 'الإمارات'));
        $this->assertSame(2, ServiceLead::whereNotNull('notified_at')->count());
    }

    private function marketplace(array $overrides = []): array
    {
        return $overrides + [
            'scope' => 'marketplace',
            'url' => 'https://mostaql.com/project/'.random_int(1000, 999999),
            'platform' => 'mostaql',
            'text' => 'تصميم شعار ومطبوعات لمنصة عقارية جديدة مع دليل هوية',
            'summary' => 'شعار ومطبوعات لمنصة عقارية',
            'category' => 'graphic_design',
            'budget' => '100 - 250 دولار',
            'budget_usd' => 250,
            'posted_at' => now()->subHours(2)->toIso8601String(),
            'post_type' => 'request',
        ];
    }

    public function test_marketplace_projects_wait_for_one_evening_digest(): void
    {
        Config::set('services.whatsapp_notify', ['base_url' => 'https://bridge.test', 'api_key' => 'k', 'number' => '96800000000']);
        Http::fake(['bridge.test/*' => Http::response(['ok' => true])]);

        // الصبح: تنحفظ بدون رسالة.
        $this->travelTo(now('Asia/Muscat')->setTime(9, 5)->utc());
        $this->sendLeads(['items' => [
            $this->marketplace(),
            $this->marketplace(['url' => 'https://khamsat.com/community/requests/1', 'platform' => 'khamsat', 'budget_usd' => 600, 'text' => 'مطلوب مونتاج فيديوهات قصيرة لقناة تعليمية', 'summary' => 'مونتاج فيديوهات قصيرة', 'category' => 'video']),
            $this->marketplace(['url' => 'https://mostaql.com/project/77', 'budget_usd' => 40, 'text' => 'تصميم 4 أغلفة لعبوات جاهزة بسرعة']),
            $this->marketplace(['url' => 'https://x.com/z/status/9', 'platform' => 'x', 'text' => 'need a logo for my brand asap please']),
        ]])->assertJson(['accepted' => 2, 'rejected' => 2]);

        $this->assertSame(['low_budget', 'not_marketplace'], ServiceLead::where('status', 'rejected')->orderBy('id')->pluck('reject_reason')->all());
        Http::assertNothingSent();

        // المسا: أول دفعة بعد 7 ترسل القائمة (الأعلى ميزانية أول)، ومرة وحدة باليوم.
        $this->travelTo(now('Asia/Muscat')->setTime(20, 10)->utc());
        $this->sendLeads(['items' => [$this->marketplace(['url' => 'https://mostaql.com/project/88', 'budget_usd' => 120, 'text' => 'إعداد متجر إلكتروني على سلة مع تصميم الواجهة'])]]);
        $this->artisan('leads:marketplace-digest')->assertSuccessful();

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_contains($request['message'], '📋 أفضل مشاريع مستقل وخمسات اليوم (3)')
            && strpos($request['message'], '$600') < strpos($request['message'], '$250')
            && str_contains($request['message'], 'خمسات · فيديو ومونتاج'));
    }

    public function test_same_text_from_another_url_is_a_duplicate(): void
    {
        $this->sendLeads($this->lead(['url' => 'https://x.com/a/status/10']))->assertJson(['accepted' => 1]);
        $this->sendLeads($this->lead(['url' => 'https://x.com/b/status/11']))->assertJson(['rejected' => 1]);

        $this->assertSame('duplicate_text', ServiceLead::latest('id')->value('reject_reason'));
    }

    public function test_admin_lists_and_updates_leads(): void
    {
        $this->sendLeads($this->lead());
        $lead = ServiceLead::first();
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->get('/admin/service-leads')->assertOk()->assertSee('شعار وهوية لمقهى في مسقط');
        $this->actingAs($admin, 'admin')->patch("/admin/service-leads/{$lead->id}/status", ['status' => 'contacted'])->assertRedirect();

        $this->assertSame('contacted', $lead->fresh()->status);
    }

    public function test_leads_are_pruned_after_60_days(): void
    {
        $this->sendLeads($this->lead());
        ServiceLead::query()->update(['created_at' => now()->subDays(61)]);

        $this->artisan('model:prune', ['--model' => [ServiceLead::class]])->assertSuccessful();

        $this->assertSame(0, ServiceLead::count());
    }
}
