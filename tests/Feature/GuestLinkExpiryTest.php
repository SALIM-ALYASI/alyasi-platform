<?php

namespace Tests\Feature;

use App\Models\PageEvent;
use App\Support\Interviews\GuestLinkExpiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GuestLinkExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_unopened_guest_links_expire_after_ten_days(): void
    {
        Carbon::setTestNow(GuestLinkExpiry::deadline()->subMinute());
        $this->assertFalse(GuestLinkExpiry::isExpired('sara'));

        Carbon::setTestNow(GuestLinkExpiry::deadline()->addMinute());
        $this->assertTrue(GuestLinkExpiry::isExpired('sara'));
        $this->assertFalse(GuestLinkExpiry::isExpired('haitham'), 'المنشورة مقابلاتهم مستثناة');

        PageEvent::query()->create(['path' => '/tools/cyberx-interview', 'type' => 'guest_open', 'label' => 'sara', 'traffic_class' => 'human']);
        $this->assertFalse(GuestLinkExpiry::isExpired('sara'), 'فتح الرابط يبقيه');
    }

    public function test_the_whole_tool_closes_one_month_after_the_conference(): void
    {
        Carbon::setTestNow(Carbon::parse(GuestLinkExpiry::TOOL_CLOSES_AT, 'Asia/Muscat')->subMinute());
        $this->get('/tools/cyberx-interview')->assertOk();

        Carbon::setTestNow(Carbon::parse(GuestLinkExpiry::TOOL_CLOSES_AT, 'Asia/Muscat')->addMinute());
        $this->get('/tools/cyberx-interview?guest=haitham')->assertStatus(410)->assertSee('انتهت فترة المقابلات');
        $this->assertTrue(GuestLinkExpiry::isExpired('haitham'));
    }

    public function test_expired_guest_is_removed_from_the_tool(): void
    {
        Carbon::setTestNow(GuestLinkExpiry::deadline()->addDay());

        $html = $this->get('/tools/cyberx-interview')->assertOk()->getContent();

        $this->assertStringNotContainsString('"id":"sara"', $html);
        $this->assertStringContainsString('"id":"haitham"', $html);
    }
}
