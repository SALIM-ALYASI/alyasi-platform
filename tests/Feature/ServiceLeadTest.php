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

    public function test_screens_a_batch_without_sending_any_message(): void
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

        // ما فيه تنبيه لكل دفعة (من 28 أكتوبر): أفضل 5 بالأسبوع بس.
        Http::assertNothingSent();
        $this->assertSame(0, ServiceLead::whereNotNull('notified_at')->count());
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

    public function test_weekly_message_has_only_the_best_five(): void
    {
        Config::set('services.whatsapp_notify', ['base_url' => 'https://bridge.test', 'api_key' => 'k', 'number' => '96800000000']);
        Http::fake(['bridge.test/*' => Http::response(['ok' => true])]);

        $budgets = [150, 5000, 300, 900, 120, 2500, 200];
        $this->sendLeads(['items' => array_map(fn ($b) => $this->marketplace([
            'url' => 'https://mostaql.com/project/'.$b, 'budget_usd' => $b, 'text' => 'مشروع رقم '.$b.' لبناء نظام ويب متكامل', 'summary' => 'مشروع '.$b,
        ]), $budgets)])->assertJson(['accepted' => 7]);
        // طلب عُماني بدون ميزانية بالدولار: أفضلية القرب (+600) تدخله بالخمسة.
        $this->sendLeads($this->lead(['url' => 'https://x.com/om/status/1']));
        // طلب قديم (9 أيام) ما يدخل.
        ServiceLead::query()->create(['agent' => 'grok', 'scope' => 'marketplace', 'platform' => 'mostaql', 'url' => 'https://mostaql.com/project/old', 'url_key' => 'old', 'text' => 'قديم', 'text_hash' => 'old', 'category' => 'web', 'budget_usd' => 9000, 'score' => 9000, 'status' => 'new'])
            ->forceFill(['created_at' => now()->subDays(9)])->save();
        Http::assertNothingSent();

        $this->artisan('leads:weekly-top')->assertSuccessful();
        $this->artisan('leads:weekly-top')->assertSuccessful(); // مرة وحدة بالأسبوع

        Http::assertSentCount(1);
        Http::assertSent(function ($request) {
            $m = $request['message'];

            return str_contains($m, '🎯 أفضل 5 طلبات عملاء هذا الأسبوع')
                && strpos($m, '$5,000') < strpos($m, '$2,500')
                && str_contains($m, 'عُمان – مسقط')
                && ! str_contains($m, '$9,000') && ! str_contains($m, '$150') && ! str_contains($m, '$120');
        });
        $this->assertSame(5, ServiceLead::whereNotNull('notified_at')->count());

        // بعد 8 أيام: اللي ما انختار ينحذف، والخمسة يبقون.
        $this->travel(9)->days();
        $this->artisan('model:prune', ['--model' => [ServiceLead::class]])->assertSuccessful();
        $this->assertSame(5, ServiceLead::count());
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
