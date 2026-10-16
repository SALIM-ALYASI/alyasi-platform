<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * صور الأخبار الجاهزة للمنصات (بطاقة 4:5 عربية، وإنجليزية لو للخبر نسخة
     * إنجليزية). وكيل Grok يقرأها من /api/news-cards/pending وينشرها في X
     * بنفسه، ويرجع النتيجة على /api/news-cards/{id}/result.
     */
    public function up(): void
    {
        Schema::create('news_cards', function (Blueprint $table): void {
            $table->id();
            $table->string('source_type', 30); // news | oman_pulse
            $table->unsignedBigInteger('source_id');
            $table->string('title_ar', 500);
            $table->string('title_en', 500)->nullable();
            $table->string('url', 1000);
            $table->string('image_ar', 500);
            $table->string('image_en', 500)->nullable();
            $table->string('text_x', 600);
            $table->string('x_status', 20)->default('pending'); // pending | posted | failed | skipped
            $table->string('x_tweet_url', 500)->nullable();
            $table->string('x_note', 500)->nullable();
            $table->timestamp('x_result_at')->nullable();
            $table->timestamps();

            $table->unique(['source_type', 'source_id']);
            $table->index(['x_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_cards');
    }
};
