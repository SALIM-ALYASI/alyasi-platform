<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FounderPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_founder_page_is_public_and_linked_from_about(): void
    {
        $this->get('/about/founder')
            ->assertOk()
            ->assertSee('سالم محمد الحجري')
            ->assertSee('محطات في الطريق إلى ALYASI')
            ->assertDontSee('مسودة للمراجعة')
            ->assertHeaderMissing('X-Robots-Tag');

        $this->get('/en/about/founder')
            ->assertOk()
            ->assertSee('Salem Mohammed Al Hajri')
            ->assertDontSee('Draft for review');

        $this->get('/about')->assertOk()->assertSee('/about/founder', false);
        $this->get('/en/about')->assertOk()->assertSee('/en/about/founder', false);
    }
}
