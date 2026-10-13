<?php

namespace Tests\Feature;

use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkBilingualTest extends TestCase
{
    use RefreshDatabase;

    private function headers(string $lang = 'ar'): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Safari/605.1.15',
            'Accept-Language' => $lang,
        ];
    }

    private function bilingualWork(): Work
    {
        return Work::query()->create([
            'title' => 'ستار بوس | نظام إدارة المطاعم',
            'title_en' => 'STAR POS | Restaurant Management',
            'slug' => 'star-pos',
            'type' => 'application',
            'short_description' => 'وصف عربي مختصر',
            'short_description_en' => 'Short English summary',
            'description' => "الفقرة العربية الأولى\n\nالفقرة العربية الثانية",
            'description_en' => "First English paragraph\n\nSecond English paragraph",
            'cover_image' => 'works/covers/star-pos.png',
            'cover_is_illustrative' => true,
            'status' => 'in_development',
            'work_url' => 'https://starposoman.com',
            'is_active' => true,
        ]);
    }

    public function test_arabic_page_shows_arabic_text_status_and_cover_note(): void
    {
        $this->bilingualWork();

        $this->withHeaders($this->headers('ar'))->get('/works/star-pos')
            ->assertOk()
            ->assertSee('ستار بوس | نظام إدارة المطاعم')
            ->assertSee('قيد التطوير')
            ->assertSee('<p class="work-detail__desc">الفقرة العربية الثانية</p>', false)
            ->assertSee('صورة ترويجية توضيحية')
            ->assertSee('"creativeWorkStatus":"In development"', false)
            ->assertDontSee('First English paragraph');
    }

    public function test_english_page_uses_english_text(): void
    {
        $this->bilingualWork();

        $this->withHeaders($this->headers('en'))->get('/en/works/star-pos')
            ->assertOk()
            ->assertSee('STAR POS | Restaurant Management')
            ->assertSee('In Development')
            ->assertSee('Application')
            ->assertSee('<p class="work-detail__desc">Second English paragraph</p>', false)
            ->assertSee('not an actual screenshot')
            ->assertDontSee('الفقرة العربية الأولى');

        $this->withHeaders($this->headers('en'))->get('/en/works')
            ->assertOk()
            ->assertSee('Short English summary')
            ->assertSee('In Development');
    }

    public function test_video_work_shows_player_instead_of_cover(): void
    {
        Work::query()->create([
            'title' => 'إعلان كرم النخيل',
            'title_en' => 'Karam Al Nakhil Ad',
            'slug' => 'karam-ad',
            'type' => 'advertisement',
            'short_description' => 'إعلان فيديو',
            'cover_image' => 'works/covers/karam.jpg',
            'video_path' => 'works/videos/karam.mp4',
            'completed_at' => '2026-10-08',
            'is_active' => true,
        ]);

        $this->withHeaders($this->headers('ar'))->get('/works/karam-ad')
            ->assertOk()
            ->assertSee('<video', false)
            ->assertSee('storage/works/videos/karam.mp4#t=0.1', false)
            ->assertSee('controlslist="nodownload', false)
            ->assertSee('"@type":"VideoObject"', false)
            ->assertDontSee('class="work-detail__hero-media"', false);

        $this->withHeaders($this->headers('ar'))->get('/works/star-pos-missing')->assertNotFound();
    }

    public function test_existing_work_without_english_or_status_renders_as_before(): void
    {
        Work::query()->create([
            'title' => 'عمل قديم',
            'slug' => 'old-work',
            'type' => 'website',
            'short_description' => 'وصف قديم',
            'description' => "سطر أول\nسطر ثاني",
            'cover_image' => 'works/covers/old.png',
            'completed_at' => '2025-07-03',
            'is_active' => true,
        ]);

        $this->withHeaders($this->headers('en'))->get('/en/works/old-work')
            ->assertOk()
            ->assertSee('عمل قديم')
            ->assertSee('Completion Date')
            ->assertSee('03.07.2025')
            ->assertDontSee('In Development')
            ->assertDontSee('not an actual screenshot')
            ->assertDontSee('creativeWorkStatus', false)
            ->assertDontSee('<video', false)
            ->assertSee('class="work-detail__hero-media"', false);
    }
}
