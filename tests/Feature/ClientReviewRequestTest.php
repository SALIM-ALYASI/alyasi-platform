<?php

namespace Tests\Feature;

use App\Models\Work;
use App\Support\Reviews\ReviewLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * صفحة طلب التقييم المؤقتة: بالرابط الموقّع بس، تنتهي، والتقييم يدخل المراجعة.
 */
class ClientReviewRequestTest extends TestCase
{
    use RefreshDatabase;

    private function work(): Work
    {
        return Work::query()->create([
            'title' => 'Sultan Law – الموقع الرسمي', 'title_en' => 'Sultan Law – Official Website', 'slug' => 'sultan-law',
            'type' => 'website', 'short_description' => 'وصف', 'description' => 'وصف', 'is_active' => true,
            'client_name' => 'مكتب سلطان البلوشي للمحاماة',
        ]);
    }

    private function path(string $url): string
    {
        return substr($url, strlen(url('')));
    }

    public function test_page_needs_a_valid_unexpired_signature(): void
    {
        $work = $this->work();
        $links = ReviewLink::make($work->slug, now()->addDays(30));

        $this->get('/review/sultan-law')->assertForbidden();
        $this->get($this->path($links['ar']))->assertOk()
            ->assertSee('كيف كانت تجربتك مع ALYASI؟')
            ->assertSee('مكتب سلطان البلوشي للمحاماة')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get($this->path($links['en']))->assertOk()->assertSee('How was your experience with ALYASI?');

        // جهاز إنجليزي يفتح الرابط العربي: يبقى عليه (ما يتحول ويبطل التوقيع).
        $this->withCookie('alyasi_lang', 'en')->get($this->path($links['ar']))->assertOk()->assertSee('كيف كانت تجربتك مع ALYASI؟');

        $this->travel(31)->days();
        $this->get($this->path($links['ar']))->assertForbidden();
    }

    public function test_review_needs_consent_and_lands_as_pending(): void
    {
        $work = $this->work();
        $path = $this->path(ReviewLink::make($work->slug, now()->addDays(30))['ar']);
        $review = ['rating' => 5, 'name' => 'سلطان البلوشي – مكتب سلطان البلوشي للمحاماة', 'body' => 'تعامل راقٍ والتزام بالمواعيد والنتيجة ممتازة.'];

        $this->post($path, $review)->assertSessionHasErrors('consent');
        $this->assertSame(0, $work->reviews()->count());

        $this->post($path, $review + ['consent' => '1'])->assertRedirect();
        $this->assertSame('pending', $work->reviews()->first()->status);
        $this->assertSame(0, $work->approvedReviews()->count());

        $this->get($path)->assertSee('شكرًا لك!');
    }
}
