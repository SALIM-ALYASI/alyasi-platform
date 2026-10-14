<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * روابط أخبار يقترحها وكيل خارجي (روتين Grok كل ساعة). الرابط بس يُستخدم:
 * بوت الأخبار يسحبه ويجلب المصدر بنفسه ويمرّره على كل المراحل -- نص الوكيل
 * يُحفظ للمراجعة، وما ينشر أبدًا.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('url', 2000);
            $table->char('url_hash', 64)->unique();
            $table->string('title', 500)->nullable();
            $table->text('summary')->nullable();
            $table->text('why_important')->nullable();
            $table->string('source_name', 255)->nullable();
            $table->string('suggested_by', 50)->default('grok');
            // pending -> fetched / duplicate / failed (يحدّثها بوت الأخبار)
            $table->string('status', 20)->default('pending')->index();
            $table->string('status_note', 500)->nullable();
            $table->unsignedBigInteger('candidate_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_suggestions');
    }
};
