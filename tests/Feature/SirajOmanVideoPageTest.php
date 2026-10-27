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

    private const FILE = 'storage/videos/siraj-oman-2026/siraj-v3-web.mp4';

    public function test_link_is_off_while_the_video_files_are_paused(): void
    {
        if (is_file(public_path(self::FILE))) {
            $this->markTestSkipped('ملف الفيديو موجود محليًا.');
        }

        $this->get('/siraj-oman-2026')->assertNotFound();
    }

    public function test_page_shows_player_and_download_button(): void
    {
        $file = public_path(self::FILE);
        $created = ! is_file($file);
        if ($created) {
            @mkdir(dirname($file), 0755, true);
            file_put_contents($file, 'test');
        }

        try {
            $this->runPageAssertions();
        } finally {
            if ($created) {
                @unlink($file);
                @rmdir(dirname($file));
                @rmdir(dirname($file, 2));
            }
        }
    }

    private function runPageAssertions(): void
    {
        $this->get('/siraj-oman-2026')->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertDontSee('فريق سراج عمان')
            ->assertSee('مدرسة زينب بنت الرسول للتعليم الأساسي')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee('storage/videos/siraj-oman-2026/siraj-v3-web.mp4', false)
            ->assertSee('download="zainab-bint-alrasool-school.mp4"', false)
            ->assertSee('تحميل الفيديو (MP4)');
    }
}
