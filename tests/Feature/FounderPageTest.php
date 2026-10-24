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

    public function test_2021_libraries_milestone_sits_between_2019_and_2025(): void
    {
        $this->get('/about/founder')->assertOk()
            ->assertSeeInOrder(['أول تطبيق: «البادية»', 'نشر ثلاث مكتبات برمجية للمطورين', 'SALIMALYASICurved', 'SMDesign', 'SMMessage', 'تأسيس ALYASI'])
            ->assertSee('href="https://cocoapods.org/pods/SMMessage"', false)
            ->assertSee('آخر تحديث: 5 يوليو 2021');

        $this->get('/en/about/founder')->assertOk()
            ->assertSeeInOrder(['First app', 'Publishing Three Developer Libraries', 'SALIMALYASICurved', 'Founding ALYASI'])
            ->assertSee('Last updated: 25 June 2021');
    }
}
