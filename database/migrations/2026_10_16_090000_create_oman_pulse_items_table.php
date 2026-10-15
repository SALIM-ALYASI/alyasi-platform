<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * «نبض عُمان»: أخبار رسمية عُمانية بنصّها الأصلي (بدون ذكاء اصطناعي). Grok
 * يرسل رابط البيان الرسمي، والموقع يستخرج العنوان والفقرات والتاريخ والصورة
 * من الصفحة الرسمية نفسها وينشرها منسوبة لمصدرها.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oman_pulse_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('suggestion_id')->nullable()->index();
            $table->string('title', 500);
            $table->text('lead');
            $table->json('paragraphs');
            $table->string('source_name', 255);
            $table->string('source_url', 2000);
            $table->char('source_url_hash', 64)->unique();
            $table->timestamp('source_published_at')->nullable();
            $table->string('image', 500)->nullable();
            $table->string('tag', 30)->default('services');
            $table->string('status', 20)->default('published')->index();
            $table->timestamps();
        });

        Schema::table('news_suggestions', function (Blueprint $table) {
            // null = أخبار البوت العادية؛ oman-pulse = نبض عُمان (ما يسحبها البوت)
            $table->string('section', 30)->nullable()->after('suggested_by')->index();
            $table->string('category', 30)->nullable()->after('section');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oman_pulse_items');

        Schema::table('news_suggestions', function (Blueprint $table) {
            $table->dropIndex(['section']);
            $table->dropColumn(['section', 'category']);
        });
    }
};
