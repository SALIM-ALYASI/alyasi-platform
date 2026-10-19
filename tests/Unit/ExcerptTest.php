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

    public function test_cutting_arabic_never_breaks_a_letter(): void
    {
        // جملة وحدة طويلة تنتهي الكلمة المقصوصة بحروف بايتاتها تشبه «،» و«ـ».
        $text = 'أصدرت الشركة يوم الاثنين نموذجًا مفتوح الوزن مصممًا لتشغيل عملاء الذكاء الاصطناعي محليًا على الأجهزة الاستهلاكية مما يوفر أوضح صورة حتى الآن لما يمكن أن تبدو عليه رؤية الرئيس التنفيذي لـ';

        foreach (range(40, 140, 7) as $max) {
            $out = Excerpt::make($text, $max);
            $this->assertTrue(mb_check_encoding($out, 'UTF-8'), "broken at {$max}");
            $this->assertStringEndsWith('…', $out);
        }
    }
}
