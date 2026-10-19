<?php

namespace Tests\Unit;

use App\Support\Text\Excerpt;
use PHPUnit\Framework\TestCase;

class ExcerptTest extends TestCase
{
    public function test_excerpt_keeps_whole_sentences_and_never_cuts_a_word(): void
    {
        $text = '<p>أعلنت الشركة عن نظام جديد. يعمل النظام على أجهزة متعددة ويوفر ميزات واسعة للمستخدمين في المنطقة كلها هذا العام.</p>';
        $this->assertSame('أعلنت الشركة عن نظام جديد.', Excerpt::make($text, 60));

        // جملة وحدة أطول من الحد: آخر كلمة كاملة + «…».
        $long = 'This is a single very long sentence that keeps going without any full stop for quite a while';
        $out = Excerpt::make($long, 40);
        $this->assertStringEndsWith('…', $out);
        $this->assertStringStartsWith('This is a single very long sentence', $out);
        $this->assertStringNotContainsString('wit…', $out);

        $this->assertTrue(Excerpt::looksCut('يتم الطلب عبر بطاقات الائتمان وباي بال اب...'));
        $this->assertFalse(Excerpt::looksCut('أعلنت الشركة عن نظام جديد.'));
    }
}
