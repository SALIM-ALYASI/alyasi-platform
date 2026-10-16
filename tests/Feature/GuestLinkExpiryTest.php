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

    public function test_expired_guest_is_removed_from_the_tool(): void
    {
        Carbon::setTestNow(GuestLinkExpiry::deadline()->addDay());

        $html = $this->get('/tools/cyberx-interview')->assertOk()->getContent();

        $this->assertStringNotContainsString('"id":"sara"', $html);
        $this->assertStringContainsString('"id":"haitham"', $html);
    }
}
