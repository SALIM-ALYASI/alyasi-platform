<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * التحقق (validation) يقول إن title_en/description_en اختياريان،
     * لكن الأعمدة كانت NOT NULL فعليًا — أي محاولة حفظ خدمة عربية فقط
     * كانت تنكسر بخطأ 500 صريح بدل رسالة تحقق واضحة. هذا الترحيل يجعل
     * الأعمدة تطابق النية المعلنة فعليًا.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('title_en')->nullable()->change();
            $table->text('description_en')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('title_en')->nullable(false)->change();
            $table->text('description_en')->nullable(false)->change();
        });
    }
};
