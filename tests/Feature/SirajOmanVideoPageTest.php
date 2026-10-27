<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * صفحة مشاركة فيديو «فريق سراج عمان 2026»: تفتح بالعربي، فيها المشغّل وزر التحميل، وما تنفهرس.
 */
class SirajOmanVideoPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_shows_player_and_download_button(): void
    {
        $this->get('/siraj-oman-2026')->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('فريق سراج عمان 2026')
            ->assertSee('مدرسة زينب بنت الرسول للتعليم الأساسي')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee('storage/videos/siraj-oman-2026/siraj-oman-2026-1080.mp4', false)
            ->assertSee('download="siraj-oman-2026-4k.mp4"', false)
            ->assertSee('تحميل بجودة عالية (4K)');
    }
}
