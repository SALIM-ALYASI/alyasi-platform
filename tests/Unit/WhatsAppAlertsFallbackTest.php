<?php

namespace Tests\Unit;

use App\Support\WhatsAppAlerts;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppAlertsFallbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.whatsapp_notify.base_url' => 'http://bridge.test',
            'services.whatsapp_notify.api_key' => 'test-key',
            'services.whatsapp_notify.number' => '96800000000',
        ]);

        Http::fake(['bridge.test/*' => Http::response(['ok' => true])]);
    }

    public function test_failed_status_sends_remembered_text_via_bridge_once(): void
    {
        WhatsAppAlerts::rememberFallback('wamid.ABC', 'نص الخبر البديل');

        $failed = ['id' => 'wamid.ABC', 'status' => 'failed', 'errors' => [['code' => 131047]]];

        $this->assertTrue(WhatsAppAlerts::handleStatus($failed));
        $this->assertFalse(WhatsAppAlerts::handleStatus($failed));

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->url() === 'http://bridge.test/send-message'
            && $request['message'] === 'نص الخبر البديل'
            && $request['number'] === '96800000000');
    }

    public function test_delivered_or_unknown_messages_are_ignored(): void
    {
        WhatsAppAlerts::rememberFallback('wamid.OK', 'نص');

        $this->assertFalse(WhatsAppAlerts::handleStatus(['id' => 'wamid.OK', 'status' => 'delivered']));
        $this->assertFalse(WhatsAppAlerts::handleStatus(['id' => 'wamid.OTHER', 'status' => 'failed']));

        Http::assertNothingSent();
    }

    public function test_reactivate_hint_is_appended_once_per_day_on_closed_window(): void
    {
        $hint = '🔄 لتفعيل الأخبار بالصور والأزرار: https://wa.me/96894443706?text=%D8%AA%D9%85';
        $closed = fn (string $id) => ['id' => $id, 'status' => 'failed', 'errors' => [['code' => 131047]]];

        WhatsAppAlerts::rememberFallback('wamid.1', 'خبر 1', $hint);
        WhatsAppAlerts::rememberFallback('wamid.2', 'خبر 2', $hint);

        WhatsAppAlerts::handleStatus($closed('wamid.1'));
        WhatsAppAlerts::handleStatus($closed('wamid.2'));

        $messages = Http::recorded()->map(fn ($pair) => $pair[0]['message'])->all();

        $this->assertSame(["خبر 1\n\n".$hint, 'خبر 2'], $messages);
    }
}
