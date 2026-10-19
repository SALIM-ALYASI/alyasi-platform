<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FounderPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_founder_draft_is_hidden_without_a_signed_preview_link(): void
    {
        $this->get('/about/founder')->assertNotFound();

        $this->get(URL::signedRoute('about.founder', [], absolute: false))
            ->assertOk()
            ->assertSee('سالم محمد الحجري')
            ->assertSee('مسودة للمراجعة')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $this->get(URL::signedRoute('about.founder.en', [], absolute: false))
            ->assertOk()
            ->assertSee('Salem Mohammed Al Hajri');
    }
}
