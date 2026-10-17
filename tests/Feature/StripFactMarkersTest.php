<?php

namespace Tests\Feature;

use App\Console\Commands\StripFactMarkers;
use Tests\TestCase;

class StripFactMarkersTest extends TestCase
{
    public function test_all_leaked_marker_forms_are_removed(): void
    {
        $this->assertSame('ميزة جديدة. وتسمح للمستخدمين', StripFactMarkers::clean('ميزة جديدة (fact_1). وتسمح (fact_2) للمستخدمين'));
        $this->assertSame('<p>Fraud reports rose.</p>', StripFactMarkers::clean('<p>Fraud reports rose (fact_3, fact_4).</p>'));
        $this->assertSame('Old form.', StripFactMarkers::clean('Old form [fact_1], [fact_3].'));
        $this->assertSame('نص سليم بدون إشارات', StripFactMarkers::clean('نص سليم بدون إشارات'));
    }
}
