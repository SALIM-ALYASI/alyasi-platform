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
            && str_contains($request['message'], '🇴🇲 عُمان – مسقط · تصميم جرافيك')
            && strpos($request['message'], 'عُمان') < strpos($request['message'], 'الإمارات'));
        $this->assertSame(2, ServiceLead::whereNotNull('notified_at')->count());
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
