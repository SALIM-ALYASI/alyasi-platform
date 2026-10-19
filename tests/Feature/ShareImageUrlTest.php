<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * صورة المشاركة تحمل ?v=وقت تعديل الملف، عشان واتساب يجيب الجديدة لما تتغير الصورة بنفس الاسم.
 */
class ShareImageUrlTest extends TestCase
{
    public function test_local_share_image_url_is_versioned_by_file_time(): void
    {
        $path = 'images/about/salem-al-hajri.jpg';

        $this->assertSame(
            asset($path).'?v='.filemtime(public_path($path)),
            share_image_url(asset($path))
        );
    }

    public function test_external_image_url_is_left_alone(): void
    {
        $this->assertSame('https://example.com/a.jpg', share_image_url('https://example.com/a.jpg'));
    }
}
