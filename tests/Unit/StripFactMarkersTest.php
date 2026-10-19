<?php

namespace Tests\Unit;

use App\Console\Commands\StripFactMarkers;
use PHPUnit\Framework\TestCase;

class StripFactMarkersTest extends TestCase
{
    public function test_every_marker_shape_is_removed(): void
    {
        $this->assertSame('as reported.', StripFactMarkers::clean('as reported (fact_1).'));
        $this->assertSame('see this and that.', StripFactMarkers::clean('see this [fact_1, fact_3] and that.'));
        // مقصوصة بآخر نص مختصر: تنشال والنص ينتهي بـ«…».
        $this->assertSame('عبر المسار /mailbox…', StripFactMarkers::clean('عبر المسار /mailbox [fact_1, fact_2'));
        $this->assertSame('recruitment messages…', StripFactMarkers::clean('recruitment messages (fact_1...'));
        $this->assertSame('الالتزام (Commit)…', StripFactMarkers::clean('الالتزام (Commit) (fact_1, fact_...'));
        // بدون أقواس.
        $this->assertSame('Middle attacks. To mitigate', StripFactMarkers::clean('Middle attacks fact_1. To mitigate'));
        // كلمات عادية تبقى.
        $this->assertSame('facts_list and factory', StripFactMarkers::clean('facts_list and factory'));
    }
}
