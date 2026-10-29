<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * «صوتي»: ملاحظات سالم الصوتية تتحول لنص على الماك، وقاموس شخصي يتعلّم من
 * تصحيحاته (كلمات تتعطى للنموذج قبل التحويل + تصحيحات تنطبق بعده).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_notes', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64)->unique(); // take_id من المتصفح: نفس التسجيل ما ينحفظ مرتين
            $table->string('status', 20)->default('queued')->index(); // queued | transcribing | done | failed
            $table->string('language', 5)->default('ar');
            $table->string('original_path');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->float('duration_seconds')->nullable();
            $table->longText('transcript_raw')->nullable(); // مخرج النموذج قبل القاموس
            $table->longText('transcript')->nullable(); // بعد تصحيحات القاموس
            $table->longText('edited_text')->nullable(); // بعد تعديل سالم
            $table->timestamp('approved_at')->nullable()->index(); // سالم أكّد النص = درس لتدريب النموذج على صوته
            $table->timestamp('claimed_at')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();
        });

        Schema::create('voice_dictionary_entries', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 12); // term | correction
            $table->string('language', 5)->default('ar');
            $table->string('wrong', 120)->default(''); // للتصحيح فقط
            $table->string('right', 120);
            $table->unsignedInteger('uses')->default(1);
            $table->timestamps();
            $table->unique(['kind', 'language', 'wrong', 'right']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_dictionary_entries');
        Schema::dropIfExists('voice_notes');
    }
};
