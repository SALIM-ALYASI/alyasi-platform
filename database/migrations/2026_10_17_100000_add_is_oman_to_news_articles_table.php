<?php

use App\Models\NewsArticle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * أخبار تخص سلطنة عُمان تظهر في «نبض عُمان» إلى جانب صفحة الأخبار.
     * العلامة تنضبط تلقائيًا عند الحفظ (NewsArticle::detectOman)؛ هنا نعبّيها للأخبار الحالية.
     */
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table): void {
            $table->boolean('is_oman')->default(false)->after('angle');
            $table->index(['is_oman', 'published_at']);
        });

        DB::table('news_articles')->select(['id', 'title_ar', 'title_en', 'excerpt_ar', 'excerpt_en'])->orderBy('id')
            ->chunk(200, function ($rows): void {
                foreach ($rows as $row) {
                    if (NewsArticle::detectOman($row->title_ar, $row->title_en, $row->excerpt_ar, $row->excerpt_en)) {
                        DB::table('news_articles')->where('id', $row->id)->update(['is_oman' => true]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table): void {
            $table->dropIndex(['is_oman', 'published_at']);
            $table->dropColumn('is_oman');
        });
    }
};
