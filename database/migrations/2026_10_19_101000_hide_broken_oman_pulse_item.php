<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * نبض عُمان #5 (برنامج «حداثة» من وزارة النقل): انسحب من صفحة إصدارات إنجليزية
     * بدون تاريخ، ونصّه فقرة «عن الوزارة» العامة مب البيان نفسه. يتخفّى (ما ينحذف)،
     * والمعالج صار يرفض الصفحات الإنجليزية وغير المؤرخة.
     */
    public function up(): void
    {
        DB::table('oman_pulse_items')
            ->where('id', 5)
            ->where('title', 'like', 'Hadatha for Cybersecurity%')
            ->where('status', 'published')
            ->update(['status' => 'hidden', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('oman_pulse_items')->where('id', 5)->where('status', 'hidden')->update(['status' => 'published']);
    }
};
